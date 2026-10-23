<?php
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$renderProductCards = static function ($items, $badge, $showFavorite = false) use ($escape) {
    $html = '';
    foreach (array_slice((array) $items, 0, 10) as $product) {
        if ((float) ($product->price ?? 0) <= 0) {
            continue;
        }
        $image = !empty($product->img)
            ? '/public/products/' . ltrim((string) $product->img, '/')
            : '/public/i/no_image.png';
        $name = trim(preg_replace('/\\s+/u', ' ', (string) ($product->title ?? '')));
        $brand = trim((string) ($product->brand_title ?? 'VIZAJE-NICA'));
        $hasDiscount = !empty($product->discount_price) && (float) $product->discount_price < (float) ($product->price ?? 0);
        $price = $hasDiscount ? $product->discount_price : ($product->price ?? 0);
        $productUrl = '/ru/katalog/' . trim((string) ($product->cat_uri ?? ''), '/') . '/' . trim((string) ($product->uri ?? ''), '/');
        $salePercent = !empty($product->sale_percent)
            ? (int) $product->sale_percent
            : (int) round((1 - ((float) $price / max(1, (float) ($product->price ?? 1)))) * 100);
        $discount = $hasDiscount
            ? '<span class="vn-product__sale">-' . $escape($salePercent) . '%</span>'
            : '';
        $badgeMarkup = trim((string) $badge) !== ''
            ? '<span class="vn-product__badge vn-product__badge--' . strtolower($escape($badge)) . '">' . $escape($badge) . '</span>'
            : '';
        $favoriteMarkup = $showFavorite
            ? '<button class="vn-product__favorite" type="button" aria-label="Добавить в избранное" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.8c0 5.3-8.8 11-8.8 11S3.2 14.1 3.2 8.8A4.7 4.7 0 0 1 12 6.3a4.7 4.7 0 0 1 8.8 2.5Z"/></svg></button>'
            : '';
        $variantMarkup = $showFavorite && !empty($product->volume)
            ? '<div class="vn-product__variants" aria-label="Объём"><span>' . $escape($product->volume) . '</span></div>'
            : '';

        $html .= '<article class="vn-product">
          <div class="vn-product__media"><a class="vn-product__visual" href="' . $escape($productUrl) . '"><img src="' . $escape($image) . '" alt="' . $escape($name) . '" loading="lazy"><span class="vn-product__flags">' . $badgeMarkup . $discount . '</span><span class="vn-product__bag" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 8h14l1 12H4L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></span></a>' . $favoriteMarkup . '</div>' . $variantMarkup . '
          <a class="vn-product__info" href="' . $escape($productUrl) . '"><p class="vn-product__brand">' . $escape($brand) . '</p><h3>' . $escape($name) . '</h3><p class="vn-product__price">' . $escape(number_format((float) $price, 0, '.', ' ')) . ' MDL' . ($hasDiscount ? ' <del>' . $escape(number_format((float) ($product->price ?? 0), 0, '.', ' ')) . ' MDL</del>' : '') . '</p></a>
        </article>';
    }

    if ($html === '') {
        $html = '<article class="vn-product"><a class="vn-product__visual" href="/ru/katalog"><img src="/app/img/card-image/01.webp" alt="Подборка косметических продуктов"><span class="vn-product__badge">Выбор редакции</span></a><a class="vn-product__info" href="/ru/katalog"><p class="vn-product__brand">VIZAJE-NICA</p><h3>Откройте подборку для ежедневного ухода</h3><p class="vn-product__price">В каталог ↗</p></a></article>';
    }

    return $html;
};

$starterHtml = '<main class="vn-home">
  <div class="vn-shipping"><span>Бесплатная доставка</span> от 500 MDL</div>
  <header class="vn-header">
    <a class="vn-wordmark" href="/ru" aria-label="Vizaje-Nica — главная">VIZAJE<span>—</span>NICA</a>
    <nav class="vn-lang-switch" aria-label="Выбор языка"><a href="/ru" aria-current="page">RU</a><a href="/ro">RO</a></nav>
  </header>

  <section class="vn-hero" aria-labelledby="vn-hero-title">
    <picture class="vn-hero__picture"><source media="(max-width: 700px)" srcset="/app/img/banner-image-mob.webp"><img src="/app/img/banner-image.webp" alt="Подборка косметики и ароматов Vizaje-Nica"></picture>
    <div class="vn-hero__veil"></div>
    <div class="vn-hero__copy"><p class="vn-eyebrow">VIZAJE-NICA · BEAUTY SELECTED</p><h1 id="vn-hero-title">Красота<br>в твоём ритме</h1><p class="vn-hero__description">Любимые бренды, новые открытия и маленькие радости на каждый день.</p><a class="vn-button" href="#vn-showcase">Открыть подборку <span aria-hidden="true">↗</span></a></div>
    <div class="vn-hero__dots" aria-hidden="true"><span class="is-active"></span><span></span><span></span><span></span></div>
  </section>';

