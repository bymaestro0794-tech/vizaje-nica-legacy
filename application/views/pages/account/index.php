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
    <section class="account">
        <div class="account__container _container">
            <h2 class="account__title"><?= $page_name ?></h2>
            <div class="account__content _tabs">
                <div class="account__sidebar sidebar-account ">
                    <ul class="sidebar-account__list list-sidebar-account">
                        <li class="list-sidebar-account__item _tabs-item <?php if (!isset($_GET['bonus']) && !isset($_GET['orders']) && !isset($_GET['favorite'])) { ?>_active<?php } ?>" data-url="cabinet">
                            <div class="list-sidebar-account__icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_217_27333)">
                                        <path class="fill"
                                            d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM5.35 16.5C6.66 15.56 8.26 15 10 15C11.74 15 13.34 15.56 14.65 16.5C13.34 17.44 11.74 18 10 18C8.26 18 6.66 17.44 5.35 16.5ZM16.14 15.12C14.3884 13.7457 12.2264 12.9988 10 12.9988C7.77362 12.9988 5.6116 13.7457 3.86 15.12C2.65692 13.6853 1.9983 11.8723 2 10C2 5.58 5.58 2 10 2C14.42 2 18 5.58 18 10C18 11.95 17.3 13.73 16.14 15.12Z"
                                            fill="#000000" />
                                        <path class="fill"
                                            d="M10 3.75C7.93214 3.75 6.25 5.43214 6.25 7.5C6.25 9.56786 7.93214 11.25 10 11.25C12.0679 11.25 13.75 9.56786 13.75 7.5C13.75 5.43214 12.0679 3.75 10 3.75ZM10 9.10714C9.11071 9.10714 8.39286 8.38929 8.39286 7.5C8.39286 6.61071 9.11071 5.89286 10 5.89286C10.8893 5.89286 11.6071 6.61071 11.6071 7.5C11.6071 8.38929 10.8893 9.10714 10 9.10714Z"
                                            fill="#000000" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_217_27333">
                                            <rect width="20" height="20" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </div>
                            <p class="list-sidebar-account__name"><?= $page_name ?></p>
                        </li>
                        <li class="list-sidebar-account__item _tabs-item <?php if (isset($_GET['favorite'])) { ?>_active<?php } ?>" data-url="favorite">
                            <div class="list-sidebar-account__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                    style="  width: 25px;  max-width: 25px;">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M5.766 1.48a41.45 41.45 0 01-.47.072c-.51.074-1.171.297-1.734.585C1.466 3.208.132 5.41.015 7.992c-.09 1.982.593 3.838 2.11 5.735 1.21 1.51 2.47 2.705 6.312 5.983.993.847 2.1 1.797 2.462 2.111.361.314.724.606.806.648.18.093.416.1.576.016.099-.052 2.78-2.329 5.109-4.338 2.647-2.284 4.166-3.858 5.155-5.339 1.037-1.551 1.516-3.154 1.44-4.816-.148-3.263-2.2-5.806-5.188-6.43-.537-.113-1.84-.112-2.336 0-1.268.289-2.287.891-3.225 1.907-.323.35-.764.954-1.007 1.381-.11.194-.213.353-.229.353-.015 0-.12-.16-.23-.356a8.272 8.272 0 00-1.345-1.709c-.874-.843-1.769-1.33-2.886-1.571-.329-.071-1.528-.13-1.773-.087zM7.43 2.997c1.268.33 2.422 1.295 3.228 2.698.229.398.535 1.114.639 1.495.157.574.792.782 1.184.387.108-.11.19-.272.285-.572a6.936 6.936 0 011.622-2.695c.889-.898 1.861-1.367 2.976-1.434 2.817-.171 5.07 2.06 5.215 5.163.097 2.056-.828 3.92-3.101 6.25-1.145 1.174-1.93 1.878-5.359 4.806l-2.123 1.814-.643-.556c-.353-.305-1.454-1.25-2.447-2.097-4.403-3.763-5.879-5.314-6.794-7.14-.922-1.84-.934-3.863-.033-5.608.713-1.382 2.068-2.37 3.57-2.6.442-.068 1.35-.023 1.78.09z"
                                        fill="#000" />
                                </svg>
                            </div>
                            <p class="list-sidebar-account__name"><?= $menu['all'][17]->title ?></p>
                        </li>
                        <li class="list-sidebar-account__item _tabs-item <?php if (isset($_GET['orders'])) { ?>_active<?php } ?>" data-url="orders">
                            <div class="list-sidebar-account__icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_217_27311)">
                                        <path class="stroke"
                                            d="M18.3346 5.83268L10.0013 1.66602L1.66797 5.83268V14.166L10.0013 18.3327L18.3346 14.166V5.83268Z"
                                            stroke="#000000" stroke-width="1.5" stroke-linejoin="round" />
                                        <path class="stroke" d="M1.66797 5.83203L10.0013 9.9987" stroke="#000000"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path class="stroke" d="M10 18.3333V10" stroke="#000000" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                        <path class="stroke" d="M18.3333 5.83203L10 9.9987" stroke="#000000"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path class="stroke" d="M14.1693 3.75L5.83594 7.91667" stroke="#000000"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_217_27311">
                                            <rect width="20" height="20" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </div>
                            <p class="list-sidebar-account__name"><?= $menu['all'][16]->title ?></p>
                        </li>
                        <li href="/<?= $lclang ?>/<?= $menu['all'][15]->uri ?>"
                            class="list-sidebar-account__item _tabs-item <?php if (isset($_GET['bonus'])) { ?>_active<?php } ?>" data-url="bonus">
                            <div class="list-sidebar-account__icon">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_575_8903)">
                                        <path d="M10 0C10 0 11.2798 4.60473 13.3375 6.66246C15.3953 8.72018 20 10 20 10C20 10 15.3953 11.2798 13.3375 13.3375C11.2798 15.3953 10 20 10 20C10 20 8.72018 15.3953 6.66246 13.3375C4.60473 11.2798 0 10 0 10C0 10 4.60473 8.72018 6.66246 6.66246C8.72018 4.60473 10 0 10 0Z"
                                            fill="#000000" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_575_8903">
                                            <rect width="20" height="20" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </div>
                            <p class="list-sidebar-account__name"><?= $menu['all'][30]->title ?></p>
                        </li>
                    </ul>
                    <a href="/<?= $lclang ?>/logout" class="sidebar-account__logout logout-sidebar-account">
                        <div class="logout-sidebar-account__icon">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.5625 2.8125H2.1875V17.8125H6.5625M13.4375 5.9375L17.8125 10.3125L13.4375 14.6875M7.1875 10.3125H17.8125"
                                    stroke="#424242" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </div>
                        <p class="logout-sidebar-account__name"><?= LOGOUT ?></p>
                    </a>
                </div>
                <div class="account__main main-account">
                    <div class="main-account__block _tabs-block <?php if (!isset($_GET['bonus']) && !isset($_GET['orders']) && !isset($_GET['favorite'])) { ?>_active<?php } ?>">
                        <div class="main-account__info info-main-account">
                            <div class="info-main-account__section">
                                <h3 class="info-main-account__title"><?= $menu['all'][15]->title ?></h3>
                                <form action="" method="post" class="info-main-account__body">
                                    <div class="info-main-account__inputs">
                                        <div class="info-main-account__row _item-input">
                                            <label class="info-main-account__label"><?= SURNAME ?></label>
                                            <input type="text" name="surname" value="<?= $client_info->surname ?>"
                                                class="info-main-account__input">
                                        </div>
                                        <div class="info-main-account__row _item-input">
                                            <label class="info-main-account__label _required"><?= NAME ?></label>
                                            <input type="text" name="name" value="<?= $client_info->name ?>"
                                                class="info-main-account__input"
                                                required>
                                        </div>
                                        <div class="info-main-account__row _item-input">
                                            <label class="info-main-account__label _required"><?= PHONE ?></label>
                                            <input type="text" name="phone" value="<?= $client_info->phone ?>"
                                                class="info-main-account__input _phone" disabled
                                                required>
                                        </div>
                                        <div class="info-main-account__row _item-input">
                                            <label class="info-main-account__label _required"><?= EMAIL_LOGIN ?></label>
                                            <input type="text" name="email" value="<?= $client_info->email ?>"
                                                class="info-main-account__input"
                                                required>
                                        </div>
                                        <div class="info-main-account__row _item-input">
                                            <label class="info-main-account__label"><?= BIRTHDAY ?></label>
                                            <input type="text" autocomplete="off" value="<?= $client_info->birthday ?>"
                                                name="birthday"
                                                class="info-main-account__input _date">
                                        </div>
                                        <div class="info-main-account__row _item-input _focus">
                                            <label class="info-main-account__label"><?= SEX ?></label>
                                            <div class="info-main-account__select">
                                                <select name="sex" class="form">
                                                    <option value=""></option>
                                                    <option value="1" <?= $client_info->sex == 1 ? 'selected' : '' ?>><?= FEMALE ?></option>
                                                    <option value="2" <?= $client_info->sex == 2 ? 'selected' : '' ?>><?= MEN ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-main-account__checks">
                                        <div class="info-main-account__check">
                                            <label class="checkbox">
                                                <input class="checkbox__input" <?= $client_info->sms_subscribe == 1 ? 'checked' : '' ?>
                                                    type="checkbox" value="1"
                                                    name="sms_subscribe">
                                                <span class="checkbox__text"><span><?= SMS_FROM_VN ?></span></span>
                                            </label>
                                        </div>
                                        <div class="info-main-account__check">
                                            <label class="checkbox">
                                                <input class="checkbox__input" <?= $client_info->mail_subscribe == 1 ? 'checked' : '' ?>
                                                    type="checkbox" value="1"
                                                    name="mail_subscribe">
                                                <span class="checkbox__text"><span><?= RECEIVE_PROMO_REG ?></span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="info-main-account__btn"><?= CHANGE ?></button>
                                </form>
                            </div>
                            <?php if (!empty($pass_off)) { ?>
                                <div class="info-main-account__section">
                                    <h3 class="info-main-account__title"><?= PASSWORD ?></h3>
                                    <form action="" method="post" class="info-main-account__body">
                                        <div class="info-main-account__passwords">
                                            <div class="info-main-account__password _item-input">
                                                <label class="info-main-account__label"><?= PASSWORD ?></label>
                                                <input type="password" name="password" required
                                                    class="info-main-account__input">
                                                <div class="info-main-account__viewpass viewpass">
                                                    <div class="viewpass__hide">
                                                        <picture>
                                                            <source srcset="/app/img/icons/eye-view.webp"
                                                                type="image/webp">
                                                            <img src="/app/img/icons/eye-view.png" alt="View">
                                                        </picture>
                                                    </div>
                                                    <div class="viewpass__view">
                                                        <picture>
                                                            <source srcset="/app/img/icons/eye-hide.webp"
                                                                type="image/webp">
                                                            <img src="/app/img/icons/eye-hide.png" alt="Hide">
                                                        </picture>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="info-main-account__password _item-input">
                                                <label class="info-main-account__label"><?= PASSWORD_NEW ?></label>
                                                <input type="password" name="password_new" required
                                                    class="info-main-account__input">
                                                <div class="info-main-account__viewpass viewpass">
                                                    <div class="viewpass__hide">
                                                        <picture>
                                                            <source srcset="/app/img/icons/eye-view.webp"
                                                                type="image/webp">
                                                            <img src="/app/img/icons/eye-view.png" alt="View">
                                                        </picture>
                                                    </div>
                                                    <div class="viewpass__view">
                                                        <picture>
                                                            <source srcset="/app/img/icons/eye-hide.webp"
                                                                type="image/webp">
                                                            <img src="/app/img/icons/eye-hide.png" alt="Hide">
                                                        </picture>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="info-main-account__btn"><?= CHANGE ?></button>
                                    </form>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="main-account__block _tabs-block <?php if (isset($_GET['favorite'])) { ?>_active<?php } ?>">
                        <div class="main-account__orders orders-main-account">
                            <h3 class="orders-main-account__title"><?= $menu['all'][17]->title ?></h3>
                            <div class="products_items">
                                <div class="main-catalog__body">
                                    <?php if (isset($products)) { ?>
                                        <?php foreach ($products as $product) { ?>
                                            <?php $this->load->view("layouts/pages/product_main", array('item' => $product)); ?>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                                <div class="main-catalog__paggination paggination-main-catalog">
                                    <? $this->load->view('layouts/pages/paginator'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="main-account__block _tabs-block <?php if (isset($_GET['orders'])) { ?>_active<?php } ?>">
                        <div class="main-account__orders orders-main-account">
                            <h3 class="orders-main-account__title"><?= $menu['all'][16]->title ?></h3>
                            <div class="orders-main-account__list">
                                <?php if (!empty($orders)) { ?>
                                    <?php foreach ($orders as $order) {
                                        $delivery = array(
                                            1 => PICKUP_STORE,
                                            2 => EXPRESS_DELIVERY,
                                            3 => EXPRESS_DELIVERY_DAY,
                                        ) ?>
                                        <div class="orders-main-account__item">
                                            <div class="orders-main-account__info info-orders-main-account">
                                                <p class="info-orders-main-account__name"><?= ORDER ?>
                                                    №<?= $order->order_id ?></p>
                                                <p class="info-orders-main-account__status _finished"><?= $order->status ?></p>
                                                <p class="info-orders-main-account__delivery"><?= $delivery[$order->delivery] ?></p>
                                                <p class="info-orders-main-account__date"><?= transformDate($order->added, $lclang) ?></p>
                                            </div>
                                            <div class="orders-main-account__products products-orders-main-account">
                                                <div class="products-orders-main-account__list">
                                                    <?php foreach ($order->products as $product) { ?>
                                                        <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $product->product->cat_uri ?>/<?= $product->product->uri ?>"
                                                            class="products-orders-main-account__item">
                                                            <div class="products-orders-main-account__image">
                                                                <?php if (!empty($product->product->img)) { ?>
                                                                    <picture>
                                                                        <?php $src = newthumbs($product->product->img->img, 'products') ?>
                                                                        <source srcset="<?= $src ?>" type="image/webp">
                                                                        <img src="<?= $src ?>" alt="Image">
                                                                    </picture>
                                                                <?php } ?>
                                                            </div>
                                                            <?php if (!empty($_SESSION['isb2b'])) { ?>
                                                                <?php if ($product->qtyWH > 1) { ?>
                                                                    <span class="products-orders-main-account__counts"><?= $product->qtyWH ?></span>
                                                                <?php } ?>
                                                            <?php } else { ?>
                                                                <?php if ($product->qty > 1) { ?>
                                                                    <span class="products-orders-main-account__counts"><?= $product->qty ?></span>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="orders-main-account__other">
                                                <a href="/<?= $lclang ?>/<?= $menu['all'][16]->uri ?>/<?= $order->order_id ?>"
                                                    class="orders-main-account__more"><?= MORE ?></a>
                                                <!--                                                <a href="#" class="orders-main-account__repeat">-->
                                                <? //= REPEAT_ORDER 
                                                ?><!--</a>-->
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="main-account__block _tabs-block <?php if (isset($_GET['bonus'])) { ?>_active<?php } ?>">
                        <div class="main-account__bonus bonus-main-account">
                            <div class="bonus-main-account__header">
                                <h3 class="bonus-main-account__title"><?= $menu['all'][30]->title ?></h3>
                                <div class="bonus-main-account__counts">
                                    <picture>
                                        <source srcset="/app/img/icons/bonus.svg" type="image/webp">
                                        <img src="/app/img/icons/bonus.svg" alt="Bonus-icon">
                                    </picture>
                                    <span><?= $client_info->Bonus ?></span>
                                </div>
                                <div class="bonus-main-account__counts">
                                    <picture>
                                        <source srcset="/app/img/icons/bonus.svg" type="image/webp">
                                        <img src="/app/img/icons/bonus.svg" alt="Bonus-icon">
                                    </picture>
                                    <span><?= $client_info->Discount ?> %</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>