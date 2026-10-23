# Vizaje-Nica legacy: карта бизнес-архитектуры

Этот документ описывает существующий legacy-сайт как источник бизнес-правил для переписывания в `vz-next`.

## Правила использования

- Legacy является source of truth для поведения, данных, маршрутов и ограничений.
- `vz-next` реализует это поведение заново на другом стеке.
- Legacy-код не изменять и не переносить механически.
- HTML, CSS и старую верстку не считать архитектурным контрактом: интерфейс в `vz-next` создается заново.
- Перед реализацией новой функции сначала найти соответствующий legacy-controller, model, library, route и database tables.
- Не придумывать новую бизнес-логику, если в legacy уже есть рабочее правило.

## Общая схема запроса

```text
HTTP route
  -> frontend/backend controller
      -> model / library / helper
          -> MySQL + session + cookies
      -> response data / JSON / redirect
```

## Framework core

### `application/core/FrontEndController.php`

Общий bootstrap всех публичных страниц:

- запускает PHP-сессию;
- определяет язык из URL `/ru` или `/ro` и сохраняет языковой контекст;
- загружает database, cart, user-agent, helpers;
- проверяет клиентскую сессию через `clients_model`;
- синхронизирует состояние корзины;
- загружает общие меню категорий, брендов и магазинов;
- передает в каждый frontend-запрос cart totals, client info, wishlist, consent и common navigation;
- применяет общие cache TTL для меню, промо и header data;
- формирует canonical/hreflang/SEO data;
- выбирает ответ страницы через `inner_view`.

При переносе обязательно сохранить: язык, гостевую сессию, авторизацию, общие cart totals и доступность общих данных на каждой странице.

### `application/core/BackEndController.php`

Общий bootstrap админки `/cp`:

- проверяет `admin_id`, `login`, `admin_key` в сессии;
- повторно проверяет администратора в таблице `admins`;
- при невалидной сессии отправляет на `/cp/login`;
- задает общие пути CRUD для dashboard controllers;
- считает новые заказы.

Админская авторизация не должна смешиваться с клиентской авторизацией.

### `application/core/BaseModel.php`

Базовые CRUD-операции для простых моделей: `find`, `find_first`, `put`, `update`, `delete`, `getByField`, сортировка и `sorder`.

## Маршруты и контроллеры

### Каталог и товар

Файл: `application/controllers/frontend/Catalog.php`

Маршруты:

- `/ru/katalog` или `/ro/catalog` — весь каталог;
- `/{lang}/katalog/{category}` — категория;
- `/{lang}/katalog/{category}/{product}` — товар;
- `/{lang}/novinki` — новые товары;
- `/{lang}/skidki` — товары со скидками;
- `/{lang}/top-prodaz` — хиты;
- `/{lang}/products/variable` — данные варианта;
- `/{lang}/products/filter_*` — AJAX-фильтры каталога.

`Catalog::item()`:

1. Находит страницу каталога.
2. Находит категорию по language URI.
3. Находит товар по product URI внутри маршрута категории.
4. Загружает изображения товара.
5. Загружает варианты товара.
6. Загружает характеристики.
7. Загружает активную акцию товара.
8. Загружает похожие товары.
9. Формирует language URLs, canonical и structured data.

Нельзя заменять этот flow простым `/product/{slug}` без сохранения языкового и категорийного контекста.

### Акции

Файлы:

- `application/controllers/frontend/Offers.php`
- `application/models/Offers_model.php`
- `application/controllers/backend/Offers.php`

Маршруты:

- `/{lang}/aktsii` или `/{lang}/promotii` — список акций;
- `/{lang}/aktsii/{uri}` — страница акции;
- `/{lang}/products/filter_offer` — фильтр товаров акции.

Бизнес-модель:

- акция хранится в `offers`;
- связь акции с товарами хранится в `offers_products`;
- публичная страница получает товары через relation, а не через mock-список;
- акция может быть скрыта через `isShown`;
- срок действия и язык определяют доступность акции;
- активная акция товара используется также на странице товара.

Админка позволяет добавлять товары к акции по одному или массово. При переносе сохранить relation, фильтрацию активных акций и связь товара с акцией.

### Корзина

Файлы:

- `application/controllers/Ajax.php`
- `application/controllers/frontend/Cart.php`
- `application/libraries/CartCalculator.php`
- `application/libraries/MY_Cart.php`

Маршруты:

- `POST /cart_add` — добавить товар;
- `POST /cart/update_cart` — изменить количество;
- `POST /cart/delete` — удалить строку;
- `GET/POST /cart/drawer` — получить drawer корзины;
- `/{lang}/korzina` — полная корзина;
- `/{lang}/oformlenie-zakaza` — checkout.

Правила корзины:

- корзина хранится в сессии, не в таблице товаров;
- строка определяется по `product_id + variant_id`;
- одинаковый товар с тем же вариантом объединяется;
- разные варианты являются разными строками;
- количество ограничено фактическим остатком;
- для retail используется `discount_price`, если она задана;
- для B2B используются отдельные `priceWH` и `on_stockWH/qtyWH`;
- после каждой операции пересчитываются subtotal, скидки, доставка, бонусы и total;
- AJAX-ответ должен возвращать актуальное количество, total items, cart total и delivery state;
- гость может пользоваться корзиной до авторизации;
- при входе корзина гостя должна сохраниться/объединиться с корзиной пользователя.

### `CartCalculator`

Главный слой расчета корзины:

