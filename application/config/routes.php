<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$langs_array = array('ro','ru');
//$CI = &get_instance();
//$langs_array = $CI->language();
$langs = '(' . implode('|', $langs_array) . ')';

$route['default_controller'] = 'pages';

$route['404_override'] = 'custom404/index';
$route['translate_uri_dashes'] = FALSE;
$route['sitemapGenerator'] = "pages/sitemapGenerator";

$route['api/orders/item'] = 'orders/item';
$route['api/orders/demo'] = 'orders/demo';


// Facebook Feed (RO, RU)
$route['feed/facebook-ro\.xml'] = 'feed/facebook/ro';
$route['feed/facebook-ru\.xml'] = 'feed/facebook/ru';

$route['feed/multisearch'] = 'feed/multisearch';

$route['import/import'] = "import/import";
$route['import/import_variable'] = "import/import_variable";
$route['import/update_qty'] = "import/update_qty";

$route['import/import_categories'] = "import/import_categories";
$route['import/brands_product'] = "import/brands_product";

$route['import/update_url'] = "import/update_url";
$route['import/update_stock'] = "import/update_stock";

$route['import/update_price'] = "import/update_price";

$route['import/update_seo'] = "import/update_seo";

$route['import/update_sale_mdl'] = "import/update_sale_mdl";

$route['paynet-callback'] = "paynet/paynet_callback";

$route['api/order_json'] = "api/order_json";
$route['api/order_check'] = "api/order_check";

$route['url_chech'] = "api/url_chech";
/* Ajax */
$route['cookie_consent'] = "ajax/cookie_consent";
$route['recently_viewed'] =
    'ajax/recently_viewed';
$route[$langs.'/subscribe'] = "ajax/subscription";

$route['wishlist_add'] = 'frontend/wishlist/add';

$route['cart_add'] = 'ajax/cart_add';
$route['cart/delete'] = 'ajax/cart_delete';
$route['cart/update_cart'] = 'ajax/cart_update';

$route['cart/drawer'] = 'ajax/cart_drawer';
$route['product/quick-view'] =
    'ajax/product_quick_view';

/* Frontend */
$route[$langs] = "pages/index";

$route[$langs.'/(certificat-cadou|podarochnyie-sertifikatyi)'] = "frontend/certificat/index";
$route[$langs.'/(certificat-cadou|podarochnyie-sertifikatyi)/success'] = "frontend/certificat/success";
$route[$langs.'/(certificat-cadou|podarochnyie-sertifikatyi)/error'] = "frontend/certificat/success";

$route[$langs . '/(sertifikaty-podlinnosti|certificate-de-autenticitate)'] =
    'frontend/certificates/index';

$route[$langs.'/(izbrannyie|favorite|favorite)'] = "frontend/wishlist/index";
$route[$langs.'/wishlist'] = "frontend/wishlist/wishlist";

$route[$langs.'/(dostavka|livrare)'] = "pages/delivery";

$route[$langs.'/(kontaktyi|contacte)'] = "pages/contacts";

$route[$langs.'/(magazinyi|magazinele)'] = "pages/stores";

$route[$langs.'/(brendyi|brands)'] = "frontend/brands/index";

$route[$langs.'/(aktsii|promotii)'] = "frontend/offers/index";
$route[$langs.'/(aktsii|promotii)/:any'] = "frontend/offers/items";

$route[$langs.'/(noutati|novosti)'] = "frontend/articles/index";
$route[$langs.'/(noutati|novosti)/:any'] = "frontend/articles/items";

$route[$langs.'/(katalog|catalog|catalog)'] = "frontend/catalog/index";
$route[$langs.'/(katalog|catalog|catalog)/(:any)'] = "frontend/catalog/category";
$route[$langs.'/(katalog|catalog|catalog)/(:any)/(:any)'] = "frontend/catalog/item";

$route[$langs.'/(novinki|nou|new)'] = "frontend/catalog/new_products";
$route[$langs.'/(skidki|reduceri|sale)'] = "frontend/catalog/best_sellers";

$route[$langs.'/products/filter_category'] = "frontend/catalog/filter_category";

$route[$langs.'/products/filter_brands'] = "frontend/catalog/filter_brands";

