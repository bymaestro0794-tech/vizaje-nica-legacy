<?php
$isRomanian = $lclang === 'ro';
?>

<div
    class="cart-drawer"
    id="cartDrawer"
    aria-hidden="true"
    data-cart-drawer
     data-lang="<?= htmlspecialchars(
        $lclang,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>
    <div
        class="cart-drawer__overlay"
        data-cart-drawer-close
        aria-hidden="true"
    ></div>

    <aside
        class="cart-drawer__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cartDrawerTitle"
        tabindex="-1"
        data-cart-drawer-panel
    >
        <header class="cart-drawer__header">
            <div class="cart-drawer__heading">
                <h2
                    class="cart-drawer__title"
                    id="cartDrawerTitle"
                >
                    <?= $isRomanian
                        ? 'Coș'
                        : 'Корзина' ?>
                </h2>

               <span
                    class="cart-drawer__count"
                    data-cart-drawer-count
                >
                    <?php if (!empty($total_items_cart)) : ?>
                        / <?= (int) $total_items_cart ?>
                        <?= $isRomanian ? 'buc.' : 'шт.' ?>
                    <?php endif; ?>
                </span>
            </div>

            <button
                type="button"
                class="cart-drawer__close"
                data-cart-drawer-close
                aria-label="<?= $isRomanian
                    ? 'Închide coșul'
                    : 'Закрыть корзину' ?>"
            >
                <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M5 5L19 19M19 5L5 19"
                        stroke="currentColor"
                        stroke-width="1.4"
                        stroke-linecap="round"
                    />
                </svg>
            </button>
        </header>

        <div
            class="cart-drawer__body"
            data-cart-drawer-body
            aria-live="polite"
            aria-busy="false"
        >
            <div
                class="cart-drawer__loading"
                data-cart-drawer-loading
            >
                <div class="cart-drawer-skeleton">
                    <div class="cart-drawer-skeleton__image"></div>

                    <div class="cart-drawer-skeleton__content">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <div class="cart-drawer-skeleton">
                    <div class="cart-drawer-skeleton__image"></div>

                    <div class="cart-drawer-skeleton__content">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="cart-drawer-sticky"
            data-cart-drawer-sticky
            hidden
        >
            <div class="cart-drawer-sticky__totals">
                <div
                    class="cart-drawer-sticky__discount"
                    data-cart-sticky-discount-wrap
                    hidden
                >
                    <span>
                        <?= $isRomanian
                            ? 'Reducere'
                            : 'Скидка' ?>
                    </span>

                    <strong data-cart-sticky-discount>
                        0 MDL
                    </strong>
                </div>

                <div class="cart-drawer-sticky__total">
                    <span>
                        <?= $isRomanian
                            ? 'Total'
                            : 'Итого' ?>
                    </span>

                    <strong data-cart-sticky-total>
                        0 MDL
                    </strong>
                </div>
            </div>

            <a
                href="/<?= $lclang ?>/<?= $menu['all'][19]->uri ?>"
                class="cart-drawer-sticky__checkout"
            >
                <?= $isRomanian
                    ? 'Finalizează comanda'
                    : 'Оформить заказ' ?>
            </a>
        </div>
        <div
            class="cart-drawer-action-sheet"
            data-cart-action-sheet
            aria-hidden="true"
        >
            <div
                class="cart-drawer-action-sheet__overlay"
                data-cart-action-sheet-close
            ></div>

            <div
                class="cart-drawer-action-sheet__panel"
                role="dialog"
                aria-modal="true"
            >
                <div class="cart-drawer-action-sheet__handle"></div>

                <div class="cart-drawer-action-sheet__content">
                    <div class="cart-drawer-action-sheet__quantity-row">
                        <strong>
                            <?= $isRomanian
                                ? 'Modifică cantitatea'
                                : 'Изменить количество' ?>
                        </strong>

                        <div
                            class="cart-drawer-action-sheet__quantity"
                            data-cart-sheet-quantity
                        >
                            <button
                                type="button"
                                data-cart-sheet-minus
                            >
                                −
                            </button>

                            <span data-cart-sheet-value>
                                1
                            </span>

                            <button
                                type="button"
                                data-cart-sheet-plus
                            >
                                +
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="cart-drawer-action-sheet__remove"
                        data-cart-sheet-remove
                    >
                        <?= $isRomanian
                            ? 'Șterge'
                            : 'Удалить' ?>
                    </button>
                </div>
            </div>
        </div>
    </aside>
</div>