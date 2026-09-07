<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * AJAX-контроллер витрины.
 *
 * Обрабатывает операции корзины, подписку на рассылку, согласие на cookies
 * и тестовую отправку email. Методы возвращают JSON, числовой ответ или
 * диагностический HTML в зависимости от конкретного endpoint.
 *
 * Важно: файл содержит рабочую legacy-логику CodeIgniter 3. Комментарии
 * описывают текущее поведение, но не изменяют его.
 */
class Ajax extends CI_Controller
{
    /**
     * Инициализирует сессию, кодировку и языковой контекст запроса.
     *
     * Язык сохраняется в сессии и затем используется моделями и ответами.
     */
    public function __construct()
    {
        parent::__construct();
        @session_start();
        header('Content-type: text/html; charset=utf-8');
        //        check_if_POST();
        //        $this->load->library('session');

        if (empty($_SESSION['lang'])) get_prefered_language();
        $this->lclang = get_language(FALSE);
        $this->clang = get_language(TRUE);
        assign_language(uri(1));
    }

    /**
     * Определяет предпочтительный язык по HTTP_ACCEPT_LANGUAGE.
     *
     * Поддерживаются ru и ro; для остальных значений используется ru.
     */
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

    /**
     * Загружает текстовые и бизнес-константы для выбранного языка.
     *
     * @param string $lang Код языка, по умолчанию EN.
     */
    private function _define_constants($lang = 'EN')
    {
        $this->load->model('constants_model');
        $lang = mb_strtoupper($lang);
        $constants = $this->constants_model->find();
        foreach ($constants as $constant) {
            if (!defined($constant->name)) {
                define($constant->name, $constant->$lang);
            }
        }
    }

