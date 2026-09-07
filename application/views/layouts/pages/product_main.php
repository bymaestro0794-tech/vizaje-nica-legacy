<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Product card display values.
 *
 * Для обычного товара используются поля родителя.
 * Для вариативного товара модель должна передать effective_* значения
 * выбранного доступного варианта.
 */

$productId = isset($item->id)
    ? (int) $item->id
    : 0;

$isVariable = !empty($item->variable);

$parentPrice = isset($item->price)
    ? (float) $item->price
    : 0;

$displayPrice = isset($item->effective_price)
    ? (float) $item->effective_price
    : $parentPrice;

$displayDiscountPrice = isset($item->effective_discount_price)
    ? (float) $item->effective_discount_price
    : (
        isset($item->discount_price)
            ? (float) $item->discount_price
            : 0
    );

$displayStock = isset($item->effective_stock)
    ? (int) $item->effective_stock
    : (
        isset($item->on_stock)
            ? (int) $item->on_stock
            : 0
    );

$displayWholesalePrice = isset($item->effective_price_wholesale)
    ? (float) $item->effective_price_wholesale
    : (
        isset($item->price_wholesale)
            ? (float) $item->price_wholesale
            : $displayPrice
    );

$displayWholesaleStock = isset($item->effective_stock_wholesale)
    ? (int) $item->effective_stock_wholesale
    : (
        isset($item->on_stockWH)
            ? (int) $item->on_stockWH
            : $displayStock
    );

$displayImage = !empty($item->effective_img)
    ? $item->effective_img
    : $item->img;

$hasDiscount =
    $displayDiscountPrice > 0 &&
    $displayPrice > 0 &&
    $displayDiscountPrice < $displayPrice;

$currentPrice = $hasDiscount
    ? $displayDiscountPrice
    : $displayPrice;

$salePercent = $hasDiscount
    ? (int) round(
        (($displayPrice - $displayDiscountPrice) / $displayPrice) * 100
    )
    : 0;

$bonusPercent = !empty($client_info) && !empty($client_info->discount)
    ? (float) $client_info->discount
    : 10;

$bonusAmount = $hasDiscount
    ? 0
    : $currentPrice * ($bonusPercent / 100);

$productUrl =
    '/' .
    $lclang .
    '/' .
    $menu['all'][3]->uri .
    '/' .
    $item->cat_uri .
    '/' .
    $item->uri;

$isLiked = in_array($productId, $wishlist);
$isB2B = !empty($_SESSION['isb2b']);

$availableStock = $isB2B
    ? $displayWholesaleStock
    : $displayStock;

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

$imageSrc = newthumbs($displayImage, 'products');
$notificationPrice = $isB2B
    ? $displayWholesalePrice
    : $currentPrice;

$notificationPriceText =
    number_format(
        $notificationPrice,
        0,
        '.',
        ' '
    ) .
    ' ' .
    MDL;
?>

