<?php
$cartUri = !empty($menu['all'][14]->uri)
    ? $menu['all'][14]->uri
    : 'cart';

$cartUrl = $lclang === 'ro'
    ? '/ro/cos'
    : '/ru/korzina';

$notificationText = [
    'cart_added' => $lclang === 'ro'
        ? 'Produsul a fost adăugat în coș'
        : 'Товар добавлен в корзину',

    'go_to_cart' => $lclang === 'ro'
        ? 'Mergi în coș'
        : 'Перейти в корзину',

    'continue' => $lclang === 'ro'
        ? 'Continuă cumpărăturile'
        : 'Продолжить покупки',

    'close' => $lclang === 'ro'
        ? 'Închide'
        : 'Закрыть',
    'remove_title' => $lclang === 'ro'
    ? 'Elimină produsul'
    : 'Удалить товар',

    'remove_question' => $lclang === 'ro'
        ? 'Doriți să eliminați acest produs din coș?'
        : 'Вы действительно хотите удалить этот товар из корзины?',

    'cancel' => $lclang === 'ro'
        ? 'Anulează'
        : 'Отмена',

    'remove' => $lclang === 'ro'
        ? 'Elimină'
        : 'Удалить',
];
?>

<div
    class="vn-notifications"
    id="vnNotifications"
    data-cart-url="<?= htmlspecialchars(
        $cartUrl,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>
    <div
        class="vn-toast-region"
        id="vnToastRegion"
        aria-live="polite"
        aria-atomic="true"
    ></div>

    <div
        class="vn-cart-added"
        id="vnCartAdded"
        aria-hidden="true"
    >
        <div
            class="vn-cart-added__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="vnCartAddedTitle"
        >
            <div class="vn-cart-added__head">
                <div class="vn-cart-added__status">
                    <span
                        class="vn-cart-added__status-icon"
                        aria-hidden="true"
                    >
                        <svg
                            width="14"
                            height="14"
                            viewBox="0 0 14 14"
                            fill="none"
                        >
                            <path
                                d="M3 7.2L5.5 9.7L11 4.3"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <h2
                        class="vn-cart-added__title"
                        id="vnCartAddedTitle"
                    >
                        <?= $notificationText['cart_added'] ?>
                    </h2>
                </div>

                <button
                    class="vn-cart-added__close"
                    type="button"
                    data-vn-cart-close
                    aria-label="<?= $notificationText['close'] ?>"
                >
                    <svg
                        width="22"
                        height="22"
                        viewBox="0 0 22 22"
                        fill="none"
                    >
                        <path
                            d="M5 5L17 17M17 5L5 17"
                            stroke="currentColor"
                            stroke-width="1.4"
                            stroke-linecap="round"
                        />
                    </svg>
                </button>
            </div>

            <div class="vn-cart-added__product">
                <div
                    class="vn-cart-added__image"
                    hidden
                >
                    <img
                        src=""
                        alt=""
                        data-vn-cart-image
                    >
                </div>

                <div class="vn-cart-added__content">
                    <p
                        class="vn-cart-added__brand"
                        data-vn-cart-brand
                    ></p>

                    <p
                        class="vn-cart-added__name"
                        data-vn-cart-name
                    ></p>

                    <p
                        class="vn-cart-added__variant"
                        data-vn-cart-variant
                    ></p>

                    <p
                        class="vn-cart-added__price"
                        data-vn-cart-price
                    ></p>
                </div>
            </div>

            <div class="vn-cart-added__actions">
                <a
                    class="
                        vn-cart-added__button
                        vn-cart-added__button--primary
                    "
                    href="<?= htmlspecialchars(
                        $cartUrl,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    <span>
                        <?= $notificationText['go_to_cart'] ?>
                    </span>

                    <span data-vn-cart-count></span>
                </a>

                <button
                    class="
                        vn-cart-added__button
                        vn-cart-added__button--secondary
                    "
                    type="button"
                    data-vn-cart-close
                >
                    <?= $notificationText['continue'] ?>
                </button>
            </div>
        </div>
    </div>
    <div
    class="vn-remove-modal"
    id="vnRemoveModal"
    aria-hidden="true"
>
    <div
        class="vn-remove-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="vnRemoveModalTitle"
    >
        <div class="vn-remove-modal__head">
            <h2
                class="vn-remove-modal__title"
                id="vnRemoveModalTitle"
            >
                <?= $notificationText['remove_title'] ?>
            </h2>

            <button
                class="vn-remove-modal__close"
                type="button"
                data-vn-remove-close
                aria-label="<?= $notificationText['close'] ?>"
            >
                <svg
                    width="22"
                    height="22"
                    viewBox="0 0 22 22"
                    fill="none"
                >
                    <path
                        d="M5 5L17 17M17 5L5 17"
                        stroke="currentColor"
                        stroke-width="1.4"
                        stroke-linecap="round"
                    />
                </svg>
            </button>
        </div>

        <p class="vn-remove-modal__question">
            <?= $notificationText['remove_question'] ?>
        </p>

        <div class="vn-remove-modal__product">
            <div
                class="vn-remove-modal__image"
                data-vn-remove-image-wrap
                hidden
            >
                <img
                    src=""
                    alt=""
                    data-vn-remove-image
                >
            </div>

            <div class="vn-remove-modal__content">
                <p
                    class="vn-remove-modal__name"
                    data-vn-remove-name
                ></p>

                <p
                    class="vn-remove-modal__variant"
                    data-vn-remove-variant
                ></p>

                <p
                    class="vn-remove-modal__price"
                    data-vn-remove-price
                ></p>
            </div>
        </div>

        <div class="vn-remove-modal__actions">
            <button
                class="
                    vn-remove-modal__button
                    vn-remove-modal__button--secondary
                "
                type="button"
                data-vn-remove-close
            >
                <?= $notificationText['cancel'] ?>
            </button>

            <button
                class="
                    vn-remove-modal__button
                    vn-remove-modal__button--primary
                "
                type="button"
                data-vn-remove-confirm
            >
                <?= $notificationText['remove'] ?>
            </button>
        </div>
    </div>
</div>
</div>