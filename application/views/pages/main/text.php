<main class="page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>" class="breadcrums__link"><?= $home_bc_title ?></a>
                </li>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= $page_name ?></p>
                </li>
            </ul>
        </div>
    </section>
    <section class="catalog">
        <div class="catalog__container _container">
            <div class="catalog__head head-catalog">
                <h2 class="head-catalog__title"><?= $page_name ?></h2>
            </div>
            <div class="catalog__stock stock-catalog">
                <div class="stock-catalog__description desc_text">
                    <?=$page->desc?>
                </div>
                <div class="stock-catalog__description">
                    <?=$page->text?>
                </div>
            </div>
        </div>
    </section>
</main>