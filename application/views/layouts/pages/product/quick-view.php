<?php
defined('BASEPATH') or exit('No direct script access allowed');

$escape = static function ($value) {
	return htmlspecialchars(
		(string) $value,
		ENT_QUOTES,
		'UTF-8'
	);
};

$isB2B =
	!empty($is_b2b);

$isRomanian =
	$lclang === 'ro';

/*
|--------------------------------------------------------------------------
| Product
|--------------------------------------------------------------------------
*/

$productId =
	(int) $product->id;

$productTitle =
	!empty($product->h1_title)
		? trim((string) $product->h1_title)
		: trim((string) $product->title);

$productTitle =
	preg_replace(
		'/\s+/u',
		' ',
		html_entity_decode(
			$productTitle,
			ENT_QUOTES | ENT_HTML5,
			'UTF-8'
		)
	);

$productBrand =
	!empty($product->brand_title)
		? trim((string) $product->brand_title)
		: '';

$productUrl =
	'/'
	. $lclang
	. '/'
	. $menu['all'][3]->uri
	. '/'
	. $product->cat_uri
	. '/'
	. $product->uri;

/*
|--------------------------------------------------------------------------
| Parent images
|--------------------------------------------------------------------------
*/

$productImages = array();

if (!empty($product->img)) {
	foreach (
		$product->img
		as $index => $image
	) {
		if (empty($image->img)) {
			continue;
		}

		$productImages[] =
			array(
				'src' =>
					newthumbs(
						$image->img,
						'products'
					),

				'alt' =>
					trim(
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
	$productImages[] =
		array(
			'src' =>
				'/app/img/no-image/no_image2.webp',

			'alt' =>
				$isRomanian
					? 'Imaginea produsului va apărea în curând'
					: 'Изображение товара скоро появится',
		);
}

/*
|--------------------------------------------------------------------------
| Variations
|--------------------------------------------------------------------------
*/

$variations = array();

$firstAvailableVariation = null;
$firstDisplayVariation = null;

if (!empty($product->variable)) {
	foreach (
		$product->variable
		as $variation
	) {
		/*
		|--------------------------------------------------------------------------
		| Images
		|--------------------------------------------------------------------------
		*/

		$variationImages =
			array();

		if (!empty($variation->img)) {
			foreach (
				$variation->img
				as $index => $image
			) {
				if (empty($image->img)) {
					continue;
				}

				$variationImages[] =
					array(
						'src' =>
							newthumbs(
								$image->img,
								'products_variable_img'
							),

						'alt' =>
							trim(
								$productBrand
								. ' '
								. $productTitle
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

		$quantity =
			$isB2B
				? (int) (
					$variation->qtyWH
					?? 0
				)
				: (int) (
					$variation->qty
					?? 0
				);

		$available =
			$quantity > 0;

		/*
		|--------------------------------------------------------------------------
		| Colors
		|--------------------------------------------------------------------------
		*/

		$colors =
			!empty($variation->color_data)
			&& is_array(
				$variation->color_data
			)
				? $variation->color_data
				: array();

		/*
		|--------------------------------------------------------------------------
		| Frontend data
		|--------------------------------------------------------------------------
		*/

		$item =
			array(
				'id' =>
					(int) $variation->id,

				'sku' =>
					(string) (
						$variation->SKU
						?? ''
					),

				'title' =>
					(string) (
						$variation->title
						?? ''
					),

				'color' =>
					(string) (
						$variation->color
						?? ''
					),

				'colors' =>
					$colors,

				'volume' =>
					(string) (
						$variation->VolumeVar
						?? ''
					),

				'price' =>
					(float) (
						$variation->price
						?? 0
					),

				'wholesalePrice' =>
					(float) (
						$variation->priceWH
						?? 0
					),

				'discountPrice' =>
					!empty(
						$variation->discount_price
					)
						? (float)
							$variation->discount_price
						: null,

				'quantity' =>
					$quantity,

				'available' =>
					$available,

				'images' =>
					!empty(
						$variationImages
					)
						? $variationImages
						: $productImages,
			);

		$variations[] =
			$item;

		if (
			$firstDisplayVariation
			=== null
		) {
			$firstDisplayVariation =
				$item;
		}

		if (
			$firstAvailableVariation
			=== null
			&& $available
		) {
			$firstAvailableVariation =
				$item;
		}
	}
}

/*
|--------------------------------------------------------------------------
| Initial state
|--------------------------------------------------------------------------
*/

$initialVariation =
	!empty($firstAvailableVariation)
		? $firstAvailableVariation
		: $firstDisplayVariation;

$initialVariationId = 0;

$initialImages =
	$productImages;

$initialPrice =
	$isB2B
		? (float) (
			$product->priceWH
			?? 0
		)
		: (float) (
			$product->price
			?? 0
		);

$initialDiscountPrice =
	!$isB2B
	&& !empty(
		$product->discount_price
	)
		? (float)
			$product->discount_price
		: null;

$initialSku =
	(string) (
		$product->SKU
		?? ''
	);

$initialVolume =
	(string) (
		$product->volume
		?? ''
	);

if (!empty($initialVariation)) {
	$initialVariationId =
		(int) $initialVariation['id'];

	$initialImages =
		$initialVariation['images'];

	$initialPrice =
		$isB2B
			? (float)
				$initialVariation[
					'wholesalePrice'
				]
			: (float)
				$initialVariation[
					'price'
				];

	$initialDiscountPrice =
		$isB2B
			? null
			: $initialVariation[
				'discountPrice'
			];

	$initialSku =
		(string)
			$initialVariation['sku'];

	$initialVolume =
		(string)
			$initialVariation['volume'];
}

/*
|--------------------------------------------------------------------------
| Groups
|--------------------------------------------------------------------------
*/

$colorVariations =
	array();

$volumeVariations =
	array();

foreach (
	$variations
	as $variation
) {
	if (
		!empty(
			$variation['color']
		)
	) {
		$colorVariations[] =
			$variation;
	} elseif (
		!empty(
			$variation['volume']
		)
	) {
		$volumeVariations[] =
			$variation;
	}
}

/*
|--------------------------------------------------------------------------
| Availability
|--------------------------------------------------------------------------
*/

$productAvailable =
	false;

if (!empty($variations)) {
	foreach (
		$variations
		as $variation
	) {
		if (
			!empty(
				$variation['available']
			)
		) {
			$productAvailable =
				true;

			break;
		}
	}
} else {
	$productAvailable =
		$isB2B
			? !empty(
				$product->on_stockWH
			)
			: !empty(
				$product->on_stock
			);
}

/*
|--------------------------------------------------------------------------
| Bonus
|--------------------------------------------------------------------------
*/

$bonusPercent =
	!empty($client_info)
	&& !empty(
		$client_info->discount
	)
		? (float)
			$client_info->discount
		: 0;

$initialEffectivePrice =
	!empty(
		$initialDiscountPrice
	)
		? $initialDiscountPrice
		: $initialPrice;

$bonusAmount =
	!$isB2B
	&& empty(
		$initialDiscountPrice
	)
	&& $bonusPercent > 0
		? $initialEffectivePrice
			* (
				$bonusPercent
				/ 100
			)
		: 0;

/*
|--------------------------------------------------------------------------
| JS state
|--------------------------------------------------------------------------
*/

$quickViewData =
	array(
		'product' =>
			array(
				'id' =>
					$productId,

				'title' =>
					$productTitle,

				'price' =>
					(float) (
						$product->price
						?? 0
					),

				'wholesalePrice' =>
					(float) (
						$product->priceWH
						?? 0
					),

				'discountPrice' =>
					!empty(
						$product->discount_price
					)
						? (float)
							$product->discount_price
						: null,

				'images' =>
					$productImages,
			),

		'initial' =>
			array(
				'variationId' =>
					$initialVariationId,

				'price' =>
					$initialPrice,

				'discountPrice' =>
					$initialDiscountPrice,

				'sku' =>
					$initialSku,

				'volume' =>
					$initialVolume,

				'images' =>
					$initialImages,
			),

		'variations' =>
			$variations,

		'currency' =>
			(string) MDL,

		'language' =>
			$lclang,

		'isB2B' =>
			$isB2B,

		'bonusPercent' =>
			$bonusPercent,

		'productUrl' =>
			$productUrl,
	);

	$initialVariationLabel = '';

if (!empty($initialVariation)) {
	$initialMain =
		!empty($initialVariation['color'])
			? trim(
				(string) $initialVariation['color']
			)
			: trim(
				(string) (
					$initialVariation['title']
					?? ''
				)
			);

	$initialVolume =
		trim(
			(string) (
				$initialVariation['volume']
				?? ''
			)
		);

	$initialVariationLabel =
		$initialMain;

	if (
		$initialVolume !== ''
		&& (
			$initialMain === ''
			|| stripos(
				$initialMain,
				$initialVolume
			) === false
		)
	) {
		$initialVariationLabel .=
			$initialVariationLabel !== ''
				? ' · ' . $initialVolume
				: $initialVolume;
	}
}
?>

<div
	class="quick-view-product"
	data-quick-view-product
	data-product-id="<?= $productId ?>"
	data-selected-variation="<?= $initialVariationId ?>"
>
	<div class="quick-view-product__top">

		<div class="quick-view-product__gallery">

			<div
				class="quick-view-gallery"
				data-quick-view-gallery
			>
				<div
					class="quick-view-gallery__stage"
					data-quick-view-stage
				>
					<button
						type="button"
						class="
							quick-view-gallery__arrow
							quick-view-gallery__arrow--prev
						"
						data-quick-view-prev
						aria-label="<?= $isRomanian
							? 'Imaginea precedentă'
							: 'Предыдущее изображение' ?>"
					>
						<img
							src="/app/img/icons-2/arrow-left.svg"
							alt=""
						>
					</button>

					<img
						src="<?= $escape(
							$initialImages[0]['src']
						) ?>"
						alt="<?= $escape(
							$initialImages[0]['alt']
						) ?>"
						class="quick-view-gallery__image"
						data-quick-view-image
						decoding="async"
					>

					<button
						type="button"
						class="
							quick-view-gallery__arrow
							quick-view-gallery__arrow--next
						"
						data-quick-view-next
						aria-label="<?= $isRomanian
							? 'Imaginea următoare'
							: 'Следующее изображение' ?>"
					>
						<img
							src="/app/img/icons-2/arrow-left.svg"
							alt=""
						>
					</button>

					<div
						class="quick-view-gallery__counter"
					>
						<span
							data-quick-view-current
						>
							1
						</span>

						<span>/</span>

						<span
							data-quick-view-total
						>
							<?= count(
								$initialImages
							) ?>
						</span>
					</div>
				</div>

				<div
					class="quick-view-gallery__thumbs"
					data-quick-view-thumbs
				>
					<?php foreach (
						$initialImages
						as $index => $image
					) : ?>

						<button
							type="button"
							class="
								quick-view-gallery__thumb
								<?= $index === 0
									? 'is-active'
									: '' ?>
							"
							data-quick-view-thumb
							data-image-index="<?= $index ?>"
						>
							<img
								src="<?= $escape(
									$image['src']
								) ?>"
								alt=""
								loading="lazy"
								decoding="async"
							>
						</button>

					<?php endforeach; ?>
				</div>
			</div>

		</div>

		<div class="quick-view-product__info">

			<?php if (
				$productBrand !== ''
			) : ?>
				<p class="quick-view-product__brand">
					<?= $escape(
						$productBrand
					) ?>
				</p>
			<?php endif; ?>

			<h2 class="quick-view-product__title">
				<?= $escape(
					$productTitle
				) ?>
			</h2>

			<a
				href="<?= $escape(
					$productUrl
				) ?>"
				class="quick-view-product__details"
			>
				<?= $isRomanian
					? 'Detalii despre produs'
					: 'Подробнее о товаре' ?>

				<span aria-hidden="true">
					→
				</span>
			</a>

			<div
				class="quick-view-product__price"
				data-quick-view-price
			>
				<span
					class="quick-view-product__price-current"
					data-quick-view-price-current
				>
					<?= number_format(
						$initialEffectivePrice,
						0,
						'.',
						' '
					) ?>
					<?= $escape(MDL) ?>
				</span>

				<span
					class="quick-view-product__price-old"
					data-quick-view-price-old
					<?= empty(
						$initialDiscountPrice
					)
						? 'hidden'
						: '' ?>
				>
					<?php if (
						!empty(
							$initialDiscountPrice
						)
					) : ?>
						<?= number_format(
							$initialPrice,
							0,
							'.',
							' '
						) ?>
						<?= $escape(MDL) ?>
					<?php endif; ?>
				</span>
			</div>

			<?php if (!$isB2B) : ?>
				<div
					class="quick-view-product__bonus"
					data-quick-view-bonus
				>
					<?php if (
						!empty(
							$initialDiscountPrice
						)
					) : ?>

						<?= $isRomanian
							? 'Pentru produsele cu reducere nu se acordă bonusuri'
							: 'На товары со скидкой бонусы не начисляются' ?>

					<?php elseif (
						$bonusAmount > 0
					) : ?>

						+<?= number_format(
							$bonusAmount,
							1,
							'.',
							''
						) ?>
						<?= $escape(
							PRODUCTS_BONUSES
						) ?>

					<?php elseif (
						empty($client_info)
					) : ?>

						<?= $escape(
							BONUS_INFO
						) ?>

					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if (
				!empty(
					$colorVariations
				)
			) : ?>

				<div class="quick-view-option">

					<span class="quick-view-option__label">
						<?= $isRomanian
							? 'Nuanță'
							: 'Оттенок' ?>
					</span>

					<div
	class="quick-view-color-select"
	data-quick-view-color-select
>
	<button
		type="button"
		class="quick-view-color-select__trigger"
		data-quick-view-color-trigger
		aria-expanded="false"
	>
		<span
			class="quick-view-color-select__swatch"
			data-quick-view-color-swatch
			<?php
			$initialColors =
				!empty($initialVariation['colors'])
					? $initialVariation['colors']
					: array();

			if (empty($initialColors)) :
			?>
				hidden
			<?php endif; ?>
			<?php if (!empty($initialColors)) : ?>
				style="--quick-view-swatch: <?php
					if (count($initialColors) === 1) {
						echo $escape(
							$initialColors[0]
						);
					} else {
						$parts = array();
						$count = count($initialColors);
						$step = 100 / $count;

						foreach (
							$initialColors
							as $index => $color
						) {
							$start =
								$index * $step;

							$end =
								($index + 1)
								* $step;

							$parts[] =
								$color
								. ' '
								. $start
								. '% '
								. $end
								. '%';
						}

						echo $escape(
							'conic-gradient('
							. implode(
								', ',
								$parts
							)
							. ')'
						);
					}
				?>;"
			<?php endif; ?>
		></span>

		<span
			class="quick-view-color-select__value"
			data-quick-view-color-value
		>
			<?= $escape(
				$initialVariationLabel
			) ?>
		</span>

		<img
			src="/app/img/icons-2/arrow-down.svg"
			alt=""
		>
	</button>

	<div
		class="quick-view-color-select__dropdown"
		data-quick-view-color-dropdown
		hidden
	>
		<?php foreach (
			$colorVariations
			as $variation
		) : ?>

			<?php
			$label =
				$variation['color']
				?: $variation['title'];

			if (
				!empty(
					$variation['volume']
				)
			) {
				$label .=
					$label !== ''
						? ' · '
							. $variation['volume']
						: $variation['volume'];
			}

			$background = '';

			if (
				!empty(
					$variation['colors']
				)
			) {
				if (
					count(
						$variation['colors']
					) === 1
				) {
					$background =
						$variation['colors'][0];
				} else {
					$parts = array();

					$count =
						count(
							$variation['colors']
						);

					$step =
						100 / $count;

					foreach (
						$variation['colors']
						as $index => $color
					) {
						$start =
							$index * $step;

						$end =
							($index + 1)
							* $step;

						$parts[] =
							$color
							. ' '
							. $start
							. '% '
							. $end
							. '%';
					}

					$background =
						'conic-gradient('
						. implode(
							', ',
							$parts
						)
						. ')';
				}
			}
			?>

			<button
				type="button"
				class="
					quick-view-color-select__option
					<?= (int)
						$variation['id']
						===
						(int)
						$initialVariationId
							? 'is-selected'
							: '' ?>
				"
				data-quick-view-variation
				data-variation-id="<?= (int)
					$variation['id'] ?>"
				<?= empty(
					$variation['available']
				)
					? 'disabled'
					: '' ?>
			>
				<?php if (
					$background !== ''
				) : ?>

					<span
						class="quick-view-color-select__swatch"
						style="--quick-view-swatch: <?= $escape(
							$background
						) ?>;"
					></span>

				<?php endif; ?>

				<span class="quick-view-color-select__option-label">
					<?= $escape(
						$label
					) ?>
				</span>

				<?php if (
					empty(
						$variation['available']
					)
				) : ?>

					<span class="quick-view-color-select__status">
						<?= $isRomanian
							? 'Indisponibil'
							: 'Нет в наличии' ?>
					</span>

				<?php endif; ?>
			</button>

		<?php endforeach; ?>
	</div>
</div>

				</div>

			<?php endif; ?>

			<?php if (
				!empty(
					$volumeVariations
				)
			) : ?>

				<div class="quick-view-option">

					<span class="quick-view-option__label">
						<?= $escape(VOLUME) ?>
					</span>

					<div class="quick-view-volume-list">

						<?php foreach (
							$volumeVariations
							as $variation
						) : ?>

							<button
								type="button"
								class="
									quick-view-volume-list__item
									<?= (int)
										$variation['id']
										===
										(int)
										$initialVariationId
											? 'is-selected'
											: '' ?>
								"
								data-quick-view-variation
								data-variation-id="<?= (int)
									$variation['id'] ?>"
								<?= empty(
									$variation['available']
								)
									? 'disabled'
									: '' ?>
							>
								<?= $escape(
									$variation['volume']
								) ?>
							</button>

						<?php endforeach; ?>

					</div>

				</div>

			<?php endif; ?>

			<div class="quick-view-product__meta">

				<span>
					<?= $escape(SKU) ?>:
				</span>

				<strong
					data-quick-view-sku
				>
					<?= $escape(
						$initialSku
					) ?>
				</strong>

			</div>

		</div>
	</div>

	<div class="quick-view-product__footer">

		<button
			type="button"
			class="quick-view-product__add"
			data-quick-view-add
			<?= !$productAvailable
				? 'disabled'
				: '' ?>
		>
			<?= $productAvailable
				? $escape(
					ADD_TO_CART
				)
				: $escape(
					STOCK_EMPTY
				) ?>
		</button>

	</div>

	<script
		type="application/json"
		data-quick-view-data
	><?= json_encode(
		$quickViewData,
		JSON_UNESCAPED_UNICODE
		| JSON_UNESCAPED_SLASHES
		| JSON_HEX_TAG
		| JSON_HEX_AMP
		| JSON_HEX_APOS
		| JSON_HEX_QUOT
	) ?></script>
</div>