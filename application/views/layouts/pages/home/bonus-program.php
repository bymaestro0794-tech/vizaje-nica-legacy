<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = ($lclang ?? 'ru') === 'ro';
$isAuthenticated = !empty($is_authenticated);
$accountUri = !empty($menu['all'][15]->uri)
	? trim((string) $menu['all'][15]->uri, '/')
	: ($isRomanian ? 'contul-meu' : 'lichnyiy-kabinet');
$bonusUrl = '/' . ($lclang ?? 'ru') . '/' . $accountUri . '?bonus';
$bonusTitle = $isRomanian ? 'Program de bonusuri' : 'Бонусная программа';
?>

<section class="home-bonus" aria-labelledby="home-bonus-title">
	<div class="home-editorial-marquee" aria-hidden="true">
		<p class="home-editorial-marquee__title">
			<?= $isRomanian ? 'PROGRAM DE BONUSURI' : 'БОНУСНАЯ ПРОГРАММА' ?>
		</p>
	</div>
	<div class="home-bonus__container _container">
		<div class="home-bonus__art" aria-hidden="true">
			<img
				class="home-bonus__image"
				src="/app/img/bonus-program-section.png"
				alt=""
				loading="lazy"
				decoding="async"
			>
		</div>
		<div class="home-bonus__content">
			<h2 class="home-bonus__title sr-only" id="home-bonus-title">
				<?= htmlspecialchars($bonusTitle, ENT_QUOTES, 'UTF-8') ?>
			</h2>
			<p class="home-bonus__description">
				<?= $isRomanian
					? 'Alăturați-vă programului de bonusuri Vizaje-Nica: acumulați bonusuri la cumpărături, folosiți-le la comenzi și bucurați-vă de beneficii.'
					: 'Присоединяйтесь к бонусной программе Vizaje-Nica: копите бонусы за покупки, используйте их при оформлении заказов и получайте больше выгод.' ?>
			</p>
			<?php if ($isAuthenticated) : ?>
				<a
					class="home-bonus__link"
					href="<?= htmlspecialchars($bonusUrl, ENT_QUOTES, 'UTF-8') ?>"
				>
					<?= $isRomanian ? 'Vreau în club' : 'Хочу в клуб' ?>
				</a>
			<?php else : ?>
				<button
					type="button"
					class="home-bonus__link"
					data-auth-trigger="register"
				>
					<?= $isRomanian ? 'Vreau în club' : 'Хочу в клуб' ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
</section>
