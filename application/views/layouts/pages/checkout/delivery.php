<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian =
	isset($lclang)
	&& $lclang === 'ro';

$defaultStore =
	!empty($stores[0])
		? $stores[0]
		: null;

/*
 * Поменяй только эти пути
 * на реальные названия твоих иконок.
 */
$deliveryIcons = array(
	'pickup' =>
		'/app/img/checkout/store.png',

	'courier' =>
		'/app/img/checkout/courier.png',

	'express' =>
		'/app/img/checkout/express.png',
);
?>

<section
	class="
		vn-checkout-step
		vn-checkout-step--delivery
		is-open
	"
	data-checkout-step="delivery"
>
	<header
		class="vn-checkout-step__header"
		data-checkout-step-open="delivery"
	>
		<div class="vn-checkout-step__heading">
			<span class="vn-checkout-step__number">
				01 / 03
			</span>

			<h2 class="vn-checkout-step__title">
				<?= $isRomanian
					? 'Livrare'
					: 'Доставка' ?>
			</h2>
		</div>

		<button
			type="button"
			class="vn-checkout-step__edit"
			data-checkout-step-edit="delivery"
			hidden
		>
			<?= $isRomanian
				? 'Modifică'
				: 'Изменить' ?>
		</button>
	</header>

	<div
		class="vn-checkout-step__summary"
		data-checkout-step-summary="delivery"
		data-checkout-step-open="delivery"
		hidden
	>
		<p
			class="vn-checkout-step__summary-title"
			data-delivery-summary-title
		></p>

		<p
			class="vn-checkout-step__summary-text"
			data-delivery-summary-text
		></p>
	</div>

	<div
		class="vn-checkout-step__content"
		data-checkout-step-content
	>
		<div class="vn-checkout-delivery">

			<p class="vn-checkout-delivery__eyebrow">
				<?= $isRomanian
					? 'Metoda de livrare'
					: 'Способ доставки' ?>
			</p>

			<div class="vn-checkout-delivery__options">

				<!-- PICKUP -->
				<label class="vn-checkout-option">
					<input
						type="radio"
						name="delivery"
						value="1"
						class="vn-checkout-option__input"
						data-delivery-option
						checked
					>

					<span class="vn-checkout-option__content">

						<span class="vn-checkout-option__icon">
							<img
								src="<?= $deliveryIcons['pickup'] ?>"
								alt=""
								loading="eager"
								decoding="async"
							>
						</span>

						<span class="vn-checkout-option__body">
							<span class="vn-checkout-option__title">
								<?= $isRomanian
									? 'Ridicare din magazin'
									: 'Самовывоз из магазина' ?>
							</span>

							<span class="vn-checkout-option__description">
								<?= $isRomanian
									? 'Ridicare gratuită din magazinele Vizaje-Nica'
									: 'Бесплатно из магазинов Vizaje-Nica' ?>
							</span>
						</span>

						<span class="vn-checkout-option__side">
							<span class="vn-checkout-option__price">
								<?= $isRomanian
									? 'Gratuit'
									: 'Бесплатно' ?>
							</span>

							<span
								class="vn-checkout-option__check"
								aria-hidden="true"
							>
								<svg
									width="14"
									height="14"
									viewBox="0 0 14 14"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										d="M3 7.2L5.55 9.75L11 4.3"
										stroke="currentColor"
										stroke-width="1.5"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
								</svg>
							</span>
						</span>

					</span>
				</label>

				<!-- COURIER -->
				<label class="vn-checkout-option">
					<input
						type="radio"
						name="delivery"
						value="2"
						class="vn-checkout-option__input"
						data-delivery-option
					>

					<span class="vn-checkout-option__content">

						<span class="vn-checkout-option__icon">
							<img
								src="<?= $deliveryIcons['courier'] ?>"
								alt=""
								loading="eager"
								decoding="async"
							>
						</span>

						<span class="vn-checkout-option__body">
							<span class="vn-checkout-option__title">
								<?= $isRomanian
									? 'Livrare prin curier'
									: 'Курьерская доставка' ?>
							</span>

							<span class="vn-checkout-option__description">
								<?= $isRomanian
									? 'Livrare gratuită de la '
									: 'Бесплатно при заказе от ' ?>

								<?= (int) FREE_SHIPPING_VALUE ?>
								<?= MDL ?>
							</span>
						</span>

						<span class="vn-checkout-option__side">
							<span class="vn-checkout-option__price">
								<?php if (!empty($delivery_price)) : ?>

									<?= number_format(
										(float) $delivery_price,
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

							<span
								class="vn-checkout-option__check"
								aria-hidden="true"
							>
								<svg
									width="14"
									height="14"
									viewBox="0 0 14 14"
									fill="none"
								>
									<path
										d="M3 7.2L5.55 9.75L11 4.3"
										stroke="currentColor"
										stroke-width="1.5"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
								</svg>
							</span>
						</span>

					</span>
				</label>
			<?php if (!empty($express_delivery_enabled)) : ?>
				<!-- EXPRESS -->
				<label class="vn-checkout-option">
					<input
						type="radio"
						name="delivery"
						value="3"
						class="vn-checkout-option__input"
						data-delivery-option
					>

					<span class="vn-checkout-option__content">

						<span class="vn-checkout-option__icon">
							<img
								src="<?= $deliveryIcons['express'] ?>"
								alt=""
								loading="eager"
								decoding="async"
							>
						</span>

						<span class="vn-checkout-option__body">
							<span class="vn-checkout-option__title">
								<?= $isRomanian
									? 'Livrare expres'
									: 'Срочная доставка' ?>
							</span>

							<span class="vn-checkout-option__description">
								<?= $isRomanian
									? 'Livrare în aproximativ 3 ore'
									: 'Доставка примерно в течение 3 часов' ?>
							</span>
						</span>

						<span class="vn-checkout-option__side">
							<span class="vn-checkout-option__price">
								100 <?= MDL ?>
							</span>

							<span
								class="vn-checkout-option__check"
								aria-hidden="true"
							>
								<svg
									width="14"
									height="14"
									viewBox="0 0 14 14"
									fill="none"
								>
									<path
										d="M3 7.2L5.55 9.75L11 4.3"
										stroke="currentColor"
										stroke-width="1.5"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
								</svg>
							</span>
						</span>

					</span>
				</label>
			<?php endif; ?>
			</div>

			<?php if ($defaultStore) : ?>

					<div
						class="vn-checkout-delivery__pickup"
						data-delivery-pickup
						data-pickup-open
						role="button"
						tabindex="0"
						hidden
					>
					<div class="vn-checkout-delivery__pickup-head">

						<div class="vn-checkout-delivery__pickup-content">

							<p class="vn-checkout-delivery__label">
								<?= $isRomanian
									? 'Magazin selectat'
									: 'Выбранный магазин' ?>
							</p>

							<p
								class="
									vn-checkout-delivery__pickup-title
									stores_title
								"
								data-selected-store-title
							>
								<?= htmlspecialchars(
									(string) $defaultStore->title,
									ENT_QUOTES,
									'UTF-8'
								) ?>
							</p>

							<p
								class="
									vn-checkout-delivery__pickup-address
									stores_address
								"
								data-selected-store-address
							>
								<?= htmlspecialchars(
									(string) $defaultStore->text,
									ENT_QUOTES,
									'UTF-8'
								) ?>
							</p>

						</div>

						<button
							type="button"
							class="vn-checkout-delivery__change"
							data-pickup-open
						>
							<?= $isRomanian
								? 'Schimbă'
								: 'Изменить' ?>
						</button>

					</div>

					<input
						type="hidden"
						name="stores"
						class="stores_id"
						value="<?= (int) $defaultStore->id ?>"
					>
				</div>

			<?php endif; ?>

			<div
				class="vn-checkout-delivery__address"
				data-delivery-address
				hidden
			>
				<div class="vn-checkout-delivery__address-head">
					<p class="vn-checkout-delivery__label">
						<?= $isRomanian
							? 'Adresa de livrare'
							: 'Адрес доставки' ?>
					</p>
				</div>

				<div class="vn-checkout-fields">

					<label class="vn-checkout-field vn-checkout-field--wide">
						<span class="vn-checkout-field__label">
							<?= $isRomanian
								? 'Localitate'
								: 'Населённый пункт' ?>
						</span>

						<input
							type="text"
							name="city"
							class="vn-checkout-field__input"
							autocomplete="address-level2"
						>

						<p
							class="vn-checkout-field__error"
							data-delivery-field-error="city"
							hidden
						></p>
					</label>

					<label class="vn-checkout-field vn-checkout-field--wide">
						<span class="vn-checkout-field__label">
							<?= $isRomanian
								? 'Stradă'
								: 'Улица' ?>
						</span>

						<input
								type="text"
								name="address"
								class="vn-checkout-field__input"
								autocomplete="address-line1"
							>

							<p
								class="vn-checkout-field__error"
								data-delivery-field-error="address"
								hidden
							></p>
					</label>

					<label class="vn-checkout-field">
						<span class="vn-checkout-field__label">
								<?= $isRomanian
									? 'Casă'
									: 'Дом' ?>
							</span>

							<input
								type="text"
								name="house"
								class="vn-checkout-field__input"
							>

							<p
								class="vn-checkout-field__error"
								data-delivery-field-error="house"
								hidden
							></p>
						</label>

					<label class="vn-checkout-field">
							<span class="vn-checkout-field__label">
								<?= $isRomanian
									? 'Scară'
									: 'Подъезд' ?>
							</span>

							<input
								type="text"
								name="porch"
								class="vn-checkout-field__input"
							>

							<p
								class="vn-checkout-field__error"
								data-delivery-field-error="porch"
								hidden
							></p> 
						</label>

					<label class="vn-checkout-field">
						<span class="vn-checkout-field__label">
							<?= $isRomanian
								? 'Apartament'
								: 'Квартира' ?>
						</span>

						<input
							type="text"
							name="apartament"
							class="vn-checkout-field__input"
						>
					</label>

				</div>
			</div>

			<div class="vn-checkout-step__actions">
				<button
					type="button"
					class="vn-checkout-step__continue"
					data-checkout-delivery-continue
					disabled
				>
					<?= $isRomanian
						? 'Continuă'
						: 'Продолжить' ?>
				</button>
			</div>

			<p
				class="vn-checkout-step__error"
				data-delivery-error
				hidden
			></p>

		</div>
	</div>
</section>