<?php
$productTitle = preg_replace(
    '/\s+/u',
    ' ',
    trim(
        html_entity_decode(
            $item->title,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        )
    )
);

$productImage = newthumbs($item->img, 'products');

if (!empty($_SESSION['isb2b'])) {
    $productPrice = $item->priceWH . ' ' . MDL;
} elseif (!empty($item->discount_price)) {
    $productPrice = $item->discount_price . ' ' . MDL;
} else {
    $productPrice = $item->price . ' ' . MDL;
}
?>

<?php if (!empty($_SESSION['isb2b'])) { ?>
<div
    class="products-colection__product
        <?php if (empty($item->stockWH)) { ?>stock_off<?php } ?>"
    data-prod_id="<?= (int) $item->id ?>"
    data-product-image="<?= htmlspecialchars(
        $productImage,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    data-product-brand="<?= htmlspecialchars(
        $item->brand_title,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    data-product-name="<?= htmlspecialchars(
        $productTitle,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    data-product-variant=""
    data-product-price="<?= htmlspecialchars(
        $productPrice,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>
        <a
            href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item->cat_uri ?>/<?= $item->uri ?>"
            class="products-colection__image"
        >
            <picture>
                <source
                    srcset="<?= $productImage ?>"
                    type="image/webp"
                >

                <img
                    src="<?= $productImage ?>"
                    alt="<?= htmlspecialchars(
                        $productTitle,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    loading="lazy"
                    decoding="async"
                >
            </picture>
        </a>
        <div class="products-colection__body">
            <div class="products-colection__info">
                <div class="products-colection__head">
                    <p class="products-colection__brand"><?= $item->brand_title ?></p>
                    <div class="products-colection__modification">
                        <?php if (!empty($item->is_new)) { ?>
                            <p class="products-colection__modification_new"><?= PRODUCTS_NEW ?></p>
                        <?php } ?>
                        <?php if (!empty($item->best_selling)) { ?>
                            <p class="products-colection__modification_hit"><?= PRODUCTS_HIT ?></p>
                        <?php } ?>
                    </div>
                </div>
                <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item->cat_uri ?>/<?= $item->uri ?>" class="products-colection__name"><?= $item->title ?></a>

                <div class="products-colection__price price-products-colection">
                    <span class="price-products-colection__value"><?= $item->priceWH ?> <?= MDL ?></span>
                </div>
            </div>
        </div>
    </div>
<?php }else{ ?>
<div
    class="products-colection__product
        <?php if (empty($item->stock)) { ?>stock_off<?php } ?>"
    data-prod_id="<?= (int) $item->id ?>"
    data-product-image="<?= htmlspecialchars(
        $productImage,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    data-product-brand="<?= htmlspecialchars(
        $item->brand_title,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    data-product-name="<?= htmlspecialchars(
        $productTitle,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    data-product-variant=""
    data-product-price="<?= htmlspecialchars(
        $productPrice,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>
    <a
        href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item->cat_uri ?>/<?= $item->uri ?>"
        class="products-colection__image"
    >
        <picture>
            <source
                srcset="<?= $productImage ?>"
                type="image/webp"
            >

            <img
                src="<?= $productImage ?>"
                alt="<?= htmlspecialchars(
                    $productTitle,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                loading="lazy"
                decoding="async"
            >
        </picture>
    </a>
    <div class="products-colection__body">
        <div class="products-colection__info">
            <div class="products-colection__head">
                <p class="products-colection__brand"><?= $item->brand_title ?></p>
                <div class="products-colection__modification">
                    <?php if (!empty($item->is_new)) { ?>
                        <p class="products-colection__modification_new"><?= PRODUCTS_NEW ?></p>
                    <?php } ?>
                    <?php if (!empty($item->discount_price)) { ?>
                        <p class="products-colection__modification_sale"><?= PRODUCTS_SALE ?></p>
                    <?php } ?>
                    <?php if (!empty($item->best_selling)) { ?>
                        <p class="products-colection__modification_hit"><?= PRODUCTS_HIT ?></p>
                    <?php } ?>
                </div>
            </div>
            <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item->cat_uri ?>/<?= $item->uri ?>"
               class="products-colection__name"><?= $item->title ?></a>

            <?php if (!empty($item->discount_price)) { ?>
                <div class="products-colection__price price-products-colection">
                    <span class="price-products-colection__value"><?= $item->discount_price ?> <?= MDL ?></span>
                    <span class="price-products-colection__old"><?= $item->price ?> <?= MDL ?></span>
                </div>
            <?php } else { ?>
                <div class="products-colection__price price-products-colection">
                    <span class="price-products-colection__value"><?= $item->price ?> <?= MDL ?></span>
                </div>
                <div class="products-colection__bonus">
                    <?php if (!empty($client_info) && !empty($client_info->discount)) { ?>
                        <p> +<?= $item->price * ($client_info->discount / 100) ?> <?= PRODUCTS_BONUSES ?></p>
                    <?php } else { ?>
                        <p> +<?= $item->price / 10 ?> <?= PRODUCTS_BONUSES ?></p>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>
