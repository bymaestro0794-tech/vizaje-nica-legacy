<?php
$cartProductImage = !empty($item['image'])
    ? $item['image']
    : '/public/i/null.png';

$cartProductName = !empty($item['name'])
    ? $item['name']
    : '';

$cartProductVariant = !empty($item['options'])
    ? implode(', ', $item['options'])
    : '';

$cartProductPrice = !empty($item['subtotal'])
    ? number_format(
        (float) $item['subtotal'],
        0,
        '.',
        ' '
    ) . ' ' . MDL
    : '';
?>

<main class="page">
    <section class="cart">
        <div class="cart__container _container">
            <div class="cart__head head-cart">
                <div class="head-cart__main">
                    <h2 class="head-cart__title"><?= $page_title ?></h2>
                    <span class="head-cart__counts"><?= PRODUCTS ?> <span class="items_count"><?= $total_items_cart; ?></span></span>
                </div>
                <a href="/<?= $lclang ?>/delete_all" class="head-cart__clear"><?= CLEAR ?></a>
            </div>
            <div class="cart__main main-cart">
                <?php if (!empty($cart_items)) { ?>
                    <div class="main-cart__products products-cart">
                        <?php foreach ($cart_items as $item) { ?>
                            <?php if (!empty($item['variable'])) {
                                if (!empty($item['variable']->color)) $item['variable']->title = $item['variable']->color;
                                elseif (!empty($item['variable']->VolumeVar)) $item['variable']->title = $item['variable']->VolumeVar; ?>
                                <div class="products-cart__item"
                                    data-cart-row
                                    data-rowid="<?= htmlspecialchars(
                                        $item['rowid'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-product-image="<?= htmlspecialchars(
                                        $itemImage,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-product-name="<?= htmlspecialchars(
                                        $item['name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-product-variant="<?= htmlspecialchars(
                                        $itemVariant,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-product-price="<?= htmlspecialchars(
                                        $itemPrice,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <div class="products-cart__main main-products-cart">
                                        <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item['products']->cat_uri ?>/<?= $item['products']->uri ?>"
                                           target="_blank" class="main-products-cart__image">
                                            <?php if (!empty($item['variable']->img[0]))
                                                $src = newthumbs($item['variable']->img[0]->img, 'products_variable_img');
                                            else
                                                $src = newthumbs($item['products']->img, 'products'); ?>
                                            <picture>
                                                <source srcset="<?= $src ?>" type="image/webp">
                                                <img src="<?= $src ?>" alt="Image"></picture>
                                        </a>
                                        <div class="main-products-cart__info">
                                            <p class="main-products-cart__brand"><?= $item['products']->brand_title ?></p>
                                            <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item['products']->cat_uri ?>/<?= $item['products']->uri ?>"
                                               target="_blank"
                                               class="main-products-cart__name"><?= $item['products']->title ?></a>
                                            <ul class="main-products-cart__list list-main-products-cart">
                                                <li class="list-main-products-cart__item">
                                                    <span class="list-main-products-cart__name"><?= SKU ?></span>
                                                    <span class="list-main-products-cart__value"><?= $item['variable']->SKU ?></span>
                                                </li>
                                                <li class="list-main-products-cart__item">
                                                    <span class="list-main-products-cart__name"><?= VOLUME ?></span>
                                                    <span class="list-main-products-cart__value"><?= $item['variable']->title ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="products-cart__other other-products-cart">
                                        <div class="other-products-cart__quantity">
                                            <div class="quantity">
                                                <div class="quantity__button quantity__button_minus"></div>
                                                <div class="quantity__input">
                                                    <?php if (!empty($_SESSION['isb2b'])) { ?>
                                                    <input autocomplete="off" type="text" name="counts"
                                                           value="<?= $item['qty'] ?>"
                                                           data-rowid="<?= $item['rowid'] ?>"
                                                           data-max="<?= $item['variable']->qtyWH ?>" readonly>
                                                   <?php }else{ ?>
                                                   <input autocomplete="off" type="text" name="counts"
                                                           value="<?= $item['qty'] ?>"
                                                           data-rowid="<?= $item['rowid'] ?>"
                                                           data-max="<?= $item['variable']->qty ?>" readonly>
                                                   <?php } ?>
                                                </div>
                                                <div class="quantity__button quantity__button_plus"></div>
                                            </div>
                                        </div>
                                        <div class="other-products-cart__price price-other-products-cart">
                                            <div class="price-other-products-cart__row">
                                                <span class="price-other-products-cart__name"><?= PRICE_1_ITEM ?></span>
                                                <span class="price-other-products-cart__value">
                                                    <?php if (!empty($_SESSION['isb2b'])) { ?>
                                                        <?= $item['variable']->priceWH ?> <?= MDL ?>
                                                    <?php } else { ?>
                                                        <?php if (!empty($item['variable']->discount_price)) { ?>
                                                            <span><?= $item['variable']->price ?> <?= MDL ?></span> <?= $item['variable']->discount_price ?> <?= MDL ?>
                                                        <?php } else { ?>
                                                            <?= $item['variable']->price ?> <?= MDL ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </span>
                                            </div>
                                            <?php if (empty($_SESSION['isb2b'])) { ?>
                                                <?php if (!empty($client_info) && !empty($client_info->discount) && empty($client_info->discount_price)) { ?>
                                                    <div class="price-other-products-cart__row _bonus">
                                                        <span class="price-other-products-cart__name"><?= BONUS_ACCRUAL ?></span>
                                                        <span class="price-other-products-cart__value sale_discount"> +<?= $item['variable']->price * ($client_info->discount / 100) ?></span>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                            <div class="price-other-products-cart__row _total">
                                                <span class="price-other-products-cart__name"></span>
                                                <span class="price-other-products-cart__total"><span
                                                            class="item-total"><?= $item['price'] * $item['qty'] ?></span> <?= MDL ?></span>
                                            </div>
                                        </div>
                                        <div class="other-products-cart__actions actions-other-products-cart"
                                             data-info="<?= DELETE_PRODUCTS ?>"
                                             data-prod_id="<?= $item['id'] ?>" data-rowid="<?= $item['rowid'] ?>">
                                            <a href="#"
                                               class="actions-other-products-cart__item _like <?= in_array($item['id'], $wishlist) ? '_liked' : '' ?>">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                          d="M5.76562 1.4798C5.70116 1.49105 5.49023 1.52334 5.29687 1.55161C4.78565 1.62633 4.12476 1.84926 3.56249 2.13675C1.46638 3.20841 0.132227 5.41031 0.0147116 7.99214C-0.0754759 9.97383 0.607727 11.8296 2.12591 13.7267C3.3351 15.2377 4.59477 16.4319 8.43749 19.7101C9.43007 20.5569 10.5378 21.5068 10.899 21.821C11.2603 22.1352 11.6231 22.4271 11.7053 22.4695C11.8848 22.5624 12.1212 22.5687 12.2812 22.4849C12.3799 22.4332 15.0612 20.1564 17.3897 18.1469C20.0366 15.8626 21.5564 14.289 22.5454 12.8084C23.5817 11.257 24.0609 9.65405 23.9853 7.99214C23.8367 4.72866 21.7855 2.18616 18.7969 1.56122C18.2604 1.44905 16.9578 1.44998 16.4607 1.56291C15.1933 1.85076 14.1736 2.45339 13.2357 3.4687C12.9126 3.81853 12.4719 4.42331 12.2288 4.85044C12.1185 5.04441 12.0155 5.20308 12 5.20308C11.9845 5.20308 11.8807 5.04305 11.7695 4.84748C11.4509 4.28756 10.9298 3.62503 10.4251 3.138C9.55143 2.295 8.65621 1.80764 7.53927 1.56689C7.21007 1.49592 6.01073 1.43705 5.76562 1.4798ZM7.42968 2.99709C8.69816 3.32709 9.85246 4.29173 10.6584 5.69526C10.8866 6.09267 11.1925 6.80883 11.2968 7.18983C11.4539 7.76372 12.0895 7.97151 12.4812 7.57711C12.5895 7.46794 12.6707 7.30481 12.7657 7.00514C13.079 6.01739 13.67 5.03564 14.3883 4.30969C15.2766 3.41189 16.249 2.94333 17.364 2.87559C20.1814 2.70455 22.4343 4.93509 22.5794 8.03902C22.6756 10.0955 21.7508 11.9591 19.4776 14.2895C18.3332 15.4628 17.5481 16.167 14.1191 19.0954L11.9959 20.9086L11.3534 20.3532C11.0001 20.0477 9.89882 19.1038 8.90624 18.2556C4.50295 14.4929 3.02741 12.9422 2.11227 11.1158C1.19015 9.27553 1.17806 7.25306 2.07881 5.50776C2.79187 4.12617 4.14721 3.13894 5.64843 2.90775C6.09131 2.8395 6.99904 2.88506 7.42968 2.99709Z"
                                                          fill="#909090"/>
                                                    <path class="_fill"
                                                          d="M9.5 3.5L11.5 6.5V7L12.5 6.5L14 3.5L17 2.5L20 3L21.5 4.5L23 6.5L22.5 9.5V11L20 15L16 18L14 20L12.5 21.5L9.5 20L4.5 15L1 10L1.5 6.5L3 3.5L6 2.5L9.5 3.5Z"
                                                          fill="#909090"/>
                                                </svg>
                                            </a>
                                            <a href="#" class="actions-other-products-cart__item _delete">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3.75 5.99955H2.25C2.05109 5.99955 1.86032 5.92053 1.71967 5.77988C1.57902 5.63922 1.5 5.44846 1.5 5.24955C1.5 5.05063 1.57902 4.85987 1.71967 4.71922C1.86032 4.57856 2.05109 4.49955 2.25 4.49955H8.25V2.24805C8.25 2.04913 8.32902 1.85837 8.46967 1.71772C8.61032 1.57706 8.80109 1.49805 9 1.49805H15C15.1989 1.49805 15.3897 1.57706 15.5303 1.71772C15.671 1.85837 15.75 2.04913 15.75 2.24805V4.49955H21.75C21.9489 4.49955 22.1397 4.57856 22.2803 4.71922C22.421 4.85987 22.5 5.05063 22.5 5.24955C22.5 5.44846 22.421 5.63922 22.2803 5.77988C22.1397 5.92053 21.9489 5.99955 21.75 5.99955H20.25V21.7495C20.25 21.9485 20.171 22.1392 20.0303 22.2799C19.8897 22.4205 19.6989 22.4995 19.5 22.4995H4.5C4.30109 22.4995 4.11032 22.4205 3.96967 22.2799C3.82902 22.1392 3.75 21.9485 3.75 21.7495V5.99955ZM14.25 4.49955V2.99955H9.75V4.49955H14.25ZM5.25 20.9995H18.75V5.99955H5.25V20.9995ZM9.75 17.9995C9.55109 17.9995 9.36032 17.9205 9.21967 17.7799C9.07902 17.6392 9 17.4485 9 17.2495V9.74955C9 9.55063 9.07902 9.35987 9.21967 9.21922C9.36032 9.07856 9.55109 8.99955 9.75 8.99955C9.94891 8.99955 10.1397 9.07856 10.2803 9.21922C10.421 9.35987 10.5 9.55063 10.5 9.74955V17.2495C10.5 17.4485 10.421 17.6392 10.2803 17.7799C10.1397 17.9205 9.94891 17.9995 9.75 17.9995ZM14.25 17.9995C14.0511 17.9995 13.8603 17.9205 13.7197 17.7799C13.579 17.6392 13.5 17.4485 13.5 17.2495V9.74955C13.5 9.55063 13.579 9.35987 13.7197 9.21922C13.8603 9.07856 14.0511 8.99955 14.25 8.99955C14.4489 8.99955 14.6397 9.07856 14.7803 9.21922C14.921 9.35987 15 9.55063 15 9.74955V17.2495C15 17.4485 14.921 17.6392 14.7803 17.7799C14.6397 17.9205 14.4489 17.9995 14.25 17.9995Z"
                                                          fill="#909090"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="products-cart__item">
                                    <div class="products-cart__main main-products-cart">
                                        <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item['products']->cat_uri ?>/<?= $item['products']->uri ?>"
                                           target="_blank" class="main-products-cart__image">
                                            <?php $src = newthumbs($item['products']->img, 'products') ?>
                                            <picture>
                                                <source srcset="<?= $src ?>" type="image/webp">
                                                <img src="<?= $src ?>" alt="Image"></picture>
                                        </a>
                                        <div class="main-products-cart__info">
                                            <p class="main-products-cart__brand"><?= $item['products']->brand_title ?></p>
                                            <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item['products']->cat_uri ?>/<?= $item['products']->uri ?>"
                                               target="_blank"
                                               class="main-products-cart__name"><?= $item['products']->title ?></a>
                                            <ul class="main-products-cart__list list-main-products-cart">
                                                <li class="list-main-products-cart__item">
                                                    <span class="list-main-products-cart__name"><?= SKU ?></span>
                                                    <span class="list-main-products-cart__value"><?= $item['products']->SKU ?></span>
                                                </li>
                                                <?php if (!empty($item['products']->volume)) { ?>
                                                    <li class="list-main-products-cart__item">
                                                        <span class="list-main-products-cart__name"><?= VOLUME ?></span>
                                                        <span class="list-main-products-cart__value"><?= $item['products']->volume ?></span>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="products-cart__other other-products-cart">
                                        <div class="other-products-cart__quantity">
                                            <div class="quantity">
                                                <div class="quantity__button quantity__button_minus"></div>
                                                <div class="quantity__input">
                                                    <?php if (!empty($_SESSION['isb2b'])) { ?>
                                                    <input autocomplete="off" type="text" name="counts"
                                                           value="<?= $item['qty'] ?>"
                                                           data-rowid="<?= $item['rowid'] ?>"
                                                           data-max="<?= $item['products']->on_stockWH ?>" readonly>
                                                   <?php }else{ ?>
                                                   <input autocomplete="off" type="text" name="counts"
                                                           value="<?= $item['qty'] ?>"
                                                           data-rowid="<?= $item['rowid'] ?>"
                                                           data-max="<?= $item['products']->on_stock ?>" readonly>
                                                   <?php } ?>
                                                </div>
                                                <div class="quantity__button quantity__button_plus"></div>
                                            </div>
                                        </div>
                                        <div class="other-products-cart__price price-other-products-cart">
                                            <div class="price-other-products-cart__row">
                                                <span class="price-other-products-cart__name"><?= PRICE_1_ITEM ?></span>
                                                <span class="price-other-products-cart__value">
                                                    <?php if (!empty($_SESSION['isb2b'])) { ?>
                                                        <?= $item['products']->priceWH ?> <?= MDL ?>
                                                    <?php } else { ?>
                                                        <?php if (!empty($item['products']->discount_price)) { ?>
                                                            <span><?= $item['products']->price ?> <?= MDL ?></span> <?= $item['products']->discount_price ?> <?= MDL ?>
                                                        <?php } else { ?>
                                                            <?= $item['products']->price ?> <?= MDL ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </span>
                                            </div>
                                            <?php if (empty($_SESSION['isb2b'])) { ?>
                                                <?php if (!empty($client_info) && !empty($client_info->discount) && empty($item['products']->discount_price)) { ?>
                                                    <div class="price-other-products-cart__row _bonus">
                                                        <span class="price-other-products-cart__name"><?= BONUS_ACCRUAL ?></span>
                                                        <span class="price-other-products-cart__value sale_discount"> +<?= $item['products']->price * ($client_info->discount / 100) ?></span>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                            <div class="price-other-products-cart__row _total">
                                                <span class="price-other-products-cart__name"></span>
                                                <span class="price-other-products-cart__total"><span
                                                            class="item-total"><?= $item['price'] * $item['qty'] ?></span> <?= MDL ?></span>
                                            </div>
                                        </div>
                                        <div class="other-products-cart__actions actions-other-products-cart"
                                             data-info="<?= DELETE_PRODUCTS ?>"
                                             data-prod_id="<?= $item['id'] ?>" data-rowid="<?= $item['rowid'] ?>">
                                            <a href="#"
                                               class="actions-other-products-cart__item _like <?= in_array($item['id'], $wishlist) ? '_liked' : '' ?>">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                          d="M5.76562 1.4798C5.70116 1.49105 5.49023 1.52334 5.29687 1.55161C4.78565 1.62633 4.12476 1.84926 3.56249 2.13675C1.46638 3.20841 0.132227 5.41031 0.0147116 7.99214C-0.0754759 9.97383 0.607727 11.8296 2.12591 13.7267C3.3351 15.2377 4.59477 16.4319 8.43749 19.7101C9.43007 20.5569 10.5378 21.5068 10.899 21.821C11.2603 22.1352 11.6231 22.4271 11.7053 22.4695C11.8848 22.5624 12.1212 22.5687 12.2812 22.4849C12.3799 22.4332 15.0612 20.1564 17.3897 18.1469C20.0366 15.8626 21.5564 14.289 22.5454 12.8084C23.5817 11.257 24.0609 9.65405 23.9853 7.99214C23.8367 4.72866 21.7855 2.18616 18.7969 1.56122C18.2604 1.44905 16.9578 1.44998 16.4607 1.56291C15.1933 1.85076 14.1736 2.45339 13.2357 3.4687C12.9126 3.81853 12.4719 4.42331 12.2288 4.85044C12.1185 5.04441 12.0155 5.20308 12 5.20308C11.9845 5.20308 11.8807 5.04305 11.7695 4.84748C11.4509 4.28756 10.9298 3.62503 10.4251 3.138C9.55143 2.295 8.65621 1.80764 7.53927 1.56689C7.21007 1.49592 6.01073 1.43705 5.76562 1.4798ZM7.42968 2.99709C8.69816 3.32709 9.85246 4.29173 10.6584 5.69526C10.8866 6.09267 11.1925 6.80883 11.2968 7.18983C11.4539 7.76372 12.0895 7.97151 12.4812 7.57711C12.5895 7.46794 12.6707 7.30481 12.7657 7.00514C13.079 6.01739 13.67 5.03564 14.3883 4.30969C15.2766 3.41189 16.249 2.94333 17.364 2.87559C20.1814 2.70455 22.4343 4.93509 22.5794 8.03902C22.6756 10.0955 21.7508 11.9591 19.4776 14.2895C18.3332 15.4628 17.5481 16.167 14.1191 19.0954L11.9959 20.9086L11.3534 20.3532C11.0001 20.0477 9.89882 19.1038 8.90624 18.2556C4.50295 14.4929 3.02741 12.9422 2.11227 11.1158C1.19015 9.27553 1.17806 7.25306 2.07881 5.50776C2.79187 4.12617 4.14721 3.13894 5.64843 2.90775C6.09131 2.8395 6.99904 2.88506 7.42968 2.99709Z"
                                                          fill="#909090"/>
                                                    <path class="_fill"
                                                          d="M9.5 3.5L11.5 6.5V7L12.5 6.5L14 3.5L17 2.5L20 3L21.5 4.5L23 6.5L22.5 9.5V11L20 15L16 18L14 20L12.5 21.5L9.5 20L4.5 15L1 10L1.5 6.5L3 3.5L6 2.5L9.5 3.5Z"
                                                          fill="#909090"/>
                                                </svg>
                                            </a>
                                            <a href="#" class="actions-other-products-cart__item _delete">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3.75 5.99955H2.25C2.05109 5.99955 1.86032 5.92053 1.71967 5.77988C1.57902 5.63922 1.5 5.44846 1.5 5.24955C1.5 5.05063 1.57902 4.85987 1.71967 4.71922C1.86032 4.57856 2.05109 4.49955 2.25 4.49955H8.25V2.24805C8.25 2.04913 8.32902 1.85837 8.46967 1.71772C8.61032 1.57706 8.80109 1.49805 9 1.49805H15C15.1989 1.49805 15.3897 1.57706 15.5303 1.71772C15.671 1.85837 15.75 2.04913 15.75 2.24805V4.49955H21.75C21.9489 4.49955 22.1397 4.57856 22.2803 4.71922C22.421 4.85987 22.5 5.05063 22.5 5.24955C22.5 5.44846 22.421 5.63922 22.2803 5.77988C22.1397 5.92053 21.9489 5.99955 21.75 5.99955H20.25V21.7495C20.25 21.9485 20.171 22.1392 20.0303 22.2799C19.8897 22.4205 19.6989 22.4995 19.5 22.4995H4.5C4.30109 22.4995 4.11032 22.4205 3.96967 22.2799C3.82902 22.1392 3.75 21.9485 3.75 21.7495V5.99955ZM14.25 4.49955V2.99955H9.75V4.49955H14.25ZM5.25 20.9995H18.75V5.99955H5.25V20.9995ZM9.75 17.9995C9.55109 17.9995 9.36032 17.9205 9.21967 17.7799C9.07902 17.6392 9 17.4485 9 17.2495V9.74955C9 9.55063 9.07902 9.35987 9.21967 9.21922C9.36032 9.07856 9.55109 8.99955 9.75 8.99955C9.94891 8.99955 10.1397 9.07856 10.2803 9.21922C10.421 9.35987 10.5 9.55063 10.5 9.74955V17.2495C10.5 17.4485 10.421 17.6392 10.2803 17.7799C10.1397 17.9205 9.94891 17.9995 9.75 17.9995ZM14.25 17.9995C14.0511 17.9995 13.8603 17.9205 13.7197 17.7799C13.579 17.6392 13.5 17.4485 13.5 17.2495V9.74955C13.5 9.55063 13.579 9.35987 13.7197 9.21922C13.8603 9.07856 14.0511 8.99955 14.25 8.99955C14.4489 8.99955 14.6397 9.07856 14.7803 9.21922C14.921 9.35987 15 9.55063 15 9.74955V17.2495C15 17.4485 14.921 17.6392 14.7803 17.7799C14.6397 17.9205 14.4489 17.9995 14.25 17.9995Z"
                                                          fill="#909090"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
                <form action="#" class="main-cart__promocode promocode-main-cart _spollers" style="opacity: 0">
                    <div class="promocode-main-cart__head _active _spoller">
                        <h2 class="promocode-main-cart__title"><?= PROMO_CODE ?></h2>
                        <div class="promocode-main-cart__arrow">
                            <picture>
                                <source srcset="/app/img/icons/arrow-down.svg" type="image/webp">
                                <img src="/app/img/icons/arrow-down.svg" alt="Icon"></picture>
                        </div>
                    </div>
                    <div class="promocode-main-cart__body">
                        <input type="text" name="promocode" placeholder="<?= ENTER_NUMBER ?>"
                               class="promocode-main-cart__input">
                        <button type="submit" class="promocode-main-cart__btn"><?= APPLY ?></button>
                    </div>
                </form>
                <div class="main-cart__list list-main-cart">
                    <h2 class="list-main-cart__title"><?= ORDER_PRICE ?></h2>
                    <ul class="list-main-cart__list">
                        <li class="list-main-cart__row">
                            <span class="list-main-cart__name"><?= PRODUCTS ?> <span
                                        class="items_count"><?= $total_items_cart ?></span></span>
                            <span class="list-main-cart__value"><span
                                        class="items_total"><?= $total_price_cart ?></span><?= MDL ?></span>
                        </li>
                        <li class="list-main-cart__row">
                            <span class="list-main-cart__name"><?= SALE ?></span>
                            <span class="list-main-cart__value">0 <?= MDL ?></span>
                        </li>
                        <li class="list-main-cart__row">
                            <span class="list-main-cart__name"><?= DELIVERY ?></span>
                            <span class="list-main-cart__value"><span class="delivery_price"><?=$delivery_price?></span> <?= MDL ?></span>
                        </li>
                        <li class="list-main-cart__row _total">
                            <span class="list-main-cart__name"><?= TOTAL_PRICE ?></span>
                            <span class="list-main-cart__value"><span
                                        class="items_total"><?= $total_price_cart + $delivery_price ?></span> <?= MDL ?></span>
                        </li>
                        <?php if (!empty($client_info) && !empty($client_info->discount)) { ?>
                            <li class="list-main-cart__row _bonus">
                                <span class="list-main-cart__name"><?= PURCHASE_BONUSES ?></span>
                                <span class="list-main-cart__value">+<?= $bonus_total ?></span>
                            </li>
                        <?php } ?>
                    </ul>
                    <?php if (empty($client_info)) { ?>
                        <div class="login_info">
                            <?= LOGIN_CART ?>
                        </div>
                    <?php } ?>
                    <a href="/<?= $lclang ?>/<?= $menu['all'][19]->uri ?>"
                       class="list-main-cart__btn"><?= CONTINUE_CHECKOUT ?></a>
                </div>
            </div>
        </div>
    </section>
</main>