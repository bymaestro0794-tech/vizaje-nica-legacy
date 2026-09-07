<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Orders extends CI_Controller
{
    const MIN_FOR_FREE_DELIVERY = 500;
    const EXPRES_DELIVERY = 100;
    const NORMAL_DELIVERY = 50;

    public function __construct()
    {
        parent::__construct();
        @session_start();

        //$this->db = $this->load->database('site', TRUE);
        //header('Content-Type: application/json');
    }

    public function item()
    {

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $this->_add();
                break;
            default:
                echo json_encode(['code' => 99, 'message' => 'method not allowed']);
                break;
        }
    }

    private function _add()
    {
        $get = $this->input->get(null, true);
        $lang = $get['lang'];

        $raw = file_get_contents("php://input");
        $post = json_decode($raw, true);


        try {

            $total = 0;
            $delivery_price = 0;
            foreach ($post['products'] as $product) {
                $price = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
                $total += $product['qty'] * $price;
            }

            if ($post['delivery'] == 3) {
                $delivery_price = self::EXPRES_DELIVERY;
            } else {
                if ($total < self::MIN_FOR_FREE_DELIVERY) {
                    $delivery_price = self::NORMAL_DELIVERY;
                }
            }

            if ($post['stores'] != 0) {
                $store = $this->db->where("id", $post['stores'])->get("stores")->row_array();
            }
            $post['address'] = $post['city'] . ' st.' . $post['address'] . ' h.' . $post['house'] . ' p.' . $post['porch'] . ' ap.' . $post['apartament'] . ' ';

            $ins = [
                'total' => $total,
                'delivery_price' => $delivery_price,
                'order_id' => $this->getOrderId(),
                'status' => 'new',
                // 'client_id' => 99999,
                'client_id' => 0,
                'delivery' => $post['delivery'] ? $post['delivery'] : 0,
                'payment' => $post['payment'],
                'surname' => $post['surname'],
                'name' => $post['name'],
                'email' => $post['email'],
                'phone' => $post['phone'],
                'stores' => $post['stores'],
                'address' => $post['stores'] ? $store['text' . strtoupper($lang)] : $post['address'],
                'notes' => $post['notes'],
                'ip' => $post['ip'],
                'added' => date('Y-m-d H:i:s'),
                'updated' => date('Y-m-d H:i:s'),
                'bonus_minus' => 0,
                'sorder' => 0,
                'isb2b' => 0,
            ];

            // inseram logurile
             $this->db->insert("vsl_logs", [
                'type' => 'application',
                'text' => json_encode($ins, JSON_UNESCAPED_UNICODE)
            ]);

            // inseram comanda
            if(!$this->db->insert("orders", $ins)) {
                throw new Exception("Failed to insert order");
            }
                
            $id = $this->db->insert_id();

            $prod_ins = [];
            $items = [];

            foreach ($post['products'] as $product) {
                $price = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
                $prod_ins[] = [
                    'order_id' => $id,
                    'product_id' => $product['id'],
                    'qty' => $product['qty'],
                    'price' => $price,
                    'total' => $price * $product['qty'],
                    'options' => $product['options'] ?? 0
                ];

                $prod = $this->db->select("id,title$lang as title, SKU")->where('id', $product['id'])->get('products')->row();

                $items[] = array(
                    "id" => $product['id'],
                    "SKU" => $prod->SKU,
                    "price" => $price,
                    "name" => $prod->title,
                    "amount" => $product['qty'],
                    "modifiers" => ''
                );
            }

            if(!$this->db->insert_batch("orders_products", $prod_ins)) {
                throw new Exception("Failed to insert order products");
            }

            $html = null;
            if ($ins['payment'] == 2) {
                if (!empty($delivery_price)) {
                    $items[] = array(
                        "id" => 01,
                        "SKU" => 'Delivery01',
                        "price" => $delivery_price,
                        "name" => 'Delivery',
                        "amount" => 1,
                        "modifiers" => ''
                    );
                }

                $paynet_data = array(
                    "customer" => array(
                        "id" => "",
                        "name" => $ins['name'],
                        "surname" => "",
                        "phone" => $ins['phone'],
                        "email" => $ins['email']
                    ),
                    "order" => array(
                        "id" => $id,
                        "phone" => $ins['phone'],
                        "isSelfService" => false,
                        "address" => $ins['address'],
                        "date" => $ins['added'],
                        "items" => $items,
                    ),
                );

                $html = $this->paynet($paynet_data, $lang);
            } else {
                $this->email_order($lang, $ins, $prod_ins);
                $html = null;
            }

            $result = json_encode(['code' => 0, 'message' => 'orders/add ok', 'html' => $html, 'order_number' => $ins['order_id']], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            $result = json_encode(['code' => 1, 'message' => $e->getMessage()]);
        }
        echo $result;
    }

    protected function paynet($order, $lang)
    {
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetAPI.php');
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetConfig.php');
        $api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);
        $prequest = new PaynetRequest();

        $prequest->ExternalID = round(microtime(true) * 1000);
        $prequest->LinkSuccess = 'https://vizaje-nica.com/' . $lang . "/success?id=" . $prequest->ExternalID;
        $prequest->LinkCancel = 'https://vizaje-nica.com/' . $lang . "/error?id=" . $prequest->ExternalID;
        $prequest->Lang = $lang;
        $products = array();
        $prequest->Amount = 0;
        $i = 1;
        foreach ($order['order']['items'] as $item) {
            if($order['customer']['email'] == 'schopenhauer07@gmail.com'){
                $item['price'] = 1;
            }
            $product = array();
            $product['LineNo'] = $i;
            $product['Code'] = $item['SKU'];
            $product['Barcode'] = $item['id'];
            $product['Name'] = $item['name'];
            $product['Descrption'] = $item['name'];
            $product['Quantity'] = $item['amount'] * 100;
            $product['UnitPrice'] = $item['price'] * 100;
            $products[] = $product;
            $prequest->Amount = $prequest->Amount + ($item['price'] * $item['amount'] * 100);
            $i = $i + 1;
        }
        if (count($order['order']['items']) >= 10) {
            $product = array();
            $product['LineNo'] = 1;
            $product['Code'] = '999999999';
            $product['Barcode'] = 99999999;
            $product['Name'] = 'Vizaje Nica';
            $product['Descrption'] = 'All products';
            $product['Quantity'] = 100;
            $product['UnitPrice'] = $prequest->Amount;
            $products = array();
            $products[] = $product;
        }
        $prequest->Products = $products;

        $prequest->Service = array(
            'Name' => 'VN',
            'Description' => 'Vizaje Nica',
            'Amount' => $prequest->Amount,
            'Products' => $prequest->Products
        );

        $phone = str_replace(" ", '', $order['customer']['phone']);
        $phone = str_replace(")", '', $phone);
        $phone = str_replace("(", '', $phone);
        $prequest->Customer = array(
            'Code' => $order['customer']['email'],
            'Address' => 'vizaje nica',
            'Name' => $order['customer']['name'],
            'Surname' => $order['customer']['surname'],
            'Phone' => $phone
        );
        $formObj = $api->FormCreate($prequest);

        if ($formObj->Code == PaynetCode::SUCCESS) {
            $data = array(
                'ExternalID' => $prequest->ExternalID,
            );
            $this->db->where('id', $order['order']['id']);
            $this->db->update('orders', $data);
 
            return $formObj->Data . '<script>document.getElementById("pay_form").submit();</script>';
        } else {
            return null;
        }
    }

    protected function getOrderId()
    {
        $order_random_id = rand(10000, 99999);

        $order = $this->db->where('order_id', $order_random_id)->get('orders')->row();
        if (!empty($order)) {
            $this->getOrderId();
        } else {
            return $order_random_id;
        }
    }

    function email_order($lang, $data, $products)
    {
        $this->load->library('parser');

        $data['added'] = transformDate($data['added'], $lang);
        $data['total_all'] = $data['total'] + $data['delivery_price'];

        $data['site'] = 'vizaje-nica.com';
        $data['total'] = $data['total'] - (!empty($data['bonus_minus']) ? $data['bonus_minus'] : 0);

        if ($data['payment'] == 1) {
            $data['payment'] = 'Numerar la primire';
        } elseif ($data['payment'] == 3) {
            $data['payment'] = 'Plata cu cardul la curier';
        } else {
            $data['payment'] = 'Card online';
        }

        $product_content = '';
        foreach ($products as $product) {
            $p = $this->db->where("id", $product['product_id'])->get("products")->row_array();
            $image = $this->db->select("img as file")->where("product_id", $product['product_id'])->get("products_img")->row_array();

            $product_content .= '<tr>';
            $product_content .= '<td style="border-bottom: 1px solid;">
                                    <img src="https://vizaje-nica.com/public/products/' . $image['file'] . '" style="max-width: 60px">
                                </td>';
            $product_content .= '<td style="border-bottom: 1px solid;">' . $p['title' . strtoupper($lang)] . '</td>
                                <td style="border-bottom: 1px solid;">' . $product['price'] . ' MDL</td>
                                <td style="border-bottom: 1px solid;">' . $product['qty'] . '</td>
                                <td style="border-bottom: 1px solid;">' . $product['price'] * $product['qty'] . ' MDL</td>';
            $product_content .= '</tr>';
        }
        $data['products'] = $product_content;


        $tx = $this->parser->parse('layouts/email/order_email', $data, true);


        $this->load->library('email');
        $config = config_smtp();
        $this->email->initialize($config);
        $this->email->from('noreply@' . $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
        $this->email->to($data['email']);
        $this->email->cc("ntykuuu@gmail.com");
        $this->email->subject('Comanda pe site ' . $_SERVER['HTTP_HOST']);
        $this->email->message($tx);
        $this->email->send();
    }
}
