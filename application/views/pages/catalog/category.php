<?php
$isRomanian = isset($lclang) && strtolower($lclang) === 'ro';
$filterCopy = $isRomanian ? array(
    'filters' => 'Filtre', 'clear' => 'Resetează tot', 'brand' => 'Brand', 'price' => 'Preț',
    'stock' => 'În stoc', 'sex' => 'Pentru cine', 'show' => 'Arată toate', 'hide' => 'Restrânge',
    'apply' => 'Arată produsele', 'search' => 'Caută brand', 'sort' => 'Sortare',
    'quick' => 'Filtre rapide', 'categories' => 'Categorii', 'selected' => 'Selectat',
    'close' => 'Închide filtrele', 'empty' => 'Nu există opțiuni disponibile în această categorie', 'remove' => 'Elimină filtrul', 'sale' => 'Cu reducere', 'sort_apply' => 'Aplică',
) : array(
    'filters' => 'Фильтры', 'clear' => 'Сбросить всё', 'brand' => 'Бренд', 'price' => 'Цена',
    'stock' => 'В наличии', 'sex' => 'Для кого', 'show' => 'Показать все', 'hide' => 'Свернуть',
    'apply' => 'Показать товары', 'search' => 'Найти бренд', 'sort' => 'Сортировка',
    'quick' => 'Быстрые фильтры', 'categories' => 'Категории', 'selected' => 'Выбрано',
    'close' => 'Закрыть фильтры', 'empty' => 'Для этой категории пока нет доступных фильтров', 'remove' => 'Сбросить фильтр', 'sale' => 'Со скидкой', 'sort_apply' => 'Применить',
);
$activeBrands = !empty($_GET['brand']) && is_array($_GET['brand']) ? array_map('intval', $_GET['brand']) : array();
$activeSex = !empty($_GET['sex']) && is_array($_GET['sex']) ? $_GET['sex'] : array();
$activeFeatures = !empty($_GET['feature']) && is_array($_GET['feature']) ? $_GET['feature'] : array();
$minPrice = isset($filter_price_min) ? $filter_price_min : 0;
$maxPrice = isset($filter_price_max) ? $filter_price_max : $minPrice;
$sortOptions = array(1 => SORTING1, 2 => SORTING2, 3 => SORTING3, 4 => AT_MAX_DISCOUNT);
$selectedSort = !empty($_GET['sort']) && isset($sortOptions[(int) $_GET['sort']]) ? (int) $_GET['sort'] : 1;
$brandGroups = array();
if (!empty($brands)) {
    foreach ($brands as $brand) {
        $firstCharacter = mb_substr(trim($brand->title), 0, 1, 'UTF-8');
        $groupName = preg_match('/^[0-9]/u', $firstCharacter) ? '0—9' : mb_strtoupper($firstCharacter, 'UTF-8');
        $brandGroups[$groupName][] = $brand;
    }
}
$catalogBackCategory = !empty($cat_parent1) ? $cat_parent1 : null;
$catalogBackUrl = $catalogBackCategory
    ? '/' . $lclang . '/' . $menu['all'][3]->uri . '/' . $catalogBackCategory->uri
    : '/' . $lclang . '/' . $menu['all'][3]->uri;
