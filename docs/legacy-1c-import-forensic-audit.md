# VIZAJE-NICA Legacy — forensic-аудит импорта 1С и вариантов

Дата аудита: 21.10.2026  
Репозиторий: `vizaje-nica-legacy`, локальное рабочее дерево  
Стек: CodeIgniter 3 / PHP / MySQL

## Объём и ограничения

Аудит выполнен статически по исходному коду, локальным JSON-файлам и поиску ссылок в репозитории. PHP-код, MySQL и импортные endpoint'ы не запускались. Внешние HTTP-запросы не выполнялись.

В рабочем дереве нет полного SQL-экспорта, поэтому ограничения и индексы таблиц, в частности уникальность `SKU`, `GUID` и `barcode`, нельзя подтвердить только по репозиторию. Числа из вводного контекста про Legacy SQL считаются пользовательскими данными. Отдельно приведена статистика текущих JSON-файлов, найденных в рабочем дереве; это другой снимок и он не обязан совпадать с production или SQL.

Статусы выводов:

- **CONFIRMED** — прямо следует из исходного кода или измерено по текущему локальному файлу.
- **INFERRED** — логический вывод из подтверждённого поведения, но не доказательство исторической причины.
- **UNKNOWN** — в коде нет достаточных данных; нужен ответ владельца 1С/production-схема/журнал запуска.

Секреты, пароли, токены и персональные данные в документ не включены.

## 0. Снимок входных файлов

**CONFIRMED — текущие локальные файлы:**

| Файл | Записей |
|---|---:|
| `public/import/products.json` | 17 941 |
| `public/import/pricevn.json` | 16 978 |
| `public/import/JobVN.json` | 11 423 |
| `public/import/old_products.json` | файл существует, но текущий код его не читает |

По текущему `products.json`:

- 761 группа с повторяющимся `ID_nom`;
- в 758 из них встречается более одного значения `Barcode`;
- в 752 группах повторяющегося `ID_nom` есть более одного непустого Barcode;
- в 80 группах повторяющегося `ID_nom` есть разные `Price`;
- 1 464 непустые группы `VariativeNomenclature` встречаются более одного раза;
- `A10.XXX` — 131 запись;
- `A10.100XX` — 26 записей, в файле значение записано как `a10.100xx`;
- `407.XXART` — 3 записи, в файле значение записано как `407.xxArt`.

По текущему `pricevn.json` найдено 84 повторяющихся группы `ID_nom`; в 82 из них различается `Price` или `DiscountPrice`. Это подтверждает наличие конфликтов во входном файле, но не объясняет их бизнес-причину.

**UNKNOWN:** почему эти числа отличаются от указанных в контексте значений `18 057`, `771`, `689`, `1 536`, `721` и `128`. Вероятные варианты — другой снимок файла, production SQL против локального JSON или предварительная очистка/другая версия импорта. Историческую причину по коду доказать нельзя.

## 1. Реальные точки входа импорта

### 1.1 Маршруты

**CONFIRMED —** в `application/config/routes.php:73-87` объявлены маршруты:

- `import/import` → `Import::import()`;
- `import/import_variable` → `Import::import_variable()`;
- `import/update_qty` → `Import::update_qty()`;
- `import/update_url` → `Import::update_url()`;
- `import/update_stock` → `Import::update_stock()`;
- `import/update_price` → `Import::update_price()`;
- `import/update_seo` → `Import::update_seo()`;
- `import/update_sale_mdl` → `Import::update_sale_mdl()`.

**CONFIRMED —** `application/controllers/Import.php:8-12` не содержит проверки администратора или другого видимого ограничения доступа в конструкторе. Это отдельный operational/security-риск: порядок и авторизация запуска импортов не зафиксированы в коде.

**CONFIRMED —** в маршрутах `application/config/routes.php:77-78` указаны методы `import_categories` и `brands_product`, но в `Import.php` найдены только `import_categories_off()` (`542-633`) и `brands_product_off()` (`635-644`). Это выглядит как устаревшая или несогласованная маршрутизация; фактический запуск этих двух маршрутов нужно проверить отдельно.

### 1.2 Фактическая последовательность

**CONFIRMED:** код содержит отдельные операции, но не содержит в репозитории scheduler/очередь/cron, который гарантировал бы их порядок:

1. `Import::import()` читает `products.json` и пишет основные товары (`Import.php:129-435`).
2. `Import::import_variable()` снова читает `products.json` и пишет варианты (`Import.php:464-540`).
3. `Import::update_price()` читает `pricevn.json` и обновляет цены (`Import.php:744-780`).
4. `Import::update_seo()` читает `JobVN.json`, но текущая функция не выполняет SQL update (`Import.php:437-462`).
5. `update_qty()`, `update_stock()` и `update_sale_mdl()` — отдельные пересчёты (`Import.php:646-806`).

