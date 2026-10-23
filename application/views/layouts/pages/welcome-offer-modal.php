<?php
defined('BASEPATH') or exit('No direct script access allowed');

$welcomeOfferIsRomanian = strtolower(trim((string) ($lclang ?? 'ru'))) === 'ro';
?>
<div
	class="welcome-offer"
	data-welcome-offer
	data-language="<?= $welcomeOfferIsRomanian ? 'ro' : 'ru' ?>"
	aria-hidden="true"
>
	<button
		type="button"
		class="welcome-offer__overlay"
		data-welcome-offer-close
		aria-label="<?= $welcomeOfferIsRomanian ? 'Închide' : 'Закрыть' ?>"
	></button>

	<section
		class="welcome-offer__dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="welcome-offer-title"
		aria-describedby="welcome-offer-description"
	>
		<figure class="welcome-offer__image">
			<img
				src="/app/b2b/img/18-percent.png"
				alt="<?= $welcomeOfferIsRomanian ? 'Cadou cu reducere de 18%' : 'Подарок со скидкой 18%' ?>"
				width="1254"
				height="1254"
				loading="eager"
				fetchpriority="high"
			>
		</figure>
		<button
			type="button"
			class="welcome-offer__close"
			data-welcome-offer-close
			aria-label="<?= $welcomeOfferIsRomanian ? 'Închide' : 'Закрыть' ?>"
		>
			<span aria-hidden="true">×</span>
		</button>

		<div class="welcome-offer__content">
			<img
				class="welcome-offer__logo"
				src="/app/img/logo_v.webp"
				alt="VIZAJE-NICA"
				width="240"
				height="52"
			>

			<h2 id="welcome-offer-title" class="welcome-offer__title">
				<span class="welcome-offer__discount">−18%</span>
				<span class="welcome-offer__title-caption">
					<?= $welcomeOfferIsRomanian ? 'LA PRIMA COMANDĂ' : 'НА ПЕРВЫЙ ЗАКАЗ' ?>
				</span>
			</h2>

			<p id="welcome-offer-description" class="welcome-offer__description">
				<?= $welcomeOfferIsRomanian
					? 'Înregistrează-te — reducerea va apărea automat în coș după confirmarea numărului de telefon.'
					: 'Зарегистрируйтесь — скидка автоматически появится в корзине после подтверждения номера телефона.' ?>
			</p>

			<div class="welcome-offer__terms">
				<strong><?= $welcomeOfferIsRomanian
					? '14 zile de la înregistrare'
					: '14 дней после регистрации' ?></strong>
				<span><?= $welcomeOfferIsRomanian
					? 'Nu se aplică pentru SHIK, MAC și Anastasia Beverly Hills'
					: 'Не действует на SHIK, MAC и Anastasia Beverly Hills' ?></span>
			</div>

			<button
				type="button"
				class="welcome-offer__submit"
				data-welcome-offer-register
			>
				<?= $welcomeOfferIsRomanian ? 'Creează un cont' : 'Создать аккаунт' ?>
			</button>

			<button
				type="button"
				class="welcome-offer__later"
				data-welcome-offer-close
			>
				<?= $welcomeOfferIsRomanian ? 'Mai târziu' : 'Позже' ?>
			</button>
		</div>
	</section>
</div>
