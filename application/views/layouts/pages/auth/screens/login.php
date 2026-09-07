<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<section
	class="auth-drawer__screen is-active"
	data-auth-screen="login"
	aria-hidden="false"
>
	<div class="auth-drawer__main">
		<header class="auth-drawer__header">
			<h2 class="auth-drawer__title">
				<?= $isRomanian
					? 'Intră în cont'
					: 'Войти в личный кабинет' ?>
			</h2>
		</header>

		<form
			action="<?= htmlspecialchars(
				$loginAction,
				ENT_QUOTES,
				'UTF-8'
			) ?>"
			method="post"
			class="auth-drawer__form login_form"
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

			<div class="auth-drawer__field _item-input">
				<label
					for="auth-login-number"
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
						id="auth-login-number"
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
					for="auth-login-password"
					class="auth-drawer__label"
				>
					<?= PASSWORD ?>
				</label>

				<div class="auth-password">
					<input
						id="auth-login-password"
						type="password"
						name="password"
						class="
							auth-drawer__input
							auth-password__input
						"
						autocomplete="current-password"
						required
					>

					<button
						type="button"
						class="auth-password__toggle"
						data-password-toggle
						aria-controls="auth-login-password"
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

			<div class="auth-drawer__form-meta">
				<button
					type="button"
					class="auth-drawer__text-button"
					data-auth-open="forgot"
				>
					<?= FORGOT_YOUR_PASS ?>
				</button>
			</div>

			<button
				type="submit"
				class="auth-drawer__primary-button"
			>
				<?= htmlspecialchars(
					$loginTitle,
					ENT_QUOTES,
					'UTF-8'
				) ?>
			</button>

			<button
				type="button"
				class="auth-drawer__secondary-button"
				data-auth-guest-checkout
			>
				<?= CONTINUE_NO_REGISTRATION ?>
			</button>
			<p class="auth-drawer__switch">
				<span>
					<?= $isRomanian
						? 'Nu aveți cont?'
						: 'Нет аккаунта?' ?>
				</span>

				<button
					type="button"
					data-auth-open="register"
				>
					<?= $isRomanian
						? 'Înregistrați-vă'
						: 'Зарегистрироваться' ?>
				</button>
			</p>
		</form>
	</div>
</section>