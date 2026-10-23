# Независимый аудит миграции каталога VIZAJE-NICA → V2

**Дата проверки:** 21 октября 2026 г.  
**Объём:** legacy CodeIgniter 3 / MySQL и исходный код importer/reconciliation на снимке V2 `d20b8be0c1989a61bbaeba6fbf0e311f89c864fc`.  
**Режим:** read-only. PHP, V2-код, базы и выгрузки не изменялись; `--write`, импорт и production-запросы не запускались.

## Резюме

**Вывод:** меры против ложного сопоставления и выдумывания категорий в целом обоснованы реальными неоднозначностями данных. Но это пока не доказывает, что текущий импортёр готов к полному переносу. В актуальном коде есть дефекты, способные потерять баркоды, сохранить конфликтующие identity mappings, размножить изображения при повторном запуске и неверно выставить публичный статус скрытым в legacy товарам. Этот importer также не читает старую таблицу `products_variable`; её строки могут быть заменены 1С-семействами, но такая parity пока не доказана. Есть и сильный сигнал, что план не формирует многовариантные карточки: если переданные числа `5 972 products` и `5 972 variants` взяты из одной сводки актуального `buildResult`, в плане нет ни одной карточки с более чем одним импортируемым вариантом.

При этом V2 уже исправила существенные ранние ошибки: dry-run раньше недосчитывал варианты, баркоды и slug-конфликты; запись нарушала FK-порядок; повторное разрешение variant mapping ломало идемпотентность. Исправления есть в текущем снимке и покрыты синтетическими тестами. Поэтому прежние отчёты о dry-run нельзя считать подтверждением текущего поведения.

**Рекомендация:** изолированную тестовую запись можно проводить только после независимой проверки конкретного PostgreSQL host/database и на репрезентативном наборе данных. Полный импорт реальной выгрузки и production cutover следует отложить до закрытия пунктов High в таблице ниже и сверки отчёта семейств с фактическими 1С/SQL-примерами.

## Границы и качество доказательств

- V2-пути проверены через read-only GitHub-доступ на точном снимке выше. Локального checkout V2 в текущем workspace нет, поэтому тесты V2 здесь не запускались. В отчёте используются исходный код, имеющиеся тесты и документированные результаты предыдущих dry-run.
- В текущем legacy workspace отсутствует `docs/legacy-reference/vizaje_nica_local.sql`, а реальная MySQL-база и обезличенные входные JSON недоступны. Поэтому предоставленные в задании числа, Brand 162 и перечисленные category IDs считаются **данными из контекста пользователя**, а не независимо пересчитанными значениями. Я не проверял отдельные SQL-строки или фактический состав действующего ассортимента.
- Severity описывает риск миграции, а не частоту инцидентов в production. «Блокирует первую запись» означает импорт в изолированную test DB; это отдельно от блокировки production cutover.

Метки: **CONFIRMED** — непосредственно следует из кода; **INFERRED** — вывод из кода и переданных агрегатов; **UNKNOWN** — нужен SQL/JSON или бизнес-ответ.

## 1. Что реально делает legacy

### Бренды без названия

