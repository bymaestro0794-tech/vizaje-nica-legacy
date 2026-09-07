<?php
defined('BASEPATH') or exit('No direct script access allowed');

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

$mobileCatalogItems = [];
$mobileSecondaryItems = [];

foreach ($desktopNavItems as $navItem) {
    if ($navItem['type'] === 'category') {
        $mobileCatalogItems[] = $navItem;
    } else {
        $mobileSecondaryItems[] = $navItem;
    }
}

$makeMobileScreenId = static function (string $level, string $uri): string {
    return 'mobile-' . $level . '-' . md5($uri);
};
?>

<header class="header" data-site-header>
    <div class="header__announcement">
        <div class="header__announcement-container _container">
            <p class="header__announcement-text">
                <?= $isRomanian
                    ? 'Livrare gratuită de la 500 MDL'
                    : 'Бесплатная доставка от 500 MDL' ?>
            </p>
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
                class="header__logo"
                aria-label="Vizaje-Nica"
            >
                <picture>
                    <source srcset="/app/img/logo_v.webp" type="image/webp">
                    <img
                        src="/app/img/logo_v.png"
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
                                class="header-navigation__link <?= $isActive ? 'is-active' : '' ?>"
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
        <!-- <div class="mobile-header__group mobile-header__group--left"> -->
            <button
                type="button"
                class="mobile-header__button mobile-header__burger"
                aria-label="<?= $isRomanian ? 'Deschide meniul' : 'Открыть меню' ?>"
                aria-expanded="false"
                aria-controls="mobileNavigation"
                data-mobile-menu-open
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

           <button
                type="button"
                class="
                    mobile-header__button
                    mobile-header__button--search
                    search-actions-main-header__icon
                "
                aria-label="<?= $isRomanian ? 'Căutare' : 'Поиск' ?>"
            >
                <img
                    src="/app/img/icons/search.svg"
                    alt=""
                    width="22"
                    height="22"
                >
            </button>
        <!-- </div> -->

        <a
            href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>"
            class="mobile-header__logo"
            aria-label="Vizaje-Nica"
        >
            <img src="/app/img/mini-logo.svg" alt="Vizaje-Nica" width="34" height="32">
        </a>

        <!-- <div class="mobile-header__group mobile-header__group--right"> -->
            <a
                href="/<?= $lclang ?>/<?= htmlspecialchars($wishlistUri, ENT_QUOTES, 'UTF-8') ?>"
                class="mobile-header__button mobile-header__button--wishlist"
                aria-label="<?= $isRomanian ? 'Favorite' : 'Избранное' ?>"
            >
                <img src="/app/img/icons/favorite.svg" alt="" width="22" height="22">

                <?php if (!empty($count_wishlist)) : ?>
                    <span class="mobile-header__badge">
                        <?= (int) $count_wishlist ?>
                    </span>
                <?php endif; ?>
            </a>

            <a
                href="/<?= $lclang ?>/<?= htmlspecialchars($cartUri, ENT_QUOTES, 'UTF-8') ?>"
                class="mobile-header__button mobile-header__button--cart"
                aria-label="<?= $isRomanian ? 'Coș' : 'Корзина' ?>"
                data-cart-drawer-open
            >
                <img src="/app/img/icons/cart.svg" alt="" width="22" height="22">
                <span
                    class="mobile-header__badge"
                    data-cart-count
                    <?= empty($total_items_cart) ? 'hidden' : '' ?>
                >
                    <?= (int) $total_items_cart ?>
                </span>
            </a>
        <!-- </div> -->
    </div>

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
                    <span class="mobile-navigation__language-label">
                        <?= $isRomanian ? 'Limbă' : 'Язык' ?>
                    </span>

                    <div class="mobile-navigation__language-switcher language-main-header">
                        <?php select_language($clang, $lang_urls); ?>
                    </div>
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
                                ?>

                                <li class="mobile-navigation__secondary-item">
                                    <a
                                        href="<?= htmlspecialchars($navItem['url'], ENT_QUOTES, 'UTF-8') ?>"
                                        class="mobile-navigation__secondary-link <?= $isPromotion
                                            ? 'mobile-navigation__secondary-link--strong'
                                            : '' ?>"
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
                        <img
                            src="/app/img/icons/search.svg"
                            alt=""
                            width="22"
                            height="22"
                        >

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
                            aria-label="<?= $isRomanian
                                ? 'Căutare'
                                : 'Поиск' ?>"
                        >
                    </form>

                    <button
                        type="button"
                        class="form-header-search__close"
                        aria-label="<?= $isRomanian
                            ? 'Închide căutarea'
                            : 'Закрыть поиск' ?>"
                        data-header-search-close
                    >
                        <span></span>
                        <span></span>
                    </button>
                </div>

                <div class="form-header-search__body"></div>
            </div>
        </div>
    </div>
</div>


<div
	class="header-overlay"
	data-header-overlay
></div>
   
</header>