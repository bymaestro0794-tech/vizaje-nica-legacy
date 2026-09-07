<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';

$sectionTitle = $isRomanian
	? 'Ați vizualizat'
	: 'Вы смотрели';
?>

<section
	class="home-products recently-viewed"
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
					<svg
						width="22"
						height="22"
						viewBox="0 0 24 24"
						fill="none"
						aria-hidden="true"
					>
						<path
							d="M19 12H5"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
						/>

						<path
							d="M10 7L5 12L10 17"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
						/>
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
					<svg
						width="22"
						height="22"
						viewBox="0 0 24 24"
						fill="none"
						aria-hidden="true"
					>
						<path
							d="M5 12H19"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
						/>

						<path
							d="M14 7L19 12L14 17"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
						/>
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
				<svg
					width="20"
					height="20"
					viewBox="0 0 24 24"
					fill="none"
					aria-hidden="true"
				>
					<path
						d="M19 12H5"
						stroke="currentColor"
						stroke-width="1.5"
						stroke-linecap="round"
					/>

					<path
						d="M10 7L5 12L10 17"
						stroke="currentColor"
						stroke-width="1.5"
						stroke-linecap="round"
						stroke-linejoin="round"
					/>
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
				<svg
					width="20"
					height="20"
					viewBox="0 0 24 24"
					fill="none"
					aria-hidden="true"
				>
					<path
						d="M5 12H19"
						stroke="currentColor"
						stroke-width="1.5"
						stroke-linecap="round"
					/>

					<path
						d="M14 7L19 12L14 17"
						stroke="currentColor"
						stroke-width="1.5"
						stroke-linecap="round"
						stroke-linejoin="round"
					/>
				</svg>
			</button>
		</div>

	</div>
</section>