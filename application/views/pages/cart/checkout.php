<?php

$productsTotal =
    isset($products_total)
        ? (float) $products_total
        : (float) $total_price_cart;

$productDiscountTotal =
    isset($product_discount_total)
        ? (float) $product_discount_total
        : 0;

$promoDiscountTotal =
    isset($promo_discount_total)
        ? (float) $promo_discount_total
        : 0;

$checkoutTotal =
    isset($checkout_total)
        ? (float) $checkout_total
        : (
            (float) $total_price_cart
            + (float) $delivery_price
        );

$promo =
    isset($promo)
    && is_array($promo)
        ? $promo
        : array();

$promoApplied =
    !empty($promo['applied']);

$promoCode =
    !empty($promo['code'])
        ? (string) $promo['code']
        : '';

$discountChoice = isset($discount_choice)
    ? (string) $discount_choice
    : 'none';
$discountOptions = isset($discount_options) && is_array($discount_options)
    ? $discount_options
    : array();
$selectedDiscount = isset($discount) && is_array($discount)
    ? $discount
    : array();
$roulette = isset($roulette) && is_array($roulette)
    ? $roulette
    : array();


$bonusTotal =
    isset($bonus_total)
        ? (float) $bonus_total
        : 0;

$bonusBalance =
    isset($bonus_balance)
        ? (float) $bonus_balance
        : 0;

$bonusEligibleTotal =
    isset($bonus_eligible_total)
        ? (float) $bonus_eligible_total
        : 0;

$bonusMaxWriteOff =
    isset($bonus_max_writeoff)
        ? (float) $bonus_max_writeoff
        : 0;

$canAccumulateBonus =
    !empty($client_info)
    && $bonusTotal > 0;

$canWriteOffBonus =
    !empty($client_info)
    && $bonusBalance > 0
    && $bonusMaxWriteOff > 0;

$showBonusSection =
    empty($_SESSION['isb2b'])
    && !empty($client_info);

$checkoutErrorMessage = !empty($_SESSION['checkout_error'])
    ? (string) $_SESSION['checkout_error']
    : '';
$promoCheckoutError = !empty($_SESSION['checkout_promo_error']);
unset($_SESSION['checkout_error']);
unset($_SESSION['checkout_promo_error']);

if ($checkoutErrorMessage === '' && $promoCheckoutError) {
    $checkoutErrorMessage = $lclang === 'ro'
        ? 'Codul promoțional nu mai poate fi aplicat. Alegeți reducerea disponibilă.'
        : 'Промокод больше нельзя применить. Выберите доступную скидку.';
}

?>


<main class="page">
    <section class="order">
        <?php if (($site_theme_key ?? 'default') === 'halloween') : ?>
            <span
                class="order__halloween-decoration order__halloween-web order__halloween-web--top"
                aria-hidden="true"
            ></span>
            <span
                class="order__halloween-decoration order__halloween-web order__halloween-web--bottom"
                aria-hidden="true"
            ></span>
            <span
                class="order__halloween-decoration order__halloween-bat order__halloween-bat--top"
                aria-hidden="true"
            ></span>
            <span
                class="order__halloween-decoration order__halloween-bat order__halloween-bat--bottom"
                aria-hidden="true"
            ></span>
            <span
                class="order__halloween-decoration order__halloween-pumpkin"
                aria-hidden="true"
            ></span>
        <?php endif; ?>

        <div class="order__container _container-mini">
            <h2 class="order__title"><?= $page_title ?></h2>
            <form action="/<?= $lclang ?>/<?= $uri2 ?>" class="order__body" method="post" data-checkout>
                <?php if ($checkoutErrorMessage !== '') : ?>
                    <p class="vn-checkout-error" role="alert">
                        <?= htmlspecialchars($checkoutErrorMessage, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
                <div class="order__main main-order">
                    <?php
                        $this->load->view(
                            'layouts/pages/checkout/recipient',
                            array(
                                'lclang' =>
                                    $lclang,

                                'client_info' =>
                                    $client_info,

                                'privacy_url' =>
                                    '/' . $lclang . '/' . $menu['all'][23]->uri,

                                'privacy_title' =>
                                    $menu['all'][23]->title,
                            )
                        );
                    ?>
                    <?php
                    $this->load->view(
                        'layouts/pages/checkout/delivery',
                        array(
                            'stores' =>
                                $stores,

                            'lclang' =>
                                $lclang,

                            'delivery_price' =>
                                $delivery_price,
                        )
                    );
                    ?>
                    <?php
                    $this->load->view(
                        'layouts/pages/checkout/payment',
                        array(
                            'lclang' =>
                                $lclang,
                        )
                    );
                    ?>
                    <?php if ($showBonusSection) : ?>

                        <?php
                        $this->load->view(
                            'layouts/pages/checkout/bonus',
                            array(
                                'lclang' =>
                                    $lclang,

                                'bonusTotal' =>
                                    $bonusTotal,

                                'bonusBalance' =>
                                    $bonusBalance,

                                'bonusMaxWriteOff' =>
                                    $bonusMaxWriteOff,
                            )
                        );
                        ?>

                    <?php endif; ?>
                </div>
                <div class="order__sidebar">

                <?php
                $this->load->view(
                    'layouts/pages/checkout/summary',
                    array(
                        'lclang' =>
                            $lclang,

                        'menu' =>
                            $menu,

                        'total_items_cart' =>
                            $total_items_cart,

                        'total_price_cart' =>
                            $total_price_cart,

                        'productsTotal' =>
                            $productsTotal,

                        'productDiscountTotal' =>
                            $productDiscountTotal,

                        'promoDiscountTotal' =>
                            $promoDiscountTotal,

                        'promoApplied' =>
                            $promoApplied,

                        'promoCode' =>
                            $promoCode,

                        'discountChoice' =>
                            $discountChoice,

                        'discountOptions' =>
                            $discountOptions,

                        'selectedDiscount' =>
                            $selectedDiscount,

                        'roulette' =>
                            $roulette,

                        'delivery_price' =>
                            $delivery_price,

                        'client_info' =>
                            $client_info,

                        'bonusTotal' =>
                            $bonusTotal,

                        'bonusMaxWriteOff' =>
                            $bonusMaxWriteOff,
                    )
                );
                ?>

            </div>
            </form>
        </div>
    </section>
</main>


<?php
$this->load->view(
	'layouts/pages/checkout/pickup-picker',
	array(
		'stores' =>
			$stores,

		'lclang' =>
			$lclang,
	)
);
?>

<?php if (
	!empty($ga4_checkout)
	&& !empty($ga4_checkout['items'])
) : ?>

	<script
		type="application/json"
		id="checkout-ga4-data"
	><?= json_encode(
		$ga4_checkout,
		JSON_UNESCAPED_UNICODE
		| JSON_UNESCAPED_SLASHES
		| JSON_HEX_TAG
		| JSON_HEX_AMP
		| JSON_HEX_APOS
		| JSON_HEX_QUOT
	) ?></script>

<?php endif; ?>