**UNKNOWN:** кто и в каком порядке вызывает эти endpoint'ы, какие из них запускаются production регулярно и что происходит при частичном падении цепочки.

## 2. Основной импорт `products.json`

Источник и чтение:

- **CONFIRMED:** `Import::import()` читает только `public/import/products.json` через `file_get_contents()` и `json_decode()` (`application/controllers/Import.php:129-148`).
- **CONFIRMED:** импорт пропускает запись, если пусто `ShortDescriptionRus` (`216-218`).
- **CONFIRMED:** категории ищутся по названию из `NomenclatureCategory1` и `NomenclatureCategory2`; отсутствующее название превращается в `0` (`221-228`).
- **CONFIRMED:** бренд ищется по `Brand`; если бренд не найден, создаётся строка в `brands` с `title` и транслитерированным `uri` (`230-240`).

### 2.1 Таблицы и поля

**CONFIRMED —** основной импорт изменяет:

1. `brands`: вставляет отсутствующий бренд, поля `title`, `uri` (`Import.php:230-239`).
2. `products`: вставляет или обновляет товар (`273-303`, `365-390`).
3. `product_categories`: при вставке создаёт связи (`305-323`); при обновлении удаляет старые связи и вставляет текущие (`393-415`).
4. `product_import_changes`: пишет журнал только если таблица существует и включён флаг tracking (`85-101`, `131-134`, `339-344`, `420-425`).

При создании `products` используются, среди прочего:

```text
GUID, SKU, volume, price, priceWH, barcode, category_id, brand_id,
variations, on_stock, on_stockWH, titleRU/titleRO/titleEN,
WarehouseName, sex, text*, instruction*, components*,
uriRU, uriRO, uriEN, isShown
```

Точный набор виден в `Import.php:267-302`. Для нового товара `isShown = 1`.

При обновлении `Import.php:368-388` обновляет цены, объём, Barcode, остатки, контент, инструкции и компоненты, но не обновляет `GUID`, `SKU`, `uri*`, `brand_id` и `isShown`.

### 2.2 Ключ поиска существующего товара

**CONFIRMED:** перед циклом импорт загружает `products` и строит PHP-map `$products[$row->GUID] = $row` (`Import.php:192-200`). Внутри цикла проверка существования выполняется только по `$value->ID_nom` (`265`). Следовательно, логический ключ основного товара в этом импорте — `ID_nom` → `products.GUID`.

**CONFIRMED:** `Barcode` не используется для поиска существующего товара. `SKU` также не используется для поиска основного товара в `import()`.

**UNKNOWN:** есть ли в production уникальный индекс на `products.GUID`. Его нельзя подтвердить без schema-only SQL.

### 2.3 Повторяющийся `ID_nom`

**CONFIRMED:** если одинаковый `ID_nom` повторяется внутри одного `products.json`:

- первая запись вставляет одну строку `products` и помещает её в `$products[$ID_nom]` (`265-346`);
- следующая запись попадает в ветку обновления (`348-430`), а не создаёт новую строку;
- scalar-поля, включая `price`, `priceWH`, `volume`, `barcode`, `title*`, `WarehouseName`, перезаписываются значениями последней обработанной записи (`365-387`);
- категории при повторной записи заменяются текущим набором, если он непустой (`393-418`).

**CONFIRMED:** порядок разрешения конфликта — порядок записей в JSON; специального сравнения времени, источника, Barcode или качества записи нет.

**INFERRED:** для текущих 761 повторяющихся групп `ID_nom` обычный запуск импортера не должен создать новую строку на каждую повторную запись, но оставляет в основной таблице последнее значение Barcode/цены/контента. Если в базе уже есть дубли `GUID`, PHP-map оставляет последний прочитанный объект, и остальные строки не будут согласованно объединены (`194-200`).

### 2.4 Пустые и отсутствующие поля

**CONFIRMED:**

- пустой `ShortDescriptionRus` полностью исключает запись из основного импорта (`216-218`);
- пустые категории дают `category_id = 0` и не создают связь в `product_categories` (`221-228`, `307-323`);
- пустой `VariativeNomenclature` даёт `SKU = ID_nom` и пустое поле `variations` (`245-247`, `267-283`);
- SEO-поля добавляются только если поле существует и после trim не пустое (`104-126`, `302`);
- остальные поля `ID_nom`, `Brand`, `Barcode`, `Price`, `PriceWH`, `VolumeVar` и контент читаются напрямую без полноценной схемной валидации (`230-302`, `365-388`).

