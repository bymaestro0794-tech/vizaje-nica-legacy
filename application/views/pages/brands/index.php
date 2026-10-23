<main class="page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>" class="breadcrums__link">
                        <?= $home_bc_title ?>
                    </a>
                </li>

                <li class="breacrums__item">
                    <p class="breacrums__name">
                        <?= $page_name ?>
                    </p>
                </li>
            </ul>
        </div>
    </section>

    <section class="brands">
        <div class="brands__container _container">
            <h1 class="head-brands__title _visually-hidden">
                <?= $page_name ?>
            </h1>

            <div class="brand-letter" aria-label="<?= $lclang === 'ro' ? 'Navigare alfabetică' : 'Навигация по алфавиту' ?>">
                <div class="brand-letter__content">
                    <div class="brand-letter__list">
                        <?php if (!empty($numeric)) { ?>
                            <a
                                href="#_brands-row-09"
                                class="brand-letter__link _goto-block"
                                data-brand-letter="09"
                            >
                                0–9
                            </a>
                        <?php } ?>

                        <?php foreach ($brands as $key => $brand) { ?>
                            <?php if (!is_numeric($key) && trim((string) $key) !== '') { ?>
                                <a
                                    href="#_brands-row-<?= strtolower($key) ?>"
                                    class="brand-letter__link _goto-block"
                                    data-brand-letter="<?= strtolower($key) ?>"
                                >
                                    <?= $key ?>
                                </a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="brands__layout">
                <aside class="brands__favorites" aria-label="<?= $lclang === 'ro' ? 'Branduri favorite' : 'Избранные бренды' ?>">
                    <h2 class="brands__favorites-title">
                        <?= $lclang === 'ro' ? 'Favorite' : 'Избранные' ?>
                    </h2>
                    <p class="brands__favorites-empty">
                        <?= $lclang === 'ro'
                            ? 'Aici nu este nimic încă. Adăugați branduri la favorite.'
                            : 'Пока здесь ничего нет. Добавляйте бренды в избранное.' ?>
                    </p>
                    <div class="brands__favorites-list" data-favorites-list></div>
                </aside>

                <div class="brands__directory">
                    <div class="brands__head head-brands">
                        <form action="" class="head-brands__search search-head-brands">
                            <input
                                type="search"
                                placeholder="<?= $lclang === 'ro' ? 'Caută branduri' : 'Найти бренды' ?>"
                                name="search-brand"
                                class="search-head-brands__input"
                                autocomplete="off"
                                aria-label="<?= $lclang === 'ro' ? 'Caută branduri' : 'Найти бренды' ?>"
                            >

                            <button
                                type="submit"
                                class="search-head-brands__btn"
                                aria-label="<?= $lclang === 'ro' ? 'Caută' : 'Найти' ?>"
                            >
                                <img
                                    src="/app/img/icons/search.svg"
                                    alt=""
                                    aria-hidden="true"
                                >
                            </button>
                        </form>
                    </div>

                    <div class="brands__content">
                        <div class="brands__rows">
                    <?php if (!empty($numeric)) { ?>
                        <section
                            id="_brands-row-09"
                            class="brands__row _brands-row-09"
                            data-brand-section="09"
                        >
                            <div class="brands__letter">
                                0–9
                            </div>

                            <div class="brands__body">
                                <div class="brands__column">
                                    <?php foreach ($brands as $key => $brand) { ?>
                                        <?php if (is_numeric($key)) { ?>
                                            <?php foreach ($brand as $value) { ?>
                                                <div
                                                    class="brands__item"
                                                    data-brand-id="<?= html_escape((string) $value->uri) ?>"
                                                    data-brand-title="<?= html_escape((string) $value->title) ?>"
                                                    data-brand-href="/<?= html_escape($lclang) ?>/<?= html_escape($menu['all'][3]->uri) ?>/<?= html_escape((string) $value->uri) ?>"
                                                    data-brand-name="<?= htmlspecialchars(
                                                        mb_strtolower($value->title),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >
                                                    <a
                                                        href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $value->uri ?>"
                                                        class="brands__link"
                                                    >
                                                        <?= $value->title ?>
                                                    </a>
                                                    <button class="brands__favorite-toggle" type="button" data-brand-favorite data-label-add="<?= $lclang === 'ro' ? 'Adaugă la favorite' : 'Добавить в избранное' ?>" data-label-remove="<?= $lclang === 'ro' ? 'Elimină din favorite' : 'Удалить из избранного' ?>" aria-label="<?= $lclang === 'ro' ? 'Adaugă la favorite' : 'Добавить в избранное' ?>" aria-pressed="false">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.8c0 4.2-8.8 10-8.8 10s-8.8-5.8-8.8-10A4.6 4.6 0 0 1 12 6.2a4.6 4.6 0 0 1 8.8 2.6Z"/></svg>
                                                    </button>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </section>
                    <?php } ?>

                    <?php foreach ($brands as $key => $brand) { ?>
                        <?php if (!is_numeric($key) && trim((string) $key) !== '') { ?>
                            <section
                                id="_brands-row-<?= strtolower($key) ?>"
                                class="brands__row _brands-row-<?= strtolower($key) ?>"
                                data-brand-section="<?= strtolower($key) ?>"
                            >
                                <div class="brands__letter">
                                    <?= $key ?>
                                </div>

                                <div class="brands__body">
                                    <div class="brands__column">
                                        <?php foreach ($brand as $value) { ?>
                                            <div
                                                class="brands__item"
                                                    data-brand-id="<?= html_escape((string) $value->uri) ?>"
                                                    data-brand-title="<?= html_escape((string) $value->title) ?>"
                                                    data-brand-href="/<?= html_escape($lclang) ?>/<?= html_escape($menu['all'][3]->uri) ?>/<?= html_escape((string) $value->uri) ?>"
                                                data-brand-name="<?= htmlspecialchars(
                                                    mb_strtolower($value->title),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >
                                                <a
                                                    href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $value->uri ?>"
                                                    class="brands__link"
                                                >
                                                    <?= $value->title ?>
                                                </a>
                                                    <button class="brands__favorite-toggle" type="button" data-brand-favorite data-label-add="<?= $lclang === 'ro' ? 'Adaugă la favorite' : 'Добавить в избранное' ?>" data-label-remove="<?= $lclang === 'ro' ? 'Elimină din favorite' : 'Удалить из избранного' ?>" aria-label="<?= $lclang === 'ro' ? 'Adaugă la favorite' : 'Добавить в избранное' ?>" aria-pressed="false">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.8c0 4.2-8.8 10-8.8 10s-8.8-5.8-8.8-10A4.6 4.6 0 0 1 12 6.2a4.6 4.6 0 0 1 8.8 2.6Z"/></svg>
                                                    </button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </section>
                        <?php } ?>
                    <?php } ?>
                </div>

                        </div>
                        <p class="brands__empty" hidden>
                            <?= $lclang === 'ro' ? 'Nu am găsit branduri' : 'Бренды не найдены' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
