<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';
$languageUrls = is_array($lang_urls ?? null) ? $lang_urls : array();
$roSlug = !empty($languageUrls['ro']) ? $languageUrls['ro'] : 'certificat-cadou';
$ruSlug = !empty($languageUrls['ru']) ? $languageUrls['ru'] : 'podarochnyie-sertifikatyi';
$roUrl = '/ro/' . ltrim((string) $roSlug, '/');
$ruUrl = '/ru/' . ltrim((string) $ruSlug, '/');
$catalogUri = $isRomanian ? 'catalog-categories' : 'katalog-categories';
$wishlistUri = $isRomanian ? 'favorite' : 'izbrannyie';
$accountUri = $isRomanian ? 'contul-meu' : 'lichnyiy-kabinet';
$cartUri = $isRomanian ? 'cos' : 'korzina';
?>

<header class="certificate-builder-mini-header">
    <a
        class="certificate-builder-mini-header__logo"
        href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>"
        aria-label="Vizaje-Nica"
    >
        <img src="/app/img/icons-2/logo-2.svg" alt="Vizaje-Nica" width="32" height="25">
        <span>VIZAJE-NICA</span>
    </a>

    <nav class="certificate-builder-mini-header__languages" aria-label="<?= $isRomanian ? 'Limbă' : 'Язык' ?>">
        <a class="<?= $isRomanian ? 'is-active' : '' ?>" href="<?= htmlspecialchars($roUrl, ENT_QUOTES, 'UTF-8') ?>">RO</a>
        <a class="<?= !$isRomanian ? 'is-active' : '' ?>" href="<?= htmlspecialchars($ruUrl, ENT_QUOTES, 'UTF-8') ?>">RU</a>
    </nav>

</header>

<nav class="mobile-tabbar certificate-builder-mobile-tabbar" aria-label="<?= $isRomanian ? 'Navigare principală' : 'Основная навигация' ?>">
    <a href="/<?= $lclang ?>" class="mobile-tabbar__item mobile-tabbar__item--brand">
        <span class="mobile-tabbar__icon">
            <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--brand" aria-hidden="true"></span>
        </span>
    </a>
    <a href="/<?= $lclang ?>/<?= $catalogUri ?>" class="mobile-tabbar__item mobile-tabbar__item--catalog" aria-label="<?= $isRomanian ? 'Catalog' : 'Каталог' ?>">
        <span class="mobile-tabbar__icon">
            <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--catalog" aria-hidden="true"></span>
        </span>
    </a>
    <a href="/<?= $lclang ?>/<?= $wishlistUri ?>" class="mobile-tabbar__item mobile-tabbar__item--wishlist" aria-label="<?= $isRomanian ? 'Favorite' : 'Избранное' ?>">
        <span class="mobile-tabbar__icon">
            <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--wishlist" aria-hidden="true"></span>
        </span>
    </a>
    <?php if (!empty($client_info)) : ?>
        <a href="/<?= $lclang ?>/<?= $accountUri ?>" class="mobile-tabbar__item mobile-tabbar__item--profile" aria-label="<?= $isRomanian ? 'Profil' : 'Профиль' ?>">
    <?php else : ?>
        <button type="button" class="mobile-tabbar__item mobile-tabbar__item--profile" data-auth-trigger="login" aria-label="<?= $isRomanian ? 'Profil' : 'Профиль' ?>">
    <?php endif; ?>
        <span class="mobile-tabbar__icon">
            <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--profile" aria-hidden="true"></span>
        </span>
    <?php if (!empty($client_info)) : ?></a><?php else : ?></button><?php endif; ?>
    <a href="/<?= $lclang ?>/<?= $cartUri ?>" class="mobile-tabbar__item mobile-tabbar__item--cart" data-cart-drawer-open aria-label="<?= $isRomanian ? 'Coș' : 'Корзина' ?>">
        <span class="mobile-tabbar__icon">
            <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--bag" aria-hidden="true"></span>
        </span>
        <span class="mobile-tabbar__badge" data-cart-count <?= empty($total_items_cart) ? 'hidden' : '' ?>><?= (int) ($total_items_cart ?? 0) ?></span>
    </a>
</nav>
