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
        'url' => 'https://www.tiktok.com/@vizajenica',
        'icon' => '/app/img/icons/social/tiktok.svg',
        'title' => 'TikTok',
    ],
];

$appDownloadUrl = '/get-app';
$appStoreUrl = 'https://apps.apple.com/ua/app/vizaje-nica/id1606198313';
$googlePlayUrl = 'https://play.google.com/store/apps/details?id=com.nicavizaje.project&hl=ru';
$phoneValue = !empty(CONT_PHONE1) ? CONT_PHONE1 : '';
$phoneUrl = $phoneValue !== ''
    ? 'tel:' . preg_replace('/[^\d+]/', '', $phoneValue)
    : '';
?>

<footer class="footer footer--storefront">
    <div class="footer__main main-footer">
        <div class="main-footer__container _container">
            <div class="main-footer__content">

                <!-- Приложение и контакты -->
                <div class="main-footer__column main-footer__column_brand">
                    <a
                        href="<?= htmlspecialchars($appDownloadUrl, ENT_QUOTES, 'UTF-8') ?>"
                        class="main-footer__app-card"
                    >
                        <img
                            src="/app/img/qr-code/vizaje-nica-app.png"
                            alt="<?= $isRomanian
                                ? 'Cod QR pentru aplicația Vizaje-Nica'
                                : 'QR-код приложения Vizaje-Nica' ?>"
                            width="150"
                            height="150"
                            loading="lazy"
                            decoding="async"
                        >
                        <span>
                            <?= $isRomanian
                                ? 'Scanează codul QR și descarcă aplicația'
                                : 'Наведите камеру и скачайте наше приложение' ?>
                        </span>
                    </a>

                    <?php if ($phoneValue !== '') : ?>
                        <div class="main-footer__hotline">
                            <span><?= $isRomanian ? 'Linie fierbinte' : 'Горячая линия' ?></span>
                            <a href="<?= htmlspecialchars($phoneUrl, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($phoneValue, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php
                    $this->load->view(
                        'partials/telegram-support',
                        array('class' => 'vn-telegram-support--footer')
                    );
                    ?>
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
                        <li class="main-footer__item">
                            <?php $this->load->view('partials/site-feedback'); ?>
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
                        <li class="main-footer__item">
                            <button
                                type="button"
                                class="main-footer__cookie-link"
                                data-cookie-action="settings"
                            ><?= COOKIE_SETTINGS_LINK ?></button>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <?php if (($site_theme_key ?? 'default') === 'halloween') : ?>
            <span
                class="footer__halloween-decoration footer__halloween-cat"
                aria-hidden="true"
            ></span>
        <?php endif; ?>
    </div>

    <div class="footer__bottom bottom-footer">
        <div class="bottom-footer__container _container">
            <div
                class="bottom-footer__social social-main-footer"
                aria-label="<?= $isRomanian ? 'Rețele sociale' : 'Социальные сети' ?>"
            >
                <?php foreach ($socialLinks as $social) : ?>
                    <?php if (empty($social['url'])) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <a
                        href="<?= htmlspecialchars($social['url'], ENT_QUOTES, 'UTF-8') ?>"
                        class="social-main-footer__item"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="<?= htmlspecialchars($social['title'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <img src="<?= htmlspecialchars($social['icon'], ENT_QUOTES, 'UTF-8') ?>" alt="" width="20" height="20" loading="lazy" decoding="async">
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="bottom-footer__apps" aria-label="<?= $isRomanian ? 'Aplicația mobilă' : 'Мобильное приложение' ?>">
                <a class="bottom-footer__store" href="<?= htmlspecialchars($appStoreUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                    <img src="/app/img/icons-2/apple-logo.svg" alt="" width="24" height="24" loading="lazy" decoding="async">
                    <span>
                        <small><?= $isRomanian ? 'Descarcă de pe' : 'Скачайте в' ?></small>
                        <strong>App Store</strong>
                    </span>
                </a>
                <a class="bottom-footer__store" href="<?= htmlspecialchars($googlePlayUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                    <img src="/app/img/icons-2/googleplay-icon.png" alt="" width="24" height="24" loading="lazy" decoding="async">
                    <span>
                        <small><?= $isRomanian ? 'Descarcă de pe' : 'Скачайте в' ?></small>
                        <strong>Google Play</strong>
                    </span>
                </a>
            </div>

            <div class="bottom-footer__payments" aria-label="<?= $isRomanian ? 'Metode de plată' : 'Способы оплаты' ?>">
                <span class="bottom-footer__payment bottom-footer__payment_visa">
                    <img src="/app/img/icons-2/checkout/visa.svg" alt="Visa" width="56" height="20" loading="lazy" decoding="async">
                </span>
                <span class="bottom-footer__payment bottom-footer__payment_mastercard">
                    <img src="/app/img/icons-2/checkout/mastercard.svg" alt="Mastercard" width="32" height="32" loading="lazy" decoding="async">
                </span>
                <span class="bottom-footer__payment bottom-footer__payment_google-pay">
                    <img src="/app/img/icons-2/checkout/google-pay.svg" alt="Google Pay" width="44" height="40" loading="lazy" decoding="async">
                </span>
                <span class="bottom-footer__payment bottom-footer__payment_apple-pay">
                    <img src="/app/img/icons-2/checkout/apple-pay.svg" alt="Apple Pay" width="44" height="38" loading="lazy" decoding="async">
                </span>
            </div>
        </div>
        <div class="bottom-footer__legal _container">
            <p class="bottom-footer__copy">© Vizaje-Nica, 1990—<?= date('Y') ?>. <span><?= ALL_RESERVED ?></span></p>
        </div>
    </div>
</footer>
