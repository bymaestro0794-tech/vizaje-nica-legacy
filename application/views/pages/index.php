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
    !empty($page_title)
        ? trim((string) $page_title)
        : 'Vizaje-Nica';

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
$globalCss = [
    'app/css/style/01-base.css',
    'app/css/style/02-overlays-auth-map.css',
    'app/css/style/02-cookie.css',
    'app/css/style/03-header.css',
    'app/css/style/04-footer.css',
    'app/css/style/05-breadcrumbs.css',
    'app/css/style/16-legacy-overrides.css',
    'app/css/style/17-interactions.css',
    'app/css/style/18-loader.css',
    'app/css/style/23-notifications.css',
    'app/css/style/24-cart-drawer.css',
    'app/css/style/25-auth-drawer.css',
    'app/css/style/26-cookie-consent-panel.css',
    'app/css/style/27-quick-view.css',
];

/*
 * CSS конкретной страницы.
 */
$pageCss = [];

$currentView = trim((string) ($inner_view ?? ''), '/');

switch ($currentView) {
    case 'pages/main/index':
        $pageCss[] = '/app/css/pages/home/home.css';
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
        $pageCss[] = 'app/css/style/06-catalog.css';
        $pageCss[] = 'app/css/style/15-nouislider.css';
        break;

    case 'pages/catalog/index':
    case 'pages/catalog/category':
    case 'pages/catalog/best_sellers':
    case 'pages/catalog/hits':
    case 'pages/catalog/new_products':
    case 'pages/catalog/search':
    case 'pages/catalog/brand':
        $pageCss[] = 'app/css/style/05-home.css';
        $pageCss[] = 'app/css/style/06-catalog.css';
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
}

?>
<?php if ($currentView === 'pages/cart/checkout') : ?>

	<link
		rel="stylesheet"
		href="https://unpkg.com/maplibre-gl@5.6.1/dist/maplibre-gl.css"
	>

<?php endif; ?>

