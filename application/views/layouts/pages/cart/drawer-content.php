<?php
$isRomanian =
    $lclang === 'ro';

$totalItems =
    isset($total_items_cart)
        ? (int) $total_items_cart
        : 0;

$productsTotal =
    isset($products_total)
        ? (float) $products_total
        : 0;

$productDiscountTotal =
    isset($product_discount_total)
        ? (float) $product_discount_total
        : 0;

$promoDiscountTotal =
    isset($promo_discount_total)
        ? (float) $promo_discount_total
        : 0;

$discountTotal =
    isset($discount_total)
        ? (float) $discount_total
        : 0;

$cartTotal =
    isset($cart_total)
        ? (float) $cart_total
        : 0;

$deliveryPrice =
    isset($delivery_price)
        ? (float) $delivery_price
        : 0;

$finalTotal =
    isset($final_total)
        ? (float) $final_total
        : (
            $cartTotal
            + $deliveryPrice
        );

$promo =
    isset($promo)
    && is_array($promo)
        ? $promo
        : array();

$shipping =
    isset($shipping)
    && is_array($shipping)
        ? $shipping
        : array();

$freeShippingValue =
    isset($shipping['threshold'])
        ? (float) $shipping['threshold']
        : 0;

$remainingForFreeShipping =
    isset($shipping['remaining'])
        ? (float) $shipping['remaining']
        : 0;

$freeShippingProgress =
    isset($shipping['progress'])
        ? (float) $shipping['progress']
        : 0;

$isFreeShipping =
    !empty($shipping['is_free']);

$promoApplied =
    !empty($promo['applied']);

$promoCode =
    !empty($promo['code'])
        ? (string) $promo['code']
        : '';

$promoReason =
    !empty($promo['reason'])
        ? (string) $promo['reason']
        : null;


$promoExcludedCartBrands =
    !empty($promo['excluded_cart_brands'])
    && is_array($promo['excluded_cart_brands'])
        ? $promo['excluded_cart_brands']
        : array();

$promoExcludedBrandTitles =
    array();

foreach (
    $promoExcludedCartBrands as $brand
) {
    if (!empty($brand['title'])) {
        $promoExcludedBrandTitles[] =
            (string) $brand['title'];
    }
}


$promoErrorsRu = array(
    'PROMO_NOT_AUTHENTICATED' =>
        'Войдите в аккаунт, чтобы использовать промокод.',

    'PROMO_NOT_FOUND' =>
        'Промокод не найден.',

    'PROMO_NOT_STARTED' =>
        'Промокод ещё не начал действовать.',

    'PROMO_EXPIRED' =>
        'Срок действия промокода закончился.',

    'PROMO_MINIMUM_AMOUNT' =>
        'Недостаточная сумма подходящих товаров.',

    'PROMO_NOT_APPLICABLE' =>
        'Промокод не применяется к товарам в корзине.',

    'PROMO_ALREADY_USED' =>
        'Вы уже использовали этот промокод.',

    'PROMO_USAGE_LIMIT_REACHED' =>
        'Лимит использований промокода исчерпан.',
);

$promoErrorsRo = array(
    'PROMO_NOT_AUTHENTICATED' =>
        'Autentificați-vă pentru a utiliza codul promoțional.',

    'PROMO_NOT_FOUND' =>
        'Codul promoțional nu a fost găsit.',

    'PROMO_NOT_STARTED' =>
        'Codul promoțional nu este încă activ.',

    'PROMO_EXPIRED' =>
        'Codul promoțional a expirat.',

    'PROMO_MINIMUM_AMOUNT' =>
        'Valoarea produselor eligibile este insuficientă.',

    'PROMO_NOT_APPLICABLE' =>
        'Codul promoțional nu se aplică produselor din coș.',

    'PROMO_ALREADY_USED' =>
        'Ați utilizat deja acest cod promoțional.',

    'PROMO_USAGE_LIMIT_REACHED' =>
        'Limita de utilizare a codului promoțional a fost atinsă.',
);

$promoErrors =
    $isRomanian
        ? $promoErrorsRo
        : $promoErrorsRu;

