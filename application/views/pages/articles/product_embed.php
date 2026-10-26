<?php
$productTitle = (string) ($product->title ?? '');
$productImage = newthumbs($product->img, 'products', 800, 800, 'selection-800', 0);
$productUrl = '/'
    . $lclang
    . '/'
    . $product_category_uri
    . '/'
    . ($product->cat_uri ?? '')
    . '/'
    . ($product->uri ?? '');
$similarProducts = array_slice($similar_products ?? array(), 0, 3);
?>
<?php if (!empty($similarProducts)) { ?>
<div class="blog-product-group blog-product-group--with-recommendations">
<?php } ?>
<div class="blog-product-embed <?= !empty($is_unavailable) ? 'blog-product-embed--unavailable' : '' ?>">
    <a class="blog-product-embed__image" href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>">
        <img src="<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($productTitle, ENT_QUOTES, 'UTF-8') ?>"
             loading="lazy" decoding="async">
        <?php if (!empty($is_unavailable)) { ?>
            <span class="blog-product-embed__availability blog-product-embed__availability--muted">
                <?= $lclang === 'ro' ? 'Indisponibil momentan' : 'Нет в наличии' ?>
            </span>
        <?php } ?>
    </a>
    <div class="blog-product-embed__copy">
        <?php include APPPATH . 'views/layouts/pages/product-card-options.php'; ?>

        <span class="blog-product-embed__eyebrow">
            <?= htmlspecialchars((string) ($product->cat_title ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <a class="blog-product-embed__title" href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($productTitle, ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php
            $basePrice = (float) ($product->effective_price ?? $product->price ?? 0);
            $salePrice = !empty($_SESSION['isb2b'])
                ? 0
                : (float) ($product->effective_discount_price ?? $product->discount_price ?? 0);
            $displayPrice = $salePrice > 0 && $salePrice < $basePrice ? $salePrice : $basePrice;
        ?>
        <?php if ($displayPrice > 0) { ?>
            <strong class="blog-product-embed__price">
                <?= number_format($displayPrice, 0, '.', ' ') ?> <?= defined('MDL') ? MDL : 'MDL' ?>
            </strong>
        <?php } ?>
    </div>
</div>
<?php if (!empty($similarProducts)) { ?>
    <section class="blog-product-recommendations"
             aria-label="<?= $lclang === 'ro' ? 'Produse similare disponibile' : 'Похожие товары в наличии' ?>">
        <h2><?= $lclang === 'ro' ? 'Produse similare' : 'Похожие продукты' ?></h2>
        <div class="blog-product-recommendations__grid">
            <?php foreach ($similarProducts as $product) { ?>
                <?php include APPPATH . 'views/pages/articles/product_card_inline.php'; ?>
            <?php } ?>
        </div>
    </section>
</div>
<?php } ?>
