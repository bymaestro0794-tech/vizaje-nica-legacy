<?php
defined('BASEPATH') or exit('No direct script access allowed');

use JasonGrimes\Paginator;

class Wishlist extends FrontEndController
{
    private $page_id;
    private $per_page;

    public function __construct()
    {
        parent:: __construct();
        $this->page_id = 17;
        $this->per_page = 25;
        $this->load->model('products_model');
        $this->load->model('brands_model');
        $this->load->model('categories_model');
    }

    private function _load_main_page($page)
    {
        $this->data['page_title'] = (!empty($page->seo_title)) ? $page->seo_title : "";
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

        $product_all = $this->products_model->get_wishlist_products($this->data['wishlist']);
        $products = $this->products_model->get_products_wishlist($this->clang, $this->data['wishlist'], $start, $this->per_page);
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
            'ro' => '/' . $page->uriRO,
            'ru' => '/' . $page->uriRU,
        );
        $this->data['inner_view'] = 'pages/catalog/wishlist';
        $this->loadOGImgData($page);
        $this->_load_main_page($page);

        $this->_render();

    }

    public function wishlist()
    {
        check_if_POST();

        $page = 1;
        $this->per_page = 99;
        $pagination_nr = $page;
        if (empty($pagination_nr) || $pagination_nr < 1) $pagination_nr = 1;
        $start = ($pagination_nr - 1) * $this->per_page;
        $product_all = $this->products_model->get_wishlist_products($this->data['wishlist']);
        $products = $this->products_model->get_products_wishlist($this->clang, $this->data['wishlist'], $start, $this->per_page);
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

        $response['html'] = $html;
        $response['count'] = $count;

        echo json_encode($response);

    }

    public function add($add_item = null)
    {
        check_if_POST();

        $post = $this->input->post();
        $product_id = $post['product_id'];
        $response['result'] = get_cookie('produse_wishlust');
        $response['status'] = 'ok';

        if (!empty($response['result'])) {
            $produse_wishlust = explode(', ', $response['result']);
            if (empty($produse_wishlust[0])) unset($produse_wishlust[0]);
            $response['count'] = count($produse_wishlust);

            if (in_array($product_id, $produse_wishlust)) {
                $response['result'] = str_replace(", " . $product_id, "", $response['result']);
                $response['result'] = str_replace($product_id, "", $response['result']);
                $cookie = array(
                    'name' => 'produse_wishlust',
                    'value' => $response['result'],
                    'expire' => 3600 * 24 * 7
                );
                set_cookie($cookie);
                $response['status'] = 'ok';
                if (!empty($this->data['client_info'])) {
                    $this->db->where('contact_id', $this->data['client_info']->id);
                    $this->db->where('product_id', $product_id);
                    $this->db->delete('wishlist_items');
                }
                $response['count'] = $response['count'] - 1;
            } else {
                $response['result'] = $response['result'] . ", " . $product_id;
                $cookie = array(
                    'name' => 'produse_wishlust',
                    'value' => $response['result'],
                    'expire' => 3600 * 24 * 7
                );
                set_cookie($cookie);
                $response['status'] = 'ok';
                if (!empty($this->data['client_info'])) {
                    $data = array(
                        'contact_id' => $this->data['client_info']->id,
                        'product_id' => $product_id,
                    );
                    $this->db->insert('wishlist_items', $data);
                }
                $response['count'] = $response['count'] + 1;
            }

        } else {
            $response['result'] = $product_id;
            $cookie = array(
                'name' => 'produse_wishlust',
                'value' => $response['result'],
                'expire' => 3600 * 24 * 7
            );
            set_cookie($cookie);
            $response['status'] = 'ok';
            if (!empty($this->data['client_info'])) {
                $data = array(
                    'contact_id' => $this->data['client_info']->id,
                    'product_id' => $product_id,
                );
                $this->db->insert('wishlist_items', $data);
            }
            $response['count'] = 1;
        }

        echo json_encode($response);
    }
}
