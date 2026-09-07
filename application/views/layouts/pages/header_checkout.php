<div class="mini-header">
    <div class="mini-header__container _container-mini">
        <div class="mini-header__column _left">
            <a href="javascript:history.back()" class="mini-header__back">
                <picture>
                    <source srcset="/app/img/icons/back.svg" type="image/webp">
                    <img src="/app/img/icons/back.svg" alt="Back"></picture>
            </a>
        </div>
        <a href="/<?= $lclang ?>" class="mini-header__logo">
            <picture>
                <source srcset="/app/img/logo_v.webp" type="image/webp">
                <img src="/app/img/logo_v.png" alt="Logo" class="mini-header__logo_pc"></picture>
            <picture>
                <source srcset="/app/img/mini-logo.svg" type="image/webp">
                <img src="/app/img/mini-logo.svg" alt="Logo" class="mini-header__logo_mob"></picture>
        </a>
        <div class="mini-header__column _right">
            <div class="mini-header__language language-main-header">
                <?php select_language($clang, $lang_urls); ?>
            </div>
        </div>
    </div>
</div>