<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Local helpers
|--------------------------------------------------------------------------
*/

$escape = static function ($value) {
	return htmlspecialchars(
		(string) $value,
		ENT_QUOTES,
		'UTF-8'
	);
};

$isB2B = !empty($_SESSION['isb2b']);

/*
|--------------------------------------------------------------------------
| Product title
|--------------------------------------------------------------------------
*/

$productTitle = !empty($product->h1_title)
	? trim((string) $product->h1_title)
	: trim((string) $page_title);

$productTitle = preg_replace(
	'/\s+/u',
	' ',
	html_entity_decode(
		$productTitle,
		ENT_QUOTES | ENT_HTML5,
		'UTF-8'
	)
);

$productBrand = !empty($product->brand_title)
	? trim((string) $product->brand_title)
	: '';

/*
|--------------------------------------------------------------------------
| Main product images
|--------------------------------------------------------------------------
*/

$productImages = array();

if (!empty($product->img)) {
	foreach ($product->img as $index => $image) {
		if (empty($image->img)) {
			continue;
		}

		$productImages[] = array(
			'src' => newthumbs(
				$image->img,
				'products'
			),
			'alt' => trim(
				$productBrand
				. ' '
				. $productTitle
				. ' — '
				. ($index + 1)
			),
		);
	}
}

if (empty($productImages)) {
	$productImages[] = array(
		'src' => '/app/img/no-image/no_image2.webp',
		'alt' => $lclang === 'ro'
			? 'Imaginea produsului va apărea în curând'
			: 'Изображение товара скоро появится',
		'isPlaceholder' => true,
	);
}

$productHasRealImage =
	!empty($product->img);

/*
|--------------------------------------------------------------------------
| Variations
|--------------------------------------------------------------------------
*/

$variationsData = array();

$firstAvailableVariation = null;
$firstDisplayVariation = null;

/*
 * Нормализуем color_data конкретного варианта.
 *
 * Model может вернуть:
 *
 * 1. NULL
 *
 * 2. JSON:
 *    {"colors":["#004080"]}
 *
 * 3. Уже декодированный массив:
 *    ["#004080"]
 *
 * 4. Уже декодированный объект:
 *    ["colors" => ["#004080"]]
 */
$normalizeVariationColors = static function ($colorData) {
	if (empty($colorData)) {
		return array();
	}

	$colors = array();

	if (is_array($colorData)) {
		if (
			isset($colorData['colors'])
			&& is_array($colorData['colors'])
		) {
			$colors = $colorData['colors'];
		} else {
			$colors = $colorData;
		}
	} else {
		$decoded = json_decode(
			(string) $colorData,
			true
		);

		if (
			is_array($decoded)
			&& !empty($decoded['colors'])
			&& is_array($decoded['colors'])
		) {
			$colors = $decoded['colors'];
		}
	}

	$normalized = array();

	foreach ($colors as $color) {
		$color = strtoupper(
			trim(
				(string) $color
			)
		);

		if (
			!preg_match(
				'/^#[0-9A-F]{6}$/',
				$color
			)
		) {
			continue;
		}

		if (
			!in_array(
				$color,
				$normalized,
				true
			)
		) {
			$normalized[] = $color;
		}

		if (count($normalized) >= 6) {
			break;
		}
	}

	return $normalized;
};

