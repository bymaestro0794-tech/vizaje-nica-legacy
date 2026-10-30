<main
    class="page"
    data-search-page
    data-search-query="<?= html_escape((string) ($search ?? '')) ?>"
    data-search-track-zero-result="<?= !empty($search_track_zero_result) ? '1' : '0' ?>"
>
    <?php
    $isRomanianSearchPage = isset($clang) && strtoupper((string) $clang) === 'RO';
    $searchFilters = (array) ($search_filters ?? array());
    $searchSort = (string) ($search_sort ?? '');
    $searchSortOptions = $isRomanianSearchPage ? array(
        '' => 'Relevanță', '1' => 'Popularitate', '2' => 'Preț crescător',
        '3' => 'Preț descrescător', '4' => 'Reducere', '5' => 'Noutăți',
    ) : array(
        '' => 'По релевантности', '1' => 'По популярности', '2' => 'Сначала дешевле',
        '3' => 'Сначала дороже', '4' => 'По размеру скидки', '5' => 'Сначала новинки',
    );
    $searchFilterLabels = $isRomanianSearchPage ? array(
        'search' => 'Căutare', 'filters' => 'Filtre', 'sort' => 'Sortare', 'brand' => 'Brand', 'category' => 'Categorie',
        'sex' => 'Pentru cine', 'price' => 'Preț', 'stock' => 'În stoc', 'discount' => 'Cu reducere',
        'min' => 'De la', 'max' => 'Până la', 'apply' => 'Aplică', 'reset' => 'Resetează',
        'empty' => 'Nu am găsit produse pentru această căutare.',
    ) : array(
        'search' => 'Поиск', 'filters' => 'Фильтры', 'sort' => 'Сортировка', 'brand' => 'Бренд', 'category' => 'Категория',
        'sex' => 'Для кого', 'price' => 'Цена', 'stock' => 'В наличии', 'discount' => 'Со скидкой',
        'min' => 'От', 'max' => 'До', 'apply' => 'Показать товары', 'reset' => 'Сбросить',
        'empty' => 'По этому запросу товары не найдены.',
    );
    $searchHeading = trim((string) ($search ?? ''));
    if ($searchHeading === '') {
        $searchHeading = (string) ($page_title ?? $searchFilterLabels['search']);
    }
    $searchSexLabels = array(
        'Женский' => defined('FEMEIE') ? FEMEIE : 'Женский',
        'Мужской' => defined('MALE') ? MALE : 'Мужской',
        'Унисекс' => defined('UNISEX') ? UNISEX : 'Унисекс',
    );
    $searchQueryParams = array('search' => (string) ($search ?? ''));
    if ($searchSort !== '') $searchQueryParams['sort'] = $searchSort;
    if (!empty($searchFilters['brands'])) $searchQueryParams['brand'] = $searchFilters['brands'];
    if (!empty($searchFilters['categories'])) $searchQueryParams['category'] = $searchFilters['categories'];
    if (!empty($searchFilters['sex'])) $searchQueryParams['sex'] = $searchFilters['sex'];
    if ($searchFilters['price_min'] !== null) $searchQueryParams['price_min'] = $searchFilters['price_min'];
    if ($searchFilters['price_max'] !== null) $searchQueryParams['price_max'] = $searchFilters['price_max'];
    if (!empty($searchFilters['in_stock'])) $searchQueryParams['in_stock'] = 1;
    if (!empty($searchFilters['discounted'])) $searchQueryParams['discounted'] = 1;
    $searchActiveFilterCount = count((array) ($searchFilters['brands'] ?? array()))
        + count((array) ($searchFilters['categories'] ?? array()))
        + count((array) ($searchFilters['sex'] ?? array()))
        + (!empty($searchFilters['in_stock']) ? 1 : 0)
        + (!empty($searchFilters['discounted']) ? 1 : 0)
        + (($searchFilters['price_min'] !== null || $searchFilters['price_max'] !== null) ? 1 : 0);
    $discountsUri = isset($menu['all'][35]->uri)
        ? trim((string) $menu['all'][35]->uri, '/')
        : ($isRomanianSearchPage ? 'reduceri' : 'skidki');
    $discountsUrl = '/' . rawurlencode((string) $lclang) . '/' . rawurlencode($discountsUri);
    ?>

    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>" class="breacrums__name"><?= $home_bc_title ?></a>
                </li>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= $page_title ?></p>
                </li>
            </ul>
        </div>
    </section>



    <section class="collections">

        <div class="collections__container _container">
            <div class="catalog__head head-catalog">
                <h1 class="head-catalog__title"><?= html_escape($searchHeading) ?></h1>
            </div>

            <?php if (!empty($search_error)) { ?>
                <p class="search-results__notice" role="status">
                    <?= html_escape((string) $search_error) ?>
                </p>
            <?php } ?>

            <?php if (!empty($search_corrected_query)) { ?>
                <p class="search-results__correction">
                    <?= $isRomanianSearchPage ? 'Ați vrut să spuneți:' : 'Возможно, вы искали:' ?>
                    <a href="<?= html_escape((string) ($page_url ?? '') . '?search=' . rawurlencode((string) $search_corrected_query)) ?>">
                        <?= html_escape((string) $search_corrected_query) ?>
                    </a>
                </p>
            <?php } ?>

            <?php if (!empty($search_controls_available) && !empty($search_filter_categories)) { ?>
                <nav class="category-shortcuts search-results__categories" aria-label="<?= html_escape($searchFilterLabels['category']) ?>">
                    <?php foreach (array_slice($search_filter_categories, 0, 16) as $categoryOption) {
                        $nextCategoryIds = (array) ($searchFilters['categories'] ?? array());
                        $categoryId = (int) $categoryOption->id;
                        if (in_array($categoryId, $nextCategoryIds, true)) {
                            $nextCategoryIds = array_values(array_diff($nextCategoryIds, array($categoryId)));
                        } else {
                            $nextCategoryIds[] = $categoryId;
                        }
                        $categoryQuery = $searchQueryParams;
                        if (!empty($nextCategoryIds)) $categoryQuery['category'] = $nextCategoryIds;
                        else unset($categoryQuery['category']);
                        $categoryImage = !empty($categoryOption->img)
                            ? newthumbs($categoryOption->img, 'categories', 92, 92, '92x92x1', 1)
                            : '';
                    ?>
                        <a class="category-shortcut <?= !empty($categoryOption->selected) ? 'is-active' : '' ?>" href="<?= html_escape((string) $page_url . '?' . http_build_query($categoryQuery)) ?>" <?= !empty($categoryOption->selected) ? 'aria-current="true"' : '' ?>>
                            <span class="category-shortcut__image">
                                <?php if ($categoryImage) { ?><img src="<?= $categoryImage ?>" alt="" loading="lazy"><?php }
                                else { ?><span aria-hidden="true"><?= html_escape(mb_substr((string) $categoryOption->title, 0, 1, 'UTF-8')) ?></span><?php } ?>
                            </span>
                            <span class="category-shortcut__label"><?= html_escape((string) $categoryOption->title) ?></span>
                        </a>
                    <?php } ?>
                </nav>
            <?php } ?>

            <?php if (!empty($search_controls_available)) { ?>
                <div class="catalog-category__toolbar search-results__toolbar">
                    <div class="catalog-category__toolbar-start">
                        <button class="catalog-filter-trigger" type="button" data-category-filter-open aria-label="<?= html_escape($searchFilterLabels['filters']) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h9m4 0h3M4 17h3m4 0h9M13 4v6M9 14v6"/></svg>
                            <span><?= html_escape($searchFilterLabels['filters']) ?></span>
                            <span class="catalog-filter-trigger__count" data-active-filter-count <?= $searchActiveFilterCount ? '' : 'hidden' ?>><?= $searchActiveFilterCount ?></span>
                        </button>
                        <div class="catalog-sort" data-catalog-sort>
                            <span><?= html_escape($searchFilterLabels['sort']) ?></span>
                            <button type="button" class="catalog-sort__trigger" data-sort-toggle aria-label="<?= html_escape($searchFilterLabels['sort'] . ': ' . $searchSortOptions[$searchSort]) ?>" aria-haspopup="dialog" aria-expanded="false">
                                <span data-sort-current><?= html_escape($searchSortOptions[$searchSort]) ?></span>
                                <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7 4v12m0 0-3-3m3 3 3-3M13 16V4m0 0 3 3m-3-3-3 3"/></svg>
                            </button>
                            <select name="sort" class="catalog-sort__native" aria-label="<?= html_escape($searchFilterLabels['sort']) ?>" tabindex="-1">
                                <?php foreach ($searchSortOptions as $sortValue => $sortTitle) { ?><option value="<?= html_escape((string) $sortValue) ?>" <?= $searchSort === (string) $sortValue ? 'selected' : '' ?>><?= html_escape($sortTitle) ?></option><?php } ?>
                            </select>
                            <div class="catalog-sort__menu" data-sort-menu role="dialog" aria-hidden="true" aria-labelledby="search-sort-title" hidden>
                                <div class="catalog-sort__sheet-head"><span class="catalog-sort__handle" aria-hidden="true"></span><h2 id="search-sort-title"><?= html_escape($searchFilterLabels['sort']) ?></h2></div>
                                <?php foreach ($searchSortOptions as $sortValue => $sortTitle) { ?>
                                    <button type="button" class="catalog-sort__option" data-sort-option="<?= html_escape((string) $sortValue) ?>" aria-checked="<?= $searchSort === (string) $sortValue ? 'true' : 'false' ?>">
                                        <span><?= html_escape($sortTitle) ?></span><svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3.5 8 3 3 6-6"/></svg>
                                    </button>
                                <?php } ?>
                                <button type="button" class="catalog-sort__apply" data-sort-apply><?= html_escape($searchFilterLabels['apply']) ?></button>
                            </div>
                        </div>
                    </div>
                    <p class="catalog-toolbar-count" data-category-count><strong><?= number_format((int) $products_count, 0, ',', ' ') ?></strong> <?= $isRomanianSearchPage ? 'produse găsite' : 'товаров найдено' ?></p>
                </div>
            <?php } ?>

            <?php if (!empty($products)) { ?>
            <div class="catalog__main main-catalog">
                <div class="main-catalog__head head-main-catalog">
                    <P class="head-main-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
                </div>
                <div class="products_catalog">
                    <div class="main-catalog__body">
                        <?php foreach ($products as $product) { ?>
                            <?php $this->load->view("layouts/pages/product_main", array('item' => $product, 'is_header_search_card' => true)); ?>
                        <?php } ?>
                    </div>
                    <div class="main-catalog__paggination paggination-main-catalog">
                        <? $this->load->view('layouts/pages/paginator'); ?>
                    </div>
                </div>
            </div>
            <?php } else { ?>
                <p class="search-results__empty"><?= html_escape($searchFilterLabels['empty']) ?></p>
                <?php if (!empty($search_fallback_products)) { ?>
                    <section class="search-results__fallback">
                        <h2 class="search-results__fallback-title">
                            <?= $isRomanianSearchPage ? 'Produse cu reducere pe care le poți descoperi' : 'Посмотрите товары со скидками' ?>
                        </h2>
                        <div class="products_catalog">
                            <div class="main-catalog__body">
                                <?php foreach ((array) $search_fallback_products as $product) { ?>
                                    <?php $this->load->view("layouts/pages/product_main", array('item' => $product, 'is_header_search_card' => true)); ?>
                                <?php } ?>
                            </div>
                        </div>
                        <a class="search-results__fallback-link" href="<?= html_escape($discountsUrl) ?>">
                            <?= $isRomanianSearchPage ? 'Vezi toate reducerile' : 'Все скидки' ?>
                            <span aria-hidden="true">→</span>
                        </a>
                    </section>
                <?php } ?>
            <?php } ?>

        </div>
    </section>

    <?php if (!empty($search_controls_available)) { ?>
        <div class="catalog-filter-backdrop" data-category-filter-close hidden></div>
        <div class="catalog-sort-backdrop" data-sort-backdrop hidden></div>
        <aside class="catalog-filter-drawer" data-category-filter-drawer data-filter-default-title="<?= html_escape($searchFilterLabels['filters']) ?>" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?= html_escape($searchFilterLabels['filters']) ?>">
            <header class="catalog-filter-drawer__header">
                <div><p class="catalog-filter-drawer__eyebrow" data-filter-drawer-eyebrow><?= html_escape($searchFilterLabels['search']) ?></p><h2 data-filter-drawer-title><?= html_escape($searchFilterLabels['filters']) ?></h2></div>
                <button type="button" class="catalog-filter-drawer__reset" data-filter-reset><?= html_escape($searchFilterLabels['reset']) ?></button>
                <button type="button" class="catalog-filter-drawer__close" data-category-filter-close aria-label="<?= $isRomanianSearchPage ? 'Închide filtrele' : 'Закрыть фильтры' ?>">×</button>
            </header>
            <form class="catalog-filter-form" method="get" action="<?= html_escape((string) $page_url) ?>" data-category-filter-form data-price-default-min="0" data-price-default-max="0" data-price-label="<?= html_escape($searchFilterLabels['price']) ?>">
                <input type="hidden" name="search" value="<?= html_escape((string) ($search ?? '')) ?>">
                <div class="catalog-filter-drawer__content">
                    <section class="catalog-filter-section" data-filter-section="stock" data-filter-title="<?= html_escape($searchFilterLabels['stock']) ?>">
                        <label class="catalog-stock-toggle"><span><?= html_escape($searchFilterLabels['stock']) ?></span><input type="checkbox" name="in_stock" value="1" <?= !empty($searchFilters['in_stock']) ? 'checked' : '' ?>><i aria-hidden="true"></i></label>
                    </section>
                    <section class="catalog-filter-section" data-filter-section="discount" data-filter-title="<?= html_escape($searchFilterLabels['discount']) ?>">
                        <label class="catalog-stock-toggle"><span><?= html_escape($searchFilterLabels['discount']) ?></span><input type="checkbox" name="discounted" value="1" <?= !empty($searchFilters['discounted']) ? 'checked' : '' ?>><i aria-hidden="true"></i></label>
                    </section>
                    <section class="catalog-filter-section" data-filter-section="price" data-filter-title="<?= html_escape($searchFilterLabels['price']) ?>">
                        <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= html_escape($searchFilterLabels['price']) ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                        <div class="catalog-filter-section__body" hidden>
                            <div class="catalog-price-fields">
                                <label><span><?= html_escape($searchFilterLabels['min']) ?></span><input type="number" name="price_min" min="0" step="1" inputmode="numeric" value="<?= $searchFilters['price_min'] !== null ? html_escape((string) $searchFilters['price_min']) : '' ?>"></label>
                                <span class="catalog-price-fields__dash">—</span>
                                <label><span><?= html_escape($searchFilterLabels['max']) ?></span><input type="number" name="price_max" min="0" step="1" inputmode="numeric" value="<?= $searchFilters['price_max'] !== null ? html_escape((string) $searchFilters['price_max']) : '' ?>"></label>
                            </div>
                        </div>
                    </section>
                    <?php if (!empty($search_filter_categories)) { ?>
                        <section class="catalog-filter-section" data-filter-section="category" data-filter-title="<?= html_escape($searchFilterLabels['category']) ?>">
                            <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= html_escape($searchFilterLabels['category']) ?><?php if (!empty($searchFilters['categories'])) { ?><small><?= count($searchFilters['categories']) ?></small><?php } ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                            <div class="catalog-filter-section__body" hidden><div class="catalog-filter-options">
                                <?php foreach ($search_filter_categories as $categoryOption) { ?>
                                    <label class="catalog-filter-option"><input type="checkbox" name="category[]" value="<?= (int) $categoryOption->id ?>" <?= !empty($categoryOption->selected) ? 'checked' : '' ?>><span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= html_escape((string) $categoryOption->title) ?></span><span class="catalog-filter-option__count"><?= (int) $categoryOption->count ?></span></label>
                                <?php } ?>
                            </div></div>
                        </section>
                    <?php } ?>
                    <?php if (!empty($search_filter_brands)) { ?>
                        <section class="catalog-filter-section" data-filter-section="brand" data-filter-title="<?= html_escape($searchFilterLabels['brand']) ?>">
                            <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= html_escape($searchFilterLabels['brand']) ?><?php if (!empty($searchFilters['brands'])) { ?><small><?= count($searchFilters['brands']) ?></small><?php } ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                            <div class="catalog-filter-section__body" hidden>
                                <label class="catalog-filter-search"><svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="8.7" cy="8.7" r="5.7"/><path d="m13 13 4 4"/></svg><input type="search" placeholder="<?= html_escape($searchFilterLabels['brand']) ?>" data-brand-search></label>
                                <div class="catalog-filter-options">
                                    <?php foreach ($search_filter_brands as $brandOption) { ?>
                                        <label class="catalog-filter-option" data-brand-name="<?= html_escape(mb_strtolower((string) $brandOption->title, 'UTF-8')) ?>"><input type="checkbox" name="brand[]" value="<?= (int) $brandOption->id ?>" <?= !empty($brandOption->selected) ? 'checked' : '' ?>><span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= html_escape((string) $brandOption->title) ?></span><span class="catalog-filter-option__count"><?= (int) $brandOption->count ?></span></label>
                                    <?php } ?>
                                </div>
                            </div>
                        </section>
                    <?php } ?>
                    <section class="catalog-filter-section" data-filter-section="sex" data-filter-title="<?= html_escape($searchFilterLabels['sex']) ?>">
                        <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= html_escape($searchFilterLabels['sex']) ?><?php if (!empty($searchFilters['sex'])) { ?><small><?= count($searchFilters['sex']) ?></small><?php } ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                        <div class="catalog-filter-section__body" hidden><div class="catalog-filter-options">
                            <?php foreach ($searchSexLabels as $sexValue => $sexTitle) { $sexCount = (int) ($search_filter_sex[$sexValue] ?? 0); if ($sexCount === 0 && !in_array($sexValue, (array) ($searchFilters['sex'] ?? array()), true)) continue; ?>
                                <label class="catalog-filter-option"><input type="checkbox" name="sex[]" value="<?= html_escape($sexValue) ?>" <?= in_array($sexValue, (array) ($searchFilters['sex'] ?? array()), true) ? 'checked' : '' ?>><span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= html_escape((string) $sexTitle) ?></span><span class="catalog-filter-option__count"><?= $sexCount ?></span></label>
                            <?php } ?>
                        </div></div>
                    </section>
                </div>
                <footer class="catalog-filter-drawer__footer">
                    <p class="catalog-filter-drawer__result"><strong data-filter-result-count><?= (int) $products_count ?></strong> <?= $isRomanianSearchPage ? 'produse găsite' : 'товаров найдено' ?></p>
                    <button type="submit" class="catalog-filter-apply"><span><?= html_escape($searchFilterLabels['apply']) ?></span><strong data-filter-apply-count><?= (int) $products_count ?></strong></button>
                </footer>
            </form>
        </aside>
    <?php } ?>
</main>
