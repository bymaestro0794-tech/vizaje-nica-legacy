<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = isset($lclang) && $lclang === 'ro';
$articles = isset($articles) && is_array($articles)
	? array_slice($articles, 0, 5)
	: array();
$blogUrl = '/' . ($lclang ?? 'ru') . '/blog';

if (empty($articles)) {
	return;
}
?>

<section class="home-blog" aria-labelledby="home-blog-title" data-home-blog>
	<header class="home-blog__header">
		<h2 class="home-blog__title" id="home-blog-title">
			<a class="home-blog__title-link" href="<?= htmlspecialchars($blogUrl, ENT_QUOTES, 'UTF-8') ?>">
				<?= $isRomanian ? 'Blog' : 'Блог' ?>
			</a>
		</h2>

		<?php if (count($articles) > 1) : ?>
			<div class="home-blog__controls" aria-label="<?= $isRomanian ? 'Navigare articole' : 'Навигация по статьям' ?>">
				<button
					type="button"
					class="home-blog__arrow"
					aria-label="<?= $isRomanian ? 'Articolul precedent' : 'Предыдущая статья' ?>"
					data-home-blog-prev
				>
					<img src="/app/img/icons-2/arrow-left-linear.svg" alt="" aria-hidden="true">
				</button>
				<button
					type="button"
					class="home-blog__arrow home-blog__arrow--next"
					aria-label="<?= $isRomanian ? 'Articolul următor' : 'Следующая статья' ?>"
					data-home-blog-next
				>
					<img src="/app/img/icons-2/arrow-left-linear.svg" alt="" aria-hidden="true">
				</button>
			</div>
		<?php endif; ?>
	</header>

	<div
		class="home-blog__viewport"
		data-home-blog-viewport
		tabindex="0"
		role="region"
		aria-roledescription="<?= $isRomanian ? 'carusel' : 'карусель' ?>"
		aria-label="<?= $isRomanian ? 'Articole recente de pe blog' : 'Последние статьи блога' ?>"
	>
		<div class="home-blog__track" data-home-blog-track>
			<?php foreach ($articles as $index => $article) : ?>
				<?php
				$title = trim((string) ($article->title ?? ''));
				$articleUri = trim((string) ($article->uri ?? ''), '/');
				$articleUrl = $blogUrl . '/' . $articleUri;
				$image = !empty($article->img)
					? newthumbs($article->img, 'articles')
					: '';
				$excerpt = trim(preg_replace(
					'/\s+/u',
					' ',
					html_entity_decode(
						strip_tags((string) ($article->desc ?? '')),
						ENT_QUOTES | ENT_HTML5,
						'UTF-8'
					)
				));
				if (mb_strlen($excerpt, 'UTF-8') > 180) {
					$excerpt = rtrim(mb_substr($excerpt, 0, 177, 'UTF-8')) . '…';
				}
				?>

				<article
					class="home-blog__item"
					data-home-blog-slide
					role="group"
					aria-roledescription="<?= $isRomanian ? 'slide' : 'слайд' ?>"
					aria-label="<?= $index + 1 ?> <?= $isRomanian ? 'din' : 'из' ?> <?= count($articles) ?>"
				>
					<a
						class="home-blog__card"
						href="<?= htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8') ?>"
						aria-label="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
					>
						<span class="home-blog__image">
							<?php if ($image !== '') : ?>
								<img
									src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
									alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
									loading="lazy"
									decoding="async"
								>
							<?php else : ?>
								<span class="home-blog__image-placeholder" aria-hidden="true">VIZAJE-NICA</span>
							<?php endif; ?>
						</span>

						<span class="home-blog__copy">
							<strong class="home-blog__card-title">
								<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
							</strong>
							<?php if ($excerpt !== '') : ?>
								<span class="home-blog__excerpt">
									<?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?>
								</span>
							<?php endif; ?>
						</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
