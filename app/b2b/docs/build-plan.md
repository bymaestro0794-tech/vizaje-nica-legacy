# План сборки по секциям

Файл Figma: https://www.figma.com/design/8OfRqkuUPINL8DAySKA0ei
Ссылка на фрейм: добавить `/?node-id=<id с дефисом>`, например `1:26` → `node-id=1-26`.
Размеры ниже взяты из макета: desktop 1440 px, mobile 390 px. Если в макете что-то выглядит неаккуратно, исправить и сообщить.

## Сводка секций

| # | Секция | Desktop | Mobile | Модалка |
|---|---|---|---|---|
| 1 | Nav | 1:27 (внутри 1:26) | 43:504 | нет |
| 2 | Hero | 1:42 | 43:514 | нет |
| 3 | Our business | 1:47 | 43:525 | да (3 шт.) |
| 4 | Numbers | 1:59 | 43:552 | нет |
| 5 | Brands we carry | 29:221 | 43:828 | да (4 шт.) |
| 6 | Слайдер магазинов | 9:169 (состояния 32:248, 32:260, 32:272, 32:284, 32:296) | 43:1026 | нет |
| 7 | What we offer | 1:78 | 43:1149 | нет |
| 8 | FAQ | 10:173 | 10:208 | нет |
| 9 | Final CTA | 1:87 | 43:1196 | нет |
| 10 | Footer | 1:91 | 43:1201 | нет |

Полная мобильная страница: 43:503. Модалки desktop: 18:250 (Retail), 18:262 (Distribution), 18:274 (E-com), 29:421 (Fragrances), 29:455 (Niche), 29:489 (Make-up), 29:524 (Skin care). Модалки mobile: `Mobile Modal / …` на странице Figma, x=7700.
Секция «Marketing 360» в проект не входит (не подошла по стилю).

## Картинки в проекте

| Где используется | Файл |
|---|---|
| Hero, пакеты | `img/hero/hero-bags.png` |
| Hero, коробки | `img/hero/hero-boxes.png` |
| Our business, Retail | `img/our-bussines/store.png` |
| Our business, Distribution | `img/our-bussines/warehouse.jpg` |
| Our business, E-com | `img/our-bussines/web.png` |
| Картинки модалок | `img/our-bussines/modal/` (уточнить имена файлов) |
| What we offer, визуал (3D пазл) | `img/360.png` |
| Иконки | `img/plus.svg`, `img/close.svg`, `img/arrow-left.svg`, `img/arrow-right.svg`, `img/arrow-down.svg` |

Папка называется `our-bussines` (опечатка). Либо переименовать в `our-business` и поправить пути, либо оставить как есть и не забывать.
Ещё нужно добавить: `img/logo.svg` (логотип), `img/stores/*.jpg` (6 фото магазинов), при желании `img/brands/*` (логотипы брендов).

## 1. Nav
- Высота 56, паддинги по бокам 40. Слева логотип, по центру ссылки Business, Company, Brands, Stores, Contact (12 px), справа синяя ссылка «Become a partner» (12 px, `--accent-blue`).
- Фон белый, при скролле блюр (см. motion-spec). Mobile: ссылки скрыты, остаётся логотип и «Become a partner», паддинг 20. Меню-гамбургер в макете не нарисовано, спросить перед добавлением.

## 2. Hero
- Высота 780, фон `--hero-gradient`. Центральный блок 760 px шириной: eyebrow «B2B / MOLDOVA» (15, semibold, secondary, uppercase), h1 «Beauty retail. Distribution. / One local partner.» (56, weight 600, line-height 108%), описание (19, secondary, ширина ~620), тёмная кнопка-таблетка «Business inquiry →» (`--text-primary` фон, белый текст 17), tagline «Beauty brings people together.» (15).
- Картинки поверх: `hero-bags.png` слева внизу (около 311 px), `hero-boxes.png` справа сверху (около 370 px). Позиции абсолютные, секция `overflow: hidden`.
- Mobile: высота 780, h1 40, коробки сверху справа (190), пакеты внизу слева (230).