<div
    class="main-catalog__product product
        <?= $isLiked ? '_liked' : '' ?>
        <?= $availableStock <= 0 ? 'stock_off' : '' ?>"
    data-prod_id="<?= $productId ?>"
    data-product-image="<?= htmlspecialchars(
        $imageSrc,
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
        $notificationPriceText,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>
    <div class="product__head head-product">
    <a
        href="<?= $productUrl ?>"
        class="head-product__image"
        aria-label="<?= htmlspecialchars(
            $productTitle,
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >
        <picture>
            <source
                srcset="<?= $imageSrc ?>"
                type="image/webp"
            >

            <img
                src="<?= $imageSrc ?>"
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

    <div class="head-product__modificator">
        <?php if (!empty($item->is_new)) { ?>
            <p class="head-product__modificator_new">
                <?= PRODUCTS_NEW ?>
            </p>
        <?php } ?>

        <?php if (!$isB2B && $hasDiscount) { ?>
            <p class="head-product__modificator_sale">
                <?= PRODUCTS_SALE ?>
            </p>

            <?php if ($salePercent > 0) { ?>
                <p class="head-product__modificator_sale">
                    <?= $salePercent ?>%
                </p>
            <?php } ?>
        <?php } ?>

        <?php if (!empty($item->best_selling)) { ?>
            <p class="products-colection__modification_hit">
                <?= PRODUCTS_HIT ?>
            </p>
        <?php } ?>
    </div>

    <?php if ($availableStock > 0) { ?>
    <a
        href="javascript:;"
        class="
            head-product__cart
            action-product__cart
            <?= $isVariable
                ? 'has-variable'
                : 'add_cart' ?>
        "
        <?php if ($isVariable) : ?>
            data-product-quick-view-open
            data-product-id="<?= $productId ?>"
        <?php endif; ?>
        aria-label="<?= htmlspecialchars(
            $productTitle,
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >
            <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M11.1401 0.0717718C9.53764 0.374959 8.23625 1.38498 7.55882 2.85118C7.30836 3.39324 7.16849 4.00735 7.13793 4.69927L7.11153 5.29693H5.80878C5.00235 5.29693 4.45583 5.31591 4.37413 5.3468C4.19014 5.41632 3.99505 5.61362 3.94072 5.78513C3.91555 5.86468 3.60641 9.53677 3.25382 13.9454C2.55453 22.6882 2.57558 22.2493 2.82932 22.8035C2.97543 23.1226 3.38882 23.5666 3.69496 23.7333C4.21063 24.0139 3.7798 24.0001 11.9766 24.0001C20.1798 24.0001 19.7419 24.0143 20.2619 23.7312C20.5552 23.5716 20.9575 23.138 21.1086 22.8185C21.3686 22.269 21.3906 22.7392 20.6979 14.0544C20.3498 9.68893 20.0526 6.02771 20.0374 5.91826C20.005 5.6836 19.8269 5.45185 19.6092 5.36087C19.4923 5.31207 19.147 5.29693 18.1493 5.29693H16.8426L16.8134 4.72271C16.7304 3.08799 15.9551 1.68844 14.6524 0.821678C14.1859 0.511272 13.6342 0.266725 13.1016 0.134303C12.5955 0.00844365 11.6363 -0.0221189 11.1401 0.0717718ZM12.8218 1.50043C14.3608 1.89404 15.4203 3.26452 15.4215 4.86333L15.4219 5.29693H11.9709H8.51994L8.54239 4.72271C8.55997 4.27393 8.59166 4.0666 8.68747 3.77349C9.05497 2.64948 9.95708 1.8053 11.1146 1.50216C11.5958 1.37616 12.3329 1.37541 12.8218 1.50043ZM7.12499 8.05562C7.12499 9.38101 7.1271 9.41115 7.23046 9.56302C7.40014 9.8124 7.56439 9.90872 7.81977 9.90872C8.08321 9.90872 8.29902 9.78005 8.42647 9.54713C8.49725 9.41776 8.50968 9.22383 8.52214 8.05083L8.53653 6.70318H11.9772H15.4179L15.4316 8.08266C15.4446 9.39132 15.4503 9.46876 15.542 9.59148C15.7155 9.82379 15.8716 9.90872 16.125 9.90872C16.3784 9.90872 16.5344 9.82379 16.708 9.59148C16.7997 9.46876 16.8054 9.39132 16.8184 8.08266L16.8321 6.70318H17.7667C18.6983 6.70318 18.7014 6.70351 18.7228 6.80865C18.7756 7.06763 19.9287 21.8847 19.908 22.0388C19.8813 22.2384 19.7641 22.4135 19.5865 22.5196C19.4743 22.5865 18.7242 22.5938 11.9787 22.5938C3.71272 22.5938 4.31694 22.6165 4.12311 22.2985C4.06949 22.2106 4.03077 22.0611 4.0296 21.9376C4.02847 21.8215 4.29702 18.3464 4.62627 14.2149L5.225 6.70318H6.17497H7.12499V8.05562ZM6.18589 18.7416C5.7875 18.9182 5.6331 19.3917 5.85617 19.7528C6.062 20.0858 5.63235 20.065 12.0396 20.0514L17.8049 20.0391L17.9304 19.9219C18.3015 19.5751 18.2631 19.0497 17.8481 18.7967C17.6977 18.705 17.6033 18.7036 11.9766 18.7066C8.83124 18.7084 6.22546 18.7241 6.18589 18.7416Z"
                    fill="currentColor"
                />
            </svg>
        </a>
    <?php } ?>
</div>

<div class="product__body">
    <div class="product__info">
        <p class="product__brand">
            <?= $item->brand_title ?>
        </p>

        <a
            href="<?= $productUrl ?>"
            class="product__name"
        >
            <?= $productTitle ?>
        </a>
    </div>

    <div class="product__other">
        <div class="product__price price-product">
            <?php if ($isB2B) { ?>
                <div class="price-product__prices">
                    <span class="price-product__value">
                        <?= number_format(
                            $displayPrice,
                            0,
                            '.',
                            ' '
                        ) ?>
                        <?= MDL ?>
                    </span>
                </div>

                <div class="price-product__prices">
                    <span>
                        <?= TXT_ANGRO_PRICE ?>
                    </span>

                    <span class="price-product__value">
                        <?= number_format(
                            $displayWholesalePrice,
                            0,
                            '.',
                            ' '
                        ) ?>
                        <?= MDL ?>
                    </span>
                </div>
            <?php } elseif ($hasDiscount) { ?>
                <div class="price-product__prices">
                    <span class="price-product__value">
                        <?= number_format(
                            $currentPrice,
                            0,
                            '.',
                            ' '
                        ) ?>
                        <?= MDL ?>
                    </span>

                    <span class="price-product__old">
                        <?= number_format(
                            $displayPrice,
                            0,
                            '.',
                            ' '
                        ) ?>
                        <?= MDL ?>
                    </span>
                </div>
            <?php } else { ?>
                <div class="price-product__prices">
                    <span class="price-product__value">
                        <?= number_format(
                            $currentPrice,
                            0,
                            '.',
                            ' '
                        ) ?>
                        <?= MDL ?>
                    </span>
                </div>

                <?php if ($bonusAmount > 0) { ?>
                    <div class="price-product__bonuses">
                        <span>
                            +<?= number_format(
                                $bonusAmount,
                                1,
                                '.',
                                ''
                            ) ?>
                            <?= PRODUCTS_BONUSES ?>
                        </span>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
</div>
