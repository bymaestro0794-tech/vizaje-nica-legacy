<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (empty($home_categories)) {
	return;
}

$isRomanian = $lclang === 'ro';
?>

<section
	class="home-categories"
	aria-label="<?= $isRomanian
		? 'Categorii populare'
		: 'Популярные категории' ?>"
	data-home-categories
>
	<div class="home-categories__container _container">
		<div class="home-categories__track">
			<?php foreach ($home_categories as $index => $category) : ?>
				<?php
				$title = trim(
					(string) ($category->title ?? '')
				);

				$url = trim(
					(string) ($category->url ?? '')
				);

				$image = '';

				if (!empty($category->img)) {
					$image = newthumbs(
						$category->img,
						'home_categories'
					);
				}

				if (
					$title === ''
					|| $url === ''
					|| $image === ''
				) {
					continue;
				}
				?>

				<a
					href="<?= htmlspecialchars(
						$url,
						ENT_QUOTES,
						'UTF-8'
					) ?>"
					class="home-category"
					data-home-category
					style="--category-index: <?= (int) $index ?>;"
				>
					<span class="home-category__media">
						<span class="home-category__image-wrapper">
							<img
								src="<?= htmlspecialchars(
									$image,
									ENT_QUOTES,
									'UTF-8'
								) ?>"
								class="home-category__image"
								width="144"
								height="144"
								loading="lazy"
								decoding="async"
								alt="<?= htmlspecialchars(
									$title,
									ENT_QUOTES,
									'UTF-8'
								) ?>"
							>
						</span>
					</span>

					<span class="home-category__title">
						<?= htmlspecialchars(
							$title,
							ENT_QUOTES,
							'UTF-8'
						) ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>