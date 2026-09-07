<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div
    class="cookie-panel"
    data-cookie-panel
    data-consent-functional="<?= !empty($consent['functional']) ? '1' : '0' ?>"
    data-consent-analytics="<?= !empty($consent['analytics']) ? '1' : '0' ?>"
    data-consent-marketing="<?= !empty($consent['marketing']) ? '1' : '0' ?>"
>
    <button
        type="button"
        class="cookie-panel__overlay"
        data-cookie-panel-close
        aria-label="<?= COOKIE_SETTINGS_LINK ?>"
    ></button>

    <div class="cookie-panel__panel" role="dialog" aria-modal="true" aria-labelledby="cookiePanelTitle">
        <button type="button" class="cookie-panel__close" data-cookie-panel-close>
            <span></span>
            <span></span>
        </button>

        <div class="cookie-panel__content">
            <h2 class="cookie-panel__title" id="cookiePanelTitle"><?= COOKIE_SETTINGS_LINK ?></h2>
            <p class="cookie-panel__description"><?= COOKIE_PANEL_DESCRIPTION ?></p>

            <div class="cookie-panel__categories">
                <div class="cookie-panel__category">
                    <div class="cookie-panel__category-head">
                        <span class="cookie-panel__category-name"><?= COOKIE_CAT_NECESSARY ?></span>
                        <span class="cookie-panel__always-on"><?= COOKIE_ALWAYS_ON ?></span>
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