$promoErrorMessage =
    $promoReason
    && isset($promoErrors[$promoReason])
        ? $promoErrors[$promoReason]
        : '';


if (
    $promoReason === 'PROMO_NOT_APPLICABLE'
    && !empty($promoExcludedBrandTitles)
) {
    if (count($promoExcludedBrandTitles) === 1) {
        $promoErrorMessage =
            (
                $isRomanian
                    ? 'Codul promoțional nu se aplică brandului '
                    : 'Промокод не действует на бренд '
            )
            . $promoExcludedBrandTitles[0]
            . '.';
    } else {
        $promoErrorMessage =
            (
                $isRomanian
                    ? 'Codul promoțional nu se aplică brandurilor '
                    : 'Промокод не действует на бренды '
            )
            . implode(
                ', ',
                $promoExcludedBrandTitles
            )
            . '.';
    }
}
        
?>

<?php if (empty($cart_items)) : ?>

    <div
        class="cart-drawer-empty"
        data-cart-empty
    >
        <div
            class="cart-drawer-empty__icon"
            aria-hidden="true"
        >
            <svg
                width="38"
                height="38"
                viewBox="0 0 24 24"
                fill="none"
            >
                <path
                    d="M6 8H18L19 21H5L6 8Z"
                    stroke="currentColor"
                    stroke-width="1.3"
                    stroke-linejoin="round"
                />

                <path
                    d="M9 8V6.5C9 4.57 10.34 3 12 3C13.66 3 15 4.57 15 6.5V8"
                    stroke="currentColor"
                    stroke-width="1.3"
                    stroke-linecap="round"
                />
            </svg>
        </div>

        <h3 class="cart-drawer-empty__title">
            <?= $isRomanian
                ? 'Coșul este gol'
                : 'Корзина пока пуста' ?>
        </h3>

        <p class="cart-drawer-empty__text">
            <?= $isRomanian
                ? 'Adăugați produsele pe care doriți să le cumpărați.'
                : 'Добавьте товары, которые хотите купить.' ?>
        </p>

        <a
            href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>"
            class="cart-drawer-empty__button"
        >
            <?= $isRomanian
                ? 'Vezi catalogul'
                : 'Перейти в каталог' ?>
        </a>
    </div>

<?php else : ?>

    <div class="cart-drawer-content">

        <section
    class="cart-drawer-shipping"
    data-cart-shipping
>
    <div
        class="cart-drawer-shipping__progress-state"
        data-cart-shipping-progress-state
        <?= $remainingForFreeShipping <= 0
            ? 'hidden'
            : '' ?>
    >
        <div class="cart-drawer-shipping__labels">
            <span>
                <?= $isRomanian
                    ? 'Livrare cu plată'
                    : 'Платная доставка' ?>
            </span>

            <span>
                <?= $isRomanian
                    ? 'Livrare gratuită'
                    : 'Бесплатная доставка' ?>
            </span>
        </div>

        <div class="cart-drawer-shipping__track">
            <span
                class="cart-drawer-shipping__progress"
                data-cart-shipping-progress
                style="width: <?= (float) $freeShippingProgress ?>%"
            ></span>
        </div>

        <div class="cart-drawer-shipping__values">
            <span>
                0 MDL
            </span>

            <span data-cart-shipping-threshold>
                <?= number_format(
                    $freeShippingValue,
                    0,
                    '.',
                    ' '
                ) ?>
                MDL
            </span>
        </div>
    </div>

    <div
        class="cart-drawer-shipping__success-state"
        data-cart-shipping-success-state
        <?= $remainingForFreeShipping > 0
            ? 'hidden'
            : '' ?>
    >
        <div class="cart-drawer-shipping__success">
            <?= $isRomanian
                ? 'Livrare gratuită'
                : 'Бесплатная доставка' ?>
        </div>
    </div>