$route[$langs.'/products/filter_offer'] = "frontend/offers/filter_offer";

$route[$langs.'/products/filter_bests'] = "frontend/catalog/filter_bests";

$route[$langs.'/(korzina|cos|cart)'] = "frontend/cart/index";
$route[$langs.'/(oformlenie-zakaza|checkout|checkout)'] = "frontend/cart/checkout";
$route[$langs.'/delete_all'] = "frontend/cart/delete_all";
$route[$langs.'/(order-success|uspeshnyiy-zakaz|order-success)'] = "frontend/cart/success";
$route[$langs.'/(success|success|success)'] = "frontend/cart/success";
$route[$langs.'/(error|eroare|error)'] = "frontend/cart/error";

$route[$langs.'/(lichnyiy-kabinet|contul-meu|contul-meu)'] = "frontend/account/index";


$route[$langs.'/(vhod|intrare|login)'] = "frontend/account/login";
$route[$langs.'/logout'] = "frontend/account/logout";
$route[$langs.'/(registratsiya|inregistrare|registration)'] = "frontend/account/registration";
$route[$langs.'/(registration_active|registration_active|registration_active)'] = "frontend/account/registration_active";
$route[$langs.'/(vosstanovlenie-parolya|resetati-parola|password-recovery)'] = "frontend/account/reset_password";

$route[$langs.'/(zakazyi|comenzile-mele|password-recovery)'] = "frontend/account/order";

$route[$langs.'/(zakazyi|comenzile-mele|password-recovery)/(:any)'] = "frontend/account/order_item";

$route[$langs.'/registration_form'] = "frontend/account/registration_form";
$route[$langs.'/login_form'] = "frontend/account/login_form";
$route[$langs.'/login_form_code'] = "frontend/account/login_form_code";
$route[$langs.'/reset_form'] = "frontend/account/reset_form";

$route[$langs.'/sendSMS'] = "frontend/account/sendSMS";

$route[$langs.'/password_reset'] = "frontend/account/reset_password";


$route['ru/nishevaya-parfyumeriya'] =
    'frontend/offers/niche_sale';

$route['ro/parfumerie-de-nisa'] =
    'frontend/offers/niche_sale';


$route[$langs.'/products/variable'] = "frontend/catalog/variable";



$route[$langs.'/(brands)'] = "frontend/brands/index";
$route[$langs.'/(brands)/(:any)'] = "frontend/brands/items";

$route[$langs . '/(poisk|cautare)'] = 'frontend/search/index';
$route[$langs . '/search'] = 'frontend/search/search';

$route[$langs.'/products/category-parent'] = "frontend/catalog/products_category_parent";
$route[$langs.'/products/brands'] = "frontend/catalog/products_brand";

$route[$langs.'/(our-project)'] = "frontend/projects/index";

$route['cp_features/(:any)'] = "features/$1";
/* Default Routing */
$route[$langs . '/:any/:any/:any/:any/:any'] = 'pages/text_pages';
$route[$langs . '/:any/:any/:any/:any'] = 'pages/text_pages';
$route[$langs . '/:any/:any/:any'] = 'pages/text_pages';
$route[$langs . '/:any/:any'] = 'pages/text_pages';
$route[$langs . '/:any'] = 'pages/text_pages';

/* Dashboard */
$route['cp'] = "backend/auth/login";
$route['cp/login'] = "backend/auth/login";
$route['cp/logout'] = "backend/auth/logout";
$route['cp/delete_photo'] = "backend/dashboard/delete_photo";
$route['cp/delete_img_row'] = "backend/dashboard/delete_img_row";
$route['cp/delete_file'] = "backend/dashboard/delete_file";
$route['cp/change_select'] = "backend/dashboard/change_select";
$route['cp/change_check'] = "backend/dashboard/change_check";

$route['cp/(:any)'] = "backend/$1";
$route['cp/(:any)/(:any)'] = "backend/$1/$2";
$route['cp/(:any)/(:any)/(:any)'] = "backend/$1/$2/$3";
$route['cp/(:any)/(:any)/(:any)/(:any)'] = "backend/$1/$2/$3/$4";
