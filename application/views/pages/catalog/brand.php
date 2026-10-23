<?php
$isRomanian = isset($lclang) && strtolower($lclang) === 'ro';
$copy = $isRomanian ? array(
    'filters' => 'Filtre', 'clear' => 'Resetează tot', 'price' => 'Preț',
    'stock' => 'În stoc', 'show' => 'Arată produsele', 'sort' => 'Sortare',
    'quick' => 'Filtre rapide', 'categories' => 'Categorii', 'selected' => 'selectate',
    'close' => 'Închide filtrele', 'sale' => 'Cu reducere', 'apply' => 'Aplică',
    'from' => 'De la', 'to' => 'Până la', 'view' => 'Vizualizare',
) : array(
    'filters' => 'Фильтры', 'clear' => 'Сбросить всё', 'price' => 'Цена',
    'stock' => 'В наличии', 'show' => 'Показать товары', 'sort' => 'Сортировка',
    'quick' => 'Быстрые фильтры', 'categories' => 'Категории', 'selected' => 'выбрано',
    'close' => 'Закрыть фильтры', 'sale' => 'Со скидкой', 'apply' => 'Применить',
    'from' => 'От', 'to' => 'До', 'view' => 'Вид',
);
$sortOptions = array(1 => SORTING1, 2 => SORTING2, 3 => SORTING3, 4 => AT_MAX_DISCOUNT);
$selectedSort = !empty($_GET['sort']) && isset($sortOptions[(int) $_GET['sort']])
    ? (int) $_GET['sort']
    : 1;
$selectedCategories = !empty($_GET['category']) && is_array($_GET['category'])
    ? array_map('intval', $_GET['category'])
    : array();
