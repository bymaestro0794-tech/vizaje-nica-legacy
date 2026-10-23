<main class="page catalog-index-page">
    <?php
    $mobileCatalogIcon = static function ($title, $uri = '') {
        $label = mb_strtolower(trim($uri . ' ' . $title), 'UTF-8');

        $iconMap = [
            '/eye|glaz|ochi|глаз|бров|gene|spranc/u' => 'icon-eye',
            '/lip|gub|buz|buze|помад|блеск/u' => 'icon-drop',
            '/nail|manichi|manichiur|лак|ногт/u' => 'icon-star',
            '/face|fata|față|lico|лицо|лиц/u' => 'icon-user',
            '/clean|curat|demach|очищ|тоник/u' => 'icon-drop',
            '/cream|crema|cremă|hidrata|serum|сыворот|крем/u' => 'icon-chemistry',
            '/women|femei|jenschin|zhensh|женщин/u' => 'icon-symbol-female',
            '/men|barbati|bărbați|mujchin|мужчин/u' => 'icon-symbol-male',
            '/niche|nishev|селектив|нишев/u' => 'icon-diamond',
            '/accessor|aksess|инструмент|кист|perie|brush/u' => 'icon-magic-wand',
            '/makeup|machiaj|makiy|maquiaj|макияж|космет/u' => 'icon-magic-wand',
            '/parfum|fragrance|parfyum|парфюм/u' => 'icon-drop',
            '/skin|face|ingrijire.*piele|uhod.*koz|уход.*кож|лиц/u' => 'icon-chemistry',
            '/hair|volos|păr|parului|волос/u' => 'icon-energy',
            '/men|barbati|bărbați|mujchin|мужчин/u' => 'icon-user',
            '/gift|certificate|sertificat|подароч|сертификат/u' => 'icon-present',
            '/brand|brend/u' => 'icon-tag',
            '/news|noutat|новост|новин/u' => 'icon-badge',
            '/store|magazin|магазин/u' => 'icon-map',
            '/promo|promot|акци|скид|reducer/u' => 'icon-fire',
        ];

        foreach ($iconMap as $pattern => $icon) {
            if (preg_match($pattern, $label)) {
                return $icon;
            }
        }

        return 'icon-grid';
    };

    $mobileCatalogRenderIcon = static function ($icon) {
        $iconName = preg_replace('/^icon-/', '', $icon);
        $iconPaths = [
            'eye' => '<path d="M2.062 12a1 1 0 0 1 0-.002 10.05 10.05 0 0 1 19.876 0 1 1 0 0 1 0 .002 10.05 10.05 0 0 1-19.876 0Z"/><circle cx="12" cy="12" r="3"/>',
            'drop' => '<path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.5-3-6s-4-5-4-5-2 3-4 5-3 4-3 6a7 7 0 0 0 7 7Z"/>',
            'star' => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.358 4.78 5.277.767a.53.53 0 0 1 .294.904l-3.817 3.72.901 5.255a.53.53 0 0 1-.77.559L12 15.8l-4.718 2.48a.53.53 0 0 1-.77-.56l.901-5.254-3.817-3.72a.53.53 0 0 1 .294-.904l5.277-.767z"/>',
            'user' => '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
            'chemistry' => '<path d="M9 3h6M10 3v7.4L4.8 19a2 2 0 0 0 1.7 3h11a2 2 0 0 0 1.7-3L14 10.4V3"/><path d="M8 15h8"/>',
            'symbol-female' => '<circle cx="12" cy="10" r="7"/><path d="M12 17v5m-3-3h6"/>',
            'symbol-male' => '<circle cx="10" cy="14" r="7"/><path d="M15 9l6-6m-5 0h5v5"/>',
            'diamond' => '<path d="M6 3h12l4 6-10 13L2 9l4-6Z"/><path d="M2 9h20M12 22 8 9l4-6 4 6-4 13"/>',
            'magic-wand' => '<path d="m15 4 5 5M13 6l2-2m3 7 2-2M3 21l9-9M4 4v3m-1.5-1.5h3M19 16v3m-1.5-1.5h3"/>',
            'energy' => '<path d="m13 2-3 8h7L8 22l3-9H5l8-11Z"/>',
            'present' => '<rect x="3" y="8" width="18" height="13" rx="2"/><path d="M12 8v13m9-9H3m9-4H7.5a2.5 2.5 0 1 1 2.5-2.5V8Zm0 0h4.5a2.5 2.5 0 1 0-2.5-2.5V8Z"/>',
            'tag' => '<path d="M12.586 2H19a2 2 0 0 1 2 2v6.414a2 2 0 0 1-.586 1.414l-8.414 8.414a2 2 0 0 1-2.828 0l-6-6a2 2 0 0 1 0-2.828l8.414-8.414A2 2 0 0 1 12.586 2Z"/><circle cx="16.5" cy="7.5" r=".5"/>',
            'badge' => '<path d="M12 3 9.5 5l-3.2-.1-.8 3.1L3 10l1.2 3-1.2 3 2.5 2 .8 3.1 3.2-.1L12 23l2.5-2 3.2.1.8-3.1 2.5-2-1.2-3 1.2-3-2.5-2-.8-3.1-3.2.1L12 3Z"/><path d="m9 15 6-6m-5.5.5h.01m5 5h.01"/>',
            'map' => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
            'fire' => '<path d="M8.5 14.5A4.5 4.5 0 0 0 13 19a4.5 4.5 0 0 0 4.5-4.5c0-2.5-1.5-4-3-5.5-.5 2-2 3-2 3s-.5-4-4-8C8.5 8 5 10.5 5 14.5A8 8 0 0 0 13 22a8 8 0 0 0 8-8c0-4-2-7-5-10"/>',
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        ];
        $paths = $iconPaths[$iconName] ?? $iconPaths['grid'];

        return '<svg class="lucide lucide-' . $iconName . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
    };

    $mobileCatalogCategories = array_values($categories ?? []);
    $mobileCatalogCategoriesWithOrder = [];
    $mobileCatalogPriorityByIcon = [
        'icon-magic-wand' => 0,
        'icon-drop' => 1,
        'icon-chemistry' => 2,
    ];

    foreach ($mobileCatalogCategories as $index => $category) {
        $icon = $mobileCatalogIcon($category->title ?? '', $category->uri ?? '');
        $mobileCatalogCategoriesWithOrder[] = [
            'category' => $category,
            'priority' => $mobileCatalogPriorityByIcon[$icon] ?? 3,
            'order' => $index,
        ];
    }

    usort($mobileCatalogCategoriesWithOrder, static function ($left, $right) {
        if ($left['priority'] === $right['priority']) {
            return $left['order'] <=> $right['order'];
        }

        return $left['priority'] <=> $right['priority'];
    });

    $mobileCatalogCategories = array_column($mobileCatalogCategoriesWithOrder, 'category');

    $mobileCatalogHasPriorityIcons = static function ($category) use ($mobileCatalogIcon) {
        return in_array(
            $mobileCatalogIcon($category->title ?? '', $category->uri ?? ''),
            ['icon-magic-wand', 'icon-drop', 'icon-chemistry'],
            true
        );
    };

    $mobileCatalogMenuItems = array_merge(
        array_values($menu['top'] ?? []),
        array_values($menu['bottom'] ?? [])
    );
    $mobileCatalogFindMenuItem = static function (array $patterns) use ($mobileCatalogMenuItems) {
        foreach ($mobileCatalogMenuItems as $menuItem) {
            $searchText = mb_strtolower(
                trim(($menuItem->title ?? '') . ' ' . ($menuItem->uri ?? '')),
                'UTF-8'
            );

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $searchText)) {
                    return $menuItem;
                }
            }
        }

        return null;
    };

    $mobileCatalogRootItems = [];
    $mobileCatalogRootDefinitions = [
        ['key' => 'brands', 'patterns' => ['/бренд|brand/u'], 'class' => 'primary'],
        ['key' => 'discounts', 'patterns' => ['/скид|reducer/u'], 'class' => 'primary'],
        ['key' => 'new', 'patterns' => ['/новост|новин|noutat/u'], 'class' => 'primary'],
        ['key' => 'gifts', 'patterns' => ['/сертифик|certificate/u'], 'class' => 'secondary'],
        ['key' => 'promotions', 'patterns' => ['/акци|promo|promot/u'], 'class' => 'secondary'],
        ['key' => 'stores', 'patterns' => ['/магаз|magazin/u'], 'class' => 'secondary'],
    ];

    foreach ($mobileCatalogRootDefinitions as $rootDefinition) {
        $menuItem = $mobileCatalogFindMenuItem($rootDefinition['patterns']);

        if ($rootDefinition['key'] === 'gifts') {
            $giftMenuItem = $mobileCatalogFindMenuItem(['/подароч|cadou|gift/u']);
            $menuItem = $giftMenuItem ?: $menuItem;
        }

        if (empty($menuItem->uri) || empty($menuItem->title)) {
            continue;
        }

        $title = $menuItem->title;
        if ($rootDefinition['key'] === 'new') {
            $title = $lclang === 'ro' ? 'Noutăți' : 'Новинки';
        } elseif ($rootDefinition['key'] === 'gifts') {
            $title = $lclang === 'ro' ? 'Certificate cadou' : 'Подарочные сертификаты';
        }

        $mobileCatalogRootItems[] = [
            'key' => $rootDefinition['key'],
            'class' => $rootDefinition['class'],
            'title' => $title,
            'uri' => trim($menuItem->uri, '/'),
        ];
    }

    $mobileCatalogRootItems[] = [
        'key' => 'blog',
        'class' => 'secondary',
        'title' => $lclang === 'ro' ? 'Blog' : 'Блог',
        'uri' => 'blog',
    ];

    $contactMenuItem = $mobileCatalogFindMenuItem(['/контакт|contact/u']);
    $mobileCatalogRootItems[] = [
        'key' => 'contacts',
        'class' => 'contact',
        'title' => $contactMenuItem->title ?? ($lclang === 'ro' ? 'Contacte' : 'Контакты'),
        'uri' => trim($contactMenuItem->uri ?? ($lclang === 'ro' ? 'contacte' : 'kontaktyi'), '/'),
    ];

    $mobileCatalogSupportEnabled = !isset($telegram_support) || !empty($telegram_support['enabled']);
    $mobileCatalogSupportUrl = $mobileCatalogSupportEnabled && isset($telegram_support['url'])
        ? (string) $telegram_support['url']
        : (defined('TELEGRAM_SUPPORT_BOT') ? TELEGRAM_SUPPORT_BOT : '/' . $lclang . '/kontaktyi');
    $mobileCatalogLanguageOptions = [
        [
            'code' => 'ru',
            'title' => $lclang === 'ro' ? 'Rusă' : 'Русский',
            'url' => '/ru/katalog',
        ],
        [
            'code' => 'ro',
            'title' => $lclang === 'ro' ? 'Română' : 'Румынский',
            'url' => '/ro/catalog',
        ],
    ];
    $mobileCatalogCurrentLanguageTitle = $lclang === 'ro' ? 'Română' : 'Русский';
    $mobileCatalogRootIconAssets = [
        'discounts' => 'catalog-discount.png',
        'new' => 'catalog-new.png',
        'blog' => 'catalog-blog.png',
        'brands' => 'catalog-brands.png',
        'gifts' => 'catalog-gifts.png',
    ];
    $mobileCatalogCategoryIcon = static function ($title, $uri = '') {
        $label = mb_strtolower(trim($title . ' ' . $uri), 'UTF-8');

        if (preg_match('/парфюм|parfum|parfyum|fragrance/u', $label)) {
            return 'perfume';
        }

        if (preg_match('/уход.*кож|skin|skincare|ingrijire.*piele/u', $label)) {
            return 'skincare';
        }

        if (preg_match('/макияж|makeup|machiaj|makiy/u', $label)) {
            return 'makeup';
        }

        if (preg_match('/волос|hair|păr|par/u', $label)) {
            return 'hair';
        }

        if (preg_match('/мужчин|men|b[aă]rba/u', $label)) {
            return 'men';
        }

        if (preg_match('/сертифик|gift|cadou|certificate/u', $label)) {
            return 'gifts';
        }

        return '';
    };
    ?>
    <section class="mobile-catalog-page" aria-labelledby="mobile-catalog-title" data-mobile-catalog-page>
        <div class="mobile-catalog-page__container">
            <div class="mobile-catalog-page__header">
                <?php if (($site_theme_key ?? 'default') === 'halloween') : ?>
                    <span class="catalog__halloween-decoration catalog__halloween-web" aria-hidden="true"></span>
                    <span class="catalog__halloween-decoration catalog__halloween-bat catalog__halloween-bat--one" aria-hidden="true"></span>
                <?php endif; ?>
                <button
                    type="button"
                    class="mobile-catalog-page__back"
                    aria-label="<?= $lclang === 'ro' ? 'Înapoi' : 'Назад' ?>"
                    data-mobile-catalog-back
                    hidden
                >
                    <svg viewBox="0 0 16 16" width="18" height="18" aria-hidden="true">
                        <path d="m10 3-5 5 5 5" fill="none" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                    <span><?= $lclang === 'ro' ? 'Înapoi' : 'Назад' ?></span>
                </button>
                <h1 class="mobile-catalog-page__title" id="mobile-catalog-title" data-mobile-catalog-title data-root-title="<?= htmlspecialchars($menu['all'][3]->title, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($menu['all'][3]->title, ENT_QUOTES, 'UTF-8') ?>
                </h1>
            </div>

            <button
                type="button"
                class="mobile-catalog-page__search search-actions-main-header__icon"
                aria-label="<?= $lclang === 'ro' ? 'Căutare în catalog' : 'Поиск по каталогу' ?>"
                aria-expanded="false"
            >
                <i class="icon-magnifier" aria-hidden="true"></i>
                <span><?= $lclang === 'ro' ? 'Căutare în catalog' : 'Поиск по каталогу' ?></span>
            </button>

            <nav class="mobile-catalog-page__nav" aria-label="<?= htmlspecialchars($menu['all'][3]->title, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mobile-catalog-page__screen is-active" data-mobile-catalog-screen="root">
                    <ul class="mobile-catalog-page__list">
                        <li class="mobile-catalog-page__item">
                            <button
                                type="button"
                                class="mobile-catalog-page__link mobile-catalog-page__link--primary"
                                data-mobile-catalog-open="catalog-root"
                                data-mobile-catalog-screen-title="<?= htmlspecialchars($menu['all'][3]->title, ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <span class="mobile-catalog-page__label"><?= htmlspecialchars($menu['all'][3]->title, ENT_QUOTES, 'UTF-8') ?></span>
                                <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><path d="m6 3 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.3" /></svg>
                            </button>
                        </li>
                        <?php foreach ($mobileCatalogRootItems as $rootItem) : ?>
                            <li class="mobile-catalog-page__item">
                                <?php $rootIconAsset = $mobileCatalogRootIconAssets[$rootItem['key']] ?? ''; ?>
                                <a
                                    class="mobile-catalog-page__link mobile-catalog-page__link--<?= htmlspecialchars($rootItem['class'], ENT_QUOTES, 'UTF-8') ?><?= $rootIconAsset !== '' ? ' mobile-catalog-page__link--has-icon' : '' ?>"
                                    href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($rootItem['uri'], ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <?php if ($rootIconAsset !== '') : ?>
                                        <span class="mobile-catalog-page__visual-icon" aria-hidden="true">
                                            <img src="/app/img/icons/mobile-catalog/<?= htmlspecialchars($rootIconAsset, ENT_QUOTES, 'UTF-8') ?>" alt="" width="42" height="42" loading="lazy" decoding="async">
                                        </span>
                                    <?php endif; ?>
                                    <span class="mobile-catalog-page__label"><?= htmlspecialchars($rootItem['title'], ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <?php if ($mobileCatalogSupportEnabled) : ?>
                        <li class="mobile-catalog-page__item mobile-catalog-page__item--utility mobile-catalog-page__item--support">
                            <a
                                class="mobile-catalog-page__link mobile-catalog-page__link--utility mobile-catalog-page__link--support"
                                href="<?= htmlspecialchars($mobileCatalogSupportUrl, ENT_QUOTES, 'UTF-8') ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <span class="mobile-catalog-page__utility-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" focusable="false">
                                        <path d="M6.5 18.5h8.75A4.75 4.75 0 0 0 20 13.75v-.5a4.75 4.75 0 0 0-4.75-4.75h-6.5A4.75 4.75 0 0 0 4 13.25v.5a4.75 4.75 0 0 0 2.5 4.2Z" stroke="currentColor" stroke-width="1.3" />
                                        <path d="m6.5 18.5-1 2.5 3.25-2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="9" cy="13.5" r=".7" fill="currentColor" />
                                        <circle cx="12" cy="13.5" r=".7" fill="currentColor" />
                                        <circle cx="15" cy="13.5" r=".7" fill="currentColor" />
                                    </svg>
                                </span>
                                <span class="mobile-catalog-page__utility-label">
                                    <?= $lclang === 'ro' ? 'scrieți suportului' : 'написать в поддержку' ?>
                                </span>
                                <span class="mobile-catalog-page__utility-meta">
                                    <?= $lclang === 'ro' ? 'online' : 'онлайн' ?>
                                </span>
                                <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><path d="m6 3 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.3" /></svg>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="mobile-catalog-page__item mobile-catalog-page__item--utility mobile-catalog-page__item--language">
                            <button
                                type="button"
                                class="mobile-catalog-page__link mobile-catalog-page__link--utility mobile-catalog-page__link--language"
                                data-mobile-catalog-open="languages"
                                data-mobile-catalog-screen-title="<?= $lclang === 'ro' ? 'Limbă' : 'Язык' ?>"
                            >
                                <span class="mobile-catalog-page__utility-label"><?= htmlspecialchars($mobileCatalogCurrentLanguageTitle, ENT_QUOTES, 'UTF-8') ?></span>
                                <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><path d="m6 3 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.3" /></svg>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="mobile-catalog-page__screen" data-mobile-catalog-screen="languages" data-mobile-catalog-screen-title="<?= $lclang === 'ro' ? 'Limbă' : 'Язык' ?>" hidden>
                    <ul class="mobile-catalog-page__list mobile-catalog-page__list--languages">
                        <?php foreach ($mobileCatalogLanguageOptions as $languageOption) : ?>
                            <li class="mobile-catalog-page__item">
                                <a
                                    class="mobile-catalog-page__link mobile-catalog-page__link--utility mobile-catalog-page__link--language-option <?= $languageOption['code'] === $lclang ? 'is-current' : '' ?>"
                                    href="<?= htmlspecialchars($languageOption['url'], ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <span class="mobile-catalog-page__utility-label"><?= htmlspecialchars($languageOption['title'], ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="mobile-catalog-page__screen" data-mobile-catalog-screen="catalog-root" data-mobile-catalog-screen-title="<?= htmlspecialchars($menu['all'][3]->title, ENT_QUOTES, 'UTF-8') ?>" hidden>
                    <ul class="mobile-catalog-page__list">
                        <?php foreach ($mobileCatalogCategories as $category) : ?>
                            <?php
                                if (empty($category->uri) || empty($category->title)) { continue; }
                                $categoryScreenId = 'catalog-category-' . (int) $category->id;
                                $categoryUrl = '/' . $lclang . '/' . $menu['all'][3]->uri . '/' . $category->uri;
                                $categoryIconKey = $mobileCatalogCategoryIcon($category->title, $category->uri);
                                $categoryLinkIconClass = $categoryIconKey !== '' ? ' mobile-catalog-page__link--has-icon' : '';
                                $categoryVisualIconClass = '';
                                if (in_array($categoryIconKey, ['perfume', 'skincare', 'makeup'], true)) {
                                    $categoryVisualIconClass = 'mobile-catalog-page__visual-icon--cosmetics mobile-catalog-page__visual-icon--cosmetics-' . $categoryIconKey;
                                } elseif ($categoryIconKey !== '') {
                                    $categoryVisualIconClass = 'mobile-catalog-page__visual-icon--standalone mobile-catalog-page__visual-icon--standalone-' . $categoryIconKey;
                                }
                            ?>
                            <li class="mobile-catalog-page__item">
                                <?php if (!empty($category->children)) : ?>
                                    <button
                                        type="button"
                                        class="mobile-catalog-page__link<?= $categoryLinkIconClass ?>"
                                        data-mobile-catalog-open="<?= $categoryScreenId ?>"
                                        data-mobile-catalog-screen-title="<?= htmlspecialchars($category->title, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <?php if ($categoryIconKey !== '') : ?>
                                            <span class="mobile-catalog-page__visual-icon <?= htmlspecialchars($categoryVisualIconClass, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <span class="mobile-catalog-page__label"><?= htmlspecialchars($category->title, ENT_QUOTES, 'UTF-8') ?></span>
                                        <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><path d="m6 3 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.3" /></svg>
                                    </button>
                                <?php else : ?>
                                    <a class="mobile-catalog-page__link<?= $categoryLinkIconClass ?>" href="<?= htmlspecialchars($categoryUrl, ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if ($categoryIconKey !== '') : ?>
                                            <span class="mobile-catalog-page__visual-icon <?= htmlspecialchars($categoryVisualIconClass, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <span class="mobile-catalog-page__label"><?= htmlspecialchars($category->title, ENT_QUOTES, 'UTF-8') ?></span>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php foreach ($mobileCatalogCategories as $category) : ?>
                    <?php if (empty($category->children) || empty($category->id) || empty($category->uri)) { continue; } ?>
                    <div class="mobile-catalog-page__screen" data-mobile-catalog-screen="catalog-category-<?= (int) $category->id ?>" data-mobile-catalog-screen-title="<?= htmlspecialchars($category->title, ENT_QUOTES, 'UTF-8') ?>" hidden>
                        <ul class="mobile-catalog-page__list">
                            <li class="mobile-catalog-page__item">
                                <a class="mobile-catalog-page__link mobile-catalog-page__link--all" href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($menu['all'][3]->uri, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($category->uri, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= $lclang === 'ro' ? 'Toate produsele din categorie' : 'Все товары категории' ?>
                                </a>
                            </li>
                            <?php foreach ($category->children as $child) : ?>
                                <?php
                                    if (empty($child->uri) || empty($child->title)) { continue; }
                                    $childUrl = '/' . $lclang . '/' . $menu['all'][3]->uri . '/' . $child->uri;
                                    $childScreenId = 'catalog-category-' . (int) $child->id;
                                ?>
                                <li class="mobile-catalog-page__item">
                                    <?php if (!empty($child->children)) : ?>
                                        <button type="button" class="mobile-catalog-page__link" data-mobile-catalog-open="<?= $childScreenId ?>" data-mobile-catalog-screen-title="<?= htmlspecialchars($child->title, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($child->title, ENT_QUOTES, 'UTF-8') ?>
                                            <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><path d="m6 3 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.3" /></svg>
                                        </button>
                                    <?php else : ?>
                                        <a class="mobile-catalog-page__link" href="<?= htmlspecialchars($childUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($child->title, ENT_QUOTES, 'UTF-8') ?></a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php foreach ($category->children as $child) : ?>
                        <?php if (empty($child->children) || empty($child->id) || empty($child->uri)) { continue; } ?>
                        <div class="mobile-catalog-page__screen" data-mobile-catalog-screen="catalog-category-<?= (int) $child->id ?>" data-mobile-catalog-screen-title="<?= htmlspecialchars($child->title, ENT_QUOTES, 'UTF-8') ?>" hidden>
                            <ul class="mobile-catalog-page__list">
                                <li class="mobile-catalog-page__item">
                                    <a class="mobile-catalog-page__link mobile-catalog-page__link--all" href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($menu['all'][3]->uri, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($child->uri, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= $lclang === 'ro' ? 'Toate produsele din categorie' : 'Все товары категории' ?>
                                    </a>
                                </li>
                                <?php foreach ($child->children as $grandChild) : ?>
                                    <?php if (empty($grandChild->uri) || empty($grandChild->title)) { continue; } ?>
                                    <li class="mobile-catalog-page__item">
                                        <a class="mobile-catalog-page__link" href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($menu['all'][3]->uri, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($grandChild->uri, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($grandChild->title, ENT_QUOTES, 'UTF-8') ?></span></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </section>

    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>" class="breacrums__name"><?= $home_bc_title ?></a>
                </li>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= $menu['all'][3]->title ?></p>
                </li>
            </ul>
        </div>
    </section>
    <section class="catalog">
        <div class="catalog__container _container">
            <div class="catalog__head head-catalog">
                <?php if (($site_theme_key ?? 'default') === 'halloween') : ?>
                    <span class="catalog__halloween-decoration catalog__halloween-web" aria-hidden="true"></span>
                    <span class="catalog__halloween-decoration catalog__halloween-bat catalog__halloween-bat--one" aria-hidden="true"></span>
                    <span class="catalog__halloween-decoration catalog__halloween-bat catalog__halloween-bat--two" aria-hidden="true"></span>
                <?php endif; ?>
                <h1 class="head-catalog__title"><?= $menu['all'][3]->title ?></h2>
                <p class="head-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
            </div>

            <div class="catalog__body">
                <div class="catalog__main main-catalog">
                    <div class="main-catalog__head head-main-catalog">
                        <P class="head-main-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
                        <div class="head-main-catalog__other">
                            <div class="head-main-catalog__column">
                                <div class="head-main-catalog__select">
                                    <select name="sorder">
                                        <option value="1" selected="selected"><?= SORTING1 ?></option>
                                        <option value="2"><?= SORTING2 ?></option>
                                        <option value="3"><?= SORTING3 ?></option>
                                        <option value="4"><?= AT_MAX_DISCOUNT ?></option>
<!--                                        <option value="4">--><?//= SORTING4 ?><!--</option>-->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="products_catalog">
                        <div class="main-catalog__body">
                            <?php foreach ($products as $product) { ?>
                                <?php $this->load->view("layouts/pages/product_main", array('item' => $product, 'is_catalog_editorial_card' => true)); ?>
                            <?php } ?>
                        </div>
                        <div class="main-catalog__paggination paggination-main-catalog">
                            <? $this->load->view('layouts/pages/paginator'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
