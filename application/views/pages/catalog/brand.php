<main class="page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>" class="breacrums__name"><?= $home_bc_title ?></a>
                </li>
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>"
                       class="breacrums__name"><?= $menu['all'][3]->title ?></a>
                </li>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= $brand->breadcrumb_title ?></p>
                </li>
            </ul>
        </div>
    </section>
    <section class="catalog">
        <div class="catalog__container _container">
            <div class="catalog__head head-catalog">
                <h1 class="head-catalog__title"><?= $brand->h1_title ?></h1>
                <p class="head-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
            </div>
            <?php if (!empty($brand->img) && 1==2) { ?>
                <?php $src = newthumbs($brand->img, 'brands'); ?>
                <div class="catalog__stock stock-catalog">
                    <div class="stock-catalog__banner">
                        <picture>
                            <source srcset="<?= $src ?>" type="image/webp">
                            <img src="<?= $src ?>" alt="<?= htmlspecialchars($brand->title, ENT_QUOTES, 'UTF-8') ?>"
                                 class="stock-catalog__banner_pc">
                        </picture>
                        <picture>
                            <source srcset="<?= $src ?>" type="image/webp">
                            <img src="<?= $src ?>" alt="<?= htmlspecialchars($brand->title, ENT_QUOTES, 'UTF-8') ?>"
                                 class="stock-catalog__banner_notebook">
                        </picture>
                        <picture>
                            <source srcset="<?= $src ?>" type="image/webp">
                            <img src="<?= $src ?>" alt="<?= htmlspecialchars($brand->title, ENT_QUOTES, 'UTF-8') ?>"
                                 class="stock-catalog__banner_tab">
                        </picture>
                        <picture>
                            <source srcset="<?= $src ?>" type="image/webp">
                            <img src="<?= $src ?>" alt="<?= htmlspecialchars($brand->title, ENT_QUOTES, 'UTF-8') ?>"
                                 class="stock-catalog__banner_mob">
                        </picture>
                    </div>
                </div>
            <?php } ?>
            <div class="catalog__body">
                <div class="catalog__sidebar sidebar-catalog">
                    <div class="sidebar-catalog__top top-sidebar-catalog">
                        <h3 class="top-sidebar-catalog__title"><?= FILTERS ?></h3>
                        <div class="top-sidebar-catalog__close">
                            <picture>
                                <source srcset="/app/img/icons/close-filter.svg" type="image/webp">
                                <img src="/app/img/icons/close-filter.svg" alt="Close"></picture>
                        </div>
                    </div>
                    <form action="javascript:;" class="sidebar-catalog__content brand_filter">
                        <input type="hidden" name="brands_filter" value="1">
                        <input type="hidden" name="brands" value="<?= $brand->id ?>">
                        <?php if (!empty($categoryes_filter)) { ?>
                            <?php $br_checked = '';
                            usort($categoryes_filter, function($a, $b) {
                                return $b->count <=> $a->count;
                            });
                            foreach ($categoryes_filter as $category) { ?>
                                <?php if (!empty($_GET['category'])) {
                                    $br_checked = '';
                                    foreach ($_GET['category'] as $value) {
                                        if ($value == $category->id) {
                                            $br_checked = 'checked';
                                        }
                                    }
                                }
                            } ?>
                            <div class="sidebar-catalog__section">
                                <div class="sidebar-catalog__head head-sidebar-catalog">
                                    <p class="head-sidebar-catalog__name"><?= CATEGORIES ?></p>
                                    <?php if (!empty($br_checked)){?>
                                    <a href="/<?= $uri1 ?>/<?= $uri2 ?>/<?= $uri3 ?>"
                                       class="head-sidebar-catalog__clear"><?= CLEAR ?></a>
                                    <?php } ?>
                                    <div class="head-sidebar-catalog__arrow">
                                        <picture>
                                            <source srcset="/app/img/icons/arrow-down.svg" type="image/webp">
                                            <img src="/app/img/icons/arrow-down.svg" alt="Arrow"></picture>
                                    </div>
                                </div>
                                <div class="sidebar-catalog__body" style="display: none;">
                                    <div class="sidebar-catalog__list list-sidebar-catalog">
                                        <?php $br_checked = '';
                                        usort($categoryes_filter, function($a, $b) {
                                            return $b->count <=> $a->count;
                                        });
                                        foreach ($categoryes_filter as $category) { ?>
                                            <?php if (!empty($_GET['category'])) {
                                                $br_checked = '';
                                                foreach ($_GET['category'] as $value) {
                                                    if ($value == $category->id) {
                                                        $br_checked = 'checked';
                                                    }
                                                }
                                            } ?>
                                            <div class="list-sidebar-catalog__item">
                                                <label class="checkbox">
                                                    <input class="checkbox__input" type="checkbox" <?= $br_checked ?>
                                                           value="<?= $category->id ?>" name="category">
                                                    <div class="checkbox__text"><span
                                                                class="checkbox__name"><?= $category->title ?></span>
                                                        <span class="checkbox__value"><?= $category->count ?></span>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="sidebar-catalog__section">
                            <div class="sidebar-catalog__head head-sidebar-catalog _active">
                                <p class="head-sidebar-catalog__name"><?= PRICE ?> <?= MDL ?></p>
                                <?php if (!empty($_GET['price_min'])){?>
                                <a href="#" class="head-sidebar-catalog__clear"><?= CLEAR ?></a>
                                <?php } ?>
                                <div class="head-sidebar-catalog__arrow">
                                    <picture>
                                        <source srcset="/app/img/icons/arrow-down.svg" type="image/webp">
                                        <img src="/app/img/icons/arrow-down.svg" alt="Arrow"></picture>
                                </div>
                            </div>
                            <div class="sidebar-catalog__body">
                                <div class="sidebar-catalog__ranger ranger-sidebar-catalog">
                                    <div class="ranger-sidebar-catalog__row">
                                        <div class="ranger-sidebar-catalog__column">
														<span class="ranger-sidebar-catalog__prefix">
															<?= FROM ?>
														</span>
                                            <input type="text" name="price-start" id="price-start"
                                                   value="<?= !empty($_GET['price_min']) ? $_GET['price_min'] : $price_min ?>"
                                                   class="ranger-sidebar-catalog__input">
                                        </div>
                                        <div class="ranger-sidebar-catalog__column">
														<span class="ranger-sidebar-catalog__prefix">
                                                            <?= TO ?>
														</span>
                                            <input type="text" name="price-end" id="price-end"
                                                   value="<?= !empty($_GET['price_max']) ? $_GET['price_max'] : $price_max ?>"
                                                   class="ranger-sidebar-catalog__input">
                                        </div>
                                    </div>
                                    <div class="ranger-sidebar-catalog__slider"></div>
                                </div>
                            </div>
                        </div>
                        <!--                        <button class="send_filter _close_filter" type="submit">-->
                        <? //=APPLY?><!--</button>-->
                    </form>
                </div>
                <div class="catalog__main main-catalog">
                    <div class="main-catalog__head head-main-catalog">
                        <P class="head-main-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
                        <div class="head-main-catalog__other">
                            <div class="head-main-catalog__column">
                                <a href="#" class="head-main-catalog__filter"><?= FILTERS ?></a>
                            </div>
                            <div class="head-main-catalog__column">
                                <div class="head-main-catalog__select brands_sorder">
                                    <input type="hidden" name="brand_id" value="<?= $brand->id ?>">
                                    <select name="sorder_brand">
                                        <option value="1" <?php if (!empty($_GET['sort']) && $_GET['sort'] == 1) echo 'selected'; ?>><?= SORTING1 ?></option>
                                        <option value="2" <?php if (!empty($_GET['sort']) && $_GET['sort'] == 2) echo 'selected'; ?>><?= SORTING2 ?></option>
                                        <option value="3" <?php if (!empty($_GET['sort']) && $_GET['sort'] == 3) echo 'selected'; ?>><?= SORTING3 ?></option>
                                        <option value="4" <?php if (!empty($_GET['sort']) && $_GET['sort'] == 4) echo 'selected'; ?>><?= SORTING4 ?></option>
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
                        <?php if (!empty(trim(strip_tags($brand->seo_text)))) : ?>
                            <div style="margin-top: 30px;">
                                <?= $brand->seo_text ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
