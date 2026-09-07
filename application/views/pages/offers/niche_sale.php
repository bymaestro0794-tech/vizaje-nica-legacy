<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian =
    isset($lclang)
    && $lclang === 'ro';

$escape = static function ($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$selectedBrandIds = !empty(
    $selected_brand_ids
)
    ? array_map(
        'intval',
        $selected_brand_ids
    )
    : array();

$discountLabels = array(
    '30-39' => '30%',
    '40-49' => '40%',
    '50' => '50%',
);

$buildFilterUrl = static function (
    array $brandIds,
    $discount
) use (
    $page_url
) {
    $query = array();

    if (!empty($brandIds)) {
        $query['brand'] =
            array_values($brandIds);
    }

    if ($discount !== '') {
        $query['discount'] =
            $discount;
    }

    $queryString =
        http_build_query($query);

    return $page_url
        . (
            $queryString !== ''
                ? '?' . $queryString
                : ''
        );
};
?>

<main class="page niche-sale">
    <section class="niche-sale__hero">
        <picture class="niche-sale__hero-picture">

            <img
                class="niche-sale__hero-image"
                src="<?= $escape(
                    $niche_sale['desktop_image']
                ) ?>"
                alt=""
                fetchpriority="high"
            >
        </picture>

        <div class="niche-sale__hero-overlay">
            <div class="niche-sale__hero-container _container">
                <nav
                    class="niche-sale__breadcrumbs"
                    aria-label="<?= $isRomanian
                        ? 'Navigare'
                        : 'Навигация' ?>"
                >
                    <a href="/<?= $escape($lclang) ?>">
                        <?= $isRomanian
                            ? 'Pagina principală'
                            : 'Главная' ?>
                    </a>

                    <span aria-hidden="true">/</span>

                    <span>
                        <?= $escape(
                            $niche_sale['title']
                        ) ?>
                    </span>
                </nav>

                <div class="niche-sale__hero-content">
                    <p class="niche-sale__hero-subtitle">
                        <?= $escape(
                            $niche_sale['subtitle']
                        ) ?>
                    </p>

                    <h1 class="niche-sale__hero-title">
                        <?= $escape(
                            $niche_sale['title']
                        ) ?>
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <section class="niche-sale__discount-cards">
    <div class="_container">
        <div class="niche-sale__discount-list">
            <?php foreach ($discountLabels as $discountValue => $discountLabel) { ?>
                <?php
                $discountUrl = $buildFilterUrl(
                    $selectedBrandIds,
                    $discountValue
                );

                $isDiscountActive =
                    $selected_discount === $discountValue;
                ?>

                <a
                    href="<?= $escape($discountUrl) ?>"
                    class="niche-sale__discount-card
                        <?= $isDiscountActive
                            ? 'is-active'
                            : '' ?>"
                >
                    <span class="niche-sale__discount-value">
                        -<?= $escape($discountLabel) ?>
                    </span>

                    <span class="niche-sale__discount-caption">
                        <?= $isRomanian
                            ? 'reducere'
                            : 'скидка' ?>
                    </span>
                </a>
            <?php } ?>
        </div>
    </div>
