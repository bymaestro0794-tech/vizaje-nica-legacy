<?php
defined('BASEPATH') or exit('No direct script access allowed');
$escapeSiteDialog = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
foreach ((array) $site_dialogs as $siteDialog) :
	$dialogId = (int) ($siteDialog['id'] ?? 0);
	if ($dialogId < 1) continue;
	$imageName = basename((string) ($siteDialog['image'] ?? ''));
?>
<div class="site-dialog" data-site-dialog="<?= $dialogId ?>" data-delay-ms="<?= (int) ($siteDialog['delay_ms'] ?? 45000) ?>" data-priority="<?= (int) ($siteDialog['priority'] ?? 0) ?>" aria-hidden="true" inert>
	<button type="button" class="site-dialog__backdrop" data-site-dialog-close aria-label="<?= $lclang === 'ro' ? 'Închide' : 'Закрыть' ?>"></button>
	<section class="site-dialog__panel<?= $imageName === '' ? ' site-dialog__panel--text-only' : '' ?>" role="dialog" aria-modal="true" aria-labelledby="site-dialog-title-<?= $dialogId ?>" tabindex="-1">
		<button type="button" class="site-dialog__close" data-site-dialog-close aria-label="<?= $lclang === 'ro' ? 'Închide' : 'Закрыть' ?>">×</button>
		<?php if ($imageName !== '') : ?>
			<div class="site-dialog__image"><img src="/public/site_dialogs/<?= rawurlencode($imageName) ?>" alt="" loading="lazy"></div>
		<?php endif; ?>
		<div class="site-dialog__content">
			<h2 id="site-dialog-title-<?= $dialogId ?>"><?= $escapeSiteDialog($siteDialog['title'] ?? '') ?></h2>
			<p><?= nl2br($escapeSiteDialog($siteDialog['text'] ?? '')) ?></p>
			<?php if (!empty($siteDialog['button_text']) && !empty($siteDialog['target_url'])) : ?>
				<a class="site-dialog__action" href="<?= $escapeSiteDialog($siteDialog['target_url']) ?>" data-site-dialog-action><?= $escapeSiteDialog($siteDialog['button_text']) ?></a>
			<?php endif; ?>
			<button type="button" class="site-dialog__later" data-site-dialog-close><?= $lclang === 'ro' ? 'Mai târziu' : 'Позже' ?></button>
		</div>
	</section>
</div>
<?php endforeach; ?>
