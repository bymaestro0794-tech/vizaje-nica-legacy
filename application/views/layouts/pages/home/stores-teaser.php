<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = isset($lclang) && $lclang === 'ro';
$storesPageUri = !empty($menu['all'][7]->uri)
	? $menu['all'][7]->uri
	: ($isRomanian ? 'magazine' : 'magaziny');
$storesUrl = '/' . ($lclang ?? 'ru') . '/' . $storesPageUri;
$storesTitle = $isRomanian ? 'Magazinele noastre' : 'Наши магазины';
?>

<section class="home-stores" aria-labelledby="home-stores-title">
	<div class="home-editorial-marquee" aria-hidden="true">
		<p class="home-editorial-marquee__title"><?= $isRomanian ? 'MAGAZINE VIZAJE-NICA' : 'МАГАЗИНЫ VIZAJE-NICA' ?></p>
	</div>
	<div class="home-stores__container _container">
		<div class="home-stores__content">
			<h2 class="home-stores__title sr-only" id="home-stores-title">
				<?= htmlspecialchars($storesTitle, ENT_QUOTES, 'UTF-8') ?>
			</h2>

			<p class="home-stores__description">
				<?= $isRomanian
					? '12 magazine de frumusețe, aproape de tine. Te așteptăm să descoperi universul Vizaje-Nica.'
					: '12 магазинов красоты рядом с вами. Приходите знакомиться с миром Vizaje-Nica.' ?>
			</p>

			<a
				href="<?= htmlspecialchars($storesUrl, ENT_QUOTES, 'UTF-8') ?>"
				class="home-stores__link"
			>
				<span><?= $isRomanian ? 'Descoperă magazinele' : 'Найти магазин' ?></span>
			</a>
		</div>

		<a
			href="<?= htmlspecialchars($storesUrl, ENT_QUOTES, 'UTF-8') ?>"
			class="home-stores__image-link"
			aria-label="<?= htmlspecialchars($storesTitle, ENT_QUOTES, 'UTF-8') ?>"
		>
			<img
				src="/app/img/b2b/home-store.png"
				alt="<?= $isRomanian ? 'Interiorul unui magazin Vizaje-Nica' : 'Интерьер магазина Vizaje-Nica' ?>"
				loading="lazy"
				decoding="async"
			>
		</a>
	</div>
</section>
