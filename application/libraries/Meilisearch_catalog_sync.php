<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Builds the public product projection shared by full and incremental syncs.
 * Import endpoints use this best-effort service after they finish updating SQL.
 */
class Meilisearch_catalog_sync
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('meilisearch_client');
    }

    public function getProductRows($afterId, $limit)
    {
        return $this->selectProductRows(
            'p.id > ?',
            array((int) $afterId),
            max(1, min(1000, (int) $limit)),
            'p.id ASC'
        );
    }

    /** Return products whose indexed brand text can change after a brand edit. */
    public function getProductIdsForBrand($brandId)
    {
        $brandId = (int) $brandId;
        if ($brandId <= 0) return array();

        $rows = $this->CI->db
            ->select('id')
            ->where('brand_id', $brandId)
            ->get('products')
            ->result_array();

        return array_map('intval', array_column($rows, 'id'));
    }

    /**
     * Return products in a category subtree. Search documents include ancestor
     * names, so changing a parent category also requires refreshing its descendants.
     */
    public function getProductIdsForCategoryTree($categoryId)
    {
        $categoryId = (int) $categoryId;
        if ($categoryId <= 0) return array();

        $categories = $this->CI->db
            ->select('id, parent_id')
            ->get('categories')
            ->result_array();

        $childrenByParent = array();
        foreach ($categories as $category) {
            $parentId = (int) $category['parent_id'];
            $childrenByParent[$parentId][] = (int) $category['id'];
        }

        $categoryIds = array($categoryId);
        $seen = array($categoryId => true);
        for ($i = 0; $i < count($categoryIds); $i++) {
            $parentId = $categoryIds[$i];
            foreach ($childrenByParent[$parentId] ?? array() as $childId) {
                if (isset($seen[$childId])) continue;
                $seen[$childId] = true;
                $categoryIds[] = $childId;
            }
        }

        $rows = $this->CI->db
            ->select('id')
            ->where_in('category_id', $categoryIds)
            ->get('products')
            ->result_array();

        return array_map('intval', array_column($rows, 'id'));
    }

    /** Update eligible product documents and remove changed products that are no longer public. */
    public function syncProducts(array $productIds)
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds), static function ($id) {
            return $id > 0;
        })));
        if (empty($productIds)) return true;

        $client = $this->CI->meilisearch_client;
        if (!$client->isConfigured()) {
            log_message('error', 'Meilisearch catalogue sync skipped: service URL is not configured.');
            return false;
        }

        $index = (string) config_item('meilisearch_index');
        if ($client->getIndex($index) === false) {
            log_message('error', 'Meilisearch catalogue sync skipped: index is unavailable. ' . $client->getLastError());
            return false;
        }

        $allSucceeded = true;
        foreach (array_chunk($productIds, 500) as $ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $rows = $this->selectProductRows('p.id IN (' . $placeholders . ')', $ids, count($ids), 'p.id ASC');
            $documents = array();
            $indexedIds = array();

            foreach ($rows as $row) {
                $documents[] = $this->makeDocument($row);
                $indexedIds[] = (int) $row->id;
            }

            if (!empty($documents)
                && $client->updateDocuments($index, $documents, 'id') === false
            ) {
                log_message('error', 'Meilisearch catalogue sync failed while updating product documents. ' . $client->getLastError());
                $allSucceeded = false;
            }

            $removedIds = array_values(array_diff($ids, $indexedIds));
            if (!empty($removedIds)
                && $client->deleteDocuments($index, $removedIds) === false
            ) {
                log_message('error', 'Meilisearch catalogue sync failed while removing unavailable products. ' . $client->getLastError());
                $allSucceeded = false;
            }
        }

        return $allSucceeded;
    }

    private function selectProductRows($whereSql, array $bindings, $limit, $orderBy)
    {
        $giftCondition = '';
        if ($this->CI->db->field_exists('isGift', 'products')) {
            $giftCondition = ' AND p.isGift = 0';
        }

        $limit = max(1, min(1000, (int) $limit));
        $sql = "
            SELECT
                p.id,
                p.SKU,
                p.GUID,
                p.barcode,
                p.category_id,
                p.brand_id,
                p.titleRU,
                p.titleRO,
                p.titleEN,
                p.uriRU,
                p.uriRO,
                CASE
                    WHEN p.price > 0 THEN p.price
                    ELSE (
                        SELECT MIN(pv_price_effective.price)
                        FROM products_variable pv_price_effective
                        WHERE pv_price_effective.product_id = p.id
                          AND pv_price_effective.isShown = 1
                          AND pv_price_effective.price > 0
                          AND pv_price_effective.qty > 0
                    )
                END AS price,
                COALESCE(
                    (
                        SELECT MIN(
                            CASE
                                WHEN pv_current.discount_price > 0
                                 AND pv_current.discount_price < pv_current.price
                                THEN pv_current.discount_price
                                ELSE pv_current.price
                            END
                        )
                        FROM products_variable pv_current
                        WHERE pv_current.product_id = p.id
                          AND pv_current.isShown = 1
                          AND pv_current.price > 0
                          AND pv_current.qty > 0
                    ),
                    CASE
                        WHEN p.discount_price > 0 AND p.discount_price < p.price
                        THEN p.discount_price
                        ELSE p.price
                    END
                ) AS current_price,
                CASE
                    WHEN p.priceWH > 0 THEN p.priceWH
                    ELSE (
                        SELECT MIN(pv_price_wh_effective.priceWH)
                        FROM products_variable pv_price_wh_effective
                        WHERE pv_price_wh_effective.product_id = p.id
                          AND pv_price_wh_effective.isShown = 1
                          AND pv_price_wh_effective.priceWH > 0
                          AND pv_price_wh_effective.qtyWH > 0
                    )
                END AS priceWH,
                p.discount_price,
                p.sale_percent,
                p.sale_mdl,
                p.stock,
                p.best_selling,
                CASE
                    WHEN (
                        p.discount_price > 0
                        AND p.discount_price < p.price
                    ) OR EXISTS(
                        SELECT 1
                        FROM products_variable pv_discount
                        WHERE pv_discount.product_id = p.id
                          AND pv_discount.isShown = 1
                          AND pv_discount.price > 0
                          AND pv_discount.qty > 0
                          AND pv_discount.discount_price > 0
                          AND pv_discount.discount_price < pv_discount.price
                    ) THEN 1
                    ELSE 0
                END AS has_discount,
                CASE
                    WHEN p.on_stock > 0 THEN p.on_stock
                    ELSE (
                        SELECT COALESCE(SUM(pv_stock.qty), 0)
                        FROM products_variable pv_stock
                        WHERE pv_stock.product_id = p.id
                          AND pv_stock.isShown = 1
                          AND pv_stock.price > 0
                          AND pv_stock.qty > 0
                    )
                END AS on_stock,
                CASE
                    WHEN p.on_stockWH > 0 THEN p.on_stockWH
                    ELSE (
                        SELECT COALESCE(SUM(pv_stock_wh.qtyWH), 0)
                        FROM products_variable pv_stock_wh
                        WHERE pv_stock_wh.product_id = p.id
                          AND pv_stock_wh.isShown = 1
                          AND pv_stock_wh.priceWH > 0
                          AND pv_stock_wh.qtyWH > 0
                    )
                END AS on_stockWH,
                p.volume,
                p.is_new,
                p.sex,
                p.search_teg,
                b.title AS brand_title,
                c.titleRU AS category_titleRU,
                c.titleRO AS category_titleRO,
                CONCAT_WS(' ', c.titleRU, cp1.titleRU, cp2.titleRU, cp3.titleRU, cp4.titleRU) AS category_pathRU,
                CONCAT_WS(' ', c.titleRO, cp1.titleRO, cp2.titleRO, cp3.titleRO, cp4.titleRO) AS category_pathRO,
                c.uriRU AS category_uriRU,
                c.uriRO AS category_uriRO,
                (
                    SELECT pi.img
                    FROM products_img pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.sorder ASC, pi.id DESC
                    LIMIT 1
                ) AS image,
                (
                    SELECT GROUP_CONCAT(DISTINCT CONCAT_WS(' ', pv_text.titleRU, pv_text.titleRO, pv_text.titleEN, pv_text.colorRU, pv_text.colorRO, pv_text.colorEN, pv_text.VolumeVar) SEPARATOR ' ')
                    FROM products_variable pv_text
                    WHERE pv_text.product_id = p.id
                      AND pv_text.isShown = 1
                ) AS indexed_search_text,
                (
                    SELECT GROUP_CONCAT(DISTINCT pv_sku.SKU SEPARATOR ' ')
                    FROM products_variable pv_sku
                    WHERE pv_sku.product_id = p.id
                      AND pv_sku.isShown = 1
                ) AS variant_skus,
                (
                    SELECT GROUP_CONCAT(DISTINCT pv_barcode.barcode SEPARATOR ' ')
                    FROM products_variable pv_barcode
                    WHERE pv_barcode.product_id = p.id
                      AND pv_barcode.isShown = 1
                ) AS variant_barcodes,
                EXISTS(
                    SELECT 1
                    FROM products_variable pv
                    WHERE pv.product_id = p.id
                      AND pv.isShown = 1
                ) AS has_variants
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN categories cp1 ON cp1.id = c.parent_id
            LEFT JOIN categories cp2 ON cp2.id = cp1.parent_id
            LEFT JOIN categories cp3 ON cp3.id = cp2.parent_id
            LEFT JOIN categories cp4 ON cp4.id = cp3.parent_id
            WHERE {$whereSql}
              AND p.isShown = 1
              {$giftCondition}
              AND EXISTS(
                  SELECT 1
                  FROM products_img pi_exists
                  WHERE pi_exists.product_id = p.id
              )
              AND (
                  p.price > 0
                  OR EXISTS(
                      SELECT 1
                      FROM products_variable pv_price
                      WHERE pv_price.product_id = p.id
                        AND pv_price.isShown = 1
                        AND pv_price.price > 0
                        AND pv_price.qty > 0
                  )
              )
            ORDER BY {$orderBy}
            LIMIT {$limit}
        ";

        return $this->CI->db->query($sql, $bindings)->result();
    }

    public function makeDocument($row)
    {
        $searchText = implode(' ', array_filter(array(
            $row->indexed_search_text,
            $row->search_teg,
            $row->titleRU,
            $row->titleRO,
            $row->titleEN,
            $row->brand_title,
            $row->category_titleRU,
            $row->category_titleRO,
            $row->category_pathRU,
            $row->category_pathRO,
        ), static function ($value) {
            return trim((string) $value) !== '';
        }));

        return array(
            'id' => (int) $row->id,
            'SKU' => (string) $row->SKU,
            'GUID' => (string) $row->GUID,
            'barcode' => (string) $row->barcode,
            'variant_barcodes' => $this->cleanText($row->variant_barcodes),
            'titleRU' => $this->cleanText($row->titleRU),
            'titleRO' => $this->cleanText($row->titleRO),
            'titleEN' => $this->cleanText($row->titleEN),
            'brand_id' => (int) $row->brand_id,
            'brand' => $this->cleanText($row->brand_title),
            'category_id' => (int) $row->category_id,
            'categoryRU' => $this->cleanText($row->category_titleRU),
            'categoryRO' => $this->cleanText($row->category_titleRO),
            'category_pathRU' => $this->cleanText($row->category_pathRU),
            'category_pathRO' => $this->cleanText($row->category_pathRO),
            'category_uriRU' => (string) $row->category_uriRU,
            'category_uriRO' => (string) $row->category_uriRO,
            'uriRU' => (string) $row->uriRU,
            'uriRO' => (string) $row->uriRO,
            'image' => (string) $row->image,
            'volume' => (string) $row->volume,
            'price' => (float) $row->price,
            'current_price' => (float) $row->current_price,
            'priceWH' => (float) $row->priceWH,
            'discount_price' => (float) $row->discount_price,
            'has_discount' => (bool) $row->has_discount,
            'sale_percent' => (float) $row->sale_percent,
            'sale_mdl' => (float) $row->sale_mdl,
            'stock' => (int) $row->stock,
            'best_selling' => (int) $row->best_selling,
            'sex' => (string) $row->sex,
            'on_stock' => (int) $row->on_stock,
            'on_stockWH' => (int) $row->on_stockWH,
            'has_stock' => (int) ((int) $row->on_stock > 0),
            'is_new' => (int) $row->is_new,
            'has_variants' => (bool) $row->has_variants,
            'search_text' => $this->cleanText($searchText),
            'variant_skus' => $this->cleanText($row->variant_skus),
        );
    }

    private function cleanText($value)
    {
        return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