**UNKNOWN:** как PHP-версия и настройки error handling production обрабатывают отсутствующее свойство JSON: как `null`, warning или остановку. Надёжная миграция не должна полагаться на такое неявное поведение.

### 2.5 Удаление, скрытие и восстановление

**CONFIRMED:** в `Import::import()` нет удаления товаров, скрытия отсутствующих в новом feed записей или soft-delete по признаку отсутствия (`129-435`). Старый товар, исчезнувший из `products.json`, остаётся в `products`.

**CONFIRMED:** новый товар получает `isShown = 1` (`273-301`). Существующий товар не получает обновление `isShown` (`368-390`), поэтому скрытый товар этим методом не восстанавливается.

**CONFIRMED:** `Import::import_variable()` ставит `isShown = 1` и при вставке, и при обновлении варианта (`486-503`, `514-532`). Таким образом, существующий скрытый вариант может быть снова показан при следующем запуске.

**CONFIRMED:** `update_stock()` меняет только `stock`, `on_stock`, `on_stockWH` (`646-693`), а `update_qty()` пересчитывает остатки опубликованных товаров (`728-742`); ни один из них не удаляет строки.

## 3. `old_products.json`

**CONFIRMED:** поиск по исходному коду репозитория не нашёл ссылок на `old_products.json`. В `Import.php` файл не открывается; список методов контроллера заканчивается операциями по `products.json`, `JobVN.json` и `pricevn.json` (`129-815`).

**UNKNOWN:** архивный ли это файл, резервная копия, источник старого импортера или ручной snapshot. Нельзя считать его частью текущего production-потока без deployment/cron-конфигурации или истории сервера.

## 4. Вариативные товары: полный путь

### 4.1 1С JSON → основная строка `products`

**CONFIRMED:** при непустом `VariativeNomenclature` основной импорт выбирает:

```php
$incomingSku = $value->VariativeNomenclature;
```

При пустом значении используется `ID_nom` (`Import.php:245-247`). Затем при вставке каждая запись всё равно создаёт отдельную строку `products` с `GUID = ID_nom`, а `SKU = incomingSku` и `variations = VariativeNomenclature` (`267-283`, `303-304`).

**Ключевое следствие — CONFIRMED:** 131 строка `products.json` группы `A10.XXX` проходят через вставку как 131 потенциальная основная строка `products`, каждая с одинаковым `SKU = A10.XXX`, если соответствующие `ID_nom` ещё отсутствуют в БД. Импортер не создаёт одну parent-строку на группу и не делает group-by по `VariativeNomenclature`.

### 4.2 Создание и привязка `products_variable`

**CONFIRMED:** `Import::import_variable()` (`Import.php:464-540`) обрабатывает только записи с непустым `VariativeNomenclature` (`472-474`).

Проверка существующего варианта:

```text
products_variable.SKU = value.ID_nom
```

Это видно в `Import.php:474-476`.

Если варианта нет, родитель ищется так:

```text
products.SKU = value.VariativeNomenclature
```

(`478-480`). При найденном родителе берётся его `id`, затем вставляется `products_variable` (`483-504`).

Вариант получает:

```text
SKU = ID_nom
priceWH = PriceWH       [только при первоначальной вставке]
qty = QuantityBalance + BalMall
qtyWH = QuantityBalance
color = ColorVarMD
colorRU = ColorVar
colorRO/colorEN = ColorVarMD
titleRU = VariativeName
titleRO/titleEN = VariativeNameMD
VolumeVar = VolumeVar
barcode = Barcode
product_id = найденный parent id
isShown = 1
```

Диапазон: `Import.php:483-503`.

### 4.3 Неуникальный групповой SKU

**CONFIRMED:** поиск parent выполняется по `products.SKU` без `order_by`, без проверки количества совпадений и без проверки соответствия бренда, категории или `VariativeNomenclature` (`Import.php:478-480`, `506-510`).

**CONFIRMED:** `row()` возвращает одну строку из потенциально нескольких совпадений. Код не определяет, какая из карточек с одинаковым SKU группы является правильной.

**INFERRED:** если `import()` создал несколько карточек с одинаковым `SKU = A10.XXX`, все варианты `A10.XXX` могут привязаться к одной произвольно выбранной карточке. Остальные карточки останутся с собственными полями `products`, но без ожидаемого набора вариантов. Это объясняет механизм риска, но не доказывает историческую причину каждой конкретной аномалии.

**UNKNOWN:** есть ли в реальной production-схеме уникальный индекс на `products.SKU`. Пользовательские данные сообщают о 128 повторяющихся SKU, но без schema-only export нельзя определить, на какой именно таблице и в каком срезе считались дубли.

### 4.4 Orphan-варианты и `product_id = 1`