$starterHtml .= '<section class="vn-section vn-showcase" id="vn-showcase" aria-label="Подборка товаров">
  <div class="vn-showcase__tabs" role="tablist" aria-label="Подборки товаров">
    <input class="vn-showcase__radio" type="radio" name="vn-showcase-tab" id="vn-tab-sale" aria-controls="vn-panel-sale" checked><label id="vn-label-sale" role="tab" aria-controls="vn-panel-sale" for="vn-tab-sale">Скидки</label>
    <input class="vn-showcase__radio" type="radio" name="vn-showcase-tab" id="vn-tab-bestsellers" aria-controls="vn-panel-bestsellers"><label id="vn-label-bestsellers" role="tab" aria-controls="vn-panel-bestsellers" for="vn-tab-bestsellers">Топ продаж</label>
    <input class="vn-showcase__radio" type="radio" name="vn-showcase-tab" id="vn-tab-new" aria-controls="vn-panel-new"><label id="vn-label-new" role="tab" aria-controls="vn-panel-new" for="vn-tab-new">Новинки</label>
  </div>
  <div class="vn-showcase__panel" id="vn-panel-sale" role="tabpanel" aria-labelledby="vn-label-sale"><div class="vn-products">' . $renderProductCards($saleProducts, 'SALE', true) . '</div><button class="vn-showcase__more" type="button" aria-label="Показать следующие товары"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
  <div class="vn-showcase__panel" id="vn-panel-bestsellers" role="tabpanel" aria-labelledby="vn-label-bestsellers"><div class="vn-products">' . $renderProductCards($bestSellerProducts, 'ХИТ', true) . '</div><button class="vn-showcase__more" type="button" aria-label="Показать следующие товары"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
  <div class="vn-showcase__panel" id="vn-panel-new" role="tabpanel" aria-labelledby="vn-label-new"><div class="vn-products">' . $renderProductCards($products, 'NEW', true) . '</div><button class="vn-showcase__more" type="button" aria-label="Показать следующие товары"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
</section>
';

$featuredBrandSection = null;
foreach ((array) ($brandSections ?? array()) as $brandCandidate) {
    if (!empty($brandCandidate->title) && !empty($brandCandidate->products)) {
        $featuredBrandSection = $brandCandidate;
        break;
    }
}

$brandMarkup = '';
if ($featuredBrandSection) {
    $brandImage = !empty($featuredBrandSection->img)
        ? newthumbs($featuredBrandSection->img, 'home_brand_sections')
        : '';
    $brandUrl = trim((string) ($featuredBrandSection->url ?? '')) ?: '/ru/brands';
    $brandUrlHost = strtolower((string) parse_url($brandUrl, PHP_URL_HOST));
    if (in_array($brandUrlHost, array('vizaje-nica.com', 'www.vizaje-nica.com', 'vizaje-nica.local'), true)) {
        $brandUrl = (string) parse_url($brandUrl, PHP_URL_PATH);
        $brandQuery = parse_url((string) ($featuredBrandSection->url ?? ''), PHP_URL_QUERY);
        if ($brandQuery) {
            $brandUrl .= '?' . $brandQuery;
        }
    }
    $brandTitle = trim((string) $featuredBrandSection->title);

    $brandMarkup = '<section class="vn-brand-feature" aria-labelledby="vn-brand-title">
    <a class="vn-brand-feature__hero" href="' . $escape($brandUrl) . '">' .
        ($brandImage !== ''
            ? '<img class="vn-brand-feature__image" src="' . $escape($brandImage) . '" alt="" loading="lazy">'
            : '') . '
      <span class="vn-brand-feature__veil" aria-hidden="true"></span>
      <span class="vn-brand-feature__copy"><span class="vn-brand-feature__eyebrow">ВЫБОР VIZAJE-NICA</span><span class="vn-brand-feature__title" id="vn-brand-title">' . $escape($brandTitle) . '</span><span class="vn-brand-feature__link">ПЕРЕЙТИ К БРЕНДУ <b aria-hidden="true">→</b></span></span>
      <span class="vn-brand-feature__tag" aria-hidden="true">БРЕНД</span>
    </a>
    <div class="vn-brand-feature__products"><div class="vn-brand-feature__rail">' . $renderProductCards($featuredBrandSection->products, '') . '</div><button class="vn-brand-feature__next" type="button" aria-label="Следующие товары бренда"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></div>
  </section>';
}

