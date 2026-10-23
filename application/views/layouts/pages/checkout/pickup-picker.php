<?php
defined('BASEPATH') or exit('No direct script access allowed');

$pickupStores = array();

foreach ($stores as $index => $store) {
	$coords =
		!empty($store->coords)
			? explode(',', $store->coords)
			: array();

	$lat =
		isset($coords[0]) && is_numeric(trim($coords[0]))
			? (float) trim($coords[0])
			: null;

	$lng =
		isset($coords[1]) && is_numeric(trim($coords[1]))
			? (float) trim($coords[1])
			: null;

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
		<header class="checkout-pickup__header">
			<div>
				<p class="checkout-pickup__eyebrow">
					<?= $lclang === 'ro' ? 'Ridicare din magazin' : 'Самовывоз' ?>
				</p>
				<h2 class="checkout-pickup__title">
					<?= $lclang === 'ro' ? 'Alegeți magazinul' : 'Выберите магазин' ?>
				</h2>
			</div>
			<button
				type="button"
				class="checkout-pickup__close"
				data-pickup-close
				aria-label="<?= $lclang === 'ro' ? 'Închide' : 'Закрыть' ?>"
			>
				<span></span><span></span>
			</button>
		</header>

		<div class="checkout-pickup__list" data-pickup-list></div>

		<footer class="checkout-pickup__footer">
			<button
				type="button"
				class="checkout-pickup__confirm"
				data-pickup-confirm
			>
				<?= $lclang === 'ro' ? 'Alege magazinul' : 'Выбрать магазин' ?>
			</button>
		</footer>
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
