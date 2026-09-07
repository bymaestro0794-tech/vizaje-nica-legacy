<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (empty($sliders)) {
    return;
}

$isRomanian = $lclang === 'ro';

/*
|--------------------------------------------------------------------------
| Valid hero slides
|--------------------------------------------------------------------------
|
| Исключаем записи без desktop-изображения заранее, чтобы:
| - количество в progress было корректным;
| - первый реально отображаемый слайд получил высокий приоритет;
| - JS не работал с пустыми элементами.
|
*/

$heroSlides = array_values(
    array_filter(
        $sliders,
        static function ($slider) {
            return !empty($slider->img);
        }
    )
);

$slidesCount = count($heroSlides);

if ($slidesCount === 0) {
    return;
}

$formatSlideNumber = static function (int $number): string {
    return str_pad(
        (string) $number,
        2,
        '0',
        STR_PAD_LEFT
    );
};
?>

<section
    class="home-hero"
    role="region"
    aria-roledescription="<?= $isRomanian
        ? 'carusel'
        : 'карусель' ?>"
    aria-label="<?= $isRomanian
        ? 'Campanii și oferte Vizaje-Nica'
        : 'Кампании и предложения Vizaje-Nica' ?>"
    data-home-hero
    data-hero-duration="7000"
    data-hero-transition-duration="760"
>
    <div
        class="home-hero__viewport"
        data-hero-viewport
    >
        <div
            class="home-hero__slides"
            data-hero-slides
        >
            <?php foreach ($heroSlides as $index => $slider) : ?>
                <?php
                $isFirstSlide = $index === 0;

                $desktopImage = newthumbs(
                    $slider->img,
                    'sliders'
                );

                $mobileImage = !empty($slider->imgMob)
                    ? newthumbs(
                        $slider->imgMob,
                        'sliders'
                    )
                    : $desktopImage;

                $slideUrl = !empty($slider->uri)
                    ? trim($slider->uri)
                    : '';

                $slideTitle = !empty($slider->title)
                    ? trim(strip_tags($slider->title))
                    : (
                        $isRomanian
                            ? 'Campanie Vizaje-Nica'
                            : 'Кампания Vizaje-Nica'
                    );

                /*
                 * Позже можно перенести эти параметры в админку.
                 * Пока используем безопасные значения по умолчанию.
                 */
                $cursorTheme = 'light';
                $desktopPosition = 'center center';
                $mobilePosition = 'center center';

                $slideNumber = $index + 1;
                ?>

                <article
                    class="home-hero__slide <?= $isFirstSlide
                        ? 'is-active'
                        : '' ?>"
                    data-hero-slide
                    data-hero-index="<?= $index ?>"
                    data-cursor-theme="<?= htmlspecialchars(
                        $cursorTheme,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    aria-hidden="<?= $isFirstSlide
                        ? 'false'
                        : 'true' ?>"
                    style="
                        --hero-object-position-desktop:
                            <?= htmlspecialchars(
                                $desktopPosition,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>;
                        --hero-object-position-mobile:
                            <?= htmlspecialchars(
                                $mobilePosition,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>;
                    "
                >
                    <?php if (!empty($slideUrl)) : ?>
                        <a
                            href="<?= htmlspecialchars(
                                $slideUrl,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="home-hero__link"
                            aria-label="<?= htmlspecialchars(
                                $slideTitle,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            tabindex="<?= $isFirstSlide
                                ? '0'
                                : '-1' ?>"
                            data-hero-link
                        >
                    <?php else : ?>
                        <div class="home-hero__link">
                    <?php endif; ?>

                        <picture class="home-hero__picture">
                            <source
                                media="(max-width: 767px)"
                                srcset="<?= htmlspecialchars(
                                    $mobileImage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <img
                                src="<?= htmlspecialchars(
                                    $desktopImage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                class="home-hero__image"
                                width="1900"
                                height="720"
                                alt="<?= htmlspecialchars(
                                    $slideTitle,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                <?= $isFirstSlide
                                    ? 'fetchpriority="high"'
                                    : 'loading="lazy"' ?>
                                decoding="async"
                                draggable="false"
                            >
                        </picture>

                        <span class="visually-hidden">
                            <?= $isRomanian
                                ? 'Slide '
                                : 'Слайд ' ?>

                            <?= $slideNumber ?>

                            <?= $isRomanian
                                ? ' din '
                                : ' из ' ?>

                            <?= $slidesCount ?>
                        </span>

                    <?php if (!empty($slideUrl)) : ?>
                        </a>
                    <?php else : ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($slidesCount > 1) : ?>
            <button
                type="button"
                class="
                    home-hero__navigation-zone
                    home-hero__navigation-zone--previous
                "
                aria-label="<?= $isRomanian
                    ? 'Campania precedentă'
                    : 'Предыдущая кампания' ?>"
                data-hero-previous
            ></button>

            <button
                type="button"
                class="
                    home-hero__navigation-zone
                    home-hero__navigation-zone--next
                "
                aria-label="<?= $isRomanian
                    ? 'Campania următoare'
                    : 'Следующая кампания' ?>"
                data-hero-next
            ></button>

            <div
                class="home-hero__cursor"
                aria-hidden="true"
                data-hero-cursor
            >
                <span
                    class="home-hero__cursor-icon home-hero__cursor-icon--navigation"
                    data-hero-cursor-navigation
                >
                    <svg
                        width="24"
                        height="24"
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
                </span>

                

<span
	class="home-hero__cursor-label"
	data-hero-cursor-link
>
	<?= $isRomanian ? 'Vezi' : 'Смотреть' ?>
</span>
            </div>

            <div
                class="home-hero__progress"
                aria-label="<?= $isRomanian
                    ? 'Poziția curentă în slider'
                    : 'Текущая позиция в слайдере' ?>"
                aria-live="polite"
            >
                <span
                    class="home-hero__progress-current"
                    data-hero-current
                >
                    <?= $formatSlideNumber(1) ?>
                </span>

                <span
                    class="home-hero__progress-separator"
                    aria-hidden="true"
                >
                    /
                </span>

                <span
                    class="home-hero__progress-total"
                    data-hero-total
                >
                    <?= $formatSlideNumber(
                        $slidesCount
                    ) ?>
                </span>

                <span
                    class="home-hero__progress-track"
                    aria-hidden="true"
                >
                    <span
                        class="home-hero__progress-value"
                        data-hero-progress
                    ></span>
                </span>
            </div>
        <?php endif; ?>
    </div>
</section>