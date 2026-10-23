# Legacy header + footer: frontend specification for `vz-next`

Источник: `application/views/layouts/pages/header.php`,
`application/views/layouts/pages/footer.php`,
`app/css/style/01-base.css`, `app/css/style/03-header.css`,
`app/css/style/04-footer.css`, `app/js/components/header.js`.

## 1. Общие правила

- Основной шрифт: **Geist**, файл `app/fonts/Geist-Variable.woff2`, variable
  weight `100–900`. `body` по умолчанию `16px`, цвет `#000`.
- Базовый контейнер legacy: `width: 100%`, `max-width: 1670px`,
  `margin: 0 auto`, горизонтальный padding `20px`.
- Header — fixed, `z-index: 999900`; контент страницы резервирует под него
  `--site-header-offset`.
- Desktop header обычно имеет высоту `136px`: announcement `30px` + main row
  `62px` + navigation `44px`.
- Mobile breakpoint — `991px` и ниже. Mobile header `64px`, нижняя tabbar
  `72px` плюс safe-area inset.
- Основная палитра: белый `#fff`, текст `#111`, граница `#eaeaea`, muted
  `rgba(17,17,17,.48)`, footer bottom `#f6f6f8`, active mobile `#ac8e7a`.

## 2. Desktop header

### Announcement strip

- Высота `30px`, overflow hidden, белый текст `11px`, line-height `1.4`.
- Фон — голубой многослойный gradient с лёгкой noise-текстурой.
- Если backend передал товары со скидкой, показывается rotating product slide:
  название, процент скидки, кнопка `Перейти` и стрелки предыдущий/следующий.
  При отсутствии товаров fallback: `Бесплатная доставка от 500 MDL`.
- Для авторизованного клиента тот же strip может переключаться на приглашение
  рулетки и countdown.
- На scroll вниз strip скрывается (`height: 0`), для roulette invite может
  оставаться видимым.

### Main row

- Grid из трёх колонок:
  `minmax(240px, 1fr) auto minmax(240px, 1fr)`.
- Слева language switcher RU/RO.
- В центре logo-контейнер `206x50`, padding `3px 8px`; обычный logo отображается
  шириной контейнера, исходные image dimensions — `240x52`. На scroll ширина
  контейнера уменьшается до `184px`.
- Справа actions: search, wishlist, account, cart. Каждая action-кнопка
  `44x44`, gap `2px`, icon image `21x21`.
- Badge: чёрный круг/капсула, минимум `17x17`, белый текст `9px`, border
  `2px solid #fff`; показывает count wishlist/cart только если count не равен 0.
- Языковой переключатель — glass segmented control: padding `3px`, gap `2px`,
  radius `999px`, item min-width `36px`, height `30px`, padding `0 9px`,
  font `12px`; активный item `#111` с белым текстом.
- Account для гостя открывает login/register flow. Для авторизованного клиента
  открывает dropdown `344px` шириной, radius `18px`, blur `20px` и shadow.
  Dropdown содержит avatar с первой буквой имени, имя/email, ссылки на кабинет,
  заказы, избранное, бонусы, рулетку, сертификаты/прочие пункты и logout.

### Desktop navigation

- Высота `44px`, белый фон, нижняя border `1px solid #eaeaea`.
- Пункты центрированы в flex-list, gap `clamp(18px, 2.1vw, 36px)`.
- Обычный link: `14px`, weight `400`, цвет `rgba(17,17,17,.58)`.
- Hover/active: `15px`, weight `500`, цвет `#111`; снизу появляется underline
  высотой `1px`.
- Состав пунктов не хардкодится: категории и direct menu items приходят из
  backend menu/category data. Нужны отдельные RU/RO URL и active state.
- Категории с children открывают mega-menu. Mega-menu — absolute под nav,
  белый, border-top, shadow `0 28px 60px rgba(17,17,17,.1)`, контент с padding
  top `42px` / bottom `48px`, grid intro + columns. Заголовок категории
  `clamp(28px, 2.5vw, 42px)`, links `13px`, заголовки колонок `15px`.
- Открытие, смена категории и stagger-анимация управляются **GSAP** в
  `app/js/components/header.js`; не нужно заменять это случайными CSS
  transition, если сохраняется legacy interaction.

## 3. Mobile header and navigation

- На `991px` и ниже desktop header скрывается, появляется mobile header высотой
  `64px`, padding `0 12px`, белый полупрозрачный фон и blur `14px`.
- Логотип ограничен шириной `min(164px, calc(100% - 108px))`, max-height `36px`.
- Нижняя fixed tabbar: 5 равных колонок, padding `8px 12px`, border-top
  `1px solid #f2f2f2`, background white. Порядок: catalog, wishlist, home/brand,
  profile, cart. В исходнике label markup есть, но CSS их скрывает; видимы
  прежде всего иконки.
