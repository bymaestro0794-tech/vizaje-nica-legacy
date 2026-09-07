<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<section
	class="auth-drawer__screen"
	data-auth-screen="sms-code"
	aria-hidden="true"
>
	<div class="auth-drawer__main">
		<button
			type="button"
			class="auth-drawer__back"
			data-auth-open="register"
			data-auth-back
		>
			<span aria-hidden="true">←</span>

			<?= BACK ?>
		</button>

		<header class="auth-drawer__header">
			<h2 class="auth-drawer__title">
				<?= $isRomanian
					? 'Cod din SMS'
					: 'Код из SMS' ?>
			</h2>

			<p class="auth-drawer__description">
				<?= $isRomanian
					? 'Am trimis codul la numărul'
					: 'Мы отправили код на номер' ?>

				<strong data-auth-phone-preview>
					+373 6XX XXX XXX
				</strong>
			</p>
		</header>

				<form
			action="<?= htmlspecialchars(
				$codeAction,
				ENT_QUOTES,
				'UTF-8'
			) ?>"
			method="post"
			class="auth-drawer__form login_formcode"
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

			<input
				type="hidden"
				name="number"
				data-auth-code-phone
			>

			<div
				class="auth-code"
				data-auth-code
			>
				<div class="auth-code__inputs">
					<?php for ($index = 0; $index < 6; $index++) : ?>
						<input
							type="text"
							class="auth-code__input"
							inputmode="numeric"
							autocomplete="<?= $index === 0
								? 'one-time-code'
								: 'off' ?>"
							maxlength="1"
							pattern="[0-9]*"
							aria-label="<?= $isRomanian
								? 'Cifra codului '
								: 'Цифра кода ' ?><?= $index + 1 ?>"
						>
					<?php endfor; ?>
				</div>

				<input
					type="hidden"
					name="password"
					data-auth-code-value
					required
				>

				<p class="auth-code__timer">
					<?= $isRomanian
						? 'Retrimite codul peste'
						: 'Отправить код повторно через' ?>

					<strong data-auth-code-timer>
						00:45
					</strong>
				</p>

				<button
					type="button"
					class="auth-code__resend"
					data-auth-code-resend
					disabled
				>
					<?= $isRomanian
						? 'Retrimite codul'
						: 'Отправить код повторно' ?>
				</button>
			</div>

			<button
				type="submit"
				class="auth-drawer__primary-button"
			>
				<?= $isRomanian
					? 'Confirmă'
					: 'Подтвердить' ?>
			</button>
		</form>
	</div>
</section>