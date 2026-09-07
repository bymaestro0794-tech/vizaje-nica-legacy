<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian =
	isset($lclang)
	&& $lclang === 'ro';

$paymentIcons = array(
	'cash' =>
		'/app/img/checkout/payment-cash.png',

	'terminal' =>
		'/app/img/checkout/payment-terminal.png',

	'online' =>
		'/app/img/checkout/payment-online.png',
);
?>

<section
	class="vn-checkout-step vn-checkout-step--payment"
	data-checkout-step="payment"
>
	<header
		class="vn-checkout-step__header"
		data-checkout-step-open="payment"
	>
		<div class="vn-checkout-step__heading">
			<span class="vn-checkout-step__number">
				03 / 03
			</span>

			<h2 class="vn-checkout-step__title">
				<?= $isRomanian
					? 'Plată'
					: 'Оплата' ?>
			</h2>
		</div>

		<button
			type="button"
			class="vn-checkout-step__edit"
			data-checkout-step-edit="payment"
			hidden
		>
			<?= $isRomanian
				? 'Modifică'
				: 'Изменить' ?>
		</button>
	</header>

	<div
		class="vn-checkout-step__summary"
		data-checkout-step-summary="payment"
		data-checkout-step-open="payment"
		hidden
	>
		<p
			class="vn-checkout-step__summary-title"
			data-payment-summary-title
		></p>

		<p
			class="vn-checkout-step__summary-text"
			data-payment-summary-text
		></p>
	</div>

	<div
		class="vn-checkout-step__content"
		data-checkout-step-content
		hidden
	>
		<div class="vn-checkout-payment">

			<p class="vn-checkout-payment__eyebrow">
				<?= $isRomanian
					? 'Metoda de plată'
					: 'Способ оплаты' ?>
			</p>

			<div class="vn-checkout-payment__options">

				<!-- CASH -->
				<label class="vn-checkout-payment-option">
					<input
						type="radio"
						name="pay"
						value="1"
						class="vn-checkout-payment-option__input"
						data-payment-option
						checked
					>

					<span class="vn-checkout-payment-option__content">

						<span class="vn-checkout-payment-option__icon">
							<img
								src="<?= $paymentIcons['cash'] ?>"
								alt=""
								decoding="async"
							>
						</span>

						<span class="vn-checkout-payment-option__body">
							<span class="vn-checkout-payment-option__title">
								<?= $isRomanian
									? 'Numerar la primire'
									: 'Наличными при получении' ?>
							</span>

							<span class="vn-checkout-payment-option__description">
								<?= $isRomanian
									? 'Achitați comanda la primire'
									: 'Оплатите заказ при получении' ?>
							</span>
						</span>

						<span
							class="vn-checkout-payment-option__check"
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
				</label>

				<!-- CARD ON DELIVERY -->
				<label class="vn-checkout-payment-option">
					<input
						type="radio"
						name="pay"
						value="3"
						class="vn-checkout-payment-option__input"
						data-payment-option
					>

					<span class="vn-checkout-payment-option__content">

						<span class="vn-checkout-payment-option__icon">
							<img
								src="<?= $paymentIcons['terminal'] ?>"
								alt=""
								decoding="async"
							>
						</span>

						<span class="vn-checkout-payment-option__body">
							<span class="vn-checkout-payment-option__title">
								<?= $isRomanian
									? 'Cu cardul la curier'
									: 'Картой при получении' ?>
							</span>

							<span class="vn-checkout-payment-option__description">
								<?= $isRomanian
									? 'Plată prin terminal la primirea comenzii'
									: 'Оплата через терминал при получении заказа' ?>
							</span>
						</span>

						<span
							class="vn-checkout-payment-option__check"
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
				</label>

				<!-- ONLINE -->
				<label class="vn-checkout-payment-option">
					<input
						type="radio"
						name="pay"
						value="2"
						class="vn-checkout-payment-option__input"
						data-payment-option
					>

					<span class="vn-checkout-payment-option__content">

						<span class="vn-checkout-payment-option__icon">
							<img
								src="<?= $paymentIcons['online'] ?>"
								alt=""
								decoding="async"
							>
						</span>

						<span class="vn-checkout-payment-option__body">
							<span class="vn-checkout-payment-option__title">
								<?= $isRomanian
									? 'Plată online'
									: 'Оплата онлайн' ?>
							</span>

							<span class="vn-checkout-payment-option__description">
								<?= $isRomanian
									? 'Plată securizată online'
									: 'Безопасная онлайн-оплата' ?>
							</span>
						</span>

						<span
							class="vn-checkout-payment-option__check"
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
				</label>

			</div>

			<div class="vn-checkout-step__actions">
				<button
					type="button"
					class="vn-checkout-step__continue"
					data-checkout-payment-continue
					disabled
				>
					<?= $isRomanian
						? 'Continuă'
						: 'Продолжить' ?>
				</button>
			</div>

			<p
				class="vn-checkout-step__error"
				data-payment-error
				hidden
			></p>

		</div>
	</div>
</section>