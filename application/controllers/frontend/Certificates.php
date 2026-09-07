<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Certificates extends FrontEndController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('brand_certificates_model');
    }

    public function index()
    {
        $this->data['page_title'] = $this->lclang === 'ro'
            ? 'Certificate de autenticitate'
            : 'Сертификаты подлинности';

        $this->data['page_name'] = $this->data['page_title'];

        $this->data['certificates_by_brand'] =
            $this->brand_certificates_model
                ->get_visible_grouped_by_brand($this->clang);

        $this->data['inner_view'] = 'pages/certificates/index';
        $this->data['home_page'] = 1;
        $this->data['class_page'] = 'certificates-page';

        $this->data['lang_urls'] = [
            'ru' => 'sertifikaty-podlinnosti',
            'ro' => 'certificate-de-autenticitate',
        ];

        $this->_render();
    }
}