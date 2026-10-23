<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian =
	isset($lclang)
	&& $lclang === 'ro';

$summaryProductsTotal =
	isset($productsTotal)
		? (float) $productsTotal
		: (float) $total_price_cart;

$summaryProductDiscount =
	isset($productDiscountTotal)
		? (float) $productDiscountTotal
		: 0;

$summaryPromoDiscount =
	isset($promoDiscountTotal)
		? (float) $promoDiscountTotal
		: 0;

$summaryCartTotal =
	(float) $total_price_cart;

$summaryCourierPrice =
	(float) $delivery_price;

$summaryExpressPrice =
	100;

$summaryBonusEarn =
	isset($bonusTotal)
		? (float) $bonusTotal
		: 0;

$summaryBonusWriteOff =
	0;

$discountChoice = isset($discountChoice) ? (string) $discountChoice : 'none';
$discountOptions = isset($discountOptions) && is_array($discountOptions) ? $discountOptions : array();
$selectedDiscount = isset($selectedDiscount) && is_array($selectedDiscount) ? $selectedDiscount : array();
$discountSource = !empty($selectedDiscount['source']) ? (string) $selectedDiscount['source'] : 'none';
$selectedDiscountCode = !empty($selectedDiscount['label']) ? (string) $selectedDiscount['label'] : $promoCode;
$promoChoiceAvailable = !empty($discountOptions['promo']['available']);
$rouletteChoiceAvailable = !empty($discountOptions['roulette']['available']);
$showDiscountChoice = ($promoChoiceAvailable && $rouletteChoiceAvailable)
	|| ($promoChoiceAvailable && $discountChoice !== 'promo')
	|| ($rouletteChoiceAvailable && $discountChoice !== 'roulette');

if (
	!empty($client_info)
	&& empty($_SESSION['isb2b'])
) {
	$summaryBonusWriteOff =
		min(
			(float) $bonusMaxWriteOff,
			(float) $client_info->Bonus
		);
}
?>

<aside
	class="vn-checkout-summary"
	data-checkout-summary

	data-cart-total="<?= htmlspecialchars(
		number_format(
			$summaryCartTotal,
			2,
			'.',
			''
		),
		ENT_QUOTES,
		'UTF-8'
	) ?>"

	data-courier-price="<?= htmlspecialchars(
		number_format(
			$summaryCourierPrice,
			2,
			'.',
			''
		),
		ENT_QUOTES,
		'UTF-8'
	) ?>"

	data-express-price="<?= htmlspecialchars(
		number_format(
			$summaryExpressPrice,
			2,
			'.',
			''
		),
		ENT_QUOTES,
		'UTF-8'
	) ?>"

	data-bonus-writeoff="<?= htmlspecialchars(
		number_format(
			$summaryBonusWriteOff,
			2,
			'.',
			''
		),
		ENT_QUOTES,
		'UTF-8'
	) ?>"