**CONFIRMED.** `Brands_model::get_brands()` и `get_brand_by_uri()` фильтруют бренд по `isShown = 1`, но не требуют непустой `title` и не подставляют fallback-название ([Brands_model.php](../application/models/Brands_model.php#L13), L13–23 и L84–105). В карточках каталога название бренда получается отдельным подзапросом по `brand_id`, тоже без fallback-проверки ([Products_model.php](../application/models/Products_model.php#L2427), L2456–2469).

Legacy не удаляет товар автоматически только из-за отсутствующего/пустого бренда: выборка каталога фильтрует сам товар по `products.isShown`, наличию изображения и sellable-условию, но не требует валидного бренда (там же, L2486–2496). Следовательно, пустое название бренда может быть видимым недостатком брендинга, а не основанием для снятия товара с сайта.

**CONFIRMED для V2:** bootstrap пропускает бренд с пустым заголовком (`empty_title`) и не придумывает название; каталог-импортёр затем ставит товару `draft`, если legacy brand mapping не разрешился ([brand-category-bootstrap.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/brand-category-bootstrap.ts#L194), L196–212; [catalog-importer.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/catalog-importer.ts#L837), L837–845 и L947–951).

По переданным данным Brand 162 имеет пустой title и связан с 56 товарами. **Влияние:** до 56 записей могут не получить brand mapping; сколько из них реально продаётся/показывается, без SQL и цен/остатков определить нельзя. Legacy fallback не найден.

### Родительские категории и фактическая видимость

**CONFIRMED.** Legacy меню строится только от корней с `parent_id == 0`; потомки добавляются проходами по фактическим `parent_id`. Самостоятельной ветки для категории с dangling parent или fallback в root нет ([Categories_model.php](../application/models/Categories_model.php#L36), L100–124). При этом прямой lookup категории по URL требует `isShown = 1`, но не проверяет корректность родителя (L196–225). Контроллер товарной категории пытается получить родителей, но при отсутствующем родителе продолжает строить список для самой категории ([Catalog.php](../application/controllers/frontend/Catalog.php#L655), L661–680). Значит, некорректный parent надёжно исключает категорию из обычного дерева меню, но сам по себе не доказывает, что прямой URL или товары под ней недоступны.

Каталоговая выборка для меню дополнительно ограничивает категории признаком `isShown`; наличие товаров для навигации определяется подзапросом по показанным товарам с ценой выше нуля ([Categories_model.php](../application/models/Categories_model.php#L36), L39–52, L100–120). Точную таблицу используемых category IDs, количество живых товаров в каждой и наличие прямых переходов по URL без SQL выгрузки установить нельзя.

**CONFIRMED по V2 bootstrap:** `parent_id = 0` и `NULL` трактуются как корень; ненулевой parent, которого нет в выгрузке, получает `invalid_parent`; дочерняя категория, чей родитель был пропущен, — `parent_not_resolved`. Граф проходится слоями, а не фиксированной глубиной ([brand-category-bootstrap.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/brand-category-bootstrap.ts#L300), L300–420). Это не излишний fallback-алгоритм: не создавать вымышленного родителя — корректная безопасная политика. Ошибка была бы молча превратить dangling category в root без решения владельца каталога.

Переданные `invalid_parent` / `parent_not_resolved` — реальная проблема legacy-графа, если выгрузка и подсчёты актуальны; сами категории могли всё же открываться напрямую. До выяснения использования нельзя считать их безвредными или удалять.

### URL, canonical и redirects в legacy

**CONFIRMED.** Страница товара ищет товар по `uri4`; если категория или товар не найдены, отдаётся 404. Затем legacy строит RU/RO URL, canonical и hreflang из URI страницы каталога, категории и товара ([Catalog.php](../application/controllers/frontend/Catalog.php#L1050), L1050–1059; L1208–1269). Это означает, что `uriRU`/`uriRO` — часть фактического URL-контракта.

В `Api::url_chech()` есть логика обработки дублирующихся URI: она транслитерирует title и добавляет SKU к RU/RO slug. Это отдельный endpoint; автоматический запуск или история всех URL изменений кодом здесь не доказаны ([Api.php](../application/controllers/Api.php#L16), L16–57). В показанной логике permanent redirect с прежнего URL на новый не обнаружен.

### Допустимая историческая особенность

`parent_id = 0` — sentinel «корень», а не отсутствующий родитель: V2 bootstrap правильно сохраняет это правило. Числовой URI может быть историческим slug, но импортёр считает его не-curated slug и формирует `legacy-<externalId>`; это техническая политика нового импортёра, не правило legacy.

## 2. Проверка V2 reconciliation и catalog importer

### Identity reconciliation и варианты

Импортёр группирует семью, только если нормализованный `products.SKU`-код распознан как group code и совпадает с `VariativeNomenclature`; для подтверждения требуется независимое GUID/barcode-свидетельство внутри семьи. Каждая позиция затем отдельно сопоставляется по GUID/barcode; порядок строк и равное число членов не используются как доказательство ([family-reconciliation.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/reconcile/family-reconciliation.ts#L255), L255–326 и L334–454). При неизвестной глобальной уникальности group code такая осторожность обоснована.

**INFERRED — сильный сигнал по реальному dry-run.** Импортёр создаёт один продукт на family unit и кладёт в результат все импортированные варианты; счётчик `variantsWritten` суммирует длины `variantIds` (L857–880; L1069–1093). В каждой успешной family/standalone записи гарантируется как минимум один variant. Поэтому, если предоставленные `5 972 planned products` и `5 972 planned variants` — это соответственно сумма успешных product units и `variantsWritten` одной и той же версии/запуска, ни одна успешная карточка не получила более одного варианта. Это не доказательство неправильности алгоритма, но сильное расхождение с требованием объединять 1С-семейства. Нужен фактический `families.summary.confirmedFamilies`, распределение членов по подтверждённым семействам и примеры групп, а не только общая сумма.

### Старая таблица `products_variable`

**CONFIRMED по проверенному пути импорта; parity с 1С — UNKNOWN.** Legacy parser описывает `products_variable` как отдельные строки с SKU, цветом, объёмом, ценой, количеством, Barcode и `isShown` ([mysql-catalog.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/parsers/mysql-catalog.ts#L90), L90–110). Старый сайт реально загружает такие варианты к товару и отбирает их по `isShown`, цене, остатку и B2B/B2C режиму ([Products_model.php](../application/models/Products_model.php#L568), L568–644; [Catalog.php](../application/controllers/frontend/Catalog.php#L710), L710–711).

В проверенном `runCatalogImport()`/`importFamily()`/`importStandalone()` нет чтения `dump.productVariables` или `dump.productVariableImgs`: новые `ProductVariant` создаются из подтверждённых строк `dump.products` и 1С-семейств ([catalog-importer.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/catalog-importer.ts#L622), L622–735, L755–975). Если приведённые ранее 8 545 legacy variant rows актуальны, этот шаг сам по себе их не переносит. Возможно, они дублируются 1С номенклатурой или выводятся из другого pipeline, но в проверенном коде crosswalk/равенства нет. Полную миграцию блокирует до сверки, какие из этих строк продолжают представлять продаваемые варианты и где они отображаются в V2.

### Повторяющийся `ID_nom` и несколько Barcode

**CONFIRMED, High.** Reconciliation индексирует каждую запись по `ID_nom`; если несколько записей с одним и тем же ID_nom совпали по GUID, условие успешного match требует `guidMatches.length <= 1`, иначе позиция помечается `ambiguous` с `duplicate_1c_records_on_single_key` ([sql-1c-reconciliation.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/reconcile/sql-1c-reconciliation.ts#L274), L274–294 и L333–356). Это конфликтует с подтверждённым правилом «одна номенклатура 1С может иметь несколько штрихкодов» для barcode-only дублей.

Классификатор `classifyIdNomDuplicateGroup()` отдельно распознаёт `barcode_only_conflict` ([duplicate-id-nom.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/reconcile/duplicate-id-nom.ts#L160), L160–220), а family reconciliation умеет объединить все Barcode по одному ID_nom (L209–218). Однако catalog importer не использует переданный `idNomDuplicates` при выборе записей: он строит `Map` по `ID_nom`, где для повторяющегося ключа остаётся последняя строка ([catalog-importer.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/catalog-importer.ts#L115), L115–121 и L622–629). При записи variant добавляется Barcode только из этой одной строки (L878–880; standalone L965–967). Иными словами, family matching может увидеть объединённый набор штрихкодов, но сам импорт сохраняет только один 1С Barcode; non-family matching может отправить позицию в ambiguous.

Тест «multiple barcode aliases» проверяет разные MySQL и 1C Barcode в двух исходных полях, но не два 1C-рядка с одинаковым ID_nom; тест дубликатов проверяет конфликтующее поле Brand и ожидает skip ([catalog-importer.test.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/tests/catalog-importer.test.ts#L183), L183–218). Указанные в контексте 689 barcode-only групп поэтому не покрыты этой проверкой.

### Конфликт mapping 1С

**CONFIRMED, High при повторном/смешанном импорте.** В family- и standalone-пути результат `resolveIdentity(... sourceSystem: '1c', entityType: 'nomenclature' ...)` вызывается, но не проверяется ([catalog-importer.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/catalog-importer.ts#L814), L814–826 и L937–945). `resolveIdentity` умеет возвращать `conflict`, если внешний ID уже указывает на другой internal ID ([mappings.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/mappings.ts#L86), L92–126). Импортёр при этом может продолжить запись variant и отчитаться об успешной карточке, хотя mapping `1c/nomenclature` остался на другом variant. Свежая пустая test DB не воспроизводит этот путь; повторный импорт или пересекающиеся семьи — воспроизводят.

### Скрытые товары и статусы

**CONFIRMED, High для cutover.** Parser сохраняет `LegacyProduct.isShown` ([mysql-catalog.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/parsers/mysql-catalog.ts#L53), L53–88). Legacy публичная выборка требует `products.isShown = 1` ([Products_model.php](../application/models/Products_model.php#L2427), L2486–2496). Но V2 importer вычисляет статус только из brand/category mapping и выставляет `active`, если они разрешились; `isShown` не участвует ([catalog-importer.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/catalog-importer.ts#L843), L843–855 и L947–959). V2 public query затем выбирает `products.status = 'active'` и товары с priced active variant ([products.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/db/queries/products.ts#L26), L26–51). Поэтому после появления price rows скрытый в legacy товар может стать публичным в V2. Сколько таких записей сопоставляется, из текущего материала неизвестно.

### Category links и частичные семьи

**CONFIRMED.** Для family категорийные ссылки собираются только из `mainPosition` (L843); функция добавляет его `categoryId` и его строки `productCategories` (L1027–1040). Остальные family members не участвуют. Если реальные члены одной семьи размечены разными категориями, их категории теряются — **UNKNOWN**, пока SQL не подтвердит такое распределение.

Если семья в целом подтверждена, но отдельные позиции не имеют однозначного match, импортёр всё равно импортирует подтверждённые позиции и может создать продукт с неполным набором вариантов; непривязанные позиции попадают в skip units (L829–831, L857–880, L638–690). Это безопаснее угадывания, но политика «публиковать подтверждённую часть семьи или оставить всю карточку в draft» не подтверждена. Проверить по семейным примерам.

### SEO slugs, aliases и canonical

**CONFIRMED.** Importer предпочитает ненумерический `uriRu`; для числового/отсутствующего URI формирует `legacy-<externalId>`, а дубли canonical slug разрешает суффиксом `-<externalId>` ([catalog-importer.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/catalog-importer.ts#L275), L275–290 и L324–340). Он записывает `uriRu` и `uriRo` в `product_slug_aliases` (L543–580). При обновлении уже существующего product `writeProductRow()` обновляет контент и статус, но не поле `products.slug` (L427–445); то есть slug при повторном импорте фактически immutable, хотя это явно не оформлено как бизнес-контракт.

В текущем V2 read path `getProductBySlug()` ищет только `products.slug` + `status='active'` и не читает `product_slug_aliases` ([products.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/db/queries/products.ts#L279), L279–300); route превращает отсутствие результата в 404 ([routes/v1/products.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/routes/v1/products.ts#L113), L113–120). Next product page сама помечает текущий `/[locale]/product/[slug]` как временный путь, а canonical/hreflang здесь намеренно не выставляет ([page.tsx](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/web/src/app/%5Blocale%5D/product/%5Bslug%5D/page.tsx#L15), L15–29). Значит, наличие записей alias пока не подтверждает сохранение доступности старых URL или redirect на canonical. Это блокирует SEO cutover, но не первую запись в test DB.

### Idempotency, media и rollback

**CONFIRMED — хорошая часть реализации.** CLI оборачивает catalog import в `db.transaction`; mapping использует блокировки и savepoint при unique-конфликте ([catalog-import.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/cli/catalog-import.ts#L60), L60–70 и L104–115; [mappings.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/mappings.ts#L92), L92–171). Есть тесты на повторный импорт product/variant/mapping, rollback и dry-run без записей ([catalog-importer.test.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/tests/catalog-importer.test.ts#L297), L297–340 и L365–414).

**CONFIRMED, Medium.** `writeMediaRows()` вставляет каждую ненулевую картинку повторно и не ищет существующую запись ([catalog-importer.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/catalog-importer.ts#L584), L584–605). В `product_images` есть уникальность `object_key`, но импортёр его не устанавливает, а уникальности `(product_id, url)` нет ([product-images.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/db/schema/product-images.ts#L14), L14–43). Тест повторного импорта не проверяет images и использует fixture без изображения (test L297–320). Первый прогон в пустой БД этим не ломается; второй создаёт дубли и dry-run показывает число повторных insert, а не чистое число новых media.

### Насколько dry-run соответствует write

**CONFIRMED.** В Sprint 12B.2.1 исправлен прежний ранний выход из dry-run: ранее он показывал только 16 вариантов, ноль баркодов и ноль slug-конфликтов на запуске с реальными файлами; в документации зафиксированы и причины, и исправления ([vizaje-catalog-importer.md](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/docs/vizaje-catalog-importer.md#L104), L104–190). Текущий dry-run проходит те же ветки планирования, а тест сравнивает counts dry-run и write на синтетической mixed fixture ([catalog-importer.test.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/tests/catalog-importer.test.ts#L424), L424–468).

Это хорошее исправление, но не сертификат будущего write по недоступному реальному снимку: результат зависит от состояния mappings, brands/categories и slug уже в PostgreSQL. Отдельный bootstrap брендов/категорий — отдельная команда и отдельная транзакция; его нужно выполнить до catalog write в той же test DB. Пропуски/неизменённые mappings надо сравнивать по объектам и ключам, не только по агрегатам. Повторный media insert и проигнорированный `1C` mapping conflict остаются расхождениями между смыслом «повторяемый импорт» и фактическим результатом.

### Ограничение защитного режима записи

**CONFIRMED, High для operational safety.** По умолчанию CLI работает в dry-run, а `--write` вызывает `assertSafeImportTarget()`. Но проверка смотрит только на имя базы: оно должно содержать `test` и не содержать `prod`; host, port и точное ожидаемое имя не сверяются ([catalog-importer.ts](https://github.com/arionel0726-dev/new-brand/blob/d20b8be0c1989a61bbaeba6fbf0e311f89c864fc/apps/api/src/import/catalog-importer.ts#L247), L247–264). Поэтому имя `*_test` само по себе не доказывает, что endpoint изолирован. До любого `--write` требуется отдельно сверить фактические host/database/роль соединения; одним текущим guard это не обеспечено.

## 3. Итоговая классификация находок

| Класс / проблема | Статус, severity | Влияние | Блокирует первую запись в изолированную test DB? | Рекомендация |
|---|---|---|---|---|
| Пустой title у Brand 162, 56 ссылок — по переданным данным | **CONFIRMED-поведение / UNKNOWN-состав live**, Medium–High | Legacy не имеет title fallback; V2 пропускает бренд, товары без brand mapping становятся draft | Нет, если skip/draft ожидаемы и вывод проверяется | До cutover решить: исправление source, явный mapping на валидный бренд или согласованный безбрендовый режим. Не создавать бренд с придуманным именем автоматически. |
| Dangling category parents и descendants | **CONFIRMED**, Medium | Нет в обычном legacy tree; отдельная категория может работать по прямому URI. V2 правильно не подменяет parent | Нет | Для затронутых ID получить product reference counts и владельца дерева; явно решить repair/root/quarantine. `parent_id=0` не исправлять. |
| Потеря `isShown` товара | **CONFIRMED**, High | Скрытые legacy товары после price/inventory migration могут стать `active` | Не блокирует исследовательский write; обязателен hidden-fixture check | Перенести visibility в draft/inactive/archived по согласованному правилу и показать её в отчёте импорта. До production cutover блокирует. |
| Старые `products_variable` не читаются этим импортёром | **CONFIRMED по коду / UNKNOWN по parity**, High | Legacy storefront строит доступные варианты из этой таблицы; в новом импорте не найдено сопоставление всех таких строк с 1С-позициями | Не блокирует тест importer на синтетических rows; блокирует принятие полного каталога | Сверить по legacy product ID + SKU/Barcode состав продаваемых `products_variable` с generated family variants; отдельно учесть variant images/status/price/inventory или показать подтверждённый отдельный importer. |
| Повторяющийся ID_nom: ambiguous в reconciliation, last-row-only в import, Barcode теряются | **CONFIRMED**, High | Потерянные barcode aliases и лишние ambiguous skips; возможен выбор произвольного content row | **Условно:** можно тестировать curated fixtures; полный real-data write не считать валидным до исправления | Для barcode-only дублей объединять Barcode по ID_nom; поля content/цены классифицировать отдельно; conflicting/price-conflict не выбирать по порядку. Добавить тест из нескольких 1C строк с одним ID_nom. |
| Игнорирование `1c/nomenclature` mapping conflict | **CONFIRMED**, High при существующих/conflicting mappings | Успешный результат может не соответствовать реально сохранённой 1C identity | Чистая БД — нет; reused test DB/второй запуск с конфликтом — да, до проверки результата | Проверять resolution outcome; конфликт делать явным skip/rollback, а не продолжать. Добавить family и standalone tests. |
| `products == variants` в переданной сводке | **INFERRED**, High как требование family import | План, вероятно, не объединяет реальную многовариантную номенклатуру; точную причину без summary семейств не установить | Блокирует принятие полного real-data dry-run, не синтетический write | Сверить точные названия полей, `confirmedFamilies`, `confirmedPositions`, число вариантов в каждой успешной unit; проверить 2–3 реальные семьи end-to-end до полного write. |
| Повторно вставляемые product images | **CONFIRMED**, Medium | Повторный импорт увеличивает число одинаковых image rows | Первый чистый прогон — нет; повторный — да, пока не проверена/исправлена идемпотентность | Сделать natural-key lookup/unique constraint по источнику изображения; проверить второй write и dry-run на уже загруженных images. |
| Семейные category links берутся только с mainPosition | **CONFIRMED-механика / UNKNOWN-семантика**, Medium | Участники семьи с другой категорией могут потерять эту связь | Нет для чистого теста, но нужны fixture/sample проверки | Сравнить category IDs участников реальных семей; утвердить правило union/главной категории до write полного каталога. |
| URL aliases записываются, но lookup/redirect не подтверждён | **CONFIRMED по текущему V2 lookup**, High для SEO cutover | Старые URI могут приводить к 404; канонический URL может не обновляться при повторном импорте | Нет для тестовой записи; блокирует SEO cutover | Подключить alias lookup с redirect на canonical, обеспечить locale и сохранить старые URI. Согласовать, immutable ли `products.slug`: update путь импортёра slug не обновляет (catalog-importer.ts L427–445). |
| Проверка test DB только по имени | **CONFIRMED**, High для безопасности | Удалённая/неизолированная БД с именем, содержащим `test`, проходит текущую проверку | **Да, пока target отдельно не верифицирован** | Проверить host, database, роль и изоляцию непосредственно перед write; усилить allowlist. Не полагаться на подстроку в имени. |
| Prices/inventory/deletion policy отсутствуют в этом importer | **CONFIRMED по scope этого файла / UNKNOWN по общему V2 pipeline** | Этот шаг сам по себе не создаёт полноценный продаваемый каталог; товары без price row не показываются публичными запросами | Нет для структурного test write; блокирует production readiness | Проверить отдельный importer цен/остатков/архивации и порядок snapshot jobs. Не считать импорт продуктов завершённым переносом каталога. |

## 4. Что выглядит правильной осторожностью, а не overengineering

1. Не создавать название для пустого бренда и не придумывать отсутствующего родителя категории — лучше явный skip и ручное решение.
2. Не объединять семьи только потому, что совпали `SKU`/`VariativeNomenclature` или число записей; текущий код требует corroborating GUID/barcode и отдельно маркирует конфликтующие сигналы. Глобальная уникальность `VariativeNomenclature` в бизнес-правилах не подтверждена.
3. Разделять `Product` и `ProductVariant`, сохранять legacy идентификаторы в namespaced mappings, проверять barcode collision и slug collision — разумная схема для миграции при наличии повторяющихся ключей.
4. `parent_id = 0` как root sentinel — исторический формат, который V2 уже трактует правильно.

Следовательно, сама reconciliation-сложность оправдана. Но текущие агрегаты не показывают, что это решение уже распознало реальные семейства; нужно подтвердить работу на исходных примерах, а не упрощать сопоставление до небезопасного match по одному group code.

## 5. Что V2 должна сохранить и чего не повторять

**Сохранить:**

- старый URI и locale как alias, если URL должен пережить переезд; канонический redirect должен быть проверен end-to-end;
- связь `ID_nom` с variant и возможность нескольких Barcode на одной номенклатурной позиции;
- видимость legacy (`isShown`) и категорийное дерево без вымышленных родителей;
- стабильные external mappings и транзакционность записи.

**Не повторять/не допускать:**

- выбор последней строки JSON как правила разрешения дублей;
- потерю части Barcode при группировке по `ID_nom`;
- активацию скрытых товаров только потому, что найдены brand/category mappings;
- успешный статус при проигнорированном конфликте identity mapping;
- накопление одинаковых изображений при повторном импорте;
- production safety, основанную только на названии базы;
- объявление dry-run «проверенным» только на основании синтетических fixtures.

## 6. Нерешённые вопросы и необходимые проверки

1. Переданные `5 972 / 5 972`: какие именно поля summary сравнивались? Сколько `confirmedFamilies`, сколько из них импортируется с 2+ variant IDs? Какие семейства не совпали и по какой причине?
2. Из 689 barcode-only duplicate ID_nom какие имеют legacy GUID/barcode match? Должны ли все их Barcode становиться aliases одного variant? Какие группы классифицированы как conflicting/price conflict и что считать source of truth?
3. Должны ли скрытые legacy товары/варианты импортироваться как `draft`, `archived` или вовсе не переноситься? Что означает `isShown=0` в текущем бизнес-процессе?
4. Для Brand 162: это действительно пустой title в записи или ошибка/кодировка выгрузки? Есть ли утверждённое бизнесом название для fallback? Не угадывать его.
5. Для категорий с отсутствующими родителями: исправлять source, сохранять category как root или исключить? Сколько показанных/продаваемых продуктов и прямых SEO URL связано с каждой категорией?
6. У всех участников вариативной семьи должна быть одна категория или V2 должна сохранять union их категорий? Допустимо ли опубликовать семью только с частью подтверждённых вариантов?
7. Какая система управляет ценами и остатками, как задаётся актуальность snapshot и как обрабатывается исчезновение товара? Этот importer этих полей не пишет; его отсутствие само по себе не объясняет, что делает отдельный pipeline.
8. Какой точный V2 URL должен стать canonical: текущий `/[locale]/product/[slug]` явно помечен в коде как placeholder; legacy строит `/{locale}/{catalog-uri}/{category-uri}/{product-uri}`. Где реализуются lookup по alias, 301 и canonical/hreflang?
9. Сверить актуальный запуск с историческим отчётом importer: в V2 docs зафиксирован прежний quarantine `374` против ранее сообщённого `403`, а прежние `variantsWritten=16` были объяснены dry-run bug и исправлены. Это исторические разные отчёты/версии; без timestamp, checksum входов и полного актуального summary их нельзя сравнивать как одну метрику.

## 7. Минимальные критерии перед первым и полным write

**Перед первым `--write` в test DB:**

1. Вручную подтвердить конкретные host, database, роль и пустоту/изоляцию БД; текущий `assertSafeImportTarget()` этого не доказывает.
2. Сначала записать bootstrap brands/categories в той же test DB, затем каталог; обе команды по отдельности транзакционные, но это не одна общая транзакция.
3. На репрезентативных fixtures проверить hidden item, barcode-only duplicate ID_nom, 1C mapping conflict, несколько категорий в семье, images и второй повторный write.
4. Для первого тестового raw-data write заранее принять ожидаемые draft/skips и сохранить сводку/причины без персональных данных.

**Перед production cutover:** обязательно закрыть visibility, duplicate-ID/barcode, ignored mapping conflict, media idempotency, семейные агрегаты, URL aliases/redirects и отдельный контракт цен/остатков/удаления. На текущем материале ответ на главный вопрос такой: **reconciliation не выглядит неоправданной сложностью, но реальные dry-run totals пока не подтверждают, что поддержка вариантов работает на реальном каталоге; часть рисков уже доказана кодом самого importer и не объясняется проблемами legacy SQL.**