**CONFIRMED:** при первоначальной вставке варианта, если parent по групповому SKU не найден, код записывает `product_id = 0` (`Import.php:477-482`, `500`).

**CONFIRMED:** если вариант с таким `SKU = ID_nom` уже существует, но parent по групповому SKU не найден, ветка обновления записывает `product_id = 1` (`505-510`, `529`).

**CONFIRMED:** это единственный найденный в импортере прямой путь, который подставляет `product_id = 1` как fallback.

**INFERRED:** группа orphan-вариантов с `product_id = 1` могла появиться так: вариант был вставлен раньше с `product_id = 0`, затем при повторном импорте parent не нашёлся, и существующая строка была переведена на товар `id = 1`. Это правдоподобный механизм, но без истории запусков и audit log нельзя доказать, что именно он создал 463 строки.

**CONFIRMED:** при загрузке PDP `Catalog::item()` получает варианты по `product_id` карточки через `get_product_variable(..., true)` (`application/controllers/frontend/Catalog.php:1048-1101`). Модель фильтрует `product_id`, `isShown = 1` и положительную цену (`Products_model.php:568-635`), а затем удаляет варианты без изображений (`641-689`). Поэтому `product_id = 0` не попадёт под обычную карточку; `product_id = 1` может визуально попасть на карточку товара 1, если проходит остальные фильтры.

### 4.5 Цена варианта при первоначальной вставке

**CONFIRMED:** в ветке новой записи `products_variable` (`Import.php:486-503`) сохраняется `priceWH`, но отсутствует `price` и отсутствует `discount_price`. Поле `Price` из `products.json` там не записывается.

**CONFIRMED:** в ветке обновления существующего варианта `price` записывается из `value->Price`, а `discount_price` всё равно не записывается (`514-532`).

**INFERRED:** retail-цена нового варианта появляется только если после `import_variable()` отдельно запускается `update_price()` или если в БД уже было значение по умолчанию. При неправильном порядке операций новый вариант может считаться недоступным для B2C, потому что модель требует `price > 0` (`Products_model.php:623-635`).

### 4.6 ColorVar, VolumeVar и HTML

**CONFIRMED:** импортер переносит `ColorVar`/`ColorVarMD` и `VolumeVar` в отдельные поля варианта (`Import.php:490-498`).

**CONFIRMED:** PDP получает варианты через `Products_model::get_product_variable()` и передаёт в view поля `SKU`, локализованный `color`, `VolumeVar`, `price`, `priceWH`, `discount_price`, `qty` (`Products_model.php:577-589`).

**CONFIRMED:** `application/views/pages/catalog/item.php:201-401` строит frontend-объект варианта, включая id, SKU, color, colors, volume, цены, остаток и images. Затем `item.php:483-495` кладёт вариант либо в группу цветов, если `color` непустой, либо в группу объёмов, если цвет пустой и заполнен volume.

**CONFIRMED:** вариант с одновременно заполненными цветом и объёмом попадёт только в `colorVariations`, потому что проверка объёма находится в `elseif` (`item.php:486-494`).

**CONFIRMED:** при запросе `/products/variable` `Catalog::variable()` повторно проверяет соответствие `variable_id` и `product_id`, возвращает price, regular price, barcode, volume, SKU, availability и quantity (`Catalog.php:2025-2154`).

## 5. Три контрольных семейства

### 5.1 `A10.XXX`

**CONFIRMED — текущий JSON:** 131 запись, бренд `Anny`, разные `ID_nom`, цвета и одинаковая группа `A10.XXX`.

**CONFIRMED — код:** `import()` не группирует эти 131 записей в одну карточку; каждая уникальная `ID_nom` создаёт/обновляет `products` (`Import.php:265-345`). `import_variable()` затем ищет parent по одинаковому `products.SKU = A10.XXX` (`478-480`).

**INFERRED:** это семейство наиболее наглядно демонстрирует риск нескольких parent-карточек с одинаковым групповым SKU и произвольного выбора одной из них для вариантов.

### 5.2 `A10.100XX`

**CONFIRMED — текущий JSON:** 26 записей, значение написано в нижнем регистре `a10.100xx`, бренд `Anny`, варианты имеют разные `ColorVar` при одинаковом `VolumeVar = 15`.

**CONFIRMED:** код не нормализует регистр `VariativeNomenclature` перед записью или поиском (`Import.php:245-247`, `478-480`).

**INFERRED:** если в одном feed/БД появятся `A10.100XX` и `a10.100xx`, они могут рассматриваться как разные SKU в зависимости от collation MySQL. Это нужно проверить по фактическому collation/индексам и не предполагать.

### 5.3 `407.XXART`

