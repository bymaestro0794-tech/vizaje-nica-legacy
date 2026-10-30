<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isProductDetailPage = trim((string) ($inner_view ?? ''), '/') === 'pages/catalog/item';
$isHalloweenTheme = ($site_theme_key ?? 'default') === 'halloween';
$headerLogoImage = $isHalloweenTheme
	? '/app/img/hellowen-logo.png'
	: '/app/img/logo_v.png';

$currentHeaderView = trim((string) ($inner_view ?? ''), '/');
$isHomeTabActive = $currentHeaderView === 'pages/main/index';
$isCatalogTabActive = in_array($currentHeaderView, [
	'pages/catalog/index',
	'pages/catalog/mobile_categories',
	'pages/catalog/category',
	'pages/catalog/best_sellers',
	'pages/catalog/hits',
	'pages/catalog/new_products',
	'pages/catalog/search',
	'pages/catalog/brand',
], true);
$isProfileTabActive =
	strpos($currentHeaderView, 'pages/account/') === 0
	&& !isset($_GET['favorite']);
$isCartTabActive = strpos($currentHeaderView, 'pages/cart/') === 0;
$isWishlistTabActive =
	strpos($currentHeaderView, 'pages/account/') === 0
	&& isset($_GET['favorite']);

/*
|--------------------------------------------------------------------------
| Header navigation preparation
|--------------------------------------------------------------------------
*/

$catalogUri = !empty($menu['all'][3]->uri)
    ? $menu['all'][3]->uri
    : ($lclang === 'ro' ? 'catalog' : 'katalog');

$searchUri = !empty($menu['all'][21]->uri)
    ? $menu['all'][21]->uri
    : ($lclang === 'ro' ? 'cautare' : 'poisk');

$accountUri = !empty($menu['all'][15]->uri)
    ? $menu['all'][15]->uri
    : ($lclang === 'ro' ? 'contul-meu' : 'lichnyiy-kabinet');

$wishlistUri = !empty($menu['all'][17]->uri)
    ? $menu['all'][17]->uri
    : ($lclang === 'ro' ? 'favorite' : 'izbrannyie');

$cartUri = !empty($menu['all'][18]->uri)
    ? $menu['all'][18]->uri
    : ($lclang === 'ro' ? 'cos' : 'korzina');

$isRomanian = $lclang === 'ro';
$mobileCatalogUri = $isRomanian ? 'catalog-categories' : 'katalog-categories';

/*
|--------------------------------------------------------------------------
| Dedicated header categories
|--------------------------------------------------------------------------
|
| Some catalog pages reuse $categories for page filters.
| The header must prefer $header_categories to avoid data collisions.
|
*/
$headerCategories = !empty($header_categories)
    ? $header_categories
    : (!empty($categories) ? $categories : []);

$giftCertificateDirectItem = null;

if (!empty($menu['top'])) {
	foreach ($menu['top'] as $topItem) {
		if (
			empty($topItem->uri)
			|| empty($topItem->title)
		) {
			continue;
		}

		$normalizedTitle = mb_strtolower(
			trim($topItem->title),
			'UTF-8'
		);

		if (
			strpos(
				$normalizedTitle,
				'сертифик'
			) !== false
			|| strpos(
				$normalizedTitle,
				'certificat'
			) !== false
		) {
			$giftCertificateDirectItem = $topItem;

			break;
		}
	}
}

$desktopNavItems = [];
$registeredUris = [];
$registeredTitles = [];

if (!empty($headerCategories)) {
	foreach ($headerCategories as $category) {
		if (
			empty($category->uri)
			|| empty($category->title)
		) {
			continue;
		}

		$categoryUri = trim(
			$category->uri
		);

		$categoryTitleKey = mb_strtolower(
			trim($category->title),
			'UTF-8'
		);

		if (
			isset(
				$registeredUris[$categoryUri]
			)
			|| isset(
				$registeredTitles[
					$categoryTitleKey
				]
			)
		) {
			continue;
		}

		$isGiftCertificateCategory =
			strpos(
				$categoryTitleKey,
				'сертифик'
			) !== false
			|| strpos(
				$categoryTitleKey,
				'certificat'
			) !== false;

		if (
			$isGiftCertificateCategory
			&& !empty(
				$giftCertificateDirectItem
			)
		) {
			$directUri = trim(
				$giftCertificateDirectItem->uri
			);

			$desktopNavItems[] = [
				'title' =>
					$giftCertificateDirectItem->title,

				'uri' =>
					$directUri,

				'url' =>
					'/'
					. $lclang
					. '/'
					. $directUri,

				'type' =>
					'direct',

				'has_children' =>
					false,

				'category' =>
					null,
			];

			$registeredUris[
				$categoryUri
			] = true;

			$registeredUris[
				$directUri
			] = true;

			$registeredTitles[
				$categoryTitleKey
			] = true;

			continue;
		}

		$registeredUris[
			$categoryUri
		] = true;

		$registeredTitles[
			$categoryTitleKey
		] = true;

		$desktopNavItems[] = [
			'title' =>
				$category->title,

			'uri' =>
				$categoryUri,

			'url' =>
				'/'
				. $lclang
				. '/'
				. $catalogUri
				. '/'
				. $categoryUri,

			'type' =>
				'category',

			'has_children' =>
				!empty(
					$category->children
				),

			'category' =>
				$category,
		];
	}
}

if (!empty($menu['top'])) {
    foreach ($menu['top'] as $navItem) {
        if (empty($navItem->uri) || empty($navItem->title)) {
            continue;
        }

        $navUri = trim($navItem->uri);
        $navTitleKey = mb_strtolower(trim($navItem->title), 'UTF-8');

        if (
            isset($registeredUris[$navUri])
            || isset($registeredTitles[$navTitleKey])
        ) {
            continue;
        }

        $registeredUris[$navUri] = true;
        $registeredTitles[$navTitleKey] = true;

        $desktopNavItems[] = [
            'title' => $navItem->title,
            'uri' => $navUri,
            'url' => '/' . $lclang . '/' . $navUri,
            'type' => 'direct',
            'has_children' => false,
            'category' => null,
        ];
    }
}

$beautySelectionsTitle = $isRomanian ? 'Selecții de frumusețe' : 'Бьюти-подборки';
$beautySelectionsUri = 'beauty-selections';
if (!isset($registeredUris[$beautySelectionsUri])) {
    $desktopNavItems[] = [
        'title' => $beautySelectionsTitle,
        'uri' => $beautySelectionsUri,
        'url' => '/' . $lclang . '/' . $beautySelectionsUri,
        'type' => 'direct',
        'has_children' => false,
        'category' => null,
    ];
}

$mobileCatalogItems = [];
$mobileSecondaryItems = [];

$isHeaderDiscountItem = static function (array $item): bool {
    $title = mb_strtolower(trim((string) ($item['title'] ?? '')), 'UTF-8');
    $uri = mb_strtolower(trim((string) ($item['uri'] ?? '')), 'UTF-8');

    return preg_match('/скид|reducer|discount|sale/u', $title . ' ' . $uri) === 1;
};

