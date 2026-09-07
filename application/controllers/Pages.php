<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pages extends FrontEndController
{
    private $page_id;

    public function __construct()
    {
        parent::__construct();
        $this->page_id = 1;
    }

    private function _init_seo_data($page)
    {
        $this->data['page_title'] = (!empty($page->title)) ? $page->title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = $page->text;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = (!empty($page->seo_title)) ? $page->seo_title : "";
        foreach (language(true) as $lang) {
            $array = $lang . '_urls';
            $this->data['lang_urls'][$lang] = $this->{$array};
        }

        $this->data['breadcrumbs'] = $this->breadcrumbs;
    }

    private function loadOGImgData($page, $dir = 'menu')
    {
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

        $bc_title = !empty($page->breadcrumb_title) ? $page->breadcrumb_title : $page->title;
        $this->breadcrumbs[] = $this->_generate_bc_data($bc_title, $page->uri);

        foreach (language(true) as $lang) {
            $array = $lang . '_urls';
            $uri = 'uri' . strtoupper($lang);
            $this->{$array}[] = $page->{$uri};
        }
        $this->load->model('sliders_model');
        $this->data['sliders'] = $this->sliders_model->get_sliders_home($this->clang);

        $this->load->model('banners_model');
        $this->data['banners'] = $this->banners_model->get_banners_home($this->clang);

        // $this->data['products_sale'] = $this->products_model->get_products_home_sale($this->clang);

        // $this->data['products_order'] = $this->products_model->get_products_home_order($this->clang);

        // $this->data['categories_home'] = $this->categories_model->get_category_home($this->clang);
        
        $this->load->model('home_categories_model');

        $this->data['home_categories'] =
            $this->home_categories_model
                ->get_front_items(
                    $this->clang
                );

        $this->load->model(
            'home_brand_sections_model'
        );

        $this->data['brand_sections_after_new'] =
            $this->home_brand_sections_model
                ->get_front_sections(
                    $this->clang,
                    'after_new'
                );

        $this->data['brand_sections_after_sale'] =
            $this->home_brand_sections_model
                ->get_front_sections(
                    $this->clang,
                    'after_sale'
                );

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/main/index';
        $this->data['page'] = $page;
        $this->data['body_class'] = 'home_page';

        $this->_render();
    }

    public function about()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 4);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        foreach (language(true) as $lang) {
            $array = $lang . '_urls';
            $uri = 'uri' . strtoupper($lang);
            $this->{$array}[] = $page->{$uri};
        }
        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/main/about';
        $this->data['page'] = $page;

        $this->_render();
    }

    public function delivery()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 10);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $this->load->model('delivery_model');
        $this->data['delivery'] = $this->delivery_model->get_info($this->clang);

        foreach (language(true) as $lang) {
            $array = $lang . '_urls';
            $uri = 'uri' . strtoupper($lang);
            $this->{$array}[] = $page->{$uri};
        }
        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/main/delivery';
        $this->data['page'] = $page;

        $this->_render();
    }

    public function contacts()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 11);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, true);
            }


            $tx = 'Name: '. $post['name'].'<br>';
            $tx .= 'Telefon: '. $post['phone'].'<br>';
            $tx .= 'E-mail: '. $post['email'].'<br>';
            $tx .= 'Mesaj: '. $post['comments'].'<br>';

            //            EMAIL TO SERVER
            $this->load->library('email');
            $config = config_smtp();
            $this->email->initialize($config);
            $this->email->from('noreply@' . $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
            $this->email->to(CONT_EMAIL1);
            $this->email->subject('Solicitare site '.$_SERVER['HTTP_HOST']);
            $this->email->message($tx);
            if ($this->email->send()) {
            } else {
//                echo '<pre>';
//                print_r($this->email->print_debugger());
//                echo '</pre>';
//                die();
            }


            $this->data['send_seccess'] = SEND_SUCCESS;

        }


        foreach (language(true) as $lang) {
            $array = $lang . '_urls';
            $uri = 'uri' . strtoupper($lang);
            $this->{$array}[] = $page->{$uri};
        }

        $stores = $this->stores_model->get_stores($this->clang);
        foreach ($stores as $key => $store) {
            $store->img = $this->db->where('stores_id', $store->id)->get('stores_img')->result();
            $stores[$key] = $store;
        }
        $this->data['stores'] = $stores;

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/main/contacts';
        $this->data['page'] = $page;

        $this->_render();
    }

    public function stores()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 7);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        foreach (language(true) as $lang) {
            $array = $lang . '_urls';
            $uri = 'uri' . strtoupper($lang);
            $this->{$array}[] = $page->{$uri};
        }

        $stores = $this->stores_model->get_stores($this->clang);
        foreach ($stores as $key => $store) {
            $store->img = $this->db->where('stores_id', $store->id)->get('stores_img')->result();
            $stores[$key] = $store;
        }
        $this->data['stores'] = $stores;

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/main/stores';
        $this->data['page'] = $page;

        $this->_render();
    }


    // public function text_pages()
    // {
    //     $page = $this->menu_model
    //         ->get_page_data(
    //             $this->clang,
    //             $this->uri2
    //         );
    
    //     if (empty($page)) {
    //         $this->not_found();
    //         return;
    //     }
    
    //     $this->breadcrumbs[] = $this->_generate_bc_data(
    //         $page->title,
    //         $page->uri
    //     );
    
    //     foreach (language(true) as $lang) {
    //         $array = $lang . '_urls';
    //         $uri = 'uri' . strtoupper($lang);
    
    //         $this->{$array}[] = $page->{$uri};
    //     }
    
    //     $this->loadOGImgData($page);
    //     $this->_init_seo_data($page);
    
    //     $this->data['inner_view'] = 'pages/main/text';
    //     $this->data['page'] = $page;
    
    //     $this->_render();
    // }
    public function text_pages()
    {
        $page =
            $this->menu_model
                ->get_page_data(
                    $this->clang,
                    $this->uri2
                );
    
        if (empty($page)) {
            $this->not_found();
    
            return;
        }
    
        $this->breadcrumbs[] =
            $this->_generate_bc_data(
                $page->title,
                $page->uri
            );
    
        foreach (
            language(true) as $lang
        ) {
            $array =
                $lang . '_urls';
    
            $uri =
                'uri'
                . strtoupper($lang);
    
            $this->{$array}[] =
                $page->{$uri};
        }
    
        $this->loadOGImgData(
            $page
        );
    
        $this->_init_seo_data(
            $page
        );
    
        if (
            $page->uri === 'pravila-primeneniya-promokoda'
            || $page->uri === 'reguli-aplicare-promocod'
        ) {
            $this->data['inner_view'] =
                'pages/main/promo_rules';
        } else {
            $this->data['inner_view'] =
                'pages/main/text';
    }
    
        $this->data['page'] =
            $page;
    
        $this->_render();
    }
    
    public function sitemapGenerator()
    {
       siteMapGenerator(); 
    }
    public function not_found()
    {
        $this->output->set_status_header(404);
    
        $isRomanian = $this->lclang === 'ro';
    
        $this->data['page_title'] = $isRomanian
            ? 'Pagina nu a fost găsită'
            : 'Страница не найдена';
    
        $this->data['page_name'] =
            $this->data['page_title'];
    
        $this->data['description_for_layout'] = $isRomanian
            ? 'Pagina solicitată nu există sau a fost mutată.'
            : 'Запрошенная страница не существует или была перемещена.';
    
        $this->data['keywords_for_layout'] = '';
    
        $this->data['otitle'] =
            $this->data['page_title'];
    
        $this->data['og_img'] =
            '/app/img/fav_icon.png';
    
        $this->data['og_img_width'] = 512;
        $this->data['og_img_height'] = 512;
    
        $this->data['breadcrumbs'] = [];
    
        /*
         * Header ожидает ссылки переключения языков.
         * На 404 безопаснее вести пользователя
         * на главную соответствующего языка.
         */
        $this->data['lang_urls'] = [
            'ru' => ['ru'],
            'ro' => ['ro'],
        ];
    
        $this->data['inner_view'] =
            'pages/errors/404';
    
        $this->data['body_class'] =
            'page-404';
    
        $this->_render();
    }
}