$minPrice = isset($filter_price_min) ? (float) $filter_price_min : (float) $price_min;
$maxPrice = isset($filter_price_max) ? (float) $filter_price_max : (float) $price_max;
$brandTitle = !empty($brand->h1_title) ? $brand->h1_title : $brand->title;
?>
<main class="page catalog-category-page catalog-brand-page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item"><a href="/<?= $lclang ?>" class="breacrums__name"><?= $home_bc_title ?></a></li>
                <li class="breacrums__item"><a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>" class="breacrums__name"><?= $menu['all'][3]->title ?></a></li>
                <li class="breacrums__item"><p class="breacrums__name"><?= html_escape($brand->breadcrumb_title ?: $brandTitle) ?></p></li>
            </ul>
        </div>
    </section>

    <section class="catalog catalog-category catalog-brand">
        <div class="catalog__container _container">
            <header class="head-catalog catalog-category__heading">
                <h1 class="head-catalog__title"><?= html_escape($brandTitle) ?></h1>
            </header>

            <?php
            $visibleBrandCategories = array();
            $activeBrandCategoryId = !empty($selectedCategories) ? (int) reset($selectedCategories) : 0;
            foreach ((array) $categoryes_filter as $categoryFilter) {
                if ((int) $categoryFilter->id === $activeBrandCategoryId) {
                    array_unshift($visibleBrandCategories, $categoryFilter);
                } else {
                    $visibleBrandCategories[] = $categoryFilter;
                }
            }
            $brandCategoryQuery = $_GET;
            unset($brandCategoryQuery['page'], $brandCategoryQuery['filter_count']);
            ?>
            <?php if (!empty($visibleBrandCategories)) { ?>
                <nav class="category-shortcuts catalog-brand__categories" aria-label="<?= $copy['categories'] ?>">
                    <?php foreach ($visibleBrandCategories as $categoryFilter) {
                        $categoryImage = !empty($categoryFilter->img)
                            ? newthumbs($categoryFilter->img, 'categories', 92, 92, '92x92x1', 1)
                            : '';
                        $categoryIsActive = (int) $categoryFilter->id === $activeBrandCategoryId;
                        $categoryQuery = $brandCategoryQuery;
                        if ($categoryIsActive) {
                            unset($categoryQuery['category']);
                        } else {
                            $categoryQuery['category'] = array((int) $categoryFilter->id);
                        }
                        $categoryQueryString = http_build_query($categoryQuery, '', '&', PHP_QUERY_RFC3986);
                        $categoryUrl = current_url() . ($categoryQueryString !== '' ? '?' . $categoryQueryString : '');
                    ?>
                        <a class="category-shortcut <?= $categoryIsActive ? 'is-active' : '' ?>" href="<?= html_escape($categoryUrl) ?>" <?= $categoryIsActive ? 'aria-current="true"' : '' ?>>
                            <span class="category-shortcut__image">
                                <?php if ($categoryImage) { ?><img src="<?= $categoryImage ?>" alt="" loading="lazy"><?php }
                                else { ?><span aria-hidden="true"><?= html_escape(mb_substr($categoryFilter->title, 0, 1, 'UTF-8')) ?></span><?php } ?>
                            </span>
                            <span class="category-shortcut__label"><?= html_escape($categoryFilter->title) ?></span>
                        </a>
                    <?php } ?>
                </nav>
            <?php } ?>

            <div class="catalog-category__toolbar">
                <div class="catalog-category__toolbar-start">
                    <button class="catalog-filter-trigger" type="button" data-category-filter-open aria-label="<?= $copy['filters'] ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h9m4 0h3M4 17h3m4 0h9M13 4v6M9 14v6"/></svg>
                        <span><?= $copy['filters'] ?></span>
                        <span class="catalog-filter-trigger__count" data-active-filter-count hidden>0</span>
                    </button>
                    <div class="catalog-sort" data-catalog-sort>
                        <span><?= $copy['sort'] ?></span>
                        <button type="button" class="catalog-sort__trigger" data-sort-toggle aria-label="<?= $copy['sort'] ?>: <?= $sortOptions[$selectedSort] ?>" aria-haspopup="dialog" aria-expanded="false">
                            <span data-sort-current><?= $sortOptions[$selectedSort] ?></span>
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7 4v12m0 0-3-3m3 3 3-3M13 16V4m0 0 3 3m-3-3-3 3"/></svg>
                        </button>
                        <select name="sorder" class="catalog-sort__native" aria-label="<?= $copy['sort'] ?>" tabindex="-1">
                            <?php foreach ($sortOptions as $sortValue => $sortLabel) { ?><option value="<?= $sortValue ?>" <?= $selectedSort === $sortValue ? 'selected' : '' ?>><?= $sortLabel ?></option><?php } ?>
                        </select>
                        <div class="catalog-sort__menu" data-sort-menu role="dialog" aria-hidden="true" aria-labelledby="catalog-sort-title" hidden>
                            <div class="catalog-sort__sheet-head"><span class="catalog-sort__handle" aria-hidden="true"></span><h2 id="catalog-sort-title"><?= $isRomanian ? 'Sortare' : 'Сортировать' ?></h2></div>
                            <?php foreach ($sortOptions as $sortValue => $sortLabel) { ?>
                                <button type="button" class="catalog-sort__option" data-sort-option="<?= $sortValue ?>" aria-checked="<?= $selectedSort === $sortValue ? 'true' : 'false' ?>"><span><?= $sortLabel ?></span><svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3.5 8 3 3 6-6"/></svg></button>
                            <?php } ?>
                            <button type="button" class="catalog-sort__apply" data-sort-apply><?= $copy['apply'] ?></button>
                        </div>
                    </div>
                    <div class="catalog-category__quick" aria-label="<?= $copy['quick'] ?>">
                        <button type="button" class="catalog-quick-pill catalog-quick-pill--promo <?= !empty($_GET['discounted']) ? 'is-active' : '' ?>" data-discount-quick-toggle aria-pressed="<?= !empty($_GET['discounted']) ? 'true' : 'false' ?>"><span aria-hidden="true">%</span><?= $copy['sale'] ?></button>
                        <span class="catalog-quick-control" data-quick-filter="price" data-default-label="<?= $copy['price'] ?>">
                            <button type="button" class="catalog-quick-chip" data-filter-open="price"><span data-quick-label><?= $copy['price'] ?></span><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                            <button type="button" class="catalog-quick-clear" data-quick-clear aria-label="<?= $copy['clear'] ?>: <?= $copy['price'] ?>" hidden><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 3 6 6M9 3 3 9"/></svg></button>
                        </span>
                        <button type="button" class="catalog-quick-pill catalog-quick-pill--stock <?= !empty($_GET['in_stock']) ? 'is-active' : '' ?>" data-stock-quick-toggle aria-pressed="<?= !empty($_GET['in_stock']) ? 'true' : 'false' ?>"><?= $copy['stock'] ?></button>
                    </div>
                </div>
                <p class="catalog-toolbar-count" data-category-count data-count-suffix="<?= $isRomanian ? 'produse găsite' : 'товаров найдено' ?>"><strong><?= number_format((int) $products_count, 0, ',', ' ') ?></strong> <?= $isRomanian ? 'produse găsite' : 'товаров найдено' ?></p>
                <div class="catalog-view-toggle" role="group" aria-label="<?= $isRomanian ? 'Vizualizarea catalogului' : 'Вид каталога' ?>">
                    <span class="catalog-view-toggle__label"><span><?= $isRomanian ? 'Vizualizare catalog' : 'Вид каталога' ?></span><span><?= $copy['view'] ?></span></span>
                    <button type="button" class="catalog-view-toggle__button is-active" data-catalog-view="standard" aria-pressed="true" aria-label="<?= $isRomanian ? 'Vizualizare grilă' : 'Вид сеткой' ?>" title="<?= $isRomanian ? 'Vizualizare grilă' : 'Вид сеткой' ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><rect x="1.5" y="1.5" width="10" height="17"/><rect x="14" y="1.5" width="4.5" height="7.5"/><rect x="14" y="11" width="4.5" height="7.5"/></svg></button>
                    <button type="button" class="catalog-view-toggle__button" data-catalog-view="list" aria-pressed="false" aria-label="<?= $isRomanian ? 'Vizualizare listă' : 'Вид списком' ?>" title="<?= $isRomanian ? 'Vizualizare listă' : 'Вид списком' ?>"><svg viewBox="0 0 20 20" aria-hidden="true"><rect x="1.5" y="1.5" width="17" height="7"/><rect x="1.5" y="11.5" width="17" height="7"/></svg></button>
                </div>
            </div>

            <div class="products_catalog">
                <div class="main-catalog__body">
                    <?php foreach ($products as $product) { $this->load->view('layouts/pages/product_main', array('item' => $product, 'is_home_product_card' => true, 'enable_catalog_hover_gallery' => true)); } ?>
                </div>
                <div class="main-catalog__paggination paggination-main-catalog"><?php $this->load->view('layouts/pages/paginator'); ?></div>
                <?php if (!empty(trim(strip_tags((string) $brand->seo_text)))) : ?><section class="category-seo"><?= $brand->seo_text ?></section><?php endif; ?>
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
            <?php foreach ($selectedCategories as $selectedCategoryId) { ?><input type="hidden" name="category[]" value="<?= (int) $selectedCategoryId ?>"><?php } ?>
            <div class="catalog-filter-drawer__content">
                <section class="catalog-filter-section" data-filter-section="stock" data-filter-title="<?= $copy['stock'] ?>">
                    <label class="catalog-stock-toggle"><span><?= $copy['stock'] ?></span><input type="checkbox" name="in_stock" value="1" <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>><i aria-hidden="true"></i></label>
                </section>
                <section class="catalog-filter-section" data-filter-section="price" data-filter-title="<?= $copy['price'] ?>">
                    <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= $copy['price'] ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                    <div class="catalog-filter-section__body" hidden>
                        <div class="catalog-price-fields">
                            <label><span><?= $copy['from'] ?></span><input type="number" name="price_min" min="<?= (float) $price_min ?>" max="<?= (float) $price_max ?>" value="<?= $minPrice ?>" inputmode="numeric"></label><span class="catalog-price-fields__dash">—</span>
                            <label><span><?= $copy['to'] ?></span><input type="number" name="price_max" min="<?= (float) $price_min ?>" max="<?= (float) $price_max ?>" value="<?= $maxPrice ?>" inputmode="numeric"></label>
                        </div>
                        <div class="catalog-price-range" data-price-slider data-min="<?= (float) $price_min ?>" data-max="<?= max(1, (float) $price_max) ?>">
                            <span class="catalog-price-range__track" aria-hidden="true"></span><span class="catalog-price-range__selected" data-price-range-selected aria-hidden="true"></span>
                            <button type="button" class="catalog-price-range__thumb" data-price-range="min" role="slider" aria-label="<?= $isRomanian ? 'Preț minim' : 'Минимальная цена' ?>" aria-valuemin="<?= (float) $price_min ?>" aria-valuemax="<?= $maxPrice ?>" aria-valuenow="<?= $minPrice ?>" style="left:0%"></button>
                            <button type="button" class="catalog-price-range__thumb" data-price-range="max" role="slider" aria-label="<?= $isRomanian ? 'Preț maxim' : 'Максимальная цена' ?>" aria-valuemin="<?= $minPrice ?>" aria-valuemax="<?= max(1, (float) $price_max) ?>" aria-valuenow="<?= $maxPrice ?>" style="left:100%"></button>
                        </div>
                    </div>
                </section>
                <input type="checkbox" name="discounted" value="1" <?= !empty($_GET['discounted']) ? 'checked' : '' ?> hidden>
            </div>
            <footer class="catalog-filter-drawer__footer"><button type="submit" class="catalog-filter-apply"><?= $copy['show'] ?> <span data-filter-apply-count><?= number_format((int) $products_count, 0, ',', ' ') ?></span></button></footer>
        </form>
    </aside>
</main>
