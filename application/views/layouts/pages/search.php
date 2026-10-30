<?php
$isRomanianSearch = strtoupper((string) ($clang ?? 'RU')) === 'RO';
$catalogUri = isset($menu['all'][3]->uri)
    ? trim((string) $menu['all'][3]->uri, '/')
    : ($isRomanianSearch ? 'catalog' : 'katalog');
$discountsUri = isset($menu['all'][35]->uri)
    ? trim((string) $menu['all'][35]->uri, '/')
    : ($isRomanianSearch ? 'reduceri' : 'skidki');
$searchTitle = trim((string) ($search_section_title ?? ''));
$searchProducts = (array) ($products_new ?? array());
$searchCategories = (array) ($categories ?? array());
$searchBrands = (array) ($brands_nav ?? array());
$popularCategories = (array) ($popular_categories ?? array());
$popularBrands = (array) ($popular_brands ?? array());
$popularSearchQueries = (array) ($popular_search_queries ?? array());
$matchingPopularSearchQueries = (array) ($matching_popular_search_queries ?? array());
$recentSearchQueries = (array) ($search_recent_queries ?? array());
$inlineCompletion = trim((string) ($search_inline_completion ?? ''));
$relatedBrands = (array) ($search_related_brands ?? array());
$searchWishlist = isset($wishlist) && is_array($wishlist) ? $wishlist : array();
$searchClientInfo = $client_info ?? null;
$catalogRoot = '/' . rawurlencode((string) $lclang) . '/' . rawurlencode($catalogUri);
$discountsUrl = '/' . rawurlencode((string) $lclang) . '/' . rawurlencode($discountsUri);
$searchUri = isset($menu['all'][21]->uri)
    ? trim((string) $menu['all'][21]->uri, '/')
    : ($isRomanianSearch ? 'cautare' : 'poisk');
$fullSearchUrl = '/' . rawurlencode((string) $lclang) . '/'
    . rawurlencode($searchUri) . '?search=' . rawurlencode((string) ($search ?? ''));