<!-- Глобальные стили -->
<?php foreach ($globalCss as $cssFile) { ?>
    <link
        rel="stylesheet"
        href="<?= asset_url($cssFile) ?>"
    >
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

    <script
        src="//code.jivo.ru/widget/NdUaPxVHnN"
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
 <script
    src="<?= asset_url('app/js/components/loader.js') ?>"
    defer
></script>
</head>

<body class="_webp <?= !empty($body_class) ? $body_class : '' ?>">

        <!-- App Loader -->
    <div class="app-loader" id="appLoader">
        <div class="app-loader__logo">
            <img class="app-loader__img" src="/app/img/icons-2/logo-2.svg" alt="Vizaje-Nica">
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
                    <img class="cookie_icon" src="/app/img/icons-2/cookie/cookie-bite-solid-svgrepo-com.svg" alt="cookie_icon">
                    <div class="cookie_text"><?= COOKIE_TEXT ?>
                        <a href="/<?= $lclang ?>/<?= $menu['all'][23]->uri ?>"><?= $menu['all'][23]->title ?></a>
                    </div>
                    <div class="cookie_actions">
                        <button type="button" class="cookie_actions__settings" data-cookie-action="settings"><?= COOKIE_SETTINGS ?></button>
                        <button type="button" class="cookie_actions__button cookie_actions__button--secondary" data-cookie-action="reject"><?= COOKIE_REJECT ?></button>
                        <button type="button" class="cookie_actions__button cookie_actions__button--primary" data-cookie-action="accept"><?= COOKIE_OK ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php $this->load->view('layouts/pages/cookie/panel'); ?>


<script>
	window.CERTIFICATE_MESSAGES = {
		design: <?= json_encode(
			CERTIFICATE_ERR_DESIGN_NOT_SELECTED,
			JSON_UNESCAPED_UNICODE
		) ?>,

		amountEmpty: <?= json_encode(
			CERTIFICATE_ERR_AMOUNT_EMPTY,
			JSON_UNESCAPED_UNICODE
		) ?>,

		amountMin: <?= json_encode(
			CERTIFICATE_ERR_AMOUNT_MIN,
			JSON_UNESCAPED_UNICODE
		) ?>,

		amountMax: <?= json_encode(
			CERTIFICATE_ERR_AMOUNT_MAX,
			JSON_UNESCAPED_UNICODE
		) ?>,

		sender: <?= json_encode(
			CERTIFICATE_ERR_SENDER_NAME_EMPTY,
			JSON_UNESCAPED_UNICODE
		) ?>,

		recipient: <?= json_encode(
			CERTIFICATE_ERR_RECIPIENT_NAME_EMPTY,
			JSON_UNESCAPED_UNICODE
		) ?>,

		greeting: <?= json_encode(
			CERTIFICATE_ERR_GREETING_TEXT_EMPTY,
			JSON_UNESCAPED_UNICODE
		) ?>,

		phone: <?= json_encode(
			CERTIFICATE_ERR_PHONE_EMPTY,
			JSON_UNESCAPED_UNICODE
		) ?>,

		timeMode: <?= json_encode(
			CERTIFICATE_ERR_SEND_TIME_NOT_SELECTED,
			JSON_UNESCAPED_UNICODE
		) ?>,

		date: <?= json_encode(
			CERTIFICATE_ERR_SEND_DATE_EMPTY,
			JSON_UNESCAPED_UNICODE
		) ?>,

		time: <?= json_encode(
			CERTIFICATE_ERR_SEND_TIME_EMPTY,
			JSON_UNESCAPED_UNICODE
		) ?>,

		email: <?= json_encode(
			CERTIFICATE_ERR_EMAIL_EMPTY,
			JSON_UNESCAPED_UNICODE
		) ?>
	}
</script>



     <script
        src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="/app/js/vendors.min.js"></script>
    <script src="/app/js/components/brands.js?v=1" defer></script>
    <script src="/app/js/analytics/ga4-ecommerce.js"></script>
    <script src="/app/js/components/auth-drawer.js" defer></script>
    <!-- APP JS START HERE -->
     <script type="module" src="/app/js/pages/home/home.js"></script>
    <script src="/app/js/app/01-dynamic-adapt.js"></script>
    <script src="/app/js/app/02-sliders.js"></script>
    <script src="/app/js/app/03-header-menu.js"></script>
    <script src="/app/js/app/04-browser-detection.js"></script>
    <script src="/app/js/app/05-ui-core.js"></script>
    <script src="/app/js/app/06-forms.js"></script>
    <script src="/app/js/app/07-selects.js"></script>
    <script src="/app/js/app/08-inputs.js"></script>
    <script src="/app/js/app/09-quantity-range.js"></script>
    <script src="/app/js/app/10-scroll-navigation.js"></script>
    <script src="/app/js/app/11-catalog-filters.js"></script>
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
        src="<?= asset_url('app/js/components/notifications.js') ?>"
        defer
    ></script>
    <script
        src="<?= asset_url('app/js/components/home-brand-showcase.js') ?>"
        defer
    ></script>
<?php if ($currentView === 'pages/cart/checkout') : ?>

	<script
		src="https://unpkg.com/maplibre-gl@5.6.1/dist/maplibre-gl.js"
	></script>

	<script
		src="<?= asset_url(
			'app/js/components/checkout-pickup.js'
		) ?>"
		defer
	></script>

	<script
		src="<?= asset_url(
			'app/js/components/checkout-v2.js'
		) ?>"
		defer
	></script>

<?php endif; ?>

    <script
        src="<?= asset_url('app/js/components/product.js') ?>"
        defer
    ></script>
    <!-- MAIN JS START HERE -->
    <script src="/app/js/main/01-catalog.js"></script>
    <script src="/app/js/main/02-subscription.js"></script>
    <script src="/app/js/main/03-wishlist.js"></script>
    <script
        src="<?= asset_url('app/js/main/04-cart.js') ?>"
        defer
    ></script> 
    <script src="/app/js/main/05-checkout.js"></script>
    <script src="/app/js/main/06-auth.js"></script>
    
    <script src="<?= asset_url('app/js/main/07-cookies.js?v=2') ?>"></script>
    <script
    src="<?= asset_url(
        'app/js/main/08-recently-viewed.js'
    ) ?>"
    defer
></script>
    <!-- MAIN JS END HERE -->
    <script src="/app/js/jquery.countdown.js"></script>
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
    ></script>
    


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