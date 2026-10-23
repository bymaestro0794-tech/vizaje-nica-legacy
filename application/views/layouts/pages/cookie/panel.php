<?php
defined('BASEPATH') or exit('No direct script access allowed');

$cookiePrivacyUri = !empty($menu['all'][23]->uri)
    ? trim((string) $menu['all'][23]->uri, '/')
    : ($lclang === 'ro'
        ? 'politica-de-confidentialitate-si-cookie-uri'
        : 'politika-konfidentsialnosti-i-cookies');
$cookiePrivacyUrl = '/' . $lclang . '/' . $cookiePrivacyUri;
?>

<div
    class="cookie-panel"
    data-cookie-panel
    data-consent-functional="<?= !empty($consent['functional']) ? '1' : '0' ?>"
    data-consent-analytics="<?= !empty($consent['analytics']) ? '1' : '0' ?>"
    data-consent-marketing="<?= !empty($consent['marketing']) ? '1' : '0' ?>"
>
    <div class="cookie-panel__panel" role="region" aria-labelledby="cookiePanelTitle">
        <button type="button" class="cookie-panel__close" data-cookie-panel-close aria-label="<?= COOKIE_SETTINGS_LINK ?>">
            <span></span>
            <span></span>
        </button>

        <div class="cookie-panel__content">
            <div class="cookie-panel__header">
                <h2 class="cookie-panel__title" id="cookiePanelTitle"><?= COOKIE_SETTINGS_LINK ?></h2>
                <a class="cookie-panel__about" href="<?= htmlspecialchars($cookiePrivacyUrl, ENT_QUOTES, 'UTF-8') ?>">
                    <?= $lclang === 'ro' ? 'Despre cookie-uri' : 'О cookies' ?>
                </a>
            </div>
            <p class="cookie-panel__description"><?= COOKIE_PANEL_DESCRIPTION ?></p>

            <div class="cookie-panel__categories">
                <div class="cookie-panel__category">
                    <div class="cookie-panel__category-head">
                        <span class="cookie-panel__category-name"><?= COOKIE_CAT_NECESSARY ?></span>
                        <span class="cookie-panel__always-on" aria-label="<?= COOKIE_ALWAYS_ON ?>"><span></span></span>
                    </div>
                    <p class="cookie-panel__category-desc"><?= COOKIE_CAT_NECESSARY_DESC ?></p>
                </div>

                <div class="cookie-panel__category">
                    <div class="cookie-panel__category-head">
                        <span class="cookie-panel__category-name"><?= COOKIE_CAT_FUNCTIONAL ?></span>
                        <label class="cookie-panel__toggle">
                            <input type="checkbox" data-cookie-toggle="functional">
                            <span class="cookie-panel__toggle-track"></span>
                        </label>
                    </div>
                    <p class="cookie-panel__category-desc"><?= COOKIE_CAT_FUNCTIONAL_DESC ?></p>
                </div>

                <div class="cookie-panel__category">
                    <div class="cookie-panel__category-head">
                        <span class="cookie-panel__category-name"><?= COOKIE_CAT_ANALYTICS ?></span>
                        <label class="cookie-panel__toggle">
                            <input type="checkbox" data-cookie-toggle="analytics">
                            <span class="cookie-panel__toggle-track"></span>
                        </label>
                    </div>
                    <p class="cookie-panel__category-desc"><?= COOKIE_CAT_ANALYTICS_DESC ?></p>
                </div>

                <div class="cookie-panel__category">
                    <div class="cookie-panel__category-head">
                        <span class="cookie-panel__category-name"><?= COOKIE_CAT_MARKETING ?></span>
                        <label class="cookie-panel__toggle">
                            <input type="checkbox" data-cookie-toggle="marketing">
                            <span class="cookie-panel__toggle-track"></span>
                        </label>
                    </div>
                    <p class="cookie-panel__category-desc"><?= COOKIE_CAT_MARKETING_DESC ?></p>
                </div>
            </div>

            <div class="cookie-panel__actions">
                <button type="button" class="cookie-panel__button cookie-panel__button--secondary" data-cookie-action="reject">
                    <?= COOKIE_REJECT ?>
                </button>
                <button type="button" class="cookie-panel__button cookie-panel__button--primary" data-cookie-action="save">
                    <?= COOKIE_SAVE ?>
                </button>
            </div>
        </div>
    </div>
</div>
