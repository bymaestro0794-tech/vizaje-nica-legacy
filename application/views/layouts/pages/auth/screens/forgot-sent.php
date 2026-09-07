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
					width="30"
					height="30"
					viewBox="0 0 30 30"
					fill="none"
				>
					<rect
						x="4.5"
						y="7.5"
						width="21"
						height="16"
						rx="1"
						stroke="currentColor"
					/>

					<path
						d="M5.5 9L15 16L24.5 9"
						stroke="currentColor"
						stroke-linecap="round"
						stroke-linejoin="round"
					/>
				</svg>
			</div>

			<h2 class="auth-drawer__title">
				<?= $isRomanian
					? 'Mesaj trimis'
					: 'Письмо отправлено' ?>
			</h2>

			<p class="auth-drawer__description">
				<?= $isRomanian
					? 'Am trimis instrucțiunile de resetare a parolei la adresa dvs. de email.'
					: 'Мы отправили инструкции по сбросу пароля на ваш email.' ?>
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