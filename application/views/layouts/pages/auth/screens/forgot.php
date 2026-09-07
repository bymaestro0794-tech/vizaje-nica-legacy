<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<section
	class="auth-drawer__screen"
	data-auth-screen="forgot"
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
					? 'Ați uitat parola?'
					: 'Забыли пароль?' ?>
			</h2>

			<p class="auth-drawer__description">
				<?= RESET_PASS_INFO ?>
			</p>
		</header>

		<form
			action="<?= htmlspecialchars(
				$forgotAction,
				ENT_QUOTES,
				'UTF-8'
			) ?>"
			method="post"
			class="auth-drawer__form reset_form"
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

			<div
				class="
					auth-drawer__message
					auth-drawer__message--success
					success_reset
				"
				role="status"
				aria-live="polite"
			></div>

			<div class="auth-drawer__field _item-input">
				<label
					for="auth-forgot-email"
					class="auth-drawer__label"
				>
					<?= EMAIL_LOGIN ?>
				</label>

				<input
					id="auth-forgot-email"
					type="email"
					name="email"
					class="auth-drawer__input"
					autocomplete="email"
					placeholder="name@email.com"
					required
				>
			</div>

			<button
				type="submit"
				class="
					auth-drawer__primary-button
					_sumbit
					reset_send
				"
			>
				<?= SEND ?>
			</button>

			<button
				type="button"
				class="auth-drawer__text-link"
				data-auth-open="login"
				data-auth-back
			>
				<?= $isRomanian
					? 'Mi-am amintit parola'
					: 'Я вспомнил пароль' ?>
			</button>
		</form>
	</div>
</section>