$renderSearchLinkList = static function ($items, $heading, $root, $modifier = '') {
    if (empty($items)) {
        return;
    }
    ?>
    <section class="header-search-panel__section header-search-panel__section--links<?= $modifier !== '' ? ' header-search-panel__section--links-' . htmlspecialchars($modifier, ENT_QUOTES, 'UTF-8') : '' ?>">
        <h3 class="header-search-panel__section-title">
            <?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?>
        </h3>
        <ul class="header-search-panel__link-list">
            <?php foreach ($items as $entry) {
                $uri = trim((string) ($entry->uri ?? ''), '/');
                $title = trim((string) ($entry->title ?? ''));
                if ($uri === '' || $title === '') {
                    continue;
                }
                $href = $root . '/' . rawurlencode($uri);
                ?>
                <li>
                    <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </section>
    <?php
};

?>
<div class="header-search-panel header-search-panel--<?= htmlspecialchars((string) ($search_mode ?? 'results'), ENT_QUOTES, 'UTF-8') ?>" data-search-inline-completion="<?= htmlspecialchars($inlineCompletion, ENT_QUOTES, 'UTF-8') ?>">
    <main class="header-search-panel__main">
        <h2 class="header-search-panel__title">
            <?= htmlspecialchars($searchTitle !== '' ? $searchTitle : ($isRomanianSearch ? 'Produse' : 'Товары'), ENT_QUOTES, 'UTF-8') ?>
        </h2>

        <?php if (!empty($search_corrected_query)) { ?>
            <p class="header-search-panel__correction">
                <?= $isRomanianSearch ? 'Am căutat pentru:' : 'Возможно, вы искали:' ?>
                <button type="button" data-search-query="<?= htmlspecialchars((string) $search_corrected_query, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars((string) $search_corrected_query, ENT_QUOTES, 'UTF-8') ?>
                </button>
            </p>
        <?php } ?>

        <?php if (!empty($searchProducts)) { ?>
            <div class="header-search-panel__mobile-toolbar">
                <h2>
                    <?= ($search_mode ?? '') === 'landing'
                        ? htmlspecialchars($searchTitle !== '' ? $searchTitle : ($isRomanianSearch ? 'Ofertele zilei' : 'Скидки дня'), ENT_QUOTES, 'UTF-8')
                        : ($isRomanianSearch ? 'Produse' : 'Товары') ?>
                </h2>
            </div>
            <div class="products_catalog header-search-panel__products">
                <div class="main-catalog__body">
                    <?php foreach ($searchProducts as $product) {
                        $this->load->view(
                            'layouts/pages/product_main',
                            array(
                                'item' => $product,
                                'lclang' => $lclang,
                                'clang' => $clang,
                                'menu' => $menu,
                                'wishlist' => $searchWishlist,
                                'client_info' => $searchClientInfo,
                                'is_catalog_editorial_card' => true,
                                'is_discount_catalog_card' => true,
                                'is_header_search_card' => true,
                                'enable_catalog_hover_gallery' => true,
                            )
                        );
                    } ?>
                </div>
            </div>
            <?php if (($search_mode ?? '') !== 'landing' && trim((string) ($search ?? '')) !== '') { ?>
                <a class="header-search-panel__all-results" href="<?= htmlspecialchars($fullSearchUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <?= $isRomanianSearch ? 'Vezi toate rezultatele' : 'Показать все результаты' ?>
                    <span aria-hidden="true">→</span>
                </a>
            <?php } ?>
        <?php } else { ?>
            <p class="header-search-panel__empty">
                <?= $isRomanianSearch ? 'Nu am găsit produse.' : 'Подходящих товаров пока нет.' ?>
            </p>
            <?php if (!empty($search_fallback_products)) { ?>
                <section class="header-search-panel__fallback">
                    <h2 class="header-search-panel__fallback-title">
                        <?= $isRomanianSearch ? 'Oferte pe care le poți descoperi' : 'Посмотрите товары со скидками' ?>
                    </h2>
                    <div class="products_catalog header-search-panel__products">
                        <div class="main-catalog__body">
                            <?php foreach ((array) $search_fallback_products as $product) {
                                $this->load->view(
                                    'layouts/pages/product_main',
                                    array(
                                        'item' => $product,
                                        'lclang' => $lclang,
                                        'clang' => $clang,
                                        'menu' => $menu,
                                        'wishlist' => $searchWishlist,
                                        'client_info' => $searchClientInfo,
                                        'is_catalog_editorial_card' => true,
                                        'is_discount_catalog_card' => true,
                                        'is_header_search_card' => true,
                                        'enable_catalog_hover_gallery' => true,
                                    )
                                );
                            } ?>
                        </div>
                    </div>
                    <a class="header-search-panel__all-results" href="<?= htmlspecialchars($discountsUrl, ENT_QUOTES, 'UTF-8') ?>">
                        <?= $isRomanianSearch ? 'Toate reducerile' : 'Все скидки' ?>
                        <span aria-hidden="true">→</span>
                    </a>
                </section>
            <?php } ?>
        <?php } ?>
        <?php if (($search_mode ?? '') === 'landing') { ?>
            <a class="header-search-panel__all-results" href="<?= htmlspecialchars($discountsUrl, ENT_QUOTES, 'UTF-8') ?>">
                <?= $isRomanianSearch ? 'Vezi toate reducerile' : 'Все скидки' ?>
                <span aria-hidden="true">→</span>
            </a>
        <?php } ?>
    </main>

    <aside class="header-search-panel__aside">
        <?php if (($search_mode ?? '') === 'landing') { ?>
            <?php if (!empty($popularSearchQueries)) { ?>
                <section class="header-search-panel__section header-search-panel__section--popular">
                    <h3 class="header-search-panel__section-title">
                        <?= $isRomanianSearch ? 'Căutări populare' : 'Популярные запросы' ?>
                    </h3>
                    <ul class="header-search-panel__query-list">
                        <?php foreach ($popularSearchQueries as $popularQuery) { ?>
                            <li>
                                <button type="button" data-search-query="<?= htmlspecialchars((string) $popularQuery, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $popularQuery, ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </li>
                        <?php } ?>
                    </ul>
                </section>
            <?php } ?>
            <?php $renderSearchLinkList(
                $popularCategories,
                $isRomanianSearch ? 'Categorii populare' : 'Популярные категории',
                $catalogRoot,
                'categories'
            ); ?>
            <?php $renderSearchLinkList(
                $popularBrands,
                $isRomanianSearch ? 'Branduri populare' : 'Популярные бренды',
                $catalogRoot,
                'brands'
            ); ?>
        <?php } else { ?>
            <?php if (!empty($recentSearchQueries)) { ?>
                <section class="header-search-panel__section header-search-panel__section--recent">
                    <h3 class="header-search-panel__section-title">
                        <?= $isRomanianSearch ? 'Căutări recente' : 'Недавние запросы' ?>
                    </h3>
                    <ul class="header-search-panel__query-list">
                        <?php foreach ($recentSearchQueries as $recentQuery) { ?>
                            <li>
                                <button type="button" data-search-query="<?= htmlspecialchars((string) $recentQuery, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $recentQuery, ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </li>
                        <?php } ?>
                    </ul>
                </section>
            <?php } ?>
            <?php if (!empty($matchingPopularSearchQueries)) { ?>
                <section class="header-search-panel__section header-search-panel__section--popular-queries">
                    <h3 class="header-search-panel__section-title">
                        <?= $isRomanianSearch ? 'Căutări populare' : 'Популярные запросы' ?>
                    </h3>
                    <ul class="header-search-panel__query-list">
                        <?php foreach ($matchingPopularSearchQueries as $popularQuery) { ?>
                            <li>
                                <button type="button" data-search-query="<?= htmlspecialchars((string) $popularQuery, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $popularQuery, ENT_QUOTES, 'UTF-8') ?>
                                </button>
                            </li>
                        <?php } ?>
                    </ul>
                </section>
            <?php } ?>
            <?php $renderSearchLinkList(
                $searchCategories,
                $isRomanianSearch ? 'Categorii' : 'Категории',
                $catalogRoot,
                'categories'
            ); ?>
            <?php if (!empty($searchBrands)) {
                $renderSearchLinkList(
                    $searchBrands,
                    $isRomanianSearch ? 'Branduri' : 'Бренды',
                    $catalogRoot,
                    'brands'
                );
            } ?>
            <?php if (!empty($relatedBrands)) {
                $renderSearchLinkList(
                    $relatedBrands,
                    $isRomanianSearch ? 'Branduri similare' : 'Похожие бренды',
                    $catalogRoot,
                    'brands'
                );
            } ?>
        <?php } ?>
    </aside>
</div>
