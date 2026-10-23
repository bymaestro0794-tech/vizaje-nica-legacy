<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';

$sectionTitle = $isRomanian
	? 'Ați vizualizat'
	: 'Вы смотрели';
?>

<section
	class="home-products home-products--home-card recently-viewed"
	data-recently-viewed
	data-products-slider
	data-current-product-id="<?= !empty($current_product_id)
		? (int) $current_product_id
		: 0 ?>"
	hidden
>
	<div class="home-products__container _container">

		<header class="home-products__header">

			<h2 class="home-products__title">
				<?= htmlspecialchars(
					$sectionTitle,
					ENT_QUOTES,
					'UTF-8'
				) ?>
			</h2>

			<div
				class="home-products__controls"
				data-recently-viewed-controls
				aria-label="<?= $isRomanian
					? 'Navigare produse'
					: 'Навигация по товарам' ?>"
				hidden
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
					data-recently-viewed-previous
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
					data-recently-viewed-next
					data-products-next
				>
					<svg class="home-arrow-icon home-arrow-icon--right" fill="none" stroke="none" viewBox="0 0 25 15" aria-hidden="true">
						<path stroke="currentColor" d="M6 2 .5 7.5 6 13" />
						<path fill="currentColor" d="M1 7h20v1H1z" />
					</svg>
				</button>
			</div>

		</header>

		<div
			class="home-products__viewport"
			data-recently-viewed-viewport
		>
			<div
				class="home-products__track"
				data-recently-viewed-track
				data-products-track
			></div>

			<button
				type="button"
				class="
					home-products__mobile-arrow
					home-products__mobile-arrow--previous
				"
				aria-label="<?= $isRomanian
					? 'Produsul precedent'
					: 'Предыдущий товар' ?>"
				data-recently-viewed-mobile-previous
				disabled
				hidden
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
				data-recently-viewed-mobile-next
				hidden
			>
				<svg class="home-arrow-icon home-arrow-icon--right" fill="none" stroke="none" viewBox="0 0 25 15" aria-hidden="true">
					<path stroke="currentColor" d="M6 2 .5 7.5 6 13" />
					<path fill="currentColor" d="M1 7h20v1H1z" />
				</svg>
			</button>
		</div>

	</div>
</section>
