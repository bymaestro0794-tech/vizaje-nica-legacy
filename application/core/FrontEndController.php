<?php
defined('BASEPATH') or exit('No direct script access allowed');

class FrontEndController extends CI_Controller
{
    protected $uri1;
    protected $uri2;
    protected $uri3;
    protected $uri4;
    protected $uri5;
    protected $uri6;
    protected $uri7;
    protected $data;
    protected $layout_path;
    protected $langs;
    protected $lclang;
    protected $clang;
    protected $site_url;
    protected $without_get_url;
    protected $full_url;
    protected $breadcrumbs;

    private function _define_constants()
    {
        $this->load->model('constants_model');
        $constants = $this->constants_model->find();
        $lang = get_language(TRUE);
        foreach ($constants as $constant) {
            if (!defined($constant->name)) {
                define($constant->name, $constant->$lang);
            }
        }
    }

    /**
     * Читает cookie `cookie_consent` (JSON {functional, analytics, marketing})
     * и отдаёт категории для PHP-условий, которыми в index.php гейтятся
     * GTM/Clarity/Meta Pixel/Jivo. necessary отдельно не хранится — всегда true.
     */
    private function _read_cookie_consent()
    {
        $consent = array(
            'given' => false,
            'necessary' => true,
            'functional' => false,
            'analytics' => false,
            'marketing' => false,
        );

        $raw = get_cookie('cookie_consent');

        if (empty($raw)) {
            return $consent;
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return $consent;
        }

        $consent['given'] = true;
        $consent['functional'] = !empty($decoded['functional']);
        $consent['analytics'] = !empty($decoded['analytics']);
        $consent['marketing'] = !empty($decoded['marketing']);

        return $consent;
    }

    protected function _generate_bc_data($title = '', $url = false)
    {
        $result['title'] = $title;
        if (!empty($url)) {
            $result['url'] = $url;
        }
        return $result;
    }

    public function __construct()
    {
        parent::__construct();

        @session_start();
        $this->load->helper('cookie');
        $this->load->library(['cart', 'user_agent']);

        date_default_timezone_set('Europe/Bucharest');
        header('Content-type: text/html; charset=utf-8');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Strict-Transport-Security: max-age=31536000');
        header('X-Frame-Options: deny');
        header("Cache-Control: max-age=31536000");
        header("Referrer-Policy: same-origin");
        header("Feature-Policy: microphone 'none'; geolocation 'none';");

        $this->uri1 = uri(1);
        $this->uri2 = uri(2);
        $this->uri3 = uri(3);
        $this->uri4 = uri(4);
        $this->uri5 = uri(5);
        $this->uri6 = uri(6);
        $this->uri7 = uri(7);

        if (empty($_SESSION['lang'])) get_prefered_language();

        assign_language($this->uri1);

        $this->_define_constants();

        $this->load->model('menu_model');
        $this->load->model('clients_model');
        $this->load->helper('mine_helper');

        $this->_is_logged_in();
        sync_cart();

        $this->output->set_header('X-XSS-Protection: 1; mode=block');
        $this->output->set_header('X-Content-Type-Options: nosniff');

        $this->layout_path = 'pages/index';
        $this->langs = array_map('strtoupper', language(true));
        $this->lclang = get_language(FALSE);
        $this->clang = get_language(TRUE);
        $this->site_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'];
        $this->without_get_url = $this->site_url . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $this->full_url = $this->site_url . $_SERVER['REQUEST_URI'];
        $this->breadcrumbs = array();

        $this->prepareHomepageStructuredData();

        $menu = $this->menu_model->get_menu($this->clang);

        $home_bc_title = (!empty($menu['all'][1]->breadcrumbTitle)) ? $menu['all'][1]->breadcrumbTitle : $menu['all'][1]->title;
        $this->data['home_bc_title'] = $home_bc_title;

        $this->categories_nav();
        $this->brands_nav();
        $this->home_products();
        $this->ilab_info();
        $this->onBottom_stores();

        $this->load->library('user_agent');
        $this->data['is_mobile'] = $this->agent->is_mobile();

        $this->data['uri1'] = $this->uri1;
        $this->data['uri2'] = $this->uri2;
        $this->data['uri3'] = $this->uri3;
        $this->data['uri4'] = $this->uri4;
        $this->data['uri5'] = $this->uri5;
        $this->data['uri6'] = $this->uri6;
        $this->data['uri7'] = $this->uri7;
        $this->data['langs'] = $this->langs;
        $this->data['lclang'] = $this->lclang;
        $this->data['clang'] = $this->clang;
        $this->data['site_url'] = $this->site_url;
        $this->data['without_get_url'] = $this->without_get_url;
        $this->data['full_url'] = $this->full_url;
        $this->data['menu'] = $menu;

        //      wishlist
        $this->data['w_count'] = 0;
        $response['result'] = get_cookie('produse_wishlust');
        $response['status'] = 'ok';
        $wishlist = explode(',', $response['result']);

        $this->data['wishlist'] = $wishlist;
        if (!empty($wishlist) && !empty($wishlist[0])) {
            $this->data['count_wishlist'] = count($wishlist);
        } else {
            $this->data['count_wishlist'] = 0;
        }
        if (!empty($response['result'])) {
            $w_count = count($wishlist);
            $this->data['w_count'] = $w_count;
        }

        if(getRealIpAddr() == '89.28.84.71') {
            // $this->output->enable_profiler(true);
        }

        $this->data['consent'] = $this->_read_cookie_consent();

//        cart
        $this->data['total_items_cart'] = $this->cart->total_items();
        $this->data['total_price_cart'] = $this->cart->total();
        $this->data['cart_items'] = $this->cart->contents();

    }

