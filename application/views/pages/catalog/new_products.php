<main class="page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>" class="breacrums__name"><?= $home_bc_title ?></a>
                </li>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= $menu['all'][34]->title ?></p>
                </li>
            </ul>
        </div>
    </section>
    <section class="catalog">
        <div class="catalog__container _container">
            <div class="catalog__head head-catalog">
                <h1 class="head-catalog__title"><?= $menu['all'][34]->title ?></h2>
                <p class="head-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
            </div>
            <div class="catalog__body">
                <div class="catalog__main main-catalog">
                    <div class="main-catalog__head head-main-catalog">
                        <P class="head-main-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
                        <div class="head-main-catalog__other">
                            <div class="head-main-catalog__column">
                                <div class="head-main-catalog__select">
                                    <select name="sorder">
                                        <option value="1" selected="selected"><?= SORTING1 ?></option>
                                        <option value="2"><?= SORTING2 ?></option>
                                        <option value="3"><?= SORTING3 ?></option>
                                        <option value="4"><?= AT_MAX_DISCOUNT ?></option>
<!--                                        <option value="4">--><?//= SORTING4 ?><!--</option>-->
                                    </select>
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
            </div>
        </div>
    </section>
</main>