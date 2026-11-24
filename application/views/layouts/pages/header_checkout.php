<?php
$isHalloweenTheme = ($site_theme_key ?? 'default') === 'halloween';
$isNewYearTheme = ($site_theme_key ?? 'default') === 'new_year';
$checkoutHeaderLogo = $isHalloweenTheme
    ? '/app/img/hellowen-logo.png'
    : ($isNewYearTheme ? '/app/img/themes/new-year/logo-wordmark.png' : '/app/img/logo_v.png');
?>
<div class="mini-header">
    <div class="mini-header__container _container-mini">
        <div class="mini-header__column _left">
            <a href="javascript:history.back()" class="mini-header__back">
                <picture>
                    <source srcset="/app/img/icons/back.svg" type="image/webp">
                    <img src="/app/img/icons/back.svg" alt="Back"></picture>
            </a>
        </div>
        <a href="/<?= $lclang ?>" class="mini-header__logo<?= $isHalloweenTheme ? ' mini-header__logo--halloween' : ($isNewYearTheme ? ' mini-header__logo--new-year' : '') ?>">
            <picture>
                <?php if (!$isHalloweenTheme && !$isNewYearTheme) : ?>
                    <source srcset="/app/img/logo_v.webp" type="image/webp">
                <?php endif; ?>
                <img src="<?= $checkoutHeaderLogo ?>" alt="Vizaje-Nica" class="mini-header__logo_pc"></picture>
            <picture>
                <?php if (!$isNewYearTheme) : ?>
                    <source srcset="/app/img/mini-logo.svg" type="image/webp">
                <?php endif; ?>
                <img src="<?= $isNewYearTheme ? '/app/img/themes/new-year/logo-mark.png' : '/app/img/mini-logo.svg' ?>" alt="Logo" class="mini-header__logo_mob"></picture>
        </a>
        <div class="mini-header__column _right">
            <div class="mini-header__language language-main-header">
                <?php select_language($clang, $lang_urls); ?>
            </div>
        </div>
    </div>
</div>