- Tabbar icon box `29x29`, brand `33x33`; active color `#ac8e7a`.
- В текущем legacy есть важная особенность: пункт «Каталог» содержит inline
  `lucide-layout-grid`, но CSS скрывает inline SVG и фактически задаёт ему
  mask `mobile-tabbar-search.svg`. Это нужно либо сохранить для pixel parity,
  либо отдельно исправить как осознанное изменение; нельзя случайно решить,
  что layout-grid уже реально отображается.
- Mobile fullscreen menu: fixed inset `0`, `z-index: 999950`, белый фон. Header
  меню `64px`, grid `minmax(96px,1fr) 52px minmax(96px,1fr)`, action `46x50`.
- Root screen имеет padding `0 20px 40px`; вложенные catalog screens имеют
  уровни 1–3, back/close, title, язык, account block, categories и secondary
  links. Account block min-height `66px`; language row min-height `56px`.
- Menu, search overlay, escape, focus restore, body lock и scroll behavior
  реализуются JS-модулем header, а не только CSS.

## 4. Footer

### Main footer

- Белый фон, верхняя border `1px solid #e8e8e8`, padding `64px 0 52px`.
- Content grid: brand/app column `minmax(210px,1.1fr)` + 3 columns
  `minmax(160px,1fr)`, column gap `72px`, row gap `42px`.
- Brand column:
  - QR card border `1px solid #dedede`, radius `2px`, padding `14px`, image
    `150x150`, подпись `12px`, max-width `180px`;
  - hotline label `11px`, phone `18px`, weight `600`;
  - Telegram support: inline Telegram SVG, подпись и handle.
- Heading footer columns: `16px`, weight `500`, line-height `1.3`.
- Link list: margin-top `18px`, gap `12px`; links `14px`, line-height `1.42`,
  color `#574a43`; hover darkens and draws underline.
- Footer menu, certificate link, feedback и cookie settings — данные/ссылки из
  backend, не dummy `#` links.

### Bottom footer

- Background `#f6f6f8`, top border `#ececef`, padding `18px 0 14px`.
- Grid `minmax(120px,1fr) auto minmax(180px,1fr)`, gap `28px`:
  social слева, app buttons по центру, payments справа.
- Social item `24x24`, image `21x21`, gap `16px`.
- Store button `158x47`, padding `5px 13px`, border `#cfcfd2`, radius `12px`;
  app icon `24x24`; small label `7px`, app name `16px`.
- Payment assets и размеры: Visa `56x20`, Mastercard `32x32`, Google Pay
  `44x40`, Apple Pay `44x38`.
- Legal row ниже с border-top, gap `28px`, text `11px`, цвет `#817670`.

## 5. Responsive footer

- До `1100px`: main grid становится 3 колонки, brand занимает две строки.
- До `767px`: 2 колонки; brand на всю ширину; bottom grid становится одной
  колонкой, social/apps/payments центрируются, legal переносится.
- До `520px`: main grid одна колонка, heading `15px`, links `13px`, app button
  `148x44`.

## 6. Assets copied for the new implementation

Ассеты уже продублированы без изменения исходных файлов:

- `app/img/next/header/` — logo, mini-logo, search, favorite, cart и mobile
  tabbar icons; `header/halloween/` — seasonal logo/sprite.
- `app/img/next/footer/social/` — Facebook, Instagram, TikTok.
- `app/img/next/footer/app/` — QR, Apple logo, Google Play icon.
- `app/img/next/footer/payments/` — Visa, Mastercard, Google Pay, Apple Pay.
- `app/img/next/footer/halloween/` — seasonal sprite и cart decoration.

## 7. Icons: what is actually used

- **Lucide-подобные inline SVG:** `lucide-house` и `lucide-layout-grid` в
  mobile tabbar. В legacy это не импорт из npm, а inline SVG с классом `lucide`.
  В новом проекте можно использовать Lucide, но формы/размеры должны совпасть.
- Профиль, order/bag, chevrons, logout, close и Telegram в header/footer —
  inline SVG или CSS, не отдельные Lucide assets.
- Search/favorite/cart/mobile tabbar/social/payment/app icons — обычные
  существующие SVG/PNG assets из `app/img/next`.
- Feedback type icons используют существующий Font Awesome icon font:
  `fa-lightbulb-o` и `fa-exclamation-triangle`; это отдельная зависимость,
  не Lucide.

## 8. Что обязательно сохранить при переносе

1. Header/footer должны быть одним shared layout, а не копиями на каждой page.
2. Все counters и состояния приходят с backend: cart, wishlist, auth, roulette.
3. Язык меняет URL и labels; нельзя оставить только RU-тексты или `/catalog`.
4. Не заменять legacy menu data статическим массивом без временного adapter-а.
5. Не считать скидки/доставку/бонусы/подарки в header или footer.
6. Не считать существование copied assets доказательством готовности backend.
7. Desktop и mobile проверяются отдельно: это два разных interaction flow,
   особенно mega-menu против fullscreen multi-level navigation.

