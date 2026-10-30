<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';
$giftCardHomeUrl = '/' . $lclang;
$giftCardCartUrl = '/' . $lclang . ($isRomanian ? '/cos' : '/korzina');
?>

<header class="gift-card-mini-header">
    <a class="gift-card-mini-header__logo" href="<?= $giftCardHomeUrl ?>">
        <img src="/app/img/icons-2/logo-2.svg" alt="Vizaje-Nica">
        <span>VIZAJE-NICA</span>
    </a>

    <nav class="gift-card-mini-header__actions" aria-label="Навигация">
        <a href="<?= $isRomanian ? '/ru/podarochnaya-karta' : '/ro/card-cadou' ?>">
            <?= $isRomanian ? 'RU' : 'RO' ?>
        </a>
        <a
            class="gift-card-mini-header__cart"
            href="<?= $giftCardCartUrl ?>"
            data-cart-drawer-open
        >
            <img src="/app/img/icons/cart.svg" alt="" width="22" height="22">
            <span><?= $isRomanian ? 'Coș' : 'Корзина' ?></span>
            <strong
                data-gift-card-cart-count
                data-cart-count
                <?= empty($cart_count) ? 'hidden' : '' ?>
            ><?= (int) ($cart_count ?? 0) ?></strong>
        </a>
    </nav>
</header>