$isHeaderPromotionItem = static function (array $item): bool {
    $title = mb_strtolower(trim((string) ($item['title'] ?? '')), 'UTF-8');
    $uri = mb_strtolower(trim((string) ($item['uri'] ?? '')), 'UTF-8');

    return preg_match('/акци|promo|promot|akts|promotii/u', $title . ' ' . $uri) === 1;
};

$isHeaderBeautySelectionsItem = static function (array $item): bool {
    $title = mb_strtolower(trim((string) ($item['title'] ?? '')), 'UTF-8');
    $uri = mb_strtolower(trim((string) ($item['uri'] ?? '')), 'UTF-8');

    return $uri === 'beauty-selections'
        || $uri === 'byuti-podborki'
        || preg_match('/бьюти-подбор|beauty selections|selecții de frumusețe|selectii de frumusete/u', $title) === 1;
};

foreach ($desktopNavItems as $navItem) {
    if ($navItem['type'] === 'category') {
        $mobileCatalogItems[] = $navItem;
    } else {
        $mobileSecondaryItems[] = $navItem;
    }
}

// Keep these destinations in the mobile secondary menu, but move them out
// of the desktop navigation into the storefront footer.
$desktopNavItems = array_values(array_filter(
    $desktopNavItems,
    static function (array $item) use ($isHeaderPromotionItem, $isHeaderBeautySelectionsItem): bool {
        return !$isHeaderPromotionItem($item) && !$isHeaderBeautySelectionsItem($item);
    }
));

$makeMobileScreenId = static function (string $level, string $uri): string {
    return 'mobile-' . $level . '-' . md5($uri);
};

$headerDiscountSlides = !empty($header_discount_products)
    && is_array($header_discount_products)
        ? $header_discount_products
        : array();
$headerDiscountCatalogUri = !empty($menu['all'][35]->uri)
    ? trim((string) $menu['all'][35]->uri, '/')
    : ($isRomanian ? 'reduceri' : 'skidki');
$headerWheelEligible = !empty($client_info->id);
$headerRouletteScope = $headerWheelEligible
    ? hash('sha256', 'vn-roulette:' . (int) $client_info->id)
    : '';
$headerRouletteStateUrl = $headerWheelEligible
    ? '/' . $lclang . '/roulette/state'
    : '';
?>

<header
	class="header<?= $isProductDetailPage ? ' header--product-detail' : '' ?>"
	data-site-header
	<?= $isProductDetailPage ? 'data-header-product-detail' : '' ?>
>
    <div
        class="header__announcement"
        data-header-promo
        data-language="<?= $isRomanian ? 'ro' : 'ru' ?>"
        data-roulette-scope="<?= htmlspecialchars($headerRouletteScope, ENT_QUOTES, 'UTF-8') ?>"
        data-roulette-state-url="<?= htmlspecialchars($headerRouletteStateUrl, ENT_QUOTES, 'UTF-8') ?>"
    >
        <div class="header__announcement-container _container">
            <?php if (!empty($headerDiscountSlides)) : ?>
                <div class="header__promo-products" data-header-promo-products>
                    <?php foreach ($headerDiscountSlides as $slideIndex => $promoProduct) : ?>
                        <?php
                        $promoTitle = trim((string) ($promoProduct->title ?? ''));
                        $promoCategoryUri = trim((string) ($promoProduct->cat_uri ?? ''), '/');
                        $promoProductUri = trim((string) ($promoProduct->uri ?? ''), '/');
                        $promoHref = $promoCategoryUri !== '' && $promoProductUri !== ''
                            ? '/' . $lclang . '/' . $catalogUri . '/' . $promoCategoryUri . '/' . $promoProductUri
                            : '/' . $lclang . '/' . $headerDiscountCatalogUri;
                        $promoPercent = max(0, (int) ($promoProduct->discount_percent ?? 0));
                        ?>
                        <a
                            class="header__promo-slide"
                            data-header-promo-slide
                            href="<?= htmlspecialchars($promoHref, ENT_QUOTES, 'UTF-8') ?>"
                            aria-label="<?= htmlspecialchars(($isRomanian ? 'Vezi produsul: ' : 'Перейти к товару: ') . $promoTitle, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $slideIndex > 0 ? 'hidden' : '' ?>
                        >
                            <p class="header__announcement-text header__promo-title" title="<?= htmlspecialchars($promoTitle, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($promoTitle, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <strong class="header__promo-discount">−<?= $promoPercent ?>%</strong>
                            <span
                                class="header__promo-link"
                            >
                                <?= $isRomanian ? 'Vezi' : 'Перейти' ?>
                                <span aria-hidden="true">→</span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (count($headerDiscountSlides) > 1) : ?>
                    <div class="header__promo-controls" data-header-promo-controls>
                        <button type="button" data-header-promo-previous aria-label="<?= $isRomanian ? 'Produsul anterior' : 'Предыдущий товар' ?>">
                            <span aria-hidden="true">‹</span>
                        </button>
                        <button type="button" data-header-promo-next aria-label="<?= $isRomanian ? 'Produsul următor' : 'Следующий товар' ?>">
                            <span aria-hidden="true">›</span>
                        </button>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="header__promo-fallback" data-header-promo-fallback>
                    <p class="header__announcement-text">
                        <?= $isRomanian
                            ? 'Livrare gratuită de la 500 MDL'
                            : 'Бесплатная доставка от 500 MDL' ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($headerWheelEligible) : ?>
                <div class="header__wheel-invite" data-header-wheel-invite hidden>
                    <p class="header__announcement-text header__wheel-message" data-header-wheel-message></p>
                    <button
                        type="button"
                        class="header__promo-link header__wheel-button"
                        data-roulette-open
                        data-header-wheel-button
                    ></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="header__desktop">
        <div class="header__desktop-main _container">
            <div class="header__desktop-side header__desktop-side--left">
                <div class="header-language language-main-header">
                    <?php select_language($clang, $lang_urls); ?>
                </div>
            </div>

            <a
                href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>"
                class="header__logo<?= $isHalloweenTheme ? ' header__logo--halloween' : '' ?>"
                aria-label="Vizaje-Nica"
            >
                <picture>
                    <?php if (!$isHalloweenTheme) : ?>
                        <source srcset="/app/img/logo_v.webp" type="image/webp">
                    <?php endif; ?>
                    <img
                        src="<?= htmlspecialchars($headerLogoImage, ENT_QUOTES, 'UTF-8') ?>"
                        alt="Vizaje-Nica"
                        width="240"
                        height="52"
                    >
                </picture>
            </a>

            <div class="header__desktop-side header__desktop-side--right">
                <div class="header-actions">
                    <button
                        type="button"
                        class="
                            header-actions__button
                            header-actions__button--search
                            search-actions-main-header__icon
                        "
                        aria-label="<?= $isRomanian ? 'Căutare' : 'Поиск' ?>"
                        aria-expanded="false"
                        data-search-label="<?= $isRomanian ? 'Căutare' : 'Поиск' ?>"
                        data-search-close-label="<?= $isRomanian ? 'Închide căutarea' : 'Закрыть поиск' ?>"
                    >
                        <img
                            src="/app/img/icons/search.svg"
                            alt=""
                            width="22"
                            height="22"
                        >
                    </button>

                    <a
                        href="/<?= $lclang ?>/<?= htmlspecialchars($wishlistUri, ENT_QUOTES, 'UTF-8') ?>"
                        class="header-actions__button header-actions__button--wishlist"
                        aria-label="<?= $isRomanian ? 'Favorite' : 'Избранное' ?>"
                    >
                        <img src="/app/img/icons/favorite.svg" alt="" width="22" height="22">

                        <?php if (!empty($count_wishlist)) : ?>
                            <span class="header-actions__badge">
                                <?= (int) $count_wishlist ?>
                            </span>
                        <?php endif; ?>
                    </a>

                                    <?php
