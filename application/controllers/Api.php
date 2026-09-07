<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Europe/Chisinau');

class Api extends CI_Controller

{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        header('Content-type: text/html; charset=utf-8');
        $this->load->library('session');
        $this->load->model('products_model');
    }

    public function url_chech()
    {
        $products = $this->db->select('id,titleRO,titleRU,uriRO,uriRU,SKU')->get('products')->result();

        $uri_array = array();
        foreach ($products as $product) {
            $uri_array[$product->uriRO][] = $product;
        }

        foreach ($uri_array as $item){
            if (count($item) > 1){
                foreach ($item as $value) {
                    $value->uriRO = transliteration($value->titleRO);
                    $value->uriRO = str_replace('â', 'a', $value->uriRO);
                    $value->uriRO = transliteration($value->uriRO.'-'.$value->SKU);

                    $value->uriRU = transliteration($value->titleRU);
                    $value->uriRU = str_replace('â', 'a', $value->uriRU);
                    $value->uriRU = transliteration($value->uriRU.'-'.$value->SKU);

                    $this->db->where('id', $value->id);
                    $this->db->update('products', $value);
                }
            }
        }
        dump($item);

        $update = array();
        foreach ($products as $product) {
            $product->uriRO = transliteration($product->titleRO);
            $product->uriRO = str_replace('â', 'a', $product->uriRO);
            $uri_empty = $this->products_model->get_by_uri($product->uriRO, 'RO');
            if (!empty($uri_empty) && $uri_empty->id != $product->id) $product->uriRO = transliteration($product->uriRO.'-'.$product->SKU);

            $product->uriRU = transliteration($product->titleRU);
            $product->uriRU = str_replace('â', 'a', $product->uriRU);
            $uri_emptyru = $this->products_model->get_by_uri($product->uriRU, 'RU');
            if (!empty($uri_emptyru) && $uri_emptyru->id != $product->id) $product->uriRU = transliteration($product->uriRU.'-'.$product->SKU);
            $update[] = array('id' => $product->id, 'uriRO' => $product->uriRO, 'uriRU' => $product->uriRU);
        }
//        $this->db->update_batch('products', $update, 'id');
    }

    public function order_json()
    {
        $users = 'vizajenica';
        $password = 'vizajenica2023';

        if ((!empty($_POST['users']) && $_POST['users'] == $users) && (!empty($_POST['password']) && $_POST['password'] == $password)) {

            $orders = $this->db->where('1c', 0)->get('orders')->result_array();

            $array_orders = array();
            $payment = array(
                '1' => 'Cash',
                '2' => 'Card',
                '3' => 'Online',
            );

            foreach ($orders as $order) {
                if ($order['payment'] == 2) $payment_id = 2; else $payment_id = $order['payment'];
                $products = $this->db->select('product_id as id, qty as quantity,price,options,
             (SELECT products.SKU FROM products WHERE products.id=orders_products.product_id) as SKU')->where('order_id', $order['id'])->get('orders_products')->result_array();

                foreach ($products as $key => $product) {
                    $products[$key]['variable'] = '';
                    if (!empty($product['options'])){
                        $variable = $this->db->select('SKU')->where('id', $product['options'])->get('products_variable')->row_array();
                        if (!empty($variable))
                            $products[$key]['variable'] = $variable['SKU'];
                    }
                }

                $array_orders[] = array(
                    'id' => $order['order_id'],
                    'status' => $order['status'],
                    'total' => $order['total'],
                    'delivery_price' => $order['delivery_price'],
                    'name' => $order['name'],
                    'surname' => $order['surname'],
                    'payment_name' => $payment[$order['payment']],
                    'payment' => $payment_id,
                    'email' => $order['email'],
                    'phone' => $order['phone'],
                    'address' => $order['address'],
                    'added' => $order['added'],
                    'products' => $products,
                );
            }
            echo json_encode($array_orders, JSON_UNESCAPED_UNICODE);
        } else {
            echo 'Error..';
        }

    }

    public function order_check()
    {
        $users = 'vizajenica';
        $password = 'vizajenica2023';

        if ((!empty($_POST['users']) && $_POST['users'] == $users) && (!empty($_POST['password']) && $_POST['password'] == $password)) {

            if (!empty($_POST['id'])) {
                $order = $this->db->where('order_id', $_POST['id'])->get('orders')->row_array();
                if (empty($order)) {
                    echo json_encode('Заказ не найден', JSON_UNESCAPED_UNICODE);
                } else {
                    $this->db->where('id', $order['id']);
                    $this->db->update('orders', array('1c' => 1));

                    $payment = array(
                        '1' => 'Cash',
                        '2' => 'Card',
                    );
                    if ($order['payment'] == 2) $payment_id = 2; else $payment_id = $order['payment'];
                    $products = $this->db->select('product_id as id, qty as quantity,price,
             (SELECT products.SKU FROM products WHERE products.id=orders_products.product_id) as SKU')->where('order_id', $order['id'])->get('orders_products')->result_array();
                    $array_orders = array(
                        'id' => $order['order_id'],
                        'status' => $order['status'],
                        'total' => $order['total'],
                        'delivery_price' => $order['delivery_price'],
                        'name' => $order['name'],
                        'surname' => $order['surname'],
                        'payment_name' => $payment[$order['payment']],
                        'payment' => $payment_id,
                        'email' => $order['email'],
                        'phone' => $order['phone'],
                        'address' => $order['address'],
                        'added' => $order['added'],
                        'products' => $products,
                    );
                    echo json_encode($array_orders, JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo json_encode('id не указан', JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo 'Error..';
        }
    }


}
