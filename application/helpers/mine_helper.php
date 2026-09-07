<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('newthumbs')) {
    function newthumbs($photo = '', $dir = '', $width = 0, $height = 0, $version = 0, $zc = 0)
    {

        $timg = realpath('public/' . $dir) . '/' . $photo;

        if(in_array($dir, [ 'products', 'sliders']) and $version !== 'mobile' and
            is_file($timg)) {
            return createWebp($photo, $dir, false);
        }

        require_once(realpath('application') . '/third_party/imres/phpthumb.class.php');

        $result = is_dir(realpath('public/' . $dir) . '/thumbs');
        if ($result) {
            $prevdir = $dir . '/thumbs';
        } else {
            $dir = createPath($dir);
            if (mkdir(realpath('public/' . $dir) . '/thumbs')) {
                $prevdir = $dir . '/thumbs';
            } else {
                return '/public/i/no_image_0_0_0.jpg';
            }
        }

        $timg = realpath('public/' . $dir) . '/' . $photo;

        $pizza = explode(".", $photo);
        if(end($pizza) == "svg") return '/public/' . $dir . '/' . $photo;

        if (!is_file($timg)) {
            $source = realpath('public/i') . '/no_image.png';
            $dest = realpath('public/i') . '/no_image_' . $width . '_' . $height . '_' . $zc . '.jpg';
            //return $dest;
            $dest2 = '/public/i/no_image_' . $width . '_' . $height . '_' . $zc . '.jpg';
            if (is_file($dest)) return $dest2;
            $phpThumb = new phpThumb();
            $phpThumb->setSourceFilename($source);
            if (!empty($width)) $phpThumb->setParameter('w', $width);
            if (!empty($height)) $phpThumb->setParameter('h', $height);
            $phpThumb->setParameter('q', 100);
            $phpThumb->setParameter('f', 'png');
            if (!empty($zc)) {
                $phpThumb->setParameter('zc', '1');
            }
            $img = '';
            if ($phpThumb->GenerateThumbnail()) {
                if ($phpThumb->RenderToFile($dest)) {
                    return $dest2;
                }
            }
        }

        if (!empty($version)) {
            $result = is_dir(realpath('public/' . $dir) . '/thumbs/version_' . $version);
            if ($result) {
                $prevdir = $dir . '/thumbs/version_' . $version;
            } else {
                if (mkdir(realpath('public/' . $dir) . '/thumbs/version_' . $version)) {
                    $prevdir = $dir . '/thumbs/version_' . $version;
                } else {
                    return '/public/i/no_image_0_0_0.jpg';
                }
            }
        }
        $va1 = explode('.', $photo);
        $ext = end($va1);

        $timg = realpath('public/' . $dir) . '/' . $photo;
        $catimg = realpath('public/' . $prevdir) . '/' . $photo;

        if (is_file($timg) && !is_file($catimg)) {
            $opath1 = realpath('public/' . $dir) . '/';
            $opath2 = realpath('public/' . $prevdir) . '/';
            $dest = $opath2 . $photo;
            $source = $opath1 . $photo;

            $phpThumb = new phpThumb();
            $phpThumb->setSourceFilename($source);

            if (!empty($width)) $phpThumb->setParameter('w', $width);
            if (!empty($height)) $phpThumb->setParameter('h', $height);
            if (!empty($height)) $phpThumb->setParameter('q', 100);
            if ($ext == 'png') $phpThumb->setParameter('f', 'png');
            if (!empty($zc)) {
                $phpThumb->setParameter('zc', '1');
            }
            $phpThumb->setParameter('q', 100);
            if ($phpThumb->GenerateThumbnail()) {
                if ($phpThumb->RenderToFile($dest)) {
                    $img = '/public/' . $prevdir . '/' . $photo;
                } else {
                    return '/public/i/no_image_0_0_0.jpg';
                }
            }

        } elseif (is_file($catimg)) {
            $img = '/public/' . $prevdir . '/' . $photo;
        } else {
            return '/public/i/no_image_0_0_0.jpg';
        }

        if (empty($img)) $img = '/public/i/no_image_0_0_0.jpg';

        return $img;
    }
}

if (!function_exists('createWebp')) {
    function createWebp($img, $dir, $removeSrc = false) {
        $pizza = explode('.', $img);
        if(!is_file(realpath('public').'/' . $dir . '/'.$pizza[0].'.webp')) {
            if($pizza[1] == 'png') {
                $im = imagecreatefrompng(realpath('public') . '/' . $dir . '/' . $img);
            } else {
                $im = imagecreatefromjpeg(realpath('public') . '/' . $dir . '/' . $img);
            }
            imagewebp($im, realpath('public') . '/' . $dir . '/'.$pizza[0].'.webp', 80);
        }
        return '/public/' . $dir . '/'.$pizza[0].'.webp';
    }
}

if(!function_exists('unlink_files')) {
    function unlink_files($dir, $files) {

        if(is_array($files)){
            foreach($files as $file) {
                // delete main file
                $main = realpath('public/' . $dir) . '/' . $file;
                if(is_file($main)) unlink($main);

                // delete thumbs
                $dirs = array_filter(glob('public/'.$dir.'/thumbs/*'), 'is_dir');

                foreach($dirs as $directory) {
                    $thumb = $directory.'/'.$file;
                    if (is_file($thumb)) unlink($thumb);
                }
            }
        } else {
            // delete main file
            $file = $files;
            $main = realpath('public/' . $dir) . '/' . $file;
            if(is_file($main)) unlink($main);

            // delete thumbs
            $dirs = array_filter(glob('public/'.$dir.'/thumbs/*'), 'is_dir');

            foreach($dirs as $directory) {
                $thumb = $directory.'/'.$file;
                if (is_file($thumb)) unlink($thumb);
            }
        }
    }
}

if (!function_exists('verify_img_extension')) {
    function verify_img_extension($ext) {
        $file_types = array('.jpg', '.jpeg', '.gif', '.png', '.svg');
        if (in_array(strtolower($ext), $file_types)) {return true;} else {return false;}
    }
}

if (!function_exists('createPath')) {
    function createPath($path) {

        if (is_dir(realpath('public') . '/' . $path)) return $path;

        $items = explode("/", $path);
        $dir = "";

        foreach($items as $item) {
            $dir .= "/" . $item;

            if (!is_dir(realpath('public') . '/' . $dir)) mkdir(realpath('public') . $dir);
        }

        return $dir;
    }
}

if (!function_exists('init_load_img')) {
    function init_load_img($path, $first=true) {

        $dir = createPath($path);

        $CI =& get_instance();
        $config['upload_path'] = realpath("public") . '/' . $dir;
        $config['allowed_types'] = 'jpg|jpeg|gif|png|svg';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;
        if($first) {
            $CI->load->library('upload', $config);
        } else {
            $CI->upload->initialize($config);
        }
    }
}

