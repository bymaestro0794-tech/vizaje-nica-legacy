<?php
defined('BASEPATH') or exit('No direct script access allowed');

use JasonGrimes\Paginator;

class Brands extends FrontEndController
{
    private $page_id;
    private $per_page;

    public function __construct()
    {
        parent:: __construct();
        $this->page_id = 26;
        $this->per_page = 20;
        $this->load->model('products_model');
        $this->load->model('brands_model');
        $this->load->model('categories_model');
    }

    private function _load_main_page($page)
    {
        $title = !empty($page->title) ? $page->title : "";
        $this->data['page_title'] = !empty($page->seo_title) ? $page->seo_title : $title;
        $this->data['h1_title'] = !empty($page->h1_title) ? $page->h1_title : $title;
        $this->data['breadcrumb_title'] = !empty($page->breadcrumb_title) ? $page->breadcrumb_title : $title;
        $this->data['page_name'] = $this->data['h1_title'];
        $this->data['text_for_layout'] = (!empty($page->text)) ? $page->text : $page->title;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $this->data['page_title'];
        $this->data['breadcrumbs'] = $this->breadcrumbs;
        $this->data['class_page'] = '_webp';
    }

    private function loadOGImgData($page = false)
    {
        if (empty($page)) throw_on_404();

        if (!empty($page->img)) {
            $this->data['og_img'] = newthumbs($page->img, 'menu', 500, 300, 'og500x300x1', 1);
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

        if (!empty($_GET['search-brand'])){
            $brands = $this->brands_model->get_pag_brands_search($this->clang, $_GET['search-brand']);
            $count = count($brands);
        } else {
            $brands = $this->brands_model->get_pag_brands($this->clang);
            $count = count($brands);
        }

        $brands_title = array();
        foreach ($brands as $brand){
            $firstChar = substr($brand->title, 0, 1);
            $brands_title[mb_strtoupper($firstChar)][] = $brand;

            if (is_numeric($firstChar)) $this->data['numeric'] = '1';
        }
        $this->data['brands'] = $brands_title;
        $this->data['brands_count'] = $count;

        $this->data['body_class'] = '_brands-page';

        $this->data['lang_urls'] = array(
            'ro' => '/' . $page->uriRO,
            'ru' => '/' . $page->uriRU,
        );
        $this->data['inner_view'] = 'pages/brands/index';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();

    }

    public function items()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $brand = $this->brands_model->get_brand_by_uri($this->clang, $this->uri3);
        if (empty($brand)) throw_on_404();

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;
        $product_all = $this->products_model->get_all_products_brands($brand->id);
        $products = $this->products_model->get_products_pag_brands($this->clang, $brand->id, $start, $this->per_page);
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
        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;
        $this->data['paginator'] = $paginator;
        $this->data['products'] = $products;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $this->data['brand'] = $brand;


        $this->data['lang_urls'] = array(
            'ro' => '/' . $page->uriRO . '/' . $brand->uri,
            'ru' => '/' . $page->uriRU . '/' . $brand->uri,
        );
        $this->data['inner_view'] = 'pages/brands/items';
        $this->loadOGImgData($brand);
        $this->_load_main_page($brand);

        $this->_render();

    }
}
