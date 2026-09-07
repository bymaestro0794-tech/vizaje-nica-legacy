<?php
defined('BASEPATH') or exit('No direct script access allowed');

$pickupStores = array();

foreach ($stores as $index => $store) {
	$coords =
		!empty($store->coords)
			? explode(',', $store->coords)
			: array();

	$lat =
		isset($coords[0])
			? (float) trim($coords[0])
			: null;

	$lng =
		isset($coords[1])
			? (float) trim($coords[1])
			: null;

	if (
		$lat === null
		|| $lng === null
	) {
		continue;
	}

	$pickupStores[] =
		array(
			'id' =>
				(int) $store->id,

			'index' =>
				(int) $index,

			'number' =>
				str_pad(
					$index + 1,
					2,
					'0',
					STR_PAD_LEFT
				),

			'title' =>
				(string) $store->title,

			'address' =>
				(string) $store->text,

			'time' =>
				(string) $store->desc,

			'phone' =>
				(string) $store->phone,

			'lat' =>
				$lat,

			'lng' =>
				$lng,
		);
}
?>

<div
	class="checkout-pickup"
	data-checkout-pickup
	aria-hidden="true"
>
	<button
		type="button"
		class="checkout-pickup__backdrop"
		data-pickup-close
		aria-label="<?= $lclang === 'ro'
			? 'Închide'
			: 'Закрыть' ?>"
	></button>

	<div
		class="checkout-pickup__dialog"
		role="dialog"
		aria-modal="true"
		aria-label="<?= $lclang === 'ro'
			? 'Selectarea magazinului'
			: 'Выбор магазина' ?>"
	>
		<div
			class="checkout-pickup__map"
			id="checkoutPickupMap"
		></div>

		<aside class="checkout-pickup__panel">

			<button
				type="button"
				class="checkout-pickup__close"
				data-pickup-close
				aria-label="<?= $lclang === 'ro'
					? 'Închide'
					: 'Закрыть' ?>"
			>
				<span></span>
				<span></span>
			</button>

			<div class="checkout-pickup__panel-inner">

				<p class="checkout-pickup__eyebrow">
					<?= $lclang === 'ro'
						? 'Punct de ridicare'
						: 'Самовывоз' ?>
				</p>

				<div class="checkout-pickup__store-number">
					<span data-pickup-number>
						01
					</span>
				</div>

				<h2
					class="checkout-pickup__title"
					data-pickup-title
				></h2>

				<p
					class="checkout-pickup__address"
					data-pickup-address
				></p>

				<div class="checkout-pickup__meta">
					<div class="checkout-pickup__meta-row">
						<span>
							<?= $lclang === 'ro'
								? 'Cost'
								: 'Стоимость' ?>
						</span>

						<strong>
							<?= $lclang === 'ro'
								? 'Gratuit'
								: 'Бесплатно' ?>
						</strong>
					</div>

					<div class="checkout-pickup__meta-row">
						<span>
							<?= $lclang === 'ro'
								? 'Program'
								: 'График работы' ?>
						</span>

						<strong
							data-pickup-time
						></strong>
					</div>
				</div>

			</div>

			<div class="checkout-pickup__footer">
				<button
					type="button"
					class="checkout-pickup__confirm"
					data-pickup-confirm
				>
					<?= $lclang === 'ro'
						? 'Alege magazinul'
						: 'Выбрать магазин' ?>
				</button>
			</div>

		</aside>
	</div>

	<script
		type="application/json"
		data-checkout-pickup-stores
	><?= json_encode(
		$pickupStores,
		JSON_UNESCAPED_UNICODE
		| JSON_UNESCAPED_SLASHES
		| JSON_HEX_TAG
		| JSON_HEX_AMP
		| JSON_HEX_APOS
		| JSON_HEX_QUOT
	) ?></script>
</div>