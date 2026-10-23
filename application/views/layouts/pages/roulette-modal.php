<?php
defined('BASEPATH') or exit('No direct script access allowed');

$rouletteIsRomanian = strtolower(trim((string) ($lclang ?? 'ru'))) === 'ro';
$rouletteLanguage = $rouletteIsRomanian ? 'ro' : 'ru';
$rouletteAccountScope = !empty($client_info->id)
	? hash('sha256', 'vn-roulette:' . (int) $client_info->id)
	: '';
?>
<div
	class="roulette-modal"
	data-roulette-modal
	data-language="<?= $rouletteLanguage ?>"
	data-state-url="/<?= $rouletteLanguage ?>/roulette/state"
	data-spin-url="/<?= $rouletteLanguage ?>/roulette/spin"
	data-account-scope="<?= htmlspecialchars($rouletteAccountScope, ENT_QUOTES, 'UTF-8') ?>"
	aria-hidden="true"
	inert
>
	<button
		type="button"
		class="roulette-modal__backdrop"
		data-roulette-close
		aria-label="<?= $rouletteIsRomanian ? 'Închide ruleta' : 'Закрыть рулетку' ?>"
	></button>

	<section
		class="roulette-modal__dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="roulette-modal-title"
		aria-describedby="roulette-modal-description"
		tabindex="-1"
	>
		<button
			type="button"
			class="roulette-modal__close"
			data-roulette-close
			aria-label="<?= $rouletteIsRomanian ? 'Închide' : 'Закрыть' ?>"
		>
			<span class="roulette-modal__close-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></span>
		</button>

		<div class="roulette-modal__copy">
			<img
				class="roulette-modal__logo"
				src="/app/img/logo_v.webp"
				alt="VIZAJE-NICA"
				width="180"
				height="40"
			>

			<h2 id="roulette-modal-title" data-roulette-title>
				<?= $rouletteIsRomanian ? 'Roata norocului' : 'Рулетка удачи' ?>
			</h2>
			<p id="roulette-modal-description" class="roulette-modal__description" data-roulette-description>
				<?= $rouletteIsRomanian
					? 'Învârte roata și află ce reducere ți se potrivește. Premiul este ales și salvat în contul tău.'
					: 'Крутите колесо и узнайте, какая скидка вам выпадет. Приз определяется сервером и сохраняется в вашем аккаунте.' ?>
			</p>

			<div class="roulette-modal__mascot" aria-hidden="true">
				<img src="/app/img/roulette-vizi.png" alt="" width="180" height="180" loading="lazy">
			</div>

			<div class="roulette-modal__result" data-roulette-result aria-live="polite" hidden>
				<p class="roulette-modal__result-label" data-roulette-result-label>
					<?= $rouletteIsRomanian ? 'Premiul tău' : 'Ваш приз' ?>
				</p>
				<strong class="roulette-modal__result-title" data-roulette-result-title></strong>
				<p class="roulette-modal__result-expiry">
					<?= $rouletteIsRomanian ? 'Valabil încă' : 'Действует ещё' ?>
					<time data-roulette-countdown>24:00:00</time>
				</p>
				<button type="button" class="roulette-modal__apply" data-roulette-apply>
					<?= $rouletteIsRomanian ? 'Aplică premiul' : 'Применить приз' ?>
				</button>
				<a class="roulette-modal__shop" href="/<?= $rouletteLanguage ?>/catalog" data-roulette-shop>
					<?= $rouletteIsRomanian ? 'Mergi la cumpărături' : 'Перейти к покупкам' ?>
					<span class="roulette-modal__arrow-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
				</a>
			</div>

			<button type="button" class="roulette-modal__spin" data-roulette-spin>
				<span data-roulette-spin-label><?= $rouletteIsRomanian ? 'Învârte roata' : 'Крутить' ?></span>
				<span class="roulette-modal__arrow-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
			</button>
			<p class="roulette-modal__error" data-roulette-error role="alert" hidden></p>

			<button
				type="button"
				class="roulette-modal__rules-toggle"
				data-roulette-rules-toggle
				aria-expanded="false"
			>
				<?= $rouletteIsRomanian ? 'Regulament' : 'Правила' ?>
			</button>
			<div class="roulette-modal__rules" data-roulette-rules hidden>
				<?= $rouletteIsRomanian
					? 'O rotire este disponibilă de luni, ora 00:00, până luni următoare la ora 00:00. Premiul este valabil 24 de ore. MAC, Anastasia Beverly Hills (ABH) și SHIK sunt excluse.'
					: 'Одна прокрутка доступна с понедельника 00:00 до следующего понедельника 00:00. Приз действует 24 часа. MAC, Anastasia Beverly Hills (ABH) и SHIK исключены.' ?>
			</div>
		</div>

		<div class="roulette-modal__wheel-column">
			<div class="roulette-wheel" data-roulette-wheel role="img" aria-label="<?= $rouletteIsRomanian ? 'Roata premiilor' : 'Колесо призов' ?>">
				<span class="roulette-wheel__pointer" aria-hidden="true"></span>
				<div class="roulette-wheel__disc" data-roulette-disc>
					<div class="roulette-wheel__labels" data-roulette-labels></div>
				</div>
				<div class="roulette-wheel__hub" aria-hidden="true">
					<img src="/app/img/mini-logo.svg" alt="" width="40" height="40">
				</div>
			</div>
			<p class="roulette-modal__wheel-caption" data-roulette-wheel-caption hidden aria-hidden="true"></p>
		</div>
	</section>
</div>
