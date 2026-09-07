<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use JasonGrimes\Paginator;

class Offers extends FrontEndController
{
    private $page_id;
    private $per_page;

    public function __construct() {
        parent::__construct();
        $this->page_id = 9;
        $this->per_page = 20;
        $this->load->model('offers_model');
        $this->load->model('products_model');
        $this->load->model('brands_model');

        $this->data['body_class'] = 'offers';
    }

    private function _init_seo_data($page) {
        $this->data['page_title'] = (!empty($page->title)) ? $page->title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = (!empty($page->text)) ? $page->text : $page->title;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $page->seo_title;
        $this->data['breadcrumbs'] = $this->breadcrumbs;
        $this->data['class_page'] = '_webp';
    }

    private function loadOGImgData($page, $dir = 'menu') {
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

    public function index() {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        foreach(language(true) as $lang){
            $array = $lang.'_urls';
            $uri = 'uri'.strtoupper($lang);
            $this->{$array}[] = $page->{$uri};
        }

        $this->data['offers'] = $this->offers_model->get_offers($this->clang);


        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/offers/index';
        $this->data['page'] = $page;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );

        $this->_render();
    }

    public function items() {

        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $offer = $this->offers_model->get_offers_data_by_uri($this->clang, $this->uri3);
        if (empty($offer)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        foreach(language(true) as $lang){
            $array = $lang.'_urls';
            $uri = 'uri'.strtoupper($lang);
            $this->{$array}[] = $page->{$uri};
        }

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        $product_all = $this->products_model->get_all_products_offers($offer->id);
        $products = $this->products_model->get_products_offers_pag($this->clang, $start, $this->per_page, $offer->id);
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

        if (!empty($product_all)) {
//                Features Start
            $this->data['brands'] = $this->brands_model->get_brands_category($product_all);

            $price_max = $this->products_model->get_all_products_price_max($product_all);
            $this->data['price_max'] = $price_max->price;
            $price_min = $this->products_model->get_all_products_price_min($product_all);
            $this->data['price_min'] = $price_min->price;
//                Features END
        }

        $this->loadOGImgData($offer);
        $this->_init_seo_data($offer);

        $this->data['inner_view'] = 'pages/offers/items';
        $this->data['page'] = $page;
        $this->data['offer'] = $offer;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO . '/' . $offer->uriRO,
            'ru' => '' . $page->uriRU . '/' . $offer->uriRU,
        );

        $this->_render();
    }

    public function filter_offer()
    {
        check_if_POST();

        if (!empty($_GET['offer_id'])){
            $offer = $this->offers_model->get_offers_data_by_id($this->clang, $_GET['offer_id']);
        } else {
            die();
        }

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        $product_all = $this->products_model->get_all_products_offers($offer->id);
        $products = $this->products_model->get_products_offers_pag($this->clang, $start, $this->per_page, $offer->id);
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

        $html = $this->load->view('pages/catalog/_filterproducts', $this->data, true);
        $count = str_replace('{count}', $count, FOUND_PRODUCTS);

        echo json_encode(array('count'=>$count,'html'=>$html));

    }
    
