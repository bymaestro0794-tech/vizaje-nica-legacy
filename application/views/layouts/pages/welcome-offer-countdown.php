<?php
defined('BASEPATH') or exit('No direct script access allowed');

$welcomeOfferIsRomanian = strtolower(trim((string) ($lclang ?? 'ru'))) === 'ro';
$welcomeOfferExpiresAt = !empty($welcome_offer_timer['expires_at'])
	? (int) $welcome_offer_timer['expires_at']
	: 0;
?>
<aside
	class="welcome-offer-countdown"
	data-welcome-countdown
	data-expires-at="<?= $welcomeOfferExpiresAt ?>"
	data-language="<?= $welcomeOfferIsRomanian ? 'ro' : 'ru' ?>"
	aria-live="polite"
>
	<strong><?= $welcomeOfferIsRomanian
		? 'Ai 18% reducere la prima comandă'
		: 'Скидка 18% на первый заказ' ?></strong>
	<span><?= $welcomeOfferIsRomanian ? 'Expiră în' : 'Осталось' ?>
		<time data-welcome-countdown-value></time>
	</span>
	<small><?= $welcomeOfferIsRomanian
		? 'Se aplică automat în coș · cu excepția SHIK, MAC și Anastasia Beverly Hills'
		: 'Применится автоматически в корзине · кроме SHIK, MAC и Anastasia Beverly Hills' ?></small>
</aside>