$clientName = '';

if (!empty($client_info)) {
	$clientName = trim(
		(string) $client_info->name
		. ' '
		. (string) $client_info->surname
	);
}

$clientInitial = !empty($clientName)
	? mb_substr(
		$clientName,
		0,
		1,
		'UTF-8'
	)
	: 'V';

$clientEmail = !empty($client_info->email)
	? (string) $client_info->email
	: '';

$wishlistUrl = $isRomanian
	? '/' . $lclang . '/favorite'
	: '/' . $lclang . '/izbrannyie';
?>

<div
	class="
		header-user
		<?= !empty($client_info)
			? 'is-authorized'
			: 'is-guest' ?>
	"
	data-header-user
>
	<?php if (!empty($client_info)) : ?>
		<button
			type="button"
			class="
				header-actions__button
				header-actions__button--user
			"
			aria-label="<?= $isRomanian
				? 'Cont personal'
				: 'Личный кабинет' ?>"
			aria-expanded="false"
			aria-haspopup="menu"
			data-header-user-toggle
		>
			<svg
				width="22"
				height="22"
				viewBox="0 0 24 24"
				fill="none"
				aria-hidden="true"
			>
				<circle
					cx="12"
					cy="7"
					r="4.5"
					stroke="currentColor"
					stroke-width="1.6"
				/>

				<path
					d="
						M3.75 22
						C4.25 17.65
						7.08 15.25
						12 15.25
						C16.92 15.25
						19.75 17.65
						20.25 22
					"
					stroke="currentColor"
					stroke-width="1.6"
					stroke-linecap="round"
				/>
			</svg>
		</button>

		<div
			class="header-user__panel"
			data-header-user-panel
			role="menu"
		>
			<?php if (($site_theme_key ?? 'default') === 'halloween') : ?>
				<span
					class="header-user__halloween-decoration header-user__halloween-web"
					aria-hidden="true"
				></span>
				<span
					class="header-user__halloween-decoration header-user__halloween-spider"
					aria-hidden="true"
				></span>
			<?php endif; ?>

			<div class="header-user__profile">
				<div
					class="header-user__avatar"
					aria-hidden="true"
				>
					<?= htmlspecialchars(
						mb_strtoupper(
							$clientInitial,
							'UTF-8'
						),
						ENT_QUOTES,
						'UTF-8'
					) ?>
				</div>

				<div class="header-user__identity">
					<p class="header-user__name">
						<?= htmlspecialchars(
							$clientName,
							ENT_QUOTES,
							'UTF-8'
						) ?>
					</p>

					<?php if (!empty($clientEmail)) : ?>
						<p class="header-user__email">
							<?= htmlspecialchars(
								$clientEmail,
								ENT_QUOTES,
								'UTF-8'
							) ?>
						</p>
					<?php endif; ?>
				</div>
			</div>

			<nav
				class="header-user__navigation"
				aria-label="<?= $isRomanian
					? 'Navigarea contului'
					: 'Навигация личного кабинета' ?>"
			>
				<a
					href="/<?= $lclang ?>/<?= htmlspecialchars(
						$accountUri,
						ENT_QUOTES,
						'UTF-8'
					) ?>?cabinet"
					class="header-user__link"
					role="menuitem"
				>
					<span class="header-user__link-icon">
						<svg
							width="22"
							height="22"
							viewBox="0 0 24 24"
							fill="none"
							aria-hidden="true"
						>
							<circle
								cx="12"
								cy="8"
								r="3.5"
								stroke="currentColor"
								stroke-width="1.5"
							/>

							<path
								d="M5 20C5.45 16.2 7.85 14.25 12 14.25C16.15 14.25 18.55 16.2 19 20"
								stroke="currentColor"
								stroke-width="1.5"
								stroke-linecap="round"
							/>
						</svg>
					</span>

					<span class="header-user__link-label">
						<?= !empty($menu['all'][15]->title)
							? $menu['all'][15]->title
							: (
								$isRomanian
									? 'Contul meu'
									: 'Личный кабинет'
							) ?>
					</span>

					<span class="header-user__link-arrow">
						<svg
							width="8"
							height="14"
							viewBox="0 0 8 14"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M1 1L7 7L1 13"
								stroke="currentColor"
								stroke-width="1.4"
								stroke-linecap="round"
								stroke-linejoin="round"
							/>
						</svg>
					</span>
				</a>

				<a
					href="/<?= $lclang ?>/<?= htmlspecialchars(
						$accountUri,
						ENT_QUOTES,
						'UTF-8'
					) ?>?orders"
					class="header-user__link"
					role="menuitem"
				>
					<span class="header-user__link-icon">
						<svg
							width="22"
							height="22"
							viewBox="0 0 24 24"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M6 8H18L19 21H5L6 8Z"
								stroke="currentColor"
								stroke-width="1.5"
								stroke-linejoin="round"
							/>

							<path
								d="M9 8V6.5C9 4.57 10.34 3 12 3C13.66 3 15 4.57 15 6.5V8"
								stroke="currentColor"
								stroke-width="1.5"
								stroke-linecap="round"
							/>
						</svg>
					</span>

					<span class="header-user__link-label">
						<?= !empty($menu['all'][16]->title)
							? $menu['all'][16]->title
							: (
								$isRomanian
									? 'Comenzile mele'
									: 'Мои заказы'
							) ?>
					</span>

					<span class="header-user__link-arrow">
						<svg
							width="8"
							height="14"
							viewBox="0 0 8 14"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M1 1L7 7L1 13"
								stroke="currentColor"
								stroke-width="1.4"
								stroke-linecap="round"
								stroke-linejoin="round"
							/>
						</svg>
					</span>
				</a>

				<a
					href="/<?= $lclang ?>/<?= htmlspecialchars(
						$accountUri,
						ENT_QUOTES,
						'UTF-8'
					) ?>?bonus"
					class="header-user__link"
					role="menuitem"
				>
					<span class="header-user__link-icon">
						<svg
							width="22"
							height="22"
							viewBox="0 0 24 24"
							fill="none"
							aria-hidden="true"
						>
							<circle
								cx="12"
								cy="12"
								r="9"
								stroke="currentColor"
								stroke-width="1.5"
							/>

							<path
								d="M12 7.5L13.35 10.25L16.4 10.7L14.2 12.85L14.72 15.9L12 14.47L9.28 15.9L9.8 12.85L7.6 10.7L10.65 10.25L12 7.5Z"
								stroke="currentColor"
								stroke-width="1.3"
								stroke-linejoin="round"
							/>
						</svg>
					</span>

					<span class="header-user__link-label">
						<?= $isRomanian
							? 'Bonusurile mele'
							: 'Мои бонусы' ?>
					</span>

					<span class="header-user__link-arrow">
						<svg
							width="8"
							height="14"
							viewBox="0 0 8 14"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M1 1L7 7L1 13"
								stroke="currentColor"
								stroke-width="1.4"
								stroke-linecap="round"
								stroke-linejoin="round"
							/>
						</svg>
					</span>
				</a>

				<a
					href="<?= htmlspecialchars(
						$wishlistUrl,
						ENT_QUOTES,
						'UTF-8'
					) ?>"
					class="header-user__link"
					role="menuitem"
				>
					<span class="header-user__link-icon">
						<svg
							width="22"
							height="22"
							viewBox="0 0 24 24"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M20.84 4.61C20.33 4.1 19.72 3.69 19.05 3.41C18.38 3.14 17.66 3 16.94 3C16.21 3 15.5 3.14 14.83 3.41C14.16 3.69 13.55 4.1 13.04 4.61L12 5.65L10.96 4.61C9.93 3.58 8.53 3 7.07 3C5.61 3 4.21 3.58 3.18 4.61C2.15 5.64 1.57 7.04 1.57 8.5C1.57 9.96 2.15 11.36 3.18 12.39L12 21.21L20.84 12.39C21.35 11.88 21.76 11.27 22.04 10.6C22.31 9.93 22.45 9.21 22.45 8.49C22.45 7.76 22.31 7.05 22.04 6.38C21.76 5.71 21.35 5.12 20.84 4.61Z"
								stroke="currentColor"
								stroke-width="1.45"
								stroke-linecap="round"
								stroke-linejoin="round"
							/>
						</svg>
					</span>

					<span class="header-user__link-label">
						<?= $isRomanian
							? 'Favorite'
							: 'Избранное' ?>
					</span>

					<span class="header-user__link-arrow">
						<svg
							width="8"
							height="14"
							viewBox="0 0 8 14"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M1 1L7 7L1 13"
								stroke="currentColor"
								stroke-width="1.4"
								stroke-linecap="round"
								stroke-linejoin="round"
							/>
						</svg>
					</span>
				</a>

				<?php if ($headerWheelEligible) : ?>
					<button
						type="button"
						class="header-user__link header-user__link--roulette"
						role="menuitem"
						aria-haspopup="dialog"
						data-roulette-entry
						data-roulette-open
						hidden
					>
						<span class="header-user__link-icon">
							<svg
								width="22"
								height="22"
								viewBox="0 0 24 24"
								fill="none"
								aria-hidden="true"
							>
								<circle
									cx="12"
									cy="12"
									r="8.5"
									stroke="currentColor"
									stroke-width="1.5"
								/>

								<path
									d="M12 3.5V20.5M4.64 7.75L19.36 16.25M4.64 16.25L19.36 7.75"
									stroke="currentColor"
									stroke-width="1.35"
									stroke-linecap="round"
								/>

								<circle
									cx="12"
									cy="12"
									r="1.7"
									fill="currentColor"
								/>
							</svg>
						</span>

						<span class="header-user__link-label">
							<?= $isRomanian
								? 'Roata norocului'
								: 'Рулетка удачи' ?>
						</span>

						<span class="header-user__link-arrow">
							<svg
								width="8"
								height="14"
								viewBox="0 0 8 14"
								fill="none"
								aria-hidden="true"
							>
								<path
									d="M1 1L7 7L1 13"
									stroke="currentColor"
									stroke-width="1.4"
									stroke-linecap="round"
									stroke-linejoin="round"
								/>
							</svg>
						</span>
					</button>
				<?php endif; ?>
			</nav>

			<div class="header-user__footer">
				<a
					href="/<?= $lclang ?>/logout"
					class="
						header-user__link
						header-user__link--logout
					"
					role="menuitem"
				>
					<span class="header-user__link-icon">
						<svg
							width="22"
							height="22"
							viewBox="0 0 24 24"
							fill="none"
							aria-hidden="true"
						>
							<path
								d="M9 4H5.5C4.67 4 4 4.67 4 5.5V18.5C4 19.33 4.67 20 5.5 20H9"
								stroke="currentColor"
								stroke-width="1.5"
								stroke-linecap="round"
							/>

							<path
								d="M14 8L18 12L14 16"
								stroke="currentColor"
								stroke-width="1.5"
								stroke-linecap="round"
								stroke-linejoin="round"
							/>

							<path
								d="M18 12H9"
								stroke="currentColor"
								stroke-width="1.5"
								stroke-linecap="round"
							/>
						</svg>
					</span>

					<span class="header-user__link-label">
						<?= defined('LOGOUT')
							? LOGOUT
							: (
								$isRomanian
									? 'Ieșire'
									: 'Выйти'
							) ?>
					</span>
				</a>
			</div>

			<?php if (($site_theme_key ?? 'default') === 'halloween') : ?>
				<span
					class="header-user__halloween-decoration header-user__halloween-cat"
					aria-hidden="true"
				></span>
				<span
					class="header-user__halloween-decoration header-user__halloween-pumpkin"
					aria-hidden="true"
				></span>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<button
			type="button"
			class="
				header-actions__button
				header-actions__button--user
			"
			aria-label="<?= $isRomanian
				? 'Autentificare'
				: 'Войти' ?>"
			aria-haspopup="dialog"
			data-auth-trigger="login"
		>
			<svg
				width="22"
				height="22"
				viewBox="0 0 24 24"
				fill="none"
				aria-hidden="true"
			>
				<circle
					cx="12"
					cy="7"
					r="4.5"
					stroke="currentColor"
					stroke-width="1.6"
				/>

				<path
					d="
						M3.75 22
						C4.25 17.65
						7.08 15.25
						12 15.25
						C16.92 15.25
						19.75 17.65
						20.25 22
					"
					stroke="currentColor"
					stroke-width="1.6"
					stroke-linecap="round"
				/>
			</svg>
		</button>
	<?php endif; ?>
