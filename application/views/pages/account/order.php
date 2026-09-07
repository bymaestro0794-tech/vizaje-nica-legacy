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
    <section class="account order_page">
        <div class="account__container _container">
            <div class="account__content _tabs">
                <div class="account__main main-account">
                    <div class="main-account__block _tabs-block _active">
                        <div class="main-account__orders orders-main-account">
                            <h3 class="orders-main-account__title"><?= $menu['all'][16]->title ?></h3>
                            <div class="orders-main-account__list">
                                <?php if (!empty($orders)) { ?>
                                    <?php foreach ($orders as $order) {
                                        $delivery = array(
                                            1 => PICKUP_STORE,
                                            2 => EXPRESS_DELIVERY,
                                            3 => EXPRESS_DELIVERY_DAY,
                                        )?>
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
                                                                <picture>
                                                                    <?php if (!empty($product->product->img->img)){
                                                                        $src = newthumbs($product->product->img->img, 'products');
                                                                    } else {
                                                                        $src = newthumbs('', 'products');
                                                                    }?>
                                                                    <picture>
                                                                        <source srcset="<?= $src ?>" type="image/webp">
                                                                        <img src="<?= $src ?>" alt="Image"></picture>
                                                                </picture>
                                                            </div>
                                                            <?php if ($product->qty > 1) { ?>
                                                                <span class="products-orders-main-account__counts"><?= $product->qty ?></span>
                                                            <?php } ?>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="orders-main-account__other">
                                                <a href="/<?= $lclang ?>/<?= $menu['all'][16]->uri ?>/<?= $order->order_id ?>"
                                                   class="orders-main-account__more"><?= MORE ?></a>
<!--                                                <a href="#" class="orders-main-account__repeat">--><?//= REPEAT_ORDER ?><!--</a>-->
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>