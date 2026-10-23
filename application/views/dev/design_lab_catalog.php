<?php
defined('BASEPATH') or exit('No direct script access allowed');

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$catalogProducts = array_values(array_filter((array) ($products ?? array()), static function ($product) {
    return !empty($product->img) && (float) ($product->price ?? 0) > 0;
}));
$catalogProducts = array_slice($catalogProducts, 0, 10);
$catalogTiles = array(
    array('name' => 'Ароматы', 'image' => '/app/img/tile/01.webp'),
    array('name' => 'Макияж', 'image' => '/app/img/tile/04.webp'),
    array('name' => 'Уход', 'image' => '/app/img/tile/05.webp'),
    array('name' => 'Для губ', 'image' => '/app/img/tile/06.webp'),
);

$catalogCards = '';
foreach ($catalogProducts as $index => $product) {
    $image = '/public/products/' . ltrim((string) $product->img, '/');
    $name = trim(preg_replace('/\s+/u', ' ', (string) ($product->title ?? '')));
    $brand = trim((string) ($product->brand_title ?? 'VIZAJE-NICA'));
    $price = !empty($product->discount_price) && (float) $product->discount_price < (float) $product->price
        ? (float) $product->discount_price
        : (float) $product->price;
    $hasDiscount = $price < (float) $product->price;
    $productUrl = '/ru/katalog/' . trim((string) ($product->cat_uri ?? ''), '/') . '/' . trim((string) ($product->uri ?? ''), '/');
    $catalogCards .= '<article class="vc-product" data-product-card>
      <div class="vc-product__media">
        <a class="vc-product__image-link" href="' . $escape($productUrl) . '"><img src="' . $escape($image) . '" alt="' . $escape($name) . '" loading="lazy"></a>
        <button class="vc-product__bag" type="button" aria-label="Добавить в корзину"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l1 12H4L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></button>
      </div>
      <div class="vc-product__details">
        <p class="vc-product__brand">' . $escape($brand) . '</p>
        <a class="vc-product__name" href="' . $escape($productUrl) . '">' . $escape($name) . '</a>
        <p class="vc-product__price">' . $escape(number_format($price, 0, '.', ' ')) . ' MDL' . ($hasDiscount ? ' <del>' . $escape(number_format((float) $product->price, 0, '.', ' ')) . ' MDL</del>' : '') . '</p>
      </div>
    </article>';
}
if ($catalogCards === '') {
    $catalogCards = '<article class="vc-product"><a class="vc-product__media" href="/ru/katalog"><img src="/app/img/card-image/01.webp" alt="Подборка косметических товаров"></a><div class="vc-product__details"><p class="vc-product__brand">VIZAJE-NICA</p><a class="vc-product__name" href="/ru/katalog">Новые открытия для красоты</a><p class="vc-product__price">Смотреть каталог</p></div></article>';
}

