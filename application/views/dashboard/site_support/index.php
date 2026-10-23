<style>
    .site-support-note {
        margin-bottom: 24px;
        padding: 14px 16px;
        border-left: 3px solid #36a3f7;
        background: #f5f9fc;
        color: #536170;
        line-height: 1.55;
    }

    .site-support-form {
        max-width: 760px;
    }

    .site-support-form .form-control {
        max-width: 620px;
    }
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
            <i class="fa fa-telegram"></i>
            <span class="caption-subject font-dark bold uppercase">Контакт поддержки</span>
        </div>
    </div>
    <div class="portlet-body">
        <div class="site-support-note">
            Один контакт используется во всех местах сайта, где показывается поддержка: профиль, каталог, контакты, футер и оформление заказа.
            Если поддержку отключить, эти ссылки не будут выводиться.
        </div>

        <?php if (!empty($migration_required)) : ?>
            <div class="alert alert-warning">
                Примените SQL-патч <code>2026-10-22-create-site-support-settings.sql</code>, чтобы сохранять настройку в базе данных.
                Пока патч не применён, сайт использует стандартный контакт.
            </div>
        <?php endif; ?>

        <?php $settings = isset($support_settings) && is_array($support_settings) ? $support_settings : array(); ?>
        <form method="post" action="/<?= ADM_CONTROLLER ?>/site_support/save" class="site-support-form">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_enabled" value="1" <?= !empty($settings['enabled']) ? 'checked' : '' ?>>
                    Показывать поддержку на сайте
                </label>
            </div>

            <div class="form-group">
                <label for="site-support-contact">Username, номер или ссылка Telegram</label>
                <input
                    id="site-support-contact"
                    type="text"
                    name="telegram_contact"
                    class="form-control"
                    value="<?= htmlspecialchars((string) ($settings['contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="@vizaje_nica_support_bot, +373... или https://t.me/..."
                    autocomplete="off"
                >
                <span class="help-block">Например: <code>@vizaje_nica_support_bot</code>. Изменение здесь обновит все места поддержки одновременно.</span>
            </div>

            <button type="submit" class="btn green">
                <i class="fa fa-check"></i> Сохранить
            </button>
        </form>
    </div>
</div>
