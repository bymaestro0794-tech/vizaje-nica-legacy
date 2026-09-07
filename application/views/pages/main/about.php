<main class="page">
    <section class="breadcrums">
        <div class="breadcrums__container _container">
            <div class="breadcrums__list">
                <div class="breadcrums__item">
                    <a href="/<?=$lclang?>" class="breadcrums__link"><?=$home_bc_title?></a>
                </div>
                <div class="breadcrums__item">
                    <span class="breadcrums__link"><?=$page_name?></span>
                </div>
            </div>
        </div>
    </section>
    <section class="about-page">
        <div class="about-page__container _container">
            <h2 class="about-page__title _title"><?=$page_name?></h2>
            <div class="about-page__content">
                <div class="about-page__image">
                    <?php $src = newthumbs($page->img, 'menu') ?>
                    <picture>
                        <source srcset="<?=$src?>" type="image/webp">
                        <img src="<?=$src?>" alt="Image"></picture>
                </div>
                <div class="about-page__body">
                    <p class="about-page__caption"><?=ABOUT_TITLE?></p>
                    <div class="about-page__description">
                        <?=$page->text?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="about-benefits">
        <div class="about-benefits__container _container">
            <div class="about-benefits__content">
                <div class="about-benefits__item">
                    <div class="about-benefits__icon">
                        <picture>
                            <source srcset="/app/img/icons/about-benefits/01.svg" type="image/webp">
                            <img src="/app/img/icons/about-benefits/01.svg" alt="Icon"></picture>
                    </div>
                    <div class="about-benefits__body">
                        <p class="about-benefits__value"><?=ABOUT_INFO1?></p>
                        <p class="about-benefits__text"><?=ABOUT_INFO1_TEXT?></p>
                    </div>
                </div>
                <div class="about-benefits__item">
                    <div class="about-benefits__icon">
                        <picture>
                            <source srcset="/app/img/icons/about-benefits/02.svg" type="image/webp">
                            <img src="/app/img/icons/about-benefits/02.svg" alt="Icon"></picture>
                    </div>
                    <div class="about-benefits__body">
                        <p class="about-benefits__value"><?=ABOUT_INFO2?></p>
                        <p class="about-benefits__text"><?=ABOUT_INFO2_TEXT?></p>
                    </div>
                </div>
                <div class="about-benefits__item">
                    <div class="about-benefits__icon">
                        <picture>
                            <source srcset="/app/img/icons/about-benefits/03.svg" type="image/webp">
                            <img src="/app/img/icons/about-benefits/03.svg" alt="Icon"></picture>
                    </div>
                    <div class="about-benefits__body">
                        <p class="about-benefits__value"><?=ABOUT_INFO3?></p>
                        <p class="about-benefits__text"><?=ABOUT_INFO3_TEXT?></p>
                    </div>
                </div>
                <div class="about-benefits__item">
                    <div class="about-benefits__icon">
                        <picture>
                            <source srcset="/app/img/icons/about-benefits/04.svg" type="image/webp">
                            <img src="/app/img/icons/about-benefits/04.svg" alt="Icon"></picture>
                    </div>
                    <div class="about-benefits__body">
                        <p class="about-benefits__value"><?=ABOUT_INFO4?></p>
                        <p class="about-benefits__text"><?=ABOUT_INFO4_TEXT?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="benefits">
        <div class="benefits__container _container">
            <div class="benefits__head">
                <h2 class="benefits__title _title-section"><?=CHOOSE_TITLE?></h2>
            </div>
            <div class="benefits__content">
                <div class="benefits__item">
                    <div class="benefits__icon">
                        <picture>
                            <source srcset="/app/img/icons/benefits/01.svg" type="image/webp">
                            <img src="/app/img/icons/benefits/01.svg" alt="Icon"></picture>
                    </div>
                    <p class="benefits__name"><?=CHOOSE_INFO1?></p>
                </div>
                <div class="benefits__item">
                    <div class="benefits__icon">
                        <picture>
                            <source srcset="/app/img/icons/benefits/02.svg" type="image/webp">
                            <img src="/app/img/icons/benefits/02.svg" alt="Icon"></picture>
                    </div>
                    <p class="benefits__name"><?=CHOOSE_INFO2?></p>
                </div>
                <div class="benefits__item">
                    <div class="benefits__icon">
                        <picture>
                            <source srcset="/app/img/icons/benefits/03.svg" type="image/webp">
                            <img src="/app/img/icons/benefits/03.svg" alt="Icon"></picture>
                    </div>
                    <p class="benefits__name"><?=CHOOSE_INFO3?></p>
                </div>
                <div class="benefits__item">
                    <div class="benefits__icon">
                        <picture>
                            <source srcset="/app/img/icons/benefits/04.svg" type="image/webp">
                            <img src="/app/img/icons/benefits/04.svg" alt="Icon"></picture>
                    </div>
                    <p class="benefits__name"><?=CHOOSE_INFO4?></p>
                </div>
            </div>
        </div>
    </section>
</main>