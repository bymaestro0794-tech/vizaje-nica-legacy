<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<section
	class="auth-drawer__screen"
	data-auth-screen="register"
	aria-hidden="true"
>
	<div class="auth-drawer__main">
		<button
			type="button"
			class="auth-drawer__back"
			data-auth-open="login"
			data-auth-back
		>
			<span aria-hidden="true">←</span>

			<?= HAVE_ACCOUNT ?>
		</button>

		<header class="auth-drawer__header">
			<h2 class="auth-drawer__title">
				<?= htmlspecialchars(
					$registerTitle,
					ENT_QUOTES,
					'UTF-8'
				) ?>
			</h2>
		</header>

		<form
			action="<?= htmlspecialchars(
				$registerAction,
				ENT_QUOTES,
				'UTF-8'
			) ?>"
			method="post"
			class="auth-drawer__form register_form"
			novalidate
		>
			<div
				class="
					auth-drawer__message
					auth-drawer__message--error
					error_login
				"
				role="alert"
				aria-live="polite"
			></div>

			<div
				class="
					auth-drawer__message
					auth-drawer__message--success
					success_login
				"
				role="status"
				aria-live="polite"
			></div>

			<div class="auth-drawer__field _item-input">
				<label
					for="auth-register-number"
					class="auth-drawer__label"
				>
					<?= PHONE_NUMBER ?>
				</label>

				<div class="auth-phone">
					<div
						class="auth-phone__prefix"
						aria-label="Moldova +373"
					>
						<span>MD</span>
						<span>+373</span>
	
					</div>

				<input
					id="auth-register-number"
					type="text"
					name="number"
					class="
						auth-drawer__input
						auth-phone__input
					"
					inputmode="numeric"
					autocomplete="tel-national"
					placeholder="XX XXX XXX"
					maxlength="10"
					data-auth-phone-input
					required
				>
				</div>
			</div>

			<div class="auth-drawer__field _item-input">
				<label
					for="auth-register-email"
					class="auth-drawer__label"
				>
					<?= EMAIL_LOGIN ?>
				</label>

				<input
					id="auth-register-email"
					type="email"
					name="email"
					class="auth-drawer__input"
					autocomplete="email"
					placeholder="name@email.com"
					required
				>
			</div>

			<div class="auth-drawer__field _item-input">
				<label
					for="auth-register-password"
					class="auth-drawer__label"
				>
					<?= PASSWORD ?>
				</label>

				<div class="auth-password">
					<input
						id="auth-register-password"
						type="password"
						name="password"
						class="
							auth-drawer__input
							auth-password__input
						"
						autocomplete="new-password"
						required
					>

					<button
						type="button"
						class="auth-password__toggle"
						data-password-toggle
						aria-controls="auth-register-password"
						aria-pressed="false"
						aria-label="<?= $isRomanian
							? 'Afișează parola'
							: 'Показать пароль' ?>"
					>
						<svg
							width="18"
							height="18"
							viewBox="0 0 24 24"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M2.5 12C4.8 7.8 8 5.7 12 5.7C16 5.7 19.2 7.8 21.5 12C19.2 16.2 16 18.3 12 18.3C8 18.3 4.8 16.2 2.5 12Z"
								stroke="currentColor"
								stroke-width="1.4"
							/>

							<circle
								cx="12"
								cy="12"
								r="2.7"
								stroke="currentColor"
								stroke-width="1.4"
							/>
						</svg>
					</button>
				</div>
			</div>

			<div class="auth-drawer__field _item-input">
				<label
					for="auth-register-password-check"
					class="auth-drawer__label"
				>
					<?= PASSWORD_CHECK ?>
				</label>

				<div class="auth-password">
					<input
						id="auth-register-password-check"
						type="password"
						name="password_check"
						class="
							auth-drawer__input
							auth-password__input
						"
						autocomplete="new-password"
						required
					>

					<button
						type="button"
						class="auth-password__toggle"
						data-password-toggle
						aria-controls="auth-register-password-check"
						aria-pressed="false"
						aria-label="<?= $isRomanian
							? 'Afișează parola'
							: 'Показать пароль' ?>"
					>
						<svg
							width="18"
							height="18"
							viewBox="0 0 24 24"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M2.5 12C4.8 7.8 8 5.7 12 5.7C16 5.7 19.2 7.8 21.5 12C19.2 16.2 16 18.3 12 18.3C8 18.3 4.8 16.2 2.5 12Z"
								stroke="currentColor"
								stroke-width="1.4"
							/>

							<circle
								cx="12"
								cy="12"
								r="2.7"
								stroke="currentColor"
								stroke-width="1.4"
							/>
						</svg>
					</button>
				</div>
			</div>

			<div class="auth-drawer__checks">
				<label class="auth-drawer__check _focus">
					<input
						class="_focus"
						type="checkbox"
						value="1"
						name="register_check"
						required
					>

					<span class="auth-drawer__check-box"></span>

					<span class="auth-drawer__check-text">
						<?= AGREE_WITH ?>

						<a
							href="<?= htmlspecialchars(
								$privacyUrl,
								ENT_QUOTES,
								'UTF-8'
							) ?>"
							target="_blank"
							rel="noopener"
						>
							<?= htmlspecialchars(
								$privacyTitle,
								ENT_QUOTES,
								'UTF-8'
							) ?>
						</a>.
					</span>
				</label>

				<label class="auth-drawer__check _focus">
					<input
						class="_focus"
						type="checkbox"
						value="1"
						name="register_newsletters"
					>

					<span class="auth-drawer__check-box"></span>

					<span class="auth-drawer__check-text">
						<?= RECEIVE_PROMO_REG ?>
					</span>
				</label>
			</div>

			<button
				type="submit"
				class="
					auth-drawer__primary-button
					_sumbit
				"
			>
				<?= REGISTER ?>
			</button>

			<button
				type="button"
				class="auth-drawer__secondary-button"
				data-auth-open="login"
				data-auth-back
			>
				<?= HAVE_ACCOUNT ?>
			</button>
		</form>
	</div>
</section>