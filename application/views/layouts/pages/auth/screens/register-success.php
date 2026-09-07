<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<section
	class="auth-drawer__screen"
	data-auth-screen="register-success"
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
					? 'Succes!'
					: 'Успешно!' ?>
			</h2>

			<p class="auth-drawer__description">
				<?= $isRomanian
					? 'Contul dvs. a fost creat cu succes.'
					: 'Ваш аккаунт успешно создан.' ?>
			</p>

		<a
        	href="/<?= htmlspecialchars(
        		$lclang,
        		ENT_QUOTES,
        		'UTF-8'
        	) ?>/"
        	class="auth-drawer__primary-button"
        >
        	<?= $isRomanian
        		? 'Continuă cumpărăturile'
        		: 'Перейти к покупкам' ?>
        </a>

			<a
				href="/<?= htmlspecialchars(
					$lclang,
					ENT_QUOTES,
					'UTF-8'
				) ?>/<?= !empty($menu['all'][15]->uri)
					? htmlspecialchars(
						trim(
							(string) $menu['all'][15]->uri,
							'/'
						),
						ENT_QUOTES,
						'UTF-8'
					)
					: '' ?>?cabinet"
				class="auth-drawer__status-link"
			>
				<?= $isRomanian
					? 'Accesează contul personal'
					: 'Перейти в личный кабинет' ?>
			</a>
		</div>
	</div>
</section>