if (!empty($product->variable)) {
	foreach ($product->variable as $variation) {

		/*
		|--------------------------------------------------------------------------
		| Colors
		|--------------------------------------------------------------------------
		*/

		$variationColors =
			$normalizeVariationColors(
				isset($variation->color_data)
					? $variation->color_data
					: null
			);

		/*
		|--------------------------------------------------------------------------
		| Images
		|--------------------------------------------------------------------------
		*/

		$variationImages = array();

		if (!empty($variation->img)) {
			foreach (
				$variation->img
				as $index => $image
			) {
				if (empty($image->img)) {
					continue;
				}

				$variationLabel = '';

				if (!empty($variation->color)) {
					$variationLabel = trim(
						(string) $variation->color
					);
				} elseif (
					!empty($variation->title)
				) {
					$variationLabel = trim(
						(string) $variation->title
					);
				} elseif (
					!empty($variation->VolumeVar)
				) {
					$variationLabel = trim(
						(string) $variation->VolumeVar
					);
				}

				$variationImages[] = array(
					'src' => newthumbs(
						$image->img,
						'products_variable_img'
					),

					'alt' => trim(
						$productBrand
						. ' '
						. $productTitle
						. (
							$variationLabel !== ''
								? ' — ' . $variationLabel
								: ''
						)
						. ' — '
						. ($index + 1)
					),
				);
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Stock
		|--------------------------------------------------------------------------
		*/

		$quantity = $isB2B
			? (int) (
				$variation->qtyWH ?? 0
			)
			: (int) (
				$variation->qty ?? 0
			);

		$isAvailable =
			$quantity > 0;

		/*
		|--------------------------------------------------------------------------
		| Variation frontend data
		|--------------------------------------------------------------------------
		*/

		$variationData = array(
			'id' => (int) $variation->id,

			'externalId' =>
				!empty(
					$variation->external_id
				)
					? (string) $variation->external_id
					: null,

			'sku' =>
				!empty($variation->SKU)
					? (string) $variation->SKU
					: '',

			'title' =>
				!empty($variation->title)
					? (string) $variation->title
					: '',

			'color' =>
				!empty($variation->color)
					? (string) $variation->color
					: '',

			/*
			 * ВАЖНО:
			 * цвета теперь принадлежат
			 * именно этому варианту.
			 */
			'colors' => $variationColors,

			'volume' =>
				!empty($variation->VolumeVar)
					? (string) $variation->VolumeVar
					: '',

			'price' => (float) (
				$variation->price ?? 0
			),

			'wholesalePrice' =>
				(float) (
					$variation->priceWH ?? 0
				),

			'discountPrice' =>
				!empty(
					$variation->discount_price
				)
					? (float) $variation->discount_price
					: null,

			'quantity' =>
				$quantity,

			'available' =>
				$isAvailable,

			'hasOwnImages' =>
				!empty(
					$variationImages
				),

			'images' =>
				$variationImages,
		);

		$variationsData[] =
			$variationData;

		/*
		|--------------------------------------------------------------------------
		| First display variation
		|--------------------------------------------------------------------------
		|
		| Даже если всё закончилось,
		| PDP должен показать конкретный вариант:
		| фото, SKU, цену, объём.
		|
		*/

		if (
				$firstDisplayVariation === null
		) {
				$firstDisplayVariation =
						$variationData;
		}

		/*
		|--------------------------------------------------------------------------
		| First available variation
		|--------------------------------------------------------------------------
		*/

		if (
			$firstAvailableVariation === null
			&& $isAvailable
		) {
			$firstAvailableVariation =
				$variationData;
		}
	}
}

$initialVariation =
    !empty($firstAvailableVariation)
        ? $firstAvailableVariation
        : $firstDisplayVariation;

/*
|--------------------------------------------------------------------------
| Initial product state
|--------------------------------------------------------------------------
*/

$initialImages = $productImages;

$initialVariationId = 0;

$initialSku = !empty($product->SKU)
	? (string) $product->SKU
	: '';

$initialVolume = !empty($product->volume)
	? (string) $product->volume
	: '';

$initialPrice = $isB2B
	? (float) ($product->priceWH ?? 0)
	: (float) ($product->price ?? 0);

$initialDiscountPrice = (
	!$isB2B
	&& !empty($product->discount_price)
)
	? (float) $product->discount_price
	: null;

if (!empty($initialVariation)) {
    $initialVariationId =
        (int) $initialVariation['id'];

    $initialSku =
        (string) $initialVariation['sku'];

    $initialVolume =
        (string) $initialVariation['volume'];

    $initialPrice = $isB2B
        ? (float) $initialVariation[
            'wholesalePrice'
        ]
        : (float) $initialVariation[
            'price'
        ];

    $initialDiscountPrice =
        !$isB2B
            ? $initialVariation[
                'discountPrice'
            ]
            : null;

    if (
        !empty(
            $initialVariation['images']
        )
    ) {
        $initialImages =
            $initialVariation['images'];
    }
}

/*
|--------------------------------------------------------------------------
| Variation groups
|--------------------------------------------------------------------------
*/

$colorVariations = array();
$volumeVariations = array();

foreach ($variationsData as $variation) {
	$hasColor =
		!empty($variation['color']);

	if ($hasColor) {
		$colorVariations[] = $variation;
	} elseif (!empty($variation['volume'])) {
		$volumeVariations[] = $variation;
	}
}

$initialColorVariation = null;

foreach ($colorVariations as $variation) {
	if (
		(int) $variation['id']
		=== (int) $initialVariationId
	) {
		$initialColorVariation = $variation;

		break;
	}
}

if (
	empty($initialColorVariation)
	&& !empty($colorVariations)
) {
	$initialColorVariation =
		reset($colorVariations);
}

/*
|--------------------------------------------------------------------------
| Stock
|--------------------------------------------------------------------------
*/

$productInStock = $isB2B
	? !empty($product->on_stockWH)
	: !empty($product->on_stock);

if (!empty($firstAvailableVariation)) {
	$productInStock = true;
}

/*
|--------------------------------------------------------------------------
| Product page JS state
|--------------------------------------------------------------------------
*/

$productPageData = array(
	'product' => array(
		'id' => (int) $product->id,

		'guid' => !empty($product->GUID)
			? (string) $product->GUID
			: null,

		'sku' => !empty($product->SKU)
			? (string) $product->SKU
			: '',

		'externalId' => !empty(
			$product->external_id
		)
			? (string) $product->external_id
			: null,

		'title' => (string) $productTitle,

		'brand' =>
			(string) $productBrand,

		'category' =>
			!empty($category->title)
				? (string) $category->title
				: '',

		'price' => (float) (
			$product->price ?? 0
		),

		'wholesalePrice' => (float) (
			$product->priceWH ?? 0
		),

		'discountPrice' => !empty(
			$product->discount_price
		)
			? (float) $product->discount_price
			: null,

		'volume' => !empty($product->volume)
			? (string) $product->volume
			: '',

		'images' => $productImages,
	),

	'initial' => array(
		'variationId' =>
			(int) $initialVariationId,

		'sku' => (string) $initialSku,

		'volume' => (string) $initialVolume,

		'price' => (float) $initialPrice,

		'discountPrice' =>
			$initialDiscountPrice,

		'images' => $initialImages,
	),

	'variations' => $variationsData,

	'currency' => (string) MDL,

	'language' => (string) $lclang,

	'isB2B' => $isB2B,

	'labels' => array(
		'unavailable' => $lclang === 'ro'
			? 'Indisponibil'
			: 'Недоступно',

		'previousImage' =>
			$lclang === 'ro'
				? 'Imaginea precedentă'
				: 'Предыдущее изображение',

		'nextImage' =>
			$lclang === 'ro'
				? 'Imaginea următoare'
				: 'Следующее изображение',
	),
);

$buildVariantSwatch = static function ($colors) {
	if (
		!is_array($colors)
		|| empty($colors)
	) {
		return '';
	}

	$validColors = array();

	foreach ($colors as $color) {
		$color = strtoupper(
			trim(
				(string) $color
			)
		);

		if (
			preg_match(
				'/^#[0-9A-F]{6}$/',
				$color
			)
		) {
			$validColors[] = $color;
		}
	}

	$validColors = array_values(
		array_unique(
			$validColors
		)
	);

	if (empty($validColors)) {
		return '';
	}

	if (count($validColors) === 1) {
		return $validColors[0];
	}

	$count = count($validColors);
	$step = 100 / $count;

	$parts = array();

	foreach (
		$validColors
		as $index => $color
	) {
		$start = round(
			$index * $step,
			4
		);

		$end = round(
			($index + 1) * $step,
			4
		);

		$parts[] =
			$color
			. ' '
			. $start
			. '% '
			. $end
			. '%';
	}

	return 'conic-gradient('
		. implode(', ', $parts)
		. ')';
};

/*
|--------------------------------------------------------------------------
| Other content
|--------------------------------------------------------------------------
*/

$hasDescription = !empty($product->text);

$hasInstruction = !empty(
	$product->instruction
);

$hasComponents = !empty(
	$product->components
);

$hasBrandDescription = !empty(
	$product->brand_text
);
?>

<main class="page">

	<!-- ================================================================
	     BREADCRUMBS
	     ================================================================ -->

	<section class="breacrums">
		<div class="breacrums__container _container">
			<ul class="breacrums__list">

				<li class="breacrums__item">
					<a
						href="/<?= $escape($lclang) ?>"
						class="breacrums__name"
					>
						<?= $escape($home_bc_title) ?>
					</a>
				</li>

				<li class="breacrums__item">
					<a
						href="/<?= $escape($lclang) ?>/<?= $escape(
							$menu['all'][3]->uri
						) ?>"
						class="breacrums__name"
					>
						<?= $escape(
							$menu['all'][3]->title
						) ?>
					</a>
				</li>

				<li class="breacrums__item">
					<a
						href="/<?= $escape($lclang) ?>/<?= $escape(
							$menu['all'][3]->uri
						) ?>/<?= $escape(
							$category->uri
						) ?>"
						class="breacrums__name"
					>
						<?= $escape(
							!empty(
								$category->breadcrumb_title
							)
								? $category->breadcrumb_title
								: $category->title
						) ?>
					</a>
				</li>

				<li
						class="breacrums__item breacrums__item--current"
						aria-current="page"
				>
						<span class="breacrums__name breacrums__name--current">
								<?= $escape(
										$productTitle
								) ?>
						</span>
				</li>

			</ul>
		</div>
	</section>

	<!-- ================================================================
	     PRODUCT PAGE
	     ================================================================ -->

	<section
		class="product-page"
		data-product-page
		data-product-id="<?= (int) $product->id ?>"
		data-selected-variation="<?= (int) $initialVariationId ?>"
	>
		<div class="product-page__container _container">
            <a
	href="/<?= $escape($lclang) ?>/<?= $escape($menu['all'][3]->uri) ?>/<?= $escape($category->uri) ?>"
	class="product-mobile-back"
