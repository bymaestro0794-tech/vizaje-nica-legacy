<?php
$creatorMetricLabels = $lclang === 'ro'
    ? array(
        'clicks' => 'Accesări linkuri', 'add_to_cart' => 'Adăugări în coș', 'orders' => 'Comenzi',
        'confirmed_orders' => 'Comenzi confirmate', 'sales_mdl' => 'Vânzări, MDL', 'conversion' => 'Conversie',
    )
    : array(
        'clicks' => 'Переходы по ссылкам', 'add_to_cart' => 'Добавления в корзину', 'orders' => 'Заказы',
        'confirmed_orders' => 'Подтверждённые заказы', 'sales_mdl' => 'Продажи, MDL', 'conversion' => 'Конверсия',
    );
$creatorLinkTypeLabels = array(
    'product' => $lclang === 'ro' ? 'Produs' : 'Товар',
    'category' => $lclang === 'ro' ? 'Categorie' : 'Категория',
    'brand' => $lclang === 'ro' ? 'Brand' : 'Бренд',
    'homepage' => $lclang === 'ro' ? 'Pagina principală' : 'Главная',
);
$creatorCurrentUrl = trim((string) ($without_get_url ?? $full_url ?? ''));
?>
<div class="creator-account__card" data-creator-dashboard-root
    data-dashboard-url="/account/creator-link/dashboard"
    data-start-url="/account/creator-link/start"
    data-create-url="/account/creator-link/create"
    data-unlink-url="/account/creator-link/unlink"
    data-revoke-prefix="/account/creator-link/revoke/"
    data-csrf-token="<?= htmlspecialchars((string) ($creator_csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>"
    data-locale="<?= htmlspecialchars($lclang === 'ro' ? 'ro' : 'ru', ENT_QUOTES, 'UTF-8') ?>">
    <header class="creator-account__header">
        <div>
            <span class="creator-account__eyebrow">Vizaje Partners</span>
            <h3 class="creator-account__title"><?= $lclang === 'ro' ? 'Panoul creatorului' : 'Кабинет креатора' ?></h3>
        </div>
        <a class="creator-account__partners-link" href="https://partners.vizaje-nica.com" target="_blank" rel="noopener noreferrer">
            <?= $lclang === 'ro' ? 'Deschide Partners' : 'Перейти в Partners' ?> <span aria-hidden="true">↗</span>
        </a>
    </header>
    <p class="creator-account__description"><?= $lclang === 'ro'
        ? 'Conectează contul Vizaje Partners pentru a vedea statistica și a administra linkurile.'
        : 'Подключите аккаунт Vizaje Partners, чтобы смотреть статистику и управлять ссылками.' ?></p>
    <?php if (!empty($creator_partner_notice)) { ?>
        <?php
        $creatorNoticeMessages = array(
            'connected' => $lclang === 'ro' ? 'Contul Vizaje Partners a fost conectat.' : 'Аккаунт Vizaje Partners подключён.',
            'inactive' => $lclang === 'ro' ? 'Contul Partners nu este activ. Verifică starea în Partners.' : 'Аккаунт Partners не активен. Проверьте его статус в Partners.',
            'unavailable' => $lclang === 'ro' ? 'Conectarea nu este disponibilă momentan.' : 'Подключение сейчас недоступно.',
            'exchange' => $lclang === 'ro' ? 'Nu am putut confirma conectarea. Încearcă din nou.' : 'Не удалось подтвердить подключение. Попробуйте ещё раз.',
            'storage' => $lclang === 'ro' ? 'Conectarea a reușit, dar nu a putut fi salvată local.' : 'Partners подтвердил связь, но её не удалось сохранить на сайте.',
            'callback_invalid' => $lclang === 'ro' ? 'Conectarea a expirat sau nu a putut fi verificată.' : 'Подключение истекло или не прошло проверку.',
        );
        $creatorNotice = $creatorNoticeMessages[$creator_partner_notice] ?? '';
        ?>
        <?php if ($creatorNotice !== '') { ?><p class="creator-account__state" role="status"><?= htmlspecialchars($creatorNotice, ENT_QUOTES, 'UTF-8') ?></p><?php } ?>
    <?php } ?>

    <div class="creator-account__partner-state" data-creator-partner-state="checking" role="status" aria-live="polite">
        <span class="creator-account__state-dot" aria-hidden="true"></span>
        <span data-creator-partner-state-text><?= $lclang === 'ro' ? 'Se verifică legătura…' : 'Проверяем подключение…' ?></span>
    </div>
    <div class="creator-account__connection-actions">
        <button type="button" class="creator-account__button" data-creator-link-start data-start-url="/account/creator-link/start" disabled>
            <?= $lclang === 'ro' ? 'Conectează Vizaje Partners' : 'Подключить Vizaje Partners' ?>
        </button>
        <button type="button" class="creator-account__text-button" data-creator-unlink data-unlink-url="/account/creator-link/unlink" hidden>
            <?= $lclang === 'ro' ? 'Deconectează contul' : 'Отвязать аккаунт' ?>
        </button>
        <span class="creator-account__state creator-account__state--error" data-creator-action-message role="status" aria-live="polite"></span>
    </div>

    <section class="creator-account__section" aria-labelledby="creator-stats-title" data-creator-dashboard data-creator-dashboard-state="loading">
        <div class="creator-account__section-heading">
            <h4 id="creator-stats-title"><?= $lclang === 'ro' ? 'Statistici' : 'Статистика' ?></h4>
            <span data-creator-id></span>
        </div>
        <div class="creator-account__metrics" data-creator-metrics hidden>
            <?php foreach ($creatorMetricLabels as $key => $label) { ?>
                <div class="creator-account__metric"><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span><strong data-creator-metric="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">—</strong></div>
            <?php } ?>
        </div>
        <p class="creator-account__state" data-creator-dashboard-pane="loading"><?= $lclang === 'ro' ? 'Se încarcă datele…' : 'Загружаем данные…' ?></p>
        <p class="creator-account__state" data-creator-dashboard-pane="empty" hidden><?= $lclang === 'ro' ? 'Nu există încă date statistice.' : 'Статистики пока нет.' ?></p>
        <p class="creator-account__state creator-account__state--error" data-creator-dashboard-pane="error" role="alert" hidden><?= $lclang === 'ro' ? 'Statistica nu este disponibilă momentan.' : 'Статистика сейчас недоступна.' ?></p>
    </section>

    <section class="creator-account__section" id="creator-links" aria-labelledby="creator-links-title">
        <div class="creator-account__section-heading">
            <h4 id="creator-links-title"><?= $lclang === 'ro' ? 'Linkurile mele' : 'Мои ссылки' ?></h4>
            <a class="creator-account__text-button" href="#creator-link-form"><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?> <span aria-hidden="true">+</span></a>
        </div>
        <p class="creator-account__state" data-creator-links-pane="loading"><?= $lclang === 'ro' ? 'Se încarcă linkurile…' : 'Загружаем ссылки…' ?></p>
        <p class="creator-account__state" data-creator-links-pane="empty" hidden><?= $lclang === 'ro' ? 'Nu ai creat încă linkuri.' : 'Вы ещё не создали ссылки.' ?></p>
        <p class="creator-account__state creator-account__state--error" data-creator-links-pane="error" role="alert" hidden><?= $lclang === 'ro' ? 'Linkurile nu sunt disponibile momentan.' : 'Ссылки сейчас недоступны.' ?></p>
        <div class="creator-account__links-table-wrap" data-creator-links-table hidden>
            <table class="creator-account__links-table">
                <thead><tr>
                    <th><?= $lclang === 'ro' ? 'Tip' : 'Тип' ?></th>
                    <th><?= $lclang === 'ro' ? 'Denumire' : 'Название' ?></th>
                    <th><?= $lclang === 'ro' ? 'Link scurt' : 'Короткий адрес' ?></th>
                    <th><?= $lclang === 'ro' ? 'Creat' : 'Создана' ?></th>
                    <th><?= $lclang === 'ro' ? 'Statut' : 'Статус' ?></th>
                    <th><?= $lclang === 'ro' ? 'Acțiuni' : 'Действия' ?></th>
                </tr></thead>
                <tbody data-creator-links></tbody>
            </table>
        </div>

        <form class="creator-account__link-create" id="creator-link-form" data-creator-link-form data-create-url="/account/creator-link/create" data-csrf-token="<?= htmlspecialchars((string) ($creator_csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-locale="<?= htmlspecialchars($lclang === 'ro' ? 'ro' : 'ru', ENT_QUOTES, 'UTF-8') ?>">
            <h5><?= $lclang === 'ro' ? 'Link nou' : 'Новая ссылка' ?></h5>
            <div class="creator-account__form-grid">
                <label><?= $lclang === 'ro' ? 'Denumire (opțional)' : 'Название (необязательно)' ?><input type="text" maxlength="120" data-creator-link-title></label>
                <label><?= $lclang === 'ro' ? 'Pagina țintă' : 'Целевая страница' ?><input type="url" value="<?= htmlspecialchars($creatorCurrentUrl, ENT_QUOTES, 'UTF-8') ?>" required data-creator-link-target></label>
                <label><?= $lclang === 'ro' ? 'Tipul paginii' : 'Тип страницы' ?><select required data-creator-link-type>
                    <?php foreach ($creatorLinkTypeLabels as $type => $typeLabel) { ?><option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?></option><?php } ?>
                </select></label>
                <label><?= $lclang === 'ro' ? 'Limba linkului' : 'Язык ссылки' ?><select data-creator-link-locale>
                    <option value="ru" <?= $lclang === 'ru' ? 'selected' : '' ?>>RU</option>
                    <option value="ro" <?= $lclang === 'ro' ? 'selected' : '' ?>>RO</option>
                </select></label>
            </div>
            <button class="creator-account__button" type="submit" data-creator-link-submit disabled><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?></button>
            <p class="creator-account__state" data-creator-create-message role="status" aria-live="polite"></p>
        </form>
    </section>

    <section class="creator-account__section" aria-labelledby="creator-settings-title">
        <div class="creator-account__section-heading"><h4 id="creator-settings-title"><?= $lclang === 'ro' ? 'Preferințe' : 'Настройки' ?></h4></div>
        <div class="creator-account__settings" data-creator-preferences data-endpoint="/creator/preferences" data-persistent="<?= !empty($client_info->creator_preferences_supported) ? '1' : '0' ?>">
            <label class="creator-account__setting">
                <span class="creator-account__setting-copy"><strong><?= $lclang === 'ro' ? 'Afișează widgetul plutitor pentru linkuri' : 'Показывать плавающий виджет ссылки' ?></strong><small><?= $lclang === 'ro' ? 'Butonul apare pe paginile site-ului.' : 'Кнопка виджета будет видна на страницах сайта.' ?></small></span>
                <span class="creator-account__switch"><input type="checkbox" data-creator-preference-toggle data-setting="widget_enabled" <?= !empty($_SESSION['creator_widget_enabled']) ? 'checked' : '' ?>><span aria-hidden="true"></span></span>
            </label>
            <label class="creator-account__setting">
                <span class="creator-account__setting-copy"><strong><?= $lclang === 'ro' ? 'Modul de preț B2C' : 'Режим розничных цен B2C' ?></strong><small><?= $lclang === 'ro' ? 'Afișează prețurile pentru cumpărători.' : 'Показывает цены для обычных покупателей.' ?></small></span>
                <span class="creator-account__switch"><input type="checkbox" data-creator-preference-toggle data-setting="b2c_mode" aria-label="<?= $lclang === 'ro' ? 'Mod de preț B2C' : 'Режим розничных цен B2C' ?>" <?= !empty($_SESSION['creator_b2c_mode']) ? 'checked' : '' ?>><span aria-hidden="true"></span></span>
            </label>
            <p class="creator-account__settings-status" data-creator-preferences-status aria-live="polite"></p>
        </div>
        <?php if (empty($client_info->creator_preferences_supported)) { ?>
            <p class="creator-account__settings-note"><?= $lclang === 'ro'
                ? 'Setările se păstrează până la ieșirea din cont. După actualizarea bazei de date, vor fi salvate în profil.'
                : 'Пока настройки действуют до выхода из аккаунта. После обновления базы они будут сохраняться в профиле.' ?></p>
        <?php } ?>
    </section>
</div>
