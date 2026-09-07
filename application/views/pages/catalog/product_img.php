<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$galleryImages = !empty($img)
    ? array_values((array) $img)
    : [];

$galleryImages = array_values(
    array_filter(
        $galleryImages,
        static function ($image) {
            return !empty($image->img);
        }
    )
);

$imagesCount = count($galleryImages);
$hasImages = $imagesCount > 0;
$hasMultipleImages = $imagesCount > 1;
?>

<?php if ($hasMultipleImages) { ?>
    <div
        class="product-gallery__thumbs"
        data-gallery-thumbs
    >
        <?php foreach ($galleryImages as $index => $image) { ?>
            <?php
            $src = newthumbs(
                $image->img,
                'products_variable_img'
            );
            ?>

            <button
                type="button"
                class="product-gallery__thumb <?= $index === 0
                    ? 'is-active'
                    : '' ?>"
                data-gallery-thumb="<?= (int) $index ?>"
                aria-label="Изображение <?= (int) $index + 1 ?>"
                aria-current="<?= $index === 0
                    ? 'true'
                    : 'false' ?>"
            >
                <img
                    src="<?= htmlspecialchars(
                        $src,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    alt=""
                    width="100"
                    height="100"
                    loading="lazy"
                >
            </button>
        <?php } ?>
    </div>
<?php } ?>

<div
    class="product-gallery__viewport"
    data-gallery-viewport
>
    <?php if ($hasImages) { ?>
        <div
            class="product-gallery__track"
            data-gallery-track
        >
            <?php foreach ($galleryImages as $index => $image) { ?>
                <?php
                $src = newthumbs(
                    $image->img,
                    'products_variable_img'
                );
                ?>

                <figure
                    class="product-gallery__slide"
                    data-gallery-slide="<?= (int) $index ?>"
                    aria-hidden="<?= $index === 0
                        ? 'false'
                        : 'true' ?>"
                >
                    <img
                        class="product-gallery__image"
                        src="<?= htmlspecialchars(
                            $src,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt=""
                        width="900"
                        height="900"
                        <?= $index === 0
                            ? 'loading="eager" fetchpriority="high"'
                            : 'loading="lazy"' ?>
                    >
                </figure>
            <?php } ?>
        </div>

        <?php if ($hasMultipleImages) { ?>
            <button
                type="button"
                class="product-gallery__arrow product-gallery__arrow--prev"
                data-gallery-prev
                aria-label="Предыдущее изображение"
            >
                <span aria-hidden="true">←</span>
            </button>

            <button
                type="button"
                class="product-gallery__arrow product-gallery__arrow--next"
                data-gallery-next
                aria-label="Следующее изображение"
            >
                <span aria-hidden="true">→</span>
            </button>
        <?php } ?>
    <?php } else { ?>
        <div class="product-gallery__empty">
            <img
                class="product-gallery__placeholder"
                src="/public/i/null.png"
                alt=""
                width="640"
                height="640"
                loading="eager"
            >

            <p class="product-gallery__empty-text">
                Изображение товара скоро будет добавлено
            </p>
        </div>
    <?php } ?>
</div>

<?php if ($hasMultipleImages) { ?>
    <div
        class="product-gallery__dots"
        data-gallery-dots
    >
        <?php foreach ($galleryImages as $index => $image) { ?>
            <button
                type="button"
                class="product-gallery__dot <?= $index === 0
                    ? 'is-active'
                    : '' ?>"
                data-gallery-dot="<?= (int) $index ?>"
                aria-label="Изображение <?= (int) $index + 1 ?>"
                aria-current="<?= $index === 0
                    ? 'true'
                    : 'false' ?>"
            ></button>
        <?php } ?>
    </div>
<?php } ?>
<?php if ($hasImages) { ?>
	<div class="product-gallery__meta">
		<?php if ($hasMultipleImages) { ?>
			<p
				class="product-gallery__counter"
				aria-live="polite"
			>
				<span data-gallery-current>1</span>
				<span aria-hidden="true"> / </span>
				<span data-gallery-total>
					<?= (int) $imagesCount ?>
				</span>
			</p>
		<?php } ?>

		<button
			type="button"
			class="product-gallery__zoom"
			data-gallery-zoom
			aria-label="Увеличить изображение"
		>
			<svg
				width="22"
				height="22"
				viewBox="0 0 24 24"
				fill="none"
				aria-hidden="true"
				xmlns="http://www.w3.org/2000/svg"
			>
				<circle
					cx="10.8"
					cy="10.8"
					r="6.3"
					stroke="currentColor"
					stroke-width="1.4"
				/>

				<path
					d="M15.4 15.4L20 20"
					stroke="currentColor"
					stroke-width="1.4"
					stroke-linecap="round"
				/>

				<path
					d="M10.8 8V13.6M8 10.8H13.6"
					stroke="currentColor"
					stroke-width="1.4"
					stroke-linecap="round"
				/>
			</svg>

			<span>Увеличить</span>
		</button>
	</div>
<?php } ?>