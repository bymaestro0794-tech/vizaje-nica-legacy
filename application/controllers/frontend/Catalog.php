<?php
defined('BASEPATH') or exit('No direct script access allowed');

use JasonGrimes\Paginator;

class Catalog extends FrontEndController
{
    private $page_id;
    private $per_page;

    public function __construct()
    {
        parent::__construct();
        $this->page_id = 3;
        $this->per_page = 32;
        $this->load->model('products_model');
        $this->load->model('brands_model');
        $this->load->model('categories_model');

        $this->data['body_class'] = 'catalog_page';
    }

    private function _load_main_page($page)
    {
        $this->data['page_title'] = (!empty($page->title)) ? $page->title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = (!empty($page->text)) ? $page->text : $page->title;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $page->seo_title;
        $this->data['breadcrumbs'] = $this->breadcrumbs;
        $this->data['class_page'] = '_webp';
    }

    private function loadOGImgData($page = false, $dir = 'menu')
    {
        if (empty($page)) throw_on_404();

        if (!empty($page->img)) {
            $this->data['og_img'] = newthumbs($page->img, $dir, 500, 300, 'og500x300x1', 1);
            $this->data['og_img_width'] = 500;
            $this->data['og_img_height'] = 300;
        } else {
            $this->data['og_img'] = '/public/i/no_image_214_51_og214x51x1.jpg';
            $this->data['og_img_width'] = 214;
            $this->data['og_img_height'] = 51;
        }
    }

