<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Beauty_selections extends FrontEndController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('beauty_selections_model');
        $this->load->model('products_model');
        $this->data['body_class'] = 'beauty-selections';
    }

    public function index()
    {
        $selections = $this->beauty_selections_model->get_public($this->clang);
        $title = $this->lclang === 'ro' ? 'Selecții de frumusețe' : 'Бьюти-подборки';

        $this->data['page_title'] = $title . ' | Vizaje-Nica';
        $this->data['page_name'] = $title;
        $this->data['h1_title'] = $title;
        $this->data['text_for_layout'] = $title;
        $this->data['keywords_for_layout'] = '';
        $this->data['description_for_layout'] = $title;
        $this->data['otitle'] = $this->data['page_title'];
        $this->data['breadcrumbs'] = array($this->_generate_bc_data($title));
        $this->data['selections'] = $selections;
        $this->data['lang_urls'] = array('ru' => '/ru/beauty-selections', 'ro' => '/ro/beauty-selections');
        $this->data['inner_view'] = 'pages/beauty_selections/index';
        $this->data['og_img'] = '/app/img/fav_icon.png';
        $this->data['og_img_width'] = 512;
        $this->data['og_img_height'] = 512;
        $this->_render();
    }

    public function item()
    {
        $selection = $this->beauty_selections_model->get_public_by_uri($this->clang, $this->uri3);
        if (empty($selection)) {
            throw_on_404();
        }

        $ids = array_column($this->beauty_selections_model->get_product_ids($selection->id), 'product_id');
        $products = empty($ids) ? array() : $this->products_model->get_products_wishlist($this->clang, $ids, 0, count($ids));
        $byId = array();
        foreach ($products as $product) {
            $byId[(int) $product->id] = $product;
        }
        $orderedProducts = array();
        foreach ($ids as $id) {
            if (isset($byId[(int) $id])) {
                $orderedProducts[] = $byId[(int) $id];
            }
        }

        $this->data['page_title'] = $selection->seo_title ?: $selection->title;
        $this->data['page_name'] = $selection->title;
        $this->data['h1_title'] = $selection->title;
        $this->data['text_for_layout'] = $selection->desc ?: $selection->title;
        $this->data['keywords_for_layout'] = $selection->seo_keywords ?: '';
        $this->data['description_for_layout'] = $selection->seo_desc ?: strip_tags((string) $selection->desc);
        $this->data['otitle'] = $this->data['page_title'];
        $this->data['breadcrumbs'] = array(
            $this->_generate_bc_data($this->lclang === 'ro' ? 'Selecții de frumusețe' : 'Бьюти-подборки', '/' . $this->uri1 . '/beauty-selections'),
            $this->_generate_bc_data($selection->title),
        );
        $this->data['selection'] = $selection;
        $this->data['products'] = $orderedProducts;
        $this->data['lang_urls'] = array('ru' => '/ru/beauty-selections/' . $selection->uriRU, 'ro' => '/ro/beauty-selections/' . $selection->uriRO);
        $this->data['inner_view'] = 'pages/beauty_selections/item';
        $this->data['og_img'] = !empty($selection->img) ? newthumbs($selection->img, 'beauty_selections', 500, 300, 'og500x300x1', 1) : '/app/img/fav_icon.png';
        $this->data['og_img_width'] = 500;
        $this->data['og_img_height'] = 300;
        $this->_render();
    }
}
