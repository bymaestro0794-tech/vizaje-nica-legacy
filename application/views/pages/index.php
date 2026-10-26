<!DOCTYPE html>
<html lang="<?= $lclang ?>">

<head>
<?php
$escapeMeta = static function ($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$metaTitle =
    !empty($meta_title)
        ? trim((string) $meta_title)
        : (!empty($page_title)
            ? trim((string) $page_title)
            : 'Vizaje-Nica');

$metaDescription =
    !empty($description_for_layout)
        ? trim(
            (string) $description_for_layout
        )
        : '';

$metaKeywords =
    !empty($keywords_for_layout)
        ? trim(
            (string) $keywords_for_layout
        )
        : '';

$metaOgTitle =
    !empty($otitle)
        ? trim((string) $otitle)
        : $metaTitle;

$metaOgType =
    !empty($og_type)
        ? (string) $og_type
        : 'website';

$metaOgUrl =
    !empty($og_url)
        ? (string) $og_url
        : (
            !empty($canonical_url)
                ? (string) $canonical_url
                : (string) $without_get_url
        );

$metaOgImage = '';

if (!empty($og_img)) {
    $metaOgImage =
        preg_match(
            '#^https?://#i',
            $og_img
        )
            ? $og_img
            : $site_url
                . '/'
                . ltrim(
                    $og_img,
                    '/'
                );
}

$metaOgImageType = 'image/jpeg';

if ($metaOgImage !== '') {
    $ogImagePath =
        parse_url(
            $metaOgImage,
            PHP_URL_PATH
        );

    $ogImageExtension =
        strtolower(
            pathinfo(
                $ogImagePath,
                PATHINFO_EXTENSION
            )
        );

    switch ($ogImageExtension) {
        case 'webp':
            $metaOgImageType =
                'image/webp';
            break;

        case 'png':
            $metaOgImageType =
                'image/png';
            break;

        case 'jpg':
        case 'jpeg':
        default:
            $metaOgImageType =
                'image/jpeg';
            break;
    }
}
?>

    <meta charset="utf-8">

    <?php if (!empty($meta_robots)) : ?>
        <meta name="robots" content="<?= $escapeMeta($meta_robots) ?>">
    <?php endif; ?>

    <title><?= $escapeMeta(
        $metaTitle
    ) ?></title>

    <?php if (
        $metaDescription !== ''
    ) : ?>
        <meta
            name="description"
            content="<?= $escapeMeta(
                $metaDescription
            ) ?>"
        >
    <?php endif; ?>

    <?php if (
        $metaKeywords !== ''
    ) : ?>
        <meta
            name="keywords"
            content="<?= $escapeMeta(
                $metaKeywords
            ) ?>"
        >
    <?php endif; ?>

    <!-- Canonical -->

    <?php if (
        !empty($canonical_url)
    ) : ?>
        <link
            rel="canonical"
            href="<?= $escapeMeta(
                $canonical_url
            ) ?>"
        >
    <?php endif; ?>

    <!-- Alternate languages -->

    <?php if (
        !empty($hreflang_urls)
        && is_array($hreflang_urls)
    ) : ?>

        <?php foreach (
            $hreflang_urls
            as $hreflang => $hreflangUrl
        ) : ?>

            <link
                rel="alternate"
                hreflang="<?= $escapeMeta(
                    $hreflang
                ) ?>"
                href="<?= $escapeMeta(
                    $hreflangUrl
                ) ?>"
            >

        <?php endforeach; ?>

    <?php endif; ?>

    <!-- Open Graph -->
    
    <meta
        property="og:type"
        content="<?= $escapeMeta(
            $metaOgType
        ) ?>"
    >

    <meta
        property="og:title"
        content="<?= $escapeMeta(
            $metaOgTitle
        ) ?>"
    >

    <?php if (
        $metaDescription !== ''
    ) : ?>
        <meta
            property="og:description"
            content="<?= $escapeMeta(
                $metaDescription
            ) ?>"
        >
    <?php endif; ?>

    <meta
        property="og:url"
        content="<?= $escapeMeta(
            $metaOgUrl
        ) ?>"
    >

    <?php if (
        $metaOgImage !== ''
    ) : ?>

        <meta
            property="og:image"
            content="<?= $escapeMeta(
                $metaOgImage
            ) ?>"
        >

        <meta
            property="og:image:secure_url"
            content="<?= $escapeMeta(
                $metaOgImage
            ) ?>"
        >

        <meta
            property="og:image:type"
            content="<?= $escapeMeta(
                $metaOgImageType
            ) ?>"
        >

        <?php if (
            !empty($og_img_width)
        ) : ?>
            <meta
                property="og:image:width"
                content="<?= (int) $og_img_width ?>"
            >
        <?php endif; ?>

        <?php if (
            !empty($og_img_height)
        ) : ?>
            <meta
                property="og:image:height"
                content="<?= (int) $og_img_height ?>"
            >
        <?php endif; ?>

    <?php endif; ?>

    <?php if (
        !empty($breadcrumb_schema)
        && is_array($breadcrumb_schema)
    ) : ?>
        <script type="application/ld+json">
