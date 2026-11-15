<?php
$creatorCurrentUrl = trim((string) ($without_get_url ?? $full_url ?? ''));
$creatorCurrentTitle = trim((string) ($page_name ?? $page_title ?? ''));
$creatorAccountPath = !empty($menu['all'][15]->uri) ? '/' . $lclang . '/' . $menu['all'][15]->uri : '/' . $lclang . '/account';
$creatorWidgetStates = array('not_connected', 'connection_required', 'pending_check', 'active', 'suspended', 'error');
$creatorWidgetState = isset($creator_partner_state) && in_array($creator_partner_state, $creatorWidgetStates, true)
    ? $creator_partner_state
    : 'pending_check';
$creatorWidgetStateLabels = array(
    'not_connected' => $lclang === 'ro' ? 'Vizaje Partners nu este conectat.' : 'Vizaje Partners не подключён.',
    'connection_required' => $lclang === 'ro' ? 'Este necesară conectarea contului.' : 'Требуется подключить аккаунт.',
    'pending_check' => $lclang === 'ro' ? 'Conectat; statutul nu a fost verificat.' : 'Подключён, статус ещё не проверен.',
    'active' => $lclang === 'ro' ? 'Cont de creator activ.' : 'Аккаунт креатора активен.',
    'suspended' => $lclang === 'ro' ? 'Accesul creatorului este suspendat.' : 'Доступ креатора приостановлен.',
    'error' => $lclang === 'ro' ? 'Conexiunea nu a putut fi verificată.' : 'Не удалось проверить подключение.',
);
if ($creatorCurrentTitle === '') {
    $creatorCurrentTitle = $lclang === 'ro' ? 'Pagina curentă' : 'Текущая страница';
}
?>
<div class="creator-link-widget" data-creator-link-widget
    data-dashboard-url="/account/creator-link/dashboard"
    data-start-url="/account/creator-link/start"
    data-create-url="/account/creator-link/create"
    data-unlink-url="/account/creator-link/unlink"
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
        <span class="creator-link-widget__eyebrow">Vizaje Partners</span>
        <h2 id="creator-link-title" class="creator-link-widget__title"><?= $lclang === 'ro' ? 'Creează un link' : 'Создать ссылку' ?></h2>
        <p class="creator-link-widget__page-title"><?= htmlspecialchars($creatorCurrentTitle, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="creator-link-widget__partner-state" data-widget-partner-state role="status" aria-live="polite"><?= $creatorWidgetStateLabels[$creatorWidgetState] ?></p>
        <button class="creator-link-widget__submit" type="button" data-creator-link-start data-start-url="/account/creator-link/start" disabled><?= $lclang === 'ro' ? 'Conectează Vizaje Partners' : 'Подключить Vizaje Partners' ?></button>
        <form data-creator-link-form data-create-url="/account/creator-link/create" data-csrf-token="<?= htmlspecialchars((string) ($creator_csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-locale="<?= htmlspecialchars($lclang === 'ro' ? 'ro' : 'ru', ENT_QUOTES, 'UTF-8') ?>">
            <label class="creator-link-widget__label" for="creator-widget-link-title"><?= $lclang === 'ro' ? 'Denumire (opțional)' : 'Название (необязательно)' ?></label>
            <input class="creator-link-widget__url" id="creator-widget-link-title" type="text" maxlength="120" data-creator-link-title>
            <label class="creator-link-widget__label" for="creator-link-current-url"><?= $lclang === 'ro' ? 'Pagina țintă' : 'Целевая страница' ?></label>
            <input class="creator-link-widget__url" id="creator-link-current-url" type="url" readonly value="<?= htmlspecialchars($creatorCurrentUrl, ENT_QUOTES, 'UTF-8') ?>" data-creator-link-target>
            <div class="creator-link-widget__form-row">
                <label class="creator-link-widget__label"><?= $lclang === 'ro' ? 'Tip' : 'Тип' ?><select data-creator-link-type><option value="product"><?= $lclang === 'ro' ? 'Produs' : 'Товар' ?></option><option value="category"><?= $lclang === 'ro' ? 'Categorie' : 'Категория' ?></option><option value="brand"><?= $lclang === 'ro' ? 'Brand' : 'Бренд' ?></option><option value="homepage"><?= $lclang === 'ro' ? 'Pagina principală' : 'Главная' ?></option></select></label>
                <label class="creator-link-widget__label"><?= $lclang === 'ro' ? 'Limba' : 'Язык' ?><select data-creator-link-locale><option value="ru" <?= $lclang === 'ru' ? 'selected' : '' ?>>RU</option><option value="ro" <?= $lclang === 'ro' ? 'selected' : '' ?>>RO</option></select></label>
            </div>
            <button class="creator-link-widget__submit" type="submit" data-creator-link-submit disabled><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?></button>
            <p class="creator-link-widget__status" data-creator-create-message role="status" aria-live="polite"></p>
        </form>
        <p class="creator-link-widget__status" data-widget-links-state role="status" aria-live="polite" hidden><?= $lclang === 'ro' ? 'Se încarcă linkurile…' : 'Загружаем ссылки…' ?></p>
        <div class="creator-link-widget__link-list" data-widget-links hidden></div>
        <div class="creator-link-widget__actions">
            <a href="<?= htmlspecialchars($creatorAccountPath, ENT_QUOTES, 'UTF-8') ?>?creator#creator-links"><?= $lclang === 'ro' ? 'Linkurile mele' : 'Мои ссылки' ?></a>
            <a href="https://partners.vizaje-nica.com" target="_blank" rel="noopener noreferrer"><?= $lclang === 'ro' ? 'Deschide Partners' : 'Перейти в Partners' ?> ↗</a>
        </div>
        <label class="creator-account__setting creator-link-widget__b2c-setting">
            <span class="creator-account__setting-copy"><strong><?= $lclang === 'ro' ? 'Mod de preț B2C' : 'Режим розничных цен B2C' ?></strong><small><?= $lclang === 'ro' ? 'Afișează prețurile pentru cumpărători.' : 'Показывает цены для обычных покупателей.' ?></small></span>
            <span class="creator-account__switch"><input type="checkbox" data-creator-preference-toggle data-setting="b2c_mode" <?= !empty($_SESSION['creator_b2c_mode']) ? 'checked' : '' ?>><span aria-hidden="true"></span></span>
        </label>
        <p class="creator-link-widget__status" data-creator-preferences-status aria-live="polite"></p>
    </section>
</div>