    public function index()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        $product_all = $this->products_model->get_all_products();
        $products = $this->products_model->get_products_pag($this->clang, $start, $this->per_page);
        $count = count($product_all);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $string = '';

        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);

        $this->data['canonical_url'] = $this->without_get_url;
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2;
        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $this->data['brands'] = $this->brands_model->get_brands();

        $this->data['lang_urls'] = array(
            'ro' => '/' . $page->uriRO,
            'ru' => '/' . $page->uriRU,
        );
        $this->data['inner_view'] = 'pages/catalog/index';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();
    }

    public function discounts()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        $product_all = $this->products_model->get_all_products_discounts();
        $products = $this->products_model->get_products_pag_discounts($this->clang, $start, $this->per_page);
        $count = count($product_all);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $string = '';

        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2;
        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $this->data['brands'] = $this->brands_model->get_brands();

        $this->data['lang_urls'] = array(
            'ro' => '/' . $page->uriRO,
            'ru' => '/' . $page->uriRU,
        );
        $this->data['inner_view'] = 'pages/catalog/discounts';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();
    }

    public function discounts_item()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $offers = $this->offers_model->get_offers_data_by_uri($this->clang, $this->uri3);
        if (empty($offers)) throw_on_404();


        $products_offers = $this->offers_model->gat_products_offers($offers->id);
        foreach ($products_offers as $id) {
            $pord_id[] = $id->product_id;
        }
        if (!empty($pord_id)) {
            $products = $this->offers_model->products_offers($this->clang, $pord_id);
            $this->data['products'] = $products;
        }
        $this->data['offers'] = $offers;

        $this->data['brands'] = $this->brands_model->get_brands();

        $this->data['lang_urls'] = array(
            'ro' => '/' . $page->uriRO . '/' . $offers->uriRO,
            'ru' => '/' . $page->uriRU . '/' . $offers->uriRU,
        );
        $this->data['inner_view'] = 'pages/catalog/discounts_item';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();
    }

    public function best_sellers()
{
    $page = $this->menu_model->get_page_data_by_id(
        $this->clang,
        35
    );

    if (empty($page)) {
        throw_on_404();
    }

    $pagination_nr = !empty($_GET['page'])
        ? (int) $_GET['page']
        : 1;

    if ($pagination_nr < 1) {
        $pagination_nr = 1;
    }

    $start = ($pagination_nr - 1) * $this->per_page;

    $product_all =
        $this->products_model
            ->get_all_products_best_sellers();

    $products =
        $this->products_model
            ->get_products_pag_best_sellers(
                $this->clang,
                $start,
                $this->per_page
            );

    $products_count = count($product_all);

    $uri_parts = explode(
        '?',
        $_SERVER['REQUEST_URI'],
        2
    );

    $urlPattern =
        $uri_parts[0]
        . '?page=(:num)';

    $paginator = new Paginator(
        $products_count,
        $this->per_page,
        $pagination_nr,
        $urlPattern
    );

    $paginator->setMaxPagesToShow(5);

    $this->data['page_url'] =
        '/'
        . $this->uri1
        . '/'
        . $this->uri2;

    $this->data['paginator'] = $paginator;

    $this->data['products'] = $products;
    $this->data['products_count'] =
        $products_count;

    $this->data['show_from'] = $start;
    $this->data['show_to'] =
        $start + $this->per_page;

    /*
    |--------------------------------------------------------------------------
    | Brands for discount-page filter
    |--------------------------------------------------------------------------
    */

    $this->data['brands'] =
        $this->brands_model
            ->get_brands_category(
                $product_all
            );

    /*
    |--------------------------------------------------------------------------
    | Categories for discount-page filter
    |--------------------------------------------------------------------------
    |
    | Важно:
    | Не записываем эти категории в $this->data['categories'],
    | потому что categories уже используется глобальным header.
    |
    */

    $cat_ids = array_unique(
        array_filter(
            array_column(
                $product_all,
                'category_id'
            )
        )
    );

    $filter_categories = [];

    if (!empty($cat_ids)) {
        $filter_categories = $this->db
            ->select(
                "
                id,
                uri{$this->clang} AS uri,
                title{$this->clang} AS title
                "
            )
            ->where('isShown', 1)
            ->where_in('id', $cat_ids)
            ->order_by('sorder ASC, id DESC')
            ->get('categories')
            ->result();
    }

    foreach (
        $filter_categories as $filter_category
    ) {
        $filter_category->count = count(
            array_filter(
                $product_all,
                static function (
                    $product
                ) use (
                    $filter_category
                ) {
                    return (
                        (int) $product->category_id
                        ===
                        (int) $filter_category->id
                    );
                }
            )
        );
    }

    /*
     * Категории только для sidebar-фильтра.
     * Глобальный $this->data['categories']
     * больше не перезаписывается.
     */
    $this->data['filter_categories'] =
        $filter_categories;

    /*
    |--------------------------------------------------------------------------
    | Price range
    |--------------------------------------------------------------------------
    */

    if (!empty($product_all)) {
        $prices = array_filter(
            array_map(
                static function ($product) {
                    return isset($product->price)
                        ? (float) $product->price
                        : null;
                },
                $product_all
            ),
            static function ($price) {
                return $price !== null;
            }
        );

        if (!empty($prices)) {
            $this->data['price_max'] =
                max($prices);

            $this->data['price_min'] =
                min($prices);
        } else {
            $this->data['price_max'] = 0;
            $this->data['price_min'] = 0;
        }
    } else {
        $this->data['price_max'] = 0;
        $this->data['price_min'] = 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Page data
    |--------------------------------------------------------------------------
    */

    $this->data['lang_urls'] = [
        'ro' => '/reduceri',
        'ru' => '/skidki',
    ];

    $this->data['inner_view'] =
        'pages/catalog/best_sellers';

    $this->loadOGImgData($page);
    $this->_load_main_page($page);

    $this->_render();
}

    public function new_products()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 34);
        if (empty($page)) throw_on_404();

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        $product_all = $this->products_model->get_all_products_new_products();
        $products = $this->products_model->get_products_pag_new_products($this->clang, $start, $this->per_page);
        $count = count($product_all);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $string = '';

        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2;
        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;

        $this->data['brands'] = $this->brands_model->get_brands();

        $this->data['lang_urls'] = array(
            'ro' => '/new_products',
            'ru' => '/new_products',
        );
        $this->data['inner_view'] = 'pages/catalog/new_products';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();
    }

    public function category()
    {

        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $category = $this->categories_model->get_category_by_uri($this->clang, $this->uri3);

        if (!empty($category)) {
            $cat_parent1 = $this->categories_model->get_category_by_id($this->clang, $category->parent_id);
            $this->data['cat_parent1'] = $cat_parent1;

            if (!empty($cat_parent1)) {
                $cat_parent2 = $this->categories_model->get_category_by_id($this->clang, $cat_parent1->parent_id);
                $this->data['cat_parent2'] = $cat_parent2;
            }

            $categories_id[] = $category->id;
            $children = $this->categories_model->get_category_children($this->clang, $category->id);


            if (!empty($children)) {
                $this->data['children'] = $children;
                foreach ($children as $child) {
                    $categories_id[] = $child->id;
                    $ch = $this->categories_model->get_category_children($this->clang, $child->id);
                    if (!empty($ch)) {
                        $child->children = $ch;
                        foreach ($ch as $c) {
                            $categories_id[] = $c->id;
                            $child->count = $child->count + $c->count;
                        }
                    }
                }
            }

            $pagination_nr = @$_GET['page'];

            if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
            $start = ($pagination_nr - 1) * $this->per_page;

            $product_all = $this->products_model->get_all_category_products($categories_id);
            $products = $this->products_model->get_category_products_pag($this->clang, $categories_id, $start, $this->per_page);

            foreach ($products as $product) {
                $product->variable = $this->products_model->get_product_variable($this->clang, $product->id);
            }

            $count = count($product_all);
            $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
            $string = $_SERVER['QUERY_STRING'];
            if (empty($_GET['page'])) $_GET['page'] = '';
            $string = str_replace("&page=" . $_GET['page'], '', $string);

            $urlPattern = $uri_parts[0] . '?' . $string . '&page=(:num)';
            $paginator = new Paginator(
                $count,
                $this->per_page,
                $pagination_nr,
                $urlPattern
            );
            $paginator->setMaxPagesToShow(5);

            $this->data['canonical_url'] = $this->without_get_url;
            $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
            $this->data['paginator'] = $paginator;
            $this->data['products'] = $products;
            $this->data['products_count'] = $count;
            $this->data['show_from'] = $start;
            $this->data['show_to'] = $start + $this->per_page;

            $this->data['category'] = $category;

            $this->data['brands'] = $this->brands_model->get_brands_category($product_all);


            //                Features Start
            $price_max = $this->products_model->get_all_category_products_price_max($categories_id);
            $this->data['price_max'] = $price_max->price;
            $price_min = $this->products_model->get_all_category_products_price_min($categories_id);
            $this->data['price_min'] = $price_min->price;
            //                Features END

            $this->data['inner_view'] = 'pages/catalog/category';


            $this->loadOGImgData($category, 'categories');
            $this->_load_main_page($category);

            $this->data['lang_urls'] = array(
                'ro' => '/' . $page->uriRO . '/' . $category->uriRO,
                'ru' => '/' . $page->uriRU . '/' . $category->uriRU,
            );

            $this->_render();
        } else {
            $this->brands();
        }
    }

    public function brands()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $brand = $this->brands_model->get_brand_by_uri($this->clang, $this->uri3);
        if (empty($brand)) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . '/' . $this->uri1 . '/' . $this->uri2);
            die();
        }

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        if (!empty($_GET['category'])) {
            $categories_id = $_GET['category'];
            $product_all = $this->products_model->get_all_products_index_filter($categories_id);
            $products = $this->products_model->get_products_pag_index_filter($this->clang, $start, $this->per_page, $categories_id);
            $count = count($product_all);
        } else {
            $product_all = $this->products_model->get_all_products_brands($brand->id);
            $products = $this->products_model->get_products_pag_brands($this->clang, $brand->id, $start, $this->per_page);
            $count = count($product_all);
        }

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        $string = '';
        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
        $this->data['paginator'] = $paginator;
        $this->data['products'] = $products;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $this->data['brand'] = $brand;

        //                Features Start
        $price_max = $this->products_model->get_all_brand_products_price_max($brand->id);
        $this->data['price_max'] = $price_max->price;
        $price_min = $this->products_model->get_all_brand_products_price_min($brand->id);
        $this->data['price_min'] = $price_min->price;

        $product_all = $this->products_model->get_all_products_brands($brand->id);
        $cat_ids = array();
        foreach ($product_all as $value) {
            $cat_ids[$value->category_id] = $value->category_id;
        }
        $this->data['categoryes_filter'] = $this->categories_model->get_category_ids($this->clang, $brand->id, $cat_ids);
        //                Features END


        $this->data['lang_urls'] = array(
            'ro' => '/' . $page->uriRO . '/' . $brand->uri,
            'ru' => '/' . $page->uriRU . '/' . $brand->uri,
        );
        $this->data['inner_view'] = 'pages/catalog/brand';
        $this->loadOGImgData($brand, 'brands');
        $this->_load_main_page($brand);

        $this->_render();
    }

   public function item()
{
    /*
    |--------------------------------------------------------------------------
    | Page / category / product
    |--------------------------------------------------------------------------
    */

    $page =
        $this->menu_model
            ->get_page_data_by_id(
                $this->clang,
                $this->page_id
            );

    if (empty($page)) {
        throw_on_404();
    }

    $category =
        $this->categories_model
            ->get_category_by_uri(
                $this->clang,
                $this->uri3
            );

    if (empty($category)) {
        throw_on_404();
    }

    $this->data['category'] =
        $category;

    $product =
        $this->products_model
            ->get_product_by_uri_for_pdp(
                $this->clang,
                $this->uri4
            );

    if (empty($product)) {
        throw_on_404();
    }

    /*
    |--------------------------------------------------------------------------
    | Category parent
    |--------------------------------------------------------------------------
    */

    if (!empty($category->parent_id)) {
        $this->data['cat_parent1'] =
            $this->categories_model
                ->get_category_by_id(
                    $this->clang,
                    $category->parent_id
                );
    }

    /*
    |--------------------------------------------------------------------------
    | Product data
    |--------------------------------------------------------------------------
    */

    $product->img =
        $this->products_model
            ->get_product_img(
                $product->id
            );

    $product->variable =
        $this->products_model
            ->get_product_variable(
                $this->clang,
                $product->id,
                true
            );

    $this->data['product'] =
        $product;

    $this->data['features'] =
        $this->products_model
            ->get_product_features(
                $this->clang,
                $product->id
            );

    /*
    |--------------------------------------------------------------------------
    | Recommendations
    |--------------------------------------------------------------------------
    */

    $this->data['similar_products'] =
        $this->products_model
            ->get_similar_products(
                $this->clang,
                $product,
                10
            );

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    */

    $this->data['inner_view'] =
        'pages/catalog/item';

    /*
    |--------------------------------------------------------------------------
    | URLs / canonical / hreflang
    |--------------------------------------------------------------------------
    */

    $this->prepareProductUrls(
        $page,
        $category,
        $product
    );

    /*
    |--------------------------------------------------------------------------
    | Common page data
    |--------------------------------------------------------------------------
    */

    $this->_load_main_page(
        $product
    );

    /*
    |--------------------------------------------------------------------------
    | Product SEO
    |--------------------------------------------------------------------------
    */

    $seoDescription =
        $this->prepareProductMeta(
            $product
        );

    $productSeoImage =
        $this->prepareProductOpenGraph(
            $product
        );

    $productSchema =
    $this->buildProductSchema(
        $product,
        $seoDescription,
        $productSeoImage
    );

    $breadcrumbSchema =
        $this->buildProductBreadcrumbSchema(
            $page,
            $category,
            $product
        );

    $itemPageSchema =
        $this->buildProductItemPageSchema(
            $product,
            $seoDescription,
            $productSeoImage
        );

    $this->data['product_schema_graph'] =
        $this->buildProductSchemaGraph(
            $productSchema,
            $breadcrumbSchema,
            $itemPageSchema
        );

    $this->_render();
}

