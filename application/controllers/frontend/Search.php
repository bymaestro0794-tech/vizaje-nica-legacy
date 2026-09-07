<?php
defined('BASEPATH') or exit('No direct script access allowed');

use JasonGrimes\Paginator;

class Search extends FrontEndController
{
    private $page_id;
    private $per_page;

    public function __construct()
    {
        parent:: __construct();
        $this->page_id = 21;
        $this->per_page = 32;
        $this->load->model('products_model');
        $this->load->model('brands_model');
        $this->load->model('search_model');
        $this->load->model('categories_model');
        $this->load->library('spellcorrector');

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

    private function loadOGImgData($page = false)
    {
        if (empty($page)) throw_on_404();

        if (!empty($page->img)) {
            $this->data['og_img'] = newthumbs($page->img, 'menu', 500, 300, 'og500x300x1', 1);
            $this->data['og_img_width'] = 500;
            $this->data['og_img_height'] = 300;
        } else {
            $this->data['og_img'] = newthumbs('og_img.png', 'i', 214, 51, 'og214x51x1', 1);
            $this->data['og_img_width'] = 214;
            $this->data['og_img_height'] = 51;
        }
    }

    public function index()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        if (empty($_GET['search'])) $_GET['search'] = '';

        $pagination_nr = @$_GET['page'];
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;

        $product_all = $this->products_model->get_all_products_search($_GET['search']);
        $products = $this->products_model->get_products_pag_search($this->clang, $start, $this->per_page, $_GET['search']);
        $count = count($product_all);

        if (empty($products)) {
            $search = $this->input->get('search', TRUE);
            // --- 2. Поиск в product_search ---
            $productsID = $this->search_model->search_ids($search);

            // --- 4. Switch раскладки ---
            if (empty($productsID)) {

                $variants = [
                    switcher_ru($search),
                    switcher_en($search),
                    switcher_syntax_ru($search),
                    switcher_syntax_en($search),
                ];

                foreach ($variants as $variant) {
                    if (!$variant) continue;
                    $productsID = $this->search_model->search_ids($variant);
                    if (!empty($productsID)) {
                        break;
                    }
                }
            }

            // --- 3. SpellCorrector ---
            if (empty($productsID)) {
                $words = trim($search);
                $words = explode(" ", $words);
                $count = count($words);
                $correct = " ";
                for ($i = 0; $i < $count; $i++) {
                    $correct .= $this->spellcorrector->{'correct' . $this->lclang}($words[$i]).' ';
                }
                $correct = trim($correct);

                $productsID = $this->search_model->search_ids($correct);
            }

            // --- 4. Switch раскладки ---
            if (empty($productsID)) {

                $variants = [
                    switcher_ru($search),
                    switcher_en($search),
                    switcher_syntax_ru($search),
                    switcher_syntax_en($search),
                ];

                foreach ($variants as $variant) {
                    if (!$variant) continue;

                    $words = trim($variant);
                    $words = explode(" ", $words);
                    $count = count($words);
                    $correct = " ";
                    for ($i = 0; $i < $count; $i++) {
                        $correct .= $this->spellcorrector->{'correct' . $this->lclang}($words[$i]).' ';
                    }
                    $correct = trim($correct);

                    $productsID = $this->search_model->search_ids($correct);

                    if (!empty($productsID)) {
                        break;
                    }
                }
            }

            // --- 5. Получаем товары ---
            if (!empty($productsID)) {
                $products = $this->products_model->get_search_productsID($this->clang, $productsID);
            }
            $count = count($products);
        }

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        $string = 'search=' . $_GET['search'] . '&';

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
            'ro' => '/' . $page->uriRO.'search=' . $_GET['search'] ,
            'ru' => '/' . $page->uriRU.'search=' . $_GET['search'] ,
        );
        $this->data['inner_view'] = 'pages/catalog/search';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();

    }

    public function search()
    {
        check_if_POST();

        foreach ($_POST as $index => $post_data) {
            $post[$index] = $this->input->post($index, TRUE);
        }

        $this->data['search'] = $post['search'];

        if (strlen($this->data['search']) >= 3) {
            $products = $this->products_model->get_search_products($this->clang, $post['search']);
            $this->data['products_new'] = $products;

            $categories = $this->categories_model->get_search_categories($this->clang, $post['search']);
            $this->data['categories'] = $categories;

            $brands_nav = $this->brands_model->get_search_brands($this->clang, $post['search']);
            $this->data['brands_nav'] = $brands_nav;
        } else {
            $this->data['success'] = 2;
        }
        $response = $this->load->view('layouts/pages/search', $this->data, true);
        echo $response;
    }


    public function searchSpell()
    {
        check_if_POST();

        $search = $this->input->post('search', TRUE);
        $this->data['search'] = $search;

        if (strlen($search) < 3) {
            $this->data['success'] = 2;
            echo $this->load->view('layouts/pages/search', $this->data, true);
            return;
        }

        // --- 1. Обычный поиск ---
        $products = $this->products_model->get_search_products($this->clang, $search);
        $categories = $this->categories_model->get_search_categories($this->clang, $search);
        $brands_nav = $this->brands_model->get_search_brands($this->clang, $search);

        if (empty($products) && empty($categories) && empty($brands_nav)) {

            // --- 2. Поиск в product_search ---
            $productsID = $this->search_model->search_ids($search);

            // --- 4. Switch раскладки ---
            if (empty($productsID)) {

                $variants = [
                    switcher_ru($search),
                    switcher_en($search),
                    switcher_syntax_ru($search),
                    switcher_syntax_en($search),
                ];

                foreach ($variants as $variant) {
                    if (!$variant) continue;
                    $productsID = $this->search_model->search_ids($variant);
                    if (!empty($productsID)) {
                        break;
                    }
                }
            }

            // --- 3. SpellCorrector ---
            if (empty($productsID)) {
                $words = trim($search);
                $words = explode(" ", $words);
                $count = count($words);
                $correct = " ";
                for ($i = 0; $i < $count; $i++) {
                    $correct .= $this->spellcorrector->{'correct' . $this->lclang}($words[$i]).' ';
                }
                $correct = trim($correct);


                $productsID = $this->search_model->search_ids($correct);
            }

            // --- 4. Switch раскладки ---
            if (empty($productsID)) {

                $variants = [
                    switcher_ru($search),
                    switcher_en($search),
                    switcher_syntax_ru($search),
                    switcher_syntax_en($search),
                ];

                foreach ($variants as $variant) {
                    if (!$variant) continue;

                    $words = trim($variant);
                    $words = explode(" ", $words);
                    $count = count($words);
                    $correct = " ";
                    for ($i = 0; $i < $count; $i++) {
                        $correct .= $this->spellcorrector->{'correct' . $this->lclang}($words[$i]).' ';
                    }
                    $correct = trim($correct);

                    $productsID = $this->search_model->search_ids($correct);

                    if (!empty($productsID)) {
                        break;
                    }
                }
            }


            // --- 5. Получаем товары ---
            if (!empty($productsID)) {
                $products = $this->products_model->get_search_productsID($this->clang, $productsID);
            }
        }

        $this->data['products_new'] = $products;
        $this->data['categories'] = $categories;
        $this->data['brands_nav'] = $brands_nav;

        echo $this->load->view('layouts/pages/search', $this->data, true);
    }
}
