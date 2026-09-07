<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (
	empty($section)
	|| empty($section->products)
) {
	return;
}

$isRomanian =
	isset($lclang)
	&& $lclang === 'ro';

$title = trim(
	(string) ($section->title ?? '')
);

$url = trim(
	(string) ($section->url ?? '')
);

$desktopImage = '';
$mobileImage = '';

if (!empty($section->img)) {
	$desktopImage = newthumbs(
		$section->img,
		'home_brand_sections'
	);
}

if (!empty($section->imgMob)) {
	$mobileImage = newthumbs(
		$section->imgMob,
		'home_brand_sections'
	);
}

if ($mobileImage === '') {
	$mobileImage = $desktopImage;
}

$products =
	is_array($section->products)
		? $section->products
		: (array) $section->products;

$productsCount = count($products);

$showDesktopNavigation =
	$productsCount > 4;
?>

<section
	class="home-brand-showcase home-products"
	data-home-brand-showcase
>
	<div class="home-brand-showcase__container">

		<header class="home-brand-showcase__header">
			<?php if ($title !== '') : ?>
				<h2 class="home-brand-showcase__title">
					<?= htmlspecialchars(
						$title,
						ENT_QUOTES,
						'UTF-8'
					) ?>
				</h2>
			<?php endif; ?>

			<?php if ($url !== '') : ?>
				<a
					href="<?= htmlspecialchars(
						$url,
						ENT_QUOTES,
						'UTF-8'
					) ?>"
					class="home-brand-showcase__brand-link"
				>
					<span>
						<?= $isRomanian
							? 'Vezi brandul'
							: 'Перейти к бренду' ?>
					</span>

					<span aria-hidden="true">
						→
					</span>
				</a>
			<?php endif; ?>
		</header>

		<?php if ($desktopImage !== '') : ?>
			<?php if ($url !== '') : ?>
				<a
					href="<?= htmlspecialchars(
						$url,
						ENT_QUOTES,
						'UTF-8'
					) ?>"
					class="home-brand-showcase__media"
					aria-label="<?= htmlspecialchars(
						$title,
						ENT_QUOTES,
						'UTF-8'
					) ?>"
				>
			<?php else : ?>
				<div class="home-brand-showcase__media">
			<?php endif; ?>

				<picture>
					<?php if ($mobileImage !== '') : ?>
						<source
							media="(max-width: 767px)"
							srcset="<?= htmlspecialchars(
								$mobileImage,
								ENT_QUOTES,
								'UTF-8'
							) ?>"
						>
					<?php endif; ?>

					<img
						src="<?= htmlspecialchars(
							$desktopImage,
							ENT_QUOTES,
							'UTF-8'
						) ?>"
						class="home-brand-showcase__image"
						alt="<?= htmlspecialchars(
							$title,
							ENT_QUOTES,
							'UTF-8'
						) ?>"
						loading="lazy"
						decoding="async"
					>
				</picture>

			<?php if ($url !== '') : ?>
				</a>
			<?php else : ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div
			class="
				home-brand-showcase__products-wrapper
				<?= !$showDesktopNavigation
					? 'home-brand-showcase__products-wrapper--without-navigation'
					: '' ?>
			"
		>
			<?php if ($showDesktopNavigation) : ?>
				<button
					type="button"
					class="
						home-brand-showcase__navigation
						home-brand-showcase__navigation--prev
					"
					data-home-brand-prev
					aria-label="<?= $isRomanian
						? 'Produsele anterioare'
						: 'Предыдущие товары' ?>"
				>
					<svg
						width="22"
						height="22"
						viewBox="0 0 24 24"
						fill="none"
						aria-hidden="true"
					>
						<path
							d="M15 5L8 12L15 19"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
						/>
					</svg>
				</button>
			<?php endif; ?>

			<div
				class="home-brand-showcase__viewport"
				data-home-brand-viewport
			>
				<div
					class="home-brand-showcase__list"
					data-home-brand-list
				>
					<?php foreach ($products as $item) : ?>
						<div class="home-brand-showcase__product">
							<?php
							$this->load->view(
								'layouts/pages/product_slider',
								[
									'item' => $item,
									'lclang' => $lclang ?? 'ru',
									'clang' => $clang ?? 'RU',
									'menu' => $menu ?? [],
								]
							);
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ($showDesktopNavigation) : ?>
				<button
					type="button"
					class="
						home-brand-showcase__navigation
						home-brand-showcase__navigation--next
					"
					data-home-brand-next
					aria-label="<?= $isRomanian
						? 'Produsele următoare'
						: 'Следующие товары' ?>"
				>
					<svg
						width="22"
						height="22"
						viewBox="0 0 24 24"
						fill="none"
						aria-hidden="true"
					>
						<path
							d="M9 5L16 12L9 19"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
						/>
					</svg>
				</button>
			<?php endif; ?>
		</div>
	</div>
</section>