$starterHtml = '<main class="vc-catalog">
  <header class="vc-topbar">
    <a class="vc-topbar__brand" href="/ru">VIZAJE-NICA</a>
    <div class="vc-lang-switcher" role="group" aria-label="Язык интерфейса"><button type="button" data-lang-option="ru" aria-pressed="true">RU</button><button type="button" data-lang-option="ro" aria-pressed="false">RO</button></div>
  </header>
  <div class="vc-promo-strip">Дополнительная скидка до −25% по промокоду <b>БОЛЬШЕ</b></div>
  <nav class="vc-categories" aria-label="Подборки каталога"><div class="vc-categories__track">' . implode('', array_map(static function ($tile) use ($escape) {
      return '<a class="vc-category" href="#vc-products"><img src="' . $escape($tile['image']) . '" alt="" loading="lazy"><span>' . $escape($tile['name']) . '</span></a>';
  }, $catalogTiles)) . '</div></nav>
  <section class="vc-catalog-body" id="vc-products" aria-label="Товары каталога">
    <div class="vc-toolbar"><button type="button" class="vc-toolbar__filter" data-open-filter aria-label="Открыть фильтры"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/><circle cx="9" cy="6" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="8" cy="18" r="2"/></svg></button><span>' . count($catalogProducts) . ' товаров</span><button type="button" class="vc-toolbar__grid" data-grid-toggle aria-label="Переключить вид сетки" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/><rect x="3" y="13" width="8" height="8"/><rect x="13" y="13" width="8" height="8"/></svg></button></div>
    <div class="vc-products" data-products-grid>' . $catalogCards . '</div>
  </section>
  <nav class="vc-bottom-nav" aria-label="Основная навигация">
    <a href="/ru" aria-label="Главная"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.5 10.5 12 3.8l8.5 6.7M5.5 9.5v10h5v-6h3v6h5v-10"/></svg><span>Главная</span></a>
    <a href="/ru/katalog" aria-current="page" aria-label="Каталог"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="6" height="6"/><rect x="14" y="4" width="6" height="6"/><rect x="4" y="14" width="6" height="6"/><rect x="14" y="14" width="6" height="6"/></svg><span>Каталог</span></a>
    <a href="/ru/search" aria-label="Поиск"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.2 4.2"/></svg><span>Поиск</span></a>
    <a href="/ru/login_form" aria-label="Профиль"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="9" r="3"/><path d="M6.5 18c1.3-2.2 3.1-3.3 5.5-3.3s4.2 1.1 5.5 3.3"/></svg><span>Профиль</span></a>
    <a href="/cart" aria-label="Корзина"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l1 12H4L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg><span>Корзина</span></a>
  </nav>
  <div class="vc-drawer-backdrop" data-filter-backdrop hidden></div>
  <aside class="vc-filter-drawer" data-filter-drawer aria-label="Фильтры" aria-hidden="true" inert>
    <div class="vc-filter-drawer__head"><h2>Фильтры</h2><button type="button" data-close-filter aria-label="Закрыть фильтры">×</button></div>
    <p class="vc-filter-drawer__count">' . count($catalogProducts) . ' товаров</p>
    <label class="vc-filter-option"><span>В наличии</span><input type="checkbox" checked><i></i></label>
    <details open><summary>Сортировка</summary><label><input type="radio" name="vc-sort" checked> По популярности</label><label><input type="radio" name="vc-sort"> Сначала новинки</label><label><input type="radio" name="vc-sort"> По возрастанию цены</label></details>
    <details><summary>Бренд</summary><label><input type="checkbox"> Популярные бренды</label><label><input type="checkbox"> Все бренды</label></details>
    <button class="vc-filter-drawer__apply" type="button" data-close-filter>Показать ' . count($catalogProducts) . ' товаров</button>
  </aside>
  <script>(function(){var root=document.querySelector(".vc-catalog");if(!root)return;var drawer=root.querySelector("[data-filter-drawer]");var backdrop=root.querySelector("[data-filter-backdrop]");var open=root.querySelector("[data-open-filter]");var closeButtons=root.querySelectorAll("[data-close-filter]");var langButtons=root.querySelectorAll("[data-lang-option]");langButtons.forEach(function(button){button.addEventListener("click",function(){langButtons.forEach(function(option){option.setAttribute("aria-pressed",option===button?"true":"false")})})});var toggle=function(show){drawer.classList.toggle("is-open",show);drawer.setAttribute("aria-hidden",show?"false":"true");drawer.inert=!show;backdrop.hidden=!show;document.body.classList.toggle("vc-lock",show);if(show)drawer.querySelector("button").focus()};open.addEventListener("click",function(){toggle(true)});closeButtons.forEach(function(button){button.addEventListener("click",function(){toggle(false)})});backdrop.addEventListener("click",function(){toggle(false)});document.addEventListener("keydown",function(event){if(event.key==="Escape"&&!backdrop.hidden)toggle(false)});root.querySelector("[data-grid-toggle]").addEventListener("click",function(event){var button=event.currentTarget;var compact=root.querySelector("[data-products-grid]").classList.toggle("is-compact");button.setAttribute("aria-pressed",compact?"true":"false")})})()</script>
</main>';