<?= json_encode(
    $breadcrumb_schema,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRETTY_PRINT
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
) ?>
        </script>
    <?php endif; ?>

    <?php if (
    !empty($product_schema_graph)
    && is_array($product_schema_graph)
) : ?>

    <script type="application/ld+json">
<?= json_encode(
    $product_schema_graph,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRETTY_PRINT
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
) ?>
    </script>

<?php endif; ?>

    <?php if (
    !empty($organization_schema)
    && is_array($organization_schema)
) : ?>

    <script type="application/ld+json">
<?= json_encode(
    $organization_schema,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRETTY_PRINT
    | JSON_HEX_TAG
) ?>
    </script>

<?php endif; ?>


<?php if (
    !empty($website_schema)
    && is_array($website_schema)
) : ?>

    <script type="application/ld+json">
<?= json_encode(
    $website_schema,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_PRETTY_PRINT
    | JSON_HEX_TAG
) ?>
    </script>

<?php endif; ?>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<link
    rel="apple-touch-icon"
    sizes="180x180"
    href="/apple-touch-icon.png"
>

<link
    rel="icon"
    type="image/png"
    sizes="32x32"
    href="/favicon-32x32.png"
>

<link
    rel="icon"
    type="image/png"
    sizes="16x16"
    href="/favicon-16x16.png"
>

<link
    rel="manifest"
    href="/site.webmanifest"
>

<link
    rel="shortcut icon"
    href="/favicon.ico"
>

<?php

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        $path = ltrim($path, '/');
        $absolutePath = FCPATH . $path;

        /*
         * Версия меняется только после физического изменения файла.
         * Благодаря этому браузер и Cloudflare не отдают старый CSS/JS.
         */
        $version = file_exists($absolutePath)
            ? filemtime($absolutePath)
            : 'missing';

        return '/' . $path . '?v=' . $version;
    }
}

/*
 * CSS, необходимый на каждой странице.
 */
$criticalCss = [
    'app/css/style/01-base.css',
    'app/css/style/31-product-card-options.css',
    'app/css/style/02-cookie.css',
    'app/css/style/03-header.css',
    'static/assets/global/plugins/simple-line-icons/simple-line-icons.min.css',
    'app/css/style/04-footer.css',
    'app/css/style/05-breadcrumbs.css',
    'app/css/style/16-legacy-overrides.css',
    'app/css/style/17-interactions.css',
    'app/css/style/18-loader.css',
    'app/css/style/37-seasonal-themes.css',
];

/*
 * Стили скрытых drawer/modal/widget-компонентов не должны блокировать
 * первый рендер. Они всё равно загружаются до взаимодействия пользователя.
 */
$deferredCss = [
    'app/css/style/02-overlays-auth-map.css',
    'app/css/style/23-notifications.css',
    'app/css/style/24-cart-drawer.css',
    'app/css/style/25-auth-drawer.css',
    'app/css/style/26-cookie-consent-panel.css',
    'app/css/style/27-quick-view.css',
    'app/css/style/32-welcome-offer.css',
    'app/css/style/33-chat-widget.css',
    'app/css/style/34-roulette-wheel.css',
    'app/css/style/35-telegram-support.css',
    'app/css/style/site-feedback.css',
];

/*
 * CSS конкретной страницы.
 */
$pageCss = [];