    protected function _render()
    {
        $this->load->vars($this->data);
        $this->load->view($this->layout_path);
    }

    private function ilab_info()
    {
        if ($this->clang == 'RU') {
            $ilab = 'Разработка сайта - ilab.md';
            $ilab_linc = 'https://ilab.md/ru/portfolio/mobilnoe-prilojenie-vizaje-nica-dlya-programmyi-loyalnosti';
        } elseif ($this->clang == 'EN') {
            $ilab = 'Site development - ilab.md';
            $ilab_linc = 'https://ilab.md/en/portfolio/vizaje-nica-mobile-app-for-the-loyalty-program';
        } else {
            $ilab = 'Elaborarea siteului - ilab.md';
            $ilab_linc = 'https://ilab.md/ro/portfolio/aplicatia-mobila-vizaje-nica-pentru-programul-de-loialitate';
        }
        $this->data['ilab'] = $ilab;
        $this->data['ilab_linc'] = $ilab_linc;
    }

    private function _is_logged_in()
    {
        $user_id = @$_SESSION['user_id'];
        $user_login = @$_SESSION['user_login'];
        $usr_key = @$_SESSION['usr_key'];
        $isb2b = @$_SESSION['isb2b'];

        if (empty($user_id) || empty($user_login) || empty($usr_key) || !isset($isb2b)) {
            unset($_SESSION['user_id']);
            unset($_SESSION['user_login']);
            unset($_SESSION['usr_key']);
            unset($_SESSION['isb2b']);
        } else {
            $client_info = $this->clients_model->get_client_login($user_id);
            if (empty($client_info)) {
                unset($_SESSION['user_id']);
                unset($_SESSION['user_login']);
                unset($_SESSION['usr_key']);
                unset($_SESSION['isb2b']);
            } else {
                $this->data['client_info'] = $client_info;
                if (empty($this->data['client_info']->discount)) $this->data['client_info']->discount = 10;
            }
        }
    }

    private function categories_nav()
    {
        $this->load->model('categories_model');
//        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
//        if(!$categories = $this->cache->get('categories'.$this->clang)) {
//            $categories = $this->categories_model->get_category_menu($this->clang);
//            $this->cache->save('categories'.$this->clang, $categories, 60 * 60 * 6);
//        }
        $categories = $this->categories_model->get_category_menu($this->clang);
        $this->data['categories'] = $categories;
    }

    private function brands_nav()
    {
        $this->load->model('brands_model');
//        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
//        if(!$categories = $this->cache->get('categories'.$this->clang)) {
//            $categories = $this->categories_model->get_category_menu($this->clang);
//            $this->cache->save('categories'.$this->clang, $categories, 60 * 60 * 6);
//        }
        $brands_nav = $this->brands_model->get_brands_menu($this->clang);
        $this->data['brands_nav'] = $brands_nav;
    }

