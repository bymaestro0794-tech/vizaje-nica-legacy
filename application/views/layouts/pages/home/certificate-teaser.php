<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = ($lclang ?? 'ru') === 'ro';
$certificateUrl = '/ru/katalog/sertifikatyi/107557';
?>

<section class="home-certificate" aria-labelledby="home-certificate-title">
	<div class="home-editorial-marquee" aria-hidden="true">
		<p class="home-editorial-marquee__title"><?= $isRomanian ? 'CARDURI CADOU' : 'ПОДАРОЧНЫЕ КАРТЫ' ?></p>
	</div>
	<div class="home-certificate__container _container">
		<div class="home-certificate__art" aria-hidden="true">
			<img
				class="home-certificate__image"
				src="/app/b2b/img/certificate-main.png"
				alt=""
				width="1240"
				height="1240"
				loading="lazy"
				decoding="async"
			>
		</div>
		<div class="home-certificate__content">
			<h2 class="home-certificate__title sr-only" id="home-certificate-title">
				<?= $isRomanian ? 'Dăruiți frumusețe' : 'Подарите красоту' ?>
			</h2>
			<p class="home-certificate__description">
				<?= $isRomanian
					? 'Alegeți un certificat cadou și bucurați-i pe cei dragi cu libertatea de a alege.'
					: 'Подарочный сертификат — простой способ порадовать близких и оставить им выбор.' ?>
			</p>
			<a class="home-certificate__link" href="<?= htmlspecialchars($certificateUrl, ENT_QUOTES, 'UTF-8') ?>">
				<?= $isRomanian ? 'Descoperă' : 'Выбрать сертификат' ?>
			</a>
		</div>
	</div>
</section>
