<?php
$isRomanian = isset($lclang) && strtolower($lclang) === 'ro';
$copy = $isRomanian ? array(
    'filters' => 'Filtre', 'clear' => 'Resetează tot', 'category' => 'Categorie', 'brand' => 'Brand',
    'sex' => 'Pentru cine', 'price' => 'Preț', 'show' => 'Arată toate', 'hide' => 'Restrânge',
    'apply' => 'Arată produsele', 'search' => 'Caută brand', 'sort' => 'Sortare', 'quick' => 'Filtre rapide',
    'selected' => 'Selectat', 'close' => 'Închide filtrele', 'remove' => 'Elimină filtrul',
    'found' => 'produse găsite', 'from' => 'De la', 'to' => 'Până la',
) : array(
    'filters' => 'Фильтры', 'clear' => 'Сбросить всё', 'category' => 'Категория', 'brand' => 'Бренд',
    'sex' => 'Для кого', 'price' => 'Цена', 'show' => 'Показать все', 'hide' => 'Свернуть',
    'apply' => 'Показать товары', 'search' => 'Найти бренд', 'sort' => 'Сортировка', 'quick' => 'Быстрые фильтры',
    'selected' => 'Выбрано', 'close' => 'Закрыть фильтры', 'remove' => 'Сбросить фильтр',
    'found' => 'товаров найдено', 'from' => 'От', 'to' => 'До',
);
$activeBrands = !empty($_GET['brand']) && is_array($_GET['brand']) ? array_map('intval', $_GET['brand']) : array();
$activeCategories = !empty($_GET['category']) && is_array($_GET['category']) ? array_map('intval', $_GET['category']) : array();
$discountFilterCategoryIds = array_map(
    static function ($filterCategory) {
        return (int) $filterCategory->id;
    },
    (array) ($filter_categories ?? array())
);
$activeSex = !empty($_GET['sex']) && is_array($_GET['sex']) ? $_GET['sex'] : array();
$priceMin = isset($filter_price_min) ? (float) $filter_price_min : (float) $price_min;
$priceMax = isset($filter_price_max) ? (float) $filter_price_max : (float) $price_max;
$sortOptions = array(1 => SORTING1, 2 => SORTING2, 3 => SORTING3, 4 => AT_MAX_DISCOUNT);
$selectedSort = !empty($_GET['sort']) && isset($sortOptions[(int) $_GET['sort']]) ? (int) $_GET['sort'] : 1;
$brandGroups = array();
foreach (($brands ?? array()) as $brand) {
    $firstCharacter = mb_substr(trim($brand->title), 0, 1, 'UTF-8');
    $groupName = preg_match('/^[0-9]/u', $firstCharacter) ? '0—9' : mb_strtoupper($firstCharacter, 'UTF-8');
    $brandGroups[$groupName][] = $brand;
}
$discountCategoryQuery = $_GET;
unset($discountCategoryQuery['page'], $discountCategoryQuery['filter_count']);
$discountCategoryNavigation = $category_navigation ?? array('parent' => null, 'items' => array());
$discountCategoryResetQuery = $_GET;
unset($discountCategoryResetQuery['page'], $discountCategoryResetQuery['filter_count'], $discountCategoryResetQuery['category']);
$discountCategoryResetQueryString = http_build_query($discountCategoryResetQuery, '', '&', PHP_QUERY_RFC3986);
$discountCategoryResetUrl = $page_url . ($discountCategoryResetQueryString !== '' ? '?' . $discountCategoryResetQueryString : '');
?>
<main class="page catalog-category-page catalog-discounts-page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item"><a href="/<?= $lclang ?>" class="breacrums__name"><?= $home_bc_title ?></a></li>
                <li class="breacrums__item"><p class="breacrums__name"><?= $menu['all'][35]->title ?></p></li>
            </ul>
        </div>
    </section>
    <section class="catalog catalog-category">
        <div class="catalog__container _container">
            <header class="head-catalog catalog-category__heading">
                <h1 class="head-catalog__title"><?= $menu['all'][35]->title ?></h1>
            </header>
            <?php if (!empty($discountCategoryNavigation['items']) || !empty($discountCategoryNavigation['parent'])) { ?>
                <nav class="category-shortcuts catalog-discounts__categories" aria-label="<?= $copy['category'] ?>">
                    <?php if (!empty($discountCategoryNavigation['parent'])) { ?>
                        <a class="category-shortcut category-shortcut--back" href="<?= html_escape($discountCategoryResetUrl) ?>">
                            <span class="category-shortcut__image" aria-hidden="true">←</span>
                            <span class="category-shortcut__label"><?= $isRomanian ? 'Toate categoriile' : 'Все категории' ?></span>
                        </a>
                    <?php } ?>
                    <?php foreach (($discountCategoryNavigation['items'] ?? array()) as $filterCategory) {
                        $categoryQuery = $discountCategoryQuery;
                        unset($categoryQuery['category']);
                        $categoryQuery['category'] = array((int) $filterCategory->id);
                        $categoryQueryString = http_build_query($categoryQuery, '', '&', PHP_QUERY_RFC3986);
                        $categoryUrl = $page_url . ($categoryQueryString !== '' ? '?' . $categoryQueryString : '');
                        $categoryImage = !empty($filterCategory->img)
                            ? newthumbs($filterCategory->img, 'categories', 92, 92, '92x92x1', 1)
                            : '';
                    ?>
                        <a class="category-shortcut <?= $categoryIsActive ? 'is-active' : '' ?>" href="<?= html_escape($categoryUrl) ?>" <?= $categoryIsActive ? 'aria-current="true"' : '' ?>>
                            <span class="category-shortcut__image">
                                <?php if ($categoryImage) { ?><img src="<?= $categoryImage ?>" alt="" loading="lazy"><?php }
                                else { ?><span aria-hidden="true"><?= html_escape(mb_substr($filterCategory->title, 0, 1, 'UTF-8')) ?></span><?php } ?>
                            </span>
                            <span class="category-shortcut__label"><?= html_escape($filterCategory->title) ?></span>
                        </a>
                    <?php } ?>
                </nav>
            <?php } ?>
            <div class="catalog-category__toolbar">
                <div class="catalog-category__toolbar-start">
                    <button class="catalog-filter-trigger" type="button" data-category-filter-open>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h9m4 0h3M4 17h3m4 0h9M13 4v6M9 14v6"/></svg>
                        <span><?= $copy['filters'] ?></span>
                        <span class="catalog-filter-trigger__count" data-active-filter-count hidden>0</span>
                    </button>
                    <div class="catalog-category__quick" aria-label="<?= $copy['quick'] ?>">
                        <span class="catalog-quick-control" data-quick-filter="brand" data-default-label="<?= $copy['brand'] ?>">
                            <button type="button" class="catalog-quick-chip" data-filter-open="brand"><span data-quick-label><?= $copy['brand'] ?></span><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                            <button type="button" class="catalog-quick-clear" data-quick-clear aria-label="<?= $copy['remove'] ?>: <?= $copy['brand'] ?>" hidden><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 3 6 6M9 3 3 9"/></svg></button>
                        </span>
                        <span class="catalog-quick-control" data-quick-filter="price" data-default-label="<?= $copy['price'] ?>">
                            <button type="button" class="catalog-quick-chip" data-filter-open="price"><span data-quick-label><?= $copy['price'] ?></span><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                            <button type="button" class="catalog-quick-clear" data-quick-clear aria-label="<?= $copy['remove'] ?>: <?= $copy['price'] ?>" hidden><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 3 6 6M9 3 3 9"/></svg></button>
                        </span>
                    </div>
                </div>
                <p class="catalog-toolbar-count" data-category-count data-count-suffix="<?= $copy['found'] ?>"><strong><?= number_format((int) $products_count, 0, ',', ' ') ?></strong> <?= $copy['found'] ?></p>
                <div class="catalog-sort" data-catalog-sort>
                    <span><?= $copy['sort'] ?></span>
                    <button type="button" class="catalog-sort__trigger" data-sort-toggle aria-haspopup="listbox" aria-expanded="false">
                        <span data-sort-current><?= $sortOptions[$selectedSort] ?></span>
                        <svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg>
                    </button>
                    <select name="sorder" class="catalog-sort__native" aria-label="<?= $copy['sort'] ?>" tabindex="-1">
                        <?php foreach ($sortOptions as $sortValue => $sortLabel) { ?><option value="<?= $sortValue ?>" <?= $selectedSort === $sortValue ? 'selected' : '' ?>><?= $sortLabel ?></option><?php } ?>
                    </select>
                    <div class="catalog-sort__menu" data-sort-menu role="listbox" aria-label="<?= $copy['sort'] ?>" hidden>
                        <?php foreach ($sortOptions as $sortValue => $sortLabel) { ?>
                            <button type="button" class="catalog-sort__option" role="option" data-sort-option="<?= $sortValue ?>" aria-selected="<?= $selectedSort === $sortValue ? 'true' : 'false' ?>">
                                <span><?= $sortLabel ?></span><svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3.5 8 3 3 6-6"/></svg>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="products_catalog">
                <div class="main-catalog__body">
                    <?php foreach ($products as $product) { $this->load->view('layouts/pages/product_main', array('item' => $product, 'is_home_product_card' => true, 'is_catalog_editorial_card' => true, 'is_discount_catalog_card' => true, 'enable_catalog_hover_gallery' => true)); } ?>
                </div>
                <?php $this->load->view('pages/catalog/_lazy_sentinel'); ?>
                <div class="main-catalog__paggination paggination-main-catalog"><?php $this->load->view('layouts/pages/paginator'); ?></div>
            </div>
        </div>
    </section>

    <div class="catalog-filter-backdrop" data-category-filter-close hidden></div>
    <div class="catalog-sort-backdrop" data-sort-backdrop hidden></div>
    <aside class="catalog-filter-drawer" data-category-filter-drawer data-filter-default-title="<?= $copy['filters'] ?>" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?= $copy['filters'] ?>">
        <header class="catalog-filter-drawer__header">
            <div><p class="catalog-filter-drawer__eyebrow" data-filter-drawer-eyebrow><?= $copy['quick'] ?></p><h2 data-filter-drawer-title><?= $copy['filters'] ?></h2></div>
            <button type="button" class="catalog-filter-drawer__reset" data-filter-reset><?= $copy['clear'] ?></button>
            <button type="button" class="catalog-filter-drawer__close" data-category-filter-close aria-label="<?= $copy['close'] ?>">×</button>
        </header>
        <form class="catalog-filter-form" method="get" action="<?= current_url() ?>" data-category-filter-form data-price-default-min="<?= (float) $price_min ?>" data-price-default-max="<?= (float) $price_max ?>" data-price-label="<?= $copy['price'] ?>">
            <?php foreach ($activeCategories as $activeCategoryId) {
                if (in_array((int) $activeCategoryId, $discountFilterCategoryIds, true)) continue;
            ?><input type="hidden" name="category[]" value="<?= (int) $activeCategoryId ?>"><?php } ?>
            <div class="catalog-filter-drawer__content">
                <?php if (!empty($filter_categories)) { ?>
                    <section class="catalog-filter-section" data-filter-section="category" data-filter-title="<?= $copy['category'] ?>">
                        <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= $copy['category'] ?><?php if ($activeCategories) { ?><small><?= count($activeCategories) ?> <?= $copy['selected'] ?></small><?php } ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                        <div class="catalog-filter-section__body" hidden><div class="catalog-filter-options">
                            <?php foreach ($filter_categories as $category) { ?>
                                <label class="catalog-filter-option"><input type="checkbox" name="category[]" value="<?= (int) $category->id ?>" <?= in_array((int) $category->id, $activeCategories, true) ? 'checked' : '' ?>><span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= html_escape($category->title) ?></span><span class="catalog-filter-option__count"><?= (int) $category->count ?></span></label>
                            <?php } ?>
                        </div></div>
                    </section>
                <?php } ?>
                <?php if (!empty($brands)) { ?>
                    <section class="catalog-filter-section" data-filter-section="brand" data-filter-title="<?= $copy['brand'] ?>">
                        <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= $copy['brand'] ?><?php if ($activeBrands) { ?><small><?= count($activeBrands) ?> <?= $copy['selected'] ?></small><?php } ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                        <div class="catalog-filter-section__body" hidden>
                            <label class="catalog-filter-search"><svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="8.7" cy="8.7" r="5.7"/><path d="m13 13 4 4"/></svg><input type="search" placeholder="<?= $copy['search'] ?>" data-brand-search></label>
                            <div class="catalog-filter-options" data-brand-options>
                                <?php $brandIndex = 0; foreach ($brandGroups as $groupName => $groupBrands) { ?>
                                    <div class="catalog-brand-group <?= $brandIndex >= 6 ? 'is-collapsed' : '' ?>"><h3 class="catalog-brand-group__title"><?= html_escape($groupName) ?></h3>
                                        <?php foreach ($groupBrands as $brand) { ?>
                                            <label class="catalog-filter-option <?= $brandIndex >= 6 ? 'is-collapsed' : '' ?>" data-brand-name="<?= html_escape(mb_strtolower($brand->title, 'UTF-8')) ?>"><input type="checkbox" name="brand[]" value="<?= (int) $brand->id ?>" <?= in_array((int) $brand->id, $activeBrands, true) ? 'checked' : '' ?>><span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= html_escape($brand->title) ?></span><span class="catalog-filter-option__count"><?= (int) $brand->count ?></span></label>
                                        <?php $brandIndex++; } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php if (count($brands) > 6) { ?><button class="catalog-filter-show-more" type="button" data-show-more data-show-label="<?= html_escape($copy['show']) ?>" data-hide-label="<?= html_escape($copy['hide']) ?>"><?= $copy['show'] ?> (<?= count($brands) ?>)</button><?php } ?>
                        </div>
                    </section>
                <?php } ?>
                <section class="catalog-filter-section" data-filter-section="sex" data-filter-title="<?= $copy['sex'] ?>">
                    <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= $copy['sex'] ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                    <div class="catalog-filter-section__body" hidden><?php foreach (array('Женский' => FEMEIE, 'Мужской' => MALE, 'Унисекс' => UNISEX) as $sexValue => $sexLabel) { ?>
                        <label class="catalog-filter-option"><input type="checkbox" name="sex[]" value="<?= html_escape($sexValue) ?>" <?= in_array($sexValue, $activeSex, true) ? 'checked' : '' ?>><span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= $sexLabel ?></span></label>
                    <?php } ?></div>
                </section>
                <section class="catalog-filter-section" data-filter-section="price" data-filter-title="<?= $copy['price'] ?>">
                    <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= $copy['price'] ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                    <div class="catalog-filter-section__body" hidden>
                        <div class="catalog-price-fields"><label><span><?= $copy['from'] ?></span><input type="number" name="price_min" min="<?= (float) $price_min ?>" max="<?= (float) $price_max ?>" value="<?= (float) $priceMin ?>" inputmode="numeric"></label><span class="catalog-price-fields__dash">—</span><label><span><?= $copy['to'] ?></span><input type="number" name="price_max" min="<?= (float) $price_min ?>" max="<?= (float) $price_max ?>" value="<?= (float) $priceMax ?>" inputmode="numeric"></label></div>
                        <div class="catalog-price-range" data-price-slider data-min="<?= (float) $price_min ?>" data-max="<?= max(1, (float) $price_max) ?>"><span class="catalog-price-range__track" aria-hidden="true"></span><span class="catalog-price-range__selected" data-price-range-selected aria-hidden="true"></span><button type="button" class="catalog-price-range__thumb" data-price-range="min" role="slider" aria-label="<?= $isRomanian ? 'Preț minim' : 'Минимальная цена' ?>" aria-valuemin="<?= (float) $price_min ?>" aria-valuemax="<?= (float) $priceMax ?>" aria-valuenow="<?= (float) $priceMin ?>" style="left:0%"></button><button type="button" class="catalog-price-range__thumb" data-price-range="max" role="slider" aria-label="<?= $isRomanian ? 'Preț maxim' : 'Максимальная цена' ?>" aria-valuemin="<?= (float) $priceMin ?>" aria-valuemax="<?= max(1, (float) $price_max) ?>" aria-valuenow="<?= (float) $priceMax ?>" style="left:100%"></button></div>
                    </div>
                </section>
            </div>
            <footer class="catalog-filter-drawer__footer"><p class="catalog-filter-drawer__result"><strong data-filter-result-count><?= (int) $products_count ?></strong> <?= $copy['found'] ?></p><button type="submit" class="catalog-filter-apply"><span><?= $copy['apply'] ?></span><strong data-filter-apply-count><?= (int) $products_count ?></strong></button></footer>
        </form>
    </aside>
</main>
