<?php
if (
    empty($products)
    || empty($menu_item)
) {
    return;
}

$collectionProducts = array_slice(
    array_reverse($products),
    0,
    3
);

$bannerImage = newthumbs(
    $menu_item->img,
    'menu'
);
?>

<section class="colection <?= !empty($reverse) ? '_revers' : '' ?>">
    <div class="colection__container _container">
        <div class="colection__content">
            <div class="colection__banner banner-colection">
                <div class="banner-colection__image">
                    <img
                        src="<?= htmlspecialchars(
                            $bannerImage,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        loading="lazy"
                        decoding="async"
                        alt="<?= htmlspecialchars(
                            $menu_item->title ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                </div>

                <div class="banner-colection__info">
                    <?php if (!empty($menu_item->desc)) : ?>
                        <p class="banner-colection__brand">
                            <?= htmlspecialchars(
                                $menu_item->desc,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>
                    <?php endif; ?>

                    <p class="banner-colection__name">
                        <?= htmlspecialchars(
                            $menu_item->title ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <a
                        href="/<?= $lclang ?>/<?= htmlspecialchars(
                            $menu_item->uri,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        class="banner-colection__link"
                    >
                        <?= PRODUCTS_GO_SHOPPING ?>

                        <img
                            src="/app/img/icons/colection-more.svg"
                            alt=""
                        >
                    </a>
                </div>
            </div>

            <div class="colection__products products-colection">
                <?php foreach ($collectionProducts as $product) : ?>
                    <?php
                    $this->load->view(
                        'layouts/pages/product_home',
                        [
                            'item' => $product,
                        ]
                    );
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>