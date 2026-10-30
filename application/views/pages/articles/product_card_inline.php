<?php
$productImage = !empty($product->img)
    ? newthumbs($product->img, 'products', 800, 800, 'selection-800', 0)
    : '';
$productUrl = '/'
    . $lclang
    . '/'
    . $menu['all'][3]->uri
    . '/'
    . ($product->cat_uri ?? '')
    . '/'
    . ($product->uri ?? '');
$productTitle = (string) ($product->title ?? '');
$productPrice = (float) ($product->effective_price ?? $product->price ?? 0);
$productSale = !empty($_SESSION['isb2b']) ? 0 : (float) ($product->effective_discount_price ?? $product->discount_price ?? 0);
if ($productSale > 0 && $productSale < $productPrice) $productPrice = $productSale;
?>
<a class="blog-recommended-product b2c-product-card b2c-product-card--editorial" href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>">
    <span class="blog-recommended-product__image">
        <?php if ($productImage !== '') { ?>
            <img src="<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($productTitle, ENT_QUOTES, 'UTF-8') ?>"
                 loading="lazy" decoding="async">
        <?php } ?>
    </span>
    <span class="blog-recommended-product__copy">
        <?php include APPPATH . 'views/layouts/pages/product-card-options.php'; ?>

        <small><?= htmlspecialchars((string) ($product->cat_title ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
        <strong><?= htmlspecialchars($productTitle, ENT_QUOTES, 'UTF-8') ?></strong>
        <?php if ($productPrice > 0) { ?>
            <b><?= number_format($productPrice, 0, '.', ' ') ?> <?= defined('MDL') ? MDL : 'MDL' ?></b>
        <?php } ?>
    </span>
</a>
