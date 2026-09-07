<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CartCalculator
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->library('cart');

        $this->CI->load->model(
            'products_model'
        );

        $this->CI->load->model(
            'promocodes_model'
        );

        $this->CI->load->model(
            'clients_model'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate
    |--------------------------------------------------------------------------
    */

    public function calculate(
        $lang = 'ru',
        $promoCode = null
    ) {
        $lang =
            strtolower(
                trim(
                    (string) $lang
                )
            );

        if (
            $lang !== 'ru'
            && $lang !== 'ro'
        ) {
            $lang = 'ru';
        }

        $clang =
            strtoupper(
                $lang
            );

        $cartItems =
            $this->CI->cart
                ->contents();

        $isB2B =
            !empty(
                $_SESSION['isb2b']
            );

        $client =
            $this->getClient();

        /*
        |--------------------------------------------------------------------------
        | Promo
        |--------------------------------------------------------------------------
        */

        if ($promoCode === null) {
            $promoCode =
                !empty(
                    $_SESSION['cart_promo']['code']
                )
                    ? $_SESSION['cart_promo']['code']
                    : null;
        }

        $promo =
            $this->preparePromo(
                $promoCode,
                $client
            );

        /*
        |--------------------------------------------------------------------------
        | Rows
        |--------------------------------------------------------------------------
        */

        $rows =
            array();

        $productsTotal = 0;
        $productDiscountTotal = 0;
        $currentProductsTotal = 0;

        foreach (
            $cartItems as $cartItem
        ) {
            $row =
                $this->prepareRow(
                    $cartItem,
                    $clang,
                    $isB2B
                );

            if (empty($row)) {
                continue;
            }

            $rows[] =
                $row;

            $productsTotal +=
                $row['original_total'];

            $productDiscountTotal +=
                $row['product_discount'];

            $currentProductsTotal +=
                $row['current_total'];
        }

        /*
        |--------------------------------------------------------------------------
        | Promo eligibility
        |--------------------------------------------------------------------------
        */

        $promo =
            $this->calculatePromo(
                $promo,
                $rows,
                $client,
                $isB2B
            );

        /*
        |--------------------------------------------------------------------------
        | Distribute promo discount
        |--------------------------------------------------------------------------
        */

        if (
            !empty($promo['applied'])
            && $promo['discount'] > 0
        ) {
            $rows =
                $this->distributePromoDiscount(
                    $rows,
                    $promo['discount']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Totals / bonuses
        |--------------------------------------------------------------------------
        */

        $promoDiscountTotal = 0;

        $bonusTotal = 0;

        $bonusEligibleTotal = 0;

        foreach (
            $rows as $key => $row
        ) {
            $promoDiscount =
                !empty(
                    $row['promo_discount']
                )
                    ? (float) $row['promo_discount']
                    : 0;

            $promoDiscountTotal +=
                $promoDiscount;

            /*
            |--------------------------------------------------------------------------
            | Final row total
            |--------------------------------------------------------------------------
            */

            $finalRowTotal =
                max(
                    0,
                    $row['current_total']
                    - $promoDiscount
                );

            $rows[$key]['final_total'] =
                $finalRowTotal;

            /*
            |--------------------------------------------------------------------------
            | Bonus eligibility
            |--------------------------------------------------------------------------
            |
            | Бонусы можно использовать только если:
            |
            | - B2C;
            | - пользователь авторизован;
            | - у строки нет обычной скидки;
            | - на строку не применился promo.
            |
            */

            $bonusEligible =
                !$isB2B
                && !empty($client)
                && $row['product_discount'] <= 0
                && $promoDiscount <= 0;

            $rows[$key]['bonus_eligible'] =
                $bonusEligible;

            /*
            |--------------------------------------------------------------------------
            | Eligible subtotal for write-off
            |--------------------------------------------------------------------------
            */

            if ($bonusEligible) {
                $bonusEligibleTotal +=
                    $finalRowTotal;
            }

            /*
            |--------------------------------------------------------------------------
            | Bonus accumulation
            |--------------------------------------------------------------------------
            |
            | Начисление дополнительно требует процент клиента.
            |
            */

            $rows[$key]['bonus'] = 0;

            if (
                $bonusEligible
                && !empty($client->discount)
            ) {
                $rowBonus =
                    $finalRowTotal
                    * (
                        (float) $client->discount
                        / 100
                    );

                $rows[$key]['bonus'] =
                    $rowBonus;

                $bonusTotal +=
                    $rowBonus;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Bonus write-off
        |--------------------------------------------------------------------------
        */

        $bonusBalance =
            !empty($client)
            && !empty($client->Bonus)
                ? (float) $client->Bonus
                : 0;

        /*
        | По бизнес-правилу после списания
        | на eligible товарах должен остаться минимум 1 MDL.
        */

        $bonusEligibleLimit =
            max(
                0,
                $bonusEligibleTotal - 1
            );

        $bonusMaxWriteOff =
            min(
                $bonusBalance,
                $bonusEligibleLimit
            );

        $bonusMaxWriteOff =
            floor(
                max(
                    0,
                    $bonusMaxWriteOff
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        $discountTotal =
            $productDiscountTotal
            + $promoDiscountTotal;

        $cartTotal =
            max(
                0,
                $currentProductsTotal
                - $promoDiscountTotal
            );

        /*
            |--------------------------------------------------------------------------
            | Express delivery availability
            |--------------------------------------------------------------------------
            |
            | Даже если option скрыт на frontend,
            | delivery=3 нельзя принимать вручную.
            |
            */

            if (
                (int) $post['delivery'] === 3
                && !$expressDeliveryEnabled
            ) {
                $_SESSION['checkout_error'] =
                    $this->lclang === 'ro'
                        ? 'Livrarea expres nu este disponibilă momentan.'
                        : 'Срочная доставка сейчас недоступна.';

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
        | Delivery
        |--------------------------------------------------------------------------
        */

        $shippingThreshold =
            defined('FREE_SHIPPING_VALUE')
                ? (float) FREE_SHIPPING_VALUE
                : 0;

        $shippingPrice =
            defined('SHIPPING_PRICE')
                ? (float) SHIPPING_PRICE
                : 0;

        $empty =
            empty($rows);

        if ($empty) {
            $deliveryPrice = 0;
            $shippingRemaining = 0;
            $shippingProgress = 0;
            $isFreeShipping = false;
        } else {
            $isFreeShipping =
                $shippingThreshold <= 0
                || $cartTotal >= $shippingThreshold;

            $deliveryPrice =
                $isFreeShipping
                    ? 0
                    : $shippingPrice;

            $shippingRemaining =
                max(
                    0,
                    $shippingThreshold
                    - $cartTotal
                );

            $shippingProgress =
                $shippingThreshold > 0
                    ? min(
                        100,
                        (
                            $cartTotal
                            / $shippingThreshold
                        ) * 100
                    )
                    : 100;
        }

        return array(
            'rows' =>
                $rows,

            'cart' => array(
                'total_items' =>
                    (int) $this->CI->cart
                        ->total_items(),

                /*
                | Цена до любых скидок.
                */
                'products_total' =>
                    $productsTotal,

                /*
                | Обычная скидка товаров.
                */
                'product_discount_total' =>
                    $productDiscountTotal,

                /*
                | Скидка promo.
                */
                'promo_discount_total' =>
                    $promoDiscountTotal,

                /*
                | Обычная + promo.
                */
                'discount_total' =>
                    $discountTotal,

                /*
                | Товары после всех скидок,
                | но без доставки.
                */
                'cart_total' =>
                    $cartTotal,

                'delivery_price' =>
                    $deliveryPrice,

                'total' =>
                    $cartTotal
                    + $deliveryPrice,

                /*
                | Сколько бонусов будет начислено,
                | если клиент не использует списание.
                */
                'bonus_total' => round($bonusTotal, 2),

                /*
                | Стоимость товаров,
                | на которые разрешено списывать бонусы.
                */
                'bonus_eligible_total' =>
                    $bonusEligibleTotal,

                /*
                | Текущий доступный баланс клиента.
                */
                'bonus_balance' =>
                    $bonusBalance,

                /*
                | Максимально допустимое списание
                | с учётом eligible товаров и баланса.
                */
                'bonus_max_writeoff' =>
                    $bonusMaxWriteOff,
            ),

            'promo' =>
                $promo,

            'shipping' => array(
                'threshold' =>
                    $shippingThreshold,

                'price' =>
                    $shippingPrice,

                'remaining' =>
                    $shippingRemaining,

                'progress' =>
                    $shippingProgress,

                'is_free' =>
                    $isFreeShipping,
            ),

            'empty' =>
                $empty,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare cart row
    |--------------------------------------------------------------------------
    */

    private function prepareRow(
        array $cartItem,
        $clang,
        $isB2B
    ) {
        $product =
            $this->CI->products_model
                ->product_item_cart(
                    $clang,
                    $cartItem['rowid']
                );

        if (empty($product)) {
            return null;
        }

        $variable = null;

        if (!empty($cartItem['options'])) {
            $variable =
                $this->CI->products_model
                    ->get_product_variable_by_id(
                        $clang,
                        $cartItem['options'],
                        $cartItem['id']
                    );
        }

        $qty =
            max(
                1,
                (int) $cartItem['qty']
            );

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        if ($isB2B) {
            if (!empty($variable)) {
                $unitOriginalPrice =
                    (float) $variable->priceWH;

                $unitCurrentPrice =
                    $unitOriginalPrice;

                $maxQuantity =
                    (int) $variable->qtyWH;
            } else {
                $unitOriginalPrice =
                    (float) $product->priceWH;

                $unitCurrentPrice =
                    $unitOriginalPrice;

                $maxQuantity =
                    (int) $product->on_stockWH;
            }
        } else {
            if (!empty($variable)) {
                $unitOriginalPrice =
                    (float) $variable->price;

                $unitCurrentPrice =
                    !empty(
                        $variable->discount_price
                    )
                        ? (float) $variable->discount_price
                        : $unitOriginalPrice;

                $maxQuantity =
                    (int) $variable->qty;
            } else {
                $unitOriginalPrice =
                    (float) $product->price;

                $unitCurrentPrice =
                    !empty(
                        $product->discount_price
                    )
                        ? (float) $product->discount_price
                        : $unitOriginalPrice;

                $maxQuantity =
                    (int) $product->on_stock;
            }
        }

        $originalTotal =
            $unitOriginalPrice
            * $qty;

        $currentTotal =
            $unitCurrentPrice
            * $qty;

        $productDiscount =
            max(
                0,
                $originalTotal
                - $currentTotal
            );

        return array(
            'row_id' =>
                $cartItem['rowid'],

            'product_id' =>
                (int) $cartItem['id'],

            'variable_id' =>
                !empty($cartItem['options'])
                    ? (int) $cartItem['options']
                    : 0,

            'brand_id' =>
                !empty($product->brand_id)
                    ? (int) $product->brand_id
                    : 0,
                    
            'brand_title' =>
                !empty($product->brand_title)
                    ? trim((string) $product->brand_title)
                    : '',

            'quantity' =>
                $qty,

            'max_quantity' =>
                $maxQuantity,

            'unit_original_price' =>
                $unitOriginalPrice,

            'unit_price' =>
                $unitCurrentPrice,

            'original_total' =>
                $originalTotal,

            'current_total' =>
                $currentTotal,

            'product_discount' =>
                $productDiscount,

            'has_product_discount' =>
                $productDiscount > 0,

            /*
            | Заполним ниже после promo validation.
            */
            'promo_eligible' =>
                false,

            'promo_discount' =>
                0,

            /*
            |--------------------------------------------------------------------------
            | Bonus
            |--------------------------------------------------------------------------
            */

            'bonus_eligible' =>
                false,

            'bonus' =>
                0,

            'final_total' =>
                $currentTotal,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare promo
    |--------------------------------------------------------------------------
    */

    private function preparePromo(
        $promoCode,
        $client
    ) {
        $state =
            $this->emptyPromoState();

        $promoCode =
            strtoupper(
                trim(
                    (string) $promoCode
                )
            );

        if ($promoCode === '') {
            return $state;
        }

        $state['code'] =
            $promoCode;

        /*
        |--------------------------------------------------------------------------
        | Auth
        |--------------------------------------------------------------------------
        */

        if (empty($client)) {
            $state['code'] =
                $promoCode;

            $state['reason'] =
                'PROMO_NOT_AUTHENTICATED';

            return $state;
        }

        /*
        |--------------------------------------------------------------------------
        | Promo
        |--------------------------------------------------------------------------
        */

        $promo =
            $this->CI
                ->promocodes_model
                ->findByCode(
                    $promoCode
                );

        if (empty($promo)) {
            $state['reason'] =
                'PROMO_NOT_FOUND';

            return $state;
        }

        $state['id'] =
            (int) $promo->id;

        $state['code'] =
            $promo->code;

        $state['promo'] =
            $promo;

        return $state;
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate promo
    |--------------------------------------------------------------------------
    */

    private function calculatePromo(
        array $state,
        array &$rows,
        $client,
        $isB2B
    ) {
        if (
            empty($state['promo'])
        ) {
            return $this->removeInternalPromo(
                $state
            );
        }

        $promo =
            $state['promo'];

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $promo->status !== 'active'
        ) {
            $state['reason'] =
                'PROMO_NOT_FOUND';

            return $this->removeInternalPromo(
                $state
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Dates
        |--------------------------------------------------------------------------
        */

        $now =
            time();

        if (
            !empty($promo->starts_at)
            && strtotime(
                $promo->starts_at
            ) > $now
        ) {
            $state['reason'] =
                'PROMO_NOT_STARTED';

            return $this->removeInternalPromo(
                $state
            );
        }

        if (
            !empty($promo->ends_at)
            && strtotime(
                $promo->ends_at
            ) < $now
        ) {
            $state['reason'] =
                'PROMO_EXPIRED';

            return $this->removeInternalPromo(
                $state
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Usage limits
        |--------------------------------------------------------------------------
        */

        if (
            $promo->usage_limit !== null
        ) {
            $used =
                $this->CI
                    ->promocodes_model
                    ->countUsages(
                        $promo->id
                    );

            if (
                $used >=
                (int) $promo->usage_limit
            ) {
                $state['reason'] =
                    'PROMO_USAGE_LIMIT_REACHED';

                return $this->removeInternalPromo(
                    $state
                );
            }
        }

        if (
            $promo->per_user_limit !== null
        ) {
            $clientUsed =
                $this->CI
                    ->promocodes_model
                    ->countClientUsages(
                        $promo->id,
                        $client->id
                    );

            if (
                $clientUsed >=
                (int) $promo->per_user_limit
            ) {
                $state['reason'] =
                    'PROMO_ALREADY_USED';

                return $this->removeInternalPromo(
                    $state
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Brand rules
        |--------------------------------------------------------------------------
        */

        $selectedBrands =
            array_map(
                'intval',
                $this->CI
                    ->promocodes_model
                    ->getBrandIds(
                        $promo->id
                    )
            );

        $excludedBrands =
            array_map(
                'intval',
                $this->CI
                    ->promocodes_model
                    ->getExcludedBrandIds(
                        $promo->id
                    )
            );

        /*
        |--------------------------------------------------------------------------
        | Eligible rows
        |--------------------------------------------------------------------------
        */

        $eligibleSubtotal = 0;
        
        $excludedCartBrands = array();

        foreach (
            $rows as $key => $row
        ) {
            $eligible = true;

            /*
            |--------------------------------------------------------------------------
            | Existing product discount
            |--------------------------------------------------------------------------
            */

            if (
                empty($promo->allow_discounted)
                && !empty(
                    $row['has_product_discount']
                )
            ) {
                $eligible = false;
            }

            /*
            |--------------------------------------------------------------------------
            | Brand
            |--------------------------------------------------------------------------
            */

            if ($eligible) {
                $blockedByBrand = false;
            
                if (
                    $promo->brand_scope
                    === 'selected'
                ) {
                    if (
                        !in_array(
                            (int) $row['brand_id'],
                            $selectedBrands,
                            true
                        )
                    ) {
                        $eligible = false;
                        $blockedByBrand = true;
                    }
                } else {
                    if (
                        in_array(
                            (int) $row['brand_id'],
                            $excludedBrands,
                            true
                        )
                    ) {
                        $eligible = false;
                        $blockedByBrand = true;
                    }
                }
            
                /*
                |--------------------------------------------------------------------------
                | Brands from current cart excluded by this promo
                |--------------------------------------------------------------------------
                */
            
                if (
                    $blockedByBrand
                    && !empty($row['brand_id'])
                    && !empty($row['brand_title'])
                ) {
                    $brandId =
                        (int) $row['brand_id'];
            
                    if (
                        !isset(
                            $excludedCartBrands[$brandId]
                        )
                    ) {
                        $excludedCartBrands[$brandId] =
                            array(
                                'id' =>
                                    $brandId,
            
                                'title' =>
                                    (string) $row['brand_title'],
                            );
                    }
                }
            }

            $rows[$key]['promo_eligible'] =
                $eligible;

            if ($eligible) {
                $eligibleSubtotal +=
                    $row['current_total'];
            }
        }
        
        

        $state['eligible_subtotal'] =
            $eligibleSubtotal;
        
        $state['excluded_cart_brands'] =
            array_values(
                $excludedCartBrands
            );

        /*
        |--------------------------------------------------------------------------
        | Nothing eligible
        |--------------------------------------------------------------------------
        */

        if ($eligibleSubtotal <= 0) {
            $state['reason'] =
                'PROMO_NOT_APPLICABLE';

            return $this->removeInternalPromo(
                $state
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum amount
        |--------------------------------------------------------------------------
        */

        if (
            $promo->min_amount !== null
            && $eligibleSubtotal
                < (float) $promo->min_amount
        ) {
            $state['reason'] =
                'PROMO_MINIMUM_AMOUNT';

            $state['minimum_amount'] =
                (float) $promo->min_amount;

            $state['minimum_remaining'] =
                max(
                    0,
                    (float) $promo->min_amount
                    - $eligibleSubtotal
                );

            return $this->removeInternalPromo(
                $state
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Discount
        |--------------------------------------------------------------------------
        */

        if (
            $promo->type
            === 'percentage'
        ) {
            $discount =
                $eligibleSubtotal
                * (
                    (float) $promo->value
                    / 100
                );

            if (
                $promo->max_discount !== null
                && (float) $promo->max_discount > 0
            ) {
                $discount =
                    min(
                        $discount,
                        (float) $promo->max_discount
                    );
            }
        } else {
            $discount =
                min(
                    (float) $promo->value,
                    $eligibleSubtotal
                );
        }

        $discount =
            round(
                max(
                    0,
                    $discount
                ),
                2
            );

        if ($discount <= 0) {
            $state['reason'] =
                'PROMO_NOT_APPLICABLE';

            return $this->removeInternalPromo(
                $state
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Applied
        |--------------------------------------------------------------------------
        */

        $state['applied'] =
            true;

        $state['valid'] =
            true;

        $state['reason'] =
            null;

        $state['discount'] =
            $discount;

        $state['type'] =
            $promo->type;

        $state['value'] =
            (float) $promo->value;

        return $this->removeInternalPromo(
            $state
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Distribute discount
    |--------------------------------------------------------------------------
    |
    | Для percentage это естественно.
    |
    | Для fixed скидку распределяем пропорционально между eligible rows.
    | Это важно для:
    |
    | - orders_products.promo_discount;
    | - bonus rules;
    | - будущих возвратов;
    | - корректного snapshot заказа.
    |
    */

    private function distributePromoDiscount(
        array $rows,
        $discount
    ) {
        $discount =
            round(
                (float) $discount,
                2
            );

        if ($discount <= 0) {
            return $rows;
        }

        $eligibleSubtotal = 0;
        $eligibleKeys = array();

        foreach (
            $rows as $key => $row
        ) {
            if (
                empty(
                    $row['promo_eligible']
                )
            ) {
                continue;
            }

            $eligibleSubtotal +=
                $row['current_total'];

            $eligibleKeys[] =
                $key;
        }

        if (
            $eligibleSubtotal <= 0
            || empty($eligibleKeys)
        ) {
            return $rows;
        }

        $distributed = 0;

        $lastKey =
            end(
                $eligibleKeys
            );

        foreach (
            $eligibleKeys as $key
        ) {
            if ($key === $lastKey) {
                $rowDiscount =
                    $discount
                    - $distributed;
            } else {
                $share =
                    $rows[$key]['current_total']
                    / $eligibleSubtotal;

                $rowDiscount =
                    round(
                        $discount
                        * $share,
                        2
                    );

                $distributed +=
                    $rowDiscount;
            }

            $rowDiscount =
                min(
                    $rowDiscount,
                    $rows[$key]['current_total']
                );

            $rows[$key]['promo_discount'] =
                max(
                    0,
                    $rowDiscount
                );
        }

        return $rows;
    }

    /*
    |--------------------------------------------------------------------------
    | Client
    |--------------------------------------------------------------------------
    */

    private function getClient()
    {
        if (
            empty($_SESSION['user_id'])
        ) {
            return null;
        }

        $client =
            $this->CI
                ->clients_model
                ->get_client_login(
                    (int) $_SESSION['user_id']
                );

        if (empty($client)) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Loyalty percentage
        |--------------------------------------------------------------------------
        |
        | Legacy frontend использует 10% по умолчанию,
        | если discount клиента отсутствует или равен 0.
        |
        | CartCalculator должен использовать тот же контракт,
        | иначе карточки показывают бонусы, а корзина считает 0.
        |
        */

        if (
            empty($client->discount)
        ) {
            $client->discount = 10;
        }

        return $client;
    }

    /*
    |--------------------------------------------------------------------------
    | Promo state
    |--------------------------------------------------------------------------
    */

    private function emptyPromoState()
    {
        return array(
            'applied' =>
                false,

            'valid' =>
                false,

            'id' =>
                null,

            'code' =>
                null,

            'type' =>
                null,

            'value' =>
                0,

            'discount' =>
                0,

            'eligible_subtotal' =>
                0,
                
            'excluded_cart_brands' =>
                array(),

            'minimum_amount' =>
                null,

            'minimum_remaining' =>
                0,

            'reason' =>
                null,

            /*
            | Только для внутренних вычислений.
            | Перед возвратом удаляется.
            */
            'promo' =>
                null,
        );
    }

    private function removeInternalPromo(
        array $state
    ) {
        unset(
            $state['promo']
        );

        return $state;
    }
}