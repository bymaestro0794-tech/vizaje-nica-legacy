<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<section
	class="auth-drawer__screen"
	data-auth-screen="forgot-sent"
	aria-hidden="true"
>
	<div class="auth-drawer__main">
		<div class="auth-drawer__status">
			<div
				class="auth-drawer__status-icon"
				aria-hidden="true"
			>
				<svg
					width="48"
					height="48"
					viewBox="0 0 30 30"
					fill="none"
				>
					<path
						d="M7 15.5L12.5 21L23 9"
						stroke="currentColor"
						stroke-width="2"
						stroke-linecap="round"
						stroke-linejoin="round"
					/>
				</svg>
			</div>

			<h2 class="auth-drawer__title">
				<?= $isRomanian
					? 'Verifică emailul'
					: 'Проверьте почту' ?>
			</h2>

			<p class="auth-drawer__description">
				<?= $isRomanian
					? 'Am trimis linkul pentru resetarea parolei. Deschide emailul și urmează instrucțiunile.'
					: 'Мы отправили ссылку для сброса пароля. Откройте письмо и следуйте инструкции.' ?>
			</p>

			<button
				type="button"
				class="auth-drawer__primary-button"
				data-auth-open="login"
				data-auth-back
			>
				<?= $isRomanian
					? 'Înapoi la autentificare'
					: 'Перейти ко входу' ?>
			</button>
		</div>
	</div>
</section>
