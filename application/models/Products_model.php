<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends BaseModel
{
    protected $tblname = 'products';
    protected $shop_type = 'shop_type';
    protected $shop_feature = 'shop_feature';
    protected $feature_items = 'shop_feature_values';

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Добавляет условие доступности товара.
     *
     * Обычный товар:
     * - имеет собственную цену.
     *
     * Вариативный товар:
     * - может иметь нулевую цену у родителя;
     * - должен иметь хотя бы один вариант с ценой и остатком.
     */
    private function applySellableCondition()
    {
        if (!empty($_SESSION['isb2b'])) {
            $this->db->where("\n                (\n                    products.priceWH > 0\n                    OR EXISTS (\n                        SELECT 1\n                        FROM products_variable pv\n                        WHERE pv.product_id = products.id\n                          AND pv.priceWH > 0\n                          AND pv.qtyWH > 0\n                    )\n                )\n            ", null, false);

            return;
        }

        $this->db->where("\n            (\n                products.price > 0\n                OR EXISTS (\n                    SELECT 1\n                    FROM products_variable pv\n                    WHERE pv.product_id = products.id\n                      AND pv.price > 0\n                      AND pv.qty > 0\n                )\n            )\n        ", null, false);
    }

    /**
     * Добавляет фильтр диапазона по фактической цене карточки.
     * Для вариативного товара используется минимальная цена доступного варианта.
     */
    private function applyEffectivePriceRange($priceMin, $priceMax)
    {
        $priceMin = max(0, (float) $priceMin);
        $priceMax = max($priceMin, (float) $priceMax);

        if (!empty($_SESSION['isb2b'])) {
            $expression = "\n                CASE\n                    WHEN products.priceWH > 0 THEN products.priceWH\n                    ELSE (\n                        SELECT MIN(pv.priceWH)\n                        FROM products_variable pv\n                        WHERE pv.product_id = products.id\n                          AND pv.priceWH > 0\n                          AND pv.qtyWH > 0\n                    )\n                END\n            ";
        } else {
            $expression = "\n                CASE\n                    WHEN products.price > 0 THEN products.price\n                    ELSE (\n                        SELECT MIN(pv.price)\n                        FROM products_variable pv\n                        WHERE pv.product_id = products.id\n                          AND pv.price > 0\n                          AND pv.qty > 0\n                    )\n                END\n            ";
        }

        $this->db->where("($expression) >= " . $this->db->escape($priceMin), null, false);
        $this->db->where("($expression) <= " . $this->db->escape($priceMax), null, false);
    }
    public function get_product_by_uri_for_pdp(
    $lang,
    $uri
) {
    if (empty($lang)) {
        return false;
    }

    $effectiveFields =
        $this->getEffectiveProductFields();

    $this->db->select("
        id,
        SKU,
        category_id,
        brand_id,

        title$lang AS title,
        h1$lang AS h1_title,
        breadcrumbTitle$lang AS breadcrumb_title,
        desc$lang AS `desc`,
        text$lang AS text,
        uri$lang AS uri,

        instruction$lang AS instruction,
        components$lang AS components,

        uriRO,
        uriRU,
        uriEN,

        seoTitle$lang AS seo_title,
        seoKeywords$lang AS seo_keywords,
        seoDesc$lang AS seo_desc,

        price,
        priceWH,
        discount_price,
        sale_percent,
        barcode,
        sex,
        volume,
        on_stock,
        on_stockWH,
        is_new,
        best_selling,

        {$effectiveFields},

        (
            SELECT brands.title
            FROM brands
            WHERE brands.id = products.brand_id
            LIMIT 1
        ) AS brand_title,

        (
            SELECT brands.text$lang
            FROM brands
            WHERE brands.id = products.brand_id
            LIMIT 1
        ) AS brand_text,

        (
            SELECT brands.uri
            FROM brands
            WHERE brands.id = products.brand_id
            LIMIT 1
        ) AS brand_uri
    ", false);

    /*
    |--------------------------------------------------------------------------
    | PDP lifecycle
    |--------------------------------------------------------------------------
    |
    | Временно закончившийся товар НЕ исчезает.
    |
    | Единственное обязательное условие:
    | товар всё ещё опубликован.
    |
    */

    $this->db->where(
        'products.isShown',
        1
    );

    $this->db->where(
        "uri$lang",
        $uri
    );

    return $this->db
        ->get($this->tblname)
        ->row();
}

public function get_product_by_id_for_quick_view(
    $lang,
    $id
) {
    if (
        empty($lang)
        || (int) $id <= 0
    ) {
        return false;
    }

    $id =
        (int) $id;

    $effectiveFields =
        $this->getEffectiveProductFields();

    $this->db->select("
        products.id,
        products.SKU,
        products.category_id,
        products.brand_id,

        products.title$lang AS title,
        products.h1$lang AS h1_title,
        products.breadcrumbTitle$lang AS breadcrumb_title,
        products.desc$lang AS `desc`,
        products.text$lang AS text,
        products.uri$lang AS uri,

        products.instruction$lang AS instruction,
        products.components$lang AS components,

        products.uriRO,
        products.uriRU,
        products.uriEN,

        products.price,
        products.priceWH,
        products.discount_price,
        products.sale_percent,
        products.barcode,
        products.sex,
        products.volume,
        products.on_stock,
        products.on_stockWH,
        products.is_new,
        products.best_selling,

        {$effectiveFields},

        (
            SELECT brands.title
            FROM brands
            WHERE brands.id = products.brand_id
            LIMIT 1
        ) AS brand_title,

        (
            SELECT brands.uri
            FROM brands
            WHERE brands.id = products.brand_id
            LIMIT 1
        ) AS brand_uri,

        (
            SELECT categories.uri$lang
            FROM categories
            WHERE categories.id = products.category_id
            LIMIT 1
        ) AS cat_uri,

        (
            SELECT categories.title$lang
            FROM categories
            WHERE categories.id = products.category_id
            LIMIT 1
        ) AS cat_title
    ", false);

    /*
    |--------------------------------------------------------------------------
    | Quick View lifecycle
    |--------------------------------------------------------------------------
    |
    | Как и PDP:
    | временно закончившийся товар всё ещё существует.
    |
    | Главное условие — товар опубликован.
    |
    */

    $this->db->where(
        'products.isShown',
        1
    );

    $this->db->where(
        'products.id',
        $id
    );

    return $this->db
        ->get($this->tblname)
        ->row();
}
    /**
     * Проверяет наличие изображения родительского товара.
     */
    private function applyProductImageCondition()
    {
        $this->db->where("\n            EXISTS (\n                SELECT 1\n                FROM products_img pi_exists\n                WHERE pi_exists.product_id = products.id\n            )\n        ", null, false);
    }
    public function inCategory($id = 0)
    {
        if ($id != 0) {
            $this->db->select("
            id as id,
        ");
            $this->db->where('parent_id', $id);
            $category = $this->db->get('categories')->result();
            $categories = array();
            foreach ($category as $cat) {
                $this->db->select(" id as id, ");
                $this->db->where('parent_id', $cat->id);
                $categories[] = $this->db->get('categories')->result();
            }
            if (!empty($categories)) {
                foreach ($categories as $categ) {
                    $category = array_merge($category, $categ);
                }
            }
            if (!empty($category)) {
                $this->db->select("*, (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,");
                $this->db->group_start();
                foreach ($category as $cat) {
                    $this->db->or_where('category_id', $cat->id);
                }
                $this->db->group_end();
            } else {
                $this->db->select("*, (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,");
                $this->db->where('category_id', $id);
            }
        } else {
            $this->db->select("*, (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,");
        }
        return $this;
    }

    public function pagination($limit, $current = 1, $xml_success)
    {

        if (!empty($_GET['brands'])) {
            $this->db->where('brand_id', $_GET['brands']);
        }
        if (!empty($_GET['sorder'])) {
            $this->db->order_by($_GET['sorder'] . ', id DESC');
        } else {
            $this->db->order_by('stock ASC, sorder ASC, id DESC');
        }

        $offset = ($current * $limit) - $limit;
        if (!empty($xml_success)) {
            $query = $this->db->order_by('stock ASC, sorder ASC, id DESC')->from($this->tblname);
        } else {
            $query = $this->db->order_by('stock ASC, sorder ASC, id DESC')->from($this->tblname);
        }

        $total = clone $query;
        $total = $total->count_all_results();


        if (!empty($_GET['brands'])) {
            $this->db->where('brand_id', $_GET['brands']);
        }
        if (!empty($_GET['sorder'])) {
            $this->db->order_by($_GET['sorder'] . ', id DESC');
        } else {
            $this->db->order_by('stock ASC, sorder ASC, id DESC');
        }
        $items = $query->limit($limit)->offset($offset)->get()->result();

        return ['data' => $items, 'count' => $total];
    }

    public function search_get_products_admin($search)
    {
        $this->db->select("*,
         (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,");
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->or_like('titleRU', $search);
            $this->db->or_like('titleRO', $search);
            $this->db->or_like('id', $search);
            $this->db->or_like('SKU', $search);
            $this->db->or_like('barcode', $search);
            $this->db->or_like('search_teg', $search);
            $this->db->group_end();
        }
        if (!empty($_GET['cat'])) {
            $this->db->where('category_id', $_GET['cat']);
        }
        if (!empty($_GET['brands'])) {
            $this->db->where('brand_id', $_GET['brands']);
        }
        if (!empty($_GET['sorder'])) {
            $this->db->order_by($_GET['sorder'] . ', id DESC');
        } else {
            $this->db->order_by('stock ASC, sorder ASC, id DESC');
        }

        $products = $this->db->get($this->tblname)->result();
        return $products;
    }

    public function get_prod_characteristics($feature_type)
    {
        if (empty($feature_type)) return false;

        $this->db->select("
            $this->shop_type.name as type_name,
            
            $this->shop_feature.id as feature_id,
            $this->shop_feature.name_RO as feature_name,
            $this->shop_feature.sorder as feature_sorder,
            $this->shop_feature.type as feature_type,
            $this->shop_feature.free_values as feature_free_values,
            
            $this->feature_items.id as item_id,
            $this->feature_items.feature_id as value_feature_id,
            $this->feature_items.name_RO as item_name,
            $this->feature_items.color as color,
        ");

        $this->db->join("$this->shop_feature", "$this->shop_type.id=$this->shop_feature.type_id");
        $this->db->join("$this->feature_items", "$this->shop_feature.id=$this->feature_items.feature_id");
        $this->db->where("$this->shop_type.id", $feature_type);
        $this->db->order_by("$this->shop_feature.sorder ASC, $this->shop_feature.id DESC");
        $this->db->order_by("$this->feature_items.sorder ASC, $this->feature_items.id DESC");
        return $this->db->get("$this->shop_type")->result();
    }

    public function findFirstCart($id)
    {
        $id = (int)$id;

        $item = $this->db->where('id', $id)->get($this->tblname)->row();
        if (!empty($item)) {
            return $item;
        } else {
            return false;
        }
    }

    public function product_item_cart($lang = false, $rowid)
    {
        $productIdRow = $this->db
            ->select('product_id')
            ->where('rowid', $rowid)
            ->get('shop_cart_items')
            ->row();

        if (empty($productIdRow)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id,
            $this->tblname.SKU,
            $this->tblname.category_id,
            $this->tblname.brand_id,
            $this->tblname.title$lang AS title,
            $this->tblname.desc$lang AS `desc`,
            $this->tblname.uri$lang AS uri,
            $this->tblname.price,
            $this->tblname.priceWH,
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,
            $this->tblname.on_stockWH,
            $this->tblname.volume,
            $this->tblname.stock,
            $this->tblname.is_new,

            {$effectiveFields},

            (
                SELECT brands.title
                FROM brands
                WHERE brands.id = $this->tblname.brand_id
                LIMIT 1
            ) AS brand_title,

            (
                SELECT products_img.img
                FROM products_img
                WHERE products_img.product_id = $this->tblname.id
                ORDER BY products_img.sorder ASC, products_img.id DESC
                LIMIT 1
            ) AS img,

            (
                SELECT categories.uri$lang
                FROM categories
                WHERE categories.id = $this->tblname.category_id
                LIMIT 1
            ) AS cat_uri,

            (
                SELECT categories.title$lang
                FROM categories
                WHERE categories.id = $this->tblname.category_id
                LIMIT 1
            ) AS cat_title
        ", false);

        $this->db->where($this->tblname . '.isShown', 1);
        $this->db->where($this->tblname . '.id', (int) $productIdRow->product_id);

        $this->applySellableCondition();
        $this->applyProductImageCondition();

        return $this->db
            ->get($this->tblname)
            ->row();
    }

    public function product_alt($sku)
    {
        if (empty($sku)) {
            return false;
        }
        $this->db->select("
            id as id,
            SKU as SKU,
            titleRU as title,
        ");
        $this->db->where("$this->tblname.isShown", 1);
       $this->applySellableCondition();

        $this->db->where('SKU', $sku);
        return $this->db->get($this->tblname)->row();
    }

    public function get_product_img($id)
    {
        $this->db->select("img");
        $this->db->where('product_id', $id);
        $this->db->order_by('sorder ASC, id ASC');
        return $this->db->get('products_img')->result();
    }

public function get_product_variable(
    $lang = false,
    $id,
    $includeOutOfStock = false
) {
    if (empty($lang)) {
        return array();
    }

    $this->db->select("
        id,
        SKU,
        title$lang AS title,
        color$lang AS color,
        color_data,
        VolumeVar,
        price,
        qtyWH,
        priceWH,
        discount_price,
        qty
    ");

    $this->db->where(
        'product_id',
        (int) $id
    );

    $this->db->where(
        'isShown',
        1
    );

    /*
    |--------------------------------------------------------------------------
    | Price
    |--------------------------------------------------------------------------
    |
    | Даже OOS-вариант должен иметь реальную цену,
    | иначе это уже не полноценный торговый вариант.
    |
    */

    if (!empty($_SESSION['isb2b'])) {
        $this->db->where(
            'priceWH >',
            0
        );

        if (!$includeOutOfStock) {
            $this->db->where(
                'qtyWH >',
                0
            );
        }
    } else {
        $this->db->where(
            'price >',
            0
        );

        if (!$includeOutOfStock) {
            $this->db->where(
                'qty >',
                0
            );
        }
    }

    $this->db->order_by(
        'sorder ASC, id ASC'
    );

    $productsVariable =
        $this->db
            ->get('products_variable')
            ->result();

    foreach (
        $productsVariable
        as $key => $value
    ) {
        $value->color_data =
            $this->decodeVariantColorData(
                isset($value->color_data)
                    ? $value->color_data
                    : null
            );

        $images =
            $this->db
                ->where(
                    'variable_id',
                    (int) $value->id
                )
                ->order_by(
                    'sorder ASC, id ASC'
                )
                ->get(
                    'products_variable_img'
                )
                ->result();

        /*
         * Пока сохраняем текущую архитектуру:
         * вариант без изображения не показываем.
         */
        if (empty($images)) {
            unset(
                $productsVariable[$key]
            );

            continue;
        }

        $value->img =
            $images;
    }

    return array_values(
        $productsVariable
    );
}

    public function get_product_variable_by_id(
    $lang = false,
    $id,
    $product_id
) {
    if (empty($lang)) {
        return false;
    }

    $this->db->select("
        id,
        SKU,
        title$lang AS title,
        price,
        priceWH,
        discount_price,
        qty,
        qtyWH,
        color$lang AS color,
        color_data,
        VolumeVar
    ");

    $this->db->where(
        'id',
        (int) $id
    );

    $this->db->where(
        'product_id',
        (int) $product_id
    );

    $productVariable = $this->db
        ->get('products_variable')
        ->row();

    if (empty($productVariable)) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Visual colors
    |--------------------------------------------------------------------------
    */

    $productVariable->color_data =
        $this->decodeVariantColorData(
            isset($productVariable->color_data)
                ? $productVariable->color_data
                : null
        );

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */

    $productVariable->img =
        $this->db
            ->where(
                'variable_id',
                (int) $productVariable->id
            )
            ->order_by(
                'sorder ASC, id ASC'
            )
            ->get(
                'products_variable_img'
            )
            ->result();

    return $productVariable;
}
private function decodeVariantColorData($colorData)
{
    if (empty($colorData)) {
        return array();
    }

    /*
     * На случай, если значение уже было
     * декодировано раньше.
     */
    if (is_array($colorData)) {
        if (
            isset($colorData['colors'])
            && is_array($colorData['colors'])
        ) {
            $colors =
                $colorData['colors'];
        } else {
            $colors =
                $colorData;
        }
    } else {
        $decoded = json_decode(
            (string) $colorData,
            true
        );

        if (
            !is_array($decoded)
            || empty($decoded['colors'])
            || !is_array($decoded['colors'])
        ) {
            return array();
        }

        $colors =
            $decoded['colors'];
    }

    $normalized = array();

    foreach ($colors as $color) {
        $color = strtoupper(
            trim(
                (string) $color
            )
        );

        if (
            !preg_match(
                '/^#[0-9A-F]{6}$/',
                $color
            )
        ) {
            continue;
        }

        if (
            !in_array(
                $color,
                $normalized,
                true
            )
        ) {
            $normalized[] = $color;
        }

        if (
            count($normalized) >= 6
        ) {
            break;
        }
    }

    return $normalized;
}
    public function get_product_by_uri($lang, $uri)
    {
        if (empty($lang)) {
            return false;
        }
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            id,
            SKU,
            category_id,
            brand_id,

            title$lang as title,
            h1$lang as h1_title,
            breadcrumbTitle$lang as breadcrumb_title,
            desc$lang as `desc`,
            text$lang as text,
            uri$lang as uri,

            instruction$lang as instruction,
            components$lang as components,

            uriRO,
            uriRU,
            uriEN,

            seoTitle$lang as seo_title,
            seoKeywords$lang as seo_keywords,
            seoDesc$lang as seo_desc,

            price,
            priceWH,
            discount_price,
            sale_percent,
            barcode,
            sex,
            volume,
            on_stock,
            on_stockWH,
            is_new,

            {$effectiveFields},

            (
                SELECT brands.title
                FROM brands
                WHERE brands.id = products.brand_id
            ) AS brand_title,

            (
                SELECT brands.text$lang
                FROM brands
                WHERE brands.id = products.brand_id
            ) AS brand_text,

            (
                SELECT brands.uri
                FROM brands
                WHERE brands.id = products.brand_id
            ) AS brand_uri
        ", false);
        $this->db->where("$this->tblname.isShown", 1);
        $this->applySellableCondition();

        $this->db->where_in("uri$lang", $uri);
        $product = $this->db->get($this->tblname)->row();
        return $product;
    }

    public function get_product_features($lang, $id)
    {
        if (empty($lang)) {
            return false;
        }
        $this->db->select("
            products_features.id as id,
            products_features.product_id as product_id,
            products_features.feature_id as feature_id,
            products_features.feature_value_id as feature_value_id,
            shop_feature.name_$lang as feature_name,
            (SELECT shop_feature_values.name_$lang FROM shop_feature_values WHERE shop_feature_values.id=products_features.feature_value_id LIMIT 1) as feature_value_name,
        ");
        $this->db->join('shop_feature', 'shop_feature.id=products_features.feature_id');
        $this->db->where('products_features.product_id', $id);
        $this->db->order_by("shop_feature.sorder ASC, shop_feature.id ASC");
        $this->db->group_by("products_features.feature_value_id");
        return $this->db->get('products_features')->result();
    }
    public function get_similar_products(
    $lang,
    $product,
    $limit = 10
) {
    if (
        empty($lang)
        || empty($product)
        || empty($product->id)
    ) {
        return array();
    }

    $productId = (int) $product->id;
    $categoryId = (int) ($product->category_id ?? 0);
    $brandId = (int) ($product->brand_id ?? 0);
    $sex = isset($product->sex)
        ? (int) $product->sex
        : 0;

    $sourcePrice = !empty($product->effective_price)
        ? (float) $product->effective_price
        : (float) ($product->price ?? 0);

    $limit = max(
        1,
        min(
            10,
            (int) $limit
        )
    );

    /*
    |--------------------------------------------------------------------------
    | Effective candidate price
    |--------------------------------------------------------------------------
    */

    if (!empty($_SESSION['isb2b'])) {
        $candidatePrice = "
            CASE
                WHEN products.priceWH > 0
                    THEN products.priceWH

                ELSE (
                    SELECT MIN(pv.priceWH)
                    FROM products_variable pv
                    WHERE pv.product_id = products.id
                      AND pv.priceWH > 0
                      AND pv.qtyWH > 0
                )
            END
        ";
    } else {
        $candidatePrice = "
            CASE
                WHEN products.price > 0
                    THEN products.price

                ELSE (
                    SELECT MIN(pv.price)
                    FROM products_variable pv
                    WHERE pv.product_id = products.id
                      AND pv.price > 0
                      AND pv.qty > 0
                )
            END
        ";
    }

    /*
    |--------------------------------------------------------------------------
    | Recommendation score
    |--------------------------------------------------------------------------
    |
    | Главный сигнал — категория.
    | Бренд помогает, но не может перебить назначение товара.
    |
    */

    $scoreParts = array();

    if ($categoryId > 0) {
        $scoreParts[] = "
            CASE
                WHEN products.category_id = {$categoryId}
                    THEN 100
                ELSE 0
            END
        ";
    }

    if ($brandId > 0) {
        $scoreParts[] = "
            CASE
                WHEN products.brand_id = {$brandId}
                    THEN 35
                ELSE 0
            END
        ";
    }

    if ($sex > 0) {
        $scoreParts[] = "
            CASE
                WHEN products.sex = {$sex}
                    THEN 15
                ELSE 0
            END
        ";
    }

    if ($sourcePrice > 0) {
        $price20Min = $sourcePrice * 0.80;
        $price20Max = $sourcePrice * 1.20;

        $price40Min = $sourcePrice * 0.60;
        $price40Max = $sourcePrice * 1.40;

        $scoreParts[] = "
            CASE
                WHEN ({$candidatePrice})
                    BETWEEN {$price20Min}
                    AND {$price20Max}
                    THEN 30

                WHEN ({$candidatePrice})
                    BETWEEN {$price40Min}
                    AND {$price40Max}
                    THEN 18

                ELSE 0
            END
        ";
    }

    $scoreParts[] = "
        CASE
            WHEN products.best_selling = 1
                THEN 6
            ELSE 0
        END
    ";

    $scoreParts[] = "
        CASE
            WHEN products.is_new = 1
                THEN 3
            ELSE 0
        END
    ";

    $scoreExpression = implode(
        ' + ',
        $scoreParts
    );

    $effectiveFields =
        $this->getEffectiveProductFields();

    /*
    |--------------------------------------------------------------------------
    | Card fields
    |--------------------------------------------------------------------------
    */

    $this->db->select("
        products.id,
        products.SKU,
        products.category_id,
        products.brand_id,
        products.sex,

        products.title$lang AS title,
        products.desc$lang AS `desc`,
        products.uri$lang AS uri,

        products.price,
        products.priceWH,
        products.discount_price,
        products.sale_percent,

        products.on_stock,
        products.on_stockWH,
        products.volume,
        products.stock,
        products.is_new,
        products.best_selling,

        {$effectiveFields},

        ({$scoreExpression}) AS similarity_score,

        (
            SELECT pi.img
            FROM products_img pi
            WHERE pi.product_id = products.id
            ORDER BY
                pi.sorder ASC,
                pi.id DESC
            LIMIT 1
        ) AS img,

        (
            SELECT b.title
            FROM brands b
            WHERE b.id = products.brand_id
            LIMIT 1
        ) AS brand_title,

        (
            SELECT c.uri$lang
            FROM categories c
            WHERE c.id = products.category_id
            LIMIT 1
        ) AS cat_uri,

        (
            SELECT c.title$lang
            FROM categories c
            WHERE c.id = products.category_id
            LIMIT 1
        ) AS cat_title
    ", false);

    /*
    |--------------------------------------------------------------------------
    | Hard filters
    |--------------------------------------------------------------------------
    */

    $this->db->where(
        'products.isShown',
        1
    );

    $this->db->where(
        'products.id !=',
        $productId
    );

    /*
     * В рекомендациях показываем только то,
     * что реально можно купить.
     */
    $this->applyProductImageCondition();

/*
|--------------------------------------------------------------------------
| Similar products must actually be purchasable
|--------------------------------------------------------------------------
|
| Для рекомендаций недостаточно наличия цены.
|
| Обычный товар:
|   price > 0 AND on_stock > 0
|
| Вариативный:
|   существует хотя бы один вариант
|   с price > 0 AND qty > 0.
|
*/

if (!empty($_SESSION['isb2b'])) {
    $this->db->group_start();

    $this->db->group_start();

    $this->db->where(
        'products.priceWH >',
        0
    );

    $this->db->where(
        'products.on_stockWH >',
        0
    );

    $this->db->group_end();

    $this->db->or_where(
        "
        EXISTS (
            SELECT 1
            FROM products_variable pv
            WHERE pv.product_id = products.id
              AND pv.isShown = 1
              AND pv.priceWH > 0
              AND pv.qtyWH > 0
        )
        ",
        null,
        false
    );

    $this->db->group_end();
} else {
    $this->db->group_start();

    /*
     * Обычный товар.
     */
    $this->db->group_start();

    $this->db->where(
        'products.price >',
        0
    );

    $this->db->where(
        'products.on_stock >',
        0
    );

    $this->db->group_end();

    /*
     * Вариативный товар.
     */
    $this->db->or_where(
        "
        EXISTS (
            SELECT 1
            FROM products_variable pv
            WHERE pv.product_id = products.id
              AND pv.isShown = 1
              AND pv.price > 0
              AND pv.qty > 0
        )
        ",
        null,
        false
    );

    $this->db->group_end();
}
    /*
    |--------------------------------------------------------------------------
    | Ranking
    |--------------------------------------------------------------------------
    */

    $this->db->order_by(
        'similarity_score DESC',
        '',
        false
    );

    $this->db->order_by(
        'effective_stock DESC',
        '',
        false
    );

    $this->db->order_by(
        'products.best_selling DESC'
    );

    $this->db->order_by(
        'products.sorder ASC'
    );

    $this->db->order_by(
        'products.id DESC'
    );

    $this->db->limit(
        $limit
    );

    return $this->db
        ->get($this->tblname)
        ->result();
}
    public function get_products_related($lang, $id)
    {
        if (empty($lang)) {
            return false;
        }

        $products_related = array();
        $products_related_result = $this->db->where('product_id', $id)->get('products_related')->result();
        if (!empty($products_related_result)) {
            foreach ($products_related_result as $products_related_row) {
                $products_related[$products_related_row->related_id] = $products_related_row->related_id;
            }
        }

        if (empty($products_related)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->applySellableCondition();

        $this->db->where_in('products.id', $products_related);
        $this->db->offset(0);
        $this->db->limit(10);

        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_products_more($lang, $id)
    {
        if (empty($lang)) {
            return false;
        }

        $more_products = array();
        $more_products_result = $this->db->where('product_id', $id)->get('products_more')->result();
        if (!empty($more_products_result)) {
            foreach ($more_products_result as $more_products_row) {
                $more_products[$more_products_row->related_id] = $more_products_row->related_id;
            }
        }

        if (empty($more_products)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);

        $this->applySellableCondition();

        $this->db->where_in('products.id', $more_products);
        $this->db->offset(0);
        $this->db->limit(10);

        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_products_category($lang, $id)
    {
        if (empty($lang)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->applySellableCondition();

        $this->db->where('products.category_id', $id);
        $this->db->offset(0);
        $this->db->limit(10);

        $this->db->order_by('', 'RANDOM');

        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_products_brand($lang, $id)
    {
        if (empty($lang)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");

        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->applySellableCondition();

        $this->db->where('products.brand_id', $id);
        $this->db->offset(0);
        $this->db->limit(10);
        $this->db->order_by('', 'RANDOM');
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_products_views(
    $lang = false,
    $prod_ids = array(),
    $limit = 10
    ) {
        if (
            empty($lang)
            || empty($prod_ids)
            || !is_array($prod_ids)
        ) {
            return array();
        }

        $productIds = array();

        foreach ($prod_ids as $productId) {
            $productId = (int) $productId;

            if (
                $productId <= 0
                || in_array(
                    $productId,
                    $productIds,
                    true
                )
            ) {
                continue;
            }

            $productIds[] = $productId;

            if (count($productIds) >= 20) {
                break;
            }
        }

        if (empty($productIds)) {
            return array();
        }

        $limit = max(
            1,
            min(
                10,
                (int) $limit
            )
        );

        $effectiveFields =
            $this->getEffectiveProductFields();

        $this->db->select("
            products.id,
            products.SKU,
            products.category_id,

            products.title$lang AS title,
            products.desc$lang AS `desc`,
            products.uri$lang AS uri,

            products.price,
            products.priceWH,
            products.discount_price,
            products.sale_percent,

            products.on_stock,
            products.on_stockWH,
            products.volume,
            products.stock,
            products.is_new,

            {$effectiveFields},

            (
                SELECT pi.img
                FROM products_img pi
                WHERE pi.product_id = products.id
                ORDER BY
                    pi.sorder ASC,
                    pi.id DESC
                LIMIT 1
            ) AS img,

            (
                SELECT b.title
                FROM brands b
                WHERE b.id = products.brand_id
                LIMIT 1
            ) AS brand_title,

            (
                SELECT c.uri$lang
                FROM categories c
                WHERE c.id = products.category_id
                LIMIT 1
            ) AS cat_uri,

            (
                SELECT c.title$lang
                FROM categories c
                WHERE c.id = products.category_id
                LIMIT 1
            ) AS cat_title
        ", false);

        $this->db->where(
            'products.isShown',
            1
        );

        $this->db->where_in(
            'products.id',
            $productIds
        );

        $this->applyProductImageCondition();
        $this->applySellableCondition();

        $orderIds = implode(
            ',',
            array_map(
                'intval',
                $productIds
            )
        );

        $this->db->order_by(
            "FIELD(products.id, {$orderIds})",
            '',
            false
        );

        $this->db->limit($limit);

        return $this->db
            ->get($this->tblname)
            ->result();
    }

    public function get_products_home_new($lang)
    {
        if (empty($lang)) {
            return false;
        }
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
             
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where('products.is_new', 1);
        $this->applySellableCondition();

        $this->db->offset(0);
        $this->db->limit(10);

        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_products_home_sale($lang)
    {
        if (empty($lang)) {
            return false;
        }
        $effectiveFields = $this->getEffectiveProductFields();


        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
       $this->applySellableCondition();

        $this->db->where('products.discount_price >', 1);
        $this->db->offset(0);
        $this->db->limit(10);

        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_products_home_order($lang)
    {
        if (empty($lang)) {
            return false;
        }
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->applySellableCondition();

        $this->db->where('products.best_selling', 1);
        $this->db->offset(0);
        $this->db->limit(10);

        $this->db->order_by("products.order DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_search_products($lang, $search)
    {
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->group_start();
        $this->db->or_like('products.titleRU', $search);
        $this->db->or_like('products.titleRO', $search);
        $this->db->or_like('products.SKU', $search);
        $this->db->or_like('products.barcode', $search);
        $this->db->or_like('products.search_teg', $search);
        $this->db->group_end();
        $this->applySellableCondition();

        $this->db->where("$this->tblname.isShown", 1);
        $this->db->offset(0);
        $this->db->limit(15);

        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        return $this->db->get($this->tblname)->result();
    }

    public function get_search_productsID($lang, $productsID)
    {
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where_in('products.id', $productsID);
       $this->applySellableCondition();

        $this->db->where("$this->tblname.isShown", 1);
        $this->db->offset(0);
        $this->db->limit(15);

        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        return $this->db->get($this->tblname)->result();
    }

    public function get_wishlist_products($ids)
    {
        $this->db->select("$this->tblname.id,$this->tblname.category_id,$this->tblname.brand_id");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where_in('products.id', $ids);
        $this->applySellableCondition();

        $this->db->where("$this->tblname.isShown", 1);
        $this->db->group_by("products.id");
        //        $this->db->where('on_stock >', 0);
        return $this->db->get($this->tblname)->result();
    }

    public function get_products_wishlist($lang, $ids, $offset, $limit, $sort = '')
    {
        if (empty($lang)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
       $this->applySellableCondition();

        $this->db->where_in('products.id', $ids);
        $this->db->offset($offset);
        $this->db->limit($limit);

        if (!empty($_GET['sort'])) {
            if ($_GET['sort'] == '1') {
                $this->db->order_by("products.stock DESC, products.best_selling DESC, products.id ASC");
            } elseif ($_GET['sort'] == '2') {
                $this->db->order_by("products.stock DESC, products.price ASC, products.id ASC");
            } elseif ($_GET['sort'] == '3') {
                $this->db->order_by("products.stock DESC, products.price DESC, products.id ASC");
            } elseif ($_GET['sort'] == '4') {
                $this->db->order_by("products.stock DESC, products.sale_mdl DESC, products.id ASC");
            } else {
                $this->db->order_by("products.stock DESC, products.title ASC, products.id ASC");
            }
        } else {
            $this->db->order_by("products.stock DESC, products.sorder ASC, products.id ASC");
        }
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_all_products_search($search)
    {
        $this->db->select("products.id");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->group_start();
        $this->db->or_like('products.titleRU', $search);
        $this->db->or_like('products.titleRO', $search);
        $this->db->or_like('products.SKU', $search);
        $this->db->or_like('products.barcode', $search);
        $this->db->or_like('products.search_teg', $search);
        $this->db->group_end();
        $this->db->where("$this->tblname.isShown", 1);
       $this->applySellableCondition();

        $this->db->group_by("products.id");
        return $this->db->get($this->tblname)->result();
    }

    public function get_products_pag_search($lang, $offset, $limit, $search, $sort = '')
    {
        if (empty($lang)) {
            return false;
        }
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->group_start();
        $this->db->or_like('products.titleRU', $search);
        $this->db->or_like('products.titleRO', $search);
        $this->db->or_like('products.SKU', $search);
        $this->db->or_like('products.barcode', $search);
        $this->db->or_like('products.search_teg', $search);
        $this->db->group_end();
       $this->applySellableCondition();

        $this->db->where("$this->tblname.isShown", 1);
        $this->db->offset($offset);
        $this->db->limit($limit);

        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_all_products()
    {
        $this->db->select('products.id');

        $this->db->where('products.isShown', 1);

        $this->db->where("
            EXISTS (
                SELECT 1
                FROM products_img pi
                WHERE pi.product_id = products.id
            )
        ", null, false);

        $this->applySellableCondition();

        return $this->db
            ->get($this->tblname)
            ->result();
    }

    public function get_products_pag($lang, $offset, $limit, $sort = '')
    {
        if (empty($lang)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            products.id,
            products.SKU,
            products.category_id,
            products.title$lang AS title,
            products.desc$lang AS `desc`,
            products.uri$lang AS uri,

            products.price,
            products.priceWH,
            products.discount_price,
            products.sale_percent,

            products.on_stock,
            products.on_stockWH,
            products.volume,
            products.stock,
            products.is_new,

            {$effectiveFields},

            (
                SELECT pi.img
                FROM products_img pi
                WHERE pi.product_id = products.id
                ORDER BY pi.sorder ASC, pi.id DESC
                LIMIT 1
            ) AS img,

            (
                SELECT b.title
                FROM brands b
                WHERE b.id = products.brand_id
                LIMIT 1
            ) AS brand_title,

            (
                SELECT c.uri$lang
                FROM categories c
                WHERE c.id = products.category_id
                LIMIT 1
            ) AS cat_uri,

            (
                SELECT c.title$lang
                FROM categories c
                WHERE c.id = products.category_id
                LIMIT 1
            ) AS cat_title
        ", false);

        $this->db->where('products.isShown', 1);

        $this->db->where("
            EXISTS (
                SELECT 1
                FROM products_img pi
                WHERE pi.product_id = products.id
            )
        ", null, false);

        $this->applySellableCondition();

        $this->db->order_by("
            effective_stock DESC,
            products.sorder ASC,
            products.id DESC
        ", '', false);

        $this->db->limit((int) $limit, (int) $offset);

        return $this->db
            ->get($this->tblname)
            ->result();
    }

    public function get_all_products_new_products()
    {
        $this->db->select("products.id");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where('products.is_new', 1);
        $this->applySellableCondition();

        $this->db->group_by("products.id");
        return $this->db->get($this->tblname)->result();
    }

    public function get_products_pag_new_products($lang, $offset, $limit, $sort = '')
    {
        if (empty($lang)) {
            return false;
        }
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where('products.is_new', 1);
       $this->applySellableCondition();

        $this->db->offset($offset);
        $this->db->limit($limit);

        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_all_products_best_sellers()
    {
        $this->db->select('
            products.id,
            products.brand_id,
            products.price,
            products.category_id
        ');

        $this->db->from('products');

        $this->db->where('products.isShown', 1);

        if (isset($_GET['price_min'], $_GET['price_max'])) {
            $this->applyEffectivePriceRange($_GET['price_min'], $_GET['price_max']);
        } else {
            $this->applySellableCondition();
        }

        if (!empty($_GET['category']) && is_array($_GET['category'])) {
            $categoryIds = array_map('intval', $_GET['category']);
            $this->db->where_in('products.category_id', $categoryIds);
        }

        if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
            $brandIds = array_map('intval', $_GET['brand']);
            $this->db->where_in('products.brand_id', $brandIds);
        }

        if (!empty($_GET['sex']) && is_array($_GET['sex'])) {
            $sexValues = array_map('intval', $_GET['sex']);
            $this->db->where_in('products.sex', $sexValues);
        }

        $this->db->where('products.discount_price >', 0);

        $this->db->where(
            'EXISTS (
        SELECT 1
        FROM products_img
        WHERE products_img.product_id = products.id
    )',
            null,
            false
        );

        return $this->db->get()->result();
    }

    public function get_all_products_hits()
    {
        $this->db->select("products.id");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
       $this->applySellableCondition();
        $this->db->where('products.best_selling', 1);
        $this->db->group_by("products.id");
        return $this->db->get($this->tblname)->result();
    }

    public function get_products_pag_best_sellers($lang, $offset, $limit)
    {
        if (empty($lang)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
    $this->tblname.id,
    $this->tblname.SKU,
    $this->tblname.category_id,
    $this->tblname.title$lang AS title,
    $this->tblname.desc$lang AS `desc`,
    $this->tblname.uri$lang AS uri,
    $this->tblname.price,
    $this->tblname.priceWH,
    $this->tblname.discount_price,
    $this->tblname.sale_percent,
    $this->tblname.on_stock,
    $this->tblname.on_stockWH,
    $this->tblname.volume,
    $this->tblname.stock,
    $this->tblname.is_new,

    {$effectiveFields},
    (
        SELECT pi.img
        FROM products_img pi
        WHERE pi.product_id = $this->tblname.id
        ORDER BY pi.sorder ASC, pi.id DESC
        LIMIT 1
    ) AS img,

    (
        SELECT b.title
        FROM brands b
        WHERE b.id = $this->tblname.brand_id
        LIMIT 1
    ) AS brand_title,

    (
        SELECT c.uri$lang
        FROM categories c
        WHERE c.id = $this->tblname.category_id
        LIMIT 1
    ) AS cat_uri,

    (
        SELECT c.title$lang
        FROM categories c
        WHERE c.id = $this->tblname.category_id
        LIMIT 1
    ) AS cat_title
", false);

        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where("$this->tblname.discount_price >", 0);

        /*
 * Exclude produsele care nu au imagini.
 * Nu mai este necesar JOIN sau GROUP BY.
 */
        $this->db->where("
    EXISTS (
        SELECT 1
        FROM products_img pi_exists
        WHERE pi_exists.product_id = $this->tblname.id
    )
", null, false);

        if (isset($_GET['price_min'], $_GET['price_max'])) {
            $this->applyEffectivePriceRange($_GET['price_min'], $_GET['price_max']);
        } else {
            $this->applySellableCondition();
        }

        if (!empty($_GET['category']) && is_array($_GET['category'])) {
            $categories = array_map('intval', $_GET['category']);

            if (!empty($categories)) {
                $this->db->where_in(
                    "$this->tblname.category_id",
                    $categories
                );
            }
        }

        if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
            $brands = array_map('intval', $_GET['brand']);

            if (!empty($brands)) {
                $this->db->where_in(
                    "$this->tblname.brand_id",
                    $brands
                );
            }
        }

        if (!empty($_GET['sex']) && is_array($_GET['sex'])) {
            $sex = array_map('intval', $_GET['sex']);

            if (!empty($sex)) {
                $this->db->where_in(
                    "$this->tblname.sex",
                    $sex
                );
            }
        }

        if (!empty($_GET['sort'])) {
            switch ((string) $_GET['sort']) {
                case '1':
                    $this->db->order_by(
                        "$this->tblname.stock DESC,
                 $this->tblname.best_selling DESC,
                 $this->tblname.id ASC"
                    );
                    break;

                case '2':
                    $this->db->order_by(
                        "$this->tblname.stock DESC,
                 $this->tblname.price ASC,
                 $this->tblname.id ASC"
                    );
                    break;

                case '3':
                    $this->db->order_by(
                        "$this->tblname.stock DESC,
                 $this->tblname.price DESC,
                 $this->tblname.id ASC"
                    );
                    break;

                case '4':
                    $this->db->order_by(
                        "$this->tblname.stock DESC,
                 $this->tblname.sale_mdl DESC,
                 $this->tblname.id ASC"
                    );
                    break;

                default:
                    $this->db->order_by(
                        "$this->tblname.stock DESC,
                 $this->tblname.title$lang ASC,
                 $this->tblname.sorder ASC,
                 $this->tblname.id DESC"
                    );
                    break;
            }
        } else {
            $this->db->order_by(
                "$this->tblname.stock DESC,
         $this->tblname.sorder ASC,
         $this->tblname.id DESC"
            );
        }

        $this->db->limit((int) $limit, (int) $offset);

        $products = $this->db
            ->get($this->tblname)
            ->result();
        return $products;
    }

    public function get_products_pag_hits($lang, $offset, $limit)
    {
        if (empty($lang)) {
            return false;
        }
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id,
            $this->tblname.SKU,
            $this->tblname.category_id,
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,
            $this->tblname.priceWH,
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,
            $this->tblname.on_stockWH,
            $this->tblname.volume,
            $this->tblname.stock,
            $this->tblname.is_new,
            $this->tblname.best_selling,
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where('products.best_selling', 1);
        $this->applySellableCondition();
        $this->db->offset($offset);
        $this->db->limit($limit);
        $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        $this->db->group_by("products.id");
        return $this->db->get($this->tblname)->result();
    }

    public function get_all_products_offers($offers)
    {
        $offers_id = array();

        $offers = $this->db->select('product_id')->where('offers_id', $offers)->get('offers_products')->result_array();
        if (!empty($offers)) {
            foreach ($offers as $id) {
                $offers_id[] = $id['product_id'];
            }
        }

        if (!empty($offers_id)) {
            $this->db->select("products.id,products.category_id,products.brand_id");
            $this->db->join('products_img', 'products_img.product_id = products.id');
            $this->db->where_in('products.id', $offers_id);

        if (isset($_GET['price_min'], $_GET['price_max'])) {
            $this->applyEffectivePriceRange($_GET['price_min'], $_GET['price_max']);
        } else {
            $this->applySellableCondition();
        }
            if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
                $this->db->where_in('products.brand_id', $_GET['brand']);
            }

            $this->db->where("$this->tblname.isShown", 1);
            $this->db->group_by("products.id");
            return $this->db->get($this->tblname)->result();
        } else {
            return array();
        }
    }

    public function get_products_offers_pag($lang, $offset, $limit, $offers, $sort = '')
    {
        if (empty($lang)) {
            return false;
        }

        $offersId = (int) $offers;
        $offerProductRows = $this->db
            ->select('product_id')
            ->where('offers_id', $offersId)
            ->get('offers_products')
            ->result_array();

        if (empty($offerProductRows)) {
            return array();
        }

        $productIds = array_values(array_filter(array_map(
            static function ($row) {
                return isset($row['product_id']) ? (int) $row['product_id'] : 0;
            },
            $offerProductRows
        )));

        if (empty($productIds)) {
            return array();
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("\n            $this->tblname.id,\n            $this->tblname.SKU,\n            $this->tblname.category_id,\n            $this->tblname.title$lang AS title,\n            $this->tblname.desc$lang AS `desc`,\n            $this->tblname.uri$lang AS uri,\n            $this->tblname.price,\n            $this->tblname.priceWH,\n            $this->tblname.discount_price,\n            $this->tblname.sale_percent,\n            $this->tblname.on_stock,\n            $this->tblname.on_stockWH,\n            $this->tblname.volume,\n            $this->tblname.stock,\n            $this->tblname.is_new,\n\n            {$effectiveFields},\n\n            (\n                SELECT products_img.img\n                FROM products_img\n                WHERE products_img.product_id = $this->tblname.id\n                ORDER BY products_img.sorder ASC, products_img.id DESC\n                LIMIT 1\n            ) AS img,\n\n            (\n                SELECT brands.title\n                FROM brands\n                WHERE brands.id = $this->tblname.brand_id\n                LIMIT 1\n            ) AS brand_title,\n\n            (\n                SELECT categories.uri$lang\n                FROM categories\n                WHERE categories.id = $this->tblname.category_id\n                LIMIT 1\n            ) AS cat_uri,\n\n            (\n                SELECT categories.title$lang\n                FROM categories\n                WHERE categories.id = $this->tblname.category_id\n                LIMIT 1\n            ) AS cat_title\n        ", false);

        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where_in("$this->tblname.id", $productIds);
        $this->applyProductImageCondition();

        if (isset($_GET['price_min'], $_GET['price_max'])) {
            $this->applyEffectivePriceRange($_GET['price_min'], $_GET['price_max']);
        } else {
            $this->applySellableCondition();
        }

        if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
            $brandIds = array_values(array_filter(array_map('intval', $_GET['brand'])));

            if (!empty($brandIds)) {
                $this->db->where_in("$this->tblname.brand_id", $brandIds);
            }
        }

        switch ((string) ($_GET['sort'] ?? $sort)) {
            case '1':
                $this->db->order_by("$this->tblname.stock DESC, $this->tblname.best_selling DESC, $this->tblname.id ASC");
                break;
            case '2':
                $this->db->order_by('effective_price ASC, ' . $this->tblname . '.id ASC', '', false);
                break;
            case '3':
                $this->db->order_by('effective_price DESC, ' . $this->tblname . '.id ASC', '', false);
                break;
            case '4':
                $this->db->order_by("$this->tblname.stock DESC, $this->tblname.sale_mdl DESC, $this->tblname.id ASC");
                break;
            default:
                $this->db->order_by("$this->tblname.stock DESC, $this->tblname.sorder ASC, $this->tblname.id ASC");
                break;
        }

        $this->db->limit((int) $limit, (int) $offset);

        return $this->db
            ->get($this->tblname)
            ->result();
    }

    public function get_all_category_products($categories_id = array())
    {
        $this->db->select("products.id, products.brand_id");
        $this->db->from($this->tblname); // products
        $this->db->join('products_img', 'products_img.product_id = products.id');

        // JOIN на таблицу product_categories
        if (!empty($categories_id)) {
            $this->db->join('product_categories pc', 'pc.product_id = products.id', 'inner');
            $this->db->where_in('pc.category_id', $categories_id);
        }

        $this->db->where("$this->tblname.isShown", 1);

        // фильтр по цене

        if (isset($_GET['price_min'], $_GET['price_max'])) {
            $this->applyEffectivePriceRange($_GET['price_min'], $_GET['price_max']);
        } else {
            $this->applySellableCondition();
        }

        // фильтр по брендам
        if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
            $this->db->where_in('products.brand_id', $_GET['brand']);
        }

        $this->db->group_by("products.id");

        return $this->db->get()->result();
    }
    public function get_category_products_pag($lang, $categories_id = array(), $offset, $limit, $sort = '')
    {
        if (empty($lang)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
        products.id, 
        products.SKU, 
        products.title$lang as title,
        products.desc$lang as `desc`,
        products.uri$lang as uri,
        products.price,  
        products.priceWH,  
        products.discount_price,
        products.on_stock,  
        products.on_stockWH,  
        products.volume, 
        products.sale_percent, 
        products.stock, 
        products.is_new,    
        {$effectiveFields},
        (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img, 
        (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title, 
        (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri, 
        (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title,
    ");

        $this->db->from('products');

        // JOIN на таблицу product_categories, если фильтруем по категориям
        if (!empty($categories_id)) {
            $this->db->join('product_categories pc', 'pc.product_id = products.id', 'inner');
            $this->db->where_in('pc.category_id', $categories_id);
        }
        $this->db->where('products.isShown', 1);

        $this->db->join('products_img', 'products_img.product_id = products.id');

        // Фильтр по цене

        if (isset($_GET['price_min'], $_GET['price_max'])) {
            $this->applyEffectivePriceRange($_GET['price_min'], $_GET['price_max']);
        } else {
            $this->applySellableCondition();
        }

        // Фильтр по брендам
        if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
            $this->db->where_in('products.brand_id', $_GET['brand']);
        }

        // Пагинация
        $this->db->offset($offset);
        $this->db->limit($limit);

        // Сортировка
        if (!empty($_GET['sort'])) {
            switch ($_GET['sort']) {
                case '1':
                    $this->db->order_by("products.stock DESC, products.best_selling DESC, products.id ASC");
                    break;
                case '2':
                    $this->db->order_by("products.stock DESC, products.price ASC, products.id ASC");
                    break;
                case '3':
                    $this->db->order_by("products.stock DESC, products.price DESC, products.id ASC");
                    break;
                case '4':
                    $this->db->order_by("products.stock DESC, products.sale_mdl DESC, products.id ASC");
                    break;
                default:
                    $this->db->order_by("products.stock DESC, products.title$lang ASC, products.sorder ASC, products.id DESC");
                    break;
            }
        } else {
            $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        }

        // Группировка, чтобы избежать дубликатов
        $this->db->group_by("products.id");

        $products = $this->db->get()->result();

        return $products;
    }


    public function get_all_products_index_filter($categories_id = array())
    {
        $this->db->select("products.id,products.brand_id");

        // JOIN на таблицу product_categories
        if (!empty($categories_id)) {
            $this->db->join('product_categories pc', 'pc.product_id = products.id', 'inner');
            $this->db->where_in('pc.category_id', $categories_id);
        }

        $this->db->join('products_img', 'products_img.product_id = products.id');

        if (isset($_GET['price_min'], $_GET['price_max'])) {
            $this->applyEffectivePriceRange($_GET['price_min'], $_GET['price_max']);
        } else {
            $this->applySellableCondition();
        }
        if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
            $this->db->where_in('products.brand_id', $_GET['brand']);
        }
        if (!empty($_GET['sex']) && is_array($_GET['sex'])) {
            $this->db->where_in('products.sex', $_GET['sex']);
        }

        $this->db->where("$this->tblname.isShown", 1);
        $this->db->group_by("products.id");
        return $this->db->get($this->tblname)->result();
    }

    public function get_products_pag_index_filter($lang, $offset, $limit, $categories_id = array())
    {
        if (empty($lang)) {
            return false;
        }

        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
    $this->tblname.id,
    $this->tblname.SKU,
    $this->tblname.category_id,
    $this->tblname.title$lang AS title,
    $this->tblname.desc$lang AS `desc`,
    $this->tblname.uri$lang AS uri,
    $this->tblname.price,
    $this->tblname.priceWH,
    $this->tblname.discount_price,
    $this->tblname.sale_percent,
    $this->tblname.on_stock,
    $this->tblname.on_stockWH,
    $this->tblname.volume,
    $this->tblname.stock,
    $this->tblname.is_new,

    {$effectiveFields},
    (
        SELECT pi.img
        FROM products_img pi
        WHERE pi.product_id = $this->tblname.id
        ORDER BY pi.sorder ASC, pi.id DESC
        LIMIT 1
    ) AS img,

    (
        SELECT b.title
        FROM brands b
        WHERE b.id = $this->tblname.brand_id
        LIMIT 1
    ) AS brand_title,

    (
        SELECT c.uri$lang
        FROM categories c
        WHERE c.id = $this->tblname.category_id
        LIMIT 1
    ) AS cat_uri,

    (
        SELECT c.title$lang
        FROM categories c
        WHERE c.id = $this->tblname.category_id
        LIMIT 1
    ) AS cat_title
", false);

        /*
 * Filtrare după categoriile din product_categories.
 * Folosim EXISTS ca să nu multiplicăm produsele și să nu avem nevoie de GROUP BY.
 */
        if (!empty($categories_id) && is_array($categories_id)) {
            $categories_id = array_values(array_filter(
                array_map('intval', $categories_id),
                static fn($id) => $id > 0
            ));

            if (!empty($categories_id)) {
                $category_ids = implode(',', $categories_id);

                $this->db->where("
            EXISTS (
                SELECT 1
                FROM product_categories pc
                WHERE pc.product_id = $this->tblname.id
                AND pc.category_id IN ($category_ids)
            )
        ", null, false);
            }
        }

        /*
 * Excludem produsele fără imagini.
 */
        $this->db->where("
    EXISTS (
        SELECT 1
        FROM products_img pi_exists
        WHERE pi_exists.product_id = $this->tblname.id
    )
", null, false);

        /*
 * Filtrare după preț.
 */

        if (isset($_GET['price_min'], $_GET['price_max'])) {
            $this->applyEffectivePriceRange($_GET['price_min'], $_GET['price_max']);
        } else {
            $this->applySellableCondition();
        }

        /*
 * Filtrare după brand.
 */
        if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
            $brand_ids = array_values(array_filter(
                array_map('intval', $_GET['brand']),
                static fn($id) => $id > 0
            ));

            if (!empty($brand_ids)) {
                $this->db->where_in("$this->tblname.brand_id", $brand_ids);
            }
        }

        /*
 * Filtrare după sex.
 */
        if (!empty($_GET['sex']) && is_array($_GET['sex'])) {
            $sex_ids = array_values(array_filter(
                array_map('intval', $_GET['sex'])
            ));

            if (!empty($sex_ids)) {
                $this->db->where_in("$this->tblname.sex", $sex_ids);
            }
        }

        $this->db->where("$this->tblname.isShown", 1);

        /*
 * Sortare.
 */
        switch ((string) ($_GET['sort'] ?? '')) {
            case '1':
                $this->db->order_by(
                    "$this->tblname.stock DESC,
             $this->tblname.best_selling DESC,
             $this->tblname.id ASC"
                );
                break;

            case '2':
                $this->db->order_by(
                    "$this->tblname.stock DESC,
             $this->tblname.price ASC,
             $this->tblname.id ASC"
                );
                break;

            case '3':
                $this->db->order_by(
                    "$this->tblname.stock DESC,
             $this->tblname.price DESC,
             $this->tblname.id ASC"
                );
                break;

            case '4':
                $this->db->order_by(
                    "$this->tblname.stock DESC,
             $this->tblname.sale_mdl DESC,
             $this->tblname.id ASC"
                );
                break;

            default:
                $this->db->order_by(
                    "$this->tblname.stock DESC,
             $this->tblname.sorder ASC,
             $this->tblname.id DESC"
                );
                break;
        }

        $this->db->limit((int) $limit, (int) $offset);

        $products = $this->db
            ->get($this->tblname)
            ->result();

        return $products;
    }

    public function get_all_products_brands($brand_id, $categories_id = array())
    {
        $this->db->select("products.id, products.category_id");

        // JOIN на таблицу product_categories
        if (!empty($categories_id)) {
            $this->db->join('product_categories pc', 'pc.product_id = products.id', 'inner');
            $this->db->where_in('pc.category_id', $categories_id);
        }

        $this->db->join('products_img', 'products_img.product_id = products.id');
        $this->db->where("$this->tblname.isShown", 1);
        $this->applySellableCondition();

        $this->db->where('products.brand_id', $brand_id);
        $this->db->group_by("products.id");
        return $this->db->get($this->tblname)->result();
    }

    public function get_products_pag_brands($lang, $brand_id, $offset, $limit, $categories_id = array(), $sort = '')
    {
        if (empty($lang)) {
            return false;
        }
        $effectiveFields = $this->getEffectiveProductFields();

        $this->db->select("
            $this->tblname.id, 
            $this->tblname.SKU, 
            $this->tblname.category_id, 
            $this->tblname.title$lang as title,
            $this->tblname.desc$lang as desc,
            $this->tblname.uri$lang as uri,
            $this->tblname.price,  
            $this->tblname.priceWH,  
            $this->tblname.discount_price,
            $this->tblname.sale_percent,
            $this->tblname.on_stock,  
            $this->tblname.on_stockWH,  
            $this->tblname.volume, 
            $this->tblname.stock, 
            $this->tblname.is_new,    
             {$effectiveFields},
             (SELECT products_img.img FROM products_img WHERE products_img.product_id=$this->tblname.id ORDER BY sorder ASC, id DESC LIMIT 1) as img,
             (SELECT brands.title FROM brands WHERE brands.id=$this->tblname.brand_id) as brand_title,
             (SELECT categories.uri$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_uri,
             (SELECT categories.title$lang FROM categories WHERE categories.id=$this->tblname.category_id) as cat_title, 
        ");
        $this->db->join('products_img', 'products_img.product_id = products.id');

        // JOIN на таблицу product_categories
        if (!empty($categories_id)) {
            $this->db->join('product_categories pc', 'pc.product_id = products.id', 'inner');
            $this->db->where_in('pc.category_id', $categories_id);
        }

        $this->db->where("$this->tblname.isShown", 1);
       $this->applySellableCondition();

        $this->db->where('products.brand_id', $brand_id);
        $this->db->offset($offset);
        $this->db->limit($limit);

        if (!empty($_GET['sort'])) {
            if ($_GET['sort'] == '1') {
                $this->db->order_by("products.stock DESC, products.best_selling DESC, products.id DESC");
            } elseif ($_GET['sort'] == '2') {
                $this->db->order_by("products.stock DESC, products.price ASC, products.id DESC");
            } elseif ($_GET['sort'] == '3') {
                $this->db->order_by("products.stock DESC, products.price DESC, products.id DESC");
            } elseif ($_GET['sort'] == '4') {
                $this->db->order_by("products.stock DESC, products.sale_mdl DESC, products.id DESC");
            } else {
                $this->db->order_by("products.stock DESC, products.title ASC, products.id DESC");
            }
        } else {
            $this->db->order_by("products.stock DESC, products.sorder ASC, products.id DESC");
        }
        $this->db->group_by("products.id");
        $products = $this->db->get($this->tblname)->result();

        return $products;
    }

    public function get_all_category_products_price_max($categories_id = array())
    {
        $this->db->select_max('price', 'price');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where_in('category_id', $categories_id);
        return $this->db->get($this->tblname)->row();
    }

    public function get_all_category_products_price_min($categories_id = array())
    {
        $this->db->select_min('price', 'price');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where_in('category_id', $categories_id);
        return $this->db->get($this->tblname)->row();
    }

    public function get_all_brand_products_price_max($brand = '')
    {
        $this->db->select_max('price', 'price');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where('brand_id', $brand);
        return $this->db->get($this->tblname)->row();
    }

    public function get_all_brand_products_price_min($brand = '')
    {
        $this->db->select_min('price', 'price');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where('brand_id', $brand);
        return $this->db->get($this->tblname)->row();
    }

    public function get_all_products_price_max($products_id = array())
    {
        $pr_id = array();

        foreach ($products_id as $product) {
            $pr_id[$product->id] = $product->id;
        }
        $this->db->select_max('price', 'price');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where_in('id', $pr_id);
        return $this->db->get($this->tblname)->row();
    }

    public function get_all_products_price_min($products_id = array())
    {
        $pr_id = array();

        foreach ($products_id as $product) {
            $pr_id[$product->id] = $product->id;
        }
        $this->db->select_min('price', 'price');
        $this->db->where("$this->tblname.isShown", 1);
        $this->db->where_in('id', $pr_id);
        return $this->db->get($this->tblname)->row();
    }
    
    
    private function normalizeNicheSaleBrandIds(
        array $brandIds
    ) {
        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $brandIds
                    ),
                    static function ($brandId) {
                        return $brandId > 0;
                    }
                )
            )
        );
    }

    private function getNicheSaleDiscountRange(
        $selectedDiscount,
        $campaignMin,
        $campaignMax
    ) {
        $campaignMin = max(
            0,
            (int) $campaignMin
        );

        $campaignMax = max(
            $campaignMin,
            (int) $campaignMax
        );

        switch ((string) $selectedDiscount) {
            case '30-39':
                return array(
                    'min' => max(
                        $campaignMin,
                        30
                    ),
                    'max' => min(
                        $campaignMax,
                        39
                    ),
                );

            case '40-49':
                return array(
                    'min' => max(
                        $campaignMin,
                        40
                    ),
                    'max' => min(
                        $campaignMax,
                        49
                    ),
                );

            case '50':
                return array(
                    'min' => max(
                        $campaignMin,
                        50
                    ),
                    'max' => $campaignMax,
                );

            default:
                return array(
                    'min' => $campaignMin,
                    'max' => $campaignMax,
                );
        }
    }

    private function applyNicheSaleDiscountCondition(
        $selectedDiscount,
        $campaignMin,
        $campaignMax
    ) {
        $discountRange =
            $this->getNicheSaleDiscountRange(
                $selectedDiscount,
                $campaignMin,
                $campaignMax
            );

        $discountMin =
            (int) $discountRange['min'];

        $discountMax =
            (int) $discountRange['max'];

        $parentDiscountPercent = "
            ROUND(
                (
                    (
                        products.price
                        - products.discount_price
                    )
                    / products.price
                )
                * 100
            )
        ";

        $variantDiscountPercent = "
            ROUND(
                (
                    (
                        pv.price
                        - pv.discount_price
                    )
                    / pv.price
                )
                * 100
            )
        ";

        $this->db->where(
            "
            (
                (
                    products.price > 0
                    AND products.discount_price > 0
                    AND products.discount_price < products.price
                    AND {$parentDiscountPercent}
                        BETWEEN {$discountMin}
                        AND {$discountMax}
                )
                OR EXISTS (
                    SELECT 1
                    FROM products_variable pv
                    WHERE pv.product_id = products.id
                    AND pv.isShown = 1
                    AND pv.price > 0
                    AND pv.qty > 0
                    AND pv.discount_price > 0
                    AND pv.discount_price < pv.price
                    AND {$variantDiscountPercent}
                        BETWEEN {$discountMin}
                        AND {$discountMax}
                )
            )
            ",
            null,
            false
        );
    }

    private function applyNicheSaleConditions(
        array $allowedBrandIds,
        array $selectedBrandIds,
        $selectedDiscount,
        $campaignMin,
        $campaignMax
    ) {
        $allowedBrandIds =
            $this->normalizeNicheSaleBrandIds(
                $allowedBrandIds
            );

        $selectedBrandIds =
            $this->normalizeNicheSaleBrandIds(
                $selectedBrandIds
            );

        if (empty($allowedBrandIds)) {
            $this->db->where(
                'products.id',
                0
            );

            return;
        }

        $this->db->where(
            'products.isShown',
            1
        );

        $this->db->where_in(
            'products.brand_id',
            $allowedBrandIds
        );

        if (!empty($selectedBrandIds)) {
            $selectedBrandIds = array_values(
                array_intersect(
                    $selectedBrandIds,
                    $allowedBrandIds
                )
            );

            if (!empty($selectedBrandIds)) {
                $this->db->where_in(
                    'products.brand_id',
                    $selectedBrandIds
                );
            }
        }

        /*
        * Обычный товар может иметь собственную цену.
        * Вариативный — хотя бы один доступный вариант.
        */
        $this->db->where(
            "
            (
                products.price > 0
                OR EXISTS (
                    SELECT 1
                    FROM products_variable pv_sellable
                    WHERE pv_sellable.product_id = products.id
                    AND pv_sellable.isShown = 1
                    AND pv_sellable.price > 0
                    AND pv_sellable.qty > 0
                )
            )
            ",
            null,
            false
        );

        /*
        * Не выводим карточку без изображения.
        */
        $this->db->where(
            "
            (
                EXISTS (
                    SELECT 1
                    FROM products_img pi_exists
                    WHERE pi_exists.product_id = products.id
                )
                OR EXISTS (
                    SELECT 1
                    FROM products_variable pv_image
                    INNER JOIN products_variable_img pvi_exists
                        ON pvi_exists.variable_id = pv_image.id
                    WHERE pv_image.product_id = products.id
                    AND pv_image.isShown = 1
                )
            )
            ",
            null,
            false
        );

        $this->applyNicheSaleDiscountCondition(
            $selectedDiscount,
            $campaignMin,
            $campaignMax
        );
    }

    

   private function getNicheSaleCardFields(
        $selectedDiscount,
        $campaignMin,
        $campaignMax
    ) {
        $discountRange =
            $this->getNicheSaleDiscountRange(
                $selectedDiscount,
                $campaignMin,
                $campaignMax
            );

        $discountMin =
            (int) $discountRange['min'];

        $discountMax =
            (int) $discountRange['max'];

        $parentDiscountCondition = "
            products.price > 0
            AND products.discount_price > 0
            AND products.discount_price < products.price
            AND ROUND(
                (
                    (
                        products.price
                        - products.discount_price
                    )
                    / products.price
                )
                * 100
            ) BETWEEN {$discountMin} AND {$discountMax}
        ";

        $variantDiscountCondition = "
            pv.isShown = 1
            AND pv.price > 0
            AND pv.qty > 0
            AND pv.discount_price > 0
            AND pv.discount_price < pv.price
            AND ROUND(
                (
                    (
                        pv.price
                        - pv.discount_price
                    )
                    / pv.price
                )
                * 100
            ) BETWEEN {$discountMin} AND {$discountMax}
        ";

        $selectedVariantId = "
            SELECT pv.id
            FROM products_variable pv
            WHERE pv.product_id = products.id
            AND {$variantDiscountCondition}
            ORDER BY
                pv.price ASC,
                pv.sorder ASC,
                pv.id ASC
            LIMIT 1
        ";

        $variantPrice = "
            SELECT pv.price
            FROM products_variable pv
            WHERE pv.product_id = products.id
            AND {$variantDiscountCondition}
            ORDER BY
                pv.price ASC,
                pv.sorder ASC,
                pv.id ASC
            LIMIT 1
        ";

        $variantDiscountPrice = "
            SELECT pv.discount_price
            FROM products_variable pv
            WHERE pv.product_id = products.id
            AND {$variantDiscountCondition}
            ORDER BY
                pv.price ASC,
                pv.sorder ASC,
                pv.id ASC
            LIMIT 1
        ";

        $variantStock = "
            SELECT pv.qty
            FROM products_variable pv
            WHERE pv.product_id = products.id
            AND {$variantDiscountCondition}
            ORDER BY
                pv.price ASC,
                pv.sorder ASC,
                pv.id ASC
            LIMIT 1
        ";

        $variantWholesalePrice = "
            SELECT pv.priceWH
            FROM products_variable pv
            WHERE pv.product_id = products.id
            AND {$variantDiscountCondition}
            ORDER BY
                pv.price ASC,
                pv.sorder ASC,
                pv.id ASC
            LIMIT 1
        ";

        $variantWholesaleStock = "
            SELECT pv.qtyWH
            FROM products_variable pv
            WHERE pv.product_id = products.id
            AND {$variantDiscountCondition}
            ORDER BY
                pv.price ASC,
                pv.sorder ASC,
                pv.id ASC
            LIMIT 1
        ";

        $parentImage = "
            SELECT pi.img
            FROM products_img pi
            WHERE pi.product_id = products.id
            ORDER BY
                pi.sorder ASC,
                pi.id DESC
            LIMIT 1
        ";

        $variantImage = "
            SELECT pvi.img
            FROM products_variable_img pvi
            WHERE pvi.variable_id = (
                {$selectedVariantId}
            )
            ORDER BY
                pvi.sorder ASC,
                pvi.id ASC
            LIMIT 1
        ";

        return "
            CASE
                WHEN {$parentDiscountCondition}
                    THEN products.price
                ELSE (
                    {$variantPrice}
                )
            END AS effective_price,

            CASE
                WHEN {$parentDiscountCondition}
                    THEN products.discount_price
                ELSE (
                    {$variantDiscountPrice}
                )
            END AS effective_discount_price,

            CASE
                WHEN {$parentDiscountCondition}
                    THEN products.on_stock
                ELSE (
                    {$variantStock}
                )
            END AS effective_stock,

            CASE
                WHEN {$parentDiscountCondition}
                    AND products.priceWH > 0
                    THEN products.priceWH
                ELSE (
                    {$variantWholesalePrice}
                )
            END AS effective_price_wholesale,

            CASE
                WHEN {$parentDiscountCondition}
                    AND products.priceWH > 0
                    THEN products.on_stockWH
                ELSE (
                    {$variantWholesaleStock}
                )
            END AS effective_stock_wholesale,

            CASE
                WHEN {$parentDiscountCondition}
                    THEN COALESCE(
                        (
                            {$parentImage}
                        ),
                        (
                            {$variantImage}
                        )
                    )
                ELSE COALESCE(
                    (
                        {$variantImage}
                    ),
                    (
                        {$parentImage}
                    )
                )
            END AS effective_img,

            EXISTS (
                SELECT 1
                FROM products_variable pv_exists
                WHERE pv_exists.product_id = products.id
                AND pv_exists.isShown = 1
                AND (
                    pv_exists.price > 0
                    OR pv_exists.priceWH > 0
                )
            ) AS variable
        ";
    }

    public function get_all_niche_sale_products(
        array $allowedBrandIds,
        array $selectedBrandIds = array(),
        $selectedDiscount = '',
        $campaignMin = 30,
        $campaignMax = 50
    ) {
        $this->db->select("
            products.id,
            products.brand_id,
            products.category_id
        ");

        $this->db->from(
            $this->tblname
        );

        $this->applyNicheSaleConditions(
            $allowedBrandIds,
            $selectedBrandIds,
            $selectedDiscount,
            $campaignMin,
            $campaignMax
        );

        return $this->db
            ->get()
            ->result();
    }

    public function get_niche_sale_products_pag(
        $lang,
        array $allowedBrandIds,
        array $selectedBrandIds,
        $selectedDiscount,
        $campaignMin,
        $campaignMax,
        $offset,
        $limit
    ) {
        if (empty($lang)) {
            return array();
        }

        $cardFields =
            $this->getNicheSaleCardFields(
                $selectedDiscount,
                $campaignMin,
                $campaignMax
            );

        $this->db->select("
            products.id,
            products.SKU,
            products.category_id,
            products.brand_id,

            products.title{$lang} AS title,
            products.desc{$lang} AS `desc`,
            products.uri{$lang} AS uri,

            products.price,
            products.priceWH,
            products.priceWH AS price_wholesale,
            products.discount_price,

            products.on_stock,
            products.on_stockWH,

            products.volume,
            products.stock,
            products.is_new,
            products.best_selling,

            {$cardFields},

            (
                SELECT pi.img
                FROM products_img pi
                WHERE pi.product_id = products.id
                ORDER BY
                    pi.sorder ASC,
                    pi.id DESC
                LIMIT 1
            ) AS img,

            (
                SELECT brands.title
                FROM brands
                WHERE brands.id = products.brand_id
                LIMIT 1
            ) AS brand_title,

            (
                SELECT categories.uri{$lang}
                FROM categories
                WHERE categories.id = products.category_id
                LIMIT 1
            ) AS cat_uri,

            (
                SELECT categories.title{$lang}
                FROM categories
                WHERE categories.id = products.category_id
                LIMIT 1
            ) AS cat_title
        ", false);

        $this->db->from(
            $this->tblname
        );

        $this->applyNicheSaleConditions(
            $allowedBrandIds,
            $selectedBrandIds,
            $selectedDiscount,
            $campaignMin,
            $campaignMax
        );

        $this->db->order_by(
            "
            products.stock DESC,
            products.best_selling DESC,
            products.sorder ASC,
            products.id DESC
            "
        );

        $this->db->limit(
            max(
                1,
                (int) $limit
            ),
            max(
                0,
                (int) $offset
            )
        );

        return $this->db
            ->get()
            ->result();
    }
    
    
    /**
     * Возвращает SQL-поля для цены и остатка карточки товара.
     *
     * Если цена родителя существует — используем её.
     * Если цена родителя равна нулю — используем минимальную
     * цену доступного варианта.
     */
    private function getEffectiveProductFields()
    {
        $retailPrice = "\n            CASE\n                WHEN products.price > 0 THEN products.price\n                ELSE (\n                    SELECT MIN(pv.price)\n                    FROM products_variable pv\n                    WHERE pv.product_id = products.id\n                      AND pv.price > 0\n                      AND pv.qty > 0\n                )\n            END\n        ";

        $retailStock = "\n            CASE\n                WHEN products.on_stock > 0 THEN products.on_stock\n                ELSE (\n                    SELECT COALESCE(SUM(pv.qty), 0)\n                    FROM products_variable pv\n                    WHERE pv.product_id = products.id\n                      AND pv.price > 0\n                      AND pv.qty > 0\n                )\n            END\n        ";

        $wholesalePrice = "\n            CASE\n                WHEN products.priceWH > 0 THEN products.priceWH\n                ELSE (\n                    SELECT MIN(pv.priceWH)\n                    FROM products_variable pv\n                    WHERE pv.product_id = products.id\n                      AND pv.priceWH > 0\n                      AND pv.qtyWH > 0\n                )\n            END\n        ";

        $wholesaleStock = "\n            CASE\n                WHEN products.on_stockWH > 0 THEN products.on_stockWH\n                ELSE (\n                    SELECT COALESCE(SUM(pv.qtyWH), 0)\n                    FROM products_variable pv\n                    WHERE pv.product_id = products.id\n                      AND pv.priceWH > 0\n                      AND pv.qtyWH > 0\n                )\n            END\n        ";

        $effectivePrice = !empty($_SESSION['isb2b'])
            ? $wholesalePrice
            : $retailPrice;

        $effectiveStock = !empty($_SESSION['isb2b'])
            ? $wholesaleStock
            : $retailStock;

        $hasVariable = !empty($_SESSION['isb2b'])
            ? "
                EXISTS (
                    SELECT 1
                    FROM products_variable pv
                    WHERE pv.product_id = products.id
                    AND pv.isShown = 1
                    AND pv.priceWH > 0
                )
            "
            : "
                EXISTS (
                    SELECT 1
                    FROM products_variable pv
                    WHERE pv.product_id = products.id
                    AND pv.isShown = 1
                    AND pv.price > 0
                )
            ";

        return "
            {$retailPrice} AS effective_price_retail,
            {$retailStock} AS effective_stock_retail,
            {$wholesalePrice} AS effective_price_wh,
            {$wholesaleStock} AS effective_stock_wh,
            {$effectivePrice} AS effective_price,
            {$effectiveStock} AS effective_stock,
            {$hasVariable} AS has_variable
        ";
    }

    /**
     * Возвращает SQL-поле, которое определяет,
     * есть ли у товара хотя бы один опубликованный вариант.
     *
     * Здесь специально НЕ проверяем stock.
     *
     * Quick View должен считаться вариативным даже если
     * все варианты временно закончились — тогда внутри
     * Quick View мы просто покажем их как недоступные.
     */
    private function getHasVariableField()
    {
        if (!empty($_SESSION['isb2b'])) {
            return "
                EXISTS (
                    SELECT 1
                    FROM products_variable pv
                    WHERE pv.product_id = products.id
                    AND pv.isShown = 1
                    AND pv.priceWH > 0
                ) AS has_variable
            ";
        }

        return "
            EXISTS (
                SELECT 1
                FROM products_variable pv
                WHERE pv.product_id = products.id
                AND pv.isShown = 1
                AND pv.price > 0
            ) AS has_variable
        ";
    }
}