>
	<img
								src="/app/img/icons-2/arrow-left.svg"
								alt=""
							>

	<span>
		<?= $escape(
			!empty($category->breadcrumb_title)
				? $category->breadcrumb_title
				: $category->title
		) ?>
	</span>
</a>
			<div class="product-page__layout">

				<!-- ====================================================
				     GALLERY
				     ==================================================== -->

				<div
					class="product-gallery"
					data-product-gallery
				>

					<div
						class="product-gallery__thumbs"
						data-gallery-thumbs
						aria-label="<?= $lclang === 'ro'
							? 'Imaginile produsului'
							: 'Изображения товара' ?>"
					>
						<?php foreach (
							$initialImages
							as $index => $image
						) : ?>

							<button
								type="button"
								class="
									product-gallery__thumb
									<?= $index === 0
										? 'product-gallery__thumb--active'
										: '' ?>
								"
								data-gallery-thumb
								data-gallery-index="<?= (int) $index ?>"
								aria-label="<?= $escape(
									$image['alt']
								) ?>"
							>
								<img
									src="<?= $escape(
										$image['src']
									) ?>"
									alt=""
									loading="<?= $index === 0
										? 'eager'
										: 'lazy' ?>"
									decoding="async"
								>
							</button>

						<?php endforeach; ?>
					</div>

					<div class="product-gallery__stage">

						<!-- Badges -->

						<div class="product-gallery__badges">

							<?php if (
								!empty(
									$product->discount_price
								)
								&& !empty(
									$product->sale_percent
								)
							) : ?>
								<span class="product-gallery__badge">
									-<?= (int) $product->sale_percent ?>%
								</span>
							<?php endif; ?>

							<?php if (
								!empty($product->is_new)
							) : ?>
								<span class="product-gallery__badge">
									<?= $escape(
										PRODUCTS_NEW
									) ?>
								</span>
							<?php endif; ?>

							<?php if (
								!empty(
									$product->best_selling
								)
							) : ?>
								<span class="product-gallery__badge">
									<?= $escape(
										PRODUCTS_HIT
									) ?>
								</span>
							<?php endif; ?>

						</div>

						<!-- Wishlist -->

						<?php
						$isProductLiked = in_array(
							(int) $product->id,
							array_map(
								'intval',
								(array) $wishlist
							),
							true
						);
						?>

						<button
							type="button"
							class="
								product-gallery__wishlist
								actions-info-card__like
								<?= $isProductLiked
									? '_liked product-gallery__wishlist--active'
									: '' ?>
							"
							data-prod_id="<?= (int) $product->id ?>"
							data-product-id="<?= (int) $product->id ?>"
							aria-pressed="<?= $isProductLiked
								? 'true'
								: 'false' ?>"
							aria-label="<?= $isProductLiked
								? (
									$lclang === 'ro'
										? 'Elimină din favorite'
										: 'Удалить из избранного'
								)
								: (
									$lclang === 'ro'
										? 'Adaugă la favorite'
										: 'Добавить в избранное'
								) ?>"
						>
							<svg
								width="22"
								height="22"
								viewBox="0 0 24 24"
								fill="none"
								aria-hidden="true"
								xmlns="http://www.w3.org/2000/svg"
							>
								<path
									d="M20.84 4.61C20.33 4.1 19.72 3.69 19.05 3.41C18.38 3.14 17.66 3 16.94 3C16.21 3 15.49 3.14 14.82 3.41C14.15 3.69 13.54 4.1 13.03 4.61L12 5.64L10.97 4.61C9.94 3.58 8.54 3 7.06 3C5.59 3 4.18 3.58 3.15 4.61C2.12 5.64 1.54 7.05 1.54 8.52C1.54 10 2.12 11.4 3.15 12.43L12 21.28L20.84 12.43C21.35 11.92 21.76 11.31 22.04 10.64C22.31 9.97 22.45 9.25 22.45 8.52C22.45 7.8 22.31 7.08 22.04 6.41C21.76 5.74 21.35 5.13 20.84 4.61Z"
									stroke="currentColor"
									stroke-width="1.5"
									stroke-linecap="round"
									stroke-linejoin="round"
								/>
							</svg>
						</button>

						<!-- Previous -->

						<button
							type="button"
							class="
								product-gallery__arrow
								product-gallery__arrow--prev
							"
							data-gallery-prev
							aria-label="<?= $lclang === 'ro'
								? 'Imaginea precedentă'
								: 'Предыдущее изображение' ?>"
						>
							<img
								src="/app/img/icons-2/arrow-left.svg"
								alt=""
							>
						</button>

						<!-- Main image -->

						<button
							type="button"
							class="product-gallery__viewport"
							<?php if ($productHasRealImage) : ?>
								data-gallery-viewport
							<?php endif; ?>
							aria-label="<?= $lclang === 'ro'
								? 'Mărește imaginea'
								: 'Увеличить изображение' ?>"
						>
							<img
								src="<?= $escape(
									$initialImages[0]['src']
								) ?>"
								alt="<?= $escape(
									$initialImages[0]['alt']
								) ?>"
								class="product-gallery__image"
								data-gallery-image
								loading="eager"
								fetchpriority="high"
								decoding="async"
							>
							<?php if ($productHasRealImage) : ?>	
								<span
									class="product-gallery__zoom-indicator"
									data-gallery-zoom-indicator
									aria-hidden="true"
								>
									<svg
										width="20"
										height="20"
										viewBox="0 0 24 24"
										fill="none"
										xmlns="http://www.w3.org/2000/svg"
									>
										<circle
											cx="11"
											cy="11"
											r="6.5"
											stroke="currentColor"
											stroke-width="1.5"
										/>

										<path
											d="M16 16L21 21"
											stroke="currentColor"
											stroke-width="1.5"
											stroke-linecap="round"
										/>

										<path
											d="M11 8V14M8 11H14"
											stroke="currentColor"
											stroke-width="1.5"
											stroke-linecap="round"
										/>
									</svg>
								</span>
							<?php endif; ?>
						</button>

						<!-- Next -->

						<button
							type="button"
							class="
								product-gallery__arrow
								product-gallery__arrow--next
							"
							data-gallery-next
							aria-label="<?= $lclang === 'ro'
								? 'Imaginea următoare'
								: 'Следующее изображение' ?>"
						>
							<img
								src="/app/img/icons-2/arrow-left.svg"
								alt=""
							>
						</button>

						<!-- Counter -->

						<div
							class="product-gallery__counter"
							aria-live="polite"
						>
							<span data-gallery-current>
								1
							</span>

							<span>/</span>

							<span data-gallery-total>
								<?= count(
									$initialImages
								) ?>
							</span>
						</div>

					</div>
				</div>

				<!-- ====================================================
				     PRODUCT DETAILS
				     ==================================================== -->

				<aside class="product-summary">

					<div class="product-summary__inner">

						<div class="product-summary__heading">

							<?php if (
								$productBrand !== ''
							) : ?>

								<a
									href="/<?= $escape($lclang) ?>/<?= $escape(
										$menu['all'][3]->uri
									) ?>/<?= $escape(
										$product->brand_uri
									) ?>"
									class="product-summary__brand"
								>
									<?= $escape(
										$productBrand
									) ?>
								</a>

							<?php endif; ?>

							<div
								class="product-title"
								data-expandable-title
							>
								<h1
									class="
										product-summary__title
										product-title__text
									"
									data-expandable-title-text
								>
									<?= htmlspecialchars(
											$product->title,
											ENT_QUOTES,
											'UTF-8'
									) ?>
								</h1>

								<button
									type="button"
									class="product-title__toggle"
									data-expandable-title-toggle
									data-label-open="<?= $lclang === 'ro'
										? 'Afișează complet'
										: 'Показать полностью' ?>"
									data-label-close="<?= $lclang === 'ro'
										? 'Restrânge'
										: 'Скрыть' ?>"
									aria-expanded="false"
									hidden
								>
									<?= $lclang === 'ro'
										? 'Afișează complet'
										: 'Показать полностью' ?>
								</button>
							</div>

						</div>

						<!-- Price -->

						<div
							class="product-summary__price"
							data-product-price
						>

							<?php if (
								!empty(
									$initialDiscountPrice
								)
							) : ?>

								<span
									class="product-summary__price-current"
									data-price-current
								>
									<?= number_format(
										$initialDiscountPrice,
										0,
										'.',
										' '
									) ?>
									<?= $escape(MDL) ?>
								</span>

								<span
									class="product-summary__price-old"
									data-price-old
								>
									<?= number_format(
										$initialPrice,
										0,
										'.',
										' '
									) ?>
									<?= $escape(MDL) ?>
								</span>

							<?php else : ?>

								<span
									class="product-summary__price-current"
									data-price-current
								>
									<?= number_format(
										$initialPrice,
										0,
										'.',
										' '
									) ?>
									<?= $escape(MDL) ?>
								</span>

								<span
									class="product-summary__price-old"
									data-price-old
									hidden
								></span>

							<?php endif; ?>

						</div>

						<!-- Bonus -->
