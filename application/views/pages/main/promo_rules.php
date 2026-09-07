<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian =
    isset($lclang)
    && $lclang === 'ro';

$desktopImage =
    '/app/img/promo-rules/promo-rules-desktop.webp';

$mobileImage =
    '/app/img/promo-rules/promo-rules-mobile.webp';
?>

<main class="page promo-rules-page">

    <!-- HERO -->
    <section class="promo-rules-hero">
        <picture>
            <source
                media="(max-width: 767.98px)"
                srcset="<?= $mobileImage ?>"
            >

            <img
                src="<?= $desktopImage ?>"
                alt="<?= htmlspecialchars(
                    $page_name,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                class="promo-rules-hero__image"
            >
        </picture>

        <h1 class="promo-rules-page__seo-title">
            <?= htmlspecialchars(
                $page_name,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>
    </section>

    <!-- BREADCRUMBS -->
    <div class="promo-rules-breadcrumbs">
        <div class="_container">
            <div class="promo-rules-breadcrumbs__list">

                <a
                    href="/<?= $lclang ?>"
                    class="promo-rules-breadcrumbs__link"
                >
                    <?= $isRomanian
                        ? 'Acasă'
                        : 'Главная' ?>
                </a>

                <span class="promo-rules-breadcrumbs__separator">
                    /
                </span>

                <span class="promo-rules-breadcrumbs__current">
                    <?= $isRomanian
                        ? 'Reguli de aplicare a promocodului'
                        : 'Правила применения промокода' ?>
                </span>

            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <section class="promo-rules-content">
        <div class="promo-rules-content__inner">

            <?php if ($isRomanian) : ?>

                <p>
                    Consultați regulile de utilizare a promocodurilor:
                </p>

                <ul>
                    <li>
                        Promocodul poate fi utilizat la plasarea unei
                        comenzi în magazinul online Vizaje-Nica.
                    </li>

                    <li>
                        Pentru utilizarea anumitor promocoduri poate fi
                        necesară autentificarea în cont.
                    </li>

                    <li>
                        Condițiile promocodului pot varia în funcție de
                        promoție: perioada de valabilitate, valoarea minimă
                        a comenzii, numărul de utilizări și brandurile
                        participante.
                    </li>

                    <li>
                        Reducerea se aplică numai produselor care
                        îndeplinesc condițiile promocodului.
                    </li>

                    <li>
                        Dacă în coș există produse ale unor branduri pentru
                        care promocodul nu este valabil, reducerea se va
                        aplica numai produselor eligibile.
                    </li>

                    <li>
                        Promocodul poate să nu se cumuleze cu alte reduceri
                        sau oferte speciale, în funcție de condițiile
                        promoției.
                    </li>
                </ul>

                <p>
                    Cum beneficiați de reducerea oferită prin promocod:
                </p>

                <ol>
                    <li>
                        Adăugați produsele dorite în coș.
                    </li>

                    <li>
                        Introduceți promocodul în câmpul
                        „Cod promoțional”.
                    </li>

                    <li>
                        Aplicați promocodul.
                    </li>

                    <li>
                        După verificarea condițiilor, reducerea va fi
                        calculată automat și afișată în coș.
                    </li>
                </ol>

                <p>
                    Livrarea gratuită este disponibilă pentru comenzile
                    care îndeplinesc valoarea minimă stabilită de
                    Vizaje-Nica.
                </p>

                <p>
                    Promocodul poate să nu fie valabil pentru anumite
                    branduri. Dacă în coș există produse ale unor astfel
                    de branduri, acestea vor fi indicate direct în coș.
                </p>

                <p>
                    Lista brandurilor excluse și celelalte condiții pot
                    varia în funcție de promocod și de campania
                    promoțională.
                </p>

            <?php else : ?>

                <p>
                    Ознакомьтесь с правилами применения промокодов:
                </p>

                <ul>
                    <li>
                        Промокод можно использовать при оформлении заказа
                        в интернет-магазине Vizaje-Nica.
                    </li>

                    <li>
                        Для использования некоторых промокодов может
                        потребоваться авторизация в аккаунте.
                    </li>

                    <li>
                        Условия действия промокода могут зависеть от
                        конкретной акции: срока действия, минимальной
                        суммы заказа, количества использований и списка
                        участвующих брендов.
                    </li>

                    <li>
                        Скидка применяется только к товарам,
                        соответствующим условиям промокода.
                    </li>

                    <li>
                        Если в корзине находятся товары брендов, на
                        которые промокод не действует, скидка будет
                        рассчитана только для подходящих товаров.
                    </li>

                    <li>
                        Промокод может не суммироваться с другими
                        скидками и специальными предложениями, если это
                        предусмотрено условиями акции.
                    </li>
                </ul>

                <p>
                    Как получить скидку по промокоду:
                </p>

                <ol>
                    <li>
                        Добавьте нужные товары в корзину.
                    </li>

                    <li>
                        Введите код в поле «Промокод».
                    </li>

                    <li>
                        Примените промокод.
                    </li>

                    <li>
                        После проверки условий скидка автоматически
                        пересчитается и отобразится в корзине.
                    </li>
                </ol>

                <p>
                    Бесплатная доставка действует для заказов,
                    соответствующих установленной Vizaje-Nica
                    минимальной сумме.
                </p>

                <p>
                    Скидка по промокоду может не распространяться на
                    отдельные бренды. Если такие товары находятся в
                    корзине, соответствующие бренды будут указаны
                    непосредственно в корзине.
                </p>

                <p>
                    Список брендов-исключений и другие условия могут
                    отличаться для каждого промокода и акции.
                </p>

            <?php endif; ?>

        </div>
    </section>

</main>