<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian =
	isset($lclang)
	&& $lclang === 'ro';

$recipientName =
	!empty($client_info)
	&& !empty($client_info->name)
		? (string) $client_info->name
		: '';

$recipientSurname =
	!empty($client_info)
	&& !empty($client_info->surname)
		? (string) $client_info->surname
		: '';

$recipientEmail =
	!empty($client_info)
	&& !empty($client_info->email)
		? (string) $client_info->email
		: '';

$recipientPhone =
	!empty($client_info)
	&& !empty($client_info->phone)
		? (string) $client_info->phone
		: '';
?>

<section
	class="vn-checkout-step vn-checkout-step--recipient"
	data-checkout-step="recipient"
>
	<header
		class="vn-checkout-step__header"
		data-checkout-step-open="recipient"
	>
		<div class="vn-checkout-step__heading">
			<span class="vn-checkout-step__number">
				02 / 03
			</span>

			<h2 class="vn-checkout-step__title">
				<?= $isRomanian
					? 'Destinatar'
					: 'Получатель' ?>
			</h2>
		</div>

		<button
			type="button"
			class="vn-checkout-step__edit"
			data-checkout-step-edit="recipient"
			hidden
		>
			<?= $isRomanian
				? 'Modifică'
				: 'Изменить' ?>
		</button>
	</header>

	<div
		class="vn-checkout-step__summary"
		data-checkout-step-summary="recipient"
		data-checkout-step-open="recipient"
		hidden
	>
		<p
			class="vn-checkout-step__summary-title"
			data-recipient-summary-name
		></p>

		<p
			class="vn-checkout-step__summary-text"
			data-recipient-summary-contact
		></p>
	</div>

	<div
		class="vn-checkout-step__content"
		data-checkout-step-content
		hidden
	>
		<div class="vn-checkout-recipient">

			<div class="vn-checkout-fields vn-checkout-fields--recipient">

				<label class="vn-checkout-field">
					<span class="vn-checkout-field__label">
						<?= $isRomanian
							? 'Prenume'
							: 'Имя' ?>
					</span>

					<input
						type="text"
						name="name"
						value="<?= htmlspecialchars(
							$recipientName,
							ENT_QUOTES,
							'UTF-8'
						) ?>"
						class="vn-checkout-field__input"
						autocomplete="given-name"
						data-recipient-input
					>

					<p
						class="vn-checkout-field__error"
						data-name-error
						hidden
					></p>
				</label>

				<label class="vn-checkout-field">
					<span class="vn-checkout-field__label">
						<?= $isRomanian
							? 'Nume'
							: 'Фамилия' ?>
					</span>

					<input
						type="text"
						name="surname"
						value="<?= htmlspecialchars(
							$recipientSurname,
							ENT_QUOTES,
							'UTF-8'
						) ?>"
						class="vn-checkout-field__input"
						autocomplete="family-name"
						data-recipient-input
					>

					<p
						class="vn-checkout-field__error"
						data-surname-error
						hidden
					></p>
				</label>

				<div
					class="vn-checkout-phone vn-checkout-field--wide"
					data-checkout-phone
				>
					<label
						class="vn-checkout-field__label"
						for="checkoutPhone"
					>
						<?= $isRomanian
							? 'Telefon'
							: 'Телефон' ?>
					</label>

					<div class="vn-checkout-phone__control">
						<span class="vn-checkout-phone__prefix">
							+373
						</span>

						<input
							id="checkoutPhone"
							type="tel"
							class="vn-checkout-phone__input"
							data-checkout-phone-input
							inputmode="numeric"
							autocomplete="tel-national"
							placeholder="<?= $isRomanian
							? 'Introduceți numărul de telefon'
							: 'Введите ваш телефон' ?>"
						>
					</div>

					<input
						type="hidden"
						name="phone"
						value="<?= htmlspecialchars(
							$recipientPhone,
							ENT_QUOTES,
							'UTF-8'
						) ?>"
						data-checkout-phone-value
					>

					<p
						class="vn-checkout-phone__confirm"
						data-checkout-phone-confirm
						hidden
					></p>

					<p
						class="vn-checkout-phone__error"
						data-checkout-phone-error
						hidden
					></p>
				</div>

				<label class="vn-checkout-field vn-checkout-field--wide">
					<span class="vn-checkout-field__label">
						Email
					</span>

					<input
						type="email"
						name="email"
						value="<?= htmlspecialchars(
							$recipientEmail,
							ENT_QUOTES,
							'UTF-8'
						) ?>"
						class="vn-checkout-field__input"
						autocomplete="email"
						data-recipient-input
						placeholder="<?= $isRomanian
							? 'Introduceți emailul dvs.'
							: 'Введите ваш email' ?>"
					>

					<p
						class="vn-checkout-field__error"
						data-email-error
						hidden
					></p>
				</label>

				<label class="vn-checkout-field vn-checkout-field--wide">
					<span class="vn-checkout-field__label">
						<?= $isRomanian
							? 'Comentariu la comandă'
							: 'Комментарий к заказу' ?>
					</span>

					<textarea
						name="message"
						class="vn-checkout-field__textarea"
						rows="4"
						placeholder="<?= $isRomanian
							? 'De exemplu: sunați înainte de livrare'
							: 'Например: позвоните перед доставкой' ?>"
					></textarea>
				</label>

			</div>

			<div class="vn-checkout-recipient__checks">

				<label class="vn-checkout-check">
					<input
						type="checkbox"
						name="checkbox"
						value="1"
						class="vn-checkout-check__input"
						data-recipient-required-check
					>

					<span class="vn-checkout-check__box"></span>

					<span class="vn-checkout-check__text">
						<?= $isRomanian
							? 'Sunt de acord cu termenii și condițiile.'
							: 'Я согласен с условиями и правилами.' ?>
					</span>
				</label>

				<p
					class="vn-checkout-check__error"
					data-recipient-terms-error
					hidden
				></p>

				<label class="vn-checkout-check">
					<input
						type="checkbox"
						name="newsletters"
						value="1"
						class="vn-checkout-check__input"
					>

					<span class="vn-checkout-check__box"></span>

					<span class="vn-checkout-check__text">
						<?= $isRomanian
							? 'Doresc să primesc noutăți și promoții.'
							: 'Получать новости и специальные предложения.' ?>
					</span>
				</label>

			</div>

			<div class="vn-checkout-step__actions">
				<button
					type="button"
					class="vn-checkout-step__continue"
					data-checkout-recipient-continue
					disabled
				>
					<?= $isRomanian
						? 'Continuă'
						: 'Продолжить' ?>
				</button>
			</div>

			<p
				class="vn-checkout-step__error"
				data-recipient-error
				hidden
			></p>

		</div>
	</div>
</section>