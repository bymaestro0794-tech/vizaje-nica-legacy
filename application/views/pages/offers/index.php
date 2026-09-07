<main class="page offers-page articles-page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a
                        href="/<?= $lclang ?>"
                        class="breadcrums__link"
                    >
                        <?= $home_bc_title ?>
                    </a>
                </li>

                <li class="breacrums__item">
                    <span class="breacrums__name">
                        <?= $page_name ?>
                    </span>
                </li>
            </ul>
        </div>
    </section>

    <section class="stock stock--offers">
        <div class="stock__container _container">

            <header class="stock__header">
                <div>
                    <p class="stock__eyebrow">
                        Vizaje-Nica
                    </p>

                    <h1 class="stock__title">
                        <?= $page_name ?>
                    </h1>
                </div>

                <?php if (!empty($offers)) { ?>
                    <p class="stock__count">
                        <?= count($offers) ?>

                        <?= $lclang === 'ro'
                            ? 'promoții'
                            : 'акций' ?>
                    </p>
                <?php } ?>
            </header>

            <?php if (!empty($offers)) { ?>
                <div class="stock__content">
                    <?php foreach ($offers as $index => $offer) { ?>
                        <?php
                            $offerUrl =
                                '/'
                                . $lclang
                                . '/'
                                . $menu['all'][9]->uri
                                . '/'
                                . $offer->uri;

                            $offerTitle = !empty($offer->title)
                                ? $offer->title
                                : '';

                            $src = !empty($offer->img)
                                ? newthumbs($offer->img, 'offers')
                                : '';

                            $isAboveTheFold = $index < 3;
                        ?>

                        <article class="stock__item stock__item--offer">
                            <a
                                href="<?= $offerUrl ?>"
                                class="stock__image"
                                aria-label="<?= htmlspecialchars(
                                    $offerTitle,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <?php if (!empty($src)) { ?>
                                    <picture>
                                        <img
                                            src="<?= $src ?>"
                                            alt="<?= htmlspecialchars(
                                                $offerTitle,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            loading="<?= $isAboveTheFold
                                                ? 'eager'
                                                : 'lazy' ?>"
                                            fetchpriority="<?= $index === 0
                                                ? 'high'
                                                : 'auto' ?>"
                                            decoding="async"
                                        >
                                    </picture>
                                <?php } else { ?>
                                    <span class="stock__image-placeholder">
                                        Vizaje-Nica
                                    </span>
                                <?php } ?>

                                <span class="stock__image-overlay"></span>

                                <span class="stock__read-more">
                                    <?= $lclang === 'ro'
                                        ? 'Vezi promoția'
                                        : 'Открыть акцию' ?>

                                    <svg
                                        width="17"
                                        height="17"
                                        viewBox="0 0 17 17"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M3.5 8.5H13.5M9.5 4.5L13.5 8.5L9.5 12.5"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </span>
                            </a>

                            <div class="stock__body">
                                <?php if (!empty($offer->date_to)) { ?>
                                    <p class="stock__date stock__date--offer">
                                        <span class="stock__date-label">
                                            <?= BEFORE ?>
                                        </span>

                                        <time>
                                            <?= transformDate(
                                                $offer->date_to,
                                                $lclang
                                            ) ?>
                                        </time>
                                    </p>
                                <?php } ?>

                                <h2 class="stock__name">
                                    <a href="<?= $offerUrl ?>">
                                        <?= $offerTitle ?>
                                    </a>
                                </h2>

                                <a
                                    href="<?= $offerUrl ?>"
                                    class="stock__link"
                                >
                                    <span>
                                        <?= $lclang === 'ro'
                                            ? 'Vezi promoția'
                                            : 'Открыть акцию' ?>
                                    </span>

                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 18 18"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M4 9H14M10 5L14 9L10 13"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="stock__empty">
                    <?= $lclang === 'ro'
                        ? 'Momentan nu există promoții active.'
                        : 'Сейчас нет активных акций.' ?>
                </div>
            <?php } ?>

        </div>
    </section>
</main>