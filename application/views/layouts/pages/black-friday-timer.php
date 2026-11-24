<?php
defined('BASEPATH') or exit('No direct script access allowed');

$campaign = !empty($black_friday_campaign) && is_array($black_friday_campaign)
	? $black_friday_campaign
	: array();
$isRomanian = strtolower(trim((string) ($lclang ?? 'ru'))) === 'ro';
$campaignName = ($site_theme_key ?? '') === 'red_friday' ? 'Red Friday' : 'Black Friday';
$campaignText = trim((string) ($campaign[$isRomanian ? 'text_ro' : 'text_ru'] ?? ''));
$endsAt = (int) ($campaign['ends_at'] ?? 0);
$variant = !empty($black_friday_timer_variant) && $black_friday_timer_variant === 'header'
	? 'header'
	: 'cart';

if ($campaignText === '' || $endsAt <= 0) {
	return;
}
?>
<aside
	class="black-friday-countdown black-friday-countdown--<?= $variant ?>"
	data-black-friday-timer
	data-ends-at="<?= $endsAt ?>"
	data-day-label="<?= $isRomanian ? 'z' : 'д' ?>"
	aria-label="<?= htmlspecialchars($isRomanian ? 'Promoția ' . $campaignName . ' se încheie în' : 'До окончания акции ' . $campaignName, ENT_QUOTES, 'UTF-8') ?>"
>
	<?php if ($variant === 'header') : ?>
		<span class="black-friday-countdown__header-line">
			<span class="black-friday-countdown__copy">
				<?= htmlspecialchars($campaignText, ENT_QUOTES, 'UTF-8') ?>
			</span>&nbsp;
			<span class="black-friday-countdown__label">
				<?= $isRomanian ? 'Se încheie în' : 'До конца акции' ?>
			</span>&nbsp;
			<time
				class="black-friday-countdown__clock"
				data-black-friday-countdown
				datetime="<?= htmlspecialchars(gmdate('c', $endsAt), ENT_QUOTES, 'UTF-8') ?>"
			>--:--:--</time>
		</span>
	<?php else : ?>
		<span class="black-friday-countdown__copy">
			<?= htmlspecialchars($campaignText, ENT_QUOTES, 'UTF-8') ?>
		</span>
		<span class="black-friday-countdown__clock-wrap">
			<span class="black-friday-countdown__label">
				<?= $isRomanian ? 'Se încheie în' : 'До конца акции' ?>
			</span>
			<time
				class="black-friday-countdown__clock"
				data-black-friday-countdown
				datetime="<?= htmlspecialchars(gmdate('c', $endsAt), ENT_QUOTES, 'UTF-8') ?>"
			>--:--:--</time>
		</span>
	<?php endif; ?>
</aside>
