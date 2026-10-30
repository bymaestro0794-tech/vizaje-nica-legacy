<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';
$options = !empty($gift_card_options)
    ? $gift_card_options
    : array(200, 300, 500, 1000);
$initialOption = is_array($options[0])
    ? $options[0]
    : array(
        'amount' => (int) $options[0],
        'variation_id' => 0,
        'image' => '/app/b2b/img/certificate-main.png',
    );
$initialAmount = (int) $initialOption['amount'];
$initialImage = !empty($initialOption['image'])
    ? $initialOption['image']
    : '/app/b2b/img/certificate-main.png';
$catalogUri = $isRomanian ? 'catalog-categories' : 'katalog-categories';
$wishlistUri = $isRomanian ? 'favorite' : 'izbrannyie';
$accountUri = $isRomanian ? 'contul-meu' : 'lichnyiy-kabinet';
$cartUri = $isRomanian ? 'cos' : 'korzina';
?>

<main class="gift-card-page">
    <section class="gift-card-page__layout" aria-labelledby="gift-card-title">
        <div class="gift-card-page__content">
            <p class="gift-card-page__eyebrow">
                <?= $isRomanian ? 'Card cadou' : 'Подарочная карта' ?>
            </p>

            <h1 class="gift-card-page__title" id="gift-card-title">
                <?= $isRomanian
                    ? 'Oferă un card cadou Vizaje-Nica'
                    : 'Подарите карту Vizaje-Nica' ?>
            </h1>

            <p class="gift-card-page__description">
                <?= $isRomanian
                    ? 'Un cadou simplu pentru alegerea produselor preferate de frumusețe și parfumerie.'
                    : 'Подарок, с которым легко выбрать любимую косметику и парфюмерию.' ?>
            </p>

            <form
                class="gift-card-page__form"
                data-gift-card-form
                data-product-id="<?= (int) ($gift_card_product_id ?? 0) ?>"
                data-confirmation-text="<?= $isRomanian ? 'Adăugat în coș' : 'Добавлено в корзину' ?>"
                data-error-text="<?= $isRomanian ? 'Nu am putut adăuga cardul în coș.' : 'Не удалось добавить карту в корзину.' ?>"
                data-submit-text="<?= $isRomanian ? 'Adaugă în coș' : 'Добавить в корзину' ?>"
            >
                <fieldset class="gift-card-page__fieldset">
                    <legend class="gift-card-page__legend">
                        <?= $isRomanian ? 'Alege valoarea' : 'Выберите номинал' ?>
                    </legend>

                    <div class="gift-card-page__amounts">
                        <?php foreach ($options as $index => $option) : ?>
                            <?php
                            $amount = is_array($option)
                                ? (int) $option['amount']
                                : (int) $option;
                            $variationId = is_array($option)
                                ? (int) ($option['variation_id'] ?? 0)
                                : 0;
                            $image = is_array($option)
                                ? ($option['image'] ?? $initialImage)
                                : $initialImage;
                            ?>
                            <button
                                type="button"
                                class="gift-card-page__amount <?= $index === 0 ? 'is-selected' : '' ?>"
                                data-gift-card-amount="<?= (int) $amount ?>"
                                data-gift-card-variation="<?= $variationId ?>"
                                data-gift-card-image="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                                aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>"
                            >
                                <span><?= number_format((int) $amount, 0, '.', ' ') ?></span>
                                <small>MDL</small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="gift-card-page__quantity-row">
                    <div>
                        <p class="gift-card-page__legend">
                            <?= $isRomanian ? 'Cantitate' : 'Количество' ?>
                        </p>
                        <p class="gift-card-page__muted">
                            <?= $isRomanian ? 'Câte carduri doriți?' : 'Сколько карт нужно?' ?>
                        </p>
                    </div>

                    <div class="gift-card-page__quantity" aria-label="Количество карт">
                        <button type="button" data-gift-card-decrease aria-label="Уменьшить количество">−</button>
                        <output data-gift-card-quantity>1</output>
                        <button type="button" data-gift-card-increase aria-label="Увеличить количество">+</button>
                    </div>
                </div>

                <div class="gift-card-page__summary">
                    <span><?= $isRomanian ? 'Total' : 'Итого' ?></span>
                    <strong data-gift-card-total><?= number_format($initialAmount, 0, '.', ' ') ?> MDL</strong>
                </div>

                <button class="gift-card-page__submit" type="submit">
                    <?= $isRomanian ? 'Adaugă în coș' : 'Добавить в корзину' ?>
                </button>

                <p class="gift-card-page__message" data-gift-card-message aria-live="polite"></p>
            </form>

            <div class="gift-card-page__faq">
                <h2><?= $isRomanian ? 'Întrebări frecvente' : 'Часто задаваемые вопросы' ?></h2>
                <details>
                    <summary><?= $isRomanian ? 'Unde poate fi folosit cardul?' : 'Где можно использовать карту?' ?></summary>
                    <p><?= $isRomanian ? 'Cardul poate fi folosit pentru produsele Vizaje-Nica.' : 'Карту можно использовать для покупок в Vizaje-Nica.' ?></p>
                </details>
                <details>
                    <summary><?= $isRomanian ? 'Pot alege un alt design?' : 'Можно выбрать другой дизайн?' ?></summary>
                    <p><?= $isRomanian ? 'Designul și opțiunile de personalizare vor fi adăugate la pasul următor.' : 'Выбор дизайна и персонализация могут появиться на следующем шаге оформления.' ?></p>
                </details>
            </div>
        </div>

        <aside class="gift-card-page__visual" aria-label="Подарочная карта">
            <div class="gift-card-page__topbar">
                <span class="gift-card-page__topbar-label">
                    <?= $isRomanian ? 'Card cadou' : 'Подарочная карта' ?>
                </span>
                <a
                    class="gift-card-page__close"
                    href="/<?= $lclang ?>"
                    aria-label="<?= $isRomanian ? 'Înapoi la pagina principală' : 'Вернуться на главную' ?>"
                >
                    <img src="/app/img/icons/close.svg" alt="" aria-hidden="true">
                </a>
            </div>

            <div class="gift-card-page__card-wrap">
                <div class="gift-card-page__card-scene" data-gift-card-scene>
                    <div class="gift-card-page__card-motion">
                        <img
                            class="gift-card-page__card-image"
                            src="<?= htmlspecialchars($initialImage, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Подарочная карта Vizaje-Nica"
                            data-gift-card-card-image
                        >
                    </div>
                </div>
                <div class="gift-card-page__caption">
                    <span></span>
                    <strong><?= $isRomanian ? 'Card cadou' : 'Подарочная карта' ?></strong>
                    <span></span>
                </div>
                <p class="gift-card-page__preview-amount" data-gift-card-preview>
                    <?= number_format($initialAmount, 0, '.', ' ') ?> MDL
                </p>
            </div>
        </aside>
    </section>

    <nav class="mobile-tabbar gift-card-mobile-tabbar" aria-label="<?= $isRomanian ? 'Navigare principală' : 'Основная навигация' ?>">
        <a href="/<?= $lclang ?>" class="mobile-tabbar__item mobile-tabbar__item--brand">
            <span class="mobile-tabbar__icon">
                <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--brand" aria-hidden="true"></span>
            </span>
        </a>
        <a href="/<?= $lclang ?>/<?= $catalogUri ?>" class="mobile-tabbar__item mobile-tabbar__item--catalog" aria-label="<?= $isRomanian ? 'Catalog' : 'Каталог' ?>">
            <span class="mobile-tabbar__icon">
                <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--catalog" aria-hidden="true"></span>
            </span>
        </a>
        <a href="/<?= $lclang ?>/<?= $wishlistUri ?>" class="mobile-tabbar__item mobile-tabbar__item--wishlist" aria-label="<?= $isRomanian ? 'Favorite' : 'Избранное' ?>">
            <span class="mobile-tabbar__icon">
                <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--wishlist" aria-hidden="true"></span>
            </span>
        </a>
        <a href="/<?= $lclang ?>/<?= $accountUri ?>" class="mobile-tabbar__item mobile-tabbar__item--profile" aria-label="<?= $isRomanian ? 'Profil' : 'Профиль' ?>">
            <span class="mobile-tabbar__icon">
                <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--profile" aria-hidden="true"></span>
            </span>
        </a>
        <a href="/<?= $lclang ?>/<?= $cartUri ?>" class="mobile-tabbar__item mobile-tabbar__item--cart" aria-label="<?= $isRomanian ? 'Coș' : 'Корзина' ?>">
            <span class="mobile-tabbar__icon">
                <span class="mobile-tabbar__asset-icon mobile-tabbar__asset-icon--bag" aria-hidden="true"></span>
            </span>
            <span class="mobile-tabbar__badge" data-gift-card-cart-count<?= empty($cart_count) ? ' hidden' : '' ?>><?= (int) ($cart_count ?? 0) ?></span>
        </a>
    </nav>
</main>