**CONFIRMED — текущий JSON:** найдены 3 записи группы `407.xxArt` (case-insensitive соответствует указанному `407.XXART`), бренд `Artdeco`, разные `ColorVar` `60`, `65`, `70`.

**CONFIRMED:** для этой группы действует тот же общий путь: основная строка на каждый `ID_nom`, затем вариант с parent-поиском по групповому SKU.

**UNKNOWN:** является ли `407.xxArt` глобально уникальным кодом parent-папки в 1С и нет ли в production других карточек с тем же значением.

## 6. Несколько штрихкодов

### 6.1 Поддержка одного `ID_nom` с несколькими Barcode

**CONFIRMED:** входной JSON допускает несколько строк с одним `ID_nom` и разными Barcode. Например, текущий feed содержит такие группы; код принимает их как повтор одного логического товара, а не как список штрихкодов.

**CONFIRMED:** `products.barcode` и `products_variable.barcode` — scalar-поля, в которые записывается одно значение из текущей записи (`Import.php:279`, `372`, `502`, `531`). Отдельной таблицы `product_barcodes`/`variant_barcodes` или массива Barcode в импортере нет.

### 6.2 Перезапись

**CONFIRMED:** при повторном `ID_nom` основной товар получает Barcode последней обработанной строки (`Import.php:365-387`, особенно `372`).

**CONFIRMED:** вариант ищется по `products_variable.SKU = ID_nom`, поэтому повторная запись того же `ID_nom` обновляет одну строку и перезаписывает её Barcode последним значением (`474-476`, `514-532`).

**CONFIRMED:** Barcode не используется как уникальный ключ ни для `products`, ни для `products_variable` в этих обработчиках.

**UNKNOWN:** есть ли database-level unique index на Barcode. В исходном коде проверки уникальности нет, а полного schema-only SQL в рабочем дереве нет.

### 6.3 Пустой Barcode

**CONFIRMED:** отдельной ветки для пустого Barcode нет. Значение напрямую записывается в `products.barcode` или `products_variable.barcode` (`Import.php:279`, `372`, `502`, `531`).

**INFERRED:** пустой Barcode может заменить ранее заполненный Barcode при повторном `ID_nom`, если пустая запись идёт позже. Это следует из последовательно выполняемого update, но конкретная история зависит от порядка строк feed.

## 7. Цены и скидки

### 7.1 Источники

**CONFIRMED:**

- обычная retail-цена и wholesale-цена первоначально берутся из `products.json`: `Price` → `products.price`, `PriceWH` → `products.priceWH` (`Import.php:273-279`, `368-374`);
- `pricevn.json` в `update_price()` обновляет `products.price` из `Price` и `products.discount_price` из `DiscountPrice` (`Import.php:744-775`);
- `PriceWH` из `pricevn.json` не существует в текущем shape и не обновляется этой функцией;
- `JobVN.json` содержит `Price`/`PriceWH`, но текущий `update_seo()` их не сохраняет (`Import.php:437-462`);
- `DiscountPrice = 0` записывается как `discount_price = 0`, то есть снимает скидку (`Import.php:758-763`, `769-774`).

### 7.2 Приоритет между фидами

**CONFIRMED:** формального правила приоритета нет. `products.json` пишет начальное значение в `import()`, а `pricevn.json` может перезаписать `products.price` при отдельном запуске `update_price()`.

**INFERRED:** фактическим источником цены становится последний успешно выполненный импортный endpoint, а не заранее определённый source-of-truth. Если `update_price()` запускается после `import()`, его `Price` побеждает `products.json`; если не запускается, остаётся цена из `products.json`.

### 7.3 Конфликтующие цены

**CONFIRMED:** в `update_price()` каждая строка `pricevn.json` обрабатывается последовательно. Для каждого `ID_nom` выбирается одна строка `products` по `GUID` и одна строка `products_variable` по `SKU`; затем выполняется update (`Import.php:752-775`). Нет сортировки, проверки конфликтов или журнала.

**CONFIRMED:** при повторяющемся `ID_nom` в `pricevn.json` последнее значение `Price`/`DiscountPrice` в порядке файла становится записанным значением.

**CONFIRMED:** основной `import()` делает аналогичное last-write-wins для повторяющегося `ID_nom` (`Import.php:348-430`).

**UNKNOWN:** почему 1С отдаёт несколько цен для одного `ID_nom`: разные склады/прайс-листы, исторические дубли, несколько штрихкодов, ошибочная генерация или разные валютные/ценовые зоны. Это должен объяснить программист 1С.

### 7.4 Страница `reduceri` / `skidki`

**CONFIRMED:** маршрут страницы объявлен в `application/config/routes.php:155-157`; контроллер `Catalog::best_sellers()` вызывает `get_all_products_best_sellers()` и `get_products_pag_best_sellers()` (`Catalog.php:238-291`).