<!-- Bonus -->

<?php if (!$isB2B) : ?>

	<div
		class="product-summary__bonus"
		data-product-bonus
	>

		<?php if (
			!empty($initialDiscountPrice)
		) : ?>

			<span class="product-summary__bonus-note">
				<?= $lclang === 'ro'
					? 'Pentru produsele cu reducere nu se acordă bonusuri'
					: 'На товары со скидкой бонусы не начисляются' ?>
			</span>

		<?php elseif (
			!empty($client_info)
			&& !empty(
				$client_info->discount
			)
		) : ?>

			<?php
			$bonusAmount =
				(float) $initialPrice
				* (
					(float) $client_info->discount
					/ 100
				);

			$formattedBonus =
				rtrim(
					rtrim(
						number_format(
							$bonusAmount,
							2,
							'.',
							' '
						),
						'0'
					),
					'.'
				);
			?>

			<span class="product-summary__bonus-value">
				+<?= $formattedBonus ?>
				<?= $escape(
					PRODUCTS_BONUSES
				) ?>
			</span>

		<?php else : ?>

			<button
				type="button"
				class="product-summary__bonus-login"
				data-auth-trigger="login"
				aria-label="<?= $escape(
					BONUS_LOGIN
				) ?>"
			>
				<?= $escape(
					BONUS_LOGIN
				) ?>
			</button>

			<span>
				<?= $escape(
					BONUS_INFO
				) ?>
			</span>

		<?php endif; ?>

	</div>