</div>

                    <a
                        href="/<?= $lclang ?>/<?= htmlspecialchars($cartUri, ENT_QUOTES, 'UTF-8') ?>"
                        class="header-actions__button header-actions__button--cart"
                        aria-label="<?= $isRomanian ? 'Coș' : 'Корзина' ?>"
                        data-cart-drawer-open
                    >
                        <img src="/app/img/icons/cart.svg" alt="" width="22" height="22">
                        <span
                            class="header-actions__badge"
                            data-cart-count
                            <?= empty($total_items_cart) ? 'hidden' : '' ?>
                        >
                            <?= (int) $total_items_cart ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <nav
            class="header-navigation"
            aria-label="<?= $isRomanian ? 'Navigare principală' : 'Основная навигация' ?>"
        >
            <div class="header-navigation__container _container">
                <ul class="header-navigation__list">
                    <?php foreach ($desktopNavItems as $navItem) : ?>
                        <?php $isActive = $uri2 === $navItem['uri']; ?>

                        <li
                            class="header-navigation__item <?= $navItem['has_children'] ? 'has-mega-menu' : '' ?>"
                            <?= $navItem['has_children'] ? 'data-mega-item' : '' ?>
                        >
                            <a
                                href="<?= htmlspecialchars($navItem['url'], ENT_QUOTES, 'UTF-8') ?>"
                                class="header-navigation__link <?= $isActive ? 'is-active' : '' ?> <?= $isHeaderDiscountItem($navItem) ? 'header-navigation__link--discount' : '' ?>"
                                <?php if ($navItem['has_children']) : ?>
                                    data-mega-trigger="<?= htmlspecialchars($navItem['uri'], ENT_QUOTES, 'UTF-8') ?>"
                                <?php endif; ?>
                            >
                                <?= htmlspecialchars($navItem['title'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
        <?php if (!empty($mobileCatalogItems)) : ?>
    <div
        class="header-mega"
        aria-hidden="true"
        data-header-mega
    >
        <?php foreach ($mobileCatalogItems as $navItem) : ?>
            <?php
            $category = $navItem['category'];

            if (
                empty($category)
                || empty($category->children)
            ) {
                continue;
            }
            ?>

            <div
                class="header-mega__panel"
                data-mega-panel="<?= htmlspecialchars(
                    $navItem['uri'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >
                <div class="header-mega__container _container">
                    <div class="header-mega__intro">
                        <p class="header-mega__eyebrow">
                            <?= $isRomanian
                                ? 'Categorie'
                                : 'Категория' ?>
                        </p>

                        <a
                            href="<?= htmlspecialchars(
                                $navItem['url'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="header-mega__title"
                        >
                            <?= htmlspecialchars(
                                $navItem['title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </a>

                        <a
                            href="<?= htmlspecialchars(
                                $navItem['url'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="header-mega__all"
                        >
                            <?= $isRomanian
                                ? 'Vezi toate produsele'
                                : 'Смотреть все товары' ?>

                            <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    <div class="header-mega__columns">
                        <?php foreach (
                            $category->children as $child
                        ) : ?>
                            <?php
                            if (
                                empty($child->uri)
                                || empty($child->title)
                            ) {
                                continue;
                            }

                            $childUrl = '/'
                                . $lclang
                                . '/'
                                . $catalogUri
                                . '/'
                                . $child->uri;
                            ?>

                            <div class="header-mega__column">
                                <a
                                    href="<?= htmlspecialchars(
                                        $childUrl,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="header-mega__column-title"
                                >
                                    <?= htmlspecialchars(
                                        $child->title,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </a>

                                <?php if (!empty($child->children)) : ?>
                                    <ul class="header-mega__list">
                                        <?php foreach (
                                            array_slice(
                                                $child->children,
                                                0,
                                                8
                                            )
                                            as $grandChild
                                        ) : ?>
                                            <?php
                                            if (
                                                empty($grandChild->uri)
                                                || empty($grandChild->title)
                                            ) {
                                                continue;
                                            }

                                            $grandChildUrl = '/'
                                                . $lclang
                                                . '/'
                                                . $catalogUri
                                                . '/'
                                                . $grandChild->uri;
                                            ?>

                                            <li>
                                                <a
                                                    href="<?= htmlspecialchars(
                                                        $grandChildUrl,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >
                                                    <?= htmlspecialchars(
                                                        $grandChild->title,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
    </div>

    <div class="header__mobile">
        <button
            type="button"
            class="mobile-header__search search-actions-main-header__icon"
            aria-label="<?= $isRomanian ? 'Căutare' : 'Поиск' ?>"
            aria-expanded="false"
            data-search-label="<?= $isRomanian ? 'Căutare' : 'Поиск' ?>"
            data-search-close-label="<?= $isRomanian ? 'Închide căutarea' : 'Закрыть поиск' ?>"
        >
            <span class="mobile-header__search-label"><?= $isRomanian ? 'CĂUTARE' : 'ПОИСК' ?></span>
            <img src="/app/img/icons/search.svg" alt="" width="20" height="20" aria-hidden="true">
        </button>
    </div>

	<nav
		class="mobile-tabbar"
		aria-label="<?= $isRomanian ? 'Navigare principală' : 'Основная навигация' ?>"
	>
		<a
			href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($mobileCatalogUri, ENT_QUOTES, 'UTF-8') ?>"
			class="mobile-tabbar__item mobile-tabbar__item--catalog<?= $isCatalogTabActive ? ' is-active' : '' ?>"
			aria-label="<?= $isRomanian ? 'Catalog' : 'Каталог' ?>"
			<?= $isCatalogTabActive ? 'aria-current="page"' : '' ?>
		>
			<span class="mobile-tabbar__icon">
				<span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--catalog" aria-hidden="true"></span>
			</span>
			<span class="mobile-tabbar__label"><?= $isRomanian ? 'Catalog' : 'Каталог' ?></span>
		</a>

		<a
			href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($wishlistUri, ENT_QUOTES, 'UTF-8') ?>"
			class="mobile-tabbar__item mobile-tabbar__item--wishlist<?= $isWishlistTabActive ? ' is-active' : '' ?>"
			aria-label="<?= $isRomanian ? 'Favorite' : 'Избранное' ?>"
			<?= $isWishlistTabActive ? 'aria-current="page"' : '' ?>
		>
			<span class="mobile-tabbar__icon">
				<span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--wishlist" aria-hidden="true"></span>
			</span>
			<span class="mobile-tabbar__label"><?= $isRomanian ? 'Favorite' : 'Избранное' ?></span>
		</a>

		<a
			href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>"
			class="mobile-tabbar__item mobile-tabbar__item--brand<?= $isHomeTabActive ? ' is-active' : '' ?>"
			aria-label="Vizaje-Nica"
			<?= $isHomeTabActive ? 'aria-current="page"' : '' ?>
		>
			<span class="mobile-tabbar__icon">
				<span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--brand" aria-hidden="true"></span>
			</span>
		</a>

		<?php if (!empty($client_info)) : ?>
			<a
				href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($accountUri, ENT_QUOTES, 'UTF-8') ?>"
				class="mobile-tabbar__item mobile-tabbar__item--profile<?= $isProfileTabActive ? ' is-active' : '' ?>"
				<?= $isProfileTabActive ? 'aria-current="page"' : '' ?>
			>
				<span class="mobile-tabbar__icon">
					<span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--profile" aria-hidden="true"></span>
					<svg class="mobile-tabbar__header-icon mobile-tabbar__header-icon--profile" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
						<circle cx="12" cy="7" r="4.5" stroke="currentColor" stroke-width="1.6" />
						<path d="M3.75 22C4.25 17.65 7.08 15.25 12 15.25S19.75 17.65 20.25 22" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
					</svg>
				</span>
				<span class="mobile-tabbar__label"><?= $isRomanian ? 'Profil' : 'Профиль' ?></span>
			</a>
		<?php else : ?>
			<button
				type="button"
				class="mobile-tabbar__item mobile-tabbar__item--profile"
				data-auth-trigger="login"
			>
				<span class="mobile-tabbar__icon">
					<span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--profile" aria-hidden="true"></span>
					<svg class="mobile-tabbar__header-icon mobile-tabbar__header-icon--profile" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
						<circle cx="12" cy="7" r="4.5" stroke="currentColor" stroke-width="1.6" />
						<path d="M3.75 22C4.25 17.65 7.08 15.25 12 15.25S19.75 17.65 20.25 22" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
					</svg>
				</span>
				<span class="mobile-tabbar__label"><?= $isRomanian ? 'Profil' : 'Профиль' ?></span>
			</button>
		<?php endif; ?>

		<a
			href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($cartUri, ENT_QUOTES, 'UTF-8') ?>"
			class="mobile-tabbar__item mobile-tabbar__item--cart<?= $isCartTabActive ? ' is-active' : '' ?>"
			<?= $isCartTabActive ? 'aria-current="page"' : '' ?>
			data-cart-drawer-open
		>
			<span class="mobile-tabbar__icon">
				<span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--bag" aria-hidden="true"></span>
				<svg class="mobile-tabbar__header-icon mobile-tabbar__header-icon--cart" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M11.14.072c-1.602.303-2.904 1.313-3.58 2.78-.25.541-.39 1.155-.421 1.847l-.026.598H5.809c-.807 0-1.353.019-1.435.05-.184.07-.379.267-.433.438-.025.08-.335 3.752-.687 8.16-.7 8.743-.678 8.304-.425 8.859.146.319.56.763.866.93.516.28.085.266 8.282.266 8.203 0 7.765.014 8.285-.269.293-.16.695-.593.847-.912.26-.55.282-.08-.411-8.764-.348-4.366-.645-8.027-.66-8.137a.746.746 0 0 0-.429-.557c-.117-.049-.462-.064-1.46-.064h-1.306l-.03-.574c-.083-1.635-.858-3.034-2.16-3.901a5.097 5.097 0 0 0-1.551-.688C12.595.01 11.636-.022 11.14.072zM12.822 1.5a3.449 3.449 0 0 1 2.6 3.362v.434H8.52l.022-.574c.018-.449.05-.656.145-.95a3.447 3.447 0 0 1 2.428-2.27c.48-.127 1.218-.127 1.707-.002zM7.125 8.056c0 1.325.002 1.355.105 1.507.17.25.334.346.59.346a.666.666 0 0 0 .606-.362c.071-.13.084-.323.096-1.496l.015-1.348h6.88l.015 1.38c.013 1.308.018 1.386.11 1.509.174.232.33.317.583.317.253 0 .41-.085.583-.317.092-.123.097-.2.11-1.51l.014-1.379h.935c.931 0 .934 0 .956.106.053.259 1.206 15.076 1.185 15.23a.662.662 0 0 1-.322.48c-.112.068-.862.075-7.607.075-8.266 0-7.662.023-7.856-.295a.786.786 0 0 1-.093-.361c-.002-.116.267-3.591.596-7.723l.599-7.512h1.9v1.353zm-.94 10.686a.703.703 0 0 0-.329 1.01c.206.334-.224.313 6.184.3l5.765-.013.125-.117c.372-.347.333-.872-.082-1.125-.15-.092-.245-.093-5.871-.09-3.146.002-5.752.017-5.791.035z" />
				</svg>
			</span>
			<span class="mobile-tabbar__label"><?= $isRomanian ? 'Coș' : 'Корзина' ?></span>
			<span class="mobile-tabbar__badge" data-cart-count <?= empty($total_items_cart) ? 'hidden' : '' ?>>
				<?= (int) $total_items_cart ?>
			</span>
		</a>
	</nav>

    <div
        id="mobileNavigation"
        class="mobile-navigation"
        aria-hidden="true"
        data-mobile-menu
    >
        <div class="mobile-navigation__header">
            <div
                class="mobile-navigation__header-group mobile-navigation__header-group--root"
                data-mobile-root-header-left
            >
                <button
                    type="button"
                    class="mobile-navigation__action mobile-navigation__close"
                    aria-label="<?= $isRomanian ? 'Închide meniul' : 'Закрыть меню' ?>"
                    data-mobile-menu-close
                >
                    <span></span>
                    <span></span>
                </button>

                <button
                    type="button"
                    class="
                        mobile-navigation__action
                        search-actions-main-header__icon
                    "
                    aria-label="<?= $isRomanian
                        ? 'Căutare'
                        : 'Поиск' ?>"
                    aria-expanded="false"
                    data-search-label="<?= $isRomanian ? 'Căutare' : 'Поиск' ?>"
                    data-search-close-label="<?= $isRomanian ? 'Închide căutarea' : 'Закрыть поиск' ?>"
                >
                    <img
                        src="/app/img/icons/search.svg"
                        alt=""
                        width="22"
                        height="22"
                    >
                </button>
            </div>

            <button
                type="button"
                class="mobile-navigation__back"
                aria-label="<?= $isRomanian ? 'Înapoi' : 'Назад' ?>"
                data-mobile-screen-back
                hidden
            >
                <svg width="10" height="18" viewBox="0 0 10 18" fill="none" aria-hidden="true">
                    <path
                        d="M9 1L1 9L9 17"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </button>

            <a
                href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>"
                class="mobile-navigation__logo"
                aria-label="Vizaje-Nica"
                data-mobile-root-logo
            >
                <img src="/app/img/mini-logo.svg" alt="Vizaje-Nica" width="34" height="32">
            </a>

            <p class="mobile-navigation__level-title" data-mobile-level-title hidden></p>

            <div
                class="mobile-navigation__header-group mobile-navigation__header-group--right"
                data-mobile-root-header-right
            >
                <a
                    href="/<?= $lclang ?>/<?= htmlspecialchars($wishlistUri, ENT_QUOTES, 'UTF-8') ?>"
                    class="mobile-navigation__action"
                    aria-label="<?= $isRomanian ? 'Favorite' : 'Избранное' ?>"
                >
                    <img src="/app/img/icons/favorite.svg" alt="" width="22" height="22">
                </a>

                <a
                    href="/<?= $lclang ?>/<?= htmlspecialchars($cartUri, ENT_QUOTES, 'UTF-8') ?>"
                    class="mobile-navigation__action"
                    aria-label="<?= $isRomanian ? 'Coș' : 'Корзина' ?>"
                >
                    <img src="/app/img/icons/cart.svg" alt="" width="22" height="22">
                    <span
                        class="mobile-navigation__badge"
                        data-cart-count
                        <?= empty($total_items_cart) ? 'hidden' : '' ?>
                    >
                        <?= (int) $total_items_cart ?>
                    </span>
                </a>
            </div>

            <button
                type="button"
                class="mobile-navigation__nested-close mobile-navigation__close"
                aria-label="<?= $isRomanian ? 'Închide meniul' : 'Закрыть меню' ?>"
                data-mobile-menu-close
                hidden
            >
                <span></span>
                <span></span>
            </button>
        </div>

        <div class="mobile-navigation__body">
            <div
                class="mobile-navigation__screen mobile-navigation__screen--root is-active"
                data-mobile-screen="root"
            >
                <div class="mobile-navigation__account">
	<?php if (!empty($client_info)) : ?>
		<a
			href="/<?= $lclang ?>/<?= htmlspecialchars(
				$accountUri,
				ENT_QUOTES,
				'UTF-8'
			) ?>"
			class="mobile-navigation__account-link"
		>
			<span class="mobile-navigation__account-main">
				<span class="mobile-navigation__account-name">
					<?= htmlspecialchars(
						trim(
							$client_info->name
							. ' '
							. $client_info->surname
						),
						ENT_QUOTES,
						'UTF-8'
					) ?>
				</span>

				<span class="mobile-navigation__account-caption">
					<?= $isRomanian
						? 'Cont personal'
						: 'Личный кабинет' ?>
				</span>
			</span>

			<svg
				width="9"
				height="15"
				viewBox="0 0 9 15"
				fill="none"
				aria-hidden="true"
			>
				<path
					d="M1 1L7.5 7.5L1 14"
					stroke="currentColor"
					stroke-width="1.4"
					stroke-linecap="round"
					stroke-linejoin="round"
				/>
			</svg>
		</a>
	<?php else : ?>
		<button
			type="button"
			class="
				mobile-navigation__account-link
				mobile-navigation__account-button
			"
			data-auth-trigger="login"
			aria-label="<?= $isRomanian
				? 'Intră sau înregistrează-te'
				: 'Войти или зарегистрироваться' ?>"
		>
			<span class="mobile-navigation__account-main">
				<span class="mobile-navigation__account-name">
					<?= $isRomanian
						? 'Intră sau înregistrează-te'
						: 'Войти или зарегистрироваться' ?>
				</span>

				<span class="mobile-navigation__account-caption">
					<?= $isRomanian
						? 'Comenzi, bonusuri și favorite'
						: 'Заказы, бонусы и избранное' ?>
				</span>
			</span>

			<svg
				width="9"
				height="15"
				viewBox="0 0 9 15"
				fill="none"
				aria-hidden="true"
			>
				<path
					d="M1 1L7.5 7.5L1 14"
					stroke="currentColor"
					stroke-width="1.4"
					stroke-linecap="round"
					stroke-linejoin="round"
				/>
			</svg>
		</button>
	<?php endif; ?>
</div>

                <div class="mobile-navigation__language">
                    <button type="button" class="mobile-navigation__language-trigger" data-language-modal-open aria-haspopup="dialog">
                        <svg class="mobile-navigation__language-globe" stroke="none" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                            <path fill-rule="evenodd" d="M14.416 10.625c-.165 2.77-1.429 5.485-3.791 7.385v-7.385h3.791Zm4.312 0a8.755 8.755 0 0 1-6.794 7.91c2.334-2.116 3.579-4.988 3.734-7.91h3.06Zm0-1.25h-3.06c-.155-2.922-1.4-5.794-3.734-7.91a8.755 8.755 0 0 1 6.794 7.91Zm-8.103 0h3.791c-.165-2.77-1.429-5.485-3.791-7.385v7.385Zm-1.25 10.606V20h1.25v-.02A9.998 9.998 0 0 0 20 10C20 4.687 15.857.342 10.625.02V0h-1.25v.02A10.001 10.001 0 0 0 .019 9.374H0v1.25h.02a10.001 10.001 0 0 0 9.355 9.356Zm-1.642-1.528a8.756 8.756 0 0 1-6.461-7.828h2.817c.153 2.885 1.368 5.72 3.644 7.828Zm1.642-.253c-2.519-1.907-3.864-4.71-4.034-7.575h4.034V18.2ZM5.341 9.375h4.034V1.801C6.856 3.707 5.511 6.51 5.341 9.375Zm2.392-7.828a8.756 8.756 0 0 0-6.461 7.828h2.817c.153-2.885 1.368-5.72 3.644-7.828Z" clip-rule="evenodd"></path>
                        </svg>
                        <span><?= $isRomanian ? 'Limba: Română' : 'Язык: Русский' ?></span>
                        <svg class="mobile-navigation__language-chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
                            <path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>

                <?php if (!empty($mobileCatalogItems)) : ?>
                    <nav
                        class="mobile-navigation__catalog"
                        aria-label="<?= $isRomanian ? 'Catalog' : 'Каталог' ?>"
                    >
                        <ul class="mobile-navigation__catalog-list">
                            <?php foreach ($mobileCatalogItems as $navItem) : ?>
                                <?php
                                $category = $navItem['category'];
                                $levelTwoId = $makeMobileScreenId('level-two', $navItem['uri']);
                                ?>

                                <li class="mobile-navigation__catalog-item">
                                    <?php if (!empty($category) && !empty($category->children)) : ?>
                                        <button
                                            type="button"
                                            class="mobile-navigation__catalog-link mobile-navigation__catalog-link--next"
                                            data-mobile-screen-open="<?= $levelTwoId ?>"
                                            data-mobile-screen-title="<?= htmlspecialchars(
                                                $navItem['title'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            <span>
                                                <?= htmlspecialchars($navItem['title'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>

                                            <svg width="9" height="15" viewBox="0 0 9 15" fill="none" aria-hidden="true">
                                                <path
                                                    d="M1 1L7.5 7.5L1 14"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </button>
                                    <?php else : ?>
                                        <a
                                            href="<?= htmlspecialchars($navItem['url'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="mobile-navigation__catalog-link"
                                        >
                                            <?= htmlspecialchars($navItem['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <?php if (!empty($mobileSecondaryItems)) : ?>
                    <nav
                        class="mobile-navigation__secondary"
                        aria-label="<?= $isRomanian ? 'Pagini' : 'Другие разделы' ?>"
                    >
                        <ul class="mobile-navigation__secondary-list">
                            <?php foreach ($mobileSecondaryItems as $navItem) : ?>
                                <?php
                                $normalizedTitle = mb_strtolower($navItem['title'], 'UTF-8');
                                $isPromotion = preg_match(
                                    '/акци|скид|promo|reducer/u',
                                    $normalizedTitle
                                );
                                $isDiscount = $isHeaderDiscountItem($navItem);
                                ?>

                                <li class="mobile-navigation__secondary-item">
                                    <a
                                        href="<?= htmlspecialchars($navItem['url'], ENT_QUOTES, 'UTF-8') ?>"
                                        class="mobile-navigation__secondary-link <?= $isPromotion
                                            ? 'mobile-navigation__secondary-link--strong'
                                            : '' ?> <?= $isDiscount ? 'mobile-navigation__secondary-link--discount' : '' ?>"
                                    >
                                        <?= htmlspecialchars($navItem['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <div class="mobile-navigation__bottom-space"></div>
            </div>

            <?php foreach ($mobileCatalogItems as $navItem) : ?>
                <?php
                $category = $navItem['category'];

                if (empty($category) || empty($category->children)) {
                    continue;
                }

                $levelTwoId = $makeMobileScreenId('level-two', $navItem['uri']);
                ?>

                <div
                    class="mobile-navigation__screen mobile-navigation__screen--nested"
                    data-mobile-screen="<?= $levelTwoId ?>"
                    data-mobile-screen-title="<?= htmlspecialchars(
                        $navItem['title'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    <ul class="mobile-navigation__level-list">
                        <li class="mobile-navigation__level-item">
                            <a
                                href="<?= htmlspecialchars($navItem['url'], ENT_QUOTES, 'UTF-8') ?>"
                                class="mobile-navigation__level-link mobile-navigation__level-link--all"
                            >
                                <?= $isRomanian
                                    ? 'Toate produsele din categorie'
                                    : 'Все товары раздела' ?>
                            </a>
                        </li>

                        <?php foreach ($category->children as $child) : ?>
                            <?php
                            if (empty($child->uri) || empty($child->title)) {
                                continue;
                            }

                            $childUrl = '/' . $lclang . '/' . $catalogUri . '/' . $child->uri;
                            $hasThirdLevel = !empty($child->children);
                            $levelThreeId = $makeMobileScreenId(
                                'level-three',
                                $category->uri . '-' . $child->uri
                            );
                            ?>

                            <li class="mobile-navigation__level-item">
                                <?php if ($hasThirdLevel) : ?>
                                    <button
                                        type="button"
                                        class="mobile-navigation__level-link mobile-navigation__level-link--next"
                                        data-mobile-screen-open="<?= $levelThreeId ?>"
                                        data-mobile-screen-title="<?= htmlspecialchars(
                                            $child->title,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >
                                        <span>
                                            <?= htmlspecialchars($child->title, ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                        <svg width="9" height="15" viewBox="0 0 9 15" fill="none" aria-hidden="true">
                                            <path
                                                d="M1 1L7.5 7.5L1 14"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>
                                <?php else : ?>
                                    <a
                                        href="<?= htmlspecialchars($childUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        class="mobile-navigation__level-link"
                                    >
                                        <?= htmlspecialchars($child->title, ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php foreach ($category->children as $child) : ?>
                    <?php
                    if (
                        empty($child->children)
                        || empty($child->uri)
                        || empty($child->title)
                    ) {
                        continue;
                    }

                    $levelThreeId = $makeMobileScreenId(
                        'level-three',
                        $category->uri . '-' . $child->uri
                    );

                    $childUrl = '/' . $lclang . '/' . $catalogUri . '/' . $child->uri;
                    ?>

                    <div
                        class="mobile-navigation__screen mobile-navigation__screen--nested"
                        data-mobile-screen="<?= $levelThreeId ?>"
                        data-mobile-screen-title="<?= htmlspecialchars(
                            $child->title,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        <ul class="mobile-navigation__level-list">
                            <?php foreach ($child->children as $grandChild) : ?>
                                <?php
                                if (empty($grandChild->uri) || empty($grandChild->title)) {
                                    continue;
                                }

                                $grandChildUrl = '/'
                                    . $lclang
                                    . '/'
                                    . $catalogUri
                                    . '/'
                                    . $grandChild->uri;
                                ?>

                                <li class="mobile-navigation__level-item">
                                    <a
                                        href="<?= htmlspecialchars($grandChildUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        class="mobile-navigation__level-link"
                                    >
                                        <?= htmlspecialchars($grandChild->title, ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>

                            <li class="mobile-navigation__level-item">
                                <a
                                    href="<?= htmlspecialchars($childUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    class="mobile-navigation__level-link mobile-navigation__level-link--all"
                                >
                                    <?= $isRomanian ? 'Toate produsele: ' : 'Все товары: ' ?>
                                    <?= htmlspecialchars($child->title, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>

<div
    class="search-actions-main-header _search"
    data-header-search
    aria-hidden="true"
>
    <div
        class="search-actions-main-header__form form-header-search"
    >
        <div class="form-header-search__container _container">
            <div class="form-header-search__main">
                <div class="form-header-search__top">
                    <button
                        type="button"
                        class="form-header-search__close"
                        data-header-search-close
                        aria-label="<?= $isRomanian ? 'Închide căutarea' : 'Закрыть поиск' ?>"
                    >
                        <span class="form-header-search__close-icon" aria-hidden="true">
                            <svg viewBox="0 0 20 20" focusable="false">
                                <path stroke-width="1.5" d="m2 2 16 16"></path>
                                <path stroke-width="1.5" d="M2 18 18 2"></path>
                            </svg>
                        </span>
                        <span class="form-header-search__back-icon" aria-hidden="true">
                            <img src="/app/img/icons/back.svg" alt="" width="20" height="20">
                        </span>
                        <span class="form-header-search__close-label">
                            <?= $isRomanian ? 'Închide căutarea' : 'Закрыть поиск' ?>
                        </span>
                    </button>
                    <form
                        action="/<?= $lclang ?>/<?= htmlspecialchars(
                            $searchUri,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        class="form-header-search__search"
                        method="get"
                        role="search"
                    >
                        <span class="form-header-search__completion" aria-hidden="true"></span>
                        <input
                            type="text"
                            name="search"
                            id="head_search"
                            class="form-header-search__input"
                            placeholder="<?= defined('WANT_TO_BUY')
                                ? htmlspecialchars(
                                    WANT_TO_BUY,
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                                : ($isRomanian
                                    ? 'Ce căutați?'
                                    : 'Что вы ищете?') ?>"
                            autocomplete="off"
                            enterkeyhint="search"
                            aria-label="<?= $isRomanian
                                ? 'Căutare'
                                : 'Поиск' ?>"
                        >

                        <button
                            type="button"
                            class="form-header-search__clear"
                            aria-label="<?= $isRomanian ? 'Șterge căutarea' : 'Очистить поиск' ?>"
                            hidden
                        >
                            <span aria-hidden="true">×</span>
                        </button>

                        <button
                            type="submit"
                            class="form-header-search__submit"
                            aria-label="<?= $isRomanian
                                ? 'Caută'
                                : 'Найти' ?>"
                            data-search-submit-label="<?= $isRomanian
                                ? 'Caută'
                                : 'Найти' ?>"
                        >
                            <svg class="form-header-search__submit-icon" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                                <path fill-rule="evenodd" stroke-width="0" d="M25.96 5.96 40 20 25.96 34.04l-1.415-1.414L36.172 21H1v-2h35.172L24.545 7.374 25.96 5.96Z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="form-header-search__mobile-suggestion" aria-live="polite" hidden></div>

                <div class="form-header-search__body search_result"></div>
            </div>
        </div>
    </div>
</div>


<div
	class="header-overlay"
	data-header-overlay
></div>

<dialog class="mobile-language-modal" data-language-modal aria-labelledby="mobile-language-modal-title">
    <section class="mobile-language-modal__sheet">
        <div class="mobile-language-modal__handle" aria-hidden="true"></div>
        <header class="mobile-language-modal__header">
            <h2 id="mobile-language-modal-title"><?= $isRomanian ? 'Alegeți limba' : 'Выберите язык' ?></h2>
            <button type="button" class="mobile-language-modal__close" data-language-modal-close aria-label="<?= $isRomanian ? 'Închide' : 'Закрыть' ?>">×</button>
        </header>
        <div class="mobile-language-modal__options language-main-header">
            <?php select_language($clang, $lang_urls); ?>
        </div>
    </section>
</dialog>
   
</header>
