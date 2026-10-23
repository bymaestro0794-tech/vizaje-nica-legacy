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

$recipientPrivacyUrl = !empty($privacy_url) ? (string) $privacy_url : '';
$recipientPrivacyTitle = !empty($privacy_title) ? (string) $privacy_title : '';
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
				01 / 03
			</span>

			<h2 class="vn-checkout-step__title">
				<?= $isRomanian
					? 'Date de contact'
					: 'Контактные данные' ?>
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
	>
		<div class="vn-checkout-recipient">

			<div class="vn-checkout-fields vn-checkout-fields--recipient">

				<label class="vn-checkout-field">
					<span class="vn-checkout-field__label">
						<?= $isRomanian
							? 'Prenume'
							: 'Имя' ?>
					</span>

					<span class="vn-checkout-field__control">
						<span class="vn-checkout-field__icon" aria-hidden="true">
							<svg class="lucide lucide-user-round" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<circle cx="12" cy="8" r="5" />
								<path d="M20 21a8 8 0 0 0-16 0" />
							</svg>
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
					</span>

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

					<span class="vn-checkout-field__control">
						<span class="vn-checkout-field__icon" aria-hidden="true">
							<svg class="lucide lucide-user-round" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<circle cx="12" cy="8" r="5" />
								<path d="M20 21a8 8 0 0 0-16 0" />
							</svg>
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
					</span>

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
							<span class="vn-checkout-phone__icon" aria-hidden="true">
								<svg class="lucide lucide-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
									<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.2 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.35 1.9.68 2.8a2 2 0 0 1-.45 2.11L8.07 9.93a16 16 0 0 0 6 6l1.3-1.27a2 2 0 0 1 2.11-.45c.9.33 1.84.56 2.8.68A2 2 0 0 1 22 16.92Z" />
								</svg>
							</span>
						</span>

						<input
							id="checkoutPhone"
							type="tel"
							class="vn-checkout-phone__input"
							data-checkout-phone-input
							inputmode="tel"
							autocomplete="tel"
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

					<span class="vn-checkout-field__control">
						<span class="vn-checkout-field__icon" aria-hidden="true">
							<svg class="lucide lucide-mail" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<rect x="2" y="4" width="20" height="16" rx="2" />
								<path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
							</svg>
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
					</span>

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

					<span class="vn-checkout-field__control vn-checkout-field__control--textarea">
						<span class="vn-checkout-field__icon" aria-hidden="true">
							<svg class="lucide lucide-message-square" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
							</svg>
						</span>

						<textarea
							name="message"
							class="vn-checkout-field__textarea"
							rows="4"
							placeholder="<?= $isRomanian
								? 'De exemplu: sunați înainte de livrare'
								: 'Например: позвоните перед доставкой' ?>"
						></textarea>
					</span>
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

			<?php if ($recipientPrivacyUrl !== '') : ?>
				<p class="vn-checkout-privacy-note">
					<?= $isRomanian
						? 'Consultați și '
						: 'Также ознакомьтесь с ' ?>
					<a
						href="<?= htmlspecialchars($recipientPrivacyUrl, ENT_QUOTES, 'UTF-8') ?>"
						target="_blank"
						rel="noopener noreferrer"
					>
						<?= htmlspecialchars($recipientPrivacyTitle, ENT_QUOTES, 'UTF-8') ?>
					</a>.
				</p>
			<?php endif; ?>

			<p
				class="vn-checkout-step__error"
				data-recipient-error
				hidden
			></p>

		</div>
	</div>
</section>
