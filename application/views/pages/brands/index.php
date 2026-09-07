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
            <div class="brands__head head-brands">
                <div class="head-brands__main">
                    <h1 class="head-brands__title">
                        <?= $page_name ?>
                    </h1>

                    <span class="head-brands__counts">
                        <?= $brands_count ?>
                    </span>
                </div>

                <form action="" class="head-brands__search search-head-brands">
                    <input
                        type="search"
                        placeholder="<?= ENTER_THE_TITLE ?>"
                        name="search-brand"
                        class="search-head-brands__input"
                        autocomplete="off"
                        aria-label="<?= ENTER_THE_TITLE ?>"
                    >

                    <button
                        type="submit"
                        class="search-head-brands__btn"
                        aria-label="Search"
                    >
                        <img
                            src="/app/img/icons/search.svg"
                            alt=""
                            aria-hidden="true"
                        >
                    </button>
                </form>
            </div>

            <div class="brand-letter">
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
                            <?php if (!is_numeric($key)) { ?>
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
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </section>
                    <?php } ?>

                    <?php foreach ($brands as $key => $brand) { ?>
                        <?php if (!is_numeric($key)) { ?>
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
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </section>
                        <?php } ?>
                    <?php } ?>
                </div>

                <p class="brands__empty" hidden>
                    Ничего не найдено
                </p>
            </div>
        </div>
    </section>
</main>