## 3. Our business
- Фон `--bg-section`, заголовок «Our business» (48/32). Три карточки в ряд, gap 20, каждая примерно 451×540, `border-radius: 28px`, фон белый, `overflow: hidden`.
- Внутри карточки: картинка сверху (занимает всё свободное место), снизу блок текста (padding 24 28, справа 88 для кнопки): подпись категории (15, semibold, secondary) и заголовок (28, semibold, line-height 110%). Кнопка «+» 40 px, круглая, `--accent-blue`, абсолютно в правом нижнем углу (отступ 24).
- Вся карточка это `<button>` (открывает модалку). Mobile: карточки друг под другом, высота 470, заголовок 24.
- Контент:
  - Retail: «Moldova’s #1 beauty retail chain.»
  - Distribution: «One partner. Full market coverage.»
  - E-com: «Over 90% of our assortment is online.»

## Общая модалка (Our business и Brands)
- Desktop: чёрная панель 950×880, `border-radius: 28px`, паддинг 56 48, по центру, затемнение rgba(0,0,0,.6). Mobile: bottom sheet 350×780 (или на всю ширину), выезжает снизу, закрытие свайпом вниз.
- Внутри: подпись категории (15, semibold, белая), заголовок (48/32), серая панель `#1d1d1f` (`--text-primary`), радиус 28, паддинг 40 48, в ней строки «Жирный лид. обычный серый текст» (19, лид белый, остальное `rgba(255,255,255,.6)`), ниже слот под картинку (420 px высотой, обрезается снизу). Кнопка закрытия 36 px в правом верхнем углу (отступ 28).
- Реализовать один раз (`js/lib/modal.js`), контент подставлять из data-атрибутов или объекта. Использовать `<dialog>` для фокус-ловушки, закрытие Esc и кликом по затемнению, при открытии блокировать скролл (`lenis.stop()`).
- Контент модалок Our business:
  - Retail: «Beauty for everyone, closer to you.» / Our stores. 12 doors in Chisinau, with one new door opening by mid 2026. / Partners. 4 VN franchisee partners and 3 exclusive regional partners. / Beauty advisors. A BA and training team of 60+ people.
  - Distribution: «Stronger brands. Wider reach.» / Network. A strong distribution network with full market coverage. / Logistics. Own warehouse and logistics, with a fully automated IT system and ordering process. / Team. A dedicated marketing and sales team.
  - E-com: «Beauty anywhere, closer to you.» / Assortment. Over 90% of our assortment is available online. / Delivery. Same-day delivery and shipping country-wide. / Apps & CRM. Our own shopping app, easy and effective CRM, and an omni-channel approach.
- Контент модалок Brands: заголовки «Iconic houses, one partner.», «Rare and collectible houses.», «Professional make-up and colour.», «Skin care from trusted names.», в панели список брендов в 3 колонки (из `data/brands.js`) и строка «Coming soon: …» где нужно.

## 4. Numbers («Vizaje-Nica in numbers»)
- Фон белый, заголовок 48/32. Bento 2×2: верхний ряд широкая (~920) + узкая (~446), нижний узкая (~448) + широкая (~920), gap 20, высота карточек 600 (mobile 280), фон `--bg-section`, радиус 28.
- В карточке: число сверху (160 px / 64 mobile, weight 600, letter-spacing -4%), подпись прижата к низу (21 px, secondary). В макете часть цифр цветные (12 красная, 100+ синяя), свериться со скриншотом.
- Контент: 35 / Years of market experience. A second-generation family company, started in 1989. · 12 / Stores in Chisinau, with a new door opening in 2026. · 100+ / Global brands in our retail portfolio. · 115,000+ / Loyal customers in our client base. Over 90% of our assortment is also available online.