- нормализует строки корзины;
- получает товар и вариант;
- вычисляет актуальную цену и остаток;
- считает product discounts;
- применяет promo code;
- применяет roulette discount;
- считает бонусы и списание бонусов;
- считает бесплатную/платную доставку;
- синхронизирует подарок рулетки;
- готовит единый state для cart drawer, cart page и checkout.

Не дублировать эти расчеты отдельно в UI, checkout и cart drawer.

### Рулетка и подарки

Файлы:

- `application/controllers/frontend/Roulette.php`
- `application/models/Roulette_model.php`
- `application/models/Roulette_game_model.php`
- `application/models/Gifts_model.php`
- `application/controllers/backend/Roulette.php`
- `application/controllers/backend/Gifts.php`

Маршруты:

- `/{lang}/roulette/state` — состояние рулетки;
- `/{lang}/roulette/spin` — запуск вращения.

Правила:

- выигрыш хранится в `roulette_spins`;
- подарок выбирается по gift id и связан с товаром через `gifts`;
- подарок нельзя заказать как обычный товар;
- после применения выигрыша подарок добавляется в корзину отдельной строкой;
- цена подарка — 1 MDL;
- подарок нельзя свободно удалить или изменить как обычный cart item;
- в заказе подарок помечается как подарок рулетки;
- старые заказы могут определять подарок через snapshot/данные roulette spin.

### Авторизация и аккаунт

Файл: `application/controllers/frontend/Account.php`

Основные маршруты:

- `/{lang}/login` — вход;
- `/{lang}/registration` — регистрация;
- `/{lang}/logout` — выход;
- `/{lang}/password-recovery` — восстановление;
- `/{lang}/lichnyiy-kabinet` — аккаунт;
- `/{lang}/zakazyi` — история заказов.

Состояние клиента legacy хранит в PHP session:

- `user_id`;
- `user_login`;
- `usr_key`;
- `isb2b`.

При проверке сессии данные клиента повторно читаются через `clients_model`. Нельзя доверять только наличию cookie или session id без проверки клиента в базе.

### Checkout и заказы

Checkout реализован в `frontend/Cart::checkout()` и связанных методах `Orders_model`.

Обязательные правила:

- перед созданием заказа повторно проверить товары, варианты и остатки;
- зафиксировать snapshot названия, SKU, варианта, цены и количества в `orders_products`;
- не строить подтвержденный заказ только из текущих данных каталога;
- атомарно создать заказ, позиции, скидки и доставку;
- списать остатки только после успешной проверки;
- очистить корзину после успешного создания заказа;
- сохранить offline/online payment state отдельно;
- подтверждение заказа и analytics строить из snapshot заказа.

## Модели бизнес-данных

### `Products_model.php`

Отвечает за:

- товары из `products`;
- фильтрацию `isShown` и наличия;
- retail/B2B цены;
- `discount_price` и `sale_percent`;
- варианты из `products_variable`;
- изображения из `products_img` и `products_variable_img`;
- характеристики из `products_features`/feature tables;
- карточечные options (объем/цвет);
- похожие товары;
- данные для корзины и quick view.

### Остальные ключевые модели

- `Categories_model` — дерево категорий, parent category, language URIs.
- `Brands_model` — бренды и brand navigation.
- `Offers_model` — акции и `offers_products`.
- `Clients_model` — клиент, авторизация, скидка клиента, account data.
- `Orders_model` — заказ, позиции заказа, snapshots, статусы и gift marker.
- `Promocodes_model` — промокоды, ограничения и usages.
- `Roulette_model` / `Roulette_game_model` — настройки, сегменты, spins и выигрыш.
- `Gifts_model` — каталог подарков и связь gift с товаром.
- `Delivery_model` / `Stores_model` — способы доставки, магазины и стоимость доставки.
- `Wishlist.php`/cookie `produse_wishlust` — legacy избранное хранится в cookie, а не в полноценной таблице user favorites.

## Конфигурация и окружение

### `application/config/routes.php`

Главный контракт URL и языковых маршрутов. При переписывании сначала составить таблицу соответствия legacy route → new route.

### `application/config/database.php`

Подключение к MySQL. Файл содержит разные environment-варианты, поэтому нельзя смешивать локальную, dev и production базы. Если товары/акции отсутствуют, сначала проверять database connection и фактическую базу.

### `application/config/config.php`

Отвечает за base URL, cookies и PHP sessions. Важные правила:

- session driver — files;
- session lifetime увеличен;
- cookie secure зависит от HTTPS;
- cookies HttpOnly;
- используется SameSite policy;
- сессия критична для guest cart, login merge, roulette и promo state.

### `application/config/autoload.php`

Автоматически подключает database и базовые helpers (`url`, `mine`, `form`, `security`, `language`, `cookie`).

## Что Claude должен делать при переносе

Для каждой новой функции Claude обязан:

1. Прочитать соответствующие legacy routes/controllers/models/libraries.
2. Выписать входные данные, состояние, правила и ошибки.
3. Отдельно отметить session/cookie/database effects.
4. Реализовать бизнес-логику в `vz-next` без копирования legacy markup.
5. Проверить happy path и edge cases: гость, авторизованный клиент, вариант товара, нет остатка, скидка, промокод, подарок, разные языки.
6. Не изменять legacy и не использовать его view/CSS как основу UI.

## Что запрещено делать без отдельного согласования

- придумывать новую структуру URL;
- заменять session cart на localStorage;
- считать цену только на frontend;
- разрешать купить товар без остатка;
- смешивать retail и B2B цены;
- превращать подарок в обычный редактируемый товар;
- брать текущую цену товара вместо order snapshot;
- использовать mock-данные на рабочих страницах;
- менять legacy-код или базу legacy.