?>
<main class="page catalog-category-page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item"><a href="/<?= $lclang ?>" class="breacrums__name"><?= $menu['all'][1]->breadcrumbTitle ?></a></li>
                <li class="breacrums__item"><a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>" class="breacrums__name"><?= $menu['all'][3]->title ?></a></li>
                <?php if (!empty($cat_parent2)) { ?><li class="breacrums__item"><a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $cat_parent2->uri ?>" class="breacrums__name"><?= $cat_parent2->title ?></a></li><?php } ?>
                <?php if (!empty($cat_parent1)) { ?><li class="breacrums__item"><a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $cat_parent1->uri ?>" class="breacrums__name"><?= $cat_parent1->title ?></a></li><?php } ?>
                <li class="breacrums__item"><p class="breacrums__name"><?= !empty($category->breadcrumb_title) ? $category->breadcrumb_title : $category->title ?></p></li>
            </ul>
        </div>
    </section>

    <section class="catalog catalog-category">
        <div class="catalog__container _container">
            <header class="head-catalog catalog-category__heading">
                <?php if (($site_theme_key ?? 'default') === 'halloween') : ?>
                    <span class="catalog__halloween-decoration catalog__halloween-web" aria-hidden="true"></span>
                    <span class="catalog__halloween-decoration catalog__halloween-bat catalog__halloween-bat--one" aria-hidden="true"></span>
                    <span class="catalog__halloween-decoration catalog__halloween-bat catalog__halloween-bat--two" aria-hidden="true"></span>
                <?php endif; ?>
                <a class="catalog-category__back" href="<?= htmlspecialchars($catalogBackUrl, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= $isRomanian ? 'Înapoi' : 'Назад' ?>">
                    <span class="catalog-category__back-icon" aria-hidden="true">
                        <img src="/app/img/icons-2/arrow-left.svg" alt="">
                    </span>
                </a>
                <h1 class="head-catalog__title"><?= !empty($category->h1_title) ? $category->h1_title : $category->title ?></h1>
            </header>

            <?php if (!empty($category_children)) { ?>
                <nav class="category-shortcuts" aria-label="<?= $filterCopy['categories'] ?>">
                    <?php foreach ($category_children as $child) {
                        $childImage = !empty($child->img) ? newthumbs($child->img, 'categories', 92, 92, '92x92x1', 1) : '';
                    ?>
                        <a class="category-shortcut" href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $child->uri ?>">
                            <span class="category-shortcut__image">
                                <?php if ($childImage) { ?><img src="<?= $childImage ?>" alt="" loading="lazy"><?php }
                                else { ?><span aria-hidden="true"><?= html_escape(mb_substr($child->title, 0, 1, 'UTF-8')) ?></span><?php } ?>
                            </span>
                            <span class="category-shortcut__label"><?= html_escape($child->title) ?></span>
                        </a>
                    <?php } ?>
                </nav>
            <?php } ?>

            <div class="catalog-category__toolbar">
                <div class="catalog-category__toolbar-start">
                    <button class="catalog-filter-trigger" type="button" data-category-filter-open aria-label="<?= $filterCopy['filters'] ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h9m4 0h3M4 17h3m4 0h9M13 4v6M9 14v6"/></svg>
                        <span><?= $filterCopy['filters'] ?></span>
                        <span class="catalog-filter-trigger__count" data-active-filter-count hidden>0</span>
                    </button>
                    <div class="catalog-sort" data-catalog-sort>
                        <span><?= $filterCopy['sort'] ?></span>
                        <button type="button" class="catalog-sort__trigger" data-sort-toggle aria-label="<?= $filterCopy['sort'] ?>: <?= $sortOptions[$selectedSort] ?>" aria-haspopup="dialog" aria-expanded="false">
                            <span data-sort-current><?= $sortOptions[$selectedSort] ?></span>
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7 4v12m0 0-3-3m3 3 3-3M13 16V4m0 0 3 3m-3-3-3 3"/></svg>
                        </button>
                        <select name="sorder" class="catalog-sort__native" aria-label="<?= $filterCopy['sort'] ?>" tabindex="-1">
                            <?php foreach ($sortOptions as $sortValue => $sortLabel) { ?><option value="<?= $sortValue ?>" <?= $selectedSort === $sortValue ? 'selected' : '' ?>><?= $sortLabel ?></option><?php } ?>
                        </select>
                        <div class="catalog-sort__menu" data-sort-menu role="dialog" aria-hidden="true" aria-labelledby="catalog-sort-title" hidden>
                            <div class="catalog-sort__sheet-head">
                                <span class="catalog-sort__handle" aria-hidden="true"></span>
                                <h2 id="catalog-sort-title"><?= $isRomanian ? 'Sortare' : 'Сортировать' ?></h2>
                            </div>
                            <?php foreach ($sortOptions as $sortValue => $sortLabel) { ?>
                                <button type="button" class="catalog-sort__option" data-sort-option="<?= $sortValue ?>" aria-checked="<?= $selectedSort === $sortValue ? 'true' : 'false' ?>">
                                    <span><?= $sortLabel ?></span><svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3.5 8 3 3 6-6"/></svg>
                                </button>
                            <?php } ?>
                            <button type="button" class="catalog-sort__apply" data-sort-apply><?= $filterCopy['sort_apply'] ?></button>
                        </div>
                    </div>
                    <div class="catalog-category__quick" aria-label="<?= $filterCopy['quick'] ?>">
                    <button type="button" class="catalog-quick-pill catalog-quick-pill--promo <?= !empty($_GET['discounted']) ? 'is-active' : '' ?>" data-discount-quick-toggle aria-pressed="<?= !empty($_GET['discounted']) ? 'true' : 'false' ?>"><span aria-hidden="true">%</span><?= $filterCopy['sale'] ?></button>
                    <span class="catalog-quick-control" data-quick-filter="price" data-default-label="<?= $filterCopy['price'] ?>">
                        <button type="button" class="catalog-quick-chip" data-filter-open="price"><span data-quick-label><?= $filterCopy['price'] ?></span><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                        <button type="button" class="catalog-quick-clear" data-quick-clear aria-label="<?= $filterCopy['remove'] ?>: <?= $filterCopy['price'] ?>" hidden><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 3 6 6M9 3 3 9"/></svg></button>
                    </span>
                    <span class="catalog-quick-control" data-quick-filter="brand" data-default-label="<?= $filterCopy['brand'] ?>">
                        <button type="button" class="catalog-quick-chip" data-filter-open="brand"><span data-quick-label><?= $filterCopy['brand'] ?></span><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                        <button type="button" class="catalog-quick-clear" data-quick-clear aria-label="<?= $filterCopy['remove'] ?>: <?= $filterCopy['brand'] ?>" hidden><svg viewBox="0 0 12 12" aria-hidden="true"><path d="m3 3 6 6M9 3 3 9"/></svg></button>
                    </span>
                    <button type="button" class="catalog-quick-pill catalog-quick-pill--stock <?= !empty($_GET['in_stock']) ? 'is-active' : '' ?>" data-stock-quick-toggle aria-pressed="<?= !empty($_GET['in_stock']) ? 'true' : 'false' ?>"><?= $filterCopy['stock'] ?></button>
                    </div>
                </div>
                <p class="catalog-toolbar-count" data-category-count data-count-suffix="<?= $isRomanian ? 'produse găsite' : 'товаров найдено' ?>"><strong><?= number_format((int) $products_count, 0, ',', ' ') ?></strong> <?= $isRomanian ? 'produse găsite' : 'товаров найдено' ?></p>
                <div class="catalog-view-toggle" role="group" aria-label="<?= $isRomanian ? 'Vizualizarea catalogului' : 'Вид каталога' ?>">
                    <span class="catalog-view-toggle__label"><span><?= $isRomanian ? 'Vizualizare catalog' : 'Вид каталога' ?></span><span><?= $isRomanian ? 'Vizualizare' : 'Вид' ?></span></span>
                    <button type="button" class="catalog-view-toggle__button is-active" data-catalog-view="standard" aria-pressed="true" aria-label="<?= $isRomanian ? 'Vizualizare grilă' : 'Вид сеткой' ?>" title="<?= $isRomanian ? 'Vizualizare grilă' : 'Вид сеткой' ?>">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><rect x="1.5" y="1.5" width="10" height="17"/><rect x="14" y="1.5" width="4.5" height="7.5"/><rect x="14" y="11" width="4.5" height="7.5"/></svg>
                    </button>
                    <button type="button" class="catalog-view-toggle__button" data-catalog-view="list" aria-pressed="false" aria-label="<?= $isRomanian ? 'Vizualizare listă' : 'Вид списком' ?>" title="<?= $isRomanian ? 'Vizualizare listă' : 'Вид списком' ?>">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><rect x="1.5" y="1.5" width="17" height="7"/><rect x="1.5" y="11.5" width="17" height="7"/></svg>
                    </button>
                </div>
            </div>

            <div class="products_catalog">
                <div class="main-catalog__body">
                    <?php foreach ($products as $product) { $this->load->view('layouts/pages/product_main', array('item' => $product, 'is_home_product_card' => true, 'is_catalog_editorial_card' => true, 'enable_catalog_hover_gallery' => true)); } ?>
                </div>
                <?php $this->load->view('pages/catalog/_lazy_sentinel'); ?>
                <div class="main-catalog__paggination paggination-main-catalog"><?php $this->load->view('layouts/pages/paginator'); ?></div>
            </div>
            <?php if (!empty($category->seo_text) && trim(strip_tags((string) $category->seo_text)) !== '') { ?>
                <section class="category-seo" aria-label="<?= html_escape($category->title) ?>">
                    <?= $category->seo_text ?>
                </section>
            <?php } ?>
        </div>
    </section>

    <div class="catalog-filter-backdrop" data-category-filter-close hidden></div>
    <div class="catalog-sort-backdrop" data-sort-backdrop hidden></div>
    <aside class="catalog-filter-drawer" data-category-filter-drawer data-filter-default-title="<?= $filterCopy['filters'] ?>" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?= $filterCopy['filters'] ?>">
        <header class="catalog-filter-drawer__header">
            <div><p class="catalog-filter-drawer__eyebrow" data-filter-drawer-eyebrow><?= $filterCopy['quick'] ?></p><h2 data-filter-drawer-title><?= $filterCopy['filters'] ?></h2></div>
            <button type="button" class="catalog-filter-drawer__reset" data-filter-reset><?= $filterCopy['clear'] ?></button>
            <button type="button" class="catalog-filter-drawer__close" data-category-filter-close aria-label="<?= $filterCopy['close'] ?>">×</button>
        </header>

        <form class="catalog-filter-form" method="get" action="<?= current_url() ?>" data-category-filter-form data-price-default-min="<?= (float) $price_min ?>" data-price-default-max="<?= (float) $price_max ?>" data-price-label="<?= $isRomanian ? 'Preț' : 'Цена' ?>">
            <input type="checkbox" name="discounted" value="1" <?= !empty($_GET['discounted']) ? 'checked' : '' ?> hidden>
            <div class="catalog-filter-drawer__content">
                <section class="catalog-filter-section" data-filter-section="stock" data-filter-title="<?= $filterCopy['stock'] ?>">
                    <label class="catalog-stock-toggle"><span><?= $filterCopy['stock'] ?></span><input type="checkbox" name="in_stock" value="1" <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>><i aria-hidden="true"></i></label>
                </section>

                <section class="catalog-filter-section" data-filter-section="price" data-filter-title="<?= $filterCopy['price'] ?>">
                    <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= $filterCopy['price'] ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                    <div class="catalog-filter-section__body" hidden>
                        <div class="catalog-price-fields">
                            <label><span><?= $isRomanian ? 'De la' : 'От' ?></span><input type="number" name="price_min" min="<?= (float) $price_min ?>" max="<?= (float) $price_max ?>" value="<?= (float) $minPrice ?>" inputmode="numeric"></label>
                            <span class="catalog-price-fields__dash">—</span>
                            <label><span><?= $isRomanian ? 'Până la' : 'До' ?></span><input type="number" name="price_max" min="<?= (float) $price_min ?>" max="<?= (float) $price_max ?>" value="<?= (float) $maxPrice ?>" inputmode="numeric"></label>
                        </div>
                        <div class="catalog-price-range" data-price-slider data-min="<?= (float) $price_min ?>" data-max="<?= max(1, (float) $price_max) ?>">
                            <span class="catalog-price-range__track" aria-hidden="true"></span>
                            <span class="catalog-price-range__selected" data-price-range-selected aria-hidden="true"></span>
                            <button type="button" class="catalog-price-range__thumb" data-price-range="min" role="slider" aria-label="<?= $isRomanian ? 'Preț minim' : 'Минимальная цена' ?>" aria-valuemin="<?= (float) $price_min ?>" aria-valuemax="<?= (float) $maxPrice ?>" aria-valuenow="<?= (float) $minPrice ?>" style="left:0%"></button>
                            <button type="button" class="catalog-price-range__thumb" data-price-range="max" role="slider" aria-label="<?= $isRomanian ? 'Preț maxim' : 'Максимальная цена' ?>" aria-valuemin="<?= (float) $minPrice ?>" aria-valuemax="<?= max(1, (float) $price_max) ?>" aria-valuenow="<?= (float) $maxPrice ?>" style="left:100%"></button>
                        </div>
                    </div>
                </section>

                <?php if (!empty($brands)) { ?>
                    <section class="catalog-filter-section" data-filter-section="brand" data-filter-title="<?= $filterCopy['brand'] ?>">
                        <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= $filterCopy['brand'] ?><?php if ($activeBrands) { ?><small><?= count($activeBrands) ?> <?= $filterCopy['selected'] ?></small><?php } ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                        <div class="catalog-filter-section__body" hidden>
                            <label class="catalog-filter-search"><svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="8.7" cy="8.7" r="5.7"/><path d="m13 13 4 4"/></svg><input type="search" placeholder="<?= $filterCopy['search'] ?>" data-brand-search></label>
                            <div class="catalog-filter-options" data-brand-options>
                                <?php $brandIndex = 0; foreach ($brandGroups as $groupName => $groupBrands) { ?>
                                    <div class="catalog-brand-group <?= $brandIndex >= 6 ? 'is-collapsed' : '' ?>">
                                        <h3 class="catalog-brand-group__title"><?= html_escape($groupName) ?></h3>
                                        <?php foreach ($groupBrands as $brand) { ?>
                                    <label class="catalog-filter-option <?= $brandIndex >= 6 ? 'is-collapsed' : '' ?>" data-brand-name="<?= html_escape(mb_strtolower($brand->title, 'UTF-8')) ?>">
                                        <input type="checkbox" name="brand[]" value="<?= (int) $brand->id ?>" <?= in_array((int) $brand->id, $activeBrands, true) ? 'checked' : '' ?>>
                                        <span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= html_escape($brand->title) ?></span><span class="catalog-filter-option__count"><?= (int) $brand->count ?></span>
                                    </label>
                                        <?php $brandIndex++; } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php if (count($brands) > 6) { ?><button class="catalog-filter-show-more" type="button" data-show-more data-show-label="<?= html_escape($filterCopy['show']) ?>" data-hide-label="<?= html_escape($filterCopy['hide']) ?>"><?= $filterCopy['show'] ?> (<?= count($brands) ?>)</button><?php } ?>
                        </div>
                    </section>
                <?php } ?>

                <section class="catalog-filter-section" data-filter-section="sex" data-filter-title="<?= $filterCopy['sex'] ?>">
                    <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= $filterCopy['sex'] ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                    <div class="catalog-filter-section__body" hidden>
                        <?php foreach (array('Женский' => FEMEIE, 'Мужской' => MALE, 'Унисекс' => UNISEX) as $sexValue => $sexLabel) { ?>
                            <label class="catalog-filter-option"><input type="checkbox" name="sex[]" value="<?= $sexValue ?>" <?= in_array($sexValue, $activeSex, true) ? 'checked' : '' ?>><span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= $sexLabel ?></span></label>
                        <?php } ?>
                    </div>
                </section>

                <?php if (!empty($filter_features)) { foreach ($filter_features as $feature) {
                    $selectedValues = isset($activeFeatures[$feature['id']]) && is_array($activeFeatures[$feature['id']]) ? array_map('intval', $activeFeatures[$feature['id']]) : array();
                ?>
                    <section class="catalog-filter-section" data-filter-section="feature-<?= (int) $feature['id'] ?>" data-filter-title="<?= html_escape($feature['name']) ?>">
                        <button type="button" class="catalog-filter-section__heading" aria-expanded="false"><span><?= html_escape($feature['name']) ?><?php if ($selectedValues) { ?><small><?= count($selectedValues) ?> <?= $filterCopy['selected'] ?></small><?php } ?></span><svg class="catalog-filter-section__chevron" viewBox="0 0 12 12" aria-hidden="true"><path d="m3 4.5 3 3 3-3"/></svg></button>
                        <div class="catalog-filter-section__body" hidden>
                            <div class="catalog-filter-options">
                                <?php foreach ($feature['values'] as $value) { ?>
                                    <label class="catalog-filter-option">
                                        <input type="checkbox" name="feature[<?= (int) $feature['id'] ?>][]" value="<?= (int) $value['id'] ?>" <?= in_array((int) $value['id'], $selectedValues, true) ? 'checked' : '' ?>>
                                        <span class="catalog-filter-option__check"></span><span class="catalog-filter-option__name"><?= html_escape($value['name']) ?></span><span class="catalog-filter-option__count"><?= (int) $value['count'] ?></span>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>
                    </section>
                <?php }} ?>
            </div>
            <footer class="catalog-filter-drawer__footer">
                <p class="catalog-filter-drawer__result"><strong data-filter-result-count><?= (int) $products_count ?></strong> <?= $isRomanian ? 'produse găsite' : 'товаров найдено' ?></p>
                <button type="submit" class="catalog-filter-apply"><span><?= $filterCopy['apply'] ?></span><strong data-filter-apply-count><?= (int) $products_count ?></strong></button>
            </footer>
        </form>
    </aside>
</main>
