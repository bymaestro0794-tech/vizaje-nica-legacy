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

    public static function getCartVariableId($options)
    {
        if (is_array($options)) {
            if (!empty($options['sku_id'])) {
                return (int) $options['sku_id'];
            }

            if (!empty($options['variant_id'])) {
                return (int) $options['variant_id'];
            }

            return 0;
        }

        return !empty($options) ? (int) $options : 0;
    }

    public static function getRouletteGiftId($options)
    {
        return is_array($options) && !empty($options['roulette_gift_id'])
            ? (int) $options['roulette_gift_id']
            : 0;
    }

    public static function getPromotionGiftId($options)
    {
        return is_array($options) && !empty($options['gift_promotion_id'])
            ? (int) $options['gift_promotion_id']
            : 0;
    }

    public static function isGift($options)
    {
        return self::getRouletteGiftId($options) > 0
            || self::getPromotionGiftId($options) > 0;
    }

    private function promotionGiftOptions($options, $promotionId, $variantId = 0)
    {
        $giftOptions = is_array($options) ? $options : array();

        if (!is_array($options) && !empty($options)) {
            $giftOptions['sku_id'] = (int) $options;
        }

        if ((int) $variantId > 0) {
            $giftOptions['sku_id'] = (int) $variantId;
        }

        $giftOptions['gift_promotion_id'] = (int) $promotionId;

        return $giftOptions;
    }

    public function promotePaidRowToGift(array $cartItem, $product, $promotionId, $variantId = 0)
    {
        $rowId = (string) ($cartItem['rowid'] ?? '');
        $quantity = max(1, (int) ($cartItem['qty'] ?? 1));

        if ($rowId === '') {
            return false;
        }

        $giftOptions = $this->promotionGiftOptions(
            $cartItem['options'] ?? null,
            $promotionId,
            $variantId
        );

        if ($quantity === 1) {
            return $this->CI->cart->update(array(
                'rowid' => $rowId,
                'qty' => 1,
                'price' => 1,
                'options' => $giftOptions,
            ));
        }

        // If several units were already bought, only one becomes the gift.
        // The remaining units stay as a normal paid row.
        $paidRowUpdated = $this->CI->cart->update(array(
            'rowid' => $rowId,
            'qty' => $quantity - 1,
        ));

        if (!$paidRowUpdated) {
            return false;
        }

        $productName = !empty($product->titleRO)
            ? $product->titleRO
            : $product->titleRU;

        $giftRowId = $this->CI->cart->insert(array(
            'id' => (int) $cartItem['id'],
            'name' => str_replace('=', '', transliteration($productName)),
            'price' => 1,
            'qty' => 1,
            'options' => $giftOptions,
        ));

        if (empty($giftRowId)) {
            $this->CI->cart->update(array(
                'rowid' => $rowId,
                'qty' => $quantity,
            ));

            return false;
        }

        return true;
    }

    public function syncRouletteGiftCart($language = 'ru')
    {
        $language = strtolower(trim((string) $language));
        $language = in_array($language, array('ru', 'ro'), true) ? $language : 'ru';

        $cartItems = $this->CI->cart->contents();
        $giftRows = array();
        $hasPaidItem = false;

        foreach ($cartItems as $cartItem) {
            $giftId = self::getRouletteGiftId($cartItem['options'] ?? null);

            if ($giftId > 0) {
                $giftRows[] = $cartItem;
            } elseif (self::getPromotionGiftId($cartItem['options'] ?? null) > 0) {
                // Условный подарок не считается платным товаром для рулетки.
            } else {
                $hasPaidItem = true;
            }
        }

        $targetGift = null;
        $choice = !empty($_SESSION['cart_discount_choice'])
            ? (string) $_SESSION['cart_discount_choice']
            : '';

        if ($choice === 'roulette' && $hasPaidItem) {
            $client = $this->getClient();

            if (!empty($client->id)) {
                $this->CI->load->model('roulette_game_model');
                $this->CI->load->model('gifts_model');

                try {
                    if ($this->CI->roulette_game_model->isReady()) {
                        $state = $this->CI->roulette_game_model->getState(
                            (int) $client->id,
                            $language
                        );
						$prize = !empty($state['visible']) && !empty($state['active_prize']) && is_array($state['active_prize'])
                            ? $state['active_prize']
                            : array();
                        $giftId = !empty($prize['gift_id']) && $prize['type'] === 'gift'
                            ? (int) $prize['gift_id']
                            : 0;

                        if ($giftId > 0) {
                            $targetGift = $this->CI->gifts_model->find_enabled($giftId);
                        }
                    }
                } catch (Throwable $exception) {
                    log_message('error', 'Could not synchronize roulette gift cart item: ' . $exception->getMessage());
                }
            }
        }

        $targetGiftId = !empty($targetGift) ? (int) $targetGift->id : 0;
        $keptGiftRow = null;

        foreach ($giftRows as $giftRow) {
            $giftId = self::getRouletteGiftId($giftRow['options'] ?? null);

            if ($targetGiftId <= 0 || $giftId !== $targetGiftId || $keptGiftRow !== null) {
                $this->CI->cart->remove($giftRow['rowid']);
                continue;
            }

            $keptGiftRow = $giftRow;
            if ((int) $giftRow['qty'] !== 1 || (float) $giftRow['price'] !== 1.0) {
                $this->CI->cart->update(array(
                    'rowid' => $giftRow['rowid'],
                    'qty' => 1,
                    'price' => 1,
                ));
            }
        }

        if ($targetGiftId <= 0 || $keptGiftRow !== null || empty($targetGift->product_id)) {
            return;
        }

        $product = $this->CI->products_model->findFirstCart((int) $targetGift->product_id);
        if (empty($product)) {
            return;
        }

        $variantId = !empty($targetGift->variant_id) ? (int) $targetGift->variant_id : 0;
        if ($variantId > 0) {
            $variant = $this->CI->products_model->get_product_variable_by_id(
                strtoupper($language),
                $variantId,
                (int) $targetGift->product_id
            );
            if (empty($variant)) {
                return;
            }
        }

        $productName = !empty($product->titleRO) ? $product->titleRO : $product->titleRU;
        $this->CI->cart->insert(array(
            'id' => (int) $targetGift->product_id,
            'name' => str_replace('=', '', transliteration($productName)),
            'price' => 1,
            'qty' => 1,
            'options' => array(
                'sku_id' => $variantId,
                'roulette_gift_id' => $targetGiftId,
            ),
        ));
    }

    /**
     * Synchronizes gifts earned by an active brand/quantity promotion.
     * A fixed gift variant is inserted immediately. An unbound variant
     * product is returned as a pending selection for the frontend dialog.
     */
    public function syncGiftPromotions($language = 'ru')
    {
        $language = strtolower(trim((string) $language));
        $language = in_array($language, array('ru', 'ro'), true) ? $language : 'ru';

        if (!$this->CI->db->table_exists('gift_promotions')
            || !$this->CI->db->table_exists('gift_promotion_brands')
            || !$this->CI->db->table_exists('gifts')
        ) {
            return array();
        }

        $this->CI->load->model('gift_promotions_model');
        $this->CI->load->model('gifts_model');
        $promotions = $this->CI->gift_promotions_model->get_active_for_frontend($language);
        $activeIds = array();
        $pending = array();
        $cartItems = $this->CI->cart->contents();

        foreach ($promotions as $promotion) {
            // A previous promotion in the same pass may have converted a
            // paid row into a gift, so always inspect the current cart.
            $cartItems = $this->CI->cart->contents();
            $promotionId = (int) $promotion->id;
            $activeIds[$promotionId] = true;
            $brandIds = array_map('intval', (array) $promotion->brand_ids);
            $qualifyingQuantity = 0;
            $existingRows = array();

            foreach ($cartItems as $cartItem) {
                $promotionGiftId = self::getPromotionGiftId($cartItem['options'] ?? null);
                if ($promotionGiftId === $promotionId) {
                    $existingRows[] = $cartItem;
                    continue;
                }
                if (self::isGift($cartItem['options'] ?? null)) {
                    continue;
                }

                $product = $this->CI->products_model->findFirstCart((int) $cartItem['id']);
                if (!empty($product) && in_array((int) ($product->brand_id ?? 0), $brandIds, true)) {
                    $qualifyingQuantity += max(1, (int) $cartItem['qty']);
                }
            }

            $gift = $this->CI->gifts_model->find_enabled((int) $promotion->gift_id);
            $giftProductId = !empty($gift->product_id) ? (int) $gift->product_id : 0;
            $giftProduct = $giftProductId > 0
                ? $this->CI->products_model->findFirstCart($giftProductId)
                : null;
            $eligible = $qualifyingQuantity >= max(1, (int) $promotion->condition_qty)
                && !empty($giftProduct);

            if (!$eligible) {
                foreach ($existingRows as $existingRow) {
                    $this->CI->cart->remove($existingRow['rowid']);
                }
                continue;
            }

            $fixedVariantId = !empty($gift->variant_id) ? (int) $gift->variant_id : 0;
            $giftVariants = $this->CI->products_model->get_product_variable(
                strtoupper($language),
                $giftProductId,
                true
            );
            $hasVariants = !empty($giftVariants);
            $canAddDirectly = $fixedVariantId > 0 || !$hasVariants;

            // If the customer already added the future gift as a normal
            // product, reuse that row instead of creating a duplicate.
            // For a variant gift the already selected variant is a valid
            // choice, so no second variant dialog is needed.
            if (empty($existingRows)) {
                $paidGiftCandidate = null;
                $candidateVariantId = $fixedVariantId;

                foreach ($cartItems as $cartItem) {
                    if (
                        self::isGift($cartItem['options'] ?? null)
                        || (int) ($cartItem['id'] ?? 0) !== $giftProductId
                    ) {
                        continue;
                    }

                    $rowVariantId = self::getCartVariableId($cartItem['options'] ?? null);

                    if ($fixedVariantId > 0 && $rowVariantId !== $fixedVariantId) {
                        continue;
                    }

                    if ($fixedVariantId <= 0 && $hasVariants) {
                        $variantExists = false;

                        foreach ($giftVariants as $giftVariant) {
                            if ((int) ($giftVariant->id ?? 0) === $rowVariantId) {
                                $variantExists = true;
                                break;
                            }
                        }

                        if (!$variantExists) {
                            continue;
                        }

                        $candidateVariantId = $rowVariantId;
                    } elseif ($fixedVariantId <= 0 && $rowVariantId !== 0) {
                        continue;
                    }

                    $paidGiftCandidate = $cartItem;
                    break;
                }

                if (
                    $paidGiftCandidate !== null
                    && $this->promotePaidRowToGift(
                        $paidGiftCandidate,
                        $giftProduct,
                        $promotionId,
                        $candidateVariantId
                    )
                ) {
                    continue;
                }
            }

            if (!$canAddDirectly) {
                // A selected variant from the protected gift endpoint is
                // already a valid choice and must remain in the cart.
                $selectedVariantId = !empty($existingRows)
                    ? self::getCartVariableId($existingRows[0]['options'] ?? null)
                    : 0;
                $selectedVariant = $selectedVariantId > 0
                    ? $this->CI->products_model->get_product_variable_by_id(
                        strtoupper($language),
                        $selectedVariantId,
                        $giftProductId
                    )
                    : null;
                if (!empty($selectedVariant) && (int) ($selectedVariant->qty ?? 0) > 0) {
                    $keptSelected = null;
                    foreach ($existingRows as $existingRow) {
                        $rowVariantId = self::getCartVariableId($existingRow['options'] ?? null);
                        if ($keptSelected !== null || $rowVariantId !== $selectedVariantId) {
                            $this->CI->cart->remove($existingRow['rowid']);
                            continue;
                        }
                        $keptSelected = $existingRow;
                        if ((int) $existingRow['qty'] !== 1 || (float) $existingRow['price'] !== 1.0) {
                            $this->CI->cart->update(array('rowid' => $existingRow['rowid'], 'qty' => 1, 'price' => 1));
                        }
                    }
                    if ($keptSelected !== null) {
                        continue;
                    }
                }
                foreach ($existingRows as $existingRow) {
                    $this->CI->cart->remove($existingRow['rowid']);
                }
                $pending[] = array(
                    'promotion_id' => $promotionId,
                    'title' => (string) $promotion->title,
                    'description' => (string) $promotion->description,
                    'gift_id' => (int) $promotion->gift_id,
                    'product_id' => $giftProductId,
                    'fixed_variant_id' => 0,
                    'offer_uri' => !empty($promotion->offer_uri) ? (string) $promotion->offer_uri : null,
                );
                continue;
            }

            $targetVariantId = $fixedVariantId;
            if ($targetVariantId > 0) {
                $variant = $this->CI->products_model->get_product_variable_by_id(
                    strtoupper($language),
                    $targetVariantId,
                    $giftProductId
                );
                if (empty($variant) || (int) ($variant->qty ?? 0) <= 0) {
                    foreach ($existingRows as $existingRow) {
                        $this->CI->cart->remove($existingRow['rowid']);
                    }
                    continue;
                }
            } elseif ((int) ($giftProduct->on_stock ?? 0) <= 0) {
                foreach ($existingRows as $existingRow) {
                    $this->CI->cart->remove($existingRow['rowid']);
                }
                continue;
            }

            $keptRow = null;
            foreach ($existingRows as $existingRow) {
                $rowVariantId = self::getCartVariableId($existingRow['options'] ?? null);
                if ($keptRow !== null || (int) $existingRow['id'] !== $giftProductId || $rowVariantId !== $targetVariantId) {
                    $this->CI->cart->remove($existingRow['rowid']);
                    continue;
                }
                $keptRow = $existingRow;
                if ((int) $existingRow['qty'] !== 1 || (float) $existingRow['price'] !== 1.0) {
                    $this->CI->cart->update(array('rowid' => $existingRow['rowid'], 'qty' => 1, 'price' => 1));
                }
            }

            if ($keptRow !== null) {
                continue;
            }

            $productName = !empty($giftProduct->titleRO) ? $giftProduct->titleRO : $giftProduct->titleRU;
            $this->CI->cart->insert(array(
                'id' => $giftProductId,
                'name' => str_replace('=', '', transliteration($productName)),
                'price' => 1,
                'qty' => 1,
                'options' => array(
                    'sku_id' => $targetVariantId,
                    'gift_promotion_id' => $promotionId,
                ),
            ));
        }

        foreach ($this->CI->cart->contents() as $cartItem) {
            $promotionGiftId = self::getPromotionGiftId($cartItem['options'] ?? null);
            if ($promotionGiftId > 0 && empty($activeIds[$promotionGiftId])) {
                $this->CI->cart->remove($cartItem['rowid']);
            }
        }

        return $pending;
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate
    |--------------------------------------------------------------------------
    */

    public function calculate(
        $lang = 'ru',
        $promoCode = null,
        $discountChoice = null
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

        $this->syncRouletteGiftCart($lang);
        $giftPromotions = $this->syncGiftPromotions($lang);
        $cartItems =
            $this->CI->cart
                ->contents();

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

        $coupon =
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

        $couponRows = $rows;
        $coupon =
            $this->calculatePromo(
                $coupon,
                $couponRows,
                $client,
                $isB2B
            );
        if (!empty($coupon['applied']) && $coupon['discount'] > 0) {
            $couponRows = $this->distributePromoDiscount(
                $couponRows,
                $coupon['discount']
            );
        }

        $roulette = $this->calculateRouletteOption(
            $client,
            $lang,
            $rows
        );

        $requestedChoice = $discountChoice;
        if ($requestedChoice === null && !empty($_SESSION['cart_discount_choice'])) {
            $requestedChoice = (string) $_SESSION['cart_discount_choice'];
        }

        if (!in_array($requestedChoice, array('promo', 'roulette'), true)) {
            $requestedChoice = null;
        }

        if ($requestedChoice === null) {
            $requestedChoice = !empty($coupon['applied'])
                ? 'promo'
                : (!empty($roulette['available']) ? 'roulette' : 'none');
        }

        if ($requestedChoice === 'promo' && empty($coupon['applied'])) {
            $requestedChoice = 'none';
        } elseif ($requestedChoice === 'roulette' && empty($roulette['available'])) {
            $requestedChoice = 'none';
        }

        $selectedDiscount = array(
            'source' => 'none',
            'code' => null,
            'label' => null,
            'discount' => 0,
            'expires_at' => null,
        );

        if ($requestedChoice === 'promo') {
            $rows = $couponRows;
            $selectedDiscount = array(
                'source' => 'promo',
                'code' => $coupon['code'],
                'label' => $coupon['code'],
                'discount' => (float) $coupon['discount'],
                'expires_at' => null,
            );
        } elseif ($requestedChoice === 'roulette') {
            $rows = $roulette['rows'];
            $selectedDiscount = array(
                'source' => 'roulette',
                'code' => $roulette['code'],
                'label' => $roulette['title'],
                'discount' => (float) $roulette['discount'],
                'expires_at' => $roulette['prize']['expires_at'],
            );
        } else {
            $rows = $this->resetRowDiscounts($rows);
        }

        $couponOption = array(
            'available' => !empty($coupon['applied']),
            'code' => $coupon['code'],
            'discount' => (float) $coupon['discount'],
            'reason' => $coupon['reason'],
        );

        $coupon['selected'] = $requestedChoice === 'promo';
        $coupon['available'] = !empty($coupon['applied']);
        if (!$coupon['selected']) {
            $coupon['applied'] = false;
            $coupon['discount'] = 0;
        }
        $promo = $coupon;

        $roulette['selected'] = $requestedChoice === 'roulette';
        unset($roulette['rows']);

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
                || $cartTotal >= $shippingThreshold
                || ($requestedChoice === 'roulette' && !empty($roulette['free_shipping']));

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

            'roulette' =>
                $roulette,

            'gift_promotions' =>
                $giftPromotions,

            'discount_choice' =>
                $requestedChoice,

            'discount' =>
                $selectedDiscount,

            'discount_options' => array(
                'promo' => $couponOption,
                'roulette' => array(
                    'available' => !empty($roulette['available']),
                    'code' => $roulette['code'],
                    'title' => $roulette['title'],
                    'discount' => (float) $roulette['discount'],
                    'free_shipping' => !empty($roulette['free_shipping']),
                    'gift_id' => !empty($roulette['gift']['id']) ? (int) $roulette['gift']['id'] : null,
                    'expires_at' => !empty($roulette['prize']['expires_at'])
                        ? $roulette['prize']['expires_at']
                        : null,
                ),
            ),

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

        $variableId = self::getCartVariableId($cartItem['options'] ?? null);
        $rouletteGiftId = self::getRouletteGiftId($cartItem['options'] ?? null);
        $promotionGiftId = self::getPromotionGiftId($cartItem['options'] ?? null);
        $isGift = $rouletteGiftId > 0 || $promotionGiftId > 0;

        if ($variableId > 0) {
            $variable =
                $this->CI->products_model
                    ->get_product_variable_by_id(
                        $clang,
                        $variableId,
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

        if ($isGift) {
            $unitCurrentPrice = 1;
            $currentTotal = $unitCurrentPrice * $qty;
            $productDiscount = max(0, $originalTotal - $currentTotal);
        }

        return array(
            'row_id' =>
                $cartItem['rowid'],

            'product_id' =>
                (int) $cartItem['id'],

            'variable_id' =>
                $variableId,

            'is_roulette_gift' =>
                $rouletteGiftId > 0,

            'is_gift' =>
                $isGift,

            'gift_promotion_id' =>
                $promotionGiftId,

            'roulette_gift_id' =>
                $rouletteGiftId,

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

    private function calculateRouletteOption($client, $language, array $rows)
    {
        $option = array(
            'available' => false,
            'selected' => false,
            'code' => null,
            'title' => null,
            'discount' => 0,
            'eligible_subtotal' => 0,
            'reason' => 'no_active_prize',
            'prize' => null,
            'free_shipping' => false,
            'gift' => null,
            'rows' => $this->resetRowDiscounts($rows),
        );

        if (empty($client) || empty($client->id)) {
            $option['reason'] = 'authentication_required';
            return $option;
        }

        $this->CI->load->model('roulette_game_model');
        if (!$this->CI->roulette_game_model->isReady()) {
            $option['reason'] = 'roulette_setup_required';
            return $option;
        }

        try {
            $gameState = $this->CI->roulette_game_model->getState((int) $client->id, $language);
        } catch (Throwable $exception) {
            log_message('error', 'Could not load roulette discount state: ' . $exception->getMessage());
            $option['reason'] = 'roulette_unavailable';
            return $option;
        }

        $prize = !empty($gameState['active_prize']) && is_array($gameState['active_prize'])
            ? $gameState['active_prize']
            : null;

		if (empty($gameState['visible'])) {
			$option['reason'] = 'roulette_hidden';
			return $option;
		}

        if (empty($prize)) {
            $option['reason'] = !empty($gameState['reason'])
                ? (string) $gameState['reason']
                : 'no_active_prize';
            return $option;
        }

		$isDiscountPrize = $prize['type'] === 'discount_percent'
			&& (float) $prize['value'] > 0
			&& (float) $prize['value'] <= 100;
		$isFreeShippingPrize = $prize['type'] === 'free_shipping';
		$isGiftPrize = $prize['type'] === 'gift' && !empty($prize['gift_id']);
        if (!$isDiscountPrize && !$isFreeShippingPrize && !$isGiftPrize) {
            $option['reason'] = 'reward_not_ready';
            $option['prize'] = $prize;
            return $option;
        }

        if (($isFreeShippingPrize || $isGiftPrize) && empty($rows)) {
            $option['reason'] = 'no_eligible_products';
            $option['prize'] = $prize;
            return $option;
        }

        $eligibleSubtotal = 0;
        foreach ($option['rows'] as $key => $row) {
            $eligible = empty($row['is_gift'])
                && empty($row['has_product_discount'])
                && !$this->isPromotionExcludedBrand($row);
            $option['rows'][$key]['promo_eligible'] = $eligible;
            if ($eligible) {
                $eligibleSubtotal += (float) $row['current_total'];
            }
        }

        $discount = $isDiscountPrize
            ? round($eligibleSubtotal * ((float) $prize['value'] / 100), 2)
            : 0;
        if ($isDiscountPrize && ($eligibleSubtotal <= 0 || $discount <= 0)) {
            $option['reason'] = 'no_eligible_products';
            $option['prize'] = $prize;
            return $option;
        }

        $option['available'] = true;
		$option['code'] = $isGiftPrize
			? 'ROULETTE_GIFT_' . (int) $prize['gift_id']
			: 'ROULETTE_' . (int) round((float) $prize['value']);
        $option['title'] = (string) $prize['title'];
        $option['discount'] = $discount;
        $option['eligible_subtotal'] = $eligibleSubtotal;
        $option['reason'] = null;
        $option['prize'] = $prize;
        $option['free_shipping'] = $isFreeShippingPrize;
		$option['gift'] = $isGiftPrize ? array('id' => (int) $prize['gift_id']) : null;
        if ($discount > 0) {
            $option['rows'] = $this->distributePromoDiscount($option['rows'], $discount);
        }

        return $option;
    }

    private function resetRowDiscounts(array $rows)
    {
        foreach ($rows as $key => $row) {
            $rows[$key]['promo_eligible'] = false;
            $rows[$key]['promo_discount'] = 0;
        }

        return $rows;
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

        if (
            strtoupper(trim((string) $promo->code)) === 'WELCOME18'
        ) {
            $startedAt = !empty($client->welcome_offer_started_at)
                ? strtotime($client->welcome_offer_started_at)
                : false;
            $expiresAt = !empty($client->welcome_offer_expires_at)
                ? strtotime($client->welcome_offer_expires_at)
                : false;

            if (!$this->CI->promocodes_model->isWelcomeOfferConfigured($promo)) {
                $state['reason'] = 'PROMO_NOT_FOUND';
                return $this->removeInternalPromo($state);
            }

            if (
                !$startedAt
                || !$expiresAt
                || $startedAt > time()
                || $expiresAt <= time()
            ) {
                $state['reason'] = 'PROMO_EXPIRED';
                return $this->removeInternalPromo($state);
            }

            $existingOrders = $this->CI->db
                ->where('client_id', (int) $client->id)
                ->count_all_results('orders');

            if ($existingOrders > 0) {
                $state['reason'] = 'PROMO_ALREADY_USED';
                return $this->removeInternalPromo($state);
            }
        }

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
            $eligible = empty($row['is_gift']);

            /*
            |--------------------------------------------------------------------------
            | Global brand exclusions
            |--------------------------------------------------------------------------
            |
            | MAC, Anastasia Beverly Hills (ABH) and SHIK never receive a
            | campaign/promo discount, including the roulette discount.
            |
            */

            if ($this->isPromotionExcludedBrand($row)) {
                $eligible = false;
                if (!empty($row['brand_id']) && !empty($row['brand_title'])) {
                    $excludedCartBrands[(int) $row['brand_id']] = array(
                        'id' => (int) $row['brand_id'],
                        'title' => (string) $row['brand_title'],
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Existing product discount
            |--------------------------------------------------------------------------
            */

            if (
                $eligible
                &&
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

    /**
     * Campaign and roulette discounts are not valid for these brands.
     * Keep this server-side so a client cannot bypass the rule by changing
     * the cart UI or selecting a different discount source.
     */
    private function isPromotionExcludedBrand(array $row)
    {
        $title = strtoupper(trim((string) ($row['brand_title'] ?? '')));
        if ($title === '') {
            return false;
        }

        $compact = preg_replace('/[^A-Z0-9]+/', '', $title);
        if ($compact === '') {
            return false;
        }

        return $compact === 'MAC'
            || strpos($compact, 'MACCOSMETICS') === 0
            || $compact === 'ABH'
            || strpos($compact, 'ANASTASIABEVERLYHILLS') === 0
            || $compact === 'SHIK'
            || strpos($compact, 'SHIK') === 0;
    }
}