$starterHtml .= '<section class="vn-categories" aria-labelledby="vn-categories-title">
    <h2 id="vn-categories-title">Покупайте по категориям</h2>
    <div class="vn-categories__track">
      <a class="vn-category-card" href="/ru/katalog/tonalnyie-sredstva">
        <img src="/app/img/tile/04.png" alt="Помады и средства для макияжа" loading="lazy">
        <span class="vn-category-card__label"><span>Makeup</span><span>Макияж <b aria-hidden="true">↗</b></span></span>
      </a>
      <a class="vn-category-card" href="/ru/katalog/parfyumirovannaya-voda-dlya-jenschin">
        <img src="/app/img/tile/01.png" alt="Парфюмерные флаконы" loading="lazy">
        <span class="vn-category-card__label"><span>Fragrance</span><span>Парфюмерия <b aria-hidden="true">↗</b></span></span>
      </a>
      <a class="vn-category-card" href="/ru/katalog/uvlajnenie-i-pitanie-dlya-litsa">
        <img src="/app/img/tile/05.png" alt="Уход за кожей лица" loading="lazy">
        <span class="vn-category-card__label"><span>Skincare</span><span>Уход за кожей <b aria-hidden="true">↗</b></span></span>
      </a>
    </div>
    <a class="vn-categories__more" href="/ru/katalog">Посмотреть больше <span aria-hidden="true">→</span></a>
  </section>
  ' . $brandMarkup . '
  <section class="vn-gift-certificate" aria-labelledby="vn-gift-certificate-title">
    <div class="vn-gift-certificate__container">
      <div class="vn-gift-certificate__art">
        <h2 class="vn-gift-certificate__title" id="vn-gift-certificate-title">Пластиковый<br>сертификат</h2>
        <img class="vn-gift-certificate__image" src="/app/b2b/img/certificate-main.png" alt="Подарочный сертификат Vizaje-Nica" width="1240" height="1240" loading="lazy">
      </div>
      <a class="vn-gift-certificate__button" href="/ru/katalog/sertifikatyi/107557">Выбрать сертификат</a>
    </div>
  </section>
  <section class="vn-store-feature" aria-labelledby="vn-store-feature-title">
    <div class="vn-store-feature__container">
      <div class="vn-store-feature__art">
        <h2 class="vn-store-feature__title" id="vn-store-feature-title">Наши<br>магазины</h2>
        <img class="vn-store-feature__image" src="/app/img/b2b/home-store.png" alt="Фирменный магазин Vizaje-Nica" width="1448" height="1086" loading="lazy">
      </div>
      <a class="vn-store-feature__button" href="/ru/magazinyi">Найти магазин</a>
    </div>
  </section>
  <section class="vn-benefits" aria-label="Преимущества Vizaje-Nica"><div><span>01</span><p>Оригинальные<br>бренды</p></div><div><span>02</span><p>Доставка по<br>Молдове</p></div><div><span>03</span><p>Помощь с<br>выбором</p></div></section>
  <footer class="vn-footer"><a class="vn-wordmark" href="/ru">VIZAJE<span>—</span>NICA</a><span>Красота — в деталях.</span><a href="/ru/katalog">Перейти в каталог <span aria-hidden="true">↗</span></a></footer>
  <nav class="vn-mobile-nav" aria-label="Нижняя навигация">
    <a href="/ru" aria-current="page" aria-label="Главная"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.5 10.5 12 3.8l8.5 6.7M5.5 9.5v10h5v-6h3v6h5v-10"/></svg><span>Главная</span></a>
    <a href="/ru/katalog" aria-label="Каталог"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg><span>Каталог</span></a>
    <a href="/ru/search" aria-label="Поиск"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.2 4.2"/></svg><span>Поиск</span></a>
    <a href="/ru/login_form" aria-label="Профиль"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="9" r="3"/><path d="M6.5 18c1.3-2.2 3.1-3.3 5.5-3.3s4.2 1.1 5.5 3.3"/></svg><span>Профиль</span></a>
    <a href="/cart" aria-label="Корзина"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l1 12H4L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg><span>Корзина</span></a>
  </nav>
  <script>(function(){var section=document.getElementById("vn-showcase");if(!section)return;section.querySelectorAll(".vn-showcase__panel").forEach(function(panel){var rail=panel.querySelector(".vn-products");var next=panel.querySelector(".vn-showcase__more");if(!rail||!next)return;next.addEventListener("click",function(){var card=rail.querySelector(".vn-product");var gap=parseFloat(window.getComputedStyle(rail).columnGap)||30;rail.scrollBy({left:(card?card.getBoundingClientRect().width:180)+gap,behavior:"smooth"})})});section.querySelectorAll(".vn-showcase__radio").forEach(function(input){input.addEventListener("change",function(){var panel=document.getElementById(input.getAttribute("aria-controls"));if(!panel)panel=document.querySelector("[aria-labelledby=\""+input.id+"\"]");var rail=panel&&panel.querySelector(".vn-products");if(rail)rail.scrollTo({left:0,behavior:"smooth"})})});section.addEventListener("click",function(event){var button=event.target.closest(".vn-product__favorite");if(!button)return;var active=button.getAttribute("aria-pressed")!=="true";button.setAttribute("aria-pressed",active?"true":"false");button.setAttribute("aria-label",active?"Убрать из избранного":"Добавить в избранное")})})()</script>
  <script>(function(){document.querySelectorAll(".vn-brand-feature").forEach(function(section){var rail=section.querySelector(".vn-brand-feature__rail");var next=section.querySelector(".vn-brand-feature__next");if(!rail||!next)return;var update=function(){next.disabled=rail.scrollWidth<=rail.clientWidth+2};var step=function(){var card=rail.querySelector(".vn-product");return (card?card.getBoundingClientRect().width:160)+(parseFloat(getComputedStyle(rail).columnGap)||10)};next.addEventListener("click",function(){rail.scrollBy({left:step(),behavior:"smooth"})});rail.addEventListener("scroll",update,{passive:true});window.addEventListener("resize",update);requestAnimationFrame(update)})})()</script>
</main>';

