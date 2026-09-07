<?php
defined('BASEPATH') or exit('No direct script access allowed');

$resetToken = isset($reset_token)
	? trim((string) $reset_token)
	: '';

$resetEmail = isset($reset_email)
	? trim((string) $reset_email)
	: '';
?>

<section
	class="auth-drawer__screen"
	data-auth-screen="new-password"
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

			<?= BACK ?>
		</button>

		<header class="auth-drawer__header">
			<h2 class="auth-drawer__title">
				<?= $isRomanian
					? 'Parolă nouă'
					: 'Новый пароль' ?>
			</h2>
		</header>

		<form
			action="<?= htmlspecialchars(
				$forgotAction,
				ENT_QUOTES,
				'UTF-8'
			) ?>"
			method="post"
			class="auth-drawer__form new_password_form"
			data-auth-new-password-form
			novalidate
		>
			<div
				class="
					auth-drawer__message
					auth-drawer__message--error
					error_reset
				"
				role="alert"
				aria-live="polite"
			></div>

			<input
				type="hidden"
				name="token"
				value="<?= htmlspecialchars(
					$resetToken,
					ENT_QUOTES,
					'UTF-8'
				) ?>"
			>

			<input
				type="hidden"
				name="email"
				value="<?= htmlspecialchars(
					$resetEmail,
					ENT_QUOTES,
					'UTF-8'
				) ?>"
			>

			<div class="auth-drawer__field _item-input">
				<label
					for="auth-new-password"
					class="auth-drawer__label"
				>
					<?= $isRomanian
						? 'Parolă nouă'
						: 'Новый пароль' ?>
				</label>

				<div class="auth-password">
					<input
						id="auth-new-password"
						type="password"
						name="password"
						class="
							auth-drawer__input
							auth-password__input
						"
						autocomplete="new-password"
						placeholder="<?= $isRomanian
							? 'Introduceți parola nouă'
							: 'Введите новый пароль' ?>"
						required
					>

					<button
						type="button"
						class="auth-password__toggle"
						data-password-toggle
						aria-controls="auth-new-password"
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
					for="auth-new-password-check"
					class="auth-drawer__label"
				>
					<?= $isRomanian
						? 'Repetați parola'
						: 'Повторите пароль' ?>
				</label>

				<div class="auth-password">
					<input
						id="auth-new-password-check"
						type="password"
						name="password_check"
						class="
							auth-drawer__input
							auth-password__input
						"
						autocomplete="new-password"
						placeholder="<?= $isRomanian
							? 'Repetați parola'
							: 'Повторите пароль' ?>"
						required
					>

					<button
						type="button"
						class="auth-password__toggle"
						data-password-toggle
						aria-controls="auth-new-password-check"
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

			<button
				type="submit"
				class="auth-drawer__primary-button"
			>
				<?= $isRomanian
					? 'Salvează parola'
					: 'Сохранить пароль' ?>
			</button>
		</form>
	</div>
</section>