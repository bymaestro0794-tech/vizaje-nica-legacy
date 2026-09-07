<main class="page">
    <section class="breadcrums">
        <div class="breadcrums__container _container">
            <div class="breadcrums__list">
                <div class="breadcrums__item">
                    <a href="/<?= $lclang ?>" class="breadcrums__link"><?= $home_bc_title ?></a>
                </div>
                <div class="breadcrums__item">
                    <a href="/<?= $lclang ?>/brands"
                       class="breadcrums__link"><?= $menu['all'][26]->title ?></a>
                </div>
                <div class="breadcrums__item">
                    <span class="breadcrums__link"><?= $breadcrumb_title ?></span>
                </div>
            </div>
        </div>
    </section>
    <section class="collections">
        <div class="collections__container _container">
            <h1 class="collections__title _title"><?= $h1_title ?></h1>
            <div class="collections__content">
                <div class="collections__main main-collections">
                    <div class="main-collections__brand brand-main-collections">
                        <div class="brand-main-collections__logo">
                            <?php $src = newthumbs($brand->img, 'brands') ?>
                            <picture>
                                <source srcset="<?=$src?>" type="image/webp">
                                <img src="<?=$src?>" alt="Logo-brand"></picture>
                        </div>
                        <div class="brand-main-collections__body">
                            <div class="brand-main-collections__description">
                                <?= $brand->text ?>
                            </div>
                            <?php if (!empty(trim(strip_tags($brand->seo_text)))) : ?>
                                <div class="brand-main-collections__description">
                                    <?= $brand->seo_text ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="collections">
        <div class="collections__container _container">
            <div class="collections__content">
                <?php if (!empty($products)) { ?>
                    <div class="main-catalog__body_collections">
                        <div class="main-catalog__list">
                            <?php foreach ($products as $product) { ?>
                                <?php $this->load->view("layouts/pages/product_main", array('item' => $product)); ?>
                            <?php } ?>
                        </div>
                        <div class="main-catalog__footer footer-main-catalog">
                            <? $this->load->view('layouts/pages/paginator'); ?>
                            <?php $pag_info = str_replace("{numb}", $show_from . '-' . $show_to, PRODUCTS_PAG_INFO);
                            $pag_info = str_replace("{total}", $products_count, $pag_info); ?>
                            <p class="footer-main-catalog__view-count"><?= $pag_info ?></p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</main>