$starterCss = <<<'CSS'
@font-face { font-family: VN-Montserrat; src: url('/app/fonts/Montserrat-Regular.woff2') format('woff2'); font-weight: 400; font-display: swap; }
@font-face { font-family: VN-Montserrat; src: url('/app/fonts/Montserrat-Medium.woff2') format('woff2'); font-weight: 500; font-display: swap; }
@font-face { font-family: VN-Montserrat; src: url('/app/fonts/Montserrat-SemiBold.woff2') format('woff2'); font-weight: 600; font-display: swap; }
@font-face { font-family: VN-Lora; src: url('/app/fonts/Lora-Regular.woff2') format('woff2'); font-weight: 400; font-display: swap; }
* { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body { margin: 0; background: #fff; color: #111; font-family: VN-Montserrat, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
a { color: inherit; text-decoration: none; }
.vn-home { --ink: #111; --paper: #fff; --soft: #f5f5f5; --line: #e8e8e8; --muted: #777; --warm: #eee9e4; width: 100%; margin: 0; padding: 0 0 42px; overflow: hidden; }
.vn-shipping { min-height: 31px; display: flex; justify-content: center; align-items: center; gap: 5px; background: #111; color: #fff; font-size: 10px; letter-spacing: .025em; }
.vn-shipping span { color: #fff; font-weight: 600; }
.vn-header { max-width: 1440px; min-height: 78px; display: flex; justify-content: space-between; align-items: center; gap: 20px; margin: 0 auto; padding: 0 clamp(22px, 5.5vw, 88px); border-bottom: 1px solid var(--line); }
.vn-wordmark { white-space: nowrap; font-family: VN-Lora, Georgia, serif; font-size: 20px; letter-spacing: .095em; }
.vn-wordmark span { padding: 0 2px; font-family: VN-Montserrat, sans-serif; font-size: 13px; }
.vn-lang-switch { display: flex; align-items: center; gap: 5px; padding: 3px; border: 1px solid var(--line); border-radius: 30px; }
.vn-lang-switch a { min-width: 34px; padding: 7px 9px; border-radius: 24px; color: #777; font-size: 9px; text-align: center; }
.vn-lang-switch a[aria-current="page"] { background: #111; color: #fff; }
.vn-text-link:hover { color: #999; }
.vn-hero { position: relative; min-height: clamp(370px, 37vw, 510px); margin: 0; overflow: hidden; background: #eee9e4; }
.vn-hero__picture, .vn-hero__picture img, .vn-hero__veil { position: absolute; inset: 0; width: 100%; height: 100%; }
.vn-hero__picture img { object-fit: cover; object-position: 78% 48%; }
.vn-hero__veil { background: linear-gradient(90deg, rgba(247,244,241,.98) 0%, rgba(247,244,241,.91) 30%, rgba(247,244,241,.34) 55%, rgba(247,244,241,0) 78%); }
.vn-hero__copy { position: relative; z-index: 1; width: min(48%, 530px); min-height: inherit; display: flex; flex-direction: column; justify-content: center; align-items: flex-start; padding: 54px clamp(25px, 5.5vw, 82px); }
.vn-eyebrow { margin: 0 0 13px; color: #777; font-size: 9px; font-weight: 500; letter-spacing: .19em; line-height: 1.4; }
.vn-hero h1 { margin: 0; font-family: VN-Lora, Georgia, serif; font-size: clamp(42px, 5.3vw, 70px); font-weight: 400; letter-spacing: -.055em; line-height: .98; }
.vn-hero__description { max-width: 330px; margin: 18px 0 0; color: #494541; font-size: 12px; line-height: 1.7; }
.vn-button { min-height: 44px; display: inline-flex; align-items: center; gap: 28px; margin-top: 25px; padding: 0 18px; background: #111; color: #fff; font-size: 10px; font-weight: 600; letter-spacing: .07em; text-transform: uppercase; transition: background .2s; }
.vn-button:hover { background: #3a3a3a; }
.vn-button span { font-size: 15px; font-weight: 400; }
.vn-hero__dots { position: absolute; z-index: 2; right: 22px; bottom: 20px; display: flex; align-items: center; gap: 6px; }
.vn-hero__dots span { width: 6px; height: 6px; border-radius: 50%; background: rgba(17,17,17,.35); }
.vn-hero__dots .is-active { width: 18px; border-radius: 6px; background: #111; }
.vn-section { max-width: 1440px; margin: 0 auto; padding: 45px clamp(22px, 5.5vw, 88px) 32px; }
.vn-showcase { position: relative; max-width: 1440px; margin: 0 auto; padding: 25px clamp(22px, 5.5vw, 88px) 36px; }
.vn-showcase__tabs { display: flex; align-items: center; gap: 24px; width: max-content; max-width: 100%; margin-bottom: 20px; border-bottom: 1px solid var(--line); }
.vn-showcase__radio { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; clip-path: inset(50%); }
.vn-showcase__tabs label { position: relative; padding: 0 0 10px; color: #888; font-size: 15px; cursor: pointer; white-space: nowrap; }
.vn-showcase__tabs input:checked + label { color: #111; font-weight: 600; }
.vn-showcase__tabs input:checked + label::after { position: absolute; right: 0; bottom: -1px; left: 0; height: 1px; background: #111; content: ""; }
.vn-showcase__tabs input:focus-visible + label { outline: 2px solid #111; outline-offset: 4px; }
.vn-showcase__panel { position: relative; display: none; }
.vn-showcase:has(#vn-tab-sale:checked) #vn-panel-sale, .vn-showcase:has(#vn-tab-bestsellers:checked) #vn-panel-bestsellers, .vn-showcase:has(#vn-tab-new:checked) #vn-panel-new { display: block; }
.vn-showcase__panel .vn-products { display: flex; gap: 30px; overflow-x: auto; padding: 0 38px 12px 0; scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
.vn-showcase__panel .vn-products::-webkit-scrollbar { display: none; }
.vn-showcase__panel .vn-product { flex: 0 0 250px; scroll-snap-align: start; }
.vn-showcase__more { position: absolute; z-index: 2; top: 40%; right: 10px; width: 42px; height: 42px; display: grid; place-items: center; border: 0; border-radius: 50%; background: #fff; box-shadow: 0 3px 15px #0000001a; cursor: pointer; transition: transform .2s; }
.vn-showcase__more svg { width: 19px; height: 19px; fill: none; stroke: #111; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
.vn-showcase__more:hover { transform: translateX(3px); }
.vn-product { min-width: 0; }
.vn-showcase .vn-product__media { position: relative; background: #f2f2f2; }
.vn-showcase .vn-product__visual { position: relative; aspect-ratio: 1; display: block; overflow: hidden; background: transparent; }
.vn-showcase .vn-product__visual img { width: 100%; height: 100%; display: block; object-fit: contain; mix-blend-mode: multiply; transition: transform .45s cubic-bezier(.2,.65,.3,1); }
.vn-product:hover .vn-product__visual img { transform: scale(1.035); }
.vn-showcase .vn-product__flags { position: absolute; top: 0; left: 0; display: flex; align-items: stretch; gap: 0; }
.vn-showcase .vn-product__badge, .vn-showcase .vn-product__sale { position: static; display: inline-flex; align-items: center; min-height: 25px; padding: 0 7px; background: #efff00; color: #111; font-size: 10px; font-weight: 700; letter-spacing: 0; }
.vn-showcase .vn-product__badge--sale { order: 2; background: #ff1682; color: #fff; }
.vn-showcase .vn-product__badge--new { background: #efff00; color: #111; }
.vn-showcase .vn-product__sale { order: 1; background: #ff1682; color: #fff; }
.vn-product__favorite { position: absolute; z-index: 1; top: 7px; right: 7px; width: 30px; height: 30px; display: grid; place-items: center; padding: 0; border: 0; background: transparent; color: #aaa; cursor: pointer; }
.vn-product__favorite svg { width: 23px; height: 23px; fill: none; stroke: currentColor; stroke-width: 1.4; stroke-linecap: round; stroke-linejoin: round; }
.vn-product__favorite[aria-pressed="true"] { color: #111; }
.vn-product__favorite[aria-pressed="true"] svg { fill: #111; }
.vn-showcase .vn-product__variants { min-height: 32px; display: flex; align-items: center; gap: 5px; padding: 6px 0 0; }
.vn-showcase .vn-product__variants span { display: inline-flex; min-width: 27px; height: 19px; align-items: center; justify-content: center; padding: 0 6px; border: 1px solid #d6d6d6; border-radius: 10px; color: #333; font-size: 9px; line-height: 1; }
.vn-product__bag { position: absolute; right: -11px; bottom: -10px; width: 36px; height: 36px; display: grid; place-items: center; border-radius: 50%; background: #222; color: #fff; }
.vn-product__bag svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
.vn-showcase .vn-product__info { display: block; padding: 8px 1px 0; }
.vn-showcase .vn-product__brand { margin: 0 0 6px; color: #111; font-size: 9px; font-weight: 600; letter-spacing: .11em; text-transform: uppercase; }
.vn-showcase .vn-product h3 { min-height: 40px; margin: 0; display: -webkit-box; overflow: hidden; font-size: 14px; font-weight: 500; line-height: 1.38; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.vn-showcase .vn-product__price { margin: 9px 0 0; font-size: 16px; font-weight: 600; }
.vn-showcase .vn-product__price del { margin-left: 6px; color: #999; font-size: 12px; font-weight: 400; }
.vn-brand-feature { max-width: 1440px; margin: 10px auto 32px; padding: 0 clamp(22px, 5.5vw, 88px); }
.vn-brand-feature__hero { position: relative; aspect-ratio: 2.65; display: block; overflow: hidden; background: #b9dff1; color: #111; }
.vn-brand-feature__image, .vn-brand-feature__veil { position: absolute; inset: 0; width: 100%; height: 100%; }
.vn-brand-feature__image { display: block; object-fit: cover; object-position: center; }
.vn-brand-feature__veil { background: linear-gradient(90deg, rgba(186,221,239,.22), transparent 68%); }
.vn-brand-feature__copy { position: absolute; z-index: 1; top: 20px; right: 9%; display: flex; flex-direction: column; align-items: flex-end; gap: 9px; text-align: right; }
.vn-brand-feature__eyebrow { font-size: 8px; font-weight: 600; letter-spacing: .15em; }
.vn-brand-feature__title { font-size: clamp(24px, 3.2vw, 42px); font-weight: 600; letter-spacing: -.055em; line-height: 1; }
.vn-brand-feature__link { display: inline-flex; align-items: center; gap: 10px; margin-top: 2px; font-size: 8px; font-weight: 600; letter-spacing: .12em; }
.vn-brand-feature__link b { font-size: 16px; font-weight: 400; }
.vn-brand-feature__tag { position: absolute; z-index: 1; top: 18px; right: 0; padding: 9px 5px; background: rgba(17,17,17,.2); color: #fff; font-size: 7px; font-weight: 600; letter-spacing: .12em; writing-mode: vertical-rl; }
.vn-brand-feature__products { position: relative; margin: -32px 12px 0; }
.vn-brand-feature__rail { display: flex; gap: 14px; overflow-x: auto; padding: 0 28px 15px 0; scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
.vn-brand-feature__rail::-webkit-scrollbar { display: none; }
.vn-brand-feature__rail .vn-product { flex: 0 0 calc((100% - 42px) / 4); min-width: 0; margin-bottom: 12px; scroll-snap-align: start; }
.vn-brand-feature__rail .vn-product h3 { display: -webkit-box; overflow: hidden; font-size: 13px; line-height: 1.4; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.vn-brand-feature__next { position: absolute; z-index: 2; top: 34%; right: -8px; width: 38px; height: 38px; display: grid; place-items: center; border: 0; border-radius: 50%; background: #fff; box-shadow: 0 3px 15px #0002; cursor: pointer; }
.vn-brand-feature__next svg { width: 18px; height: 18px; fill: none; stroke: #111; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
.vn-brand-feature__next:disabled { opacity: 0; pointer-events: none; }
.vn-gift-certificate { padding: 48px 0 56px; }
.vn-gift-certificate__container { width: min(calc(100% - 36px), 520px); display: flex; flex-direction: column; align-items: center; gap: 68px; margin: 0 auto; }
.vn-gift-certificate__art { position: relative; width: calc(100% - 42px); max-width: 460px; aspect-ratio: .82; margin-top: 36px; overflow: visible; background: #f5f5f5; }
.vn-gift-certificate__image { display: block; width: 100%; height: 100%; max-width: 100%; object-fit: cover; }
.vn-gift-certificate__title { position: absolute; z-index: 1; top: -39px; left: 0; margin: 0; font-family: VN-Montserrat, Arial, sans-serif; font-size: clamp(24px, 6vw, 34px); font-weight: 500; letter-spacing: -.025em; line-height: 1.05; text-transform: uppercase; }
.vn-gift-certificate__button { width: 100%; min-height: 48px; display: flex; align-items: center; justify-content: center; border: 1px solid #171717; color: #171717; font-size: 13px; font-weight: 500; text-transform: uppercase; transition: color 180ms ease, background-color 180ms ease; }
.vn-gift-certificate__button:hover { background: #171717; color: #fff; }
.vn-gift-certificate__button:focus-visible { outline: 2px solid #171717; outline-offset: 4px; }
.vn-store-feature { padding: 30px 0 56px; }
.vn-store-feature__container { width: min(calc(100% - 36px), 520px); display: flex; flex-direction: column; align-items: center; gap: 68px; margin: 0 auto; }
.vn-store-feature__art { position: relative; width: calc(100% - 42px); max-width: 460px; aspect-ratio: .82; margin-top: 36px; overflow: visible; background: #f5f5f5; }
.vn-store-feature__image { display: block; width: 100%; height: 100%; max-width: 100%; object-fit: cover; }
.vn-store-feature__title { position: absolute; z-index: 1; top: -39px; left: 0; margin: 0; font-family: VN-Montserrat, Arial, sans-serif; font-size: clamp(24px, 6vw, 34px); font-weight: 500; letter-spacing: -.025em; line-height: 1.05; text-transform: uppercase; }
.vn-store-feature__button { width: 100%; min-height: 48px; display: flex; align-items: center; justify-content: center; border: 1px solid #171717; color: #171717; font-size: 13px; font-weight: 500; text-transform: uppercase; transition: color 180ms ease, background-color 180ms ease; }
.vn-store-feature__button:hover { background: #171717; color: #fff; }
.vn-store-feature__button:focus-visible { outline: 2px solid #171717; outline-offset: 4px; }
.vn-categories { max-width: 1264px; margin: 28px auto 0; padding: 0 0 20px; }
.vn-categories h2 { margin: 0 0 18px; font-size: 21px; font-weight: 500; letter-spacing: -.035em; }
.vn-categories__track { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
.vn-category-card { position: relative; aspect-ratio: .92; display: block; overflow: hidden; border-radius: 12px; background: #f3f1ef; color: #fff; }
.vn-category-card::after { position: absolute; inset: 35% 0 0; background: linear-gradient(180deg, transparent, rgba(0,0,0,.48)); content: ""; }
.vn-category-card img { width: 100%; height: 100%; display: block; object-fit: cover; transition: transform .45s ease; }
.vn-category-card:hover img { transform: scale(1.035); }
.vn-category-card__label { position: absolute; z-index: 1; right: 12px; bottom: 12px; left: 12px; display: flex; flex-direction: column; gap: 3px; padding: 11px 13px; border-radius: 0; background: rgba(255,255,255,.94); color: #111; text-align: center; }
.vn-category-card__label > span:first-child { color: #777; font-size: 8px; letter-spacing: .14em; text-transform: uppercase; }
.vn-category-card__label > span:last-child { font-size: 11px; font-weight: 500; }
.vn-category-card__label b { margin-left: 5px; font-size: 13px; font-weight: 400; }
.vn-categories__more { width: max-content; min-height: 42px; display: flex; align-items: center; justify-content: center; gap: 17px; margin: 17px auto 0; padding: 0 21px; border: 1px solid #111; border-radius: 0; font-size: 10px; }
.vn-benefits { max-width: 1264px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin: 0 auto; padding: 34px 0; border-bottom: 1px solid var(--line); }
.vn-benefits > div { display: flex; align-items: center; gap: 14px; }
.vn-benefits span { color: #999; font-family: VN-Lora, Georgia, serif; font-size: 20px; }
.vn-benefits p { margin: 0; font-size: 10px; line-height: 1.5; }
.vn-footer { max-width: 1264px; min-height: 65px; display: flex; justify-content: space-between; align-items: center; gap: 14px; margin: 0 auto; color: #777; font-size: 9px; }
.vn-footer .vn-wordmark { color: #111; font-size: 14px; }
.vn-footer > a:last-child { color: #111; }
.vn-mobile-nav { display: none; }
@media (max-width: 900px) {
  .vn-benefits, .vn-footer { margin-right: 24px; margin-left: 24px; }
}
@media (max-width: 700px) {
  .vn-home { padding: 0 0 calc(74px + env(safe-area-inset-bottom)); }
  .vn-shipping { min-height: 27px; font-size: 9px; }
  .vn-header { min-height: 55px; padding: 0 16px; border-bottom: 0; }
  .vn-wordmark { font-size: 16px; }
  .vn-lang-switch { gap: 2px; }
  .vn-lang-switch a { min-width: 30px; padding: 6px 7px; font-size: 8px; }
  .vn-hero { min-height: 410px; margin: 0; }
  .vn-hero__picture img { object-position: 78% center; }
  .vn-hero__veil { background: linear-gradient(0deg, rgba(247,244,241,.98) 0%, rgba(247,244,241,.88) 32%, rgba(247,244,241,.08) 72%, rgba(247,244,241,0) 100%); }
  .vn-hero__copy { width: 100%; min-height: 410px; justify-content: flex-end; padding: 30px 22px 34px; }
  .vn-hero .vn-eyebrow { margin-bottom: 8px; font-size: 8px; }
  .vn-hero h1 { font-size: clamp(38px, 11vw, 49px); }
  .vn-hero__description { max-width: 275px; margin-top: 11px; font-size: 10px; line-height: 1.6; }
  .vn-button { min-height: 40px; margin-top: 15px; font-size: 9px; }
  .vn-hero__dots { right: 13px; bottom: 14px; }
  .vn-section { padding: 29px 14px 19px; }
  .vn-showcase { padding: 18px 18px 27px; }
  .vn-showcase__tabs { gap: 23px; margin-bottom: 18px; }
  .vn-showcase__tabs label { padding-bottom: 10px; font-size: 15px; }
  .vn-showcase__panel .vn-products { gap: 30px; padding-right: 46px; }
  .vn-showcase__panel .vn-product { flex-basis: 58%; }
  .vn-showcase__more { top: 37%; right: 2px; width: 38px; height: 38px; }
  .vn-section__heading { margin-bottom: 13px; }
  .vn-section__heading .vn-eyebrow { margin-bottom: 4px; font-size: 8px; }
  .vn-section h2 { font-size: 29px; }
  .vn-text-link { font-size: 9px; }
  .vn-products { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 19px 10px; }
  .vn-product__visual { aspect-ratio: 1 / 1.12; }
  .vn-product__badge { top: 7px; left: 7px; padding: 5px 6px; font-size: 7px; }
  .vn-product__info { padding-top: 9px; }
  .vn-showcase .vn-product__brand { margin-bottom: 5px; font-size: 8px; }
  .vn-showcase .vn-product h3 { min-height: 38px; font-size: 12px; }
  .vn-showcase .vn-product__price { margin-top: 7px; font-size: 14px; }
  .vn-showcase .vn-product__price del { font-size: 10px; }
  .vn-showcase .vn-product__badge, .vn-showcase .vn-product__sale { min-height: 22px; padding: 0 6px; font-size: 9px; }
  .vn-showcase .vn-product__variants { min-height: 29px; }
  .vn-showcase .vn-product__favorite { top: 4px; right: 4px; }
  .vn-brand-feature { margin: 0 0 19px; padding: 0; }
  .vn-brand-feature__hero { aspect-ratio: 1.18; }
  .vn-brand-feature__image { object-position: center; }
  .vn-brand-feature__veil { background: linear-gradient(180deg, rgba(185,221,240,.04), rgba(185,221,240,0) 50%, rgba(0,0,0,.05)); }
  .vn-brand-feature__copy { top: 14px; right: 15px; gap: 7px; }
  .vn-brand-feature__eyebrow { font-size: 7px; }
  .vn-brand-feature__title { font-size: clamp(23px, 7vw, 31px); }
  .vn-brand-feature__link { gap: 8px; font-size: 7px; }
  .vn-brand-feature__tag { top: 14px; font-size: 6px; }
  .vn-brand-feature__products { margin: -31px 15px 0; }
  .vn-brand-feature__rail { gap: 10px; padding-right: 32px; }
  .vn-brand-feature__rail .vn-product { flex-basis: calc((100% - 10px) / 2); }
  .vn-brand-feature__next { top: 29%; right: -7px; width: 34px; height: 34px; }
  .vn-brand-feature__rail .vn-product__visual { aspect-ratio: 1 / 1.12; }
  .vn-brand-feature__rail .vn-product__brand { font-size: 7px; }
  .vn-brand-feature__rail .vn-product h3 { min-height: 0; font-size: 10px; line-height: 1.4; }
  .vn-brand-feature__rail .vn-product__price { font-size: 10px; }
  .vn-gift-certificate { padding: 38px 0 46px; }
  .vn-gift-certificate__container { width: min(calc(100% - 36px), 520px); gap: 68px; }
  .vn-gift-certificate__art { width: calc(100% - 42px); aspect-ratio: .82; margin-top: 36px; }
  .vn-gift-certificate__title { top: -39px; font-size: clamp(24px, 7vw, 32px); }
  .vn-store-feature { padding: 22px 0 42px; }
  .vn-store-feature__container { width: min(calc(100% - 36px), 520px); gap: 68px; }
  .vn-store-feature__art { width: calc(100% - 42px); aspect-ratio: .82; margin-top: 36px; }
  .vn-store-feature__title { top: -39px; font-size: clamp(24px, 7vw, 32px); }
  .vn-categories { margin: 17px 0 0; padding: 0 0 13px; }
  .vn-categories h2 { margin: 0 18px 14px; font-size: 19px; }
  .vn-categories__track { display: flex; gap: 12px; overflow-x: auto; padding: 0 18px 3px; scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
  .vn-categories__track::-webkit-scrollbar { display: none; }
  .vn-category-card { flex: 0 0 76%; aspect-ratio: .8; border-radius: 0; scroll-snap-align: start; }
  .vn-category-card__label { right: 11px; bottom: 11px; left: 11px; padding: 10px 12px; }
  .vn-category-card__label > span:first-child { font-size: 7px; }
  .vn-category-card__label > span:last-child { font-size: 11px; }
  .vn-categories__more { min-height: 40px; margin-top: 14px; font-size: 10px; }
  .vn-benefits { margin: 0 14px; padding: 22px 0; gap: 5px; }
  .vn-benefits > div { align-items: flex-start; gap: 6px; }
  .vn-benefits span { font-size: 14px; }
  .vn-benefits p { font-size: 8px; }
  .vn-footer { padding: 0 15px; font-size: 8px; }
  .vn-footer .vn-wordmark { font-size: 12px; }
  .vn-mobile-nav { position: fixed; z-index: 5; right: 0; bottom: 0; left: 0; min-height: 70px; display: grid; grid-template-columns: repeat(5, 1fr); align-items: center; padding: 7px 8px calc(7px + env(safe-area-inset-bottom)); border-top: 1px solid #eee; background: rgba(255,255,255,.98); box-shadow: 0 -3px 18px #00000008; }
  .vn-mobile-nav a { display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 3px; min-height: 50px; margin: 0 3px; border-radius: 12px; color: #111; text-decoration: none; }
  .vn-mobile-nav a[aria-current="page"] { background: #f5f5f5; font-weight: 600; }
  .vn-mobile-nav svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 1.45; stroke-linecap: round; stroke-linejoin: round; }
  .vn-mobile-nav span { font-size: 9px; line-height: 1; white-space: nowrap; }
}

/* Design Lab slider card experiment */
.vn-showcase .vn-product { position: relative; }
.vn-showcase .vn-product__media { position: relative; background: #f2f2f2; }
.vn-showcase .vn-product__visual { aspect-ratio: 1; overflow: hidden; background: transparent; }
.vn-showcase .vn-product__visual img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }
.vn-showcase .vn-product__flags { position: absolute; top: 0; left: 0; display: flex; align-items: stretch; }
.vn-showcase .vn-product__badge, .vn-showcase .vn-product__sale { position: static; display: inline-flex; align-items: center; min-height: 25px; padding: 0 7px; background: #efff00; color: #111; font-size: 10px; font-weight: 700; letter-spacing: 0; }
.vn-showcase .vn-product__badge--sale { order: 2; background: #ff1682; color: #fff; }
.vn-showcase .vn-product__badge--new { background: #efff00; color: #111; }
.vn-showcase .vn-product__sale { order: 1; background: #ff1682; color: #fff; }
.vn-showcase .vn-product__favorite { position: absolute; z-index: 1; top: 7px; right: 7px; width: 30px; height: 30px; display: grid; place-items: center; padding: 0; border: 0; background: transparent; color: #aaa; cursor: pointer; }
.vn-showcase .vn-product__favorite svg { width: 23px; height: 23px; fill: none; stroke: currentColor; stroke-width: 1.4; stroke-linecap: round; stroke-linejoin: round; }
.vn-showcase .vn-product__favorite[aria-pressed="true"] { color: #111; }
.vn-showcase .vn-product__favorite[aria-pressed="true"] svg { fill: #111; }
.vn-showcase .vn-product__variants { min-height: 32px; display: flex; align-items: center; gap: 5px; padding: 6px 0 0; }
.vn-showcase .vn-product__variants span { display: inline-flex; min-width: 27px; height: 19px; align-items: center; justify-content: center; padding: 0 6px; border: 1px solid #d6d6d6; border-radius: 10px; color: #333; font-size: 9px; line-height: 1; }
.vn-showcase .vn-product__info { padding: 8px 1px 0; }
.vn-showcase .vn-product__brand { margin: 0 0 6px; color: #111; font-size: 9px; font-weight: 600; letter-spacing: .11em; text-transform: uppercase; }
.vn-showcase .vn-product h3 { min-height: 40px; display: -webkit-box; overflow: hidden; font-size: 14px; font-weight: 500; line-height: 1.38; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.vn-showcase .vn-product__price { margin: 9px 0 0; font-size: 16px; font-weight: 600; }
.vn-showcase .vn-product__price del { margin-left: 6px; color: #999; font-size: 12px; font-weight: 400; }
@media (max-width: 700px) {
  .vn-showcase .vn-product__badge, .vn-showcase .vn-product__sale { min-height: 22px; padding: 0 6px; font-size: 9px; }
  .vn-showcase .vn-product__variants { min-height: 29px; }
  .vn-showcase .vn-product__favorite { top: 4px; right: 4px; }
  .vn-showcase .vn-product__brand { margin-bottom: 5px; font-size: 8px; }
  .vn-showcase .vn-product h3 { min-height: 38px; font-size: 12px; }
  .vn-showcase .vn-product__price { margin-top: 7px; font-size: 14px; }
  .vn-showcase .vn-product__price del { font-size: 10px; }
}
CSS;
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Design Lab · Vizaje-Nica</title>
    <link rel="stylesheet" href="/app/css/dev/design-lab.css?v=20261002-viewport-state">
    <script src="/app/js/dev/design-lab.js?v=20261002-viewport-state" defer></script>
</head>
<body class="vn-design-lab">
    <header class="lab-topbar">
        <a class="lab-wordmark" href="#" aria-label="Design Lab">VN<span> / </span>DESIGN LAB</a>
        <div class="lab-topbar__right"><span class="lab-local-indicator"><i></i> Только локальный просмотр</span><a class="lab-page-link" href="/dev/design-lab/catalog">Каталог ↗</a><button type="button" class="lab-button lab-button--quiet" data-reset>Сбросить</button></div>
    </header>

    <main class="lab-layout">
        <section class="lab-workspace" aria-label="Редактор и предпросмотр">
            <div class="lab-workspace__heading">
                <div><h1>Дизайн-лаборатория</h1><p>Черновики изолированы от сайта и сохраняются только в этом браузере.</p></div>
                <div class="lab-actions"><span class="lab-save-state" data-save-state>Стартовый макет</span><button type="button" class="lab-button" data-restore>Вернуть стартовый макет</button></div>
            </div>

            <div class="lab-preview-bar">
                <div class="lab-preview-bar__label"><span class="lab-live-dot"></span>Предпросмотр</div>
                <div class="lab-preview-bar__controls">
                    <div class="lab-viewport-switch" role="group" aria-label="Размер предпросмотра">
                        <button type="button" data-viewport="desktop" aria-pressed="true">Компьютер</button>
                        <button type="button" data-viewport="mobile" aria-pressed="false">Телефон</button>
                    </div>
                    <button type="button" class="lab-fullscreen-button" data-fullscreen aria-pressed="false">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M7 3H3v4M13 3h4v4M17 13v4h-4M3 13v4h4"/></svg>
                        <span data-fullscreen-label>На весь экран</span>
                    </button>
                </div>
            </div>

            <div class="lab-stage" data-stage data-viewport="desktop">
                <iframe title="Изолированный предпросмотр дизайна" sandbox="allow-scripts" data-preview></iframe>
            </div>
        </section>

        <aside class="lab-editor" aria-label="HTML и CSS редакторы">
            <div class="lab-editor__heading"><div><h2>Редактор</h2><p>Меняйте HTML и CSS — предпросмотр обновляется сразу.</p></div><span class="lab-editor__language">HTML + CSS</span></div>
            <label class="lab-code-label" for="lab-html">HTML</label>
            <textarea id="lab-html" class="lab-code" spellcheck="false" data-html-editor><?= htmlspecialchars($starterHtml, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
            <label class="lab-code-label" for="lab-css">CSS</label>
            <textarea id="lab-css" class="lab-code lab-code--css" spellcheck="false" data-css-editor><?= htmlspecialchars($starterCss, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
            <p class="lab-editor__note">Предпросмотр работает в изолированном iframe: код не меняет стили или данные основного сайта.</p>
        </aside>
    </main>
</body>
</html>