>
	<div class="vn-checkout-summary__head">

		<h3 class="vn-checkout-summary__title">
			<?= $isRomanian
				? 'Comanda dvs.'
				: 'Ваш заказ' ?>
		</h3>

	</div>

	<div class="vn-checkout-summary__body">

		<div class="vn-checkout-summary__rows">

			<div class="vn-checkout-summary__row">
				<span class="vn-checkout-summary__name">
					<?= PRODUCTS ?>

					<span>
						· <?= (int) $total_items_cart ?>
					</span>
				</span>

				<span class="vn-checkout-summary__value">
					<?= number_format(
						$summaryProductsTotal,
						0,
						'.',
						' '
					) ?>
					<?= MDL ?>
				</span>
			</div>

			<?php if ($summaryProductDiscount > 0) : ?>

				<div
					class="
						vn-checkout-summary__row
						vn-checkout-summary__row--discount
					"
				>
					<span class="vn-checkout-summary__name">
						<?= $isRomanian
							? 'Reducere'
							: 'Скидка' ?>
					</span>

					<span class="vn-checkout-summary__value">
						−<?= number_format(
							$summaryProductDiscount,
							0,
							'.',
							' '
						) ?>
						<?= MDL ?>
					</span>
				</div>

			<?php endif; ?>

			<?php if ($summaryPromoDiscount > 0 && $discountSource !== 'none') : ?>

				<div
					class="
						vn-checkout-summary__row
						vn-checkout-summary__row--promo
					"
				>
					<span class="vn-checkout-summary__name">
						<span>
							<?= $discountSource === 'roulette'
								? ($isRomanian ? 'Reducere la ruletă' : 'Скидка рулетки')
								: ($isRomanian ? 'Cod promoțional' : 'Промокод') ?>
						</span>

						<strong>
							<?= htmlspecialchars(
								$selectedDiscountCode,
								ENT_QUOTES,
								'UTF-8'
							) ?>
						</strong>
					</span>

					<span class="vn-checkout-summary__value">
						−<?= number_format(
							$summaryPromoDiscount,
							0,
							'.',
							' '
						) ?>
						<?= MDL ?>
					</span>
				</div>

			<?php endif; ?>

			<?php if ($showDiscountChoice) : ?>
				<div class="vn-checkout-discount-choice" role="group" aria-label="<?= $isRomanian ? 'Alegeți o singură reducere' : 'Выберите одну скидку' ?>">
					<p class="vn-checkout-discount-choice__title"><?= $isRomanian ? 'Alegeți o singură reducere' : 'Выберите одну скидку' ?></p>
					<?php if ($promoChoiceAvailable) : ?>
						<button type="button" class="vn-checkout-discount-choice__option<?= $discountChoice === 'promo' ? ' is-selected' : '' ?>" data-checkout-discount-choice="promo">
							<span><?= htmlspecialchars((string) $discountOptions['promo']['code'], ENT_QUOTES, 'UTF-8') ?></span>
							<strong>−<?= number_format((float) $discountOptions['promo']['discount'], 0, '.', ' ') ?> <?= MDL ?></strong>
						</button>
					<?php endif; ?>
					<?php if ($rouletteChoiceAvailable) : ?>
						<button type="button" class="vn-checkout-discount-choice__option<?= $discountChoice === 'roulette' ? ' is-selected' : '' ?>" data-checkout-discount-choice="roulette">
							<span><?= htmlspecialchars((string) $discountOptions['roulette']['title'], ENT_QUOTES, 'UTF-8') ?></span>
							<strong><?= !empty($discountOptions['roulette']['free_shipping'])
								? ($isRomanian ? 'Livrare gratuită' : 'Бесплатная доставка')
								: '−' . number_format((float) $discountOptions['roulette']['discount'], 0, '.', ' ') . ' ' . MDL ?></strong>
						</button>
					<?php endif; ?>
					<p class="vn-checkout-discount-choice__note"><?= $isRomanian ? 'Reducerile nu se cumulează.' : 'Скидки не суммируются.' ?></p>
					<p class="vn-checkout-discount-choice__error" data-checkout-discount-error hidden></p>
				</div>
			<?php endif; ?>

			<div
				class="
					vn-checkout-summary__row
					vn-checkout-summary__row--bonus
				"
				data-summary-bonus-row
				hidden
			>
				<span class="vn-checkout-summary__name">
					<?= $isRomanian
						? 'Bonusuri'
						: 'Бонусы' ?>
				</span>

				<span
					class="vn-checkout-summary__value"
					data-summary-bonus
				></span>
			</div>

			<div class="vn-checkout-summary__row">
				<span class="vn-checkout-summary__name">
					<?= DELIVERY ?>
				</span>

				<span
					class="vn-checkout-summary__value"
					data-summary-delivery
				>
					—
				</span>
			</div>

		</div>

		<div class="vn-checkout-summary__divider"></div>

		<div class="vn-checkout-summary__total">

			<span class="vn-checkout-summary__total-name">
				<?= TOTAL_PRICE ?>
			</span>

			<span
				class="vn-checkout-summary__total-value"
				data-summary-total
			>
				<?= number_format(
					$summaryCartTotal,
					0,
					'.',
					' '
				) ?>
				<?= MDL ?>
			</span>

		</div>

		<?php if (
			!empty($client_info)
			&& empty($_SESSION['isb2b'])
			&& $summaryBonusEarn > 0
		) : ?>

			<div
				class="vn-checkout-summary__earn"
				data-summary-bonus-earn
				hidden
			>
				<span>
					<?= $isRomanian
						? 'Veți primi'
						: 'Будет начислено' ?>
				</span>

				<strong>
					+<?= number_format(
						$summaryBonusEarn,
						2,
						'.',
						''
					) ?>
				</strong>
			</div>

		<?php endif; ?>

	</div>

	<div class="vn-checkout-summary__footer">

	<button
		type="submit"
		class="
			vn-checkout-summary__submit
			order_send
		"
		data-checkout-submit
		disabled
	>
		<span data-checkout-submit-text>
			<?= FINESH_CLEARANCE ?>
		</span>
	</button>

	<p
		class="vn-checkout-summary__submit-hint"
		data-checkout-submit-hint
	>
		<?= $isRomanian
			? 'Selectați metoda de livrare'
			: 'Выберите способ доставки' ?>
	</p>

		<div class="vn-checkout-summary__payments">

			<p class="vn-checkout-summary__payments-label">
				<?= $isRomanian
					? 'Metode de plată'
					: 'Способы оплаты' ?>
			</p>

			<div class="vn-checkout-summary__payment-icons">

				<div class="vn-checkout-summary__payment-icon">
					<img
						src="/app/img/checkout/google-pay.png"
						alt="Google Pay"
						loading="lazy"
						decoding="async"
					>
				</div>

				<div class="vn-checkout-summary__payment-icon">
					<img
						src="/app/img/checkout/apple-pay.png"
						alt="Apple Pay"
						loading="lazy"
						decoding="async"
					>
				</div>

				<div class="vn-checkout-summary__payment-icon">
					<img
						src="/app/img/checkout/mastercard.png"
						alt="Mastercard"
						loading="lazy"
						decoding="async"
					>
				</div>

				<div class="vn-checkout-summary__payment-icon">
					<img
						src="/app/img/checkout/visa.png"
						alt="Visa"
						loading="lazy"
						decoding="async"
					>
				</div>

			</div>

			<p class="vn-checkout-summary__secure">
				<?= $isRomanian
					? 'Plată sigură și protejată'
					: 'Безопасная и защищённая оплата' ?>
			</p>

		</div>

	</div>
</aside>