private function prepareProductUrls(
    $page,
    $category,
    $product
) {
    $ruLanguagePath =
        '/'
        . $page->uriRU
        . '/'
        . $category->uriRU
        . '/'
        . $product->uriRU;

    $roLanguagePath =
        '/'
        . $page->uriRO
        . '/'
        . $category->uriRO
        . '/'
        . $product->uriRO;

    /*
     * Legacy language switcher.
     * Он самостоятельно добавляет /ru и /ro.
     */
    $this->data['lang_urls'] = array(
        'ro' => $roLanguagePath,
        'ru' => $ruLanguagePath,
    );

    $ruSeoPath =
        '/ru'
        . $ruLanguagePath;

    $roSeoPath =
        '/ro'
        . $roLanguagePath;

    $currentSeoPath =
        $this->lclang === 'ro'
            ? $roSeoPath
            : $ruSeoPath;

    $canonicalUrl =
        $this->site_url
        . $currentSeoPath;

    $this->data['canonical_url'] =
        $canonicalUrl;

    $this->data['hreflang_urls'] = array(
        'ro' =>
            $this->site_url
            . $roSeoPath,

        'ru' =>
            $this->site_url
            . $ruSeoPath,
    );

    $this->data['og_url'] =
        $canonicalUrl;
}
private function prepareProductMeta(
    $product
) {
    $seoTitle =
        !empty($product->seo_title)
            ? trim(
                (string) $product->seo_title
            )
            : trim(
                (string) $product->title
            );

    $seoTitle =
        preg_replace(
            '/\s+/u',
            ' ',
            $seoTitle
        );

    $seoTitle =
        trim($seoTitle);

    if (
        stripos(
            $seoTitle,
            'Vizaje-Nica'
        ) === false
    ) {
        $seoTitle .=
            ' | Vizaje-Nica';
    }

    $seoDescription =
        $this->buildProductDescription(
            $product
        );

    $this->data['page_title'] =
        $seoTitle;

    $this->data['otitle'] =
        $seoTitle;

    $this->data[
        'description_for_layout'
    ] = $seoDescription;

    return $seoDescription;
}
private function buildProductDescription(
    $product
) {
    if (!empty($product->seo_desc)) {
        return trim(
            (string) $product->seo_desc
        );
    }

    $descriptionSource = '';

    if (!empty($product->desc)) {
        $descriptionSource =
            (string) $product->desc;
    } elseif (!empty($product->text)) {
        $descriptionSource =
            (string) $product->text;
    }

    if ($descriptionSource === '') {
        return '';
    }

    $descriptionSource =
        preg_replace(
            '#<(br\s*/?|/p|/div|/li|/h[1-6])\s*>#iu',
            ' ',
            $descriptionSource
        );

    $descriptionSource =
        html_entity_decode(
            strip_tags(
                $descriptionSource
            ),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

    $descriptionSource =
        preg_replace(
            '/\s+/u',
            ' ',
            $descriptionSource
        );

    $descriptionSource =
        trim(
            $descriptionSource
        );

    if (
        mb_strlen(
            $descriptionSource,
            'UTF-8'
        ) <= 155
    ) {
        return $descriptionSource;
    }

    return rtrim(
        mb_substr(
            $descriptionSource,
            0,
            155,
            'UTF-8'
        )
    ) . '…';
}
private function prepareProductOpenGraph(
    $product
) {
    $this->data['og_type'] =
        'product';

    $productSeoImage = null;

    if (!empty($product->img)) {
        $productSeoImage =
            newthumbs(
                $product->img[0]->img,
                'products',
                1200,
                1200,
                'seo1200x1200x1',
                1
            );

        $this->data['og_img'] =
            $productSeoImage;

        $this->data[
            'product_has_real_image'
        ] = true;
    } else {
        $this->data['og_img'] =
            '/app/img/no-image/no_image-og.webp';

        $this->data[
            'product_has_real_image'
        ] = false;
    }

    $this->data['og_img_width'] =
        1200;

    $this->data['og_img_height'] =
        1200;

    return $productSeoImage;
}
private function buildProductSchema(
    $product,
    $seoDescription,
    $productSeoImage = null
) {
    $productSchemaName =
        !empty($product->title)
            ? trim(
                (string) $product->title
            )
            : '';

    $productSchemaDescription =
        $seoDescription !== ''
            ? $seoDescription
            : $productSchemaName;

    /*
    |--------------------------------------------------------------------------
    | Price
    |--------------------------------------------------------------------------
    */

    $schemaPrice = 0;

    if (!empty($product->variable)) {
        $availablePrices = array();
        $allPrices = array();

        foreach (
            $product->variable
            as $variable
        ) {
            $qty =
                (int) (
                    $variable->qty ?? 0
                );

            $price =
                (float) (
                    !empty(
                        $variable
                            ->discount_price
                    )
                        ? $variable
                            ->discount_price
                        : (
                            $variable
                                ->price
                            ?? 0
                        )
                );

            if ($price <= 0) {
                continue;
            }

            $allPrices[] =
                $price;

            if ($qty > 0) {
                $availablePrices[] =
                    $price;
            }
        }

        if (!empty($availablePrices)) {
            $schemaPrice =
                min($availablePrices);
        } elseif (!empty($allPrices)) {
            $schemaPrice =
                min($allPrices);
        }
    } else {
        $schemaPrice =
            (float) (
                !empty(
                    $product
                        ->discount_price
                )
                    ? $product
                        ->discount_price
                    : (
                        $product->price
                        ?? 0
                    )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Availability
    |--------------------------------------------------------------------------
    */

    $inStock = false;

    if (!empty($product->variable)) {
        foreach (
            $product->variable
            as $variable
        ) {
            if (
                (int) (
                    $variable->qty ?? 0
                ) > 0
                &&
                (float) (
                    $variable->price ?? 0
                ) > 0
            ) {
                $inStock = true;
                break;
            }
        }
    } else {
        $inStock =
            (int) (
                $product->on_stock
                ?? 0
            ) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    $schema = array(
        '@type' =>
            'Product',

        '@id' =>
            $this->data['canonical_url']
            . '#product',

        'name' =>
            $productSchemaName,

        'description' =>
            $productSchemaDescription,

        'sku' =>
            !empty($product->SKU)
                ? (string) $product->SKU
                : (string) $product->id,

        'url' =>
            $this->data['canonical_url'],
    );

    /*
    |--------------------------------------------------------------------------
    | Real product image only
    |--------------------------------------------------------------------------
    */

    if (
        $productSeoImage !== null
    ) {
        $schema['image'] =
            array(
                $this->site_url
                . $productSeoImage,
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

    if (
        !empty(
            $product->brand_title
        )
    ) {
        $schema['brand'] =
            array(
                '@type' =>
                    'Brand',

                'name' =>
                    trim(
                        (string)
                        $product
                            ->brand_title
                    ),
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Offer
    |--------------------------------------------------------------------------
    */

    if ($schemaPrice > 0) {
        $schema['offers'] =
            array(
                '@type' =>
                    'Offer',

                'url' =>
                    $this->data[
                        'canonical_url'
                    ],

                'priceCurrency' =>
                    'MDL',

                'price' =>
                    number_format(
                        $schemaPrice,
                        2,
                        '.',
                        ''
                    ),

                'availability' =>
                    $inStock
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/OutOfStock',

                'itemCondition' =>
                    'https://schema.org/NewCondition',
            );
    }

    return $schema;
}

private function buildProductBreadcrumbSchema(
    $page,
    $category,
    $product
) {
    $homeUrl =
        $this->site_url
        . '/'
        . $this->lclang;

    $catalogUrl =
        $this->site_url
        . '/'
        . $this->lclang
        . '/'
        . (
            $this->lclang === 'ro'
                ? $page->uriRO
                : $page->uriRU
        );

    $categoryUrl =
        $catalogUrl
        . '/'
        . (
            $this->lclang === 'ro'
                ? $category->uriRO
                : $category->uriRU
        );

    $productName =
        !empty($product->breadcrumb_title)
            ? trim(
                (string) $product->breadcrumb_title
            )
            : trim(
                (string) $product->title
            );

    $productName =
        preg_replace(
            '/\s+/u',
            ' ',
            $productName
        );

    return array(
        '@type' =>
            'BreadcrumbList',

        '@id' =>
            $this->data['canonical_url']
            . '#breadcrumb',

        'itemListElement' =>
            array(
                array(
                    '@type' =>
                        'ListItem',

                    'position' =>
                        1,

                    'name' =>
                        (string)
                        $this->data[
                            'home_bc_title'
                        ],

                    'item' =>
                        $homeUrl,
                ),

                array(
                    '@type' =>
                        'ListItem',

                    'position' =>
                        2,

                    'name' =>
                        !empty($page->title)
                            ? trim(
                                (string)
                                $page->title
                            )
                            : (
                                $this->lclang === 'ro'
                                    ? 'Catalog'
                                    : 'Каталог'
                            ),

                    'item' =>
                        $catalogUrl,
                ),

                array(
                    '@type' =>
                        'ListItem',

                    'position' =>
                        3,

                    'name' =>
                        !empty(
                            $category
                                ->breadcrumb_title
                        )
                            ? trim(
                                (string)
                                $category
                                    ->breadcrumb_title
                            )
                            : trim(
                                (string)
                                $category->title
                            ),

                    'item' =>
                        $categoryUrl,
                ),

                array(
                    '@type' =>
                        'ListItem',

                    'position' =>
                        4,

                    'name' =>
                        $productName,
                ),
            ),
    );
}

private function buildProductItemPageSchema(
    $product,
    $seoDescription,
    $productSeoImage = null
) {
    $pageName =
        !empty($this->data['page_title'])
            ? trim(
                (string)
                $this->data['page_title']
            )
            : trim(
                (string)
                $product->title
            );

    $schema = array(
        '@type' =>
            'ItemPage',

        '@id' =>
            $this->data['canonical_url']
            . '#webpage',

        'url' =>
            $this->data['canonical_url'],

        'name' =>
            $pageName,

        'description' =>
            $seoDescription,

        'inLanguage' =>
            $this->lclang === 'ro'
                ? 'ro-RO'
                : 'ru-RU',

        'isPartOf' =>
            array(
                '@id' =>
                    $this->site_url
                    . '/#website',
            ),

        'breadcrumb' =>
            array(
                '@id' =>
                    $this->data['canonical_url']
                    . '#breadcrumb',
            ),

        'mainEntity' =>
            array(
                '@id' =>
                    $this->data['canonical_url']
                    . '#product',
            ),
    );

    /*
    |--------------------------------------------------------------------------
    | Primary image
    |--------------------------------------------------------------------------
    |
    | Добавляем только настоящее изображение товара.
    | Placeholder не объявляем изображением самого продукта.
    |
    */

    if ($productSeoImage !== null) {
        $imageUrl =
            preg_match(
                '#^https?://#i',
                $productSeoImage
            )
                ? $productSeoImage
                : $this->site_url
                    . '/'
                    . ltrim(
                        $productSeoImage,
                        '/'
                    );

        $schema['primaryImageOfPage'] =
            array(
                '@type' =>
                    'ImageObject',

                '@id' =>
                    $this->data['canonical_url']
                    . '#primaryimage',

                'url' =>
                    $imageUrl,

                'contentUrl' =>
                    $imageUrl,
            );
    }

    return $schema;
}
private function buildProductSchemaGraph(
    $productSchema,
    $breadcrumbSchema,
    $itemPageSchema
) {
    return array(
        '@context' =>
            'https://schema.org',

        '@graph' =>
            array(
                $itemPageSchema,
                $productSchema,
                $breadcrumbSchema,
            ),
    );
}

    public function products_category_parent()
    {

        check_if_POST();
        $pages = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);

        $category = $this->categories_model->get_category_by_id($this->clang, $_POST['category_id']);

        $categories_id[] = $_POST['category_id'];
        $children = $this->categories_model->get_category_children($this->clang, $_POST['category_id']);

        if (!empty($children)) {
            foreach ($children as $child) {
                $categories_id[] = $child->id;
                $ch = $this->categories_model->get_category_children($this->clang, $child->id);
                if (!empty($ch)) {
                    $child->children = $ch;
                    foreach ($ch as $c) {
                        $categories_id[] = $c->id;
                    }
                }
            }
            $this->data['filter_category'] = $children;
            $this->data['parent_category'] = true;
        }

        $pagination_nr = !empty($_GET['page']) ? $_GET['page'] : 1;
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        $product_all = $this->products_model->get_all_category_products($categories_id);
        $products = $this->products_model->get_category_products_pag($this->clang, $categories_id, $start, $this->per_page);
        $count = count($product_all);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        $string = 'sort=' . $_GET['sort'] . '&';

        $uri_parts[0] = str_replace("products/category-parent", $pages->uri . '/' . $category->uri, $uri_parts[0]);
        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
        $this->data['paginator'] = $paginator;
        $this->data['products'] = $products;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $html = $this->load->view('pages/catalog/_filterproducts', $this->data, true);

        echo $html;
    }

    public function products_brand()
    {

        check_if_POST();
        $pages = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);

        $brand = $this->brands_model->get_brand_by_id($this->clang, $_POST['brand_id']);

        $categories_id = [];
        if (!empty($_POST['categories'])) $categories_id = $_POST['categories'];


        $pagination_nr = !empty($_GET['page']) ? $_GET['page'] : 1;
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;


        $product_all = $this->products_model->get_all_products_brands($brand->id, $categories_id);
        $products = $this->products_model->get_products_pag_brands($this->clang, $brand->id, $start, $this->per_page, $categories_id);
        $count = count($product_all);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        $string = 'sort=' . $_GET['sort'] . '&';

        $uri_parts[0] = str_replace("products/brands", 'katalog/' . $brand->uri, $uri_parts[0]);
        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
        $this->data['paginator'] = $paginator;
        $this->data['products'] = $products;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $html = $this->load->view('pages/catalog/_filterproducts', $this->data, true);

        $count = str_replace('{count}', $count, FOUND_PRODUCTS);
        echo json_encode(array('count' => $count, 'html' => $html));
    }

public function variable()
{
    check_if_POST();

    $productId = isset($_POST['product_id'])
        ? (int) $_POST['product_id']
        : 0;

    $variableId = isset($_POST['variable'])
        ? (int) $_POST['variable']
        : 0;

    if ($productId <= 0 || $variableId <= 0) {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(422)
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'Некорректный товар или вариант.'
            ]));
    }

    $variableData = $this->products_model
        ->get_product_variable_by_id(
            $this->clang,
            $variableId,
            $productId
        );

    $info = $this->db
        ->select(
            'id,
             product_id,
             discount_price,
             SKU,
             price,
             VolumeVar,
             priceWH,
             barcode,
             qty,
             qtyWH,
             color,
             colorRU,
             colorRO,
             titleRU,
             titleRO'
        )
        ->where('id', $variableId)
        ->where('product_id', $productId)
        ->where('isShown', 1)
        ->get('products_variable')
        ->row();

    if (empty($info)) {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'Вариант товара не найден.'
            ]));
    }

    $html = '';

    if (!empty($variableData)) {
        $html = $this->load->view(
            'pages/catalog/variable_img',
            $variableData,
            true
        );
    }

    $isB2B = !empty($_SESSION['isb2b']);

    $currentPrice = $isB2B
        ? (float) $info->priceWH
        : (float) $info->price;

    $discountPrice = $isB2B
        ? 0
        : (float) $info->discount_price;

    $price = $this->load->view(
        'pages/catalog/variable_price',
        [
            'discount_price' => $discountPrice,
            'price' => $currentPrice
        ],
        true
    );

    $availableQuantity = $isB2B
        ? (int) $info->qtyWH
        : (int) $info->qty;

    $variantTitle = '';

    if ($this->clang === 'ro' && !empty($info->colorRO)) {
        $variantTitle = trim((string) $info->colorRO);
    } elseif (!empty($info->colorRU)) {
        $variantTitle = trim((string) $info->colorRU);
    } elseif (!empty($info->color)) {
        $variantTitle = trim((string) $info->color);
    } elseif (!empty($info->VolumeVar)) {
        $variantTitle = trim((string) $info->VolumeVar);
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => 'ok',
            'html' => $html,
            'price' => $price,

            'info' => [
                'id' => (int) $info->id,
                'SKU' => (string) $info->SKU,
                'barcode' => (string) $info->barcode,
                'VolumeVar' => (string) $info->VolumeVar,
                'variant_title' => $variantTitle,
                'available' => $availableQuantity > 0,
                'quantity' => $availableQuantity,
                'price' => $discountPrice > 0
                    ? $discountPrice
                    : $currentPrice,
                'regular_price' => $currentPrice
            ]
        ]));
}

    public function search_pharm_product()
    {
        check_if_POST();
        foreach ($_POST as $index => $item) {
            $post[$index] = $this->input->post($index);
        }
        if (empty($post['search_in'])) $post['search_in'] = '';
        if (empty($post['region_popup'])) $post['region_popup'] = '';
        if (empty($post['city_popup'])) $post['city_popup'] = '';
        if (empty($post['sector_popup'])) $post['sector_popup'] = '';

        $product = $this->products_model->get_product_by_id($this->clang, $post['prod_id']);
        $file = realpath('application') . '/libraries/rests/' . $product->SKU . '/' . $product->SKU . '.json';
        $pharmacy_rests = array();
        if (file_exists($file)) {
            $pharmacy_rests = file_get_contents($file);
            $pharmacy_rests = json_decode($pharmacy_rests);
            foreach ($pharmacy_rests as $key => $pharmacy_rest) {
                if ($pharmacy_rest->rest == 0) {
                    unset($pharmacy_rests[$key]);
                } else {
                    $pharmacy_rests[$key]->info = $this->pharmacies_model->get_pharmacy_info_prod($this->clang, $pharmacy_rest->id_pharmacy, $post);
                }
            }
        }

        $map_json = (object)array();
        $map_json->type = 'FeatureCollection';
        $map_json->features = array();
        if (!empty($pharmacy_rests)) {
            foreach ($pharmacy_rests as $key => $pharmacy) {
                if (!empty($pharmacy->info->lat)) {
                    $pharmacy->title = "<h3 class='map-blocks-presence-popup__title'>" . $pharmacy->info->title . "</h3>";
                    $pharmacy->body = "<div class='map-blocks-presence-popup__info'>
                                        <div class='map-blocks-presence-popup__row'>
                                            <div class='map-blocks-presence-popup__images'>
                                                <img src='/assets/img/icon/time-work.svg'>
                                            </div>
                                            <div class='map-blocks-presence-popup__text'>
                                                <p class='map-blocks-presence-popup__label'>" . WORKING_MODE . "</p>
                                                <div class='map-blocks-presence-popup__time'>" . $pharmacy->info->time . "</div>
                                            </div>
                                        </div>
                                        <div class='map-blocks-presence-popup__row'>
                                            <div class='map-blocks-presence-popup__images'>
                                                <img src='/assets/img/icon/tel.svg'>
                                            </div>
                                            <div class='map-blocks-presence-popup__text'>
                                                <p class='map-blocks-presence-popup__label'>" . PHONE . "</p>
                                                <a href='tel:" . $pharmacy->info->phone . "' class='map-blocks-presence-popup__tel'>" . $pharmacy->info->phone . "</a>
                                            </div>
                                        </div>
                                    </div>";
                    $map_json->features[] = array(
                        'type' => 'Feature',
                        'id' => $pharmacy->info->id,
                        'geometry' => array('type' => 'Point', 'coordinates' => array((float)$pharmacy->info->lat, (float)$pharmacy->info->lng)),
                        'properties' => array('balloonContentHeader' => $pharmacy->title, 'balloonContentBody' => $pharmacy->body, 'balloonContentFooter' => ''),

                    );
                } else {
                    unset($pharmacy_rests[$key]);
                }
            }
        }

        $map_json = json_encode($map_json);
        $data['map_json'] = $map_json;

        $data['pharmacies'] = $pharmacy_rests;
        echo $this->load->view('pages/catalog/_search_pharmacy', $data, true);
    }

    public function filter_index()
    {
        check_if_POST();
        $pages = $this->menu_model->get_page_data_by_id($this->page_id, $this->clang);

        $filters_get = explode('&', $_POST['filter']);
        $features = array();
        $page = 1;
        $sort = 1;
        foreach ($filters_get as $fl) {
            $filters[] = explode('=', $fl);
        }
        foreach ($filters as $key => $filter) {
            if ($filter[0] == 'category_id') {
                $category_id = $filter[1];
            } elseif ($filter[0] == 'sort') {
                $sort = $filter[1];
            } elseif ($filter[0] == 'price_min') {
                $price_min = $filter[1];
            } elseif ($filter[0] == 'price_max') {
                $price_max = $filter[1];
            } elseif ($filter[0] == 'view') {
                $view = $filter[1];
            } elseif ($filter[0] == 'fl%5B%5D') {
                $features[] = $filter[1];
            }
        }
        if ($sort == 'undefined') $sort = 'sorder ASC';
        $this->per_page = $view;
        $pagination_nr = $page;
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;
        $product_all = $this->products_model->get_all_products_index_filter($category_id, $sort, $price_min, $price_max, $features);
        $products = $this->products_model->get_products_pag_index_filter($this->clang, $start, $this->per_page, $category_id, $sort, $price_min, $price_max, $features);
        $count = count($product_all);

        $category = $this->categories_model->get_category_by_id($this->clang, $category_id);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_POST['filter'] = str_replace("page=", "page_prev=", $_POST['filter']);
        $string = $_POST['filter'] . '&';

        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->menu['all'][2]->uri . '/' . $category->uri;

        $urlPattern = $this->data['page_url'] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);

        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $html = $this->load->view('pages/catalog/_filterproducts', $this->data, true);

        if (!empty($features)) {
            $fl_active = $this->products_model->filter_value($this->clang, $features);
            $filter_active = $this->load->view('pages/catalog/_filteractive', array('price_min' => $price_min, 'price_max' => $price_max, 'filter' => $fl_active), true);
            $filter_active_top = $this->load->view('pages/catalog/_filteractive_top', array('price_min' => $price_min, 'price_max' => $price_max, 'filter' => $fl_active), true);
        } else {
            $filter_active = '';
            $filter_active_top = '';
        }

        $response['html'] = $html;
        $response['filter_active'] = $filter_active;
        $response['filter_active_top'] = $filter_active_top;
        $response['count'] = $count;

        echo json_encode($response);
    }

    public function filter_category()
    {
        check_if_POST();
        $pages = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        $categories_id = array();
        if (!empty($_GET['category_id'])) {
            $category = $this->categories_model->get_category_by_id($this->clang, $_GET['category_id']);
            $categories_id[] = $category->id;
            $children = $this->categories_model->get_category_children($this->clang, $category->id);
            if (!empty($children)) {
                $this->data['children'] = $children;
                foreach ($children as $child) {
                    $categories_id[] = $child->id;
                    $ch = $this->categories_model->get_category_children($this->clang, $child->id);
                    if (!empty($ch)) {
                        $child->children = $ch;
                        foreach ($ch as $c) {
                            $categories_id[] = $c->id;
                        }
                    }
                }
            }
        }

        $pagination_nr = 1;
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;
        $product_all = $this->products_model->get_all_products_index_filter($categories_id);
        $products = $this->products_model->get_products_pag_index_filter($this->clang, $start, $this->per_page, $categories_id);
        $count = count($product_all);


        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_POST['filter'] = str_replace("page=", "page_prev=", $_POST['filter']);
        $string = $_POST['filter'] . '&';

        if (!empty($category)) {
            $uri_parts[0] = str_replace("products/filter_category", $pages->uri . '/' . $category->uri, $uri_parts[0]);
        } else {
            $uri_parts[0] = str_replace("products/filter_category", $pages->uri, $uri_parts[0]);
        }

        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;

        $html = $this->load->view('pages/catalog/_filterproducts', $this->data, true);
        $count = str_replace('{count}', $count, FOUND_PRODUCTS);
        echo json_encode(array('count' => $count, 'html' => $html));
    }

    public function filter_brands()
    {
        check_if_POST();
        $pages = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        $categories_id = array();

        if (!empty($_GET['category'])) {
            $categories_id = $_GET['category'];
        }

        $pagination_nr = 1;
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;
        $product_all = $this->products_model->get_all_products_index_filter($categories_id);
        $products = $this->products_model->get_products_pag_index_filter($this->clang, $start, $this->per_page, $categories_id);
        $count = count($product_all);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_POST['filter'] = str_replace("page=", "page_prev=", $_POST['filter']);
        $string = $_POST['filter'] . '&';

        if (!empty($brand)) {
            $uri_parts[0] = str_replace("products/filter_category", $pages->uri . '/' . $brand->uri, $uri_parts[0]);
        } else {
            $uri_parts[0] = str_replace("products/filter_category", $pages->uri, $uri_parts[0]);
        }

        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;

        $html = $this->load->view('pages/catalog/_filterproducts', $this->data, true);
        $count = str_replace('{count}', $count, FOUND_PRODUCTS);
        echo json_encode(array('count' => $count, 'html' => $html));
    }

    public function filter_bests()
    {
        check_if_POST();
        $pages = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        $pagination_nr = 1;
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;
        $product_all = $this->products_model->get_all_products_best_sellers();
        $products = $this->products_model->get_products_pag_best_sellers($this->clang, $start, $this->per_page);
        $count = count($product_all);

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        $_POST['filter'] = str_replace("page=", "page_prev=", $_POST['filter']);
        $string = $_POST['filter'] . '&';

        $uri_parts[0] = str_replace("products/filter_category", $pages->uri . '/reduceri'  , $uri_parts[0]);

        $urlPattern = $uri_parts[0] . '?' . $string . 'page=(:num)';
        $paginator = new Paginator(
            $count,
            $this->per_page,
            $pagination_nr,
            $urlPattern
        );
        $paginator->setMaxPagesToShow(5);
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
        $this->data['paginator'] = $paginator;

        $this->data['products'] = $products;
        $this->data['products_count'] = $count;

        $html = $this->load->view('pages/catalog/_filterproducts', $this->data, true);
        $count = str_replace('{count}', $count, FOUND_PRODUCTS);
        echo json_encode(array('count' => $count, 'html' => $html));
    }
}
