<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<section
	class="auth-drawer__screen"
	data-auth-screen="password-success"
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
					<path
						d="M7 15.5L12.5 21L23 9"
						stroke="currentColor"
						stroke-width="1.5"
						stroke-linecap="round"
						stroke-linejoin="round"
					/>
				</svg>
			</div>

			<h2 class="auth-drawer__title">
				<?= $isRomanian
					? 'Parola a fost schimbată'
					: 'Пароль изменён' ?>
			</h2>

			<p class="auth-drawer__description">
				<?= $isRomanian
					? 'Parola dvs. a fost schimbată cu succes.'
					: 'Ваш пароль успешно изменён.' ?>
			</p>

			<button
				type="button"
				class="auth-drawer__primary-button"
				data-auth-open="login"
				data-auth-back
			>
				<?= $isRomanian
					? 'Autentificare'
					: 'Войти' ?>
			</button>
		</div>
	</div>
</section>