## 5. Brands we carry
- Фон `--bg-section`, заголовок «Brands we carry». Bento 2×2 (широкая ~924 + узкая 448, ниже зеркально), высота карточек 600 (mobile 540), фон белый, радиус 28.
- Верх карточки: категория (15, secondary) и заголовок (28) вида «Chanel, Dior, Guerlain and more.», справа снизу «+».
- Ниже сетка плиток: квадраты 128 px, радиус 32, фон `--bg-section`, название бренда (13, semibold, uppercase, по центру). Шаг 144, gap 16. Сетка сдвинута на -40 px влево и обрезается краями карточки (как иконки в карточке «Apps» на apple.com). В широких карточках 7 колонок, в узких 4.
- Данные и логотипы: `data/brands.js`. Пока плитки с названиями, потом логотипы через `getLogoUrl(brand)`.

## 6. Слайдер магазинов
- Фон чёрный, отступы сверху 35 и снизу 60. Карточка магазина 1000×540, радиус 28, слот под фото (пока `#1d1d1f`), бейдж «Chisinau» слева сверху (отступ 32), кнопка повтора видео справа внизу (36 px, если будет видео), под фото подпись (21 px): жирное название магазина и серое описание, ниже «Visit the store ›».
- Активная карточка по центру (левый отступ трека 220), соседняя выглядывает справа, gap 20. Стрелки 36 px справа под слайдером (левая на первом слайде тусклая).
- Магазины: Malldova, Port Mall, NR1, Sun City 1, Unic, Soiuz. Подпись «One of our 12 beauty stores in Chisinau.»
- Mobile: карточка 310, медиа 210, стрелок нет (свайп со scroll-snap).

## 7. What we offer
- Фон `--bg-section`. Шапка по центру: eyebrow «WHAT WE OFFER» (15, letter-spacing 10%), h1 «One partner. / The whole market.» (96/44, weight 600, letter-spacing -3%, line-height 100%), описание «From import and logistics to nationwide retail distribution.»
- Карточка 1280×560, белая, радиус 32, рамка 1px `--border-default`, слева список (ширина 768, паддинг 56 64): «Everything in one place» (40) и 4 строки с номерами 01–04 и разделителями (360° market coverage / One point of contact / Import & logistics / Distribution to key retailers). Справа визуал `img/360.png` (3D пазл), фон блока серый градиент.
- Ниже блок «Built for long-term growth.» (56 / 36 mobile) и три колонки с верхней границей: Visibility («Maximum visibility and prestige image.»), Stability («Financially stable and growing local partner.»), Partnership («Long-term partnership.»). Заголовки колонок 36, описание 19 secondary.
- Mobile: список и визуал друг под другом, принципы друг под другом.

## 8. FAQ
- Две колонки: слева заголовок «Frequently asked questions» (48/32), подзаголовок «Answers for brands considering a partnership with Vizaje-Nica.», ссылка «Contact us ›». Справа аккордеон шириной ~800, вопросы 24 (mobile 19), «+» справа, первый вопрос раскрыт, разделитель 1px сверху у каждого.
- Вопросы и ответы: см. в Figma (карточки в 10:173). Семь вопросов: What is Vizaje-Nica? · What do you offer to brands? · Which brands do you work with? · How big is your retail network? · Do you handle import and logistics? · How do you support brands in marketing? · How do we start working together?
- Сделать на `<details>` или кнопках с `aria-expanded`. Ответ седьмого вопроса заглушка.

## 9. Final CTA
- Белый фон, центр: «Ready to become a partner?» (64/40), описание «Tell us about your brand and we will get back to you to discuss the next steps.», тёмная кнопка «Business inquiry →». Паддинг 140 (mobile 72).

## 10. Footer
- Фон `--bg-section`. Слева логотип и «Beauty brings people together.», справа две колонки (COMPANY: Business, Company, Brands, Stores; CONTACT: 32 Puskin str, Chisinau, Moldova · @vizaje_nica · www.vizaje-nica.com). Внизу линия и строка «Copyright © 2026 Vizaje-Nica. All rights reserved.» / «People · Beauty · Progress». Mobile: колонки друг под другом.