if (!function_exists('init_load_files')) {
    function init_load_files($path, $first=true) {

        $dir = createPath($path);

        $CI =& get_instance();
        $config['upload_path'] = realpath("public") . '/' . $dir;
        $config['allowed_types'] = 'pdf|doc|docx';
        $config['encrypt_name'] = TRUE;
        if($first) {
            $CI->load->library('upload', $config);
        } else {
            $CI->upload->initialize($config);
        }
    }
}

if (!function_exists('dump')) {
    function dump($data, $continue = false)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if (!$continue) {
            die();
        }
        return true;
    }
}

if (!function_exists('throw_on_404')) {
    function throw_on_404()
    {
        header("HTTP/1.0 404 Not Found");
        show_404();
        exit();
    }
}

if (!function_exists('check_if_POST')) {
    function check_if_POST()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            throw_on_404();
        }
    }
}

if (!function_exists('check_if_GET')) {
    function check_if_GET()
    {
        if (empty($_GET)) {
            throw_on_404();
        }
    }
}

if(!function_exists('language')) {
    function language($param=false) {
        $language = array(
            'ro' => 'România',
            'ru' => 'Русский',
        );
        if($param){
            $keys = array();
            foreach($language as $key => $name){
                $keys[] = $key;
            }
            return $keys;
        } else {
            return $language;
        }
    }
}

if (!function_exists('get_prefered_language')) {
    function get_prefered_language() {
        $langs = language(true);
        $browserlang = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2) : '';

        if(in_array($browserlang, $langs )) {
            $_SESSION['lang'] = $browserlang;
        } else {
            $_SESSION['lang'] = 'ro';
        }
    }
}

if (!function_exists('assign_language')) {
    function assign_language($lang = FALSE) {

        $langs = language(true);
        if(in_array($lang, $langs )) {
            $_SESSION['lang'] = $lang;
        } else {
            $_SESSION['lang'] = 'ro';
        }
    }
}

if (!function_exists('get_language')) {
    function get_language($up = FALSE)
    {
        return ($up) ? strtoupper($_SESSION['lang']) : strtolower($_SESSION['lang']);
    }
}

if (!function_exists('get_language_for_admin')) {
    function get_language_for_admin($up = FALSE) {
        return ($up) ? 'RU' : 'ru';
    }
}

if (!function_exists('select_language')) {
    function select_language($lang = FALSE, $langs_array = FALSE)
    {
        if (empty($lang) || empty($langs_array)) {
            throw_on_404();
        }
        $CI = &get_instance();
        $protocol = (isset($_SERVER['HTTPS']) ? "https" : "http");
        $host = '://' . $_SERVER['HTTP_HOST'];
        $get_data = $_SERVER['QUERY_STRING'];
        $current_lang = strtolower($lang);
        $lang_title = array();
        foreach ($langs_array as $lang => $item) {

            switch ($lang) {
                case 'ru':
                    $name = 'RU';
                    break;
                case 'en':
                    $name = 'EN';
                    break;
                case 'ro':
                    $name = 'RO';
                    break;
            }
            $lang_title[$lang] = $name;
        }

        $CI->load->view('layouts/pages/langs',
            array('langs_array' => $langs_array,
                'protocol' => $protocol,
                'host' => $host,
                'get_data' => $get_data,
                'lang_title' => $lang_title,
                'current_lang' => $current_lang));
    }
}


if (!function_exists('reArrayFiles')) {
    function reArrayFiles($file)
    {
        $file_ary = array();
        $file_count = count($file['name']);
        $file_key = array_keys($file);

        for ($i = 0; $i < $file_count; $i++) {
            foreach ($file_key as $val) {
                if (isset($file[$val][$i])) {
                    $file_ary[$i][$val] = $file[$val][$i];
                }
            }
        }
        return $file_ary;
    }
}

if (!function_exists('transliteration')) {
    function transliteration($str)
    {
        $tr = array(
            "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
            "Д" => "D", "Е" => "E", "Ё" => "E", "Ж" => "J", "З" => "Z", "И" => "I",
            "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N", 'â' => 'a', 'Â'=> 'A', 'ă' => 'a', 'Ă' => 'A', 'ţ' => 't', 'Ţ' => 'T', 'ş' => 's', 'Ş' => 'S',
            "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
            "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH", "î" => "i", "Î" => "I", "," => "", "№" => "", "ț" => "t", "Ț" => "T", "ș" => "s", "Ș" => "S",
            "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ё" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya", "_" => "-", " (" => "", ")" => "",
            "." => "", "\\" => "", "/" => "-", "'" => "", "»" => "", "«" => "", "&quot;" => "", "\"" => "", "&" => "i", "%" => "",
            "$" => "usd", "€" => "eur", "!" => "", " " => "-", "?" => "", "+" => "",
        );

        $str = strtolower(strtr($str, $tr));
        $str = preg_replace('/\s+/u', '-', $str);
        $str = preg_replace('/[^a-zA-Z0-9_-]+/', '', $str);

        return $str;
    }
}

if (!function_exists('get_url_pattern')) {
    function get_url_pattern()
    {
        $url = preg_replace('/\&page=.*/', '', $_SERVER["REQUEST_URI"]);
        /* Заносим переменную page в ГЕТ */
        if (!preg_match('/\?/', $_SERVER["REQUEST_URI"])) {
            $urlPattern = $url . '?&page=(:num)';
        } else {
            $urlPattern = $url . '&page=(:num)';
        }
        return $urlPattern;
    }
}

if (!function_exists('is_assoc')) {
    function is_assoc($var)
    {
        return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
    }
}

if (!function_exists('get_youtube_id')) {
    function get_youtube_id($url)
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        $youtube_id = $match[1];
        return $youtube_id;
    }
}

if (!function_exists('formatSizeUnits')) {
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}

if (!function_exists('numberFormat')) {
    function numberFormat($number, $zecimal=2) {
        return number_format((float)$number, $zecimal, '.', ' ');
    }
}

if (!function_exists('str_replace_nth')) {
    function str_replace_nth($search, $replace, $subject, $nth=1) {
        $nth = $nth-1;
        $found = preg_match_all('/'.preg_quote($search).'/', $subject, $matches, PREG_OFFSET_CAPTURE);
        if (false !== $found && $found > $nth) {
            return substr_replace($subject, $replace, $matches[0][$nth][1], strlen($search));
        }
        return $subject;
    }
}

function zerofill ($num, $zerofill = 5) {
    return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
}