**CONFIRMED:** выборка страницы скидок требует именно скидку у parent `products.discount_price > 0`, наличие изображения и опубликованный/продаваемый parent (`Products_model.php:2567-2620`, `2633-2718`).

**CONFIRMED:** вторая выборка страницы также фильтрует `products.discount_price > 0` (`Products_model.php:2699-2701`). Отдельная скидка только у `products_variable` сама по себе не делает parent видимым на странице `reduceri`.

**CONFIRMED:** `update_sale_mdl()` рассчитывает `sale_mdl` и `sale_percent` только для таблицы `products`, не для вариантов (`Import.php:782-806`).

**UNKNOWN:** если текущий production содержит отдельную новую выборку для header discount carousel, её поведение не следует смешивать со страницей `reduceri`; в коде есть отдельный `get_header_discount_products()` (`Products_model.php:1971-2064`), который уже проверяет скидочные варианты, но это другой consumer.

## 8. URL, категории, canonical и SEO

### 8.1 Формат маршрута

**CONFIRMED:** URL товара имеет структуру `/{lang}/{catalog-uri}/{category-uri}/{product-uri}` по маршруту `application/config/routes.php:149-153`. Последний сегмент приходит в `Catalog::item()` как `uri4` и ищется как локализованный URI (`Catalog.php:1034-1057`).

**CONFIRMED:** `Products_model::get_product_by_uri_for_pdp()` фильтрует `products.isShown = 1` и точно сравнивает `uri$lang = $uri` (`Products_model.php:97-200`). Нет fallback по `id`, `SKU`, `GUID` или Barcode.

**CONFIRMED:** категория также ищется по `uri$lang` и `isShown = 1` (`Categories_model.php:196-224`). Бренд ищется по своему единому `brands.uri` (`Brands_model.php:84-104`).

### 8.2 Как создаются URI

**CONFIRMED:** новый товар из `products.json` получает numeric URI от `ID_nom` через `transliteration($value->ID_nom)` для `uriRU`, `uriRO`, `uriEN` (`Import.php:297-300`).

**CONFIRMED:** при обновлении существующего товара `uri*` не входят в `$data`, поэтому `Import::import()` не переводит уже существующую карточку с numeric URI на title slug (`Import.php:368-390`).

**CONFIRMED:** `Import::update_url()` группирует товары по `uriRO`; для каждого дубликата после первого добавляет `-id` ко всем трём URI (`Import.php:695-710`). Аналогично обрабатываются категории (`712-725`).

**CONFIRMED:** `Api::url_chech()` содержит другую логику для duplicate `uriRO`: строит URI из title + SKU и сразу обновляет `products` (`application/controllers/Api.php:16-40`). Вторая проверка title URI собирается в `$update`, но `update_batch` закомментирован (`43-56`).

**INFERRED:** смешение numeric URI, title-SKU URI и URI с `-id` может быть результатом разных исторических импортов/ручных запусков `update_url` и `url_chech`, а не одной текущей формулы. Точная причина для конкретного товара требует истории его строк и запусков.

### 8.3 Canonical, hreflang и sitemap

**CONFIRMED:** `Catalog::prepareProductUrls()` строит текущий canonical из сохранённых `page->uri`, `category->uri` и `product->uri`; также строит RU/RO hreflang (`Catalog.php:1205-1267`).

**CONFIRMED:** sitemap строит URL из тех же сохранённых URI (`application/helpers/mine_helper.php:1823-1972`), добавляя только опубликованные товары с опубликованной категорией и проходящие `isSeoIndexable`.

**CONFIRMED:** в найденном коде нет таблицы истории URI, mapping old→new или автоматического product-redirect. `update_url()` и `url_chech()` меняют текущие URI, но не сохраняют старый адрес.

**CONFIRMED:** `Catalog::item()` при старом/числовом URI, которого больше нет в `products.uri$lang`, получает пустой product и вызывает 404 (`Catalog.php:1048-1057`).

**UNKNOWN:** полный список старых URL из Google/Search Console и production access logs отсутствует; нельзя восстановить redirect map только из текущей БД/кода.

## 9. Фактическая схема обработки 1С в Legacy