$currentView = trim((string) ($inner_view ?? ''), '/');
$rouletteExcludedViews = array(
    'pages/cart/checkout',
    'pages/cart/success',
    'pages/cart/error',
    'pages/account/login',
    'pages/account/registration',
    'pages/account/reset_password',
);
$rouletteShouldRender = !empty($client_info->id)
    && !in_array($currentView, $rouletteExcludedViews, true);

switch ($currentView) {
    case 'pages/main/index':
	$pageCss[] = '/app/css/pages/home/home.css?v=20261022-catalog-cards';
	$pageCss[] = '/app/css/pages/home/catalog-slider-card.css?v=20261022-catalog-cards';
        break;
    case 'pages/beauty_selections/index':
    case 'pages/beauty_selections/item':
        $pageCss[] = 'app/css/style/19-articles.css';
        $pageCss[] = '/app/css/pages/beauty-selections.css?v=20261023-5';
        break;
    case 'pages/main/contacts':
        $pageCss[] = 'app/css/style/22-contactpage.css';
        break;
    case 'pages/articles/index':
	    $pageCss[] = 'app/css/style/19-articles.css';
	    break;
    case 'pages/articles/items':
	    $pageCss[] = 'app/css/style/19-articles.css';
	    break;
	case 'pages/articles/blog_item':
	    $pageCss[] = 'app/css/style/19-articles.css';
	    break;

    case 'pages/cart/index':
        $pageCss[] = 'app/css/style/08-cart.css';
        break;

    case 'pages/cart/checkout':
        $pageCss[] = 'app/css/style/09-checkout.css';
        $pageCss[] = 'app/css/style/28-pickup.css';
        $pageCss[] = 'app/css/style/29-checkout-v2.css';
        break;

    case 'pages/catalog/item':
        $pageCss[] = '/app/css/pages/home/product-card.css';
        $pageCss[] = '/app/css/pages/home/products.css';
        $pageCss[] = '/app/css/pages/home/product-tabs.css';
        $pageCss[] = 'app/css/style/07-product-page.css';
        break;
    
    case 'pages/certificates/index':
        $pageCss[] = 'app/css/pages/01-certificates.css';
        break;
    
    case 'pages/certificat/index':
        $pageCss[] = 'app/css/pages/certificate-builder.css';
        break;
    
    case 'pages/errors/404':
        $pageCss[] = 'app/css/pages/02-notfound.css';
        break; 

    case 'pages/catalog/wishlist':
        $pageCss[] = 'app/css/style/26-wishlist.css';

        break;

    case 'pages/offers/niche_sale':
        $pageCss[] = 'app/css/style/05-home.css';
        $pageCss[] = 'app/css/style/06-catalog.css?v=20261026-category-scroll';
        $pageCss[] = 'app/css/style/15-nouislider.css';
        break;

    case 'pages/catalog/index':
    case 'pages/catalog/mobile_categories':
    case 'pages/catalog/category':
    case 'pages/catalog/best_sellers':
    case 'pages/catalog/hits':
    case 'pages/catalog/new_products':
    case 'pages/catalog/search':
    case 'pages/catalog/brand':
        if ($currentView === 'pages/catalog/best_sellers') {
            $pageCss[] = '/app/css/pages/catalog/discount-cards.css?v=20261022-discount-cards';
        }
        $pageCss[] = 'app/css/style/05-home.css';
        $pageCss[] = 'app/css/style/06-catalog.css?v=20261026-category-scroll';
        $pageCss[] = 'app/css/style/15-nouislider.css';
        break;

    case 'pages/cart/success':
        $pageCss[] = 'app/css/style/20-success.css';
        break;
    case 'pages/cart/error':
        $pageCss[] = 'app/css/style/21-error.css';
        break;
    case 'pages/main/stores':
        $pageCss[] = 'app/css/style/13-stores.css';
        break;
    case 'pages/main/promo_rules':
        $pageCss[] = 'app/css/style/30-promo-rules.css';
        break;

}

if (strpos($currentView, 'pages/account/') === 0) {
    $pageCss[] = 'app/css/style/10-account.css';
}

if (strpos($currentView, 'pages/brands/') === 0) {
    $pageCss[] = 'app/css/style/11-brands.css';
}

