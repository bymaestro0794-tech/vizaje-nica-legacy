<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use JasonGrimes\Paginator;

class Articles extends FrontEndController
{
    private $page_id;
    private $per_page;

    public function __construct() {
        parent::__construct();
        $this->page_id = 37;
        $this->per_page = 20;
        $this->load->model('articles_model');
    }

    private function _init_seo_data($page) {
        $this->data['page_title'] = (!empty($page->seo_title)) ? $page->seo_title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = $page->text;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $page->seo_title;
        $this->data['breadcrumbs'] = $this->breadcrumbs;
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

        $articles = $this->articles_model->get_articles($this->clang);
        $this->data['articles'] = $articles;

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;
        $articles_all = $this->articles_model->get_all_articles();
        $articles = $this->articles_model->get_pag_articles($this->clang, $start, $this->per_page);
        $count = count($articles_all);


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
        $this->data['articles'] = $articles;
        $this->data['products_count'] = $count;
        $this->data['show_from'] = $start;
        $this->data['show_to'] = $start + $this->per_page;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/articles/index';
        $this->data['page'] = $page;

        $this->_render();
    }

    public function items() {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $article = $this->articles_model->get_article_data($this->clang, $this->uri3);
        if (empty($article)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $articles = $this->articles_model->get_articles_more($this->clang);
        $this->data['articles'] = $articles;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO . '/' . $article->uriRO,
            'ru' => '' . $page->uriRU . '/' . $article->uriRU,
        );

        $this->loadOGImgData($article);
        $this->_init_seo_data($article);

        $this->data['inner_view'] = 'pages/articles/items';
        $this->data['page'] = $page;
        $this->data['article'] = $article;

        $this->_render();
    }
}