```text
products.json
  ├─ Import::import()
  │    ├─ key: ID_nom → products.GUID
  │    ├─ one products row per first-seen ID_nom
  │    ├─ repeated ID_nom → update same row, last scalar value wins
  │    ├─ VariativeNomenclature → products.SKU + products.variations
  │    └─ product_categories / brands
  │
  └─ Import::import_variable()
       ├─ only non-empty VariativeNomenclature
       ├─ variant key: ID_nom → products_variable.SKU
       ├─ parent lookup: products.SKU = VariativeNomenclature
       ├─ missing parent on insert → product_id = 0
       ├─ missing parent on update → product_id = 1
       └─ ColorVar / VolumeVar / Barcode / qty / images downstream

pricevn.json
  └─ Import::update_price()
       ├─ products by GUID = ID_nom
       └─ products_variable by SKU = ID_nom
       └─ Price + DiscountPrice, last duplicate wins

JobVN.json
  └─ Import::update_seo()
       └─ currently only builds $data and dump(); DB update absent

stored products.uri*
  └─ Catalog::item() exact URI lookup
       └─ product page / variants / canonical / sitemap
```

## 10. Правила, которые V2 обязана сохранить

Это не означает копировать legacy-ошибки; это бизнес-поведение, которое нужно сохранить после явной нормализации.

1. **CONFIRMED / required:** `ID_nom` должен быть сохранён как внешний стабильный идентификатор конкретной номенклатурной позиции.
2. **CONFIRMED / required:** `VariativeNomenclature` должен быть сохранён как внешний идентификатор группы вариантов, если 1С подтверждает его semantics.
3. **CONFIRMED / required:** вариант должен иметь собственные Barcode, цену, wholesale price, остаток, цвет/оттенок, объём, SKU и изображения.
4. **CONFIRMED / required:** parent и variant должны иметь независимые retail/wholesale цены и остатки; в Legacy они используются раздельно (`Products_model.php:68-76`, `568-635`).
5. **CONFIRMED / required:** PDP должна разрешать карточку по стабильному canonical slug, а вариант выбирать по variant id/external id с проверкой принадлежности parent (`Catalog.php:2025-2154`).
6. **CONFIRMED / required:** текущие SEO URI нужно перенести в таблицу aliases/redirects, чтобы numeric и title-SKU URL не исчезли.
7. **UNKNOWN until 1C contract:** точное правило начисления/обновления цены, остатков и статусов должно быть формально зафиксировано, а не выводиться из порядка HTTP-запусков.

## 11. Ошибки Legacy, которые V2 не должна повторять

**CONFIRMED:**

- использовать неуникальный групповой SKU как единственный parent lookup;
- создавать parent-card на каждую запись с одинаковым `VariativeNomenclature`;
- хранить несколько Barcode в одном scalar-поле и молча оставлять последний;
- разрешать одинаковый `ID_nom` с конфликтующими ценами без проверки;
- вставлять новый вариант без retail `price`;
- подставлять `product_id = 1` как fallback для отсутствующего parent;
- восстанавливать `isShown` варианта без явного статуса публикации;
- считать, что `JobVN.json` обновляет SEO, хотя текущая функция только формирует массив и выводит его;
- не удалять/архивировать исчезнувшие записи и не иметь reconciliation report;
- изменять URI без таблицы redirect history;
- принимать отсутствие `ShortDescriptionRus` как единственную валидацию записи;
- запускать публичные import routes без явно подтверждённой авторизации (`Import.php:8-12`).

## 12. Рекомендации по миграции идентификаторов и вариантов

### 12.1 Canonical mapping

В V2 нужны отдельные поля, минимум:

```text
legacy_product_id       — локальный numeric products.id
legacy_guid              — products.GUID / ID_nom для parent-строки
legacy_nomenclature_id   — ID_nom конкретной позиции 1С
legacy_group_code        — VariativeNomenclature
legacy_sku               — products.SKU или products_variable.SKU
legacy_barcode           — только для обратной совместимости, если один
```

Для Barcode нужна отдельная таблица `product_barcodes` или `variant_barcodes` с отношением one-to-many, статусом primary и источником. Нельзя выбирать один Barcode без правила 1С.

### 12.2 Нормализация групп

1. Нормализовать пробелы и регистр только в отдельном comparison key; оригинальное значение хранить без изменения.
2. Проверить глобальную уникальность `VariativeNomenclature` в выгрузке.
3. Если одна группа действительно описывает одну карточку, создать один parent и все `ID_nom` этой группы как variants.
4. Если одна группа используется несколькими карточками, parent key должен включать подтверждённый дополнительный идентификатор 1С; нельзя угадывать по title/brand.
5. Сначала построить conflict report: duplicate group, brands, categories, titles, prices, barcodes.

### 12.3 Цены

До миграции зафиксировать matrix source-of-truth:

```text
products.json  → initial retail / wholesale
pricevn.json   → current retail / discount?
JobVN.json     → SEO only or also prices?
```

При конфликте не применять last-write-wins молча. Сохранять все входные значения, источник, timestamp feed и решение reconciliation.

### 12.4 URL

