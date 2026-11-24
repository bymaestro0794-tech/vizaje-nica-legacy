<style>
    .site-theme-grid {
        display: flex;
        flex-wrap: wrap;
    }

    .site-theme-option {
        display: block;
        height: 100%;
        margin-bottom: 20px;
        cursor: pointer;
    }

    .site-theme-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .site-theme-card {
        display: flex;
        height: 100%;
        min-height: 160px;
        flex-direction: column;
        padding: 20px;
        border: 1px solid #d9dee3;
        border-radius: 8px;
        background: #fff;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .site-theme-option:hover .site-theme-card {
        border-color: #8a9bab;
        box-shadow: 0 8px 24px rgba(20, 31, 43, .08);
        transform: translateY(-2px);
    }

    .site-theme-option input:checked + .site-theme-card {
        border-color: #111;
        box-shadow: 0 0 0 2px #111;
    }

    .site-theme-swatch {
        display: block;
        width: 100%;
        height: 8px;
        margin-bottom: 18px;
        border-radius: 999px;
    }

    .site-theme-swatch--default {
        background:
            radial-gradient(circle at 8% 12%, rgba(232, 249, 255, 0.96) 0, rgba(232, 249, 255, 0) 34%),
            radial-gradient(circle at 35% 82%, rgba(170, 224, 250, 0.9) 0, rgba(170, 224, 250, 0) 42%),
            radial-gradient(circle at 62% 18%, rgba(139, 175, 239, 0.84) 0, rgba(139, 175, 239, 0) 44%),
            radial-gradient(circle at 92% 76%, rgba(92, 205, 239, 0.84) 0, rgba(92, 205, 239, 0) 42%),
            linear-gradient(116deg, #e0f6ff 0%, #a6d9f3 32%, #8daee8 66%, #84d9f2 100%);
    }
    .site-theme-swatch--halloween { background: linear-gradient(90deg, #f97316 0 50%, #21130d 50%); }
    .site-theme-swatch--black_friday { background: linear-gradient(90deg, #000 0 50%, #FFE600 50%); }
    .site-theme-swatch--red_friday { background: linear-gradient(90deg, #000 0 50%, #D7193F 50%); }
    .site-theme-swatch--easter { background: linear-gradient(90deg, #a78bfa 0 50%, #78a878 50%); }
    .site-theme-swatch--valentine { background: linear-gradient(90deg, #e85d75 0 50%, #8e2338 50%); }
    .site-theme-swatch--new_year {
        background:
            radial-gradient(circle at 9% 8%, rgba(216, 239, 238, 0.72) 0, rgba(216, 239, 238, 0) 32%),
            radial-gradient(circle at 57% 18%, rgba(108, 132, 183, 0.7) 0, rgba(108, 132, 183, 0) 44%),
            radial-gradient(circle at 100% 92%, rgba(18, 24, 57, 0.84) 0, rgba(18, 24, 57, 0) 54%),
            linear-gradient(118deg, #a8c6c9 0%, #536b9b 48%, #20284e 100%);
    }

    .site-theme-card__name {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        color: #263443;
        font-size: 17px;
        font-weight: 600;
    }

    .site-theme-card__check {
        color: #26a269;
        opacity: 0;
    }

    .site-theme-option input:checked + .site-theme-card .site-theme-card__check {
        opacity: 1;
    }

    .site-theme-card__description,
    .site-theme-card__preview {
        color: #6b7785;
        font-size: 13px;
        line-height: 1.5;
    }

    .site-theme-card__preview {
        margin-top: auto;
        padding-top: 12px;
        font-weight: 600;
    }

    .site-theme-note {
        margin-bottom: 24px;
        padding: 14px 16px;
        border-left: 3px solid #36a3f7;
        background: #f5f9fc;
        color: #536170;
        line-height: 1.55;
    }

    .black-friday-settings[hidden] { display: none !important; }
    .black-friday-settings {
        margin: 4px 0 24px;
        padding: 18px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fffdf0;
    }
    .black-friday-settings h3 { margin: 0 0 14px; color: #202020; font-size: 16px; font-weight: 600; }
    .black-friday-settings .form-group { margin-bottom: 14px; }
    .black-friday-settings .help-block { margin: 8px 0 0; }
</style>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="/<?= ADM_CONTROLLER ?>/menu/"><?= lang('Home') ?></a>
            <i class="fa fa-circle"></i>
        </li>
        <li><span><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span></li>
    </ul>
</div>

<h1 class="page-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>

<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-block alert-success fade in">
        <button type="button" class="close" data-dismiss="alert"></button>
        <?= htmlspecialchars((string) $_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])) : ?>
    <div class="alert alert-block alert-danger fade in">
        <button type="button" class="close" data-dismiss="alert"></button>
        <?php foreach ((array) $_SESSION['error'] as $error) : ?>
            <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?><br>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-magic"></i>
            <span class="caption-subject font-dark bold uppercase">Сезонные темы</span>
        </div>
    </div>
    <div class="portlet-body">
        <div class="site-theme-note">
            Здесь выбирается только визуальная атмосфера. Структура страниц, каталог, цены и контент не меняются:
            тема влияет на цвета и оформление. Для Black Friday можно отдельно задать период и текст объявления с таймером.
        </div>

        <?php if (!empty($migration_required)) : ?>
            <div class="alert alert-warning">
                Примените SQL-патч <code>2026-10-21-create-site-theme-settings.sql</code>, чтобы сохранять выбор в базе данных.
                До этого сайт продолжает работать в обычной теме.
            </div>
        <?php endif; ?>

        <?php
        $themeSettings = isset($theme_settings) && is_array($theme_settings) ? $theme_settings : array();
        $formatThemeDateTime = static function ($value) {
            $value = trim((string) $value);
            return $value === '' ? '' : str_replace(' ', 'T', substr($value, 0, 16));
        };
        $blackFridaySettingsInstalled = !empty($black_friday_settings_installed);
        ?>
        <form method="post" action="/<?= ADM_CONTROLLER ?>/site_themes/save" data-site-themes-form data-black-friday-settings-installed="<?= $blackFridaySettingsInstalled ? '1' : '0' ?>">
            <div class="row site-theme-grid">
                <?php foreach ($theme_presets as $themeKey => $themePreset) : ?>
                    <div class="col-lg-4 col-md-6">
                        <label class="site-theme-option">
                            <input
                                type="radio"
                                name="theme_key"
                                value="<?= htmlspecialchars($themeKey, ENT_QUOTES, 'UTF-8') ?>"
                                data-site-theme-choice
                                <?= $active_theme_key === $themeKey ? 'checked' : '' ?>
                            >
                            <span class="site-theme-card">
                                <span class="site-theme-swatch site-theme-swatch--<?= htmlspecialchars($themeKey, ENT_QUOTES, 'UTF-8') ?>"></span>
                                <span class="site-theme-card__name">
                                    <?= htmlspecialchars($themePreset['name'], ENT_QUOTES, 'UTF-8') ?>
                                    <i class="fa fa-check site-theme-card__check" aria-hidden="true"></i>
                                </span>
                                <span class="site-theme-card__description">
                                    <?= htmlspecialchars($themePreset['description'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="site-theme-card__preview">
                                    <?= htmlspecialchars($themePreset['preview'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <section
                class="black-friday-settings"
                data-black-friday-settings
                <?= in_array($active_theme_key, array('black_friday', 'red_friday'), true) ? '' : 'hidden' ?>
            >
                <h3 data-campaign-settings-title><?= $active_theme_key === 'red_friday' ? 'Объявление и таймер Red Friday' : 'Объявление и таймер Black Friday' ?></h3>
                <?php if (!$blackFridaySettingsInstalled) : ?>
                    <div class="alert alert-warning">
                        Для сохранения этих настроек сначала примените SQL-патч
                        <code>database/patches/2026-11-16-black-friday-theme-settings.sql</code>.
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="black-friday-start">Начало акции</label>
                            <input
                                id="black-friday-start"
                                class="form-control"
                                type="datetime-local"
                                name="black_friday_starts_at"
                                value="<?= htmlspecialchars($formatThemeDateTime($themeSettings['black_friday_starts_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-black-friday-required
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="black-friday-end">Окончание акции</label>
                            <input
                                id="black-friday-end"
                                class="form-control"
                                type="datetime-local"
                                name="black_friday_ends_at"
                                value="<?= htmlspecialchars($formatThemeDateTime($themeSettings['black_friday_ends_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-black-friday-required
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="black-friday-text-ru">Текст RU</label>
                            <input
                                id="black-friday-text-ru"
                                class="form-control"
                                type="text"
                                name="black_friday_text_ru"
                                maxlength="255"
                                value="<?= htmlspecialchars((string) ($themeSettings['black_friday_text_ru'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-black-friday-required
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="black-friday-text-ro">Текст RO</label>
                            <input
                                id="black-friday-text-ro"
                                class="form-control"
                                type="text"
                                name="black_friday_text_ro"
                                maxlength="255"
                                value="<?= htmlspecialchars((string) ($themeSettings['black_friday_text_ro'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-black-friday-required
                            >
                        </div>
                    </div>
                </div>
                <p class="help-block">
                    Таймер показывается в верхней плашке и в корзине только в заданный период. Время указывается по часовому поясу Europe/Bucharest.
                    Эта тема меняет оформление, но сама по себе не создаёт скидки и не меняет цены.
                </p>
            </section>

            <button type="submit" class="btn green">
                <i class="fa fa-check"></i> Сохранить тему
            </button>
        </form>
    </div>
</div>

<script>
(() => {
    const form = document.querySelector('[data-site-themes-form]')
    if (!form) return

    const settings = form.querySelector('[data-black-friday-settings]')
    const choices = Array.from(form.querySelectorAll('[data-site-theme-choice]'))
    const fields = Array.from(form.querySelectorAll('[data-black-friday-required]'))
    const hasBlackFridaySettings = form.dataset.blackFridaySettingsInstalled === '1'

    const updateBlackFridayFields = () => {
        const selected = choices.find(choice => choice.checked)
        const isCampaignTheme = ['black_friday', 'red_friday'].includes(selected?.value)
        settings.hidden = !isCampaignTheme
        settings.querySelector('[data-campaign-settings-title]').textContent = selected?.value === 'red_friday'
            ? 'Объявление и таймер Red Friday'
            : 'Объявление и таймер Black Friday'
        fields.forEach(field => {
            field.required = Boolean(isCampaignTheme && hasBlackFridaySettings)
        })
    }

    choices.forEach(choice => choice.addEventListener('change', updateBlackFridayFields))
    updateBlackFridayFields()
})()
</script>