</section>

        <div
            class="cart-drawer-products"
            data-cart-products
        >   
            <?php foreach (
                $cart_items as $item
            ) : ?>

                <?php
                $product =
                    !empty($item['products'])
                        ? $item['products']
                        : null;

                $variable =
                    !empty($item['variable'])
                        ? $item['variable']
                        : null;

                if (empty($product)) {
                    continue;
                }

                if (
                    !empty($variable)
                    && !empty($variable->img[0])
                ) {
                    $image =
                        newthumbs(
                            $variable->img[0]->img,
                            'products_variable_img'
                        );
                } else {
                    $image =
                        newthumbs(
                            $product->img,
                            'products'
                        );
                }

                $variantTitle = '';

                if (!empty($variable)) {
                    if (
                        !empty($variable->color)
                    ) {
                        $variantTitle =
                            $variable->color;
                    } elseif (
                        !empty($variable->VolumeVar)
                    ) {
                        $variantTitle =
                            $variable->VolumeVar;
                    }
                } elseif (
                    !empty($product->volume)
                ) {
                    $variantTitle =
                        $product->volume;
                }

                $calculated =
                    !empty($item['calculated'])
                    && is_array($item['calculated'])
                        ? $item['calculated']
                        : array();

                $currentPrice =
                    isset($calculated['unit_price'])
                        ? (float) $calculated['unit_price']
                        : 0;

                $oldPrice =
                    !empty($calculated['has_product_discount'])
                        ? (float) $calculated['unit_original_price']
                        : 0;

                $maxStock =
                    isset($calculated['max_quantity'])
                        ? (int) $calculated['max_quantity']
                        : 1;

                $discountAmount =
                    isset($calculated['product_discount'])
                        ? (float) $calculated['product_discount']
                        : 0;

                $promoDiscountAmount =
                    isset($calculated['promo_discount'])
                        ? (float) $calculated['promo_discount']
                        : 0;

                $productUrl =
                    '/'
                    . $lclang
                    . '/'
                    . $menu['all'][3]->uri
                    . '/'
                    . $product->cat_uri
                    . '/'
                    . $product->uri;
                ?>

                <?php
                    $analyticsPrice =
                        (float) $currentPrice;

                    $calculated =
                        !empty($item['calculated'])
                        && is_array($item['calculated'])
                            ? $item['calculated']
                            : null;

                    /*
                    * Если CartCalculator уже применил promo,
                    * используем реальную финальную unit price.
                    */
                    if (
                        !empty($calculated)
                        && isset($calculated['final_total'])
                        && (int) $item['qty'] > 0
                    ) {
                        $analyticsPrice =
                            round(
                                (float) $calculated['final_total']
                                / (int) $item['qty'],
                                2
                            );
                    }
                    ?>

                <article
                    class="cart-drawer-item"
                    data-cart-drawer-row

                    data-rowid="<?= htmlspecialchars(
                        $item['rowid'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"

                    data-ga4-item-id="<?= (int) $product->id ?>"

                    data-ga4-item-name="<?= htmlspecialchars(
                        $product->title,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"

                    data-ga4-item-brand="<?= htmlspecialchars(
                        !empty($product->brand_title)
                            ? $product->brand_title
                            : '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"

                    data-ga4-item-category="<?= htmlspecialchars(
                        !empty($product->cat_title)
                            ? $product->cat_title
                            : '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"

                    data-ga4-item-variant="<?= htmlspecialchars(
                        $variantTitle,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"

                    data-ga4-item-price="<?= htmlspecialchars(
                        number_format(
                            $analyticsPrice,
                            2,
                            '.',
                            ''
                        ),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    <a
                        href="<?= htmlspecialchars(
                            $productUrl,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        class="cart-drawer-item__image"
                    >
                        <img
                            src="<?= htmlspecialchars(
                                $image,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $product->title,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                    </a>

                    <div class="cart-drawer-item__content">
                        <div class="cart-drawer-item__top">
                            <div>
                                <p class="cart-drawer-item__brand">
                                    <?= htmlspecialchars(
                                        $product->brand_title,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </p>

                                <a
                                    href="<?= htmlspecialchars(
                                        $productUrl,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="cart-drawer-item__name"
                                >
                                    <?= htmlspecialchars(
                                        $product->title,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </a>

                                <?php if (
                                    $variantTitle !== ''
                                ) : ?>
                                    <p class="cart-drawer-item__variant">
                                        <?= htmlspecialchars(
                                            $variantTitle,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <button
                                type="button"
                                class="cart-drawer-item__remove"
                                data-cart-drawer-remove
                                aria-label="<?= $isRomanian
                                    ? 'Șterge produsul'
                                    : 'Удалить товар' ?>"
                            >
                                <svg
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M7 7L17 17M17 7L7 17"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="cart-drawer-item__more"
                                data-cart-drawer-more
                                aria-label="<?= $isRomanian
                                    ? 'Acțiuni produs'
                                    : 'Действия с товаром' ?>"
                            >
                                <span></span>
                                <span></span>
                                <span></span>
                            </button>
                        </div>

                        <div class="cart-drawer-item__bottom">
                            <div
																class="cart-drawer-quantity"
																data-cart-drawer-quantity
														>
																<button
																		type="button"
																		class="cart-drawer-quantity__button"
																		data-cart-drawer-minus
																		aria-label="<?= $isRomanian
																				? 'Micșorează cantitatea'
																				: 'Уменьшить количество' ?>"
																		<?= (int) $item['qty'] <= 1
																				? 'disabled'
																				: '' ?>
																>
																		<span aria-hidden="true">−</span>
																</button>

																<span
																		class="cart-drawer-quantity__value"
																		data-cart-drawer-qty
																		data-rowid="<?= htmlspecialchars(
																				$item['rowid'],
																				ENT_QUOTES,
																				'UTF-8'
																		) ?>"
																		data-max="<?= (int) $maxStock ?>"
																>
																		<?= (int) $item['qty'] ?>
																</span>

																<button
																		type="button"
																		class="cart-drawer-quantity__button"
																		data-cart-drawer-plus
																		aria-label="<?= $isRomanian
																				? 'Mărește cantitatea'
																				: 'Увеличить количество' ?>"
																		<?= (int) $item['qty'] >= (int) $maxStock
																				? 'disabled'
																				: '' ?>
																>
																		<span aria-hidden="true">+</span>
																</button>
														</div>

                            <div class="cart-drawer-item__price">
                                <?php if ($discountAmount > 0) : ?>
                                    <span class="cart-drawer-item__discount">
                                        <?= $isRomanian
                                            ? 'Reducere'
                                            : 'Скидка' ?>

                                        <?= number_format(
                                            $discountAmount,
                                            0,
                                            '.',
                                            ' '
                                        ) ?>

                                        <?= MDL ?>
                                    </span>
                                <?php endif; ?>

                                <div class="cart-drawer-item__price-row">
                                    <?php if ($oldPrice > 0) : ?>
                                        <span class="cart-drawer-item__old-price">
                                            <?= number_format(
                                                $oldPrice * (int) $item['qty'],
                                                0,
                                                '.',
                                                ' '
                                            ) ?>
                                            <?= MDL ?>
                                        </span>
                                    <?php endif; ?>

                                    <strong class="cart-drawer-item__current-price">
                                        <?= number_format(
                                            $currentPrice * (int) $item['qty'],
                                            0,
                                            '.',
                                            ' '
                                        ) ?>
                                        <?= MDL ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

            <?php endforeach; ?>
        </div>
       <section
            class="cart-drawer-promo"
            data-cart-promo
            data-cart-promo-status="<?= $promoApplied
                ? 'applied'
                : 'form' ?>"
        >
            <?php if ($promoApplied) : ?>

                <div
                    class="cart-drawer-promo__applied"
                    data-cart-promo-applied
                >
                    <div class="cart-drawer-promo__applied-info">
                        <span class="cart-drawer-promo__label">
                            <?= $isRomanian
                                ? 'Cod promoțional'
                                : 'Промокод' ?>
                        </span>

                        <strong data-cart-promo-code>
                            <?= htmlspecialchars(
                                $promoCode,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>
                    </div>

                    <div class="cart-drawer-promo__applied-side">
                        <span
                            class="cart-drawer-promo__discount"
                            data-cart-promo-discount
                        >
                            −<?= number_format(
                                $promoDiscountTotal,
                                0,
                                '.',
                                ' '
                            ) ?>
                            <?= MDL ?>
                        </span>

                        <button
                            type="button"
                            class="cart-drawer-promo__remove"
                            data-cart-promo-remove
                            aria-label="<?= $isRomanian
                                ? 'Elimină codul promoțional'
                                : 'Удалить промокод' ?>"
                        >
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M7 7L17 17M17 7L7 17"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <?php if (
                    !empty($promoExcludedBrandTitles)
                ) : ?>
                
                    <p class="cart-drawer-promo__notice">
                        <?php if (
                            count($promoExcludedBrandTitles) === 1
                        ) : ?>
                
                            <?= $isRomanian
                                ? 'Codul promoțional nu se aplică brandului '
                                : 'Промокод не действует на бренд ' ?>
                
                        <?php else : ?>
                
                            <?= $isRomanian
                                ? 'Codul promoțional nu se aplică brandurilor '
                                : 'Промокод не действует на бренды ' ?>
                
                        <?php endif; ?>
                
                        <strong>
                            <?= htmlspecialchars(
                                implode(
                                    ', ',
                                    $promoExcludedBrandTitles
                                ),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </strong>.
                    </p>
                
                <?php endif; ?>

            <?php else : ?>

                <form
                    class="cart-drawer-promo__form"
                    data-cart-promo-form
                >
                    <div class="cart-drawer-promo__field">
                        <input
                            type="text"
                            name="code"
                            class="cart-drawer-promo__input"
                            data-cart-promo-input
                            value="<?= htmlspecialchars(
                                $promoCode,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            placeholder="<?= $isRomanian
                                ? 'Introduceți codul promoțional'
                                : 'Введите промокод' ?>"
                            autocomplete="off"
                            maxlength="64"
                        >

                        <button
                            type="submit"
                            class="cart-drawer-promo__submit"
                            data-cart-promo-submit
                            aria-label="<?= $isRomanian
                                ? 'Aplică codul promoțional'
                                : 'Применить промокод' ?>"
                        >
                            <span aria-hidden="true">→</span>
                        </button>
                    </div>

                    <p
                        class="cart-drawer-promo__error"
                        data-cart-promo-error
                        <?= $promoErrorMessage === ''
                            ? 'hidden'
                            : '' ?>
                    >
                        <?= htmlspecialchars(
                            $promoErrorMessage,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>
                </form>

            <?php endif; ?>
        </section>

        <section class="cart-drawer-summary">
            <h3 class="cart-drawer-summary__title">
                <?= $isRomanian
                    ? 'Suma comenzii'
                    : 'Сумма заказа' ?>
            </h3>

            <div class="cart-drawer-summary__rows">
                <div class="cart-drawer-summary__row">
                    <span>
                        <?= $isRomanian
                            ? 'Produse'
                            : 'Товары' ?>
                        ·
                        <span data-cart-summary-count>
                            <?= $totalItems ?>
                        </span>
                    </span>

                   <span data-cart-summary-products>
                    <?= number_format(
                        $productsTotal,
                        0,
                        '.',
                        ' '
                    ) ?>
                    <?= MDL ?>
                </span>
                </div>

                <?php if ($productDiscountTotal > 0) : ?>
                    <div
                        class="
                            cart-drawer-summary__row
                            cart-drawer-summary__row--discount
                        "
                    >
                        <span>
                            <?= $isRomanian
                                ? 'Reducere'
                                : 'Скидка' ?>
                        </span>

                        <span data-cart-summary-discount>
                            −<?= number_format(
                                $productDiscountTotal,
                                0,
                                '.',
                                ' '
                            ) ?>
                            <?= MDL ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div
                    class="
                        cart-drawer-summary__row
                        cart-drawer-summary__row--promo
                    "
                    data-cart-summary-promo-wrap
                    <?= $promoDiscountTotal <= 0
                        ? 'hidden'
                        : '' ?>
                >
                    <span>
                        <?= $isRomanian
                            ? 'Cod promoțional'
                            : 'Промокод' ?>

                        <span data-cart-summary-promo-code>
                            <?= htmlspecialchars(
                                $promoCode,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>
                    </span>

                    <span data-cart-summary-promo>
                        −<?= number_format(
                            $promoDiscountTotal,
                            0,
                            '.',
                            ' '
                        ) ?>
                        <?= MDL ?>
                    </span>
                </div>

                <div class="cart-drawer-summary__row">
                    <span>
                        <?= $isRomanian
                            ? 'Livrare'
                            : 'Доставка' ?>
                    </span>

                    <span
                        data-cart-summary-delivery
                    >
                        <?php if (
                            $deliveryPrice > 0
                        ) : ?>
                            <?= number_format(
                                $deliveryPrice,
                                0,
                                '.',
                                ' '
                            ) ?>
                            <?= MDL ?>
                        <?php else : ?>
                            <?= $isRomanian
                                ? 'Gratuit'
                                : 'Бесплатно' ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="cart-drawer-summary__total">
                <span>
                    <?= $isRomanian
                        ? 'Total'
                        : 'Итого' ?>
                </span>

                <strong data-cart-summary-total>
                    <?= number_format(
                        $finalTotal,
                        0,
                        '.',
                        ' '
                    ) ?>
                    <?= MDL ?>
                </strong>
            </div>

            <?php if (
                !empty($client_info)
                && !$is_b2b
                && $bonus_total > 0
            ) : ?>

                <p class="cart-drawer-summary__bonus">
                    <?= $isRomanian
                        ? 'Veți primi'
                        : 'Будет начислено' ?>

                    +<?= number_format(
                        $bonus_total,
                        0,
                        '.',
                        ' '
                    ) ?>

                    <?= $isRomanian
                        ? 'bonusuri'
                        : 'бонусов' ?>
                </p>

            <?php endif; ?>

            <?php if (
                empty($client_info)
            ) : ?>
                <p class="cart-drawer-summary__login">
                    <?= $isRomanian
                        ? 'Autentificați-vă pentru a primi bonusuri.'
                        : 'Войдите в аккаунт, чтобы получать бонусы.' ?>
                </p>
            <?php endif; ?>

           <a
                href="/<?= $lclang ?>/<?= $menu['all'][19]->uri ?>"
                class="cart-drawer-summary__checkout"
                data-cart-checkout
                data-auth-required="<?= empty($client_info)
                    ? 'true'
                    : 'false' ?>"
            >
                <?= $isRomanian
                    ? 'Finalizează comanda'
                    : 'Оформить заказ' ?>
            </a>
        </section>

    </div>

<?php endif; ?> 
<template data-cart-checkout-gate-template>
	<section
		class="cart-checkout-gate"
		data-cart-checkout-gate
	>
		<div class="cart-checkout-gate__content">

			<h2 class="cart-checkout-gate__title">
				<?= $isRomanian
					? 'Cumpărați cu avantaje'
					: 'Покупайте с выгодой' ?>
			</h2>

			<p class="cart-checkout-gate__text">
				<?= $isRomanian
                    ? 'Autentificați-vă sau creați un cont pentru a primi bonusuri și oferte personalizate.'
                    : 'Войдите или зарегистрируйтесь, чтобы получать бонусы и персональные предложения.' ?>
			</p>

			<div class="cart-checkout-gate__actions">

				<button
					type="button"
					class="cart-checkout-gate__primary"
					data-cart-checkout-auth
				>
					<?= $isRomanian
						? 'Intră / Înregistrează-te'
						: 'Войти / Зарегистрироваться' ?>
				</button>

				<a
					href="/<?= $lclang ?>/<?= $menu['all'][19]->uri ?>"
					class="cart-checkout-gate__secondary"
					data-cart-checkout-guest
				>
					<?= $isRomanian
						? 'Continuă ca vizitator'
						: 'Продолжить как гость' ?>
				</a>

			</div>

		</div>
	</section>
</template>