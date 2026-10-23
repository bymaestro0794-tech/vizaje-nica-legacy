<?php
    $offerTitle = !empty($offer->title)
        ? $offer->title
        : $page_name;

    $offerDescription = !empty($offer->desc)
        ? $offer->desc
        : '';

    $offerLocation = !empty($offer->location)
        ? $offer->location
        : '';

    $offerDateTo = !empty($offer->date_to)
        ? transformDate($offer->date_to, $lclang)
        : '';

    $desktopImage = !empty($offer->imgB)
        ? newthumbs($offer->imgB, 'offers')
        : '';

    $mobileImage = !empty($offer->img)
        ? newthumbs($offer->img, 'offers')
        : '';

    /*
     * Если отдельного desktop-баннера нет,
     * используем обычное изображение акции.
     */
    if (empty($desktopImage)) {
        $desktopImage = $mobileImage;
    }

    /*
     * Если mobile-баннера нет,
     * используем desktop-вариант.
     */
    if (empty($mobileImage)) {
        $mobileImage = $desktopImage;
    }
?>

<main class="page offer-item-page articles-item-page catalog-category-page catalog-discounts-page">

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
                    <a
                        href="/<?= $lclang ?>/<?= $menu['all'][9]->uri ?>"
                        class="breadcrums__link"
                    >
                        <?= $menu['all'][9]->title ?>
                    </a>
                </li>

                <li class="breacrums__item">
                    <span class="breacrums__name">
                        <?= htmlspecialchars(
                            $offerTitle,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>
                </li>
            </ul>
        </div>
    </section>

    <section class="offer-hero">
        <div class="offer-hero__container _container">

            <header class="offer-hero__header">
                <p class="offer-hero__eyebrow">
                    <?= $lclang === 'ro'
                        ? 'Promoție Vizaje-Nica'
                        : 'Акция Vizaje-Nica' ?>
                </p>

                <h1 class="offer-hero__title">
                    <?= $offerTitle ?>
                </h1>
            </header>

            <?php if (!empty($desktopImage)) { ?>
                <div class="offer-hero__media">
                    <picture>
                        <?php if (!empty($mobileImage)) { ?>
                            <source
                                media="(max-width: 767px)"
                                srcset="<?= $mobileImage ?>"
                            >
                        <?php } ?>

                        <img
                            src="<?= $desktopImage ?>"
                            alt="<?= htmlspecialchars(
                                $offerTitle,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="offer-hero__image"
                            fetchpriority="high"
                            decoding="async"
                        >
                    </picture>
                </div>
            <?php } ?>

            <div class="offer-hero__info">

                <?php if (!empty($offerDateTo) || !empty($offerLocation)) { ?>
                    <div class="offer-hero__meta">

                        <?php if (!empty($offerDateTo)) { ?>
                            <div class="offer-hero__meta-item">
                                <span class="offer-hero__meta-icon">
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <rect
                                            x="2.5"
                                            y="4"
                                            width="15"
                                            height="13.5"
                                            rx="2"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                        />

                                        <path
                                            d="M6 2.5V5.5M14 2.5V5.5M2.5 8H17.5"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </span>

                                <div>
                                    <span class="offer-hero__meta-label">
                                        <?= BEFORE ?>
                                    </span>

                                    <strong class="offer-hero__meta-value">
                                        <?= $offerDateTo ?>
                                    </strong>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (!empty($offerLocation)) { ?>
                            <div class="offer-hero__meta-item">
                                <span class="offer-hero__meta-icon">
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M10 18C10 18 16 12.5 16 7.5C16 4.19 13.31 1.5 10 1.5C6.69 1.5 4 4.19 4 7.5C4 12.5 10 18 10 18Z"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                        />

                                        <circle
                                            cx="10"
                                            cy="7.5"
                                            r="2"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                        />
                                    </svg>
                                </span>

                                <div>
                                    <span class="offer-hero__meta-label">
                                        <?= LOCATION ?>
                                    </span>

                                    <strong class="offer-hero__meta-value">
                                        <?= $offerLocation ?>
                                    </strong>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                <?php } ?>

                <?php if (!empty($offerDescription)) { ?>
                    <div class="offer-hero__description">
                        <?= $offerDescription ?>
                    </div>
                <?php } ?>

            </div>
        </div>
    </section>

    <?php
        $offerProducts = !empty($products) && is_array($products)
            ? $products
            : array();

        $offerProductsCount = isset($products_count)
            ? (int) $products_count
            : count($offerProducts);

        $offerProductsTitle = $lclang === 'ro'
            ? 'Produsele promoției'
            : 'Товары акции';

        $offerProductsLabel = $lclang === 'ro'
            ? 'Produse incluse în ofertă'
            : 'Товары, участвующие в акции';

        $offerProductsEmpty = $lclang === 'ro'
            ? 'În această promoție nu există produse disponibile.'
            : 'В этой акции пока нет доступных товаров.';
    ?>

    <section class="offer-catalog" aria-labelledby="offer-catalog-title">
        <div class="offer-catalog__container _container">
            <header class="offer-catalog__header">
                <div>
                    <p class="offer-catalog__eyebrow">
                        <?= htmlspecialchars(
                            $offerProductsLabel,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <h2
                        id="offer-catalog-title"
                        class="offer-catalog__title"
                    >
                        <?= htmlspecialchars(
                            $offerProductsTitle,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h2>
                </div>

                <p class="offer-catalog__count">
                    <?= $offerProductsCount ?>
                </p>
            </header>

            <?php if (!empty($offerProducts)) { ?>
                <div class="products_catalog">
                    <div class="main-catalog__body">
                        <?php foreach ($offerProducts as $product) { ?>
                            <?php
                                $this->load->view(
                                    'layouts/pages/product_main',
                                    array(
                                        'item' => $product,
                                        'is_home_product_card' => true,
                                        'is_catalog_editorial_card' => true,
                                        'is_discount_catalog_card' => true,
                                        'enable_catalog_hover_gallery' => true,
                                    )
                                );
                            ?>
                        <?php } ?>
                    </div>
                </div>

                <div class="main-catalog__paggination paggination-main-catalog">
                    <?php $this->load->view('layouts/pages/paginator'); ?>
                </div>
            <?php } else { ?>
                <p class="offer-catalog__empty">
                    <?= htmlspecialchars(
                        $offerProductsEmpty,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>
            <?php } ?>
        </div>
    </section>

</main>