if (!function_exists('ilabCrypt')) {
    function ilabCrypt($string, $action = true)
    {
        // Инициализируем даныне
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = '0jWLPvrkm0bASR}L_UpWi:TWzV3<%|(4~OglNl;+Z+G3C@l>AC)mW!g6w4C[LgMz';
        $secret_iv = 'dq+;{dB-Wa8R61(`UK5FglVP`P>MW+y!T{f-gc-k#Gu#~gxu1GYmzNA].P~@h&:,';
        // Преобразуем данные
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        // Основной скрипт
        if ($action) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}

if (!function_exists('generatePassword')) {
    function generatePassword()
    {
        return str_shuffle(bin2hex(openssl_random_pseudo_bytes(4)));
    }
}

if (!function_exists('uri')) {
    function uri($num){
        $CI  = &get_instance();
        return clear($CI->uri->segment($num));
    }
}

if (!function_exists('clear')) {
    function clear($str, $type = '0')
    {
        $str = trim($str);
        if ($type == 'email') {
            if (filter_var($str, FILTER_VALIDATE_EMAIL) === false) {
                $str = "";
            }
        } else if ($type == 1 or $type == 'int') {
            $str = intval($str);
        } else if ($type == 2 or $type == 'float') {
            $str = str_replace(",", ".", $str);
            $str = floatval($str);
        } else if ($type == 3 or $type == 'regx') {
            $str = preg_replace("/[^a-zA-ZА-Яа-я0-9.,!\s]/", "", $str);
        } else if ($type == 'alias') {
            $str = preg_replace("/[^a-zA-Z0-9_-\s]/", "", $str);
        } else if ($type == 4 or $type == 'text') {
            $str = str_replace("'", "&#8242;", $str);
            $str = str_replace("\"", "&#34;", $str);
            $str = stripslashes($str);
            $str = htmlspecialchars($str);
        } else {
            $str = strip_tags($str);
            $str = str_replace("\n", " ", $str);
            $str = str_replace("'", "&#8242;", $str);
            $str = str_replace("\"", "&#34;", $str);
            $str = preg_replace('!\s+!', ' ', $str);
            $str = stripslashes($str);
            $str = htmlspecialchars($str);
        }
        return $str;
    }
}

if(!function_exists('getRealCatChilds')) {
    function getRealCatChilds($id_parent){
        $CI      =&get_instance();
        $dataset = $CI->db->select('id, parent_id')->get('category')->result_array();
        $dataset = key_to_id($dataset);
        $ids     = array();
        foreach ($dataset as $id => &$node) {
            if ($id_parent==$id) {
                $ids[] = $id;
                $id_parent=$id;
            } else {
                $dataset[$node['parent_id']]['childs'][$id] =& $node;
                if ($id_parent==$node['parent_id'] or in_array($node['parent_id'], $ids)) {
                    $ids[] = $id;
                    $id_parent=$id;
                }
            }
        }
        return $ids;
    }
}

if (!function_exists('key_to_id')) {
    function key_to_id($array) {
        if (empty($array)) {
            return array();
        }
        $new_arr = array();
        foreach ($array as $id => &$node) {
            $new_arr[$node['id']] =& $node;
        }
        return $new_arr;
    }
}

if (!function_exists('getRealIpAddr')) {
    function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $_SERVER['REMOTE_ADDR'];
    }
}