<?php endif; ?>

						<!-- =================================================
						     COLOR VARIANTS
						     ================================================= -->

						<?php if (
							!empty($colorVariations)
						) : ?>

							<div
								class="product-options"
								data-color-select
							>

								<span class="product-options__label">
									<?= $lclang === 'ro'
										? 'Nuanță'
										: 'Оттенок' ?>
								</span>

								<div class="product-color-select">

									<button
										type="button"
										class="product-color-select__trigger"
										data-color-select-trigger
										aria-expanded="false"
									>

										<?php
										$initialSwatch = !empty(
											$initialColorVariation['colors']
										)
											? $buildVariantSwatch(
												$initialColorVariation['colors']
											)
											: '';
										?>

										<span
											class="product-color-select__swatch"
											data-color-select-swatch
											<?= $initialSwatch === ''
												? 'hidden'
												: '' ?>
											<?php if ($initialSwatch !== '') { ?>
												style="--product-swatch: <?= $escape($initialSwatch) ?>;"
											<?php } ?>
										></span>

										<span
											class="product-color-select__value"
											data-color-select-value
										>
											<?php
											$initialColorLabel = '';

											if (
												!empty(
													$initialColorVariation['color']
												)
											) {
												$initialColorLabel =
													$initialColorVariation['color'];
											} elseif (
												!empty(
													$initialColorVariation['title']
												)
											) {
												$initialColorLabel =
													$initialColorVariation['title'];
											}

											if (
												!empty(
													$initialColorVariation['volume']
												)
											) {
												$initialColorLabel .=
													$initialColorLabel !== ''
														? ' · '
															. $initialColorVariation['volume']
														: $initialColorVariation['volume'];
											}
											?>

											<?= $escape(
												$initialColorLabel
											) ?>
										</span>

										<span
											class="product-color-select__chevron"
											aria-hidden="true"
										>
											<img
												src="/app/img/icons-2/arrow-down.svg"
												alt=""
											>
										</span>

									</button>

									<div
										class="product-color-select__dropdown"
										data-color-select-dropdown
										hidden
									>

										<?php foreach (
											$colorVariations
											as $variation
										) : ?>

											<?php
											$isSelected =
												(int) $variation['id']
												=== (int) $initialVariationId;

											$variationLabel = '';

											if (
												!empty(
													$variation['color']
												)
											) {
												$variationLabel =
													$variation['color'];
											} elseif (
												!empty(
													$variation['title']
												)
											) {
												$variationLabel =
													$variation['title'];
											}

											if (
												!empty(
													$variation['volume']
												)
											) {
												$variationLabel .=
													$variationLabel !== ''
														? ' · '
															. $variation['volume']
														: $variation['volume'];
											}
											?>

											<button
												type="button"
												class="
													product-color-select__option
													<?= $isSelected
														? 'product-color-select__option--selected'
														: '' ?>
													<?= empty(
														$variation['available']
													)
														? 'product-color-select__option--disabled'
														: '' ?>
												"
												data-variation-option
												data-variation-id="<?= (int) $variation['id'] ?>"
												<?= empty(
													$variation['available']
												)
													? 'disabled'
													: '' ?>
											>

												<?php
												$variationSwatch =
													$buildVariantSwatch(
														$variation['colors']
													);
												?>

												<?php if (
													$variationSwatch !== ''
												) { ?>
													<span
														class="product-color-select__swatch"
														style="--product-swatch: <?= $escape($variationSwatch) ?>;"
													></span>
												<?php } ?>

												<span class="product-color-select__option-label">
													<?= $escape(
														$variationLabel
													) ?>
												</span>

												<?php if (
													empty(
														$variation['available']
													)
												) : ?>

													<span class="product-color-select__status">
														<?= $lclang === 'ro'
															? 'Indisponibil'
															: 'Недоступно' ?>
													</span>

												<?php endif; ?>

											</button>

										<?php endforeach; ?>

									</div>
								</div>
							</div>

						<?php endif; ?>

						<!-- =================================================
						     VOLUME VARIANTS
						     ================================================= -->

						<?php if (
							!empty($volumeVariations)
						) : ?>

							<div class="product-options">

								<span class="product-options__label">
									<?= $escape(VOLUME) ?>
								</span>

								<div class="product-volume-list">

									<?php foreach (
										$volumeVariations
										as $variation
									) : ?>

										<button
											type="button"
											class="
												product-volume-list__option
												<?= (int) $variation['id']
													=== (int) $initialVariationId
													? 'product-volume-list__option--selected'
													: '' ?>
												<?= empty(
													$variation['available']
												)
													? 'product-volume-list__option--disabled'
													: '' ?>
											"
											data-variation-option
											data-variation-id="<?= (int) $variation['id'] ?>"
											<?= empty(
												$variation['available']
											)
												? 'disabled'
												: '' ?>
										>
											<?= $escape(
												$variation['volume']
											) ?>

											<?php if (
												empty(
													$variation['available']
												)
											) : ?>

												<small>
													<?= $lclang === 'ro'
														? 'Indisponibil'
														: 'Недоступно' ?>
												</small>

											<?php endif; ?>

										</button>

									<?php endforeach; ?>

								</div>
							</div>

						<?php endif; ?>

						

						<!-- =================================================
						     CART ACTIONS
						     ================================================= -->

						<div
							class="product-actions"
							data-product-actions
						>

							<?php if (
								$productInStock
							) : ?>

								<button
									type="button"
									class="
										product-actions__button
										product-actions__button--primary
										add_cart
									"
									data-product-add
								>
									<?= $escape(
										ADD_TO_CART
									) ?>
								</button>

							<?php else : ?>

								<button
									type="button"
									class="
										product-actions__button
										product-actions__button--disabled
									"
									disabled
								>
									<?= $escape(
										STOCK_EMPTY
									) ?>
								</button>

							<?php endif; ?>

						</div>

						<?php if (
							!empty(
								$product->discount_price
							)
						) : ?>

							<p class="product-summary__discount-note">
								<?= $escape(
									CART_DISCOUNT_NOT
								) ?>
							</p>

						<?php endif; ?>

                        <!-- =================================================
						     PRODUCT META
						     ================================================= -->

						<dl class="product-meta">

							<div class="product-meta__row">
								<dt>
									<?= $escape(SKU) ?>
								</dt>

								<dd data-product-sku>
									<?= $escape(
										$initialSku
									) ?>
								</dd>
							</div>

							<?php if (
								!empty($product->barcode)
							) : ?>

								<div class="product-meta__row">
									<dt>
										<?= $escape(
											BARCODE
										) ?>
									</dt>

									<dd>
										<?= $escape(
											$product->barcode
										) ?>
									</dd>
								</div>

							<?php endif; ?>

							<?php if (
								!empty($product->sex)
							) : ?>

								<div class="product-meta__row">
									<dt>
										<?= $escape(SEX) ?>
									</dt>

									<dd>
										<?= $escape(
											sex_translate(
												$product->sex
											)
										) ?>
									</dd>
								</div>

							<?php endif; ?>

							<div
								class="product-meta__row"
								data-product-volume-row
								<?= empty($initialVolume)
									? 'hidden'
									: '' ?>
							>
								<dt>
									<?= $escape(VOLUME) ?>
								</dt>

								<dd data-product-volume>
									<?= $escape(
										$initialVolume
									) ?>
								</dd>
							</div>

						</dl>

						<!-- =================================================
						     ACCORDIONS / AVAILABILITY
						     ================================================= -->

						<div class="product-accordions">

							

							<?php if (
								$hasDescription
							) : ?>

								<div class="product-accordion">

									<button
										type="button"
										class="product-accordion__trigger"
										data-product-accordion-trigger
										aria-expanded="false"
									>
										<span>
											<?= $escape(
												DESCRIPTION
											) ?>
										</span>

										<span aria-hidden="true">
											+
										</span>
									</button>

									<div
										class="product-accordion__content"
										data-product-accordion-content
									>
										<div class="product-accordion__body">

											<div class="product-description-preview">

												<div class="product-description-preview__viewport">

													<div class="product-rich-text">
														<?= $product->text ?>
													</div>

													<div
														class="product-description-preview__fade"
														aria-hidden="true"
													></div>

												</div>

												<button
													type="button"
													class="product-description-preview__button"
													data-reading-drawer-open
												>
													<?= $lclang === 'ro'
														? 'Citește complet'
														: 'Читать полностью' ?>
												</button>

											</div>

										</div>
									</div>
								</div>

							<?php endif; ?>

							<?php if (
								$hasInstruction
							) : ?>

								<div class="product-accordion">

									<button
										type="button"
										class="product-accordion__trigger"
										data-product-accordion-trigger
										aria-expanded="false"
									>
										<span>
											<?= $escape(
												INSTRUCTION
											) ?>
										</span>

										<span aria-hidden="true">
											+
										</span>
									</button>

									<div
										class="
											product-accordion__content
											product-rich-text
										"
										data-product-accordion-content
									>
										<div class="product-accordion__body">
											<?= nl2br(
												$escape(
													$product->instruction
												)
											) ?>
										</div>
									</div>
								</div>

							<?php endif; ?>

							<?php if (
								$hasComponents
							) : ?>

								<div class="product-accordion">

									<button
										type="button"
										class="product-accordion__trigger"
										data-product-accordion-trigger
										aria-expanded="false"
									>
										<span>
											<?= $escape(
												COMPONENTS
											) ?>
										</span>

										<span aria-hidden="true">
											+
										</span>
									</button>

									<div
										class="
											product-accordion__content
											product-rich-text
										"
										data-product-accordion-content
									>
										<div class="product-accordion__body">
											<?= nl2br(
												$escape(
													$product->components
												)
											) ?>
										</div>
									</div>
								</div>

							<?php endif; ?>

							<?php if (
								$hasBrandDescription
							) : ?>

								<div class="product-accordion">

									<button
										type="button"
										class="product-accordion__trigger"
										data-product-accordion-trigger
										aria-expanded="false"
									>
										<span>
											<?= $escape(
												BRAND
											) ?>
										</span>

										<span aria-hidden="true">
											+
										</span>
									</button>

									<div
										class="
											product-accordion__content
											product-rich-text
										"
										data-product-accordion-content
									>
										<div class="product-accordion__body">
											<?= $product->brand_text ?>
										</div>
									</div>

								</div>

							<?php endif; ?>

						</div>

					</div>
				</aside>

			</div>

			<!-- ========================================================
			     LIGHTBOX
			     ======================================================== -->

			<div
				class="product-lightbox"
				data-product-lightbox
				aria-hidden="true"
			>

				<div
					class="product-lightbox__backdrop"
					data-lightbox-close
				></div>

				<div
					class="product-lightbox__dialog"
					role="dialog"
					aria-modal="true"
					aria-label="<?= $lclang === 'ro'
						? 'Galeria produsului'
						: 'Галерея товара' ?>"
				>

					<div class="product-lightbox__header">

						<div
							class="product-lightbox__counter"
							aria-live="polite"
						>
							<span data-lightbox-current>
								1
							</span>

							<span>/</span>

							<span data-lightbox-total>
								<?= count(
									$initialImages
								) ?>
							</span>
						</div>

						<button
							type="button"
							class="product-lightbox__close"
							data-lightbox-close
							aria-label="<?= $lclang === 'ro'
								? 'Închide'
								: 'Закрыть' ?>"
						>
							<span></span>
							<span></span>
						</button>

					</div>

					<div class="product-lightbox__content">

						<div
							class="product-lightbox__thumbs"
							data-lightbox-thumbs
						></div>

						<div
							class="product-lightbox__stage"
							data-lightbox-stage
						>

							<button
								type="button"
								class="
									product-lightbox__arrow
									product-lightbox__arrow--prev
								"
								data-lightbox-prev
								aria-label="<?= $lclang === 'ro'
									? 'Imaginea precedentă'
									: 'Предыдущее изображение' ?>"
							>
								<img
									src="/app/img/icons-2/arrow-left.svg"
									alt=""
								>
							</button>

							<div
								class="product-lightbox__viewport"
								data-lightbox-viewport
							>
								<img
									src=""
									alt=""
									class="product-lightbox__image"
									data-lightbox-image
									draggable="false"
								>
							</div>

							<button
								type="button"
								class="
									product-lightbox__arrow
									product-lightbox__arrow--next
								"
								data-lightbox-next
								aria-label="<?= $lclang === 'ro'
									? 'Imaginea următoare'
									: 'Следующее изображение' ?>"
							>
								<img
									src="/app/img/icons-2/arrow-left.svg"
									alt=""
								>
							</button>

							<button
								type="button"
								class="product-lightbox__zoom-reset"
								data-lightbox-reset
								hidden
							>
								<?= $lclang === 'ro'
									? 'Resetează'
									: 'Сбросить' ?>
							</button>

						</div>
					</div>

				</div>
			</div>

			<!-- ========================================================
			     READING DRAWER
			     ======================================================== -->

			<?php if ($hasDescription) : ?>

				<div
					class="product-reading-drawer"
					data-reading-drawer
					aria-hidden="true"
				>

					<button
						type="button"
						class="product-reading-drawer__backdrop"
						data-reading-drawer-close
						aria-label="<?= $lclang === 'ro'
							? 'Închide'
							: 'Закрыть' ?>"
					></button>

					<aside
						class="product-reading-drawer__panel"
						role="dialog"
						aria-modal="true"
						aria-labelledby="product-reading-drawer-title"
					>

						<header class="product-reading-drawer__header">

							<div>

								<span class="product-reading-drawer__eyebrow">
									<?= $escape(
										$productBrand
									) ?>
								</span>

								<h2
									class="product-reading-drawer__title"
									id="product-reading-drawer-title"
								>
									<?= $escape(
										DESCRIPTION
									) ?>
								</h2>

							</div>

							<button
								type="button"
								class="product-reading-drawer__close"
								data-reading-drawer-close
								aria-label="<?= $lclang === 'ro'
									? 'Închide'
									: 'Закрыть' ?>"
							>
								<span></span>
								<span></span>
							</button>

						</header>

						<div class="product-reading-drawer__scroll">

							<h3 class="product-reading-drawer__product-title">
								<?= $escape(
									$productTitle
								) ?>
							</h3>

							<div class="product-reading-drawer__content product-rich-text">
								<?= $product->text ?>
							</div>

						</div>

					</aside>

				</div>

			<?php endif; ?>

		</div>
	</section>

	<!-- ================================================================
	     PRODUCT PAGE DATA
	     ================================================================ -->

	<script
		type="application/json"
		id="product-page-data"
	><?= json_encode(
		$productPageData,
		JSON_UNESCAPED_UNICODE
		| JSON_UNESCAPED_SLASHES
		| JSON_HEX_TAG
		| JSON_HEX_AMP
		| JSON_HEX_APOS
		| JSON_HEX_QUOT
	) ?></script>

	<!-- ================================================================
	     RECOMMENDATIONS
	     ================================================================ -->
			 <?php if (!empty($similar_products)) { ?>

    <?php
    $this->load->view(
        'layouts/pages/home/products-slider',
        array(
            'products' => $similar_products,

            'title' => $lclang === 'ro'
                ? 'Produse similare'
                : 'Похожие товары',

            'section_key' => 'similar',

            'section_url' => '',

            'lclang' => $lclang,
        )
    );
    ?>

<?php } ?>