    private function get_niche_sale_config()
    {
        $isRomanian =
            $this->lclang === 'ro';

        return array(
            'title' => $isRomanian
                ? 'Parfumerie de nișă'
                : 'Нишевая парфюмерия',

            'subtitle' => $isRomanian
                ? 'Reduceri de la 30% până la 50%'
                : 'Скидки от 30% до 50%',

            'seo_title' => $isRomanian
                ? 'Parfumerie de nișă cu reduceri de până la 50% | Vizaje-Nica'
                : 'Нишевая парфюмерия со скидками до 50% | Vizaje-Nica',

            'seo_description' => $isRomanian
                ? 'Descoperă parfumuri de nișă selectate cu reduceri de la 30% până la 50% la Vizaje-Nica.'
                : 'Откройте избранную нишевую парфюмерию со скидками от 30% до 50% в Vizaje-Nica.',

            'desktop_image' =>
                '/app/img/niche-sale/niche-sale-desktop.webp',

            'mobile_image' =>
                '/app/img/niche-sale/niche-sale-mobile.webp',

            'brand_titles' => array(
                'Ungaro',
                'Ojar',
                'Moresque',
                'Jacques Zolty',
                'Chabaud',
                'ByBozo',
                'Blend Oud',
            ),

            'discount_min' => 30,
            'discount_max' => 50,

            'public_path' => $isRomanian
                ? '/ro/parfumerie-de-nisa'
                : '/ru/nishevaya-parfyumeriya',
        );
    }
    private function get_niche_sale_selected_brands(
        array $allowedBrandIds
    ) {
        $selectedBrandIds = isset($_GET['brand'])
            ? $_GET['brand']
            : array();

        if (!is_array($selectedBrandIds)) {
            $selectedBrandIds = array(
                $selectedBrandIds
            );
        }

        $selectedBrandIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $selectedBrandIds
                    ),
                    static function ($brandId) {
                        return $brandId > 0;
                    }
                )
            )
        );

        return array_values(
            array_intersect(
                $selectedBrandIds,
                $allowedBrandIds
            )
        );
    }
    private function get_niche_sale_selected_discount()
    {
        $selectedDiscount = isset(
            $_GET['discount']
        )
            ? (string) $_GET['discount']
            : '';

        $allowedDiscounts = array(
            '30-39',
            '40-49',
            '50',
        );

        return in_array(
            $selectedDiscount,
            $allowedDiscounts,
            true
        )
            ? $selectedDiscount
            : '';
    }
    private function get_niche_sale_pagination_url(
        $publicPath
    ) {
        $query = $_GET;

        unset(
            $query['page']
        );

        $queryString =
            http_build_query($query);

        return $publicPath
            . (
                $queryString !== ''
                    ? '?' . $queryString . '&'
                    : '?'
            )
            . 'page=(:num)';
    }
    private function prepare_niche_sale_brands(
        array $brands,
        array $baseProducts
    ) {
        $brandCounts = array();

        foreach ($baseProducts as $product) {
            $brandId = isset($product->brand_id)
                ? (int) $product->brand_id
                : 0;

            if ($brandId <= 0) {
                continue;
            }

            if (!isset($brandCounts[$brandId])) {
                $brandCounts[$brandId] = 0;
            }

            $brandCounts[$brandId]++;
        }

        foreach ($brands as $brand) {
            $brandId = isset($brand->id)
                ? (int) $brand->id
                : 0;

            $brand->count = isset(
                $brandCounts[$brandId]
            )
                ? (int) $brandCounts[$brandId]
                : 0;
        }

        return $brands;
    }
    public function niche_sale()
    {
        $config =
            $this->get_niche_sale_config();

        $brands =
            $this->brands_model
                ->get_niche_sale_brands(
                    $config['brand_titles']
                );

        if (empty($brands)) {
            $brands = array();
        }

        $allowedBrandIds = array_values(
            array_filter(
                array_map(
                    static function ($brand) {
                        return isset($brand->id)
                            ? (int) $brand->id
                            : 0;
                    },
                    $brands
                )
            )
        );

        $selectedBrandIds =
            $this->get_niche_sale_selected_brands(
                $allowedBrandIds
            );

        $selectedDiscount =
            $this->get_niche_sale_selected_discount();

        $paginationNumber = isset($_GET['page'])
            ? (int) $_GET['page']
            : 1;

        if ($paginationNumber < 1) {
            $paginationNumber = 1;
        }

        $start =
            ($paginationNumber - 1)
            * $this->per_page;

        /*
        * Полный список нужен для количества
        * возле быстрых бренд-фильтров.
        */
        $baseProducts =
            $this->products_model
                ->get_all_niche_sale_products(
                    $allowedBrandIds,
                    array(),
                    '',
                    $config['discount_min'],
                    $config['discount_max']
                );

        $brands =
            $this->prepare_niche_sale_brands(
                $brands,
                $baseProducts
            );

        /*
        * Уже отфильтрованный список.
        */
        $filteredProducts =
            $this->products_model
                ->get_all_niche_sale_products(
                    $allowedBrandIds,
                    $selectedBrandIds,
                    $selectedDiscount,
                    $config['discount_min'],
                    $config['discount_max']
                );

        $productsCount =
            count($filteredProducts);

        $products =
            $this->products_model
                ->get_niche_sale_products_pag(
                    $this->clang,
                    $allowedBrandIds,
                    $selectedBrandIds,
                    $selectedDiscount,
                    $config['discount_min'],
                    $config['discount_max'],
                    $start,
                    $this->per_page
                );

        $paginator = new Paginator(
            $productsCount,
            $this->per_page,
            $paginationNumber,
            $this->get_niche_sale_pagination_url(
                $config['public_path']
            )
        );

        $paginator->setMaxPagesToShow(5);

        $this->data['body_class'] =
            'offers catalog_page niche_sale_page';

        $this->data['page_title'] =
            $config['seo_title'];

        $this->data['page_name'] =
            $config['title'];

        $this->data['text_for_layout'] =
            $config['title'];

        $this->data['keywords_for_layout'] = '';

        $this->data['description_for_layout'] =
            $config['seo_description'];

        $this->data['otitle'] =
            $config['seo_title'];

        $this->data['canonical_url'] =
            $config['public_path'];

        $this->data['og_img'] =
            $config['desktop_image'];

        $this->data['og_img_width'] = 1900;
        $this->data['og_img_height'] = 720;

        $this->data['niche_sale'] =
            $config;

        $this->data['brands'] =
            $brands;

        $this->data['selected_brand_ids'] =
            $selectedBrandIds;

        $this->data['selected_discount'] =
            $selectedDiscount;

        $this->data['products'] =
            $products;

        $this->data['products_count'] =
            $productsCount;

        $this->data['show_from'] =
            $start;

        $this->data['show_to'] =
            min(
                $productsCount,
                $start + $this->per_page
            );

        $this->data['page_url'] =
            $config['public_path'];

        $this->data['paginator'] =
            $paginator;

        $this->data['lang_urls'] = array(
            'ru' => 'nishevaya-parfyumeriya',
            'ro' => 'parfumerie-de-nisa',
        );

        $this->data['inner_view'] =
            'pages/offers/niche_sale';

        $this->_render();
    }
}
