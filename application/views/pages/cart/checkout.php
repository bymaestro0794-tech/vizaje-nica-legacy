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

?>


<main class="page">
    <section class="order">
        <div class="order__container _container-mini">
            <h2 class="order__title"><?= $page_title ?></h2>
            <form action="/<?= $lclang ?>/<?= $uri2 ?>" class="order__body" method="post" data-checkout>
                <div class="order__main main-order">
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
                            'layouts/pages/checkout/recipient',
                            array(
                                'lclang' =>
                                    $lclang,

                                'client_info' =>
                                    $client_info,
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