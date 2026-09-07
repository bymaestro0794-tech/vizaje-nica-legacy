<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';

$homeUrl = '/' . $lclang;

$certificatesUrl = $isRomanian
    ? '/ro/certificate-de-autenticitate'
    : '/ru/sertifikaty-podlinnosti';

$certificatesTitle = $isRomanian
    ? 'Certificate de autenticitate'
    : 'Сертификаты подлинности';

/*
 * Формируем пункты существующих footer-меню.
 * ID сохраняем без изменений, чтобы не ломать текущую админку.
 */
$footerMenuFirst = [
    $menu['bottom'][4] ?? null,
    $menu['bottom'][7] ?? null,
    $menu['bottom'][31] ?? null,
    $menu['bottom'][32] ?? null,
];

$footerMenuSecond = [
    $menu['bottom'][29] ?? null,
    $menu['bottom'][9] ?? null,
    $menu['bottom'][30] ?? null,
];

$footerMenuThird = [
    $menu['bottom'][11] ?? null,
    $menu['bottom'][10] ?? null,
    $menu['bottom'][22] ?? null,
    $menu['bottom'][23] ?? null,
];

$socialLinks = [
    [
        'url' => !empty(FACEBOOK) ? FACEBOOK : null,
        'icon' => '/app/img/icons/social/fb.svg',
        'title' => 'Facebook',
    ],
    [
        'url' => !empty(INSTAGRAM) ? INSTAGRAM : null,
        'icon' => '/app/img/icons/social/inst.svg',
        'title' => 'Instagram',
    ],
    [
        'url' => !empty(YOUTUBE) ? YOUTUBE : null,
        'icon' => '/app/img/icons/social/yt.svg',
        'title' => 'YouTube',
    ],
];
?>

<footer class="footer">
    <div class="footer__main main-footer">
        <div class="main-footer__container _container">
            <div class="main-footer__content">

                <!-- Бренд -->
                <div class="main-footer__column main-footer__column_brand">
                    <a
                        href="<?= htmlspecialchars(
                            $homeUrl,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        class="main-footer__logo"
                        aria-label="Vizaje-Nica"
                    >
                        <picture>
                            <source
                                srcset="/app/img/logo_v.webp"
                                type="image/webp"
                            >

                            <img
                                src="/app/img/logo_v.png"
                                alt="Vizaje-Nica"
                                width="120"
                                height="120"
                            >
                        </picture>
                    </a>

                    <p class="main-footer__description">
                        <?= $isRomanian
                            ? 'Cosmetice și parfumerie originală din 1992.'
                            : 'Оригинальная косметика и парфюмерия с 1992 года.' ?>
                    </p>

                    <div
                        class="main-footer__social social-main-footer"
                        aria-label="<?= $isRomanian
                            ? 'Rețele sociale'
                            : 'Социальные сети' ?>"
                    >
                        <?php foreach ($socialLinks as $social) : ?>
                            <?php if (empty($social['url'])) : ?>
                                <?php continue; ?>
                            <?php endif; ?>

                            <a
                                href="<?= htmlspecialchars(
                                    $social['url'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                class="social-main-footer__item"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="<?= htmlspecialchars(
                                    $social['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <img
                                    src="<?= htmlspecialchars(
                                        $social['icon'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    alt=""
                                    width="20"
                                    height="20"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Первое меню -->
                <div class="main-footer__column">
                    <p class="main-footer__name">
                        <?= FOOTER_MENU1 ?>
                    </p>

                    <ul class="main-footer__list">
                        <?php foreach ($footerMenuFirst as $menuItem) : ?>
                            <?php if (empty($menuItem)) : ?>
                                <?php continue; ?>
                            <?php endif; ?>

                            <li class="main-footer__item">
                                <a
                                    href="/<?= $lclang ?>/<?= htmlspecialchars(
                                        $menuItem->uri,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="main-footer__link"
                                >
                                    <?= htmlspecialchars(
                                        $menuItem->title,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>

                        <li class="main-footer__item">
                            <a
                                href="<?= $certificatesUrl ?>"
                                class="main-footer__link"
                            >
                                <?= $certificatesTitle ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Второе меню -->
                <div class="main-footer__column">
                    <p class="main-footer__name">
                        <?= FOOTER_MENU2 ?>
                    </p>

                    <ul class="main-footer__list">
                        <?php foreach ($footerMenuSecond as $menuItem) : ?>
                            <?php if (empty($menuItem)) : ?>
                                <?php continue; ?>
                            <?php endif; ?>

                            <li class="main-footer__item">
                                <a
                                    href="/<?= $lclang ?>/<?= htmlspecialchars(
                                        $menuItem->uri,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="main-footer__link"
                                >
                                    <?= htmlspecialchars(
                                        $menuItem->title,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Третье меню -->
                <div class="main-footer__column">
                    <p class="main-footer__name">
                        <?= FOOTER_MENU3 ?>
                    </p>

                    <ul class="main-footer__list">
                        <?php foreach ($footerMenuThird as $menuItem) : ?>
                            <?php if (empty($menuItem)) : ?>
                                <?php continue; ?>
                            <?php endif; ?>

                            <li class="main-footer__item">
                                <a
                                    href="/<?= $lclang ?>/<?= htmlspecialchars(
                                        $menuItem->uri,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    class="main-footer__link"
                                >
                                    <?= htmlspecialchars(
                                        $menuItem->title,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <div class="footer__bottom bottom-footer">
        <div class="bottom-footer__container _container">
            <p class="bottom-footer__copy">
                © Vizaje-Nica, 1992—<?= date('Y') ?>.
                <span><?= ALL_RESERVED ?></span>
            </p>

            <button
                type="button"
                class="bottom-footer__cookie-link"
                data-cookie-action="settings"
            ><?= COOKIE_SETTINGS_LINK ?></button>

            <?php if (!empty($ilab_linc) && !empty($ilab)) : ?>
                <a
                    href="<?= htmlspecialchars(
                        $ilab_linc,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    class="bottom-footer__developer"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <img
                        src="/app/img/icons/dev.svg"
                        alt=""
                        width="20"
                        height="20"
                        loading="lazy"
                        decoding="async"
                    >

                    <span>
                        <?= htmlspecialchars(
                            $ilab,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</footer>