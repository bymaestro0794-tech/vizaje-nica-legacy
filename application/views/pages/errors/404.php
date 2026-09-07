<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';

$homeUrl = '/' . $lclang;

$catalogUrl = '/' . $lclang . '/' . (
    !empty($menu['all'][3]->uri)
        ? $menu['all'][3]->uri
        : ($isRomanian ? 'catalog' : 'katalog')
);

/*
 * Готовое изображение с девушкой и цифрами 404.
 * Замени путь, если файл называется иначе.
 */
$errorImage = '/app/img/404.png';

/*
 * Фирменный знак Vizaje-Nica между разделительными линиями.
 */
$brandLogo = '/app/img/icons-2/logo-2.svg';
?>

<main class="page page-404">
    <section class="error-404">
        <div class="error-404__container _container">
            <div class="error-404__content">

                <div class="error-404__visual">
                    <img
                        src="<?= htmlspecialchars(
                            $errorImage,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        alt="<?= $isRomanian
                            ? 'Eroare 404'
                            : 'Ошибка 404' ?>"
                        class="error-404__visual-image"
                        width="1400"
                        height="760"
                        decoding="async"
                    >
                </div>

                <div class="error-404__body">
                    <p class="error-404__eyebrow">
                        <?= $isRomanian
                            ? 'Eroare 404'
                            : 'Ошибка 404' ?>
                    </p>

                    <h1 class="error-404__title">
                        <?= $isRomanian
                            ? 'Pagina nu a fost găsită'
                            : 'Страница не найдена' ?>
                    </h1>

                    <p class="error-404__text">
                        <?= $isRomanian
                            ? 'Pagina pe care o căutați nu există sau a fost mutată.'
                            : 'Страница, которую вы ищете, не существует или была перемещена.' ?>
                    </p>

                    <div
                        class="error-404__separator"
                        aria-hidden="true"
                    >
                        <span class="error-404__separator-line"></span>

                        <span class="error-404__separator-logo">
                            <img
                                src="<?= htmlspecialchars(
                                    $brandLogo,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                alt=""
                                width="42"
                                height="42"
                            >
                        </span>

                        <span class="error-404__separator-line"></span>
                    </div>

                    <p class="error-404__question">
                        <?= $isRomanian
                            ? 'Unde doriți să mergeți?'
                            : 'Куда хотите отправиться?' ?>
                    </p>

                    <div class="error-404__actions">
                        <a
                            href="<?= htmlspecialchars(
                                $homeUrl,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="
                                error-404__button
                                error-404__button--primary
                            "
                        >
                            <?= $isRomanian
                                ? 'Pagina principală'
                                : 'На главную' ?>
                        </a>

                        <a
                            href="<?= htmlspecialchars(
                                $catalogUrl,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="
                                error-404__button
                                error-404__button--secondary
                            "
                        >
                            <?= $isRomanian
                                ? 'În catalog'
                                : 'В каталог' ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>