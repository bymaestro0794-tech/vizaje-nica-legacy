<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Offers_model extends BaseModel
{
    protected $tblname = 'offers';

    public function __construct()
    {
        parent::__construct();
    }

    private function progressModeSelect($alias = 'offers')
    {
        if ($this->db->field_exists('progress_mode', 'offers')) {
            return "COALESCE(NULLIF($alias.progress_mode, ''), 'time') AS progress_mode";
        }

        return "'time' AS progress_mode";
    }

    private function remainingStockSelect($alias = 'products')
    {
        return "GREATEST(
            COALESCE($alias.on_stock, 0),
            COALESCE((
                SELECT SUM(pv_offer_stock.qty)
                FROM products_variable pv_offer_stock
                WHERE pv_offer_stock.product_id = $alias.id
                  AND pv_offer_stock.isShown = 1
            ), 0)
        ) AS remaining_stock";
    }

    public function gat_products_offers($id)
    {
        if (empty($id)) {
            return false;
        }
        $this->db->select("
            id as id,
            offers_id as offers_id,
            product_id as product_id,
            (SELECT brand_id FROM products WHERE products.id = offers_products.product_id) AS brand_id,
            title as title,
            SKU as SKU,
            sorder as sorder,
        ");
        $this->db->where('offers_id', $id);
        $this->db->order_by('sorder ASC, id DESC');
        return $this->db->get('offers_products')->result();
    }

    public function get_offers_data_by_uri($lang, $uri)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            location$lang as location,
            uri$lang as uri,
            uriRU as uriRU,
            uriRO as uriRO,
            uriEN as uriEN,
            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,
            img$lang as img,
            imgB$lang as imgB,
            view_home,
            " . $this->progressModeSelect('offers') . ",
            date_from,
            date_to,
        ");
        $this->db->where('isShown', 1);
        $this->db->where("uri$lang", $uri);
        $today = date('Y-m-d');
        $this->db->group_start()
            ->where('date_from <=', $today)
            ->or_where('date_from IS NULL', null, false)
            ->or_where('date_from', '')
            ->or_where('date_from', '0000-00-00')
            ->group_end();
        $this->db->group_start()
            ->where('date_to >=', $today)
            ->or_where('date_to IS NULL', null, false)
            ->or_where('date_to', '')
            ->or_where('date_to', '0000-00-00')
            ->group_end();
        return $this->db->get($this->tblname)->row();
    }

    public function get_offers_data_by_id($lang, $id)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            location$lang as location,
            uri$lang as uri,
            uriRU as uriRU,
            uriRO as uriRO,
            uriEN as uriEN,
            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,
                     img$lang as img,
            imgB$lang as imgB,
            view_home,
            " . $this->progressModeSelect('offers') . ",
            date_from,
            date_to,
        ");
        $this->db->where('isShown', 1);
        $this->db->where("id", $id);
        $today = date('Y-m-d');
        $this->db->group_start()
            ->where('date_from <=', $today)
            ->or_where('date_from IS NULL', null, false)
            ->or_where('date_from', '')
            ->or_where('date_from', '0000-00-00')
            ->group_end();
        $this->db->group_start()
            ->where('date_to >=', $today)
            ->or_where('date_to IS NULL', null, false)
            ->or_where('date_to', '')
            ->or_where('date_to', '0000-00-00')
            ->group_end();
        return $this->db->get($this->tblname)->row();
    }

    public function get_offers_home($lang = false)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            uri$lang as uri,
                     img$lang as img,
            imgB$lang as imgB,
            view_home,
            " . $this->progressModeSelect('offers') . "
        ");
        $this->db->where('date_from <=', date('Y-m-d'));
        $this->db->where('date_to >=', date('Y-m-d'));
        $this->db->where('isShown', 1);
        $this->db->where('view_home', 1);
        $this->db->order_by('sorder ASC, date_to DESC, id DESC');
        $offers = $this->db->get($this->tblname)->result();
        return $offers;
    }

    public function get_offers($lang = false)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            uri$lang as uri,
                     img$lang as img,
            imgB$lang as imgB,
            view_home,
            is_online
        ");
        $this->db->where('isShown', 1);
        $this->db->order_by('sorder ASC, date_to DESC, id DESC');
        $offers = $this->db->get($this->tblname)->result();
        return $offers;
    }

    public function get_offers_active($lang = false)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            id,
            date_from,
            date_to,
            title$lang as title,
            desc$lang as desc,
            uri$lang as uri,
                     img$lang as img,
            imgB$lang as imgB,
            view_home,
            " . $this->progressModeSelect('offers') . ",
            date_from,
            date_to,
        ");
        $this->db->where('date_from <', date('Y-m-d'));
        $this->db->where('date_to >', date('Y-m-d'));
        $this->db->order_by('sorder ASC, date_to DESC,  id DESC');
        $offers = $this->db->get($this->tblname)->result();
        if (!empty($offers)) {
            foreach ($offers as $offer) {
                if (!empty($offer->view_home)) {
                    $offers_products = $this->db->select('id,product_id')->where('offers_id', $offer->id)->offset(0)->limit(6)->order_by('sorder ASC, id DESC')->get('offers_products')->result();
                    if (!empty($offers_products)) {
                        foreach ($offers_products as $value) {
                            $products_id[] = $value->product_id;
                        }
                        $offer->products = $this->products_offers($lang, $products_id);
                    }
                }
            }
        }
        return $offers;
    }

    public function get_active_offer_for_product($lang, $productId)
    {
        if (empty($lang) || empty($productId)) {
            return null;
        }

        $today = date('Y-m-d');
        $discountedProduct = $this->db
            ->select('p.id')
            ->from('products p')
            ->where('p.id', (int) $productId)
            ->group_start()
                ->where('(COALESCE(p.discount_price, 0) > 0 AND COALESCE(p.price, 0) > COALESCE(p.discount_price, 0))', null, false)
                ->or_where('COALESCE(p.sale_percent, 0) > 0', null, false)
                ->or_where("EXISTS (\n                    SELECT 1\n                    FROM products_variable pv_discount\n                    WHERE pv_discount.product_id = p.id\n                      AND pv_discount.isShown = 1\n                      AND pv_discount.price > 0\n                      AND pv_discount.discount_price > 0\n                      AND pv_discount.discount_price < pv_discount.price\n                )", null, false)
            ->group_end()
            ->get()
            ->row();
        if (!empty($discountedProduct)) {
            return null;
        }

        $activeOfferCountSelect = "(
            SELECT COUNT(DISTINCT o_conflict.id)
            FROM offers o_conflict
            INNER JOIN offers_products op_conflict ON op_conflict.offers_id = o_conflict.id
            WHERE op_conflict.product_id = p.id
              AND o_conflict.isShown = 1
              AND o_conflict.is_online IN (1, 2)
              AND (
                    o_conflict.date_from <= '" . $today . "'
                    OR o_conflict.date_from IS NULL
                    OR o_conflict.date_from = ''
                    OR o_conflict.date_from = '0000-00-00'
              )
              AND (
                    o_conflict.date_to >= '" . $today . "'
                    OR o_conflict.date_to IS NULL
                    OR o_conflict.date_to = ''
                    OR o_conflict.date_to = '0000-00-00'
              )
        ) AS active_offer_count";
        $this->db->select(
            'o.id, o.date_from, o.date_to, '
            . 'o.title' . $lang . ' AS title, '
            . 'o.uri' . $lang . ' AS uri, '
            . 'o.sale, o.is_online, '
            . $this->progressModeSelect('o') . ', '
            . $this->remainingStockSelect('p') . ', '
            . $activeOfferCountSelect
        );
        $this->db->from('offers o');
        $this->db->join('offers_products op', 'op.offers_id = o.id', 'inner');
        $this->db->join('products p', 'p.id = op.product_id', 'inner');
        $this->db->where('op.product_id', (int) $productId);
        $this->db->where('o.isShown', 1);
        $this->db->where('p.isShown', 1);
        if ($this->db->field_exists('isGift', 'products')) {
            $this->db->where('p.isGift', 0);
        }
        $this->db->group_start()
            ->where('p.on_stock >', 0)
            ->or_where("EXISTS (
                SELECT 1
                FROM products_variable pv_offer
                WHERE pv_offer.product_id = p.id
                  AND pv_offer.isShown = 1
                  AND pv_offer.qty > 0
            )", null, false)
            ->group_end();
        $this->db->where_in('o.is_online', array(1, 2));
        $this->db->group_start()
            ->where('o.date_from <=', $today)
            ->or_where('o.date_from IS NULL', null, false)
            ->or_where('o.date_from', '')
            ->or_where('o.date_from', '0000-00-00')
            ->group_end();
        $this->db->group_start()
            ->where('o.date_to >=', $today)
            ->or_where('o.date_to IS NULL', null, false)
            ->or_where('o.date_to', '')
            ->or_where('o.date_to', '0000-00-00')
            ->group_end();
        $this->db->order_by('o.date_to ASC, o.id DESC');

        $offer = $this->db->limit(1)->get()->row();
        if (!empty($offer) && (int) ($offer->active_offer_count ?? 0) > 1) {
            return null;
        }

        return $offer;
    }

    public function products_offers($lang, $products_id)
    {
        $this->db->select("
            id, 
            SKU, 
            category_id, 
            title$lang as title,
            desc$lang as desc,
            uri$lang as uri,
            price,  
            priceWH,
            discount_price, 
            on_stock,
            on_stockWH,
            is_new, 
             (SELECT brands.title FROM brands WHERE brands.id=products.brand_id) as brand_title,
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=products.id LIMIT 1) as img,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=products.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=products.category_id) as cat_title, 
        ");
        $this->db->where_in('id', $products_id);
        $this->db->where('isShown', 1);
        if ($this->db->field_exists('isGift', 'products')) {
            $this->db->where('isGift', 0);
        }
        $this->db->group_start()
            ->where('on_stock >', 0)
            ->or_where("EXISTS (
                SELECT 1
                FROM products_variable pv_offer
                WHERE pv_offer.product_id = products.id
                  AND pv_offer.isShown = 1
                  AND pv_offer.qty > 0
            )", null, false)
            ->group_end();
        return $this->db->get('products')->result();
    }
}
