<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Team extends FrontEndController
{
    private $page_id;

    public function __construct()
    {
        parent::__construct();
        $this->page_id = 6;
        $this->load->model('team_model');
    }

    private function _init_seo_data($page)
    {
        $this->data['page_title'] = (!empty($page->seo_title)) ? $page->seo_title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = $page->text;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $page->seo_title;
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

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $teams = $this->team_model->get_teams($this->clang);
        $this->data['teams'] = $teams;

        foreach (language(true) as $lang) {
            $array = $lang . '_urls';
            $uri = 'uri' . strtoupper($lang);
            $this->{$array}[] = $page->{$uri};
        }

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/team/index';
        $this->data['page'] = $page;

        $this->_render();
    }
}