$starterCss = <<<'CSS'
* { box-sizing: border-box; }
html, body { margin: 0; min-height: 100%; }
@font-face { font-family: VN-Montserrat; src: url('/app/fonts/Montserrat-Regular.woff2') format('woff2'); font-weight: 400; font-display: swap; }
@font-face { font-family: VN-Montserrat; src: url('/app/fonts/Montserrat-Medium.woff2') format('woff2'); font-weight: 500; font-display: swap; }
@font-face { font-family: VN-Montserrat; src: url('/app/fonts/Montserrat-SemiBold.woff2') format('woff2'); font-weight: 600; font-display: swap; }
@font-face { font-family: VN-Lora; src: url('/app/fonts/Lora-Regular.woff2') format('woff2'); font-weight: 400; font-display: swap; }
body { background: #fff; color: #111; font-family: VN-Montserrat, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
button, a { color: inherit; font: inherit; }
a { text-decoration: none; }
.vc-catalog { --vc-accent: #111; width: 100%; min-height: 100vh; overflow-x: clip; padding-bottom: calc(70px + env(safe-area-inset-bottom)); background: #fff; }
.vc-topbar { height: 55px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; border-bottom: 1px solid #eee; }
.vc-topbar__brand { font-family: VN-Lora, Georgia, serif; font-size: 15px; font-weight: 600; letter-spacing: .1em; }
.vc-lang-switcher { width: 88px; height: 32px; display: grid; grid-template-columns: 1fr 1fr; align-items: center; padding: 2px; border: 1px solid #e5e5e5; border-radius: 999px; background: #fff; }
.vc-lang-switcher button { min-width: 0; height: 26px; padding: 0; border: 0; border-radius: 999px; background: transparent; color: #999; font-size: 11px; font-weight: 500; cursor: pointer; }
.vc-lang-switcher button[aria-pressed="true"] { background: #111; color: #fff; }
.vc-promo-strip { min-height: 38px; display: flex; align-items: center; justify-content: center; gap: 4px; padding: 7px 12px; background: var(--vc-accent); color: #fff; font-size: 11px; font-weight: 600; text-align: center; }
.vc-categories { overflow: hidden; border-bottom: 1px solid #e9e9e9; }
.vc-categories__track { display: flex; gap: 12px; overflow-x: auto; padding: 18px; scrollbar-width: none; scroll-snap-type: x mandatory; }
.vc-categories__track::-webkit-scrollbar { display: none; }
.vc-category { flex: 0 0 auto; min-width: 126px; height: 66px; display: flex; align-items: center; gap: 9px; padding: 5px 12px 5px 5px; border: 1px solid #e8e8e8; border-radius: 40px; scroll-snap-align: start; }
.vc-category img { width: 54px; height: 54px; flex: 0 0 54px; border-radius: 50%; object-fit: cover; }
.vc-category span { font-size: 11px; font-weight: 600; white-space: nowrap; }
.vc-toolbar { height: 62px; display: grid; grid-template-columns: 50px 1fr 50px; align-items: center; border-bottom: 1px solid #eee; text-align: center; font-size: 13px; }
.vc-toolbar button { height: 100%; display: grid; place-items: center; border: 0; background: #fff; cursor: pointer; }
.vc-toolbar svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 1.45; stroke-linecap: round; stroke-linejoin: round; }
.vc-products { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px 10px; padding: 0 0 16px; }
.vc-product { min-width: 0; }
.vc-product__media { position: relative; display: block; aspect-ratio: 1 / 1; background: #f2f2f2; }
.vc-product__image-link, .vc-product__image-link img { width: 100%; height: 100%; display: block; }
.vc-product__media img { position: absolute; top: 50%; left: 50%; width: 88%; height: 88%; object-fit: contain; mix-blend-mode: multiply; transform: translate(-50%, -50%); }
.vc-product__bag { position: absolute; right: 8px; bottom: -17px; width: 36px; height: 36px; display: grid; place-items: center; border: 0; border-radius: 50%; background: #2f2f2f; color: #fff; }
.vc-product__bag svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.3; stroke-linecap: round; stroke-linejoin: round; }
.vc-product__details { min-height: 138px; display: flex; flex-direction: column; padding: 14px 16px 16px; }
.vc-product__brand { margin: 0 0 5px; overflow: hidden; color: #6f6f6f; font-size: 9px; font-weight: 500; line-height: 1.2; letter-spacing: .08em; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }
.vc-product__name { min-height: 2.6em; display: -webkit-box; overflow: hidden; font-size: 13px; font-weight: 500; line-height: 1.3; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.vc-product__price { display: flex; align-items: baseline; flex-wrap: wrap; gap: 7px; margin: auto 0 0; padding-top: 10px; font-size: 16px; font-weight: 600; line-height: 1.15; }
.vc-product__price del { color: #999; font-size: 11px; font-weight: 400; }
.vc-products.is-compact { grid-template-columns: minmax(0, 1fr); }
.vc-products.is-compact .vc-product { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: start; gap: 0; }
.vc-products.is-compact .vc-product__media { aspect-ratio: 1 / 1; }
.vc-products.is-compact .vc-product__details { padding: 10px 12px; }
.vc-bottom-nav { position: fixed; z-index: 9; right: 0; bottom: 0; left: 0; min-height: calc(62px + env(safe-area-inset-bottom)); display: grid; grid-template-columns: repeat(5, 1fr); align-items: start; padding: 9px 5px env(safe-area-inset-bottom); border-top: 1px solid #eee; background: #fff; }
.vc-bottom-nav a { min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 3px; color: #111; }
.vc-bottom-nav a[aria-current="page"] { color: #ad907a; }
.vc-bottom-nav svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
.vc-bottom-nav span { font-size: 8px; line-height: 1.1; }
.vc-drawer-backdrop { position: fixed; z-index: 19; inset: 0; background: #0007; }
.vc-filter-drawer { position: fixed; z-index: 20; top: 0; right: 0; bottom: 0; width: min(88vw, 390px); display: flex; flex-direction: column; gap: 16px; padding: 22px 20px calc(20px + env(safe-area-inset-bottom)); background: #fff; transform: translateX(102%); transition: transform .25s ease; }
.vc-filter-drawer.is-open { transform: translateX(0); }
.vc-filter-drawer__head { display: flex; align-items: center; justify-content: space-between; }
.vc-filter-drawer__head h2 { margin: 0; font-size: 23px; font-weight: 600; }
.vc-filter-drawer__head button { width: 36px; height: 36px; border: 0; border-radius: 50%; background: #f3f3f3; font-size: 24px; }
.vc-filter-drawer__count { margin: -8px 0 0; color: #777; font-size: 12px; }
.vc-filter-option { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; font-size: 14px; }
.vc-filter-option input { position: absolute; opacity: 0; }
.vc-filter-option i { position: relative; width: 38px; height: 22px; border-radius: 20px; background: #ddd; }
.vc-filter-option i::after { position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; content: ""; transition: transform .2s; }
.vc-filter-option input:checked + i { background: #111; }
.vc-filter-option input:checked + i::after { transform: translateX(16px); }
.vc-filter-drawer details { padding: 13px 0; border-bottom: 1px solid #eee; }
.vc-filter-drawer summary { font-size: 15px; font-weight: 600; cursor: pointer; }
.vc-filter-drawer details label { display: flex; align-items: center; gap: 9px; margin-top: 14px; color: #555; font-size: 12px; }
.vc-filter-drawer__apply { min-height: 48px; margin-top: auto; border: 0; background: #111; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; }
.vc-lock { overflow: hidden; }
@media (min-width: 700px) {
  .vc-topbar { height: 64px; padding: 0 5vw; }
  .vc-topbar__brand { font-size: 18px; }
  .vc-categories__track { justify-content: center; }
  .vc-category { min-width: 155px; }
  .vc-toolbar { max-width: 1280px; margin: auto; }
  .vc-products { max-width: 1280px; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 24px 16px; margin: 0 auto; }
  .vc-product__media { aspect-ratio: 1 / 1; }
  .vc-product__details { padding: 14px 10px 0; }
  .vc-product__name { font-size: 15px; }
  .vc-bottom-nav { max-width: 480px; right: 50%; left: auto; width: min(100%, 480px); transform: translateX(50%); border-right: 1px solid #eee; border-left: 1px solid #eee; }
  .vc-filter-drawer { width: 390px; }
}
@media (max-width: 360px) {
  .vc-product__details { padding-right: 10px; padding-left: 10px; }
  .vc-product__name { font-size: 12px; }
  .vc-promo-strip { font-size: 9px; }
}
@media (max-width: 767px) {
  .vc-product__details { min-height: 130px; padding: 17px 0 0; }
  .vc-product__brand { margin-bottom: 7px; color: #111; font-size: 10px; font-weight: 600; }
  .vc-product__name { min-height: 2.7em; font-size: 15px; font-weight: 400; line-height: 1.35; }
}
CSS;
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Design Lab · Каталог · Vizaje-Nica</title>
    <link rel="stylesheet" href="/app/css/dev/design-lab.css?v=20261002-catalog-fit-3">
    <script src="/app/js/dev/design-lab.js?v=20261002-lang-segmented" defer></script>
</head>
<body class="vn-design-lab" data-draft-key="vn-design-lab-catalog-draft-v1">
    <header class="lab-topbar">
        <a class="lab-wordmark" href="/dev/design-lab" aria-label="Вернуться к главной в Design Lab">VN<span> / </span>DESIGN LAB</a>
        <div class="lab-topbar__right"><span class="lab-local-indicator"><i></i> Только локальный просмотр</span><a class="lab-page-link" href="/dev/design-lab">Главная ↗</a><button type="button" class="lab-button lab-button--quiet" data-reset>Сбросить</button></div>
    </header>
    <main class="lab-layout">
        <section class="lab-workspace" aria-label="Редактор и предпросмотр каталога">
            <div class="lab-workspace__heading">
                <div><h1>Дизайн-лаборатория · Каталог</h1><p>Мобильная версия каталога по макету Figma. Черновик отдельный от главной.</p></div>
                <div class="lab-actions"><span class="lab-save-state" data-save-state>Стартовый макет</span><button type="button" class="lab-button" data-restore>Вернуть стартовый макет</button></div>
            </div>
            <div class="lab-preview-bar">
                <div class="lab-preview-bar__label"><span class="lab-live-dot"></span>Предпросмотр каталога</div>
                <div class="lab-preview-bar__controls">
                    <div class="lab-viewport-switch" role="group" aria-label="Размер предпросмотра">
                        <button type="button" data-viewport="desktop" aria-pressed="false">Компьютер</button>
                        <button type="button" data-viewport="mobile" aria-pressed="true">Телефон</button>
                    </div>
                    <button type="button" class="lab-fullscreen-button" data-fullscreen aria-pressed="false"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7 3H3v4M13 3h4v4M17 13v4h-4M3 13v4h4"/></svg><span data-fullscreen-label>На весь экран</span></button>
                </div>
            </div>
            <div class="lab-stage" data-stage data-content="catalog" data-default-viewport="mobile" data-viewport="mobile"><iframe title="Изолированный предпросмотр каталога" sandbox="allow-scripts" data-preview></iframe></div>
        </section>
        <aside class="lab-editor" aria-label="HTML и CSS редакторы">
            <div class="lab-editor__heading"><div><h2>Редактор</h2><p>Меняйте HTML и CSS — предпросмотр обновляется сразу.</p></div><span class="lab-editor__language">HTML + CSS</span></div>
            <label class="lab-code-label" for="lab-html">HTML</label>
            <textarea id="lab-html" class="lab-code" spellcheck="false" data-html-editor><?= htmlspecialchars($starterHtml, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
            <label class="lab-code-label" for="lab-css">CSS</label>
            <textarea id="lab-css" class="lab-code lab-code--css" spellcheck="false" data-css-editor><?= htmlspecialchars($starterCss, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
            <p class="lab-editor__note">Черновик каталога хранится отдельно от главной страницы и не меняет витрину.</p>
        </aside>
    </main>
</body>
</html>