if (!function_exists('GUID')) {
    function GUID() {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}

if (!function_exists('user_login')) {
    function user_login($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->fullname;
        $_SESSION['user_key'] = hash('sha512', $user->email . $user->password . $user->id );
    }
}

if (!function_exists('user_is_logged_in')) {
    function user_is_logged_in()
    {
        $user_id = @$_SESSION['user_id'];
        $name = @$_SESSION['user_name'];
        $key = @$_SESSION['user_key'];
        $isb2b = @$_SESSION['isb2b'];

        $lang = strtolower($_SESSION['lang']);
        if (empty($user_id) || empty($name) || empty($key) || !isset($isb2b)) {
            unset($_SESSION['user_id']);
            unset($_SESSION['user_name']);
            unset($_SESSION['user_key']);
            unset($_SESSION['isb2b']);
            return false;
        }
        return true;
    }
}

if (!function_exists('user_is_auth')) {
    function user_is_auth($lang)
    {
        $CI = &get_instance();
        $user_id = @$_SESSION['user_id'];
        $user_name = @$_SESSION['user_name'];
        $user_key = @$_SESSION['user_key'];
        $isb2b = @$_SESSION['isb2b'];

        try {
            if(empty($user_id) || empty($user_name) || empty($user_key) || !isset($isb2b)) {
                throw new Exception('USER ERROR');
            }

            $user = $CI->db->where('ID', $user_id)->get('users')->row();

            if (empty($user)) {
                throw new Exception('USER ERROR');
            }

            $key_check = hash('sha512', $user->email . $user->password . $user->id );

            if ($user_key != $key_check) {
                throw new Exception('Key ERROR');
            }
        } catch (Exception $e) {
            unset($_SESSION['user_id']);
            unset($_SESSION['user_name']);
            unset($_SESSION['user_key']);
            unset($_SESSION['isb2b']);
            redirect('/');
        }
    }
}

if (!function_exists('sync_cart')) {
    function sync_cart()
    {
        $CI = &get_instance();
        $CI->load->library(['cart']);
        $CI->load->model('products_model');

        $CI->load->model('constants_model');
        $lang = mb_strtoupper('EN');
        $constants = $CI->constants_model->find();
        foreach ($constants as $constant) {
            if (!defined($constant->name)) {
                define($constant->name, $constant->$lang);
            }
        }
        $cart_items = $CI->cart->contents();
        foreach ($cart_items as $cart_items_item) {
            $cart_product = $CI->products_model->product_item_cart('ro', $cart_items_item['rowid']);
            if (!empty($cart_product)) {

                if (!empty($_SESSION['isb2b'])) {
                    $cart_product->price = $cart_product->priceWH;
                } else {
                    if (!empty($cart_product->discount_price) && $cart_product->discount_price > 0) {
                        $cart_product->price = $cart_product->discount_price;
                    }
                }
                if (!empty($cart_items_item['options'])) {
                    $variable =  $CI->products_model->get_product_variable_by_id('RO', $cart_items_item['options'], $cart_product->id);
                    if (!empty($_SESSION['isb2b'])) {
                        $cart_product->price = $cart_product->priceWH;
                    } else {
                        if (!empty($variable->discount_price) && $variable->discount_price > 0) {
                            $cart_product->price = $variable->discount_price;
                        } else {
                            $cart_product->price = $variable->price;
                        }
                    }
                    if (!empty($_SESSION['isb2b'])) {
                        $cart_product->on_stock = $variable->qtyWH;
                    } else {
                        $cart_product->on_stock = $variable->qty;
                    }
                }
                $row_id = $cart_items_item['rowid'];
                $quantity = $cart_items_item['qty'];
                if ($quantity <= 0) {
                    $CI->cart->remove($row_id);
                } else {
                    if (!empty($_SESSION['isb2b'])) {
                        if ($quantity > $cart_product->on_stockWH) $quantity = $cart_product->on_stockWH;
                    } else {
                        if ($quantity > $cart_product->on_stock) $quantity = $cart_product->on_stock;
                    }

                    $CI->cart->update(array('rowid' => $row_id, 'qty' => $quantity, 'price' => $cart_product->price));
                }
            }else{
                $CI->cart->remove($cart_items_item['rowid']);
            }
        }
    }
}

if (!function_exists('menu_map')) {
    function menu_map($objects, $parent = 0) {

        $result = array();
        foreach ($objects as $object) {

            if ($object->parent_id == $parent) {
                $result[$object->id] = (object) array();
                $result[$object->id]->id = $object->id;
                $result[$object->id]->parent_id = $object->parent_id;
                $result[$object->id]->menu_category_id = $object->menu_category_id;
                $result[$object->id]->title = $object->title;
                $result[$object->id]->uri = $object->uri;
                $result[$object->id]->isShown = $object->isShown;
                $result[$object->id]->onTop = $object->onTop;
                $result[$object->id]->onBottom = $object->onBottom;
                $result[$object->id]->children = menu_map($objects, $object->id, false);
            }

        }

        return $result;
    }
}
if (!function_exists('options_categories')) {
    function options_categories($objects, $parent = 0)
    {
        $result = array();
        foreach ($objects as $object) {
            if ($object->parent_id == $parent) {
                $result[$object->id]['id'] = $object->id;
                $result[$object->id]['title'] = $object->title;
                $result[$object->id]['parent_id'] = $object->parent_id;
                $result[$object->id]['uri'] = $object->uri;
                $result[$object->id]['children'] = options_categories($objects, $object->id);
            }
        }

        return $result;
    }
}
if (!function_exists('jstree')) {
    function jstree($data, $parent = 0)
    {
        ?>
        <ul>
            <? foreach ($data as $item) {
                if ($item->parent_id == $parent) { ?>
                    <li data-jstree='{"id":"<?= $item->id ?>","icon":"fa fa-folder", "selected":<?= isset($_GET['cat']) && $_GET['cat'] == $item->id ? "true" : "false" ?>, "opened":<?= isset($_GET['category_id']) && $_GET['category_id'] == $item->id ? "true" : "false" ?>}'
                        data-id="<?= $item->id ?>" data-parent_id="<?= $item->parent_id ?>">
                        <span><?= $item->title ?></span>
                        <?= jstree($data, $item->id) ?>
                    </li>
                    <?
                } ?>
                <?
            } ?>
        </ul>
        <?php
    }
};
if (!function_exists('categories_map')) {
    function categories_map($objects, $parent = 0, $clear=true) {

        $result = array();
        if($clear) unset($_SESSION['all_cat_children']);
        foreach ($objects as $object) {

            if ($object->parent_id == $parent) {
                $_SESSION['all_cat_children'][] = $object->id;
                $result[$object->id] = (object) array();
                $result[$object->id]->id = $object->id;
                $result[$object->id]->parent_id = $object->parent_id;
                $result[$object->id]->title = $object->title;
                $result[$object->id]->uri = $object->uri;
                $result[$object->id]->onMain = $object->onMain;
                $result[$object->id]->min_price = $object->min_price;
                $result[$object->id]->img = $object->img;
                $result[$object->id]->children = categories_map($objects, $object->id, false);
            }

        }

        return $result;
    }
}

if (!function_exists('categories_cat_ids')) {
    function categories_cat_ids($objects, $id = 0) {

        foreach ($objects as $object) {
            if ($object->id == $id && $id !=0 ) {
                $_SESSION['cat_ids'][$object->level] = $object->id;
                categories_cat_ids($objects, $object->parent_id);
            }
        }
    }
}

if (!function_exists('admin_categories_tree')) {
    function admin_categories_tree($objects, $e_path, $del_path, $parent_id = 0)
    {
        foreach ($objects as $item) {
            if ($item->parent_id == $parent_id) {
                $cmod = (!empty($item->isShown)) ? 'checked' : '';
                $cmod3 = (!empty($item->isCustomMobile)) ? 'checked' : '';
                $cmod2 = (!empty($item->top_menu)) ? 'checked' : '';
                $self_class = 'treegrid-' . $item->id;
                $parent_class = ($parent_id != 0) ? 'treegrid-parent-' . $parent_id : '';
                echo '
                    <tr class="' . $self_class . ' ' . $parent_class . '" style="height: 51px;">
                        <td class="align-middle td-flex">
                        <input style="width:50px;margin-right:15px" type="text" onkeyup="this.value=this.value.replace(/[^\d]/,\'\')" min="1"
                        class="form-control text-center sorder" value="' . $item->sorder . '"
                        name="so[' . $item->id . ']">
                        <a style="font-weight: 900;"
                            href="' . $e_path . $item->id . '">' . $item->{'title'.get_language_for_admin(true)} . '</a></td>
                        <td class="align-middle" >
                            <label class="mt-checkbox mt-checkbox-outline">
                                <input type="checkbox" ' . $cmod . ' value="' . $item->id . '" data-table="categories" data-col="isShown"
                                class="mine_change_check"> '  . lang('Show on site'). '
                                <span></span>
                            </label>
                        </td> 
                        <td class="align-middle" >
                            <label class="mt-checkbox mt-checkbox-outline">
                                <input type="checkbox" ' . $cmod3 . ' value="' . $item->id . '" data-table="categories" data-col="isCustomMobile"
                                class="mine_change_check"> isCustomMobile
                                <span></span>
                            </label>
                        </td> 
                        <td class="align-middle" >
                            <label class="mt-checkbox mt-checkbox-outline">
                                <input type="checkbox" ' . $cmod2 . ' value="' . $item->id . '" data-table="categories" data-col="top_menu"
                                class="mine_change_check"> '  . lang('Show on main'). '
                                <span></span>
                            </label>
                        </td>
                        <td class="align-middle">
                            <a href="' . $e_path . $item->id . '/' . '"
                            class="btn btn-xs default btn-editable green-stripe">
                                <i class="glyphicon glyphicon-edit"></i> '  . lang('Edit'). '
                            </a>
                                <a href="' . $del_path . $item->id . '/' . '"
                                class="btn btn-xs default btn-editable red-stripe mine_delete_row">
                                    <i class="glyphicon glyphicon-remove-circle"></i> '  . lang('Add'). '
                                </a>
                        </td>
                    </tr>';
                admin_categories_tree($objects, $e_path, $del_path, $item->id);
            }
        }
    }
}

if(!function_exists('percentage')) {
    function percentage($NumberFour, $Everything) {
        $percentage = ( $NumberFour / $Everything ) * 100;
        $percentage = number_format($percentage, 0);
        $discount = (100 -$percentage) * -1;
        $display = $discount.'%';
        return $display;
    }
}

if (!function_exists('admin_categories_tree_with_products')) {
    function admin_categories_tree_with_products($categories, $table, $ids, $objects, $e_path, $del_path, $parent_id = 0)
    {
        foreach ($categories as $item) {
            if ($item->parent_id == $parent_id) {
                $self_class = 'treegrid-' . $item->id;
                $parent_class = ($parent_id != 0) ? 'treegrid-parent-' . $parent_id : '';

                if(!in_array($item->id, $ids)){
                    echo '
                    <tr class="' . $self_class . ' ' . $parent_class . '">
                        <td colspan="7" class="align-middle"><span class="caption-subject bold font-grey-gallery uppercase">' . $item->{'title'.get_language_for_admin(true)}.'</span></td>
                    </tr>';
                } else {
                    echo '
                    <tr class="' . $self_class . ' ' . $parent_class . '">
                        <td colspan="7" class="align-middle"><span class="caption-subject bold font-grey-gallery uppercase">' . $item->{'title'.get_language_for_admin(true)}.'</span>
                            <a style="float:right" data-id="'.$item->id.'" class="category_ajax" href="/"><i class="fa fa-plus"></i>&nbsp;<span>'  . lang('Show products'). '</span></a>
                        
                            <table class="table table-hover table-hide" style="margin-top:10px;">
                                <tr>
                                    <th>Название</th>
                                    <th width="80" class="mine-center-item">on Toate produsele</th>
                                    <th width="80" class="mine-center-item">'.lang('On site').'</th>
                                    <th width="80" class="mine-center-item">'.lang('Popular').'</th>
                                    <th width="80" class="mine-center-item">'.lang('New').'</th>
                                    <th width="110" class="mine-center-item">'.lang('Product day').'</th>
                                    <th width="320" class="mine-center-item">'.lang('Action').'</th>
                                </tr>';

                    foreach($objects as $object) {
                        $cmod5 = (!empty($object->toateHainele)) ? 'checked' : '';
                        $cmod1 = (!empty($object->isShown)) ? 'checked' : '';
                        $cmod2 = (!empty($object->isPopular)) ? 'checked' : '';
                        $cmod3 = (!empty($object->isNew)) ? 'checked' : '';
                        $cmod4 = (!empty($object->isToday)) ? 'checked' : '';
                        if($object->category_id == $item->id) {
                            echo '<tr>
                                        <td class="align-middle td-flex">
                                        <input style="width:50px;margin-right:15px" type="text" onkeyup="this.value=this.value.replace(/[^\d]/,\'\')" min="1"
                                                class="form-control text-center sorder" value="' . $object->sorder . '"
                                                name="so[' . $object->id . ']">
                                        <a style="font-weight: 900;"
                                            href="' . $e_path . $object->id . '">' . $object->{'title'.get_language_for_admin(true)} . '</a></td>
                                        <td class="align-middle">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input data-col="toateHainele" data-table="' . $table . '" type="checkbox" ' . $cmod5 . ' value="' . $object->id . '"
                                                        class="mine_change_check">&nbsp;
                                                    <span></span>
                                                </label>
                                            </td>
                                        <td class="align-middle">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input data-col="isShown" data-table="'.$table.'" type="checkbox" '.$cmod1.' value="'.$object->id.'"
                                                        class="mine_change_check">&nbsp;
                                                    <span></span>
                                                </label>
                                            </td>
                                        <td class="align-middle">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input data-col="isPopular" data-table="'.$table.'" type="checkbox" '.$cmod2.' value="'.$object->id.'"
                                                        class="mine_change_check">&nbsp;
                                                    <span></span>
                                                </label>
                                            </td>
                                        <td class="align-middle">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input data-col="isNew" data-table="'.$table.'" type="checkbox" '.$cmod3.' value="'.$object->id.'"
                                                        class="mine_change_check">&nbsp;
                                                    <span></span>
                                                </label>
                                            </td>
                                        <td class="align-middle">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input data-col="isToday" data-table="'.$table.'" type="checkbox" '.$cmod4.' value="'.$object->id.'"
                                                        class="mine_change_check">&nbsp;
                                                    <span></span>
                                                </label>
                                            </td>
                                        <td width="320" class="align-middle">
                                            <a href="' . $e_path . $object->id . '/' . '"
                                            class="btn btn-xs default btn-editable green-stripe">
                                                <i class="glyphicon glyphicon-edit"></i> '  . lang('Edit'). '
                                            </a>
                                                <a href="' . $del_path . $object->id . '/' . '"
                                                class="btn btn-xs default btn-editable red-stripe mine_delete_row">
                                                    <i class="glyphicon glyphicon-remove-circle"></i> '  . lang('Delete'). '
                                                </a>
                                                <a href="/cp/products/duplicate?id=' . $object->id . '"
                                           class="btn btn-xs default btn-editable green-stripe duplicate">
                                            <i class="fa fa-object-ungroup"></i> Duplicate
                                        </a>
                                        </td>
                                    </tr>';
                        }
                    }
                    echo '
                            </table>

                        </td>
                    </tr>';
                }

                admin_categories_tree_with_products($categories, $table, $ids, $objects, $e_path, $del_path,  $item->id);
            }
        }
    }
}

if (!function_exists('admin_categories_map')) {
    function admin_categories_map($objects, $parent = 0) {

        $result = array();
        foreach ($objects as $object) {

            if ($object->parent_id == $parent) {
                $result[$object->id] = (object) array();
                $result[$object->id]->id = $object->id;
                $result[$object->id]->parent_id = $object->parent_id;
                $result[$object->id]->{'title'.get_language_for_admin(true)} = $object->{'title'.get_language_for_admin(true)};
                $result[$object->id]->children = admin_categories_map($objects, $object->id);
            }
        }

        return $result;
    }
}

if (!function_exists('admin_categories_json')) {
    function admin_categories_json($objects, $parent = 0, $parent_id = null, $id = null) {

        $result = '';
        foreach ($objects as $object) {
            if ($object->parent_id == $parent) {
                $child = admin_categories_json($objects, $object->id, $parent_id, $id);

                $selected = ($object->id == $parent_id) ? true : false;
                $disabled = ($object->id == $id) ? true : false;

                $result .= "{text:'".$object->{'title'.get_language_for_admin(true)}."',state:{selected:'".$selected."',disabled:'".$disabled."'}, id:".$object->id.", children:[".json_encode($child, JSON_UNESCAPED_UNICODE)."]},";
            }
        }

        $result = str_replace('"', '', $result);
        $result = str_replace('\\', '', $result);

        return $result;
    }
}

if (!function_exists('updateChar')) {
    function updateChar($str, $char, $offset) {
        if(!is_array($offset)){
            if ( ! isset($str[$offset])) {
                return FALSE;
            }
            $str[$offset] = $char;
        } else {
            foreach($offset as $item){
                if ( ! isset($str[$item])) {
                    return FALSE;
                }
                $str[$item] = $char;
            }
        }
        return $str;
    }
}

if (!function_exists('get_youtube_video_ID')) {
    function get_youtube_video_ID($youtube_video_url) {
        /**
         * Pattern matches
         * http://youtu.be/ID
         * http://www.youtube.com/embed/ID
         * http://www.youtube.com/watch?v=ID
         * http://www.youtube.com/?v=ID
         * http://www.youtube.com/v/ID
         * http://www.youtube.com/e/ID
         * http://www.youtube.com/user/username#p/u/11/ID
         * http://www.youtube.com/leogopal#p/c/playlistID/0/ID
         * http://www.youtube.com/watch?feature=player_embedded&v=ID
         * http://www.youtube.com/?feature=player_embedded&v=ID
         */
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
        if (preg_match($pattern, $youtube_video_url, $match)) {
            return $match[1];
        }
        // if no match return false.
        return false;
    }
}

if(!function_exists('admin_get_filters_by_category')) {
    function admin_get_filters_by_category($filter_groups_ids = array(), $product_ids = array()) {

        $lang_values = "";
        $lang_titles = "";

        foreach(language(true) as $lang){
            $lang_values .= "value".strtoupper($lang).",";
            $lang_titles .= "title".strtoupper($lang).",";
        }

        $CI =& get_instance();
        $filter_groups = $CI->db->select('
            ' . $lang_titles . '
            id as filter_group_id, 
            ')->where_in('id', $filter_groups_ids)
            ->get('filter_groups')
            ->result();

        foreach($filter_groups as $filter_group) {
            $filters = $CI->db->select('
            id as id,
            ' . $lang_titles . '
            ')->where('filter_group_id', $filter_group->filter_group_id)->get('filters')->result();

            if(!empty($filters)) {
                foreach($filters as $filter) {
                    $values = $CI->db->select('filter_id, '. $lang_values . ' COUNT(filter_id) as count')
                        ->where('filter_id', $filter->id)
                        ->where_in('product_id', $product_ids)
                        ->group_by('valueRO')
                        ->get('product_filters_value')
                        ->result();

                    $filter->values = $values;
                }
            }

            $filter_group->filters = $filters;
        }

        return $filter_groups;
    }
}

if(!function_exists('update_category_has_products')) {
    function update_category_has_products() {

        $CI =& get_instance();

        $CI->db->update("categories", array("has_products" => 0));

        $CI->db->where_in('id', array(128,133))->update("categories", array("has_products" => 1));

        $query = $CI->db->where("isShown", 1)->where("price >", 0)->where("qty > ", 0)->get("products")->result();

        $ids = array_map(function ($item){ return $item->category_id;}, $query);
        $ids = array_unique($ids);

        $categories = $CI->db->get("categories")->result();

        $to_update = array();

        foreach ($ids as $id){
            unset($_SESSION['cat_ids']);
            categories_cat_ids($categories, $id);
            if(!isset($_SESSION['cat_ids'])) $_SESSION['cat_ids'] = array();
            $to_update = array_merge($to_update, $_SESSION['cat_ids']);
        }

        $to_update = array_unique($to_update);

        if(!empty($to_update)) $CI->db->where_in("id", $to_update)->update("categories", array("has_products" => 1));
    }
}

if(!function_exists('sendNotification')) {
    function sendNotification($to, $subject, $message, $attach = false): bool
    {
        $CI =& get_instance();

        $CI->load->library('email');

        $config = config_smtp();

        $CI->email->initialize($config);
        $CI->email->from('noreply@' . $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
        $CI->email->to($to);
        $CI->email->reply_to('no-reply@' . $_SERVER['HTTP_HOST']);
        $CI->email->subject($subject);
        $CI->email->message($message);
        if ($attach) $CI->email->attach($attach);
        if ($CI->email->send()) {
            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('url_exists')) {
    function url_exists($url): bool
    {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3)==200;
    }
}

if (!function_exists('transformDate')) {
    function transformDate($date = '2022-01-1', $lang = 'ru')
    {
        if ($lang == 'ru') {
            $months = array('01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель', '05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь');
        } else {
            $months = array('01' => 'ianuarie', '02' => 'februarie', '03' => 'martie', '04' => 'aprilie', '05' => 'mai', '06' => 'iunie', '07' => 'iulie', '08' => 'august', '09' => 'septembrie', 10 => 'octombrie', 11 => 'noiembrie', 12 => 'decembrie');
        }
        $date = date("d", strtotime($date)) . ' ' . $months[date("m", strtotime($date))] . ' ' . date("Y", strtotime($date));

        return $date;
    }
};
if (!function_exists('siteMapGenerator')) {
    function siteMapGenerator()
    {
        set_time_limit(900);

        $CI =& get_instance();

        $CI->load->model([
            'products_model' => 'products',
            'categories_model' => 'categories',
            'menu_model' => 'menu',
            'articles_model' => 'news',
            'brands_model' => 'brand',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Base config
        |--------------------------------------------------------------------------
        */

        if (ENVIRONMENT === 'production') {
            $host = 'https://vizaje-nica.com';
        } else {
            $protocol =
                (
                    isset($_SERVER['HTTPS'])
                    && $_SERVER['HTTPS'] !== 'off'
                )
                    ? 'https://'
                    : 'http://';

            $host =
                $protocol
                . $_SERVER['HTTP_HOST'];
        }

        $sitemapPath =
            FCPATH
            . 'sitemap.xml';

        $langs =
            language(true);

        /*
        |--------------------------------------------------------------------------
        | URL storage
        |--------------------------------------------------------------------------
        |
        | Ассоциативный массив используется,
        | чтобы автоматически удалять дубли.
        |
        */

        $urls = [];

        $addUrl =
            static function (
                &$urls,
                $url
            ) {
                $url =
                    trim(
                        (string) $url
                    );

                if ($url === '') {
                    return;
                }

                $urls[$url] = true;
            };

        /*
        |--------------------------------------------------------------------------
        | Root homepage
        |--------------------------------------------------------------------------
        */

        $addUrl(
            $urls,
            $host . '/'
        );

        /*
        |--------------------------------------------------------------------------
        | Language homepages
        |--------------------------------------------------------------------------
        */

        foreach (
            $langs
            as $lang
        ) {
            $addUrl(
                $urls,
                $host
                . '/'
                . $lang
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Menu pages
        |--------------------------------------------------------------------------
        */

        $pages =
            $CI->menu
                ->get_feed_menu();

        /*
         * Legacy ID exclusions.
         *
         * Пока сохраняем текущую бизнес-логику.
         */
        $excludedPageIds = [
            1,
            25,
            21,
            20,
            19,
            18,
            27,
            16,
            15,
            14,
            12,
        ];

        /*
         * Дополнительная страховка по URI.
         *
         * Эти страницы не должны попадать
         * в sitemap даже если ID в базе изменятся.
         */
        $excludedUris = [
            '404',

            'favorite',
            'izbrannyie',

            'cos',
            'korzina',

            'checkout',
            'oformlenie-zakaza',

            'vhod',
            'intrare',
            'login',

            'registratsiya',
            'inregistrare',
            'registration',

            'password_reset',
            'resetati-parola',
            'vosstanovlenie-parolya',

            'logout',
        ];

        foreach (
            $pages
            as $page
        ) {
            if (
                in_array(
                    (int) $page->id,
                    $excludedPageIds,
                    true
                )
            ) {
                continue;
            }

            foreach (
                $langs
                as $lang
            ) {
                $field =
                    'uri'
                    . strtoupper(
                        $lang
                    );

                if (
                    empty(
                        $page->{$field}
                    )
                ) {
                    continue;
                }

                $pageUri =
                    trim(
                        (string) $page->{$field},
                        '/'
                    );

                if (
                    $pageUri === ''
                    || in_array(
                        $pageUri,
                        $excludedUris,
                        true
                    )
                ) {
                    continue;
                }

                $addUrl(
                    $urls,
                    $host
                    . '/'
                    . $lang
                    . '/'
                    . $pageUri
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Catalog config
        |--------------------------------------------------------------------------
        */

        $catalog =
            $CI->db
                ->where(
                    'id',
                    3
                )
                ->get(
                    'menu'
                )
                ->row();

        if (empty($catalog)) {
            show_error(
                'Catalog menu item not found'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories =
            $CI->categories
                ->getByField(
                    'isShown',
                    1,
                    true,
                    'sorder'
                );

        foreach (
            $categories
            as $category
        ) {
            foreach (
                $langs
                as $lang
            ) {
                $langUpper =
                    strtoupper(
                        $lang
                    );

                $catalogUri =
                    $catalog
                        ->{'uri' . $langUpper};

                $categoryUri =
                    $category
                        ->{'uri' . $langUpper};

                if (
                    empty($catalogUri)
                    || empty($categoryUri)
                ) {
                    continue;
                }

                $categoryUrl =
                    $host
                    . '/'
                    . $lang
                    . '/'
                    . trim(
                        $catalogUri,
                        '/'
                    )
                    . '/'
                    . trim(
                        $categoryUri,
                        '/'
                    );

                $addUrl(
                    $urls,
                    $categoryUrl
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Brands
        |--------------------------------------------------------------------------
        |
        | Brand index:
        |
        | /ro/brands
        | /ru/brendyi
        |
        | Brand pages:
        |
        | /ro/catalog/{brand}
        | /ru/katalog/{brand}
        |
        */

        $brands =
            $CI->brand
                ->getByField(
                    'isShown',
                    1,
                    true,
                    'sorder'
                );

        foreach (
            $brands
            as $brand
        ) {
            $brandUri =
                trim(
                    (string) $brand->uri,
                    '/'
                );

            if ($brandUri === '') {
                continue;
            }

            foreach (
                $langs
                as $lang
            ) {
                $langUpper =
                    strtoupper(
                        $lang
                    );

                $catalogUri =
                    $catalog
                        ->{'uri' . $langUpper};

                if (
                    empty(
                        $catalogUri
                    )
                ) {
                    continue;
                }

                $brandUrl =
                    $host
                    . '/'
                    . $lang
                    . '/'
                    . trim(
                        $catalogUri,
                        '/'
                    )
                    . '/'
                    . $brandUri;

                $addUrl(
                    $urls,
                    $brandUrl
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | News
        |--------------------------------------------------------------------------
        */

        $newsPage =
            $CI->db
                ->where(
                    'id',
                    37
                )
                ->get(
                    'menu'
                )
                ->row();

        $news =
            $CI->news
                ->getByField(
                    'isShown',
                    1,
                    true,
                    'sorder'
                );

        if (
            !empty($newsPage)
        ) {
            /*
            |--------------------------------------------------------------------------
            | News index
            |--------------------------------------------------------------------------
            */

            foreach (
                $langs
                as $lang
            ) {
                $langUpper =
                    strtoupper(
                        $lang
                    );

                $newsIndexUri =
                    $newsPage
                        ->{'uri' . $langUpper};

                if (
                    empty(
                        $newsIndexUri
                    )
                ) {
                    continue;
                }

                $newsIndexUrl =
                    $host
                    . '/'
                    . $lang
                    . '/'
                    . trim(
                        $newsIndexUri,
                        '/'
                    );

                $addUrl(
                    $urls,
                    $newsIndexUrl
                );
            }

            /*
            |--------------------------------------------------------------------------
            | News articles
            |--------------------------------------------------------------------------
            */

            foreach (
                $news
                as $article
            ) {
                foreach (
                    $langs
                    as $lang
                ) {
                    $langUpper =
                        strtoupper(
                            $lang
                        );

                    $newsIndexUri =
                        $newsPage
                            ->{'uri' . $langUpper};

                    $articleUriField =
                        'uri'
                        . $langUpper;

                    if (
                        !empty(
                            $article
                                ->{$articleUriField}
                        )
                    ) {
                        $articleUri =
                            $article
                                ->{$articleUriField};
                    } else {
                        $articleUri =
                            $article->uri
                            ?? '';
                    }

                    $articleUri =
                        trim(
                            (string) $articleUri,
                            '/'
                        );

                    if (
                        empty(
                            $newsIndexUri
                        )
                        || $articleUri === ''
                    ) {
                        continue;
                    }

                    $articleUrl =
                        $host
                        . '/'
                        . $lang
                        . '/'
                        . trim(
                            $newsIndexUri,
                            '/'
                        )
                        . '/'
                        . $articleUri;

                    $addUrl(
                        $urls,
                        $articleUrl
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        |
        | В sitemap попадают:
        |
        | - только isShown = 1
        | - только товары с опубликованной категорией
        |
        | Stock НЕ проверяем:
        | временно OutOfStock PDP остаются индексируемыми.
        |
        */

        $products =
            $CI->products
                ->getByField(
                    'isShown',
                    1,
                    true,
                    'sorder',
                    'ASC'
                );

        /*
         * Создаём map опубликованных категорий,
         * чтобы не делать отдельный SQL-запрос
         * для каждого товара.
         */
        $categoriesById = [];

        foreach (
            $categories
            as $category
        ) {
            $categoriesById[
                (int) $category->id
            ] = $category;
        }

        foreach (
            $products
            as $product
        ) {
            $categoryId =
                (int) $product
                    ->category_id;

            if (
                !isset(
                    $categoriesById[
                        $categoryId
                    ]
                )
            ) {
                continue;
            }

            $category =
                $categoriesById[
                    $categoryId
                ];

            foreach (
                $langs
                as $lang
            ) {
                $langUpper =
                    strtoupper(
                        $lang
                    );

                $catalogUri =
                    $catalog
                        ->{'uri' . $langUpper};

                $categoryUri =
                    $category
                        ->{'uri' . $langUpper};

                $productUri =
                    $product
                        ->{'uri' . $langUpper};

                if (
                    empty($catalogUri)
                    || empty($categoryUri)
                    || empty($productUri)
                ) {
                    continue;
                }

                $productUrl =
                    $host
                    . '/'
                    . $lang
                    . '/'
                    . trim(
                        $catalogUri,
                        '/'
                    )
                    . '/'
                    . trim(
                        $categoryUri,
                        '/'
                    )
                    . '/'
                    . trim(
                        $productUri,
                        '/'
                    );

                $addUrl(
                    $urls,
                    $productUrl
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Build XML
        |--------------------------------------------------------------------------
        */

        $xml =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . PHP_EOL;

        $xml .=
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            . PHP_EOL;

        foreach (
            array_keys($urls)
            as $url
        ) {
            $xml .=
                '    <url>'
                . PHP_EOL;

            $xml .=
                '        <loc>'
                . htmlspecialchars(
                    $url,
                    ENT_XML1
                    | ENT_QUOTES,
                    'UTF-8'
                )
                . '</loc>'
                . PHP_EOL;

            $xml .=
                '    </url>'
                . PHP_EOL;
        }

        $xml .=
            '</urlset>'
            . PHP_EOL;

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $result =
            file_put_contents(
                $sitemapPath,
                $xml,
                LOCK_EX
            );

        if (
            $result === false
        ) {
            show_error(
                'Unable to write sitemap.xml'
            );

            return;
        }

        echo
            'Done successful. URLs: '
            . count($urls);
    }
}
if (!function_exists('sex_translate')) {
function sex_translate($sex)
    {
        switch ($sex) {
            case 'Женский':
                $name = FEMEIE;
                break;
            case 'Мужской':
                $name = MALE;
                break;
            case 'Унисекс':
                $name = UNISEX;
                break;
        }

        return $name;
    }
}

if (!function_exists('config_smtp')) {

    function config_smtp($type = 'gmail')
    {
        return [
            'protocol'  => 'smtp',
            'smtp_host' => 'ssl://smtp.gmail.com',
            'smtp_port' => 465,
            'smtp_user' => 'noreply@vizaje-nica.com',
            'smtp_pass' => 'qnrs czgj awsr wvyu',
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'newline'   => "\r\n",
            'crlf'      => "\r\n",
            'smtp_timeout' => 30,
            'wordwrap'  => TRUE,
            'validate'  => TRUE
        ];
    }
}



/**
 * Общий свитчер
 */
if (!function_exists('kb_switch')) {
    function kb_switch($str, $map)
    {
        return strtr($str, $map);
    }
}

/**
 * EN → RU (раскладка)
 */
if (!function_exists('switcher_ru')) {
    function switcher_ru($str)
    {
        static $map = [
            'f' => 'а', ',' => 'б', 'd' => 'в', 'u' => 'г', 'l' => 'д', 't' => 'е', '`' => 'ё',
            ';' => 'ж', 'p' => 'з', 'b' => 'и', 'q' => 'й', 'r' => 'к', 'k' => 'л', 'v' => 'м',
            'y' => 'н', 'j' => 'о', 'g' => 'п', 'h' => 'р', 'c' => 'с', 'n' => 'т', 'e' => 'у',
            'a' => 'ф', '[' => 'х', 'w' => 'ц', 'x' => 'ч', 'i' => 'ш', 'o' => 'щ', 'm' => 'ь',
            's' => 'ы', ']' => 'ъ', "'" => 'э', '.' => 'ю', 'z' => 'я',

            'F' => 'А', '<' => 'Б', 'D' => 'В', 'U' => 'Г', 'L' => 'Д', 'T' => 'Е', '~' => 'Ё',
            ':' => 'Ж', 'P' => 'З', 'B' => 'И', 'Q' => 'Й', 'R' => 'К', 'K' => 'Л', 'V' => 'М',
            'Y' => 'Н', 'J' => 'О', 'G' => 'П', 'H' => 'Р', 'C' => 'С', 'N' => 'Т', 'E' => 'У',
            'A' => 'Ф', '{' => 'Х', 'W' => 'Ц', 'X' => 'Ч', 'I' => 'Ш', 'O' => 'Щ', 'M' => 'Ь',
            'S' => 'Ы', '}' => 'Ъ', '"' => 'Э', '>' => 'Ю', 'Z' => 'Я',

            '@' => '"', '#' => '№', '$' => ';', '^' => ':', '&' => '?', '/' => '.', '?' => ',',
        ];

        return kb_switch($str, $map);
    }
}

/**
 * RU → EN (раскладка)
 */
if (!function_exists('switcher_en')) {
    function switcher_en($str)
    {
        static $map = [
            'а' => 'f', 'б' => ',', 'в' => 'd', 'г' => 'u', 'д' => 'l', 'е' => 't', 'ё' => '`',
            'ж' => ';', 'з' => 'p', 'и' => 'b', 'й' => 'q', 'к' => 'r', 'л' => 'k', 'м' => 'v',
            'н' => 'y', 'о' => 'j', 'п' => 'g', 'р' => 'h', 'с' => 'c', 'т' => 'n', 'у' => 'e',
            'ф' => 'a', 'х' => '[', 'ц' => 'w', 'ч' => 'x', 'ш' => 'i', 'щ' => 'o', 'ь' => 'm',
            'ы' => 's', 'ъ' => ']', 'э' => "'", 'ю' => '.', 'я' => 'z',

            'А' => 'F', 'Б' => '<', 'В' => 'D', 'Г' => 'U', 'Д' => 'L', 'Е' => 'T', 'Ё' => '~',
            'Ж' => ':', 'З' => 'P', 'И' => 'B', 'Й' => 'Q', 'К' => 'R', 'Л' => 'K', 'М' => 'V',
            'Н' => 'Y', 'О' => 'J', 'П' => 'G', 'Р' => 'H', 'С' => 'C', 'Т' => 'N', 'У' => 'E',
            'Ф' => 'A', 'Х' => '{', 'Ц' => 'W', 'Ч' => 'X', 'Ш' => 'I', 'Щ' => 'O', 'Ь' => 'M',
            'Ы' => 'S', 'Ъ' => '}', 'Э' => '"', 'Ю' => '>', 'Я' => 'Z',

            '"' => '@', '№' => '#', ';' => '$', ':' => '^', '?' => '&', '.' => '/',
            ',' => '?'
        ];

        return kb_switch($str, $map);
    }
}

/**
 * EN → RU (транслит/фонетика)
 */
if (!function_exists('switcher_syntax_ru')) {
    function switcher_syntax_ru($str)
    {
        $str = mb_strtolower($str, 'UTF-8');

        // сначала сложные сочетания
        $map2 = [
            'sh' => 'ш', 'ch' => 'ч', 'ya' => 'я', 'yu' => 'ю', 'yo' => 'ё', 'zh' => 'ж'
        ];

        $str = strtr($str, $map2);

        // потом одиночные
        static $map = [
            'a' => 'а', 'b' => 'б', 'c' => 'к', 'd' => 'д', 'e' => 'е', 'f' => 'ф', 'g' => 'г', 'h' => 'х',
            'i' => 'и', 'j' => 'ж', 'k' => 'к', 'l' => 'л', 'm' => 'м', 'n' => 'н', 'o' => 'о', 'p' => 'п',
            'q' => 'к', 'r' => 'р', 's' => 'с', 't' => 'т', 'u' => 'у', 'v' => 'в', 'w' => 'в', 'x' => 'кс',
            'y' => 'и', 'z' => 'з'
        ];

        return kb_switch($str, $map);
    }
}

/**
 * RU → EN (транслит/фонетика)
 */
if (!function_exists('switcher_syntax_en')) {
    function switcher_syntax_en($str)
    {
        $str = mb_strtolower($str, 'UTF-8');

        static $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh',
            'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];

        return kb_switch($str, $map);
    }
}
