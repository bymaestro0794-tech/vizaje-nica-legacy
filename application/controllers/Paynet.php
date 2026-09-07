<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Europe/Chisinau');

class Paynet extends CI_Controller
{
    protected $access_token;
    private $API_URL = 'http://api.vizaje-nica.com:90/Vizaje-Nica/hs/WORKAPP/';

    public function __construct()
    {
        parent::__construct();
        //        @session_start();
        header('Content-type: text/html; charset=utf-8');
        $this->load->library('session');
        if (empty($_SESSION['lang'])) $this->_get_prefered_lang();
        $this->lclang = "RO";
        $this->clang = 'ro';
        $this->_define_constants();
    }

    private function _get_prefered_lang()
    {
        $lang = isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2) : '';
        switch ($lang) {
            case "ru":
                $_SESSION['lang'] = 'ru';
                break;
            case "ro":
                $_SESSION['lang'] = 'ro';
                break;
            default:
                $_SESSION['lang'] = 'ru';
                break;
        }
    }

    private function _define_constants()
    {
        $this->load->model('constants_model');
        $constants = $this->constants_model->find();

        $lang = 'RO';
        foreach ($constants as $constant) {
            define($constant->name, nl2br($constant->$lang));
        }
    }


    //    Отправка заказов
    public function index()
    {
        $token = $this->token_get();
        if (!empty($_POST['order_id'])) {
            $order_id = ilabCrypt($_POST['order_id'], false);
            $this->_get_prefered_lang();
            $this->load->model('orders_model');
            $order = $this->orders_model->get_order_by_id($order_id);
            if (!empty($order)) {
                $products = $this->orders_model->get_order_products($order_id, $_SESSION['lang']);
                $orderGUID = $_GET['order'];
                $order_date = date('Y-m-d H:i:s');
                $address = [
                    "city" => "mun. Chișinău",
                    "street" => $order->street,
                    "home" => $order->house,
                    "housing" => "",
                    "apartment" => $order->apartment,
                    "entrance" => $order->entrance,
                    "floor" => $order->floor,
                    "doorphone" => $order->code,
                    "comment" => $order->message
                ];

                /*                ПЕРЕДАЧА ЗАКАЗА iiko*/

                $item = array();
                foreach ($products as $product) {
                    $modifiers = array();
                    if (!empty($product['options'])) {
                        foreach ($product['options'] as $options) {
                            $modifiers[] = array(
                                "id" => $options['GUID'],
                                "name" => $options['title'],
                                "amount" => $options['qty'],
                                "price" => $options['price'],
                            );
                        }
                    }
                    $item[] = array(
                        "id" => $product['id'],
                        "SKU" => $product['GUID'],
                        "price" => $product['price'],
                        "name" => $product['title'],
                        "amount" => $product['qty'],
                        "modifiers" => $modifiers
                    );
                }
                if (!empty($order->delivery_price)) {
                    $item[] = array(
                        "id" => '1',
                        "SKU" => '76067ea3-356f-eb93-9d14-1fa00d082c4e',
                        "price" => $order->delivery_price,
                        "name" => 'Delivery',
                        "amount" => 1,
                        "modifiers" => array()
                    );
                }

                $paynet_data = array(
                    "customer" => array(
                        "id" => "",
                        "name" => stristr($order->name, ' ', true),
                        "surname" => stristr($order->name, ' '),
                        "phone" => $order->phone,
                        "email" => $order->email
                    ),
                    "order" => array(
                        "id" => $order_id,
                        "phone" => $order->phone,
                        "isSelfService" => false,
                        "address" => $address,
                        "date" => $order_date,
                        "items" => $item,
                    ),
                );
                $this->client_server($this->clang, $this->lclang, $paynet_data);
            }
        }
    }

    public function paynet_callback()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $paymentInfo = file_get_contents('php://input');
            $paymentObj = json_decode($paymentInfo);

            $data = array(
                'order_id' => 'certificat callback',
                'message' => 'paymentInfo',
                'response' => $paymentInfo,
                'date' => date("Y-m-d H:i:s")
            );

            $this->db->insert('paynet_logs', $data);

            $order = $this->db->where('ExternalID', $paymentObj->Payment->ExternalId)->get('orders')->row();

            if ($order) {
                $this->_paynet_order($paymentObj);
            } else {
                $this->_paynet_certificate($paymentObj);
            }
        }
    }

    private function _paynet_order($paymentObj)
    {
        $order = $this->db->where('ExternalID', $paymentObj->Payment->ExternalId)->get('orders')->row();

        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetAPI.php');
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetConfig.php');

        $api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);

        $data = array(
            'order_id' => 'paymentInfo',
            'response' => json_encode($paymentObj),
            'date' => date("Y-m-d H:i:s")
        );
        $this->db->insert('paynet_logs', $data);

        if (!$paymentObj) {
            $data = array(
                'order_id' => 'error paymentObj',
                'response' => json_encode($paymentObj),
                'date' => date("Y-m-d H:i:s")
            );
            $this->db->insert('paynet_logs', $data);
            return;
        }
        if ($paymentObj->EventType !== 'PAID') {
            $data = array(
                'order_id' => 'NOT SUCCESS EventType',
                'response' => json_encode($paymentObj),
                'date' => date("Y-m-d H:i:s")
            );
            $this->db->insert('paynet_logs', $data);
            return;
        }
        $checkObj = $api->PaymentGet($paymentObj->Payment->ExternalId);

        if ($checkObj->IsOk()) {
            if ($checkObj->Data['status'] !== 4) {
                $data = array(
                    'order_id' => 'The payment status is not complete. Please wait and try again !!!',
                    'response' => json_encode($paymentObj),
                    'date' => date("Y-m-d H:i:s")
                );
                $this->db->insert('paynet_logs', $data);
                return;
            } else {
                $data = array(
                    'order_id' => 'The payment has confirmed',
                    'response' => json_encode($paymentObj),
                    'date' => date("Y-m-d H:i:s")
                );
                $this->db->insert('paynet_logs', $data);
                $this->db->where('ExternalID', $paymentObj->Payment->ExternalId);
                $this->db->update('orders', array('pay_success' => 1));

                $order =
                    $this->db
                        ->where(
                            'ExternalID',
                            $paymentObj->Payment->ExternalId
                        )
                        ->get('orders')
                        ->row();

                if (
                    !empty($order)
                    && !empty($order->promo_id)
                    && !empty($order->client_id)
                ) {
                    $this->load->model(
                        'promocodes_model'
                    );

                    $alreadyUsed =
                        $this->db
                            ->where(
                                'promocode_id',
                                (int) $order->promo_id
                            )
                            ->where(
                                'client_id',
                                (int) $order->client_id
                            )
                            ->where(
                                'order_id',
                                (int) $order->id
                            )
                            ->count_all_results(
                                'promocode_usages'
                            );

                    if ($alreadyUsed === 0) {
                        $this->promocodes_model
                            ->registerUsage(
                                (int) $order->promo_id,
                                (int) $order->client_id,
                                (int) $order->id
                            );
                    }
                }

                $this->db->where('ExternalID', $paymentObj->Payment->ExternalId);
                $order = $this->db->get('orders')->row_array();

                $this->db->select('*')->where('order_id', $order['id']);
                $products = $this->db->get('orders_products')->result_array();

                $items = array();
                foreach ($products as $product) {
                    $prod = $this->db->select("id,titleRO as title,SKU")->where('id', $product['product_id'])->get('products')->row();
                    $items[] = array(
                        "id" => $prod->id,
                        "SKU" => $prod->SKU,
                        "price" => $product['price'],
                        "name" => $prod->title,
                        "amount" => $product['qty'],
                        "modifiers" => ''
                    );
                }


                //------------- Send success info !
                $this->email_order($order, $items);
            }
            //------------- here you can confirm your transaction !

        }
    }

    private function _paynet_certificate($paymentObj)
    {
        $appDB = $this->load->database('app', TRUE);
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetAPI.php');
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetConfig.php');
        $api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);

        $data = array(
            'order_id' => 'certificat callback',
            'message' => 'o venit callback',
            'response' => json_encode($paymentObj),
            'date' => date("Y-m-d H:i:s")
        );

        $this->db->insert('paynet_logs', $data);

        if (!$paymentObj) {
            $data = array(
                'order_id' => 'certificat callback',
                'message' => 'error paymentObj',
                'response' => json_encode($paymentObj),
                'date' => date("Y-m-d H:i:s")
            );
            $this->db->insert('paynet_logs', $data);
            return;
        }

        if ($paymentObj->EventType !== 'PAID') {
            $data = array(
                'order_id' => 'certificat callback',
                'message' => 'NOT SUCCESS EventType',
                'response' => json_encode($paymentObj),
                'date' => date("Y-m-d H:i:s")
            );
            $this->db->insert('paynet_logs', $data);
            return;
        }

        $checkObj = $api->PaymentGet($paymentObj->Payment->ExternalId);

        $data = array(
            'order_id' => 'certificat callback',
            'message' => 'Check obj',
            'response' => json_encode($checkObj),
            'date' => date("Y-m-d H:i:s")
        );
        $this->db->insert('paynet_logs', $data);

        if ($checkObj->IsOk()) {

            $data = array(
                'order_id' => 'certificat callback',
                'message' => 'The payment has confirmed',
                'response' => json_encode($paymentObj),
                'date' => date("Y-m-d H:i:s")
            );
            $this->db->insert('paynet_logs', $data);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->API_URL . "PostSalesLog/");
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($curl);


            $row = $appDB->where('ExternalID', $paymentObj->Payment->ExternalId)->get('gift_cards_info')->row_array();
            if (!$row) return false;
            
            if (
            	(int) $row['paid_confirmed'] === 1
            	&& (int) $row['sended'] === 1
            	&& !empty($row['barcode'])
            ) {
            	$this->db->insert('paynet_logs', [
            		'order_id' => 'certificat callback',
            		'message' => 'CERTIFICATE ALREADY PROCESSED',
            		'response' => json_encode([
            			'external_id' => $paymentObj->Payment->ExternalId,
            			'barcode' => $row['barcode'],
            		]),
            		'date' => date('Y-m-d H:i:s'),
            	]);
            
            	return true;
            }

            $appDB->where('ExternalID', $paymentObj->Payment->ExternalId);
            $appDB->update('gift_cards_info', array('paid_confirmed' => 1));


            if ($row['send_now'] == 1) {
                $appDB->where('ExternalID', $paymentObj->Payment->ExternalId)->update('gift_cards_info', array('sended' => 1));
                $giftCard = $appDB->where('id', intval($row['gift_cards_id']))->get('gift_cards')->row();


                $phone = str_replace([' ', '(', ')'], "", $row['receiver_phone_number']);

                $this->load->library('FirebaseLib');
                $firebase = new FirebaseLib();

                // $receiverUid = $firebase->getUserByPhoneNumber($phone)->uid;
                // if (!$receiverUid) return false;

                $userData = array();

                $receiverUser = $firebase->getUserByPhoneNumber($phone);

                if ($receiverUser && !empty($receiverUser->uid)) {
                    $receiverUid = $receiverUser->uid;

                    $userData = $appDB
                        ->where('uid', $receiverUid)
                        ->get('users')
                        ->row_array();

                    if (!empty($userData) && !empty($userData['notificationToken'])) {
                        $notifData = [
                            'title' => $userData['Lang'] == 'ro'
                                ? 'Certificat cadou'
                                : 'Подарочный сертификат',

                            'body' => $row['text'],

                            'image' =>
                                'https://app.vizaje-nica.com/public/gift_cards/' .
                                $giftCard->img
                        ];

                        $dataPayload = [
                            'to' => $userData['notificationToken'],
                            'body' => $row['text'],
                            'giftCardId' => $giftCard->id
                        ];

                        $send = $firebase->sendPush(
                            $notifData,
                            $dataPayload,
                            $userData['notificationToken']
                        );

                        $data = array(
                            'order_id' => 'certificat callback',
                            'message' => 'Push notification response',
                            'response' => json_encode($send),
                            'date' => date('Y-m-d H:i:s')
                        );

                        $this->db->insert('paynet_logs', $data);
                    }
                }

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $this->API_URL . 'GetNewDigitalCertificateWNominal/' . $row['price']);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                $res = curl_exec($curl);

                if (!empty($res)) {
                    $barCodeDataDecoded = json_decode($res, true);

                    $this->load->library('barcode_lib');

                    $this->barcode_lib->barcode(
                        'public/barcode/' . $barCodeDataDecoded['BarCode'] . '.png',
                        $barCodeDataDecoded['BarCode'],
                        '100',
                        'horizontal',
                        'code128',
                        true
                    );

                    $giftsBarcodePath =
                        (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://')
                        . $_SERVER['SERVER_NAME']
                        . '/public/barcode/'
                        . $barCodeDataDecoded['BarCode']
                        . '.png';

                    $isRussian = !empty($userData)
                        && isset($userData['Lang'])
                        && $userData['Lang'] === 'ru';

                    if ($row['receiver_email'] != '') {
                        $this->load->library('email');
                        $config = array();
                        $config = config_smtp();
                        $this->email->initialize($config);

                        

                        $message = $this->load->view('layouts/email/gift_email', array(
                            'giftCardImg' => $giftCard->img,
                            'giftCardTitle' => $isRussian
                                ? $giftCard->{'nameRU'}
                                : $giftCard->{'nameRO'},
                            'giftCardTextColor' => $giftCard->text_color,
                            'giftCardShowIcon' => $giftCard->show_icon,
                            'giftCardShowName' => $giftCard->show_name,
                            'giftCardInfoText' => $row['text'],
                            'giftCardInfoNameTo' => $row['name_to'],
                            'giftCardInfoNameFrom' => $row['name_from'],
                            'giftCardInfoPrice' => $row['price'],
                            'giftCardInfoReceiverPhoneNumber' => $row['receiver_phone_number'],
                            'giftCardInfoReceiverEmail' => $row['receiver_email'],
                            'giftCardInfoTicketEmail' => $row['ticket_email'],
                            'giftCardInfoBarcode' => $barCodeDataDecoded['BarCode'],
                            'giftsBarcodePath' => $giftsBarcodePath
                        ), true);

                        $this->email->from('noreply@' . $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
                        $this->email->to($row['receiver_email']);
                        $this->email->subject(
                            $isRussian
                                ? 'Подарочный сертификат'
                                : 'Certificat cadou'
                        );
                        $this->email->message($message);
                        $this->email->send();

                       
                    }
                    if (!empty($row['ticket_email'])) {
                        $this->load->library('email');

                        $config = config_smtp();

                        $this->email->initialize($config);

                        

                        $receiptMessage = $this->load->view(
                            'layouts/email/gift_receipt_email',
                            array(
                                'title' => $isRussian
                                    ? 'Оплата подарочного сертификата подтверждена'
                                    : 'Plata certificatului cadou a fost confirmată',

                                'amountLabel' => $isRussian
                                    ? 'Сумма'
                                    : 'Suma',

                                'paymentLabel' => $isRussian
                                    ? 'Статус'
                                    : 'Status',

                                'paidText' => $isRussian
                                    ? 'Оплачено'
                                    : 'Achitat',

                                'description' => $isRussian
                                    ? 'Сертификат будет отправлен получателю согласно выбранному времени отправки.'
                                    : 'Certificatul va fi trimis destinatarului conform timpului de expediere selectat.',

                                'price' => $row['price'],

                                'externalId' => $paymentObj->Payment->ExternalId,
                            ),
                            true
                        );

                        $this->email->from(
                            'noreply@' . $_SERVER['HTTP_HOST'],
                            'Vizaje-Nica'
                        );

                        $this->email->to($row['ticket_email']);

                        $this->email->subject(
                            $isRussian
                                ? 'Оплата подарочного сертификата — Vizaje-Nica'
                                : 'Plata certificatului cadou — Vizaje-Nica'
                        );

                        $this->email->message($receiptMessage);

                        $receiptEmailSent = $this->email->send();
                        
                        $this->db->insert('paynet_logs', [
                        	'order_id' => 'certificate receipt email',
                        	'message' => $receiptEmailSent
                        		? 'RECEIPT EMAIL SENT'
                        		: 'RECEIPT EMAIL FAILED',
                        
                        	'response' => json_encode([
                        		'email' => $row['ticket_email'],
                        
                        		'external_id' =>
                        			$paymentObj->Payment->ExternalId,
                        
                        		'debug' => $receiptEmailSent
                        			? null
                        			: $this->email->print_debugger(),
                        	], JSON_UNESCAPED_UNICODE),
                        
                        	'date' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    $appDB
                        ->where(
                            'ExternalID',
                            $paymentObj->Payment->ExternalId
                        )
                        ->update(
                            'gift_cards_info',
                            array(
                                'barcode' => $barCodeDataDecoded['BarCode']
                            )
                        );
                }

                if (!empty($row['receiver_phone_number'])) {
                	$message = "Ai primit un certificat cadou! Pentru a o folosi puteți descărca aplicația de pe link-ul:
                android: https://play.google.com/store/apps/details?id=com.nicavizaje.project
                iOS: https://apps.apple.com/md/app/vizaje-nica/id1606198313";
                
                	$this->_sendSMS(
                		$row['receiver_phone_number'],
                		$message
                	);
                }
            }
        }
    }

    private function _sendSMS($number, $message)
    {
        /*
        * Нормализуем номер.
        *
        * Возможные входные варианты:
        * +(373) 61 111 111
        * +37361111111
        * 37361111111
        * 61111111
        */

        $number = preg_replace('/\D+/', '', $number);

        /*
        * Если код страны уже присутствует,
        * удаляем его, потому что ниже добавим +373 сами.
        */
        if (strpos($number, '373') === 0) {
            $number = substr($number, 3);
        }

        $fullNumber = '+373' . $number;

        $url = 'https://messages.inter-mob.com/sms.asp?' . http_build_query([
            'username' => 'vizaje',
            'password' => 'z2ZLaKc8',
            'from' => 'Vizaje-Nica',
            'to' => $fullNumber,
            'text' => $message,
            'coding' => 2,
            'charset' => 'utf-8',
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($curl);

        $curlError = curl_error($curl);

        $httpCode = curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );

        curl_close($curl);

        /*
        * Логируем всё, чтобы больше не гадать:
        * - какой номер реально отправили;
        * - HTTP status;
        * - ответ SMS-провайдера;
        * - cURL error.
        */
        $this->db->insert('paynet_logs', [
            'order_id' => 'certificate sms',
            'message' => $curlError
                ? 'SMS CURL ERROR'
                : 'SMS RESPONSE',
            'response' => json_encode([
                'phone' => $fullNumber,
                'http_code' => $httpCode,
                'provider_response' => $response,
                'curl_error' => $curlError,
            ], JSON_UNESCAPED_UNICODE),
            'date' => date('Y-m-d H:i:s'),
        ]);

        if ($curlError) {
            return false;
        }

        return $response;
    }


    function token_get()
    {
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetAPI.php');
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetConfig.php');
        $api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);
        $token = $api->TokenGet();
        if (!empty($token)) {
            return $token->Data;
        } else {
            return false;
        }
    }

    function client_server($clang, $lclang, $order)
    {
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetAPI.php');
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetConfig.php');
        $api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);
        $prequest = new PaynetRequest();
        //---------- merchant id , for example the order from eshop
        $prequest->ExternalID = round(microtime(true) * 1000);
        $prequest->LinkSuccess = 'https://' . $_SERVER['HTTP_HOST'] . "/" . $lclang . "/order_success?id=" . $prequest->ExternalID;
        $prequest->LinkCancel = 'https://' . $_SERVER['HTTP_HOST'] . "/" . $lclang . "/order_error?id=" . $prequest->ExternalID;
        $prequest->Lang = $clang;
        $products = array();
        $prequest->Amount = 0;
        $i = 1;
        foreach ($order['order']['items'] as $item) {
            $product = array();
            if (!empty($item['modifiers'])) {
                foreach ($item['modifiers'] as $modifiers) {
                    $item['price'] = $item['price'] + ($modifiers['price'] * $modifiers['amount']);
                }
            }
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
            $product['Name'] = 'KH Foods';
            $product['Descrption'] = 'All orders foods';
            $product['Quantity'] = 100;
            $product['UnitPrice'] = $prequest->Amount;
            $products = array();
            $products[] = $product;
        }
        $prequest->Products = $products;

        $prequest->Service = array(
            'Name' => 'corso',
            'Description' => 'kh md',
            'Amount' => $prequest->Amount,
            'Products' => $prequest->Products
        );

        $prequest->Customer = array(
            'Code' => $order['customer']['email'],
            'Address' => 'www.kh.md',
            'Name' => $order['customer']['name'],
            'Surname' => $order['customer']['surname'],
            'Phone' => $order['customer']['phone']
        );
        $formObj = $api->FormCreate($prequest);
        if ($formObj->Code == PaynetCode::SUCCESS) {
            $data = array(
                'ExternalID' => $prequest->ExternalID,
            );
            $this->db->where('id', $order['order']['id']);
            $this->db->update('orders', $data);
            echo $formObj->Data;
        }
    }

    function server_server($clang)
    {
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetAPI.php');
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetConfig.php');
        $api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);
        $prequest = new PaynetRequest();
        $prequest->ExternalID = round(microtime(true) * 1000);
        $prequest->LinkSuccess = "/ok?id=" . $prequest->ExternalID;
        $prequest->LinkCancel = "/cancel?id=" . $prequest->ExternalID;
        $prequest->Lang = 'ru';

        $prequest->Products = array(
            array(
                'LineNo' => '1',
                'Code' => 'code1001',
                'Barcode' => '1001',
                'Name' => 'Ticket mini',
                'Description' => 'Description your product MINI',
                'Quantity' => 200,    // // 200 = 2.00  two
                'UnitPrice' => 2000
            ),
            array(
                'LineNo' => '2',
                'Name' => 'Ticket MAX',
                'Code' => 'code1002',
                'Barcode' => '1002',
                'Description' => 'Description your product MAX',
                'Quantity' => 100,    // 100 = 1.00  one
                'UnitPrice' => 1050
            ),
            array(
                'LineNo' => '3',
                'Name' => 'Ticket MAX 3',
                'Code' => 'code1003',
                'Barcode' => '1003',
                'Description' => 'Description your product MAX',
                'Quantity' => 300,    // 300 = 3.00  three
                'UnitPrice' => 500
            )
        );

        $prequest->Service = array(
            array(
                'Name' => 'Demo eshop',
                'Description' => 'Demo eShop online desc',
                'Amount' => $prequest->Amount,
                'Products' => $prequest->Products
            )
        );

        $prequest->Customer = array(
            'Code' => 'v.bragari@paynet.md',
            'Address' => 'www.paynet.md',
            'Name' => 'Slavan'
        );

        $paymentRegObj = $api->PaymentReg($prequest);
        echo $paymentRegObj->Data;
    }

    function email_order($data, $products)
    {
        $this->load->library('parser');

        $data['added'] = transformDate($data['added'], 'ro');
        $data['total_all'] = $data['total'] + $data['delivery_price'];

        $data['site'] = $_SERVER['HTTP_HOST'];

        if ($data['payment'] == 1) {
            $data['payment'] = 'Numerar la primire';
        } else {
            $data['payment'] = 'Card online';
        }

        $product_content = '';
        foreach ($products as $product) {
            $image = $this->db->select("img as file")->where("product_id", $product['id'])->get("products_img")->row_array();

            $product_content .= '<tr>';
            $product_content .= '<td style="border-bottom: 1px solid;">
                                    <img src="https://vizaje-nica.com/public/products/' . $image['file'] . '" style="max-width: 60px">
                                </td>';
            $product_content .= '<td style="border-bottom: 1px solid;">' . $product['name'] . '</td>
                                <td style="border-bottom: 1px solid;">' . $product['price'] . ' MDL</td>
                                <td style="border-bottom: 1px solid;">' . $product['amount'] . '</td>
                                <td style="border-bottom: 1px solid;">' . $product['price'] * $product['amount'] . ' MDL</td>';
            $product_content .= '</tr>';
        }
        $data['products'] = $product_content;

        $tx = $this->parser->parse('layouts/email/order_email', $data, true);

        //        ПЕРЕВЕРСТАТЬ ПИСЬМО $tx

        //            EMAIL TO SERVER
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

    public function test()
    {
        $order = $this->db->where('id', 3805)->get('orders')->row_array();

        $products = $this->db->select("orders_products.*, products.titleRO as title, products.SKU")
            ->where('order_id', 3805)
            ->join("products", "products.id = orders_products.product_id")
            ->get('orders_products')
            ->result_array();

        $items = [];
        foreach ($products as $key => $product) {
            $items[] = array(
                "id" => $product['product_id'],
                "SKU" => $product['SKU'],
                "price" => $product['price'],
                "name" => $product['title'],
                "amount" => $product['qty'],
                "modifiers" => ''
            );
        }

        $this->email_order($order, $items);
    }
}