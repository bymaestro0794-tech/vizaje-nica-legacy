<?php
$creatorCurrentUrl = trim((string) ($without_get_url ?? $full_url ?? ''));
$creatorCurrentTitle = trim((string) ($page_name ?? $page_title ?? ''));
if ($creatorCurrentTitle === '') {
    $creatorCurrentTitle = $lclang === 'ro' ? 'Pagina curentă' : 'Текущая страница';
}
?>
<div class="creator-link-widget" data-creator-link-widget
    data-dashboard-url="/account/creator-link/dashboard"
    data-create-url="/account/creator-link/create"
    data-csrf-token="<?= htmlspecialchars((string) ($creator_csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>"
    data-locale="<?= htmlspecialchars($lclang === 'ro' ? 'ro' : 'ru', ENT_QUOTES, 'UTF-8') ?>"
    <?= empty($_SESSION['creator_widget_enabled']) ? 'hidden' : '' ?>>
    <button class="creator-link-widget__trigger" type="button" data-creator-widget-open aria-haspopup="dialog" aria-controls="creator-link-dialog">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13.5 14 9.5M8.2 15.8l-1.1 1.1a4 4 0 0 1-5.7-5.7l4-4a4 4 0 0 1 5.7 0M15.8 8.2l1.1-1.1a4 4 0 1 1 5.7 5.7l-4 4a4 4 0 0 1-5.7 0"/></svg>
        <span><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?></span>
    </button>
    <div class="creator-link-widget__backdrop" data-creator-widget-backdrop hidden></div>
    <section class="creator-link-widget__dialog" id="creator-link-dialog" role="dialog" aria-modal="true" aria-labelledby="creator-link-title" data-creator-widget-dialog hidden>
        <button class="creator-link-widget__close" type="button" data-creator-widget-close aria-label="<?= $lclang === 'ro' ? 'Închide' : 'Закрыть' ?>">×</button>
        <h2 id="creator-link-title" class="creator-link-widget__title"><?= $lclang === 'ro' ? 'Creează un link' : 'Создать ссылку' ?></h2>
        <p class="creator-link-widget__page-title"><?= htmlspecialchars($creatorCurrentTitle, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="creator-link-widget__partner-state" data-widget-partner-state role="status" aria-live="polite"><?= $lclang === 'ro' ? 'Tipul paginii este detectat automat. Limba nu schimbă atribuirea creatorului.' : 'Тип страницы определяется автоматически. Язык не меняет идентификатор креатора.' ?></p>
        <form data-creator-link-form data-create-url="/account/creator-link/create" data-csrf-token="<?= htmlspecialchars((string) ($creator_csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-locale="<?= htmlspecialchars($lclang === 'ro' ? 'ro' : 'ru', ENT_QUOTES, 'UTF-8') ?>">
            <h3 class="creator-link-widget__form-heading"><?= $lclang === 'ro' ? 'Setările linkului' : 'Настройки ссылки' ?></h3>
            <label class="creator-link-widget__label" for="creator-widget-link-title"><?= $lclang === 'ro' ? 'Denumire (opțional)' : 'Название (необязательно)' ?></label>
            <input class="creator-link-widget__url" id="creator-widget-link-title" type="text" maxlength="120" data-creator-link-title>
            <label class="creator-link-widget__label" for="creator-link-current-url"><?= $lclang === 'ro' ? 'Pagina țintă' : 'Целевая страница' ?></label>
            <input class="creator-link-widget__url" id="creator-link-current-url" type="url" readonly value="<?= htmlspecialchars($creatorCurrentUrl, ENT_QUOTES, 'UTF-8') ?>" data-creator-link-target>
            <div class="creator-link-widget__preview"><span><?= $lclang === 'ro' ? 'Pagina de destinație' : 'Страница назначения' ?></span><strong data-creator-link-preview><?= htmlspecialchars($creatorCurrentUrl, ENT_QUOTES, 'UTF-8') ?></strong></div>
            <button class="creator-link-widget__submit" type="submit" data-creator-link-submit disabled><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?></button>
            <p class="creator-link-widget__status" data-creator-create-message role="status" aria-live="polite"></p>
        </form>
        <p class="creator-link-widget__status" data-widget-links-state role="status" aria-live="polite" hidden><?= $lclang === 'ro' ? 'Linkurile nu sunt disponibile momentan.' : 'Ссылки сейчас недоступны.' ?></p>
        <label class="creator-account__setting creator-link-widget__b2c-setting">
            <span class="creator-account__setting-copy"><strong><?= $lclang === 'ro' ? 'Mod de preț B2C' : 'Режим розничных цен B2C' ?></strong><small><?= $lclang === 'ro' ? 'Afișează prețurile pentru cumpărători.' : 'Показывает цены для обычных покупателей.' ?></small></span>
            <span class="creator-account__switch"><input type="checkbox" data-creator-preference-toggle data-setting="b2c_mode" <?= !empty($_SESSION['creator_b2c_mode']) ? 'checked' : '' ?>><span aria-hidden="true"></span></span>
        </label>
        <p class="creator-link-widget__status" data-creator-preferences-status aria-live="polite"></p>
    </section>
</div>
