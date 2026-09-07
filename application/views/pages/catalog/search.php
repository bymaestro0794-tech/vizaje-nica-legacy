<main class="page">

    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>" class="breacrums__name"><?= $home_bc_title ?></a>
                </li>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= $page_title ?></p>
                </li>
            </ul>
        </div>
    </section>



    <section class="collections">

        <div class="collections__container _container">
            <div class="catalog__head head-catalog">
                <h2 class="head-catalog__title"><?= $page_title ?></h2>
            </div>

            <?php if (!empty($products)) { ?>
            <div class="catalog__main main-catalog">
                <div class="main-catalog__head head-main-catalog">
                    <P class="head-main-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
                    <div class="head-main-catalog__other">
                        <div class="head-main-catalog__column">
                            <a href="#" class="head-main-catalog__filter"><?= FILTERS ?></a>
                        </div>
                        <div class="head-main-catalog__column">
                            <div class="head-main-catalog__select category_sorder">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="products_catalog">
                    <div class="main-catalog__body">
                        <?php foreach ($products as $product) { ?>
                            <?php $this->load->view("layouts/pages/product_main", array('item' => $product)); ?>
                        <?php } ?>
                    </div>
                    <div class="main-catalog__paggination paggination-main-catalog">
                        <? $this->load->view('layouts/pages/paginator'); ?>
                    </div>
                </div>
            </div>
            <?php } ?>

        </div>
    </section>
</main>