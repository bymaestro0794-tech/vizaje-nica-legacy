<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Certificat extends FrontEndController
{
    private $page_id;
    private $API_URL = 'http://api.vizaje-nica.com:90/Vizaje-Nica/hs/WORKAPP/';

    public function __construct()
    {
        parent::__construct();
        $this->page_id = 31;
    }

    private function _init_seo_data($page)
    {
        $this->data['page_title'] = (!empty($page->seo_title)) ? $page->seo_title : "";
        $this->data['page_name'] = $page->title;
        $this->data['text_for_layout'] = $page->text;
        $this->data['keywords_for_layout'] = (!empty($page->seo_keywords)) ? $page->seo_keywords : "";
        $this->data['description_for_layout'] = (!empty($page->seo_desc)) ? $page->seo_desc : "";
        $this->data['otitle'] = $page->seo_title;
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

    public function success()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );

        $id = $this->input->get('id');
        $data['id'] = $id;

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/certificat/success';
        $this->data['page'] = $page;

        $this->_render();
    }

    public function index()
    {
        $db = $this->load->database('app', TRUE);
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $post = $this->input->post(null, true);

            $ins = [
                'gift_cards_id' => $post['design-certificate'],
                'price' => $post['denomination'],
                'name_from' => $post['from'],
                'name_to' => $post['to'],
                'text' => $post['text'],
                'send_now' => $post['time-send'] === 'inst' ? 1 : 0,
                'send_date' => $post['time-send'] === 'set' ? date('Y-m-d', strtotime($post['date'])).' '.$post['time'] : null,
                'ticket_email' => $post['cec_email'],
                'receiver_phone_number' => str_replace([" ", ")", "("], '', $post['phone']),
                'receiver_email' => $post['email'],
            ];

            $db->insert('gift_cards_info', $ins);
            $ins['id'] = $db->insert_id();
            //$ins['price'] = rand(1, 100) / 100;

            $this->client_server($this->lclang, $ins);
        }
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $this->data['certificates'] = $db->where("is_deleted", 0)->where("isShown", 1)->get('gift_cards')->result();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $this->data['page_url'] = '/' . $this->uri1 . '/' . $this->uri2 . '/' . $this->uri3;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/certificat/index';
        $this->data['page'] = $page;

        $this->_render();
    }

    function client_server($lang, $certificat)
    {
        $db = $this->load->database('app', TRUE);

        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetAPI.php');
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetConfig.php');
        $api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);
        $prequest = new PaynetRequest();

        $prequest->ExternalID = round(microtime(true) * 1000);
        $certificateSlug = $lang === 'ru'
            ? 'podarochnyie-sertifikatyi'
            : 'certificat-cadou';
        $certificateSlug = $lang === 'ru'
            ? 'podarochnyie-sertifikatyi'
            : 'certificat-cadou';

        $prequest->LinkSuccess =
            'https://' .
            $_SERVER['HTTP_HOST'] .
            '/' .
            $lang .
            '/' .
            $certificateSlug .
            '/success?id=' .
            $prequest->ExternalID;

        $prequest->LinkCancel =
            'https://' .
            $_SERVER['HTTP_HOST'] .
            '/' .
            $lang .
            '/' .
            $certificateSlug .
            '/error?id=' .
            $prequest->ExternalID;
        $prequest->Lang = $lang;
        $prequest->Amount = 0;

        $product = array();
        $product['LineNo'] = 1;
        $product['Code'] = 'certificat_' . $certificat['gift_cards_id'];
        $product['Barcode'] = 'certificat_' . $certificat['gift_cards_id'];
        $product['Name'] = 'Vizaje Nica  Certificat ' . $certificat['price'] . ' MDL';
        $product['Descrption'] = 'Vizaje Nica  Certificat ' . $certificat['price'] . ' MDL';
        $product['Quantity'] = 100;
        $product['UnitPrice'] = $certificat['price'] * 100;
        $prequest->Amount = $prequest->Amount + ($certificat['price'] * 100);

        $prequest->Products = [
            $product
        ];

        $prequest->Service = array(
            'Name' => 'VN',
            'Description' => 'Vizaje Nica  Certificat',
            'Amount' => $prequest->Amount,
            'Products' => $prequest->Products
        );

        $phone = str_replace(" ", '', $certificat['receiver_phone_number']);
        $phone = str_replace(")", '', $phone);
        $phone = str_replace("(", '', $phone);

        $prequest->Customer = array(
            'Code' => $certificat['ticket_email'],
            'Address' => 'vizaje nica',
            'Name' => $certificat['name_from'],
            'Surname' => $certificat['name_from'],
            'Phone' => $phone
        );
        $formObj = $api->FormCreate($prequest);

        if ($formObj->Code == PaynetCode::SUCCESS) {
            $data = array(
                'ExternalID' => $prequest->ExternalID,
            );
            $db->where('id', $certificat['id']);
            $db->update('gift_cards_info', $data);
            echo $formObj->Data;


            echo '<script>
                    document.getElementById("pay_form").submit();
                </script>';
            die();
        }
    }
}