Перенести текущие `uriRO`, `uriRU`, `uriEN` как canonical values. Отдельно загрузить исторические numeric/title-SKU варианты из Search Console, access logs, старых sitemap и БД. Для каждого изменения slug создать permanent redirect и запретить автоматическую замену уже опубликованного URL без записи alias.

## 13. Вопросы программисту 1С

1. Является ли `ID_nom` уникальным идентификатором номенклатуры навсегда, или он может переиспользоваться?
2. Что означает повтор одного `ID_nom` с несколькими Barcode: несколько упаковок, рынков, поставщиков или ошибочная выгрузка?
3. Как хранить все Barcode и какой из них считать primary?
4. Всегда ли один `VariativeNomenclature` означает одну parent-карточку? Может ли код повторяться между брендами/категориями?
5. Регистрозависимы ли `VariativeNomenclature`, SKU и Barcode в 1С?
6. Что означают две строки `pricevn.json` для одного `ID_nom` с разными `Price`?
7. Какой feed является authoritative для retail `Price`, `PriceWH`, `DiscountPrice` и на какой момент времени?
8. Как передаются нулевая цена, отсутствие цены и снятие товара с продажи?
9. Есть ли в feed явный delete/archive/disable сигнал?
10. Что означает `QuantityBalance` и что означают `BalMall` и остальные `Bal*`: уже суммарный остаток или независимые склады?
11. Нужно ли складывать `QuantityBalance + BalMall`, как делает Legacy (`Import.php:283-284`, `489-501`), или это может приводить к двойному учёту?
12. Какой официальный жизненный цикл заказа и payment status между сайтом и 1С?
13. Как выполняются бонусные reserve, spend, confirm, cancel, refund и accrual? Нужны operation IDs и idempotency rules.
14. Какой ответ и retry policy должны быть при повторном чтении заказа до подтверждения `1c`?
15. Является ли `JobVN.json` SEO-only feed или должен обновлять продуктовый контент/цены тоже?
16. Что такое `old_products.json` и можно ли считать его историческим источником?

## 14. Итоговая оценка

**CONFIRMED:** основная проблема не в одной ошибочной строке, а в несовпадении модели 1С и модели Legacy:

- 1С может описывать одну parent-папку и множество номенклатурных позиций;
- Legacy `import()` продолжает создавать product-row на каждую позицию;
- групповой код копируется в `products.SKU` и затем используется как будто он уникален;
- варианты ищут parent через этот неуникальный SKU;
- повторные Barcode и цены схлопываются последней записью;
- часть импортных операций вынесена в отдельные endpoint'ы без кода, гарантирующего порядок и транзакционность.

**INFERRED:** именно это сочетание способно объяснить duplicate SKU, неправильные parent links, orphan-варианты и различие между карточками/вариантами. Для конкретных 721 orphan и 128 duplicate SKU нужно сопоставление с реальным schema-only SQL и историей запусков.

**UNKNOWN:** без официального контракта 1С нельзя безопасно решить, какие дубли являются допустимыми бизнес-сущностями, а какие — ошибками feed. До миграции нужно получить schema-only export, versioned примеры всех трёх feed и письменные правила идентификаторов, цен, остатков и бонусных операций.

## Приложение A. Ключевые доказательства

| Область | Файл и строки |
|---|---|
| Маршруты импорта | `application/config/routes.php:73-87` |
| Чтение `products.json` | `application/controllers/Import.php:129-148` |
| Lookup по GUID/ID_nom | `Import.php:192-200`, `265`, `348-390` |
| Product insert/update fields | `Import.php:267-302`, `365-390` |
| Duplicate category replacement | `Import.php:393-418` |
| SEO feed | `Import.php:437-462` |
| Variant importer | `Import.php:464-540` |
| Parent lookup and orphan fallback | `Import.php:472-510`, `514-532` |
| Stock/hide behavior | `Import.php:646-742` |
| URL collision suffix | `Import.php:695-725` |
| `pricevn.json` | `Import.php:744-780` |
| Sale fields | `Import.php:782-806` |
| Title-SKU URL repair | `application/controllers/Api.php:16-57` |
| PDP URI lookup | `application/models/Products_model.php:97-200` |
| Variant query and image requirement | `Products_model.php:568-689` |
| Product route and PDP lifecycle | `application/config/routes.php:149-153`, `application/controllers/frontend/Catalog.php:1015-1203` |
| Canonical/hreflang | `Catalog.php:1205-1267` |
| Variant HTML transformation | `application/views/pages/catalog/item.php:201-401`, `483-495`, `1325-1575` |
| Reduceri query | `application/controllers/frontend/Catalog.php:238-291`, `application/models/Products_model.php:2567-2815` |
| Sitemap | `application/helpers/mine_helper.php:1823-1972` |

