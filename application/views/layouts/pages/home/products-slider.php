<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (empty($products)) {
	return;
}

$sectionTitle = !empty($title)
	? $title
	: '';

$sectionKey = !empty($section_key)
	? preg_replace('/[^a-z0-9_-]/i', '', $section_key)
	: 'products';

$itemsPerPage = 4;

$sectionUrl = !empty($section_url)
	? $section_url
	: '#';

$sliderItems = array_values($products);

/*
|--------------------------------------------------------------------------
| Final catalog card
|--------------------------------------------------------------------------
|
| CTA добавляется после последнего товара и участвует
| в обычном разбиении на страницы.
|
*/

$sliderItems[] = [
	'is_catalog_link' => true,
];

$productPages = array_chunk(
	$sliderItems,
	$itemsPerPage
);

$pagesCount = count($productPages);

$isRomanian = $lclang === 'ro';

$useCatalogProductCardStyle = !empty($is_catalog_product_card);

$useHomeProductCardStyle = !$useCatalogProductCardStyle
	&& (!empty($is_home_product_tabs)
	|| !empty($is_product_recommendation));

$sectionClasses = 'home-products';
if ($useHomeProductCardStyle) {
	$sectionClasses .= ' home-products--home-card';
}
if ($useCatalogProductCardStyle) {
	$sectionClasses .= ' products_catalog home-products--catalog-card';
}
?>

<section
	class="<?= $sectionClasses ?>"
	data-products-slider
	data-products-key="<?= htmlspecialchars(
		$sectionKey,
		ENT_QUOTES,
		'UTF-8'
	) ?>"
>
	<div class="home-products__container _container">

		<header class="home-products__header">
			<?php if (!empty($sectionTitle)) : ?>
				<h2 class="home-products__title">
					<?= htmlspecialchars(
						$sectionTitle,
						ENT_QUOTES,
						'UTF-8'
					) ?>
				</h2>
			<?php endif; ?>

			<?php if ($pagesCount > 1) : ?>
				<div
					class="home-products__controls"
					aria-label="<?= $isRomanian
						? 'Navigare produse'
						: 'Навигация по товарам' ?>"
				>
					<button
						type="button"
						class="
							home-products__arrow
							home-products__arrow--previous
						"
						aria-label="<?= $isRomanian
							? 'Produsele precedente'
							: 'Предыдущие товары' ?>"
						data-products-previous
						disabled
					>
						<svg class="home-arrow-icon" fill="none" stroke="none" viewBox="0 0 25 15" aria-hidden="true">
							<path stroke="currentColor" d="M6 2 .5 7.5 6 13" />
							<path fill="currentColor" d="M1 7h20v1H1z" />
						</svg>
					</button>

					<button
						type="button"
						class="
							home-products__arrow
							home-products__arrow--next
						"
						aria-label="<?= $isRomanian
							? 'Produsele următoare'
							: 'Следующие товары' ?>"
						data-products-next
					>
						<svg class="home-arrow-icon home-arrow-icon--right" fill="none" stroke="none" viewBox="0 0 25 15" aria-hidden="true">
							<path stroke="currentColor" d="M6 2 .5 7.5 6 13" />
							<path fill="currentColor" d="M1 7h20v1H1z" />
						</svg>
					</button>
				</div>
			<?php endif; ?>
		</header>

		<div
	class="home-products__viewport"
	data-products-viewport
>
	<div
		class="home-products__track"
		data-products-track
	>
		<?php foreach ($productPages as $pageIndex => $pageProducts) : ?>
			<div
				class="
					home-products__page
					<?= $pageIndex === 0
						? 'is-active'
						: '' ?>
				"
				data-products-page
				data-products-page-index="<?= $pageIndex ?>"
				aria-hidden="<?= $pageIndex === 0
					? 'false'
					: 'true' ?>"
			>
				<?php foreach ($pageProducts as $product) : ?>
					<?php if (
						is_array($product)
						&& !empty($product['is_catalog_link'])
					) : ?>
						<a
							href="<?= htmlspecialchars(
								$sectionUrl,
								ENT_QUOTES,
								'UTF-8'
							) ?>"
							class="home-products__catalog-link"
							data-products-catalog-link
						>
							<span class="home-products__catalog-link-label">
								<?= $isRomanian
									? 'Vezi toate produsele'
									: 'Перейти к товарам' ?>
							</span>

							<svg class="home-arrow-icon home-arrow-icon--right" fill="none" stroke="none" viewBox="0 0 25 15" aria-hidden="true">
								<path stroke="currentColor" d="M6 2 .5 7.5 6 13" />
								<path fill="currentColor" d="M1 7h20v1H1z" />
							</svg>
						</a>
					<?php else : ?>
						<?php
						$this->load->view(
							'layouts/pages/product_slider',
							[
								'item' => $product,
								'is_catalog_product_card' => $useCatalogProductCardStyle,
							]
						);
						?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if (count($sliderItems) > 1) : ?>
		<button
			type="button"
			class="
				home-products__mobile-arrow
				home-products__mobile-arrow--previous
			"
			aria-label="<?= $isRomanian
				? 'Produsul precedent'
				: 'Предыдущий товар' ?>"
			data-products-mobile-previous
			disabled
		>
				<svg class="home-arrow-icon" fill="none" stroke="none" viewBox="0 0 25 15" aria-hidden="true">
					<path stroke="currentColor" d="M6 2 .5 7.5 6 13" />
					<path fill="currentColor" d="M1 7h20v1H1z" />
				</svg>
		</button>

		<button
			type="button"
			class="
				home-products__mobile-arrow
				home-products__mobile-arrow--next
			"
			aria-label="<?= $isRomanian
				? 'Produsul următor'
				: 'Следующий товар' ?>"
			data-products-mobile-next
		>
			<svg class="home-arrow-icon home-arrow-icon--right" fill="none" stroke="none" viewBox="0 0 25 15" aria-hidden="true">
				<path stroke="currentColor" d="M6 2 .5 7.5 6 13" />
				<path fill="currentColor" d="M1 7h20v1H1z" />
			</svg>
		</button>
	<?php endif; ?>
</div>

	</div>
</section>
