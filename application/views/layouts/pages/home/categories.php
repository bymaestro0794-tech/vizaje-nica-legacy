<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';
$sectionTitle = $isRomanian ? 'Cumpără după categorie' : 'Покупайте по категориям';
$homeCategories = [];

foreach ($home_categories ?? [] as $category) {
	$title = trim((string) ($category->title ?? ''));
	$url = trim((string) ($category->url ?? ''));
	$image = !empty($category->img)
		? newthumbs($category->img, 'home_categories')
		: '';

	if ($title === '' || $url === '' || $image === '') {
		continue;
	}

	$homeCategories[] = [
		'title' => $title,
		'url' => $url,
		'image' => $image,
	];

	if (count($homeCategories) === 3) {
		break;
	}
}

if (empty($homeCategories)) {
	return;
}

$categoryPageUri = $isRomanian ? 'catalog-collections' : 'katalog-collections';
$viewMoreUrl = '/' . $lclang . '/' . $categoryPageUri;
?>

<section
	class="home-categories"
	aria-label="<?= $isRomanian
		? 'Categorii populare'
		: 'Популярные категории' ?>"
	data-home-categories
>
	<div class="home-categories__container _container">
		<h2 class="home-categories__heading">
			<?= htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') ?>
		</h2>
		<div class="home-categories__track">
			<?php foreach ($homeCategories as $index => $category) : ?>
				<a
					href="<?= htmlspecialchars(
						$category['url'],
						ENT_QUOTES,
						'UTF-8'
					) ?>"
					class="home-category"
					data-home-category
					style="--category-index: <?= (int) $index ?>;"
				>
					<span class="home-category__media">
						<img
								src="<?= htmlspecialchars(
									$category['image'],
									ENT_QUOTES,
									'UTF-8'
								) ?>"
								class="home-category__image"
								width="480"
								height="600"
								loading="lazy"
								decoding="async"
								alt="<?= htmlspecialchars(
									$category['title'],
									ENT_QUOTES,
									'UTF-8'
								) ?>"
							>
						<span class="home-category__label">
							<?= htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') ?>
						</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
		<a class="home-categories__more" href="<?= htmlspecialchars($viewMoreUrl, ENT_QUOTES, 'UTF-8') ?>">
			<?= $isRomanian ? 'Vezi mai multe' : 'Посмотреть больше' ?>
			<svg class="home-arrow-icon home-arrow-icon--right" fill="none" stroke="none" viewBox="0 0 25 15" aria-hidden="true">
				<path stroke="currentColor" d="M6 2 .5 7.5 6 13" />
				<path fill="currentColor" d="M1 7h20v1H1z" />
			</svg>
		</a>
	</div>
</section>