</section>

    <section class="niche-sale__catalog">
        <div class="niche-sale__catalog-container _container">
            <div class="niche-sale__toolbar">
                <div class="niche-sale__toolbar-label">
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M4 7H20M7 12H17M10 17H14"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>

                    <span>
                        <?= $isRomanian
                            ? 'Filtre'
                            : 'Фильтры' ?>
                    </span>
                </div>

                <p class="niche-sale__products-count">
                    <?= str_replace(
                        '{count}',
                        $products_count,
                        FOUND_PRODUCTS
                    ) ?>
                </p>

                <div
                    class="niche-sale__toolbar-spacer"
                    aria-hidden="true"
                ></div>
            </div>

            <div class="niche-sale__filter-row">
                <button
                    type="button"
                    class="niche-sale__filter-button"
                    data-niche-brand-open
                    aria-expanded="false"
                    aria-controls="niche-brand-drawer"
                >
                    <span>
                        <?= $isRomanian ? 'Brand' : 'Бренд' ?>
                    </span>

                    <?php if (!empty($selectedBrandIds)) { ?>
                        <span class="niche-sale__filter-value">
                            <?= count($selectedBrandIds) ?>
                        </span>
                    <?php } ?>

                    <span aria-hidden="true" style="padding-bottom: 7px;">⌄</span>
                </button>
            </div>

            <?php if (
                !empty($selectedBrandIds)
                || $selected_discount !== ''
            ) { ?>
                <div class="niche-sale__active-filters">
                    <?php foreach ($brands as $brand) { ?>
                        <?php
                        $brandId =
                            (int) $brand->id;

                        if (!in_array(
                            $brandId,
                            $selectedBrandIds,
                            true
                        )) {
                            continue;
                        }

                        $remainingBrandIds =
                            array_values(
                                array_diff(
                                    $selectedBrandIds,
                                    array($brandId)
                                )
                            );
                        ?>

                        <a
                            href="<?= $escape(
                                $buildFilterUrl(
                                    $remainingBrandIds,
                                    $selected_discount
                                )
                            ) ?>"
                            class="niche-sale__active-filter"
                        >
                            <?= $escape(
                                $brand->title
                            ) ?>

                            <span aria-hidden="true">×</span>
                        </a>
                    <?php } ?>

                    <?php if (
                        $selected_discount !== ''
                        && isset(
                            $discountLabels[
                                $selected_discount
                            ]
                        )
                    ) { ?>
                        <a
                            href="<?= $escape(
                                $buildFilterUrl(
                                    $selectedBrandIds,
                                    ''
                                )
                            ) ?>"
                            class="niche-sale__active-filter"
                        >
                            <?= $escape(
                                $discountLabels[
                                    $selected_discount
                                ]
                            ) ?>

                            <span aria-hidden="true">×</span>
                        </a>
                    <?php } ?>

                    <a
                        href="<?= $escape($page_url) ?>"
                        class="niche-sale__clear-filters"
                    >
                        <?= $isRomanian
                            ? 'Resetează'
                            : 'Очистить всё' ?>
                    </a>
                </div>
            <?php } ?>

            <div class="niche-sale__results">
                <?php if (!empty($products)) { ?>
                    <div class="products_catalog">
                        <div class="main-catalog__body">
                            <?php foreach (
                                $products
                                as $product
                            ) { ?>
                                <?php
                                $this->load->view(
                                    'layouts/pages/product_main',
                                    array(
                                        'item' => $product
                                    )
                                );
                                ?>
                            <?php } ?>
                        </div>

                        <div class="main-catalog__paggination paggination-main-catalog">
                            <?php
                            $this->load->view(
                                'layouts/pages/paginator'
                            );
                            ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="niche-sale__empty">
                        <h2>
                            <?= $isRomanian
                                ? 'Nu am găsit produse'
                                : 'Товары не найдены' ?>
                        </h2>

                        <p>
                            <?= $isRomanian
                                ? 'Încercați să schimbați sau să resetați filtrele.'
                                : 'Попробуйте изменить или сбросить выбранные фильтры.' ?>
                        </p>

                        <a href="<?= $escape($page_url) ?>">
                            <?= $isRomanian
                                ? 'Resetează filtrele'
                                : 'Сбросить фильтры' ?>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
    <div
    class="niche-sale__drawer"
    id="niche-brand-drawer"
    data-niche-brand-drawer
    aria-hidden="true"
>
    <div
        class="niche-sale__drawer-overlay"
        data-niche-brand-close
    ></div>

    <aside
        class="niche-sale__drawer-panel"
        role="dialog"
        aria-modal="true"
        aria-label="<?= $isRomanian
            ? 'Selectați brandurile'
            : 'Выбор брендов' ?>"
    >
        <div class="niche-sale__drawer-header">
            <h2>
                <?= $isRomanian
                    ? 'Branduri'
                    : 'Бренды' ?>
            </h2>

            <button
                type="button"
                class="niche-sale__drawer-close"
                data-niche-brand-close
                aria-label="<?= $isRomanian
                    ? 'Închide'
                    : 'Закрыть' ?>"
            >
                ×
            </button>
        </div>

        <div class="niche-sale__drawer-list">
            <?php foreach ($brands as $brand) { ?>
                <?php
                $brandId = (int) $brand->id;

                $isActive = in_array(
                    $brandId,
                    $selectedBrandIds,
                    true
                );

                $nextBrandIds = $selectedBrandIds;

                if ($isActive) {
                    $nextBrandIds = array_values(
                        array_diff(
                            $nextBrandIds,
                            array($brandId)
                        )
                    );
                } else {
                    $nextBrandIds[] = $brandId;
                }
                ?>

                <a
                    href="<?= $escape(
                        $buildFilterUrl(
                            $nextBrandIds,
                            $selected_discount
                        )
                    ) ?>"
                    class="niche-sale__drawer-option
                        <?= $isActive
                            ? 'is-active'
                            : '' ?>"
                >
                    <span>
                        <?= $escape($brand->title) ?>
                    </span>

                    <span class="niche-sale__drawer-count">
                        <?= (int) $brand->count ?>
                    </span>
                </a>
            <?php } ?>
        </div>
    </aside>
</div>
</main>
