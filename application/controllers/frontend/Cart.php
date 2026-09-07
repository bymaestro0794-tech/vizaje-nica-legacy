<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart extends FrontEndController
{
    private $page_id;

    public function __construct()
    {
        parent::__construct();
        $this->page_id = 18;
        $this->load->model('products_model');
        $this->load->model('clients_model');
        $this->load->model('orders_model');
        $this->load->model('promocodes_model');
    }

    private function _init_seo_data($page)
    {
        $this->data['page_title'] = (!empty($page->title)) ? $page->title : "";
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

    public function index()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, $this->page_id);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/cart/index';
        $this->data['page'] = $page;
        $this->data['home_page'] = 1;

        $this->data['class_page'] = '';

        if ($this->cart->total() < (float)FREE_SHIPPING_VALUE) {
            $delivery_price = (float)SHIPPING_PRICE;
        } else {
            $delivery_price = 0;
        }

        $bonus_total = 0;
        if (!empty($this->data['cart_items'])) {
            foreach ($this->data['cart_items'] as $key => $item) {
                $this->data['cart_items'][$key]['products'] = $this->products_model->product_item_cart($this->clang, $item['rowid']);
                if (!empty($item['options'])) {
                    $this->data['cart_items'][$key]['variable'] = $this->products_model->get_product_variable_by_id($this->clang, $item['options'], $item['id']);
                    if (!empty($this->data['client_info'])) {
                        if (empty($_SESSION['isb2b'])) {
                            if (empty($this->data['cart_items'][$key]['variable']->discount_price)) {
                                $bonus_total = $bonus_total + ($this->data['cart_items'][$key]['variable']->price * ($this->data['client_info']->discount / 100) * $this->data['cart_items'][$key]['qty']);
                            }
                        }
                    }
                } else {
                    if (!empty($this->data['client_info'])) {
                        if (empty($_SESSION['isb2b'])) {
                            if (empty($this->data['cart_items'][$key]['products']->discount_price)) {
                                $bonus_total = $bonus_total + ($this->data['cart_items'][$key]['products']->price * ($this->data['client_info']->discount / 100) * $this->data['cart_items'][$key]['qty']);
                            }
                        }
                    }
                }
            }
        }
        $this->data['bonus_total'] = $bonus_total;

        $this->data['delivery_price'] = $delivery_price;

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU,
        );
        $this->_render();
    }

    public function success()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 20);
        if (empty($page)) throw_on_404();

        if (!empty($_GET['id'])) {
            $order = $this->db->where('ExternalID', $_GET['id'])->get('orders')->row();
        }

        if (!empty($_GET['order'])) {
            $order_id = ilabCrypt($_GET['order'], false);
            $order = $this->db->where('id', $order_id)->get('orders')->row();
        }

        if (empty($order)) {
            redirect('/' . $this->lclang);
        }
        $_GET['order'] = ilabCrypt($order->id);

        $this->data['order'] = $order;
        
        /*
        |--------------------------------------------------------------------------
        | GA4 purchase confirmation
        |--------------------------------------------------------------------------
        |
        | Offline:
        | заказ считается созданным сразу.
        |
        | Online Paynet:
        | purchase разрешаем только после подтверждённого callback,
        | когда orders.pay_success = 1.
        |
        */
        
        $isOnlinePayment =
            isset($order->payment)
            && (int) $order->payment === 2;
        
        $isPurchaseConfirmed =
            !$isOnlinePayment
            || (
                isset($order->pay_success)
                && (int) $order->pay_success === 1
            );

        /*
        |--------------------------------------------------------------------------
        | GA4 — purchase
        |--------------------------------------------------------------------------
        |
        | Источник истины:
        |
        | orders
        | orders_products
        |
        | Корзина к этому моменту уже уничтожена,
        | поэтому analytics строим только из snapshot заказа.
        |
        */

        if ($isPurchaseConfirmed) {

            /*
            |--------------------------------------------------------------------------
            | GA4 — purchase
            |--------------------------------------------------------------------------
            */
        
            $orderProducts =
                $this->db
                    ->where(
                        'order_id',
                        (int) $order->id
                    )
                    ->get(
                        'orders_products'
                    )
                    ->result();
        
            $ga4PurchaseItems =
                array();
        
            foreach ($orderProducts as $orderProduct) {
        
                $productId =
                    !empty($orderProduct->product_id)
                        ? (int) $orderProduct->product_id
                        : 0;
        
                $quantity =
                    !empty($orderProduct->qty)
                        ? (int) $orderProduct->qty
                        : 0;
        
                if (
                    $productId <= 0
                    || $quantity <= 0
                ) {
                    continue;
                }
        
                $product =
                    $this->products_model
                        ->get_product_by_id_for_quick_view(
                            $this->clang,
                            $productId
                        );
        
                if (empty($product)) {
                    continue;
                }
        
                /*
                |--------------------------------------------------------------------------
                | Variant
                |--------------------------------------------------------------------------
                */
        
                $variantLabel = '';
        
                $variableId =
                    !empty($orderProduct->options)
                        ? (int) $orderProduct->options
                        : 0;
        
                if ($variableId > 0) {
                    $variable =
                        $this->products_model
                            ->get_product_variable_by_id(
                                $this->clang,
                                $variableId,
                                $productId
                            );
        
                    if (!empty($variable)) {
                        $variantParts =
                            array();
        
                        if (!empty($variable->color)) {
                            $variantParts[] =
                                trim(
                                    (string) $variable->color
                                );
                        }
        
                        if (!empty($variable->VolumeVar)) {
                            $variantParts[] =
                                trim(
                                    (string) $variable->VolumeVar
                                );
                        }
        
                        if (
                            empty($variantParts)
                            && !empty($variable->title)
                        ) {
                            $variantParts[] =
                                trim(
                                    (string) $variable->title
                                );
                        }
        
                        if (
                            empty($variantParts)
                            && !empty($variable->SKU)
                        ) {
                            $variantParts[] =
                                trim(
                                    (string) $variable->SKU
                                );
                        }
        
                        $variantLabel =
                            implode(
                                ' · ',
                                $variantParts
                            );
                    }
                }
        
                /*
                |--------------------------------------------------------------------------
                | Final price
                |--------------------------------------------------------------------------
                */
        
                $rowTotal =
                    isset($orderProduct->total)
                        ? (float) $orderProduct->total
                        : (
                            (float) $orderProduct->price
                            * $quantity
                        );
        
                $unitPrice =
                    $quantity > 0
                        ? round(
                            $rowTotal / $quantity,
                            2
                        )
                        : 0;
        
                $item =
                    array(
                        'item_id' =>
                            (string) $productId,
        
                        'item_name' =>
                            !empty($product->title)
                                ? (string) $product->title
                                : '',
        
                        'item_brand' =>
                            !empty($product->brand_title)
                                ? (string) $product->brand_title
                                : '',
        
                        'item_category' =>
                            !empty($product->cat_title)
                                ? (string) $product->cat_title
                                : '',
        
                        'price' =>
                            $unitPrice,
        
                        'quantity' =>
                            $quantity,
                    );
        
                if ($variantLabel !== '') {
                    $item['item_variant'] =
                        $variantLabel;
                }
        
                $ga4PurchaseItems[] =
                    $item;
            }
        
            /*
            |--------------------------------------------------------------------------
            | Purchase value
            |--------------------------------------------------------------------------
            */
        
            $purchaseValue =
                max(
                    0,
                    round(
                        (float) $order->total
                        - (
                            !empty($order->bonus_minus)
                                ? (float) $order->bonus_minus
                                : 0
                        ),
                        2
                    )
                );
        
            $ga4Purchase =
                array(
                    'transaction_id' =>
                        (string) $order->id,
        
                    'value' =>
                        $purchaseValue,
        
                    'tax' =>
                        0,
        
                    'shipping' =>
                        !empty($order->delivery_price)
                            ? (float) $order->delivery_price
                            : 0,
        
                    'currency' =>
                        'MDL',
        
                    'items' =>
                        $ga4PurchaseItems,
                );
        
            if (!empty($order->promo_code)) {
                $ga4Purchase['coupon'] =
                    (string) $order->promo_code;
            }
        
            $this->data['ga4_purchase'] =
                $ga4Purchase;
        }

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/cart/success';
        $this->data['page'] = $page;
        $this->data['home_page'] = 1;

        $this->data['class_page'] = '';

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO . '?order=' . $_GET['order'],
            'ru' => '' . $page->uriRU . '?order=' . $_GET['order']
        );
        $this->_render();
    }

    public function error()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 21);
        if (empty($page)) throw_on_404();

        $this->breadcrumbs[] = $this->_generate_bc_data($page->title, $page->uri);

        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['inner_view'] = 'pages/cart/error';
        $this->data['page'] = $page;
        $this->data['home_page'] = 1;

        $this->data['class_page'] = '';

        $this->data['lang_urls'] = array(
            'ro' => '' . $page->uriRO,
            'ru' => '' . $page->uriRU
        );
        $this->_render();
    }

