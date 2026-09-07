<main class="page">
    <section class="vn-stores">
        <div class="vn-stores__container _container">

            <div class="vn-stores__breadcrumbs">
                <a href="/<?= $lclang ?>">
                    <?= $lclang === 'ro' ? 'Principală' : 'Главная' ?>
                </a>

                <span>/</span>

                <span><?= $page_name ?></span>
            </div>

            <div class="vn-stores__layout">

                <!-- Левая колонка -->
                <div class="vn-stores__content">
                    <header class="vn-stores__header">
                        <h1 class="vn-stores__title">
                            <?= $page_name ?>
                        </h1>

                        <p class="vn-stores__count">
                            <?= count($stores) ?>

                            <?= $lclang === 'ro'
                                ? 'magazine în Chișinău'
                                : 'магазинов в Кишинёве' ?>
                        </p>
                    </header>

                    <!-- Mobile switcher -->
                    <div class="vn-stores__mobile-tabs" data-store-tabs>
                        <button
                            type="button"
                            class="vn-stores__mobile-tab is-active"
                            data-store-view="list"
                        >
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M7 5H17M7 10H17M7 15H17M3 5H3.01M3 10H3.01M3 15H3.01"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <?= $lclang === 'ro' ? 'Listă' : 'Список' ?>
                        </button>

                        <button
                            type="button"
                            class="vn-stores__mobile-tab"
                            data-store-view="map"
                        >
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M10 18C10 18 16 12.5 16 7.5C16 4.19 13.31 1.5 10 1.5C6.69 1.5 4 4.19 4 7.5C4 12.5 10 18 10 18Z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                                <circle
                                    cx="10"
                                    cy="7.5"
                                    r="2"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                            </svg>

                            <?= $lclang === 'ro' ? 'Hartă' : 'Карта' ?>
                        </button>
                    </div>

                    <!-- Список магазинов -->
                    <div
                        class="vn-stores__list"
                        data-store-list
                    >
                        <?php foreach ($stores as $index => $store) { ?>
                            <?php
                                $coords = !empty($store->coords)
                                    ? $store->coords
                                    : '47.0183674,28.8516902';

                                $coordinateParts = explode(',', $coords);

                                $lat = !empty($coordinateParts[0])
                                    ? trim($coordinateParts[0])
                                    : '47.0183674';

                                $lng = !empty($coordinateParts[1])
                                    ? trim($coordinateParts[1])
                                    : '28.8516902';

                                $phoneClean = !empty($store->phone)
                                    ? preg_replace('/[^\d+]/', '', $store->phone)
                                    : '';

                                $number = str_pad(
                                    $index + 1,
                                    2,
                                    '0',
                                    STR_PAD_LEFT
                                );
                            ?>

                            <article
                                class="vn-store-card <?= $index === 0 ? 'is-active' : '' ?>"
                                data-store-card
                                data-index="<?= $index ?>"
                                data-number="<?= $number ?>"
                                data-title="<?= htmlspecialchars(
                                    $store->title,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                data-address="<?= htmlspecialchars(
                                    $store->text,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                data-time="<?= htmlspecialchars(
                                    $store->desc,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                data-phone="<?= htmlspecialchars(
                                    $store->phone,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                data-phone-clean="<?= htmlspecialchars(
                                    $phoneClean,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                data-lat="<?= htmlspecialchars(
                                    $lat,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                data-lng="<?= htmlspecialchars(
                                    $lng,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <button
                                    type="button"
                                    class="vn-store-card__select"
                                    aria-label="<?= htmlspecialchars(
                                        $store->title,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <span class="vn-store-card__number">
                                        <?= $number ?>
                                    </span>

                                    <span class="vn-store-card__main">
                                        <strong class="vn-store-card__title">
                                            <?= $store->title ?>
                                        </strong>

                                        <?php if (!empty($store->text)) { ?>
                                            <span class="vn-store-card__address">
                                                <?= $store->text ?>
                                            </span>
                                        <?php } ?>
                                    </span>

                                    <span class="vn-store-card__schedule">
                                        <?php if (!empty($store->desc)) { ?>
                                            <span class="vn-store-card__status">
                                                <?= $lclang === 'ro'
                                                    ? 'Program'
                                                    : 'График работы' ?>
                                            </span>

                                            <span class="vn-store-card__time">
                                                <?= $store->desc ?>
                                            </span>
                                        <?php } ?>
                                    </span>

                                    <span class="vn-store-card__action">
                                        <span>
                                            <?= $lclang === 'ro'
                                                ? 'Arată pe hartă'
                                                : 'Показать на карте' ?>
                                        </span>

                                        <svg
                                            width="18"
                                            height="18"
                                            viewBox="0 0 18 18"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M4 9H14M10 5L14 9L10 13"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </span>
                                </button>
                            </article>
                        <?php } ?>
                    </div>
                </div>

                <!-- Правая колонка -->
                <div
                    class="vn-stores__map-wrap"
                    data-store-map-view
                >
                    <div
                        id="vnStoresMap"
                        class="vn-stores__map"
                    ></div>

                    <div
                        class="vn-map-panel"
                        data-map-panel
                    >
                        <button
                            type="button"
                            class="vn-map-panel__close"
                            data-panel-close
                            aria-label="<?= $lclang === 'ro'
                                ? 'Închide'
                                : 'Закрыть' ?>"
                        >
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M4 4L16 16M16 4L4 16"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>

                        <span class="vn-map-panel__number" data-panel-number>
                            01
                        </span>

                        <h2
                            class="vn-map-panel__title"
                            data-panel-title
                        ></h2>

                        <p
                            class="vn-map-panel__address"
                            data-panel-address
                        ></p>

                        <div class="vn-map-panel__time">
                            <svg
                                width="19"
                                height="19"
                                viewBox="0 0 19 19"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="9.5"
                                    cy="9.5"
                                    r="7"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                />
                                <path
                                    d="M9.5 5.5V9.8L12.5 11.5"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linecap="round"
                                />
                            </svg>

                            <div>
                                <strong data-panel-time></strong>

                                <span>
                                    <?= $lclang === 'ro'
                                        ? 'Magazin'
                                        : 'Магазин' ?>
                                </span>
                            </div>
                        </div>

                        <a
                            href="#"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="vn-map-panel__route"
                            data-panel-route
                        >
                            <span>
                                <?= $lclang === 'ro'
                                    ? 'Construiește ruta'
                                    : 'Построить маршрут' ?>
                            </span>

                            <svg
                                width="19"
                                height="19"
                                viewBox="0 0 19 19"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M16.5 2.5L8.2 16.2L6.7 10.5L1.5 7.5L16.5 2.5Z"
                                    fill="currentColor"
                                />
                            </svg>
                        </a>
                    </div>

                    <div class="vn-stores__map-footer">
                        <div class="vn-stores__map-feature">
                            <svg
                                width="32"
                                height="32"
                                viewBox="0 0 32 32"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M28 4L14.7 27L12.2 17.4L4 12.5L28 4Z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linejoin="round"
                                />
                            </svg>

                            <div>
                                <strong>
                                    <?= $lclang === 'ro'
                                        ? 'Nu ai găsit magazinul dorit?'
                                        : 'Не нашли нужный магазин?' ?>
                                </strong>

                                <a href="/<?= $lclang ?>/<?= $lclang === 'ro'
                                    ? 'contacte'
                                    : 'kontaktyi' ?>">
                                    <?= $lclang === 'ro'
                                        ? 'Scrie-ne'
                                        : 'Напишите нам' ?>
                                </a>
                            </div>
                        </div>

                        <div class="vn-stores__map-feature">
                            <svg
                                width="32"
                                height="32"
                                viewBox="0 0 32 32"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M16 29C16 29 25 20.7 25 13C25 8 21 4 16 4C11 4 7 8 7 13C7 20.7 16 29 16 29Z"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                                <circle
                                    cx="16"
                                    cy="13"
                                    r="3.3"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                            </svg>

                            <div>
                                <strong>
                                    <?= $lclang === 'ro'
                                        ? 'Ridicare din orice magazin'
                                        : 'Самовывоз из любого магазина' ?>
                                </strong>

                                <span>
                                    <?= $lclang === 'ro'
                                        ? 'Gratuit și comod'
                                        : 'Бесплатно и удобно' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<link
    rel="stylesheet"
    href="https://unpkg.com/maplibre-gl@5.6.1/dist/maplibre-gl.css"
>

<script
    src="https://unpkg.com/maplibre-gl@5.6.1/dist/maplibre-gl.js"
></script>

<script src="/app/js/pages/stores.js?v=3" defer></script>