<!-- last viwied -->
			<?php
				$this->load->view(
						'layouts/pages/home/recently-viewed',
						array(
								'lclang' => $lclang,
								'current_product_id' =>
										(int) $product->id,
						)
				);
			?>

</main>

<!-- ====================================================================
     MOBILE STICKY CART
     ВАЖНО: вне <main> и вне recommendations.
     Существует всегда, независимо от products_related.
     ==================================================================== -->

<div
	class="product-sticky-bar"
	data-product-sticky-bar
	aria-hidden="true"
>
	<div class="product-sticky-bar__inner">

		<div class="product-sticky-bar__product">

			<p class="product-sticky-bar__title">
				<?= $escape(
					$productTitle
				) ?>
			</p>

			<p
				class="product-sticky-bar__price"
				data-product-sticky-price
			>
				<?= number_format(
					!empty(
						$initialDiscountPrice
					)
						? $initialDiscountPrice
						: $initialPrice,
					0,
					'.',
					' '
				) ?>
				<?= $escape(MDL) ?>
			</p>

		</div>

		<button
			type="button"
			class="product-sticky-bar__button add_cart"
			data-product-sticky-add
			<?= !$productInStock
				? 'disabled'
				: '' ?>
		>
			<?= $productInStock
				? $escape(ADD_TO_CART)
				: $escape(STOCK_EMPTY) ?>
		</button>

	</div>
</div>

<!-- ====================================================================
     AVAILABILITY DRAWER
     ==================================================================== -->