public function checkout()
    {
        $page = $this->menu_model->get_page_data_by_id($this->clang, 19);
        if (empty($page)) throw_on_404();

        $expressDelivery =
            $this->db
                ->select('isShown')
                ->where('id', 39)
                ->get('menu')
                ->row();

        $expressDeliveryEnabled =
            !empty($expressDelivery)
            && (int) $expressDelivery->isShown === 1;

        if (empty($this->data['cart_items'])) {
            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][3]->uri);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, true);
            }

            $data = array();

            $data['total_items_cart'] = $this->cart->total_items();
            if (empty($post['name']) && empty($post['email']) && empty($data['total_items_cart'])) {
                redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][17]->uri . '?checkout=error');
            }
            if (empty($post['pay'])) $post['pay'] = 1;

            /*
            |--------------------------------------------------------------------------
            | Client
            |--------------------------------------------------------------------------
            */

            if (!empty($this->data['client_info']->id)) {
                $client_id =
                    (int) $this->data['client_info']->id;
            } else {
                $client_id = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | Phone
            |--------------------------------------------------------------------------
            */

            $phone =
                !empty($post['phone'])
                    ? trim((string) $post['phone'])
                    : '';

            /*
            |--------------------------------------------------------------------------
            | Normalize submitted phone
            |--------------------------------------------------------------------------
            */

            $digits =
                preg_replace(
                    '/\D/',
                    '',
                    $phone
                );

            if (strlen($digits) === 8) {
                $phone =
                    '+373' . $digits;

            } elseif (
                strlen($digits) === 11
                && strpos($digits, '373') === 0
            ) {
                $phone =
                    '+' . $digits;

            } else {
                $phone = '';
            }

            /*
            |--------------------------------------------------------------------------
            | Authorized client fallback
            |--------------------------------------------------------------------------
            |
            | Поле может содержать только маску +(373),
            | поэтому fallback делаем ПОСЛЕ validation.
            |
            */

            if (
                $phone === ''
                && !empty($this->data['client_info']->phone)
            ) {
                $clientPhoneDigits =
                    preg_replace(
                        '/\D/',
                        '',
                        (string) $this->data['client_info']->phone
                    );

                if (strlen($clientPhoneDigits) === 8) {
                    $phone =
                        '+373' . $clientPhoneDigits;

                } elseif (
                    strlen($clientPhoneDigits) === 11
                    && strpos($clientPhoneDigits, '373') === 0
                ) {
                    $phone =
                        '+' . $clientPhoneDigits;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Phone required
            |--------------------------------------------------------------------------
            */

            if ($phone === '') {
                $_SESSION['checkout_error'] =
                    $this->lclang === 'ro'
                        ? 'Introduceți un număr de telefon valid.'
                        : 'Введите корректный номер телефона.';

                redirect(
                    '/'
                    . $this->lclang
                    . '/'
                    . $page->uri
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Authoritative cart calculation
            |--------------------------------------------------------------------------
            |
            | Никогда не доверяем сумме, которая была показана в checkout ранее.
            |
            | Между открытием checkout и нажатием submit могли измениться:
            |
            | - promo status;
            | - срок действия promo;
            | - usage limit;
            | - остатки / цены;
            | - товары в корзине;
            | - eligibility.
            |
            */

            $this->load->library(
                'CartCalculator'
            );

            $orderState =
                $this->cartcalculator
                    ->calculate(
                        $this->lclang
                    );

            if (
                empty($orderState['rows'])
                || empty($orderState['cart']['total_items'])
            ) {
                redirect(
                    '/'
                    . $this->lclang
                    . '/'
                    . $this->data['menu']['all'][3]->uri
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Promo revalidation
            |--------------------------------------------------------------------------
            |
            | Если пользователь пришёл в checkout с активным promo,
            | но к моменту создания заказа promo перестал быть валидным,
            | НЕ создаём заказ молча по большей цене.
            |
            */

            $sessionPromoCode =
                !empty($_SESSION['cart_promo']['code'])
                    ? strtoupper(
                        trim(
                            (string) $_SESSION['cart_promo']['code']
                        )
                    )
                    : '';

            if (
                $sessionPromoCode !== ''
                && empty($orderState['promo']['applied'])
            ) {
                $_SESSION['checkout_promo_error'] =
                    !empty($orderState['promo']['reason'])
                        ? $orderState['promo']['reason']
                        : 'PROMO_NOT_APPLICABLE';

                redirect(
                    '/'
                    . $this->lclang
                    . '/'
                    . $page->uri
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Promo snapshot
            |--------------------------------------------------------------------------
            */

            $promoId = null;
            $promoCode = null;
            $promoDiscount = 0;

            if (!empty($orderState['promo']['applied'])) {
                $promoId =
                    !empty($orderState['promo']['id'])
                        ? (int) $orderState['promo']['id']
                        : null;

                $promoCode =
                    !empty($orderState['promo']['code'])
                        ? (string) $orderState['promo']['code']
                        : null;

                $promoDiscount =
                    round(
                        (float) $orderState['cart']['promo_discount_total'],
                        2
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Authoritative order subtotal
            |--------------------------------------------------------------------------
            |
            | Это товары:
            |
            | original price
            | - product discount
            | - promo discount
            |
            | Доставка добавляется отдельно.
            |
            */

            $total =
                round(
                    (float) $orderState['cart']['cart_total'],
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | Legacy voucher
            |--------------------------------------------------------------------------
            |
            | Старый voucher пока сохраняем для совместимости.
            | Но базовую сумму всегда сначала получает CartCalculator.
            |
            */

            $voucher = '';

            if (!empty($_SESSION['voucher'])) {
                $voucher =
                    trim(
                        (string) $_SESSION['voucher']
                    );

                $voucherOn =
                    $this->db
                        ->where(
                            'voucher',
                            $voucher
                        )
                        ->get(
                            'subscriptions'
                        )
                        ->row();

                if (!empty($voucherOn)) {
                    $voucherOrder =
                        $this->db
                            ->select('id')
                            ->where(
                                'voucher',
                                $voucher
                            )
                            ->get(
                                'orders'
                            )
                            ->row();

                    if (
                        empty($voucherOrder)
                        && $total > (float) VAUCHER_SUM_ORDER
                    ) {
                        $total =
                            max(
                                0,
                                $total
                                - (float) VAUCHER_SUM
                            );
                    }
                }
            } else {
                $_SESSION['voucher'] = '';
            }

            /*
            |--------------------------------------------------------------------------
            | Delivery
            |--------------------------------------------------------------------------
            */

            if ((int) $post['delivery'] === 3) {
                /*
                | Срочная доставка.
                */
                $delivery_price = 100;
            } elseif ((int) $post['delivery'] === 2) {
                /*
                | Курьерская доставка.
                |
                | Threshold проверяем уже ПОСЛЕ promo.
                */
                $delivery_price =
                    (float) $orderState['cart']['cart_total']
                    < (float) FREE_SHIPPING_VALUE
                        ? (float) SHIPPING_PRICE
                        : 0;
            } else {
                /*
                | Самовывоз.
                */
                $delivery_price = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            if ((int) $post['delivery'] === 1) {
                /*
                | Pickup.
                |
                | Адрес доставки отсутствует.
                | Магазин хранится отдельно в orders.stores.
                */
                $post['address'] = '';
            } else {
                $city =
                    !empty($post['city'])
                        ? trim((string) $post['city'])
                        : '';

                $street =
                    !empty($post['address'])
                        ? trim((string) $post['address'])
                        : '';

                $house =
                    !empty($post['house'])
                        ? trim((string) $post['house'])
                        : '';

                $porch =
                    !empty($post['porch'])
                        ? trim((string) $post['porch'])
                        : '';

                $apartment =
                    !empty($post['apartament'])
                        ? trim((string) $post['apartament'])
                        : '';

                $addressParts = array();

                if ($city !== '') {
                    $addressParts[] =
                        $city;
                }

                if ($street !== '') {
                    $addressParts[] =
                        'st. ' . $street;
                }

                if ($house !== '') {
                    $addressParts[] =
                        'h. ' . $house;
                }

                if ($porch !== '') {
                    $addressParts[] =
                        'p. ' . $porch;
                }

                if ($apartment !== '') {
                    $addressParts[] =
                        'ap. ' . $apartment;
                }

                $post['address'] =
                    implode(
                        ', ',
                        $addressParts
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Bonuses
            |--------------------------------------------------------------------------
            |
            | Правила:
            |
            | - только B2C;
            | - только авторизованный клиент;
            | - по умолчанию бонусы начисляются;
            | - при списании клиент сам выбирает сумму;
            | - списывать можно только с bonus-eligible товаров;
            | - после списания на eligible товарах должен остаться минимум 1 MDL;
            | - backend никогда не доверяет сумме из frontend.
            |
            */

            $bonus_plus = 0;
            $bonus_minus = 0;

            $isBonusClient =
                !empty($this->data['client_info'])
                && empty($_SESSION['isb2b']);

            if ($isBonusClient) {
                /*
                |--------------------------------------------------------------------------
                | Default mode: accumulate
                |--------------------------------------------------------------------------
                |
                | Даже если frontend по какой-то причине не передал поле bonus,
                | авторизованный B2C-клиент не должен потерять начисление.
                |
                */

                $bonus_plus =
                    !empty($orderState['cart']['bonus_total'])
                        ? (float) $orderState['cart']['bonus_total']
                        : 0;

                /*
                |--------------------------------------------------------------------------
                | Selected mode
                |--------------------------------------------------------------------------
                */

                $bonusMode = 1;

                if (!empty($post['bonus'])) {
                    $bonusParts =
                        explode(
                            '-',
                            (string) $post['bonus']
                        );

                    if (!empty($bonusParts[0])) {
                        $bonusMode =
                            (int) $bonusParts[0];
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Write off
                |--------------------------------------------------------------------------
                */

                if ($bonusMode === 2) {
                    /*
                    |--------------------------------------------------------------------------
                    | Requested amount
                    |--------------------------------------------------------------------------
                    |
                    | Основное поле frontend:
                    |
                    | bonus_amount=150
                    |
                    | Дополнительно поддерживаем старый формат:
                    |
                    | bonus=2-150
                    |
                    | чтобы legacy checkout не сломался во время перехода.
                    |
                    */

                    $requestedBonus = 0;

                    if (isset($post['bonus_amount'])) {
                        $requestedBonus =
                            (float) str_replace(
                                ',',
                                '.',
                                (string) $post['bonus_amount']
                            );
                    } elseif (
                        !empty($bonusParts[1])
                    ) {
                        $requestedBonus =
                            (float) str_replace(
                                ',',
                                '.',
                                (string) $bonusParts[1]
                            );
                    }

                    $requestedBonus =
                        floor(
                            max(
                                0,
                                $requestedBonus
                            )
                        );

                   /*
                    |--------------------------------------------------------------------------
                    | Server-authoritative maximum
                    |--------------------------------------------------------------------------
                    */

                    $calculatorMaxWriteOff =
                        !empty(
                            $orderState['cart']['bonus_max_writeoff']
                        )
                            ? (float) $orderState['cart']['bonus_max_writeoff']
                            : 0;

                    /*
                    | Дополнительная защита после legacy voucher.
                    |
                    | Итог товаров никогда не должен уходить ниже 1 MDL.
                    */
                    $orderMaxWriteOff =
                        max(
                            0,
                            $total - 1
                        );

                    $maxWriteOff =
                        min(
                            $calculatorMaxWriteOff,
                            $orderMaxWriteOff
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Final write-off
                    |--------------------------------------------------------------------------
                    */

                    $bonus_minus =
                        min(
                            $requestedBonus,
                            $maxWriteOff
                        );

                    $bonus_minus =
                        floor(
                            max(
                                0,
                                $bonus_minus
                            )
                        );

                    /*
                    | При списании новые бонусы
                    | за этот заказ не начисляем.
                    */
                    $bonus_plus = 0;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Pickup store validation
            |--------------------------------------------------------------------------
            */

            if (
                (int) $post['delivery'] === 1
                && empty($post['stores'])
            ) {
                $_SESSION['checkout_error'] =
                    $this->lclang === 'ro'
                        ? 'Selectați magazinul pentru ridicare.'
                        : 'Выберите магазин для самовывоза.';

                redirect(
                    '/'
                    . $this->lclang
                    . '/'
                    . $page->uri
                );

                return;
            }

            $data = array(
                'status' =>
                    'new',

                'order_id' =>
                    $this->getOrderId(),

                /*
                |--------------------------------------------------------------------------
                | Financial snapshot
                |--------------------------------------------------------------------------
                */

                'total' =>
                    $total,

                'delivery_price' =>
                    $delivery_price,

                /*
                |--------------------------------------------------------------------------
                | Promo snapshot
                |--------------------------------------------------------------------------
                */

                'promo_id' =>
                    $promoId,

                'promo_code' =>
                    $promoCode,

                'promo_discount' =>
                    $promoDiscount,

                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */

                'client_id' =>
                    $client_id,

                'name' =>
                    $post['name'],

                'surname' =>
                    $post['surname'],

                'email' =>
                    $post['email'],

                'phone' =>
                    $phone,

                'message' =>
                    !empty($post['message'])
                        ? trim((string) $post['message'])
                        : '',

                /*
                |--------------------------------------------------------------------------
                | Order
                |--------------------------------------------------------------------------
                */

                'payment' =>
                    $post['pay'],

                'stores' =>
                    !empty($post['stores'])
                        ? $post['stores']
                        : '',

                'delivery' =>
                    !empty($post['delivery'])
                        ? $post['delivery']
                        : 1,

                'bonus_plus' =>
                    $bonus_plus,

                'bonus_minus' =>
                    $bonus_minus,

                'address' =>
                    $post['address'],

                'ip' =>
                    $_SERVER['REMOTE_ADDR'],

                'voucher' =>
                    $voucher,

                'added' =>
                    date('Y-m-d H:i:s'),

                'isb2b' =>
                    !empty($_SESSION['isb2b'])
                        ? $_SESSION['isb2b']
                        : 0,
            );
            $this->db->insert('orders', $data);
            $order_id = $this->db->insert_id();

            unset($_SESSION['voucher']);

            if (!empty($order_id)) {
                /*
                |--------------------------------------------------------------------------
                | Order products snapshot
                |--------------------------------------------------------------------------
                |
                | Используем только строки, которые уже посчитал CartCalculator.
                |
                | price          = цена единицы после обычной скидки, до promo
                | promo_discount = promo-скидка всей строки
                | total          = финальная стоимость строки после promo
                |
                */

                $items = array();

                foreach ($orderState['rows'] as $row) {
                    $productId =
                        (int) $row['product_id'];

                    $quantity =
                        (int) $row['quantity'];

                    $unitPrice =
                        round(
                            (float) $row['unit_price'],
                            2
                        );

                    $promoRowDiscount =
                        round(
                            (float) $row['promo_discount'],
                            2
                        );

                    $finalRowTotal =
                        round(
                            (float) $row['final_total'],
                            2
                        );

                    $variableId =
                        !empty($row['variable_id'])
                            ? (int) $row['variable_id']
                            : 0;

                    /*
                    |--------------------------------------------------------------------------
                    | orders_products
                    |--------------------------------------------------------------------------
                    */

                    $product = array(
                        'order_id' =>
                            $order_id,

                        'product_id' =>
                            $productId,

                        'qty' =>
                            $quantity,

                        /*
                        | Unit price after ordinary product discount.
                        */
                        'price' =>
                            $unitPrice,

                        /*
                        | Variant ID, если товар вариативный.
                        */
                        'options' =>
                            $variableId,

                        /*
                        | Promo discount allocated to this whole row.
                        */
                        'promo_discount' =>
                            $promoRowDiscount,

                        /*
                        | Final row total after ordinary + promo discounts.
                        */
                        'total' =>
                            $finalRowTotal,
                    );

                    if (
                        !$this->db->insert(
                            'orders_products',
                            $product
                        )
                    ) {
                        log_message(
                            'error',
                            'Order product insert failed. Order ID: '
                            . $order_id
                            . ', product ID: '
                            . $productId
                        );

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Product metadata
                    |--------------------------------------------------------------------------
                    */

                    $prod =
                        $this->db
                            ->select(
                                "id,title$this->clang as title,SKU"
                            )
                            ->where(
                                'id',
                                $productId
                            )
                            ->get(
                                'products'
                            )
                            ->row();

                    if (empty($prod)) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Runtime order items
                    |--------------------------------------------------------------------------
                    |
                    | Пока сохраняем отдельно всю финансовую информацию.
                    | На следующем шаге именно из неё правильно соберём Paynet.
                    |
                    */

                    $items[] = array(
                        'id' =>
                            $productId,

                        'SKU' =>
                            $prod->SKU,

                        'price' =>
                            $unitPrice,

                        'name' =>
                            $prod->title,

                        'amount' =>
                            $quantity,

                        'promo_discount' =>
                            $promoRowDiscount,

                        'bonus_eligible' =>
                            !empty($row['bonus_eligible']),

                        'total' =>
                            $finalRowTotal,

                        'modifiers' =>
                            '',
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Promo audit — order created
                |--------------------------------------------------------------------------
                |
                | Фиксируем состояние промокода именно в момент,
                | когда заказ уже создан в БД.
                |
                | Это не promocode_usages:
                | usage отвечает за бизнес-факт использования,
                | а event — за историю действий пользователя.
                |
                */

                if (
                    !empty($promoId)
                    && !empty($promoCode)
                    && $client_id > 0
                ) {
                    $this->promocodes_model->logEvent(
                        'ORDER_CREATED',
                        array(
                            'promocode_id' =>
                                (int) $promoId,

                            'client_id' =>
                                (int) $client_id,

                            'order_id' =>
                                (int) $order_id,

                            'code' =>
                                (string) $promoCode,

                            /*
                            | Стоимость товаров после обычных
                            | скидок, но до promo.
                            */
                            'cart_subtotal' =>
                                round(
                                    (float) $total
                                    + (float) $promoDiscount,
                                    2
                                ),

                            'discount_amount' =>
                                round(
                                    (float) $promoDiscount,
                                    2
                                ),

                            /*
                            | Реальный итог заказа:
                            |
                            | товары после promo
                            | + доставка
                            | - списанные бонусы
                            */
                            'final_total' =>
                                round(
                                    (float) $total
                                    + (float) $delivery_price
                                    - (float) $bonus_minus,
                                    2
                                ),

                            'ip_address' =>
                                $this->input->ip_address(),

                            'user_agent' =>
                                $this->input->user_agent(),
                        )
                    );
                }

               /*
                |--------------------------------------------------------------------------
                | Register promo usage
                |--------------------------------------------------------------------------
                |
                | Offline payment:
                | promo считается использованным сразу после создания заказа.
                |
                | Online payment:
                | promo usage регистрируем только после успешного Paynet callback.
                |
                */

                if (
                    !empty($promoId)
                    && $client_id > 0
                    && (int) $post['pay'] !== 2
                ) {
                    $usageSaved =
                        $this->promocodes_model
                            ->registerUsage(
                                $promoId,
                                $client_id,
                                $order_id
                            );

                    if (!$usageSaved) {
                        log_message(
                            'error',
                            'Promo usage registration failed. '
                            . 'Promo ID: '
                            . $promoId
                            . ', client ID: '
                            . $client_id
                            . ', order ID: '
                            . $order_id
                        );
                    }
                }


                unset($_SESSION['cart_promo']);


                if ((int) $post['pay'] === 2) {

                    /*
                    |--------------------------------------------------------------------------
                    | Paynet product totals
                    |--------------------------------------------------------------------------
                    |
                    | $items остаётся исходным order snapshot:
                    |
                    | product discount
                    | + promo
                    |
                    | Для Paynet создаём отдельную копию и уже на неё
                    | распределяем bonus_minus.
                    |
                    */

                    $paynetItems =
                        $this->applyBonusToPaynetItems(
                            $items,
                            $bonus_minus
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Delivery
                    |--------------------------------------------------------------------------
                    */

                    if (!empty($delivery_price)) {
                        $paynetItems[] =
                            array(
                                'id' =>
                                    1,

                                'SKU' =>
                                    'Delivery01',

                                'price' =>
                                    (float) $delivery_price,

                                'name' =>
                                    'Delivery',

                                'amount' =>
                                    1,

                                'total' =>
                                    (float) $delivery_price,

                                'bonus_eligible' =>
                                    false,

                                'modifiers' =>
                                    '',
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Paynet data
                    |--------------------------------------------------------------------------
                    */

                    $paynet_data =
                        array(
                            'customer' =>
                                array(
                                    'id' =>
                                        '',

                                    'name' =>
                                        $data['name'],

                                    'surname' =>
                                        $data['surname'],

                                    'phone' =>
                                        $data['phone'],

                                    'email' =>
                                        $data['email'],
                                ),

                            'order' =>
                                array(
                                    'id' =>
                                        $order_id,

                                    'phone' =>
                                        $data['phone'],

                                    'isSelfService' =>
                                        false,

                                    'address' =>
                                        $data['address'],

                                    'date' =>
                                        $data['added'],

                                    'items' =>
                                        $paynetItems,
                                ),
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Diagnostic total
                    |--------------------------------------------------------------------------
                    */

                    $paynetExpectedTotal =
                        0;

                    foreach (
                        $paynetItems as $paynetItem
                    ) {
                        if (isset($paynetItem['total'])) {
                            $paynetExpectedTotal +=
                                (float) $paynetItem['total'];
                        } else {
                            $paynetExpectedTotal +=
                                (float) $paynetItem['price']
                                * (int) $paynetItem['amount'];
                        }
                    }

                    log_message(
                        'error',
                        'CHECKOUT PAYNET EXPECTED: '
                        . number_format(
                            $paynetExpectedTotal,
                            2,
                            '.',
                            ''
                        )
                        . '; ORDER FINAL: '
                        . number_format(
                            $total
                            + $delivery_price
                            - $bonus_minus,
                            2,
                            '.',
                            ''
                        )
                        . '; BONUS_MINUS: '
                        . number_format(
                            $bonus_minus,
                            2,
                            '.',
                            ''
                        )
                    );

                    $this->cart->destroy();

                    $this->client_server(
                        $this->clang,
                        $this->lclang,
                        $paynet_data
                    );

                } else {
                    $this->email_order(
                        $data,
                        $items
                    );

                    $this->cart->destroy();
                }

            }

            redirect('/' . $this->lclang . '/' . $this->data['menu']['all'][20]->uri . '?order=' . ilabCrypt($order_id));
        }

        /*
        |--------------------------------------------------------------------------
        | Unified checkout calculation
        |--------------------------------------------------------------------------
        */

        $this->load->library(
            'CartCalculator'
        );

        $checkoutState =
            $this->cartcalculator
                ->calculate(
                    $this->lclang
                );

        /*
        |--------------------------------------------------------------------------
        | Cart totals
        |--------------------------------------------------------------------------
        */

        $this->data['checkout_state'] =
            $checkoutState;

        $this->data['total_items_cart'] =
            (int) $checkoutState['cart']['total_items'];

        $this->data['products_total'] =
            (float) $checkoutState['cart']['products_total'];

        $this->data['product_discount_total'] =
            (float) $checkoutState['cart']['product_discount_total'];

        $this->data['promo_discount_total'] =
            (float) $checkoutState['cart']['promo_discount_total'];

        $this->data['discount_total'] =
            (float) $checkoutState['cart']['discount_total'];

        $this->data['total_price_cart'] =
            (float) $checkoutState['cart']['cart_total'];

        $this->data['delivery_price'] =
            (float) $checkoutState['cart']['delivery_price'];

        $this->data['checkout_total'] =
            (float) $checkoutState['cart']['total'];

        $this->data['bonus_total'] =
            (float) $checkoutState['cart']['bonus_total'];

        $this->data['bonus_eligible_total'] =
            !empty(
                $checkoutState['cart']['bonus_eligible_total']
            )
                ? (float) $checkoutState['cart']['bonus_eligible_total']
                : 0;

        $this->data['bonus_balance'] =
            !empty(
                $checkoutState['cart']['bonus_balance']
            )
                ? (float) $checkoutState['cart']['bonus_balance']
                : 0;

        $this->data['bonus_max_writeoff'] =
            !empty(
                $checkoutState['cart']['bonus_max_writeoff']
            )
                ? (float) $checkoutState['cart']['bonus_max_writeoff']
                : 0;

        /*
        |--------------------------------------------------------------------------
        | Promo / shipping
        |--------------------------------------------------------------------------
        */

        $this->data['promo'] =
            $checkoutState['promo'];

        $this->data['shipping'] =
            $checkoutState['shipping'];

        /*
        |--------------------------------------------------------------------------
        | GA4 — begin_checkout
        |--------------------------------------------------------------------------
        |
        | Формируем analytics snapshot из того же authoritative state,
        | который используется самим checkout.
        |
        */

        $ga4CheckoutItems =
            array();

        if (!empty($checkoutState['rows'])) {
            foreach ($checkoutState['rows'] as $row) {
                $productId =
                    !empty($row['product_id'])
                        ? (int) $row['product_id']
                        : 0;

                $quantity =
                    !empty($row['quantity'])
                        ? (int) $row['quantity']
                        : 1;

                if (
                    $productId <= 0
                    || $quantity <= 0
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Product metadata
                |--------------------------------------------------------------------------
                */

                $product =
                    $this->products_model
                        ->product_item_cart(
                            $this->clang,
                            $row['row_id']
                        );

                if (empty($product)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Variant
                |--------------------------------------------------------------------------
                */

                $variantLabel =
                    '';

                $variableId =
                    !empty($row['variable_id'])
                        ? (int) $row['variable_id']
                        : 0;

                if ($variableId > 0) {
                    $variable =
                        $this->products_model
                            ->get_product_variable_by_id(
                                $this->clang,
                                $variableId,
                                $productId
                            );

                    if (!empty($variable)) {
                        $variantParts =
                            array();

                        if (!empty($variable->color)) {
                            $variantParts[] =
                                trim(
                                    (string) $variable->color
                                );
                        }

                        if (!empty($variable->VolumeVar)) {
                            $variantParts[] =
                                trim(
                                    (string) $variable->VolumeVar
                                );
                        }

                        if (
                            empty($variantParts)
                            && !empty($variable->title)
                        ) {
                            $variantParts[] =
                                trim(
                                    (string) $variable->title
                                );
                        }

                        $variantLabel =
                            implode(
                                ' · ',
                                $variantParts
                            );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Final unit price
                |--------------------------------------------------------------------------
                |
                | final_total уже содержит:
                | ordinary discount + promo allocation.
                |
                */

                $finalRowTotal =
                    isset($row['final_total'])
                        ? (float) $row['final_total']
                        : 0;

                $unitFinalPrice =
                    $quantity > 0
                        ? round(
                            $finalRowTotal
                            / $quantity,
                            2
                        )
                        : 0;

                $item =
                    array(
                        'item_id' =>
                            (string) $productId,

                        'item_name' =>
                            !empty($product->title)
                                ? (string) $product->title
                                : '',

                        'item_brand' =>
                            !empty($product->brand_title)
                                ? (string) $product->brand_title
                                : '',

                        'item_category' =>
                            !empty($product->cat_title)
                                ? (string) $product->cat_title
                                : '',

                        'price' =>
                            $unitFinalPrice,

                        'quantity' =>
                            $quantity,
                    );

                if ($variantLabel !== '') {
                    $item['item_variant'] =
                        $variantLabel;
                }

                $ga4CheckoutItems[] =
                    $item;
            }
        }

        $ga4Checkout =
            array(
                'currency' =>
                    'MDL',

                'value' =>
                    round(
                        (float) $checkoutState['cart']['cart_total'],
                        2
                    ),

                'items' =>
                    $ga4CheckoutItems,
            );

        /*
        |--------------------------------------------------------------------------
        | Coupon
        |--------------------------------------------------------------------------
        */

        if (
            !empty($checkoutState['promo']['applied'])
            && !empty($checkoutState['promo']['code'])
        ) {
            $ga4Checkout['coupon'] =
                (string) $checkoutState['promo']['code'];
        }

        $this->data['ga4_checkout'] =
            $ga4Checkout;

        $this->data['page'] = $page;

        $stores = $this->stores_model->get_stores_delivery($this->clang);
        foreach ($stores as $key => $store) {
            $store->img = $this->db->where('stores_id', $store->id)->get('stores_img')->result();
            $stores[$key] = $store;
        }
        $this->data['stores'] = $stores;


        $this->data['inner_view'] = 'pages/cart/checkout';

        $this->data['body_class'] = '_order-page';


        $this->loadOGImgData($page);
        $this->_init_seo_data($page);

        $this->data['checkout_on'] = 'on';
        $this->data['lang_urls'] = array(
            'ru' => '' . $page->uriRU,
            'ro' => '' . $page->uriRO,
        );

        $this->data['express_delivery_enabled'] =
	        $expressDeliveryEnabled;

        $this->_render();

    }


    public function delete_all()
    {
        $this->cart->destroy();
        redirect('/' . $this->lclang);
        die();
    }

    protected function getOrderId()
    {
        do {
            $order_random_id = rand(10000, 99999);
    
            $exists = $this->db
                ->where('order_id', $order_random_id)
                ->count_all_results('orders') > 0;
    
        } while ($exists);
    
        return $order_random_id;
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
    private function applyBonusToPaynetItems(
        array $items,
        $bonusMinus
    ) {
        /*
        |--------------------------------------------------------------------------
        | Normalize bonus
        |--------------------------------------------------------------------------
        |
        | Работаем в копейках/банях, чтобы не получить float drift.
        |
        */

        $bonusCents =
            (int) round(
                max(
                    0,
                    (float) $bonusMinus
                ) * 100
            );

        if (
            $bonusCents <= 0
            || empty($items)
        ) {
            return $items;
        }

        /*
        |--------------------------------------------------------------------------
        | Eligible rows
        |--------------------------------------------------------------------------
        */

        $eligibleIndexes =
            array();

        $eligibleTotalCents =
            0;

        foreach (
            $items as $index => $item
        ) {
            if (
                empty($item['bonus_eligible'])
                || !isset($item['total'])
            ) {
                continue;
            }

            $rowCents =
                (int) round(
                    max(
                        0,
                        (float) $item['total']
                    ) * 100
                );

            if ($rowCents <= 0) {
                continue;
            }

            $eligibleIndexes[] =
                $index;

            $eligibleTotalCents +=
                $rowCents;
        }

        if (
            empty($eligibleIndexes)
            || $eligibleTotalCents <= 0
        ) {
            return $items;
        }

        /*
        |--------------------------------------------------------------------------
        | Final server protection
        |--------------------------------------------------------------------------
        |
        | Даже если выше уже был clamp, здесь ещё раз гарантируем:
        |
        | eligible total после бонусов >= 1 MDL.
        |
        */

        $maxBonusCents =
            max(
                0,
                $eligibleTotalCents - 100
            );

        $bonusCents =
            min(
                $bonusCents,
                $maxBonusCents
            );

        if ($bonusCents <= 0) {
            return $items;
        }

        /*
        |--------------------------------------------------------------------------
        | Allocate
        |--------------------------------------------------------------------------
        |
        | Распределяем бонус последовательно.
        |
        | Каждой строке оставляем хотя бы 0.01 MDL,
        | чтобы не отправлять Paynet товар с нулевой стоимостью.
        |
        | Бизнес-правило "оставить минимум 1 MDL"
        | всё равно соблюдается на общем eligible subtotal,
        | потому что bonusMinus уже ограничен выше.
        |
        */

        $remainingBonusCents =
            $bonusCents;

        foreach (
            $eligibleIndexes as $index
        ) {
            if ($remainingBonusCents <= 0) {
                break;
            }

            $rowCents =
                (int) round(
                    max(
                        0,
                        (float) $items[$index]['total']
                    ) * 100
                );

            /*
            | Не делаем Paynet line полностью нулевой.
            */
            $rowCapacity =
                max(
                    0,
                    $rowCents - 1
                );

            if ($rowCapacity <= 0) {
                continue;
            }

            $rowBonusCents =
                min(
                    $remainingBonusCents,
                    $rowCapacity
                );

            $rowFinalCents =
                $rowCents
                - $rowBonusCents;

            /*
            |--------------------------------------------------------------------------
            | Runtime Paynet data only
            |--------------------------------------------------------------------------
            */

            $items[$index]['bonus_discount'] =
                $rowBonusCents / 100;

            $items[$index]['total'] =
                $rowFinalCents / 100;

            $remainingBonusCents -=
                $rowBonusCents;
        }

        /*
        |--------------------------------------------------------------------------
        | Safety log
        |--------------------------------------------------------------------------
        |
        | Сюда в нормальном сценарии попасть не должны.
        |
        */

        if ($remainingBonusCents > 0) {
            log_message(
                'error',
                'PAYNET BONUS ALLOCATION INCOMPLETE: '
                . 'requested='
                . number_format(
                    $bonusCents / 100,
                    2,
                    '.',
                    ''
                )
                . '; remaining='
                . number_format(
                    $remainingBonusCents / 100,
                    2,
                    '.',
                    ''
                )
            );
        }

        return $items;
    }

    function client_server($clang, $lclang, $order)
    {
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetAPI.php');
        require_once(realpath('application') . '/libraries/paynet/paynet/PaynetConfig.php');
        $api = new PaynetEcomAPI(MERCHANT_CODE, MERCHANT_SEC_KEY, MERCHANT_USER, MERCHANT_USER_PASS);
        $prequest = new PaynetRequest();
//---------- merchant id , for example the order from eshop
        $prequest->ExternalID = round(microtime(true) * 1000);
        $prequest->LinkSuccess = 'https://' . $_SERVER['HTTP_HOST'] . "/" . $lclang . "/success?id=" . $prequest->ExternalID;
        $prequest->LinkCancel = 'https://' . $_SERVER['HTTP_HOST'] . "/" . $lclang . "/error?id=" . $prequest->ExternalID;
        $prequest->Lang = $lclang;
        $products = array();
        $prequest->Amount = 0;
        $i = 1;
        foreach ($order['order']['items'] as $item) {
            $quantity =
                !empty($item['amount'])
                    ? (int) $item['amount']
                    : 1;

            /*
            |--------------------------------------------------------------------------
            | Final Paynet amount for this row
            |--------------------------------------------------------------------------
            |
            | Для товаров CartCalculator уже передал final row total.
            |
            | Для доставки поля total нет, поэтому используем
            | обычный price * amount.
            |
            */

            if (isset($item['total'])) {
                $rowTotal =
                    round(
                        (float) $item['total'],
                        2
                    );
            } else {
                $rowTotal =
                    round(
                        (float) $item['price']
                        * $quantity,
                        2
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Paynet works with UnitPrice × Quantity
            |--------------------------------------------------------------------------
            |
            | Поэтому распределяем финальную стоимость строки
            | обратно на единицу товара.
            |
            */

            $unitFinalPrice =
                $quantity > 0
                    ? round(
                        $rowTotal / $quantity,
                        2
                    )
                    : 0;

            $product = array();

            $product['LineNo'] =
                $i;

            $product['Code'] =
                $item['SKU'];

            $product['Barcode'] =
                $item['id'];

            $product['Name'] =
                $item['name'];

            $product['Descrption'] =
                $item['name'];

            $product['Quantity'] =
                $quantity * 100;

            $product['UnitPrice'] =
                (int) round(
                    $unitFinalPrice * 100
                );

            $products[] =
                $product;

            /*
            |--------------------------------------------------------------------------
            | Paynet total
            |--------------------------------------------------------------------------
            */

            $prequest->Amount +=
                (int) round(
                    $rowTotal * 100
                );

            $i++;
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

        $prequest->Service = array('Name' => 'VN',
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

        // if($order['customer']['email'] === 'bodarev@ilab.md') {
        //     $prequest->Service['Amount'] = 1;
        //         $prequest->Products[0]['UnitPrice'] = 1;
        //         $prequest->Amount = 1;
        // }
        log_message(
            'error',
            'PAYNET AMOUNT: '
            . $prequest->Amount
            . '; PRODUCTS: '
            . json_encode($products)
        );
        $formObj = $api->FormCreate($prequest);
        if ($formObj->Code == PaynetCode::SUCCESS) {
            $data = array(
                'ExternalID' => $prequest->ExternalID,
            );
            $this->db->where('id', $order['order']['id']);
            $this->db->update('orders', $data);
            echo $formObj->Data;


            echo '<script>
                    document.getElementById("pay_form").submit();
                </script>';
            die();
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
            array('LineNo' => '1',
                'Code' => 'code1001',
                'Barcode' => '1001',
                'Name' => 'Ticket mini',
                'Description' => 'Description your product MINI',
                'Quantity' => 200,    // // 200 = 2.00  two
                'UnitPrice' => 2000),
            array('LineNo' => '2',
                'Name' => 'Ticket MAX',
                'Code' => 'code1002',
                'Barcode' => '1002',
                'Description' => 'Description your product MAX',
                'Quantity' => 100,    // 100 = 1.00  one
                'UnitPrice' => 1050),
            array('LineNo' => '3',
                'Name' => 'Ticket MAX 3',
                'Code' => 'code1003',
                'Barcode' => '1003',
                'Description' => 'Description your product MAX',
                'Quantity' => 300,    // 300 = 3.00  three
                'UnitPrice' => 500)
        );

        $prequest->Service = array(
            array('Name' => 'Demo eshop',
                'Description' => 'Demo eShop online desc',
                'Amount' => $prequest->Amount,
                'Products' => $prequest->Products)
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

    /*
    |--------------------------------------------------------------------------
    | General data
    |--------------------------------------------------------------------------
    */

    $data['site'] =
        $_SERVER['HTTP_HOST'];

    $data['added'] =
        transformDate(
            $data['added'],
            $this->lclang
        );

    $data['recipient_name'] =
        trim(
            ($data['name'] ?? '')
            . ' '
            . ($data['surname'] ?? '')
        );

    /*
    |--------------------------------------------------------------------------
    | Financial snapshot
    |--------------------------------------------------------------------------
    */

    $promoDiscount =
        !empty($data['promo_discount'])
            ? (float) $data['promo_discount']
            : 0;

    $bonusMinus =
        !empty($data['bonus_minus'])
            ? (float) $data['bonus_minus']
            : 0;

    /*
     * orders.total уже после promo,
     * но без доставки.
     */

    $data['products_total'] =
        round(
            (float) $data['total']
            + $promoDiscount,
            2
        );

    $data['promo_discount'] =
        round(
            $promoDiscount,
            2
        );

    $data['delivery_price'] =
        round(
            (float) $data['delivery_price'],
            2
        );

    $data['bonus_minus'] =
        round(
            $bonusMinus,
            2
        );

    $data['final_total'] =
        round(
            (float) $data['total']
            + $data['delivery_price']
            - $bonusMinus,
            2
        );

    /*
    |--------------------------------------------------------------------------
    | Payment title
    |--------------------------------------------------------------------------
    */

    if ((int) $data['payment'] === 1) {
        $data['payment'] =
            'Numerar la primire';
    } elseif ((int) $data['payment'] === 3) {
        $data['payment'] =
            'Plata cu cardul la curier';
    } else {
        $data['payment'] =
            'Card online';
    }

    /*
    |--------------------------------------------------------------------------
    | Promo row
    |--------------------------------------------------------------------------
    */

    $data['promo_row'] = '';

    if (
        !empty($data['promo_code'])
        && $data['promo_discount'] > 0
    ) {
        $data['promo_row'] = '
            <tr>
                <td
                    style="
                        padding: 0 0 12px;
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                        line-height: 20px;
                        color: #9a684b;
                    "
                >
                    Promocod '
                    . html_escape($data['promo_code'])
                    . '
                </td>

                <td
                    align="right"
                    style="
                        padding: 0 0 12px;
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                        line-height: 20px;
                        color: #9a684b;
                    "
                >
                    −'
                    . number_format(
                        $data['promo_discount'],
                        2,
                        '.',
                        ''
                    )
                    . ' MDL
                </td>
            </tr>
        ';
    }

    /*
    |--------------------------------------------------------------------------
    | Bonus row
    |--------------------------------------------------------------------------
    */

    $data['bonus_row'] = '';

    if ($data['bonus_minus'] > 0) {
        $data['bonus_row'] = '
            <tr>
                <td
                    style="
                        padding: 0 0 12px;
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                        line-height: 20px;
                        color: #777777;
                    "
                >
                    Bonusuri utilizate
                </td>

                <td
                    align="right"
                    style="
                        padding: 0 0 12px;
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                        line-height: 20px;
                        color: #111111;
                    "
                >
                    −'
                    . number_format(
                        $data['bonus_minus'],
                        2,
                        '.',
                        ''
                    )
                    . ' MDL
                </td>
            </tr>
        ';
    }

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    $productContent = '';

    foreach ($products as $product) {
        $productId =
            (int) $product['id'];

        $image =
            $this->db
                ->select('img')
                ->where(
                    'product_id',
                    $productId
                )
                ->order_by(
                    'id',
                    'ASC'
                )
                ->get(
                    'products_img'
                )
                ->row();

        $imageUrl = '';

        if (
            !empty($image)
            && !empty($image->img)
        ) {
            $imageUrl =
                'https://'
                . $data['site']
                . '/public/products/'
                . $image->img;
        }

        $rowTotal =
            isset($product['total'])
                ? (float) $product['total']
                : (
                    (float) $product['price']
                    * (int) $product['amount']
                );

        $promoRowDiscount =
            !empty($product['promo_discount'])
                ? (float) $product['promo_discount']
                : 0;

        $productContent .= '
            <tr>
                <td
                    width="82"
                    valign="top"
                    style="
                        width: 82px;
                        padding: 18px 16px 18px 0;
                        border-bottom: 1px solid #eeeeee;
                    "
                >';

        if ($imageUrl !== '') {
            $productContent .= '
                <img
                    src="' . html_escape($imageUrl) . '"
                    width="72"
                    height="72"
                    alt=""
                    style="
                        display: block;
                        width: 72px;
                        height: 72px;
                        object-fit: contain;
                        border: 0;
                        background-color: #f7f7f7;
                    "
                >
            ';
        } else {
            $productContent .= '
                <div
                    style="
                        width: 72px;
                        height: 72px;
                        background-color: #f7f7f7;
                    "
                ></div>
            ';
        }

        $productContent .= '
                </td>

                <td
                    valign="top"
                    style="
                        padding: 18px 10px 18px 0;
                        border-bottom: 1px solid #eeeeee;
                    "
                >
                    <p
                        style="
                            margin: 0;
                            font-family: Arial, Helvetica, sans-serif;
                            font-size: 14px;
                            line-height: 20px;
                            font-weight: 600;
                            color: #111111;
                        "
                    >
                        ' . html_escape($product['name']) . '
                    </p>

                    <p
                        style="
                            margin: 7px 0 0;
                            font-family: Arial, Helvetica, sans-serif;
                            font-size: 12px;
                            line-height: 18px;
                            color: #888888;
                        "
                    >
                        '
                        . number_format(
                            (float) $product['price'],
                            2,
                            '.',
                            ''
                        )
                        . ' MDL × '
                        . (int) $product['amount']
                        . '
                    </p>';

        if ($promoRowDiscount > 0) {
            $productContent .= '
                <p
                    style="
                        margin: 4px 0 0;
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 12px;
                        line-height: 18px;
                        color: #9a684b;
                    "
                >
                    Reducere promocod:
                    −'
                    . number_format(
                        $promoRowDiscount,
                        2,
                        '.',
                        ''
                    )
                    . ' MDL
                </p>
            ';
        }

        $productContent .= '
                </td>

                <td
                    width="105"
                    align="right"
                    valign="top"
                    style="
                        width: 105px;
                        padding: 18px 0;
                        border-bottom: 1px solid #eeeeee;
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                        line-height: 20px;
                        font-weight: 700;
                        color: #111111;
                        white-space: nowrap;
                    "
                >
                    '
                    . number_format(
                        $rowTotal,
                        2,
                        '.',
                        ''
                    )
                    . ' MDL
                </td>
            </tr>
        ';
    }

    $data['products'] =
        $productContent;

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    $tx =
        $this->parser
            ->parse(
                'layouts/email/order_email',
                $data,
                true
            );

    /*
    |--------------------------------------------------------------------------
    | Send
    |--------------------------------------------------------------------------
    */

    $this->load->library('email');

    $config =
        config_smtp();

    $this->email
        ->initialize(
            $config
        );

    $this->email->from(
        'noreply@vizaje-nica.com',
        'Vizaje-Nica'
    );

    $this->email->to(
        $data['email']
    );

    /*
     * PROD:
     * отправляем копию на рабочий адрес компании.
     */
    $this->email->cc(
        ORDER_EMAIL
    );

    $this->email->subject(
        'Comanda Vizaje-Nica #'
        . $data['order_id']
    );

    $this->email->message(
        $tx
    );

    if (!$this->email->send()) {
        log_message(
            'error',
            'Order email FAILED. Order #'
            . $data['order_id']
            . '; email='
            . $data['email']
        );

        return false;
    }

    log_message(
        'info',
        'Order email sent. Order #'
        . $data['order_id']
        . '; email='
        . $data['email']
    );

    return true;
}
}
