<main class="page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>" class="breacrums__name"><?= $menu['all'][1]->breadcrumbTitle ?></a>
                </li>
                <li class="breacrums__item">
                    <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>"
                       class="breacrums__name"><?= $menu['all'][3]->title ?></a>
                </li>
                <?php if (!empty($cat_parent2)) { ?>
                    <li class="breacrums__item">
                        <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $cat_parent2->uri ?>"
                           class="breacrums__name"><?= $cat_parent2->title ?></a>
                    </li>
                <?php } ?>
                <?php if (!empty($cat_parent1)) { ?>
                    <li class="breacrums__item">
                        <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $cat_parent1->uri ?>"
                           class="breacrums__name"><?= $cat_parent1->title ?></a>
                    </li>
                <?php } ?>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= $page_title ?></p>
                </li>
            </ul>
        </div>
    </section>
    <section class="catalog">
        <div class="catalog__container _container">
            <div class="catalog__head head-catalog">
                <h1 class="head-catalog__title"><?= $page_title ?></h2>
                <p class="head-catalog__counts"><?= str_replace('{count}', $products_count, FOUND_PRODUCTS) ?></p>
            </div>
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
                    <form action="javascript:;" class="sidebar-catalog__content category_filter">
                        <input type="hidden" name="category_id" value="<?= $category->id ?>">
                        <?php if (!empty($children)) { ?>
                            <div class="sidebar-catalog__section">
                                <div class="sidebar-catalog__head head-sidebar-catalog">
                                    <p class="head-sidebar-catalog__name"><?= CATEGORIES ?></p>
                                    <div class="head-sidebar-catalog__arrow">
                                        <picture>
                                            <source srcset="/app/img/icons/arrow-down.svg" type="image/webp">
                                            <img src="/app/img/icons/arrow-down.svg" alt="Arrow"></picture>
                                    </div>
                                </div>
                                <div class="sidebar-catalog__body" style="display: none;">
                                    <div class="sidebar-catalog__list list-sidebar-catalog">
                                        <?php foreach ($children as $child) { ?>
                                            <div class="list-sidebar-catalog__item">
                                                <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $child->uri ?>"
                                                   class="checkbox__url"><span
                                                            class="checkbox__name"><?= $child->title ?></span> <span
                                                            class="checkbox__value"><?= $child->count ?></span></a>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (!empty($brands)) { ?>
                            <div class="sidebar-catalog__section">
                                <div class="sidebar-catalog__head head-sidebar-catalog">
                                    <p class="head-sidebar-catalog__name"><?= BRAND ?></p>
                                    <?php if (!empty($_GET['brand'])) { ?>
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
                                        foreach ($brands as $brand) { ?>
                                            <?php if (!empty($_GET['brand'])) {
                                                $br_checked = '';
                                                foreach ($_GET['brand'] as $value) {
                                                    if ($value == $brand->id) {
                                                        $br_checked = 'checked';
                                                    }
                                                }
                                            } ?>
                                            <div class="list-sidebar-catalog__item">
                                                <label class="checkbox">
                                                    <input class="checkbox__input" type="checkbox" <?= $br_checked ?>
                                                           value="<?= $brand->id ?>" name="brands">
                                                    <div class="checkbox__text"><span
                                                                class="checkbox__name"><?= $brand->title ?></span>
                                                        <span class="checkbox__value"><?= $brand->count ?></span></div>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="sidebar-catalog__section sex_filter">
                            <div class="sidebar-catalog__head head-sidebar-catalog">
                                <p class="head-sidebar-catalog__name"><?= SEX ?></p>
                                <?php if (!empty($_GET['sex'])){?>
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
                                    <?php $sex_checked = '';
                                    if (!empty($_GET['sex'])) {
                                        $sex_checked = '';
                                        foreach ($_GET['sex'] as $value) {
                                            if ($value == 'Женский') {
                                                $sex_checked = 'checked';
                                            }
                                        }
                                    } ?>
                                    <div class="list-sidebar-catalog__item">
                                        <label class="checkbox">
                                            <input class="checkbox__input" type="checkbox" <?= $sex_checked ?>
                                                   value="Женский" name="sex">
                                            <div class="checkbox__text"><span
                                                        class="checkbox__name"><?= FEMEIE ?></span>
                                            </div>
                                        </label>
                                    </div>

                                    <?php $sex_checked = '';
                                    if (!empty($_GET['sex'])) {
                                        $sex_checked = '';
                                        foreach ($_GET['sex'] as $value) {
                                            if ($value == 'Мужской') {
                                                $sex_checked = 'checked';
                                            }
                                        }
                                    } ?>
                                    <div class="list-sidebar-catalog__item">
                                        <label class="checkbox">
                                            <input class="checkbox__input" type="checkbox" <?= $sex_checked ?>
                                                   value="Мужской" name="sex">
                                            <div class="checkbox__text"><span
                                                        class="checkbox__name"><?= MALE ?></span>
                                            </div>
                                        </label>
                                    </div>

                                    <?php $sex_checked = '';
                                    if (!empty($_GET['sex'])) {
                                        $sex_checked = '';
                                        foreach ($_GET['sex'] as $value) {
                                            if ($value == 'Унисекс') {
                                                $sex_checked = 'checked';
                                            }
                                        }
                                    } ?>
                                    <div class="list-sidebar-catalog__item">
                                        <label class="checkbox">
                                            <input class="checkbox__input" type="checkbox" <?= $sex_checked ?>
                                                   value="Унисекс" name="sex">
                                            <div class="checkbox__text"><span
                                                        class="checkbox__name"><?= UNISEX ?></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

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
<!--                        <button class="send_filter _close_filter" type="submit">--><?//= APPLY ?><!--</button>-->
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
                                <div class="head-main-catalog__select category_sorder">
                                    <select name="sorder">
                                        <option value="1" <?php if (!empty($_GET['sort']) && $_GET['sort'] == 1) echo 'selected'; ?>><?= SORTING1 ?></option>
                                        <option value="2" <?php if (!empty($_GET['sort']) && $_GET['sort'] == 2) echo 'selected'; ?>><?= SORTING2 ?></option>
                                        <option value="3" <?php if (!empty($_GET['sort']) && $_GET['sort'] == 3) echo 'selected'; ?>><?= SORTING3 ?></option>
                                        <option value="4" <?php if (!empty($_GET['sort']) && $_GET['sort'] == 4) echo 'selected'; ?>><?= AT_MAX_DISCOUNT ?></option>
                                        <!--                                        <option value="4" -->
                                        <?php //if (!empty($_GET['sort']) && $_GET['sort'] == 4) echo 'selected'; ?> <? //= SORTING4 ?><!--</option>-->
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