    // private function products_new()
    // {
    //     $this->load->model('products_model');
    //     $products_new = $this->products_model->get_products_home_new($this->clang);
    //    $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    //    if(!$categories = $this->cache->get('categories'.$this->clang)) {
    //        $categories = $this->categories_model->get_category_menu($this->clang);
    //        $this->cache->save('categories'.$this->clang, $categories, 60 * 60 * 6);
    //    }
    //     $this->data['products_new'] = $products_new;
    // }

    private function home_products()
{
	$this->load->model(
		'home_product_items_model'
	);

	$manualNew =
		$this->home_product_items_model
			->get_products_home_section(
				$this->clang,
				'new',
				30
			);

	$manualHits =
		$this->home_product_items_model
			->get_products_home_section(
				$this->clang,
				'hits',
				30
			);

	$manualSale =
		$this->home_product_items_model
			->get_products_home_section(
				$this->clang,
				'sale',
				30
			);

	$this->data['products_new'] =
		$manualNew;

	$this->data['products_order'] =
		$manualHits;

	$this->data['products_sale'] =
		$manualSale;

	log_message(
		'error',
		'HOME PRODUCTS: new='
		. count($manualNew)
		. ', hits='
		. count($manualHits)
		. ', sale='
		. count($manualSale)
	);
}

    private function onBottom_stores()
    {
        $this->load->model('stores_model');

//        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
//        if(!$categories = $this->cache->get('stores'.$this->clang)) {
//            $stores = $this->stores_model->get_stores_onBottom($this->clang);
//            $this->cache->save('stores'.$this->clang, $stores, 60 * 60 * 6);
//        }

        $stores = $this->stores_model->get_stores_onBottom($this->clang);
        $this->data['onBottom_stores'] = $stores;

    }

    private function buildOrganizationSchema()
{
    return array(
        '@context' =>
            'https://schema.org',

        '@type' =>
            'OnlineStore',

        '@id' =>
            $this->site_url
            . '/#organization',

        'name' =>
            'Vizaje-Nica',

        'alternateName' =>
            'Vizaje Nica',

        'url' =>
            $this->site_url
            . '/',

        'logo' =>
            array(
                '@type' =>
                    'ImageObject',

                'url' =>
                    $this->site_url
                    . '/app/img/logo_v.png',

                'contentUrl' =>
                    $this->site_url
                    . '/app/img/logo_v.png',
            ),

        'email' =>
            'marketing@vizaje-nica.com',

        'address' =>
            array(
                '@type' =>
                    'PostalAddress',

                'streetAddress' =>
                    'str. Albișoara 42/2, of. 3',

                'addressLocality' =>
                    'Chișinău',

                'addressCountry' =>
                    'MD',
            ),

        'sameAs' =>
            array(
                'https://www.facebook.com/VizajeNicaMD',
                'https://www.instagram.com/vizaje_nica/',
                'https://www.tiktok.com/@vizajenica',
            ),
    );
}

private function buildWebsiteSchema()
{
    return array(
        '@context' =>
            'https://schema.org',

        '@type' =>
            'WebSite',

        '@id' =>
            $this->site_url
            . '/#website',

        'url' =>
            $this->site_url
            . '/',

        'name' =>
            'Vizaje-Nica',

        'alternateName' =>
            'Vizaje Nica',

        'publisher' =>
            array(
                '@id' =>
                    $this->site_url
                    . '/#organization',
            ),
    );
}

private function prepareHomepageStructuredData()
{
    /*
    |--------------------------------------------------------------------------
    | Site-level structured data
    |--------------------------------------------------------------------------
    |
    | Google рекомендует WebSite размещать только на domain-level homepage.
    |
    | У Vizaje-Nica:
    |
    | /     -> главная страница на румынском
    | /ru   -> языковая версия
    | /ro   -> языковая версия
    |
    | Поэтому WebSite и OnlineStore выводим только на /.
    |
    */

    if (!empty($this->uri1)) {
        return;
    }

    $this->data['organization_schema'] =
        $this->buildOrganizationSchema();

    $this->data['website_schema'] =
        $this->buildWebsiteSchema();
}

}