    /**
     * Проверяет состояние авторизации по данным сессии.
     *
     * При валидной сессии помещает данные клиента в $this->data[client_info].
     * При неполной или устаревшей сессии удаляет её идентификаторы.
     */
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
            $this->load->model('clients_model');
            $client_info = $this->clients_model->get_client_login($user_id);
            if (empty($client_info)) {
                unset($_SESSION['user_id']);
                unset($_SESSION['user_login']);
                unset($_SESSION['usr_key']);
                unset($_SESSION['isb2b']);
            } else {
                $this->data['client_info'] = $client_info;
            }
        }
    }

    /* ======================================================================
     * КОРЗИНА: ДОБАВЛЕНИЕ ТОВАРА
     * ====================================================================== */
    /**
     * Добавляет товар или его вариант в корзину.
     *
     * POST: product_id, quantity, variable.
     * Если передан $add_item, метод использует его вместо POST и возвращает
     * массив; при обычном AJAX-вызове выводит JSON.
     *
     * @param array|null $add_item Данные для внутреннего вызова.
     * @return array|void
     */
    public function cart_add($add_item = null)
{
    check_if_POST();

    $this->load->library(['cart']);
    $this->load->model('products_model');
    $this->_define_constants();

    $post = $add_item
        ? $add_item
        : $this->input->post();

    $productId = !empty($post['product_id'])
        ? (int) $post['product_id']
        : 0;

    $variableId = !empty($post['variable'])
        ? (int) $post['variable']
        : 0;

    $requestedQuantity = !empty($post['quantity'])
        ? max(1, (int) $post['quantity'])
        : 1;

    $response = [
        'status' => 'error',
        'code' => 'CART_ADD_FAILED',
        'message' => 'Не удалось добавить товар в корзину.',
        'quantity' => 0,
        'added_quantity' => 0,
        'total_items' => $this->cart->total_items(),
        'delivery_price' => defined('SHIPPING_PRICE')
            ? SHIPPING_PRICE
            : 0,
        'delivery_free' => defined('FREE_SHIPPING_VALUE')
            ? FREE_SHIPPING_VALUE
            : 0,
    ];

    if ($productId <= 0) {
        return $this->cartAddResponse(
            $response,
            $add_item,
            422
        );
    }

    $product = $this->products_model
        ->findFirstCart($productId);

    if (empty($product)) {
        $response['code'] = 'PRODUCT_NOT_FOUND';
        $response['message'] = 'Товар не найден.';

        return $this->cartAddResponse(
            $response,
            $add_item,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Вариант
    |--------------------------------------------------------------------------
    */

    $variable = null;

    if ($variableId > 0) {
        $variable = $this->products_model
            ->get_product_variable_by_id(
                $this->clang,
                $variableId,
                $productId
            );

        if (empty($variable)) {
            $response['code'] = 'VARIABLE_NOT_FOUND';
            $response['message'] =
                'Выбранный вариант товара не найден.';

            return $this->cartAddResponse(
                $response,
                $add_item,
                404
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Цена и остаток
    |--------------------------------------------------------------------------
    */

    $isB2B = !empty($_SESSION['isb2b']);

    if (!empty($variable)) {
        if ($isB2B) {
            $price = (float) $variable->priceWH;
            $availableStock = (int) $variable->qtyWH;
        } else {
            $price = !empty($variable->discount_price)
                ? (float) $variable->discount_price
                : (float) $variable->price;

            $availableStock = (int) $variable->qty;
        }
    } else {
        if ($isB2B) {
            $price = (float) $product->priceWH;
            $availableStock = (int) $product->on_stockWH;
        } else {
            $price = !empty($product->discount_price)
                ? (float) $product->discount_price
                : (float) $product->price;

            $availableStock = (int) $product->on_stock;
        }
    }

    if ($availableStock <= 0) {
        $response['code'] = 'OUT_OF_STOCK';
        $response['message'] = 'Товара нет в наличии.';

        return $this->cartAddResponse(
            $response,
            $add_item,
            409
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ищем такую же позицию в корзине
    |--------------------------------------------------------------------------
    */

    $existingRowId = null;
    $existingQuantity = 0;

    foreach ($this->cart->contents() as $cartItem) {
        $cartVariableId = !empty($cartItem['options'])
            ? (int) $cartItem['options']
            : 0;

        if (
            (int) $cartItem['id'] === $productId
            && $cartVariableId === $variableId
        ) {
            $existingRowId = $cartItem['rowid'];
            $existingQuantity = (int) $cartItem['qty'];

            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Уже достигнут доступный максимум
    |--------------------------------------------------------------------------
    */

    if ($existingQuantity >= $availableStock) {
        $response['status'] = 'limit';
        $response['code'] = 'MAX_STOCK_REACHED';
        $response['message'] = $availableStock === 1
            ? 'Последняя доступная единица уже находится в корзине.'
            : 'В корзине уже находится максимальное доступное количество.';

        $response['quantity'] = $existingQuantity;
        $response['max_quantity'] = $availableStock;
        $response['total_items'] =
            $this->cart->total_items();

        return $this->cartAddResponse(
            $response,
            $add_item,
            200
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Сколько реально можно добавить
    |--------------------------------------------------------------------------
    */

    $remainingStock =
        $availableStock - $existingQuantity;

    $acceptedQuantity = min(
        $requestedQuantity,
        $remainingStock
    );

    if ($acceptedQuantity <= 0) {
        $response['status'] = 'limit';
        $response['code'] = 'MAX_STOCK_REACHED';
        $response['message'] =
            'Максимальное доступное количество уже находится в корзине.';

        return $this->cartAddResponse(
            $response,
            $add_item,
            200
        );
    }

    $finalQuantity =
        $existingQuantity + $acceptedQuantity;

    /*
    |--------------------------------------------------------------------------
    | Обновление или вставка
    |--------------------------------------------------------------------------
    */

    if ($existingRowId) {
        $saved = $this->cart->update([
            'rowid' => $existingRowId,
            'qty' => $finalQuantity,
            'price' => $price,
        ]);

        $rowId = $existingRowId;
    } else {
        $item = [
            'id' => $productId,
            'name' => str_replace(
                '=',
                '',
                transliteration($product->titleRO)
            ),
            'price' => $price,
            'qty' => $acceptedQuantity,
            'options' => $variableId,
        ];

        $rowId = $this->cart->insert($item);
        $saved = !empty($rowId);
    }

    if (!$saved) {
    $response['code'] = 'CART_SAVE_FAILED';
    $response['message'] = 'Корзина не подтвердила добавление товара.';

    return $this->cartAddResponse(
        $response,
        $add_item,
        500
    );
}

/*
|--------------------------------------------------------------------------
| Успешный ответ
|--------------------------------------------------------------------------
|
| Доверяем локально посчитанным данным ($finalQuantity),
| а не get_item() сразу после insert/update — библиотека Cart
| после _save_cart() сама перечитывает контент из сессии,
| и это чтение "по горячим следам" на первом запросе новой
| сессии не всегда успевает отразить только что записанные
| данные. Отсюда ложный CART_ITEM_NOT_CONFIRMED на первом клике.
|
*/

$response['status'] = 'ok';
$response['code'] = 'ITEM_ADDED';
$response['message'] =
    'Товар добавлен в корзину.';

$response['row_id'] =
    $rowId;

$response['quantity'] =
    $finalQuantity;

$response['added_quantity'] =
    $acceptedQuantity;

$response['max_quantity'] =
    $availableStock;

$response['total_items'] =
    $this->cart->total_items();

$response['cart_total'] =
    number_format(
        $this->cart->total(),
        0,
        '.',
        ''
    );

return $this->cartAddResponse(
    $response,
    $add_item,
    200
);
}
private function cartAddResponse(
    array $response,
    $internalCall = null,
    $statusCode = 200
) {
    if ($internalCall) {
        return $response;
    }

    return $this->output
        ->set_status_header($statusCode)
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                $response,
                JSON_UNESCAPED_UNICODE
            )
        );
}

private function _cart_ui_state(
    $lclang,
    $targetRowId = null
) {
    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    $lclang =
        strtolower(
            trim(
                (string) $lclang
            )
        );

    if (
        $lclang !== 'ru'
        && $lclang !== 'ro'
    ) {
        $lclang = 'ru';
    }

    /*
    |--------------------------------------------------------------------------
    | Calculator
    |--------------------------------------------------------------------------
    */

    $this->load->library(
        'CartCalculator'
    );

    $state =
        $this->cartcalculator
            ->calculate(
                $lclang
            );

    /*
    |--------------------------------------------------------------------------
    | Requested row
    |--------------------------------------------------------------------------
    |
    | CartCalculator возвращает все строки.
    | Для cart_update нам по старому контракту нужна только изменённая строка.
    |
    */

    $targetRow =
        null;

    if (
        $targetRowId !== null
        && !empty($state['rows'])
    ) {
        foreach (
            $state['rows'] as $row
        ) {
            if (
                (string) $row['row_id']
                !== (string) $targetRowId
            ) {
                continue;
            }

            $targetRow = array(
                'row_id' =>
                    $row['row_id'],

                'quantity' =>
                    (int) $row['quantity'],

                'max_quantity' =>
                    (int) $row['max_quantity'],

                'unit_price' =>
                    (float) $row['unit_price'],

                'unit_original_price' =>
                    (float) $row['unit_original_price'],

                /*
                |--------------------------------------------------------------------------
                | Current total
                |--------------------------------------------------------------------------
                |
                | Здесь оставляем именно цену товара ДО promo.
                |
                | JS строки корзины сейчас показывает обычную цену товара,
                | а promo позже будем выводить отдельным состоянием.
                |
                */

                'current_total' =>
                    (float) $row['current_total'],

                'original_total' =>
                    (float) $row['original_total'],

                /*
                |--------------------------------------------------------------------------
                | Legacy row discount
                |--------------------------------------------------------------------------
                |
                | Старый JS ожидает row.discount для обычной скидки товара.
                | Promo сюда пока не смешиваем.
                |
                */

                'discount' =>
                    (float) $row['product_discount'],

                /*
                |--------------------------------------------------------------------------
                | New promo fields
                |--------------------------------------------------------------------------
                */

                'promo_eligible' =>
                    !empty(
                        $row['promo_eligible']
                    ),

                'promo_discount' =>
                    (float) $row['promo_discount'],

                'final_total' =>
                    (float) $row['final_total'],

                'bonus' =>
                    (float) $row['bonus'],
            );

            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Unified state
    |--------------------------------------------------------------------------
    */

    return array(
        'row' =>
            $targetRow,

        'rows' =>
            $state['rows'],

        'cart' =>
            $state['cart'],

        'promo' =>
            $state['promo'],

        'shipping' =>
            $state['shipping'],

        'empty' =>
            $state['empty'],
    );
}

    /**
     * Возвращает позиции корзины вместе с данными товаров из модели.
     *
     * @return array
     */
    protected function cart_contents()
    {
        $cart_products = $this->cart->contents();
        foreach ($cart_products as $key => $product) {
            $this->load->model('products_model');
            $cart_products[$key]['product'] = $this->products_model->product_item_cart($this->clang, $product['rowid']);
        }
        return $cart_products;
    }

    /* ======================================================================
     * КОРЗИНА: ИЗМЕНЕНИЕ КОЛИЧЕСТВА
     * ====================================================================== */
    /**
     * Обновляет количество конкретной позиции корзины.
     *
     * POST: rowid, quantity, lang.
     * Дополнительно пересчитывает цены, доставку, скидки и ожидаемые бонусы.
     */
   public function cart_update()
{
    check_if_POST();

    $this->load->library('cart');
    $this->load->model('products_model');

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    $lclang = strtolower(
        trim(
            (string) $this->input->post('lang')
        )
    );

    if (
        $lclang !== 'ru'
        && $lclang !== 'ro'
    ) {
        $lclang = 'ru';
    }

    $clang =
        strtoupper($lclang);

    $this->_define_constants(
        $lclang
    );

    $this->_is_logged_in();

    /*
    |--------------------------------------------------------------------------
    | Input
    |--------------------------------------------------------------------------
    */

    $rowId =
        (string) $this->input->post(
            'rowid'
        );

    $requestedQuantity =
        (int) $this->input->post(
            'quantity'
        );

    $response = array(
        'status' => 'error',
        'code' => 'CART_UPDATE_FAILED',
    );

    if (empty($rowId)) {
        return $this->output
            ->set_status_header(422)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    $response,
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Existing cart row
    |--------------------------------------------------------------------------
    */

    $item =
        $this->cart->get_item(
            $rowId
        );

    if (empty($item)) {
        $response['code'] =
            'CART_ROW_NOT_FOUND';

        return $this->output
            ->set_status_header(404)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    $response,
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    $product =
        $this->products_model
            ->findFirstCart(
                $item['id']
            );

    if (empty($product)) {
        $response['code'] =
            'PRODUCT_NOT_FOUND';

        return $this->output
            ->set_status_header(404)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    $response,
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Variant
    |--------------------------------------------------------------------------
    |
    | Не восстанавливаем вариант по цене.
    | CI Cart уже хранит его ID в options.
    |
    */

    $variable = null;

    $variableId =
        !empty($item['options'])
            ? (int) $item['options']
            : 0;

    if ($variableId > 0) {
        $variable =
            $this->products_model
                ->get_product_variable_by_id(
                    $clang,
                    $variableId,
                    $item['id']
                );

        if (empty($variable)) {
            $response['code'] =
                'VARIABLE_NOT_FOUND';

            return $this->output
                ->set_status_header(404)
                ->set_content_type(
                    'application/json',
                    'utf-8'
                )
                ->set_output(
                    json_encode(
                        $response,
                        JSON_UNESCAPED_UNICODE
                    )
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Authoritative price / stock
    |--------------------------------------------------------------------------
    */

    $isB2B =
        !empty($_SESSION['isb2b']);

    if (!empty($variable)) {
        if ($isB2B) {
            $price =
                (float) $variable->priceWH;

            $availableStock =
                (int) $variable->qtyWH;
        } else {
            $price =
                !empty(
                    $variable->discount_price
                )
                    ? (float) $variable->discount_price
                    : (float) $variable->price;

            $availableStock =
                (int) $variable->qty;
        }
    } else {
        if ($isB2B) {
            $price =
                (float) $product->priceWH;

            $availableStock =
                (int) $product->on_stockWH;
        } else {
            $price =
                !empty(
                    $product->discount_price
                )
                    ? (float) $product->discount_price
                    : (float) $product->price;

            $availableStock =
                (int) $product->on_stock;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Quantity
    |--------------------------------------------------------------------------
    */

    if ($requestedQuantity <= 0) {
        $this->cart->remove(
            $rowId
        );

        $quantity = 0;
    } else {
        $quantity =
            min(
                max(
                    1,
                    $requestedQuantity
                ),
                max(
                    1,
                    $availableStock
                )
            );

        $saved =
            $this->cart->update(
                array(
                    'rowid' =>
                        $rowId,

                    'qty' =>
                        $quantity,

                    'price' =>
                        $price,
                )
            );

        if (!$saved) {
            $response['code'] =
                'CART_SAVE_FAILED';

            return $this->output
                ->set_status_header(500)
                ->set_content_type(
                    'application/json',
                    'utf-8'
                )
                ->set_output(
                    json_encode(
                        $response,
                        JSON_UNESCAPED_UNICODE
                    )
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Unified UI state
    |--------------------------------------------------------------------------
    */

    $state =
        $this->_cart_ui_state(
            $lclang,
            $quantity > 0
                ? $rowId
                : null
        );

    /*
    |--------------------------------------------------------------------------
    | New contract
    |--------------------------------------------------------------------------
    */

    $response = array(
        'status' =>
            'ok',

        'code' =>
            'CART_UPDATED',

        'row' =>
            $state['row'],

        'cart' =>
            $state['cart'],

        'promo' =>
            $state['promo'],

        'shipping' =>
            $state['shipping'],

        'empty' =>
            $state['empty'],
    );

    /*
    |--------------------------------------------------------------------------
    | Legacy aliases
    |--------------------------------------------------------------------------
    |
    | Пока оставляем, чтобы не ломать старый JS / Cart Page.
    |
    */

    $response['row_id'] =
        $rowId;

    $response['product_qty'] =
        $quantity;

    $response['price'] =
        $price;

    $response['total_items'] =
        $state['cart']['total_items'];

    $response['cart_total'] =
        number_format(
            $state['cart']['cart_total'],
            0,
            '.',
            ''
        );

    $response['total_price'] =
        !empty($state['row'])
            ? number_format(
                $state['row']['current_total'],
                0,
                '.',
                ''
            )
            : '0';

    $response['sele_price'] =
        number_format(
            $state['cart']['discount_total'],
            2,
            '.',
            ''
        );

    $response['delivery_price'] =
        $state['cart']['delivery_price'];

    $response['delivery_free'] =
        $state['shipping']['threshold'];

    $response['total'] =
        number_format(
            $state['cart']['total'],
            0,
            '.',
            ''
        );

    $response['bonuses'] =
        $state['cart']['bonus_total'];

    return $this->output
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                $response,
                JSON_UNESCAPED_UNICODE
            )
        );
}

    /* ======================================================================
     * КОРЗИНА: УДАЛЕНИЕ ПОЗИЦИИ
     * ====================================================================== */
    /**
     * Удаляет позицию по rowid и пересчитывает корзину.
     *
     * POST: rowid, lang.
     * Ответ содержит количество товаров, суммы, доставку и бонусы.
     */
    public function cart_delete()
{
    check_if_POST();

    $this->load->library('cart');

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    $lclang =
        strtolower(
            trim(
                (string) $this->input->post(
                    'lang'
                )
            )
        );

    if (
        $lclang !== 'ru'
        && $lclang !== 'ro'
    ) {
        $lclang = 'ru';
    }

    /*
    |--------------------------------------------------------------------------
    | Constants / auth
    |--------------------------------------------------------------------------
    */

    $this->_define_constants(
        $lclang
    );

    $this->_is_logged_in();

    /*
    |--------------------------------------------------------------------------
    | Row
    |--------------------------------------------------------------------------
    */

    $rowId =
        (string) $this->input->post(
            'rowid'
        );

    if (empty($rowId)) {
        return $this->output
            ->set_status_header(422)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' =>
                            'error',

                        'code' =>
                            'ROW_ID_REQUIRED',
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Existing row
    |--------------------------------------------------------------------------
    */

    $item =
        $this->cart->get_item(
            $rowId
        );

    if (empty($item)) {
        return $this->output
            ->set_status_header(404)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' =>
                            'error',

                        'code' =>
                            'CART_ROW_NOT_FOUND',
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Remove
    |--------------------------------------------------------------------------
    */

    $removed =
        $this->cart->remove(
            $rowId
        );

    if (!$removed) {
        return $this->output
            ->set_status_header(500)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' =>
                            'error',

                        'code' =>
                            'CART_REMOVE_FAILED',
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Unified UI state
    |--------------------------------------------------------------------------
    */

    $state =
        $this->_cart_ui_state(
            $lclang
        );

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    $response = array(
        'status' =>
            'ok',

        'code' =>
            'CART_ITEM_REMOVED',

        'row_id' =>
            $rowId,

        'row' =>
            null,

        'cart' =>
            $state['cart'],

        'promo' =>
            $state['promo'],

        'shipping' =>
            $state['shipping'],

        'empty' =>
            $state['empty'],
    );

    /*
    |--------------------------------------------------------------------------
    | Legacy aliases
    |--------------------------------------------------------------------------
    */

    $response['quantity'] =
        $state['cart']['total_items'];

    $response['total_items'] =
        $state['cart']['total_items'];

    $response['cart_subtotal'] =
        $state['cart']['cart_total'];

    $response['cart_total'] =
        number_format(
            $state['cart']['cart_total'],
            0,
            '.',
            ''
        );

    $response['delivery_price'] =
        $state['cart']['delivery_price'];

    $response['delivery_free'] =
        $state['shipping']['threshold'];

    $response['total'] =
        number_format(
            $state['cart']['total'],
            0,
            '.',
            ''
        );

    $response['sele_price'] =
        number_format(
            $state['cart']['discount_total'],
            2,
            '.',
            ''
        );

    $response['bonuses'] =
        $state['cart']['bonus_total'];

    return $this->output
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                $response,
                JSON_UNESCAPED_UNICODE
            )
        );
}

public function promo_apply()
{
    check_if_POST();

    $this->load->library(
        'cart'
    );

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    $lclang =
        strtolower(
            trim(
                (string) $this->input->post(
                    'lang'
                )
            )
        );

    if (
        $lclang !== 'ru'
        && $lclang !== 'ro'
    ) {
        $lclang = 'ru';
    }

    /*
    |--------------------------------------------------------------------------
    | Constants / auth
    |--------------------------------------------------------------------------
    */

    $this->_define_constants(
        $lclang
    );

    $this->_is_logged_in();

    $this->load->model(
        'promocodes_model'
    );

    $clientId =
        !empty($_SESSION['user_id'])
            ? (int) $_SESSION['user_id']
            : null;

    /*
    |--------------------------------------------------------------------------
    | Code
    |--------------------------------------------------------------------------
    */

    $code =
        strtoupper(
            trim(
                (string) $this->input->post(
                    'code',
                    true
                )
            )
        );

    if ($code === '') {
        return $this->output
            ->set_status_header(422)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' =>
                            'error',

                        'code' =>
                            'PROMO_CODE_REQUIRED',

                        'message' =>
                            'Введите промокод.',
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate promo without saving it first
    |--------------------------------------------------------------------------
    */

    $this->load->library(
        'CartCalculator'
    );

    $state =
        $this->cartcalculator
            ->calculate(
                $lclang,
                $code
            );

    /*
    |--------------------------------------------------------------------------
    | Invalid
    |--------------------------------------------------------------------------
    */

    if (
        empty(
            $state['promo']['applied']
        )
    ) {

        $this->promocodes_model->logEvent(
            'APPLY_FAILED',
            array(
                'promocode_id' =>
                    !empty($state['promo']['id'])
                        ? (int) $state['promo']['id']
                        : null,

                'client_id' =>
                    $clientId,

                'code' =>
                    $code,

                'cart_subtotal' =>
                    !empty($state['cart']['cart_total'])
                        ? (float) $state['cart']['cart_total']
                        : 0,

                'discount_amount' =>
                    0,

                'final_total' =>
                    isset($state['cart']['total'])
                        ? (float) $state['cart']['total']
                        : 0,

                'reason' =>
                    !empty($state['promo']['reason'])
                        ? $state['promo']['reason']
                        : 'PROMO_NOT_APPLICABLE',

                'ip_address' =>
                    $this->input->ip_address(),

                'user_agent' =>
                    $this->input->user_agent(),
            )
        );

        unset(
            $_SESSION['cart_promo']
        );

        return $this->output
            ->set_status_header(422)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' =>
                            'error',

                        'code' =>
                            !empty(
                                $state['promo']['reason']
                            )
                                ? $state['promo']['reason']
                                : 'PROMO_NOT_APPLICABLE',

                        'promo' =>
                            $state['promo'],

                        'cart' =>
                            $state['cart'],

                        'shipping' =>
                            $state['shipping'],

                        'empty' =>
                            $state['empty'],
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Save only code in session
    |--------------------------------------------------------------------------
    */

    $_SESSION['cart_promo'] =
        array(
            'code' =>
                $code,
        );

    /*
    |--------------------------------------------------------------------------
    | Recalculate from session
    |--------------------------------------------------------------------------
    */

    $state =
        $this->_cart_ui_state(
            $lclang
        );

    $this->promocodes_model->logEvent(
        'APPLY_SUCCESS',
        array(
            'promocode_id' =>
                !empty($state['promo']['id'])
                    ? (int) $state['promo']['id']
                    : null,

            'client_id' =>
                $clientId,

            'code' =>
                !empty($state['promo']['code'])
                    ? $state['promo']['code']
                    : $code,

            'cart_subtotal' =>
                isset($state['cart']['cart_total'])
                    ? (float) $state['cart']['cart_total']
                    : 0,

            'discount_amount' =>
                isset($state['cart']['promo_discount_total'])
                    ? (float) $state['cart']['promo_discount_total']
                    : 0,

            'final_total' =>
                isset($state['cart']['total'])
                    ? (float) $state['cart']['total']
                    : 0,

            'ip_address' =>
                $this->input->ip_address(),

            'user_agent' =>
                $this->input->user_agent(),
        )
    );

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return $this->output
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                array(
                    'status' =>
                        'ok',

                    'code' =>
                        'PROMO_APPLIED',

                    'promo' =>
                        $state['promo'],

                    'cart' =>
                        $state['cart'],

                    'shipping' =>
                        $state['shipping'],

                    'empty' =>
                        $state['empty'],
                ),
                JSON_UNESCAPED_UNICODE
            )
        );
}

public function promo_remove()
{
    check_if_POST();

    $this->load->library(
        'cart'
    );

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    $lclang =
        strtolower(
            trim(
                (string) $this->input->post(
                    'lang'
                )
            )
        );

    if (
        $lclang !== 'ru'
        && $lclang !== 'ro'
    ) {
        $lclang = 'ru';
    }

    /*
    |--------------------------------------------------------------------------
    | Constants / auth
    |--------------------------------------------------------------------------
    */

    $this->_define_constants(
        $lclang
    );

    $this->_is_logged_in();

    $this->load->model(
        'promocodes_model'
    );

    $clientId =
        !empty($_SESSION['user_id'])
            ? (int) $_SESSION['user_id']
            : null;

    /*
    |--------------------------------------------------------------------------
    | Remove promo
    |--------------------------------------------------------------------------
    */

    $beforeState =
        $this->_cart_ui_state(
            $lclang
        );

    $removedCode =
        !empty($_SESSION['cart_promo']['code'])
            ? strtoupper(
                trim(
                    (string) $_SESSION['cart_promo']['code']
                )
            )
            : '';

    unset(
        $_SESSION['cart_promo']
    );

    /*
    |--------------------------------------------------------------------------
    | Recalculate cart
    |--------------------------------------------------------------------------
    */

    $state =
        $this->_cart_ui_state(
            $lclang
        );

    if ($removedCode !== '') {
        $this->promocodes_model->logEvent(
            'REMOVE',
            array(
                'promocode_id' =>
                    !empty($beforeState['promo']['id'])
                        ? (int) $beforeState['promo']['id']
                        : null,

                'client_id' =>
                    $clientId,

                'code' =>
                    $removedCode,

                'cart_subtotal' =>
                    isset($beforeState['cart']['cart_total'])
                        ? (float) $beforeState['cart']['cart_total']
                        : 0,

                'discount_amount' =>
                    isset($beforeState['cart']['promo_discount_total'])
                        ? (float) $beforeState['cart']['promo_discount_total']
                        : 0,

                'final_total' =>
                    isset($beforeState['cart']['total'])
                        ? (float) $beforeState['cart']['total']
                        : 0,

                'ip_address' =>
                    $this->input->ip_address(),

                'user_agent' =>
                    $this->input->user_agent(),
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return $this->output
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                array(
                    'status' =>
                        'ok',

                    'code' =>
                        'PROMO_REMOVED',

                    'promo' =>
                        $state['promo'],

                    'cart' =>
                        $state['cart'],

                    'shipping' =>
                        $state['shipping'],

                    'empty' =>
                        $state['empty'],
                ),
                JSON_UNESCAPED_UNICODE
            )
        );
}

public function product_quick_view()
{
    check_if_POST();

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    $lclang =
        strtolower(
            trim(
                (string) $this->input->post(
                    'lang',
                    true
                )
            )
        );

    if (
        $lclang !== 'ru'
        && $lclang !== 'ro'
    ) {
        $lclang = 'ru';
    }

    $clang =
        strtoupper(
            $lclang
        );

    /*
    |--------------------------------------------------------------------------
    | Product ID
    |--------------------------------------------------------------------------
    */

    $productId =
        (int) $this->input->post(
            'product_id',
            true
        );

    if ($productId <= 0) {
        return $this->output
            ->set_status_header(422)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' =>
                            'error',

                        'code' =>
                            'PRODUCT_ID_REQUIRED',
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Context
    |--------------------------------------------------------------------------
    */

    $this->_define_constants(
        $lclang
    );

    $this->_is_logged_in();

    $this->load->model(
        'products_model'
    );

    $this->load->model(
        'menu_model'
    );

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    $product =
        $this->products_model
            ->get_product_by_id_for_quick_view(
                $clang,
                $productId
            );

    if (empty($product)) {
        return $this->output
            ->set_status_header(404)
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' =>
                            'error',

                        'code' =>
                            'PRODUCT_NOT_FOUND',
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */

    $product->img =
        $this->products_model
            ->get_product_img(
                $product->id
            );

    /*
    |--------------------------------------------------------------------------
    | Variations
    |--------------------------------------------------------------------------
    |
    | true:
    | Quick View, как PDP, получает также OOS варианты.
    |
    */

    $product->variable =
        $this->products_model
            ->get_product_variable(
                $clang,
                $product->id,
                true
            );

    /*
    |--------------------------------------------------------------------------
    | Menu
    |--------------------------------------------------------------------------
    */

    $menu =
        $this->menu_model
            ->get_menu(
                $clang
            );

    /*
    |--------------------------------------------------------------------------
    | Client
    |--------------------------------------------------------------------------
    */

    $clientInfo =
        isset(
            $this->data['client_info']
        )
            ? $this->data['client_info']
            : null;

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    $html =
        $this->load->view(
            'layouts/pages/product/quick-view',
            array(
                'product' =>
                    $product,

                'lclang' =>
                    $lclang,

                'clang' =>
                    $clang,

                'menu' =>
                    $menu,

                'client_info' =>
                    $clientInfo,

                'is_b2b' =>
                    !empty(
                        $_SESSION['isb2b']
                    ),
            ),
            true
        );

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return $this->output
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                array(
                    'status' =>
                        'ok',

                    'html' =>
                        $html,

                    'product_id' =>
                        $productId,
                ),
                JSON_UNESCAPED_UNICODE
            )
        );
}

    public function recently_viewed()
{
    check_if_POST();
     $this->_define_constants(
        $this->lclang
    );
    $this->load->model('products_model');
    $this->load->model('menu_model');
    $this->load->model('clients_model');

    $ids = $this->input->post('ids');

    if (!is_array($ids)) {
        $ids = array();
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize IDs
    |--------------------------------------------------------------------------
    */

    $productIds = array();

    foreach ($ids as $id) {
        $id = (int) $id;

        if (
            $id <= 0
            || in_array(
                $id,
                $productIds,
                true
            )
        ) {
            continue;
        }

        $productIds[] = $id;

        if (count($productIds) >= 20) {
            break;
        }
    }

    if (empty($productIds)) {
        return $this->output
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' => 'ok',
                        'html' => '',
                        'count' => 0,
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    $products =
        $this->products_model
            ->get_products_views(
                $this->clang,
                $productIds,
                10
            );

    if (empty($products)) {
        return $this->output
            ->set_content_type(
                'application/json',
                'utf-8'
            )
            ->set_output(
                json_encode(
                    array(
                        'status' => 'ok',
                        'html' => '',
                        'count' => 0,
                    ),
                    JSON_UNESCAPED_UNICODE
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Frontend context required by product_slider
    |--------------------------------------------------------------------------
    */

    $menu =
        $this->menu_model
            ->get_menu(
                $this->clang
            );

    $wishlist = array();

    $wishlistCookie =
        get_cookie(
            'produse_wishlust'
        );

    if (!empty($wishlistCookie)) {
        $wishlist = array_filter(
            array_map(
                'intval',
                explode(
                    ',',
                    $wishlistCookie
                )
            )
        );
    }

    $clientInfo = null;

    if (
        !empty($_SESSION['user_id'])
        && !empty($_SESSION['user_login'])
        && !empty($_SESSION['usr_key'])
    ) {
        $clientInfo =
            $this->clients_model
                ->get_client_login(
                    (int) $_SESSION['user_id']
                );
    }

    /*
    |--------------------------------------------------------------------------
    | Render cards
    |--------------------------------------------------------------------------
    */

    $html = '';

    foreach ($products as $product) {
        $html .=
            $this->load->view(
                'layouts/pages/product_slider',
                array(
                    'item' => $product,
                    'lclang' => $this->lclang,
                    'clang' => $this->clang,
                    'menu' => $menu,
                    'wishlist' => $wishlist,
                    'client_info' => $clientInfo,
                ),
                true
            );
    }

    return $this->output
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                array(
                    'status' => 'ok',
                    'html' => $html,
                    'count' => count($products),
                ),
                JSON_UNESCAPED_UNICODE
            )
        );
}

public function cart_drawer()
{
    $this->load->library('cart');

    $this->load->model(
        'products_model'
    );

    $this->load->model(
        'menu_model'
    );

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */

    $lclang =
        strtolower(
            trim(
                (string) $this->input->get(
                    'lang'
                )
            )
        );

    if (
        $lclang !== 'ru'
        && $lclang !== 'ro'
    ) {
        $lclang = 'ru';
    }

    $clang =
        strtoupper(
            $lclang
        );

    /*
    |--------------------------------------------------------------------------
    | Constants / auth
    |--------------------------------------------------------------------------
    */

    $this->_define_constants(
        $lclang
    );

    $this->_is_logged_in();

    /*
    |--------------------------------------------------------------------------
    | Unified cart state
    |--------------------------------------------------------------------------
    */

    $state =
        $this->_cart_ui_state(
            $lclang
        );

    /*
    |--------------------------------------------------------------------------
    | Calculator rows indexed by row_id
    |--------------------------------------------------------------------------
    |
    | CartCalculator содержит всю финансовую информацию строки:
    |
    | - original/current price
    | - product discount
    | - promo eligibility
    | - promo discount
    | - bonus
    | - final total
    |
    */

    $calculatedRows =
        array();

    if (!empty($state['rows'])) {
        foreach (
            $state['rows'] as $row
        ) {
            $calculatedRows[
                (string) $row['row_id']
            ] = $row;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cart items for view
    |--------------------------------------------------------------------------
    |
    | Здесь больше ничего не считаем.
    | Только собираем данные для отображения.
    |
    */

    $cartItems =
        $this->cart->contents();

    foreach (
        $cartItems as $key => $item
    ) {
        $product =
            $this->products_model
                ->product_item_cart(
                    $clang,
                    $item['rowid']
                );

        $cartItems[$key]['products'] =
            $product;

        /*
        |--------------------------------------------------------------------------
        | Variable
        |--------------------------------------------------------------------------
        */

        $cartItems[$key]['variable'] =
            null;

        if (!empty($item['options'])) {
            $cartItems[$key]['variable'] =
                $this->products_model
                    ->get_product_variable_by_id(
                        $clang,
                        (int) $item['options'],
                        (int) $item['id']
                    );
        }

        /*
        |--------------------------------------------------------------------------
        | Calculated state
        |--------------------------------------------------------------------------
        */

        $rowId =
            (string) $item['rowid'];

        $cartItems[$key]['calculated'] =
            isset(
                $calculatedRows[$rowId]
            )
                ? $calculatedRows[$rowId]
                : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Menu
    |--------------------------------------------------------------------------
    */

    $menu =
        $this->menu_model
            ->get_menu(
                $clang
            );

    /*
    |--------------------------------------------------------------------------
    | Client / B2B
    |--------------------------------------------------------------------------
    */

    $clientInfo =
        isset(
            $this->data['client_info']
        )
            ? $this->data['client_info']
            : null;

    $isB2B =
        !empty(
            $_SESSION['isb2b']
        );

    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */

    $html =
        $this->load->view(
            'layouts/pages/cart/drawer-content',
            array(
                /*
                |--------------------------------------------------------------------------
                | Products
                |--------------------------------------------------------------------------
                */

                'cart_items' =>
                    $cartItems,

                /*
                |--------------------------------------------------------------------------
                | Legacy-compatible values
                |--------------------------------------------------------------------------
                */

                'total_items_cart' =>
                    $state['cart']['total_items'],

                'total_price_cart' =>
                    $state['cart']['cart_total'],

                'delivery_price' =>
                    $state['cart']['delivery_price'],

                'bonus_total' =>
                    $state['cart']['bonus_total'],

                /*
                |--------------------------------------------------------------------------
                | New totals
                |--------------------------------------------------------------------------
                */

                'products_total' =>
                    $state['cart']['products_total'],

                'product_discount_total' =>
                    $state['cart']['product_discount_total'],

                'promo_discount_total' =>
                    $state['cart']['promo_discount_total'],

                'discount_total' =>
                    $state['cart']['discount_total'],

                'cart_total' =>
                    $state['cart']['cart_total'],

                'final_total' =>
                    $state['cart']['total'],

                /*
                |--------------------------------------------------------------------------
                | Promo
                |--------------------------------------------------------------------------
                */

                'promo' =>
                    $state['promo'],

                /*
                |--------------------------------------------------------------------------
                | Shipping
                |--------------------------------------------------------------------------
                */

                'shipping' =>
                    $state['shipping'],

                /*
                |--------------------------------------------------------------------------
                | User context
                |--------------------------------------------------------------------------
                */

                'client_info' =>
                    $clientInfo,

                'is_b2b' =>
                    $isB2B,

                /*
                |--------------------------------------------------------------------------
                | Language / menu
                |--------------------------------------------------------------------------
                */

                'lclang' =>
                    $lclang,

                'clang' =>
                    $clang,

                'menu' =>
                    $menu,
            ),
            true
        );

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    $response =
        array(
            'status' =>
                'ok',

            'html' =>
                $html,

            'lang' =>
                $lclang,

            /*
            |--------------------------------------------------------------------------
            | Unified state
            |--------------------------------------------------------------------------
            */

            'cart' =>
                $state['cart'],

            'promo' =>
                $state['promo'],

            'shipping' =>
                $state['shipping'],

            'empty' =>
                $state['empty'],

            /*
            |--------------------------------------------------------------------------
            | Legacy aliases
            |--------------------------------------------------------------------------
            */

            'total_items' =>
                $state['cart']['total_items'],

            'cart_total' =>
                number_format(
                    $state['cart']['cart_total'],
                    0,
                    '.',
                    ''
                ),

            'delivery_price' =>
                $state['cart']['delivery_price'],

            'total' =>
                number_format(
                    $state['cart']['total'],
                    0,
                    '.',
                    ''
                ),

            'bonuses' =>
                $state['cart']['bonus_total'],
        );

    return $this->output
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                $response,
                JSON_UNESCAPED_UNICODE
            )
        );
}

public function cart_calculator_test()
{
    $lang =
        $this->input->get('lang')
            ?: 'ru';

    $this->_define_constants(
        $lang
    );

    $this->_is_logged_in();

    $this->load->library(
        'CartCalculator'
    );

    $promoCode =
        $this->input->get(
            'promo'
        );

    $result =
        $this->cartcalculator
            ->calculate(
                $lang,
                $promoCode
            );

    return $this->output
        ->set_content_type(
            'application/json',
            'utf-8'
        )
        ->set_output(
            json_encode(
                $result,
                JSON_UNESCAPED_UNICODE
                | JSON_PRETTY_PRINT
            )
        );
}

    /* ======================================================================
     * ПОДПИСКА НА РАССЫЛКУ
     * ====================================================================== */
    /**
     * Регистрирует email в списке подписчиков.
     *
     * POST: subscribe, lang.
     * Для нового адреса сохраняет подписку и отправляет приветственное письмо.
     */
    public function subscription()
    {
        // Безопасное получение всех POST-полей через Input CodeIgniter.
        check_if_POST();
        foreach ($_POST as $index => $item) {
            $post[$index] = $this->input->post($index, TRUE);
        }
        // Проверка email на существующую подписку.
        if (!empty($post['subscribe'])) {
            $this->_define_constants(strtolower($post['lang']));
            $this->load->model('subscriptions_model');
            $subscribe = $this->subscriptions_model->get_front_subscriptions_email($post['subscribe']);

            if (!empty($subscribe)) {
            // Существующий адрес получает локализованное сообщение об ошибке.
                $return['info'] = SUBSCRIBE_ERROR_INFO;
            } else {
                $voucher = 'RD-' . rand(100000000, 999999999);
                // Сохранение нового подписчика и подготовка email-шаблона.
                $this->subscriptions_model->put(array('email' => $post['subscribe'], 'isShown' => 1));

                $this->load->library('parser');
                $parse = [
                    'SUBSCRIBE_EMAIL_SUBJECT' => SUBSCRIBE_EMAIL_SUBJECT,
                    'SUBSCRIBE_INFI' => SUBSCRIBE_INFI,
                    'email' => $post['subscribe'],
                ];
                $tx = $this->parser->parse('layouts/email/subscribe', $parse, true);

                // Отправка письма подписчику через SMTP-конфигурацию проекта.
                //            EMAIL TO SERVER
                $this->load->library('email');
                $config = config_smtp();
                $this->email->initialize($config);
                $this->email->from('noreply@' . $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
                $this->email->to($post['subscribe']);
                $this->email->subject(SUBSCRIBE_EMAIL_SUBJECT);
                $this->email->message($tx);
                $this->email->send();

                $return['info'] = SUBSCRIBE_SUCCEESS_INFO;
            }
        } else {
            $return['info'] = 'ERROR';
        }

        // JSON содержит текст результата для блока подписки на странице.
        echo json_encode($return);
    }

    /* ======================================================================
     * СОГЛАСИЕ НА COOKIES
     * ====================================================================== */
    /**
     * Сохраняет выбор пользователя по категориям cookie (Law 195/2024) в
     * cookie_consent — JSON {functional, analytics, marketing}. necessary
     * отдельно не хранится, всегда подразумевается true. Срок — 180 дней.
     */
    public function cookie_consent()
    {
        // Endpoint принимает только POST-запрос.
        check_if_POST();

        $value = array(
            'functional' => $this->input->post('functional') ? true : false,
            'analytics' => $this->input->post('analytics') ? true : false,
            'marketing' => $this->input->post('marketing') ? true : false,
        );

        $cookie = array(
            'name' => 'cookie_consent',
            'value' => json_encode($value),
            'expire' => 3600 * 24 * 180,
        );
        set_cookie($cookie);

        echo 1;
    }

    /* ======================================================================
     * ТЕСТОВАЯ ОТПРАВКА EMAIL
     * ====================================================================== */
    /**
     * Отправляет тестовое письмо с жёстко заданным HTML заказа.
     *
     * Метод содержит демонстрационные данные и фиксированного получателя.
     * Он выводит результат отправки или SMTP-отладку и не является обычным
     * JSON endpoint.
     */
    public function email()
    {
        // Полный HTML тестового письма хранится непосредственно в контроллере.
        $text = '<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>
        Email
    </title>
    <style type="text/css">
        @media only screen and (max-device-width: 480px) {
            #table-1, #table-2, #table-3, #table-4 {
                width: 100%;
            }

            #table-1-1, #table-2-2, #table-3-3, #table-4-4 {
                padding: 0px 15px;
            }
        }
    </style>
</head>
<body bgcolor="#EAEAEA" style="margin: 0px">
<table style="" border="0" width="600" cellspacing="0" cellpadding="0" align="center"
       bgcolor="#f0f0f0">
    <tbody>
    <tr>
        <td>
            <table style="" border="0" width="100%" cellspacing="0" cellpadding="0"
                   align="center" bgcolor="#f0f0f0">
                <tbody>
                <tr>
                    <td>
                        <table id="table-1-1" style="padding: 15px 40px;text-align: center"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#f7e7da">
                            <tbody>
                            <tr>
                                <td>
                                    <a href="https://vizaje-nica.com" class="head-email__logo">
                                        <img src="https://vizaje-nica.com/app/img/logo_v.png" alt="Logo">
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table id="table-1-2" style="padding: 15px 40px;text-align: center"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#ffffff">
                            <tbody>
                            <tr>
                                <td>
                                    <h3 style="font-weight: 400; font-size: 20px;  line-height: 1.3;  text-align: center;color: #383838;">
                                        Salut, <strong>Aisif</strong></h3>
                                    <p style="font-weight: 400; font-size: 14px; line-height: 16px;  text-align: center;  color: #383838;  margin-top: -12px;">
                                        Am primit de la dvs. o comanda <strong>#48128</strong>, pentru suma de
                                        <strong>538 MDL</strong> .</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table id="table-1-3" style="padding: 15px 40px;text-align: center"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#ffffff">
                            <tbody>
                            <tr>
                                <td>
                                    <p style="color: #383838;">Data comenzii: <strong>13 Февраль 2026</strong></p>
                                </td>
                                <td>
                                    <p style="font-weight: 700; color: #0BBE32;">Comanda trimisă cu succes</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table id="table-1-5" style="padding: 15px 40px;text-align: left"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#ffffff">
                            <tbody>
                            <tr>
                                <td>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Datele utilizatorului</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table id="table-1-6" style="padding: 15px 40px;text-align: left"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#ffffff">
                            <tbody>
                            <tr>
                                <td>
                                    <p style="font-weight: 400; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Nume destinatar:</p>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Aisif</p>
                                </td>
                                <td>
                                    <p style="font-weight: 400; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Adresa:</p>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        </p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p style="font-weight: 400; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Telefon:</p>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        27384</p>
                                </td>
                                <td>
                                    <p style="font-weight: 400; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Plata:</p>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Numerar la primire</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p style="font-weight: 400; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        E-mail:</p>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Schopenhauer07@gmail.com</p>
                                </td>
                                <td>
                                    <p style="font-weight: 400; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Data:</p>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        13 Февраль 2026</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table id="table-1-7" style="padding: 15px 40px;text-align: left"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#ffffff">
                            <tbody>
                            <tr>
                                <td>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Comanda Dvs.</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table id="table-1-8" style="padding: 15px 40px;text-align: left"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#ffffff">
                            <tbody>
                            <tr>
                                <td style="border-bottom: 1px solid;">Produse</td>
                                <td style="border-bottom: 1px solid;"></td>
                                <td style="border-bottom: 1px solid;">Preț</td>
                                <td style="border-bottom: 1px solid;">Cantitane</td>
                                <td style="border-bottom: 1px solid;">Total</td>
                            </tr>
                                <tr><td style="border-bottom: 1px solid;">
                                    <img src="https://vizaje-nica.com/public/products/f752185daada8e46e08640e0e05458d6.jpg" style="max-width: 60px">
                                </td><td style="border-bottom: 1px solid;">CLARINS Lip Comfort Oil - Limited Edition Масло для губ</td>
                                <td style="border-bottom: 1px solid;">488 MDL</td>
                                <td style="border-bottom: 1px solid;">1</td>
                                <td style="border-bottom: 1px solid;">488 MDL</td></tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table id="table-1-9" style="padding: 15px 40px;text-align: left"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#ffffff">
                            <tbody>
                            <tr>
                                <td>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Livrare</p>
                                </td>
                                <td>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        50 MDL</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        Total</p>
                                </td>
                                <td>
                                    <p style="font-weight: 700; font-size: 16px;  line-height: 1.2; color: #383838;">
                                        538 MDL</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table id="table-1-10" style="padding: 15px 40px;text-align: center"
                               border="0" width="100%" cellspacing="0" cellpadding="10" bgcolor="#f7e7da">
                            <tbody>
                            <tr>
                                <td>
                                    <a href="https://vizaje-nica.com" class="head-email__logo">
                                        <img src="https://vizaje-nica.com/app/img/logo_v.png" alt="Logo">
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>';

        // Инициализация SMTP и отправка тестового сообщения.
        $this->load->library('email');

        $config = config_smtp();


        $this->email->initialize($config);
        // $this->email->from('noreply@vizaje-nica.com');
        $this->email->to('bymaestro0794@yahoo.com');
        $this->email->subject('Comanda pe site ' . 'vizaje-nica.com');
        $this->email->message($text);
        // Диагностический ответ об успехе или ошибке отправки.
        if ($this->email->send()) {
            echo 'Email sent.';
        } else {
            echo '<pre>';
            print_r($this->email->print_debugger());
            echo '</pre>';
        }
    }
}