if (strpos($currentView, 'pages/offers/') === 0) {
    $pageCss[] = 'app/css/style/12-promotions.css';
    $pageCss[] = 'app/css/style/19-articles.css';

    if ($currentView === 'pages/offers/items') {
        $pageCss[] = 'app/css/style/05-home.css';
        $pageCss[] = 'app/css/style/06-catalog.css?v=20261026-category-scroll';
        $pageCss[] = '/app/css/pages/catalog/discount-cards.css?v=20261022-discount-cards';
    }
}

if (!empty($is_privacy_policy_page)) {
    $pageCss[] = 'app/css/style/36-privacy-policy.css';
}

?>
<!-- Критические глобальные стили -->
<?php foreach ($criticalCss as $cssFile) { ?>
    <link
        rel="stylesheet"
        href="<?= asset_url($cssFile) ?>"
    >
<?php } ?>

<!-- Некритические стили drawer/modal/widget-компонентов -->
<?php foreach ($deferredCss as $cssFile) { ?>
    <?php $cssUrl = asset_url($cssFile); ?>
    <link
        rel="stylesheet"
        href="<?= $cssUrl ?>"
        media="print"
        onload="this.onload=null;this.media='all'"
    >
    <noscript>
        <link rel="stylesheet" href="<?= $cssUrl ?>">
    </noscript>
<?php } ?>

<!-- Стили текущей страницы -->
<?php foreach ($pageCss as $cssFile) { ?>
    <link
        rel="stylesheet"
        href="<?= asset_url($cssFile) ?>"
    >
<?php } ?>

<link
    rel="stylesheet"
    href="<?= asset_url('app/css/media.css') ?>"
>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <!--
        Google Consent Mode v2 (default). Должен выполниться до GTM/gtag,
        поэтому стоит первым и не зависит от того, дано ли согласие —
        отражает ТЕКУЩЕЕ состояние cookie_consent на каждой загрузке
        страницы (после смены выбора страница перезагружается).
    -->
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('consent', 'default', {
            'analytics_storage': '<?= !empty($consent['analytics']) ? 'granted' : 'denied' ?>',
            'ad_storage': '<?= !empty($consent['marketing']) ? 'granted' : 'denied' ?>',
            'ad_user_data': '<?= !empty($consent['marketing']) ? 'granted' : 'denied' ?>',
            'ad_personalization': '<?= !empty($consent['marketing']) ? 'granted' : 'denied' ?>'
        });
    </script>

<?php if (!empty($consent['marketing'])) : ?>
    <!-- Meta Pixel Code -->
    <script>
        ! function(f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function() {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1534298674427845');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=1534298674427845&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->
<?php endif; ?>
<?php if (!empty($consent['functional'])) : ?>

    <!-- Jivo temporarily disabled; kept here for a quick rollback.
    <script src="//code.jivo.ru/widget/NdUaPxVHnN" async></script>
    -->

    <?php
    $chatIsRomanian = isset($lclang) && strtolower((string) $lclang) === 'ro';
    $chatPrivacyUri = isset($menu['all'][23]->uri)
        ? trim((string) $menu['all'][23]->uri, '/')
        : '';
    $chatPrivacyUrl = $chatPrivacyUri !== ''
        ? rtrim((string) ($site_url ?? ''), '/')
            . '/'
            . trim((string) $lclang, '/')
            . '/'
            . $chatPrivacyUri
        : '';
    $chatTitle = $chatIsRomanian
        ? 'Suport Vizaje-Nica'
        : 'Поддержка Vizaje-Nica';
    $chatAgent = $chatIsRomanian ? 'Suport' : 'Поддержка';
    $chatGreeting = $chatIsRomanian
        ? 'Bună! Cu ce vă putem ajuta?'
        : 'Здравствуйте! Чем мы можем помочь?';
    $chatSubtitle = $chatIsRomanian
        ? 'Echipa noastră vă poate ajuta'
        : 'Поможем с товарами и заказом';
    $chatQuickReplies = $chatIsRomanian
        ? 'Disponibilitatea produselor,Termenele de livrare,Cum plasez o comandă'
        : 'Наличие товара,Сроки доставки,Как оформить заказ';
    $chatPrivacyText = $chatIsRomanian
        ? 'Politica de confidențialitate'
        : 'Политикой конфиденциальности';
    ?>
    <script
        src="https://chat.vizaje-nica.com/widget.js"
        data-api="https://chat.vizaje-nica.com"
        data-theme="light"
        data-title="<?= $escapeMeta($chatTitle) ?>"
        data-agent="<?= $escapeMeta($chatAgent) ?>"
        data-lang="<?= $chatIsRomanian ? 'ro-RO' : 'ru-RU' ?>"
        data-greeting="<?= $escapeMeta($chatGreeting) ?>"
        data-subtitle="<?= $escapeMeta($chatSubtitle) ?>"
        data-quick-replies="<?= $escapeMeta($chatQuickReplies) ?>"
        data-privacy-url="<?= $escapeMeta($chatPrivacyUrl) ?>"
        data-privacy-text="<?= $escapeMeta($chatPrivacyText) ?>"
        async
    ></script>

<?php endif; ?>

<?php if (!empty($consent['analytics'])) : ?>
    <script type="text/javascript">
        (function(c, l, a, r, i, t, y) {
            c[a] = c[a] || function() {
                (c[a].q = c[a].q || []).push(arguments)
            };
            t = l.createElement(r);
            t.async = 1;
            t.src = "https://www.clarity.ms/tag/" + i;
            y = l.getElementsByTagName(r)[0];
            y.parentNode.insertBefore(t, y);
        })(window, document, "clarity", "script", "wx2dsgfmr6");
    </script>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-N4P57D64');
    </script>
    <!-- End Google Tag Manager -->

    
<?php endif; ?>
<!-- inner_view: <?= htmlspecialchars($inner_view ?? '') ?> -->
<?php if (!empty($consent['analytics'])) : ?>
    <script src="https://analytics.ahrefs.com/analytics.js" data-key="nvlPHNwm72VusIwFpEwPKA" async></script>
<?php endif; ?>
 <script
    src="<?= asset_url('app/js/components/loader.js') ?>"
    defer
></script>
</head>

<body
    class="_webp <?= !empty($body_class) ? $body_class : '' ?>"
    data-site-theme="<?= htmlspecialchars((string) ($site_theme_key ?? 'default'), ENT_QUOTES, 'UTF-8') ?>"
>

        <!-- App Loader -->
    <div class="app-loader" id="appLoader">
        <div class="app-loader__logo">
            <img class="app-loader__img" src="<?= ($site_theme_key ?? 'default') === 'halloween' ? '/app/img/hollow.svg' : '/app/img/icons-2/logo-2.svg' ?>" alt="Vizaje-Nica">
        </div>

        <div class="app-loader__line"></div>
    </div>

<?php if (!empty($consent['analytics'])) : ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N4P57D64"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
<?php endif; ?>
    <div class="wrapper">
        
        <!-- <script>
            (function(d) {
                var s = d.createElement('script');
                s.defer = true;
                s.src = 'https://plugin.multisearch.io/plugin/12771?lang=<?= $lclang ?>&key=649da4562679f466726c7df38fcda0ad';
                if (d.head) d.head.appendChild(s);
            })(document);
        </script> -->
        <?php if (!empty($checkout_on)) {
            $this->load->view('/layouts/pages/header_checkout');
        } else {
            $this->load->view('/layouts/pages/header');
        } ?>
        <?php if (!empty($welcome_offer_timer)) { ?>
            <?php $this->load->view('layouts/pages/welcome-offer-countdown'); ?>
        <?php } ?>
        <?php $this->load->view($inner_view); ?>
        <?php $this->load->view('/layouts/pages/footer'); ?>
    </div>

    <?php
    $this->load->view(
        '/layouts/pages/cart/drawer'
    );
    ?>

    <?php
    $this->load->view(
        'layouts/pages/product/quick-view-shell'
    );
    ?>

    <?php if (empty($client_info)) { ?>
        <?php $this->load->view('/layouts/pages/auth/drawer'); ?>

    <?php } ?>
    <?php if (!empty($welcome_offer_prompt_enabled) && empty($client_info)) { ?>
        <?php $this->load->view('layouts/pages/welcome-offer-modal'); ?>
    <?php } ?>
    <?php if ($rouletteShouldRender) { ?>
        <?php $this->load->view('layouts/pages/roulette-modal'); ?>
    <?php } ?>
    <div class="select_variation d_none">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 16.1698L4.83 11.9998L3.41 13.4098L9 18.9998L21 6.99984L19.59 5.58984L9 16.1698Z" fill="red" />
        </svg>
        <span>
            <?= TXT_SELECT_OPTION ?>
        </span>
    </div>
     
    <?php $this->load->view('/layouts/pages/notification'); ?>

    <?php if (empty($consent['given'])) { ?>
        <div class="cookie_on" data-cookie-banner>
            <div class="wrapper">
                <div class="cookie_content">
                    <div class="cookie_text">
                        <strong class="cookie_text__title"><?= $lclang === 'ro' ? 'Confidențialitatea ta contează' : 'Мы ценим вашу конфиденциальность' ?></strong>
                        <span class="cookie_text__description"><?= COOKIE_TEXT ?>
                        <a href="/<?= $lclang ?>/<?= $menu['all'][23]->uri ?>"><?= $menu['all'][23]->title ?></a>
                        </span>
                    </div>
                    <div class="cookie_actions">
                        <button type="button" class="cookie_actions__button cookie_actions__button--secondary" data-cookie-action="reject"><?= COOKIE_REJECT ?></button>
                        <button type="button" class="cookie_actions__settings" data-cookie-action="settings" aria-label="<?= COOKIE_SETTINGS ?>" title="<?= COOKIE_SETTINGS ?>">
                            <span class="icon-equalizer" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="cookie_actions__button cookie_actions__button--primary" data-cookie-action="accept"><?= COOKIE_OK ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php $this->load->view('layouts/pages/cookie/panel'); ?>

     <script
        src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="/app/js/vendors.min.js"></script>
    <script src="/app/js/components/brands.js?v=2" defer></script>
    <script src="/app/js/analytics/ga4-ecommerce.js" defer></script>
    <script>
        window.VN_ANALYTICS_CONSENT =
            <?= !empty($consent['analytics']) ? 'true' : 'false' ?>;
    </script>

    <script src="/app/js/analytics/site-analytics.js?v=5" defer></script>
    <script src="/app/js/analytics/site-analytics-events.js?v=2" defer></script>
    <!-- <script
        src="<?= asset_url('app/js/components/site-feedback.js') ?>"
        defer
    ></script> -->
    <script src="/app/js/components/auth-drawer.js?v=20261019-bonus-register" defer></script>
    <script src="/app/js/components/welcome-offer.js" defer></script>
    <script src="<?= asset_url('app/js/components/header-promo-slider.js') ?>" defer></script>
    <?php if ($rouletteShouldRender) { ?><script src="<?= asset_url('app/js/components/roulette-wheel.js') ?>" defer></script><?php } ?>
    <!-- APP JS START HERE -->
     <?php if ($currentView === 'pages/main/index') { ?>
         <script type="module" src="/app/js/pages/home/home.js?v=20261013-product-hover-gallery"></script>
     <?php } ?>
     <?php if ($currentView === 'pages/catalog/item') { ?>
         <script type="module" src="/app/js/pages/home/products-slider.js?v=20261026-product-recommendations"></script>
     <?php } ?>
     <script src="/app/js/app/01-dynamic-adapt.js" defer></script>
     <script src="/app/js/app/02-sliders.js" defer></script>
     <script src="<?= asset_url('app/js/app/03-header-menu.js') ?>" defer></script>
     <script src="/app/js/app/04-browser-detection.js" defer></script>
     <script src="/app/js/app/05-ui-core.js?v=20261007-account-profile" defer></script>
     <script src="/app/js/app/06-forms.js" defer></script>
     <script src="/app/js/app/07-selects.js" defer></script>
     <script src="/app/js/app/08-inputs.js" defer></script>
     <script src="/app/js/app/09-quantity-range.js" defer></script>
     <script src="/app/js/app/10-scroll-navigation.js" defer></script>
     <script src="/app/js/app/11-catalog-filters.js?v=20261026-category-scroll" defer></script>
    <?php if (in_array($currentView, array('pages/catalog/category', 'pages/catalog/brand', 'pages/catalog/best_sellers'), true)) { ?><script src="/app/js/app/12-category-filter-drawer.js?v=20261009-1" defer></script><script src="/app/js/app/14-catalog-lazy.js?v=20261026-catalog-lazy" defer></script><?php } ?>
    <?php if ($currentView === 'pages/catalog/mobile_categories') { ?><script src="<?= asset_url('app/js/app/13-collections-carousel.js') ?>" defer></script><?php } ?>

    <!-- APP JS END HERE -->
    <script
        src="<?= asset_url('app/js/components/cart-drawer.js') ?>"
        defer
    ></script>
    <script
        src="<?= asset_url('app/js/components/quick-view.js') ?>"
        defer
    ></script>
    <script
        src="<?= asset_url('app/js/components/gift-promotions.js') ?>"
        defer
    ></script>
    <script
        src="<?= asset_url('app/js/components/notifications.js') ?>"
        defer
    ></script>
    <?php if ($currentView === 'pages/main/index') { ?>
        <script
            src="<?= asset_url('app/js/components/home-brand-showcase.js') ?>"
            defer
        ></script>
    <?php } ?>
<?php if ($currentView === 'pages/cart/checkout') : ?>
	<script
        type="module"
		src="<?= asset_url(
			'app/js/components/checkout/checkout.js?v=20261022-phone'
		) ?>"
		defer
	></script>

<?php endif; ?>

    <?php if ($currentView === 'pages/catalog/item') { ?>
        <script
            src="<?= asset_url('app/js/components/product.js') ?>"
            defer
        ></script>
        <script
            src="<?= asset_url('app/js/components/product-promotion.js') ?>"
            defer
        ></script>
    <?php } ?>
    <script
        src="<?= asset_url('app/js/components/catalog-hover-gallery.js?v=20261013') ?>"
        defer
    ></script>
    <!-- MAIN JS START HERE -->
    <script src="/app/js/main/01-catalog.js" defer></script>
    <script src="/app/js/main/02-subscription.js" defer></script>
    <script src="/app/js/main/03-wishlist.js" defer></script>
    <script
        src="<?= asset_url('app/js/main/04-cart.js') ?>"
        defer
    ></script> 
    <script src="/app/js/main/05-checkout.js" defer></script>
    <script src="/app/js/main/06-auth.js" defer></script>
    
    <script src="<?= asset_url('app/js/main/07-cookies.js?v=3') ?>" defer></script>
    <script
    src="<?= asset_url(
        'app/js/main/08-recently-viewed.js'
    ) ?>"
    defer
></script>
    <!-- MAIN JS END HERE -->
    <script src="/app/js/jquery.countdown.js" defer></script>
<?php if ($currentView === 'pages/main/stores') : ?>

	<script
		src="<?= asset_url(
			'app/js/pages/stores.js'
		) ?>"
		defer
	></script>

<?php endif; ?>

<?php if ($currentView === 'pages/certificat/index') : ?>

    <script
        src="<?= asset_url(
            'app/js/pages/certificate-builder.js'
        ) ?>"
        defer
    ></script>

<?php endif; ?>
   
    <script
        src="<?= asset_url('app/js/components/header.js') ?>"
        defer
    ></script>

    <?php if ($currentView === 'pages/articles/blog_item') { ?>
        <script src="<?= asset_url('app/js/pages/article-detail.js') ?>" defer></script>
    <?php } ?>
    


<!-- SEARCH PLUGIN -->


<script>
(function (document) {
	'use strict';

	function loadMultisearch() {
		var searchInput = document.getElementById('head_search');

		if (!searchInput) {
			console.warn(
				'Multisearch: #head_search не найден'
			);

			return;
		}

		if (
			document.querySelector(
				'script[data-multisearch-script]'
			)
		) {
			return;
		}

		var script = document.createElement('script');

		script.src =
			'https://plugin.multisearch.io/plugin/12771'
			+ '?lang=<?= htmlspecialchars(
				$lclang,
				ENT_QUOTES,
				'UTF-8'
			) ?>'
			+ '&key=649da4562679f466726c7df38fcda0ad';

		script.async = false;

		script.setAttribute(
			'data-multisearch-script',
			'true'
		);

		script.onload = function () {
			console.info(
				'Multisearch загружен после #head_search'
			);
		};

		script.onerror = function () {
			console.error(
				'Ошибка загрузки Multisearch'
			);
		};

		document.body.appendChild(script);
	}

	if (document.readyState === 'loading') {
		document.addEventListener(
			'DOMContentLoaded',
			loadMultisearch,
			{
				once: true
			}
		);
	} else {
		loadMultisearch();
	}
})(document);
</script>

</body>

</html>
