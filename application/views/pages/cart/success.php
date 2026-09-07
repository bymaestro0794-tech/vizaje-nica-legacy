<?php
    $deliveryPrice = !empty($order->delivery_price)
        ? (float) $order->delivery_price
        : 0;

    $bonusMinus = !empty($order->bonus_minus)
        ? (float) $order->bonus_minus
        : 0;

    $promoDiscount = !empty($order->promo_discount)
        ? (float) $order->promo_discount
        : 0;

    $promoCode = !empty($order->promo_code)
        ? (string) $order->promo_code
        : '';

    /*
     * orders.total уже после promo.
     * Поэтому сумма товаров до promo:
     */
    $productsTotal =
        (float) $order->total
        + $promoDiscount;

    $orderTotal =
        (float) $order->total
        + $deliveryPrice
        - $bonusMinus;
?>

<main class="page">
    <section class="order-success">
        <div class="order-success__container _container">

            <div class="order-success__card">

                <div class="order-success__status">
                    <span class="order-success__icon" aria-hidden="true">
                        <svg
                            width="32"
                            height="32"
                            viewBox="0 0 32 32"
                            fill="none"
                        >
                            <path
                                d="M9 16.5L13.5 21L23 11.5"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <div class="order-success__status-content">
                        <span class="order-success__eyebrow">
                            <?= $lclang === 'ro'
                                ? 'Comanda a fost plasată'
                                : 'Заказ успешно оформлен' ?>
                        </span>

                        <h1 class="order-success__title">
                            <?= $page_title ?> №<?= (int) $order->id ?>
                        </h1>

                        <p class="order-success__description">
                            <?= $lclang === 'ro'
                                ? 'Vă mulțumim pentru comandă. În curând vă vom contacta pentru confirmare.'
                                : 'Спасибо за заказ. В ближайшее время мы свяжемся с вами для подтверждения.' ?>
                        </p>
                    </div>
                </div>

                <div class="order-success__content">

                    <div class="order-success__summary">
                        <div class="order-success__summary-head">
                            <h2 class="order-success__summary-title">
                                <?= ORDER_PRICE ?>
                            </h2>

                            <span class="order-success__number">
                                №<?= (int) $order->id ?>
                            </span>
                        </div>

                        <ul class="order-success__list">

                            <li class="order-success__row">
                                <span class="order-success__name">
                                    <?= PRODUCTS ?>
                                </span>

                                <span class="order-success__value">
                                    <?= number_format(
                                        $productsTotal,
                                        0,
                                        '.',
                                        ' '
                                    ) ?>
                                    <?= MDL ?>
                                </span>
                            </li>

                            <?php if ($promoDiscount > 0 && $promoCode !== '') { ?>
                                <li class="order-success__row order-success__row--promo">
                                    <span class="order-success__name">
                                        <?= $lclang === 'ro'
                                            ? 'Cod promoțional'
                                            : 'Промокод' ?>

                                        <?= htmlspecialchars(
                                            $promoCode,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>

                                    <span class="order-success__value">
                                        −<?= number_format(
                                            $promoDiscount,
                                            0,
                                            '.',
                                            ' '
                                        ) ?>
                                        <?= MDL ?>
                                    </span>
                                </li>
                            <?php } ?>

                            <li class="order-success__row">
                                <span class="order-success__name">
                                    <?= DELIVERY ?>
                                </span>

                                <span class="order-success__value delivery-info">
                                    <?php if ($deliveryPrice > 0) { ?>
                                        <?= number_format(
                                            $deliveryPrice,
                                            0,
                                            '.',
                                            ' '
                                        ) ?>
                                        <?= MDL ?>
                                    <?php } else { ?>
                                        <?= PICKUP_FREE ?>
                                    <?php } ?>
                                </span>
                            </li>

                            <?php if ($bonusMinus > 0) { ?>
                                <li class="order-success__row">
                                    <span class="order-success__name">
                                        <?= $lclang === 'ro'
                                            ? 'Bonusuri utilizate'
                                            : 'Списано бонусов' ?>
                                    </span>

                                    <span class="order-success__value">
                                        −<?= number_format(
                                            $bonusMinus,
                                            0,
                                            '.',
                                            ' '
                                        ) ?>
                                        <?= MDL ?>
                                    </span>
                                </li>
                            <?php } ?>

                            <li class="order-success__row order-success__row--total">
                                <span class="order-success__name">
                                    <?= TOTAL_PRICE ?>
                                </span>

                                <span class="order-success__value">
                                    <?= number_format(
                                        $orderTotal,
                                        0,
                                        '.',
                                        ' '
                                    ) ?>
                                    <?= MDL ?>
                                </span>
                            </li>

                            <?php if (!empty($order->bonus_plus)) { ?>
                                <li class="order-success__row order-success__row--bonus">
                                    <span class="order-success__name">
                                        <?= PURCHASE_BONUSES ?>
                                    </span>

                                    <span class="order-success__value">
                                        +<?= $order->bonus_plus ?>
                                    </span>
                                </li>
                            <?php } ?>

                        </ul>
                    </div>

                    <div class="order-success__actions">
                        <a
                            href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>"
                            class="order-success__button"
                        >
                            <?= $lclang === 'ro'
                                ? 'Continuați cumpărăturile'
                                : 'Продолжить покупки' ?>
                        </a>

                        <a
                            href="/<?= $lclang ?>"
                            class="order-success__home"
                        >
                            <?= $lclang === 'ro'
                                ? 'Înapoi la pagina principală'
                                : 'Вернуться на главную' ?>
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </section>
</main>
<?php if (
    !empty($ga4_purchase)
    && !empty($ga4_purchase['transaction_id'])
    && !empty($ga4_purchase['items'])
) : ?>

    <script
        type="application/json"
        id="purchase-ga4-data"
    ><?= json_encode(
        $ga4_purchase,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ) ?></script>

<?php endif; ?>
<script>
	try {
		localStorage.removeItem('vizaje_checkout_draft_v2')
	} catch (error) {
		console.warn('[Checkout draft clear]', error)
	}
</script>