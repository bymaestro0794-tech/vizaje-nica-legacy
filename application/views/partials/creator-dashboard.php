<?php
$creatorStateKeys = array('not_connected', 'connection_required', 'pending_check', 'active', 'suspended', 'error');
$creatorPartnerState = isset($creator_partner_state) && in_array($creator_partner_state, $creatorStateKeys, true)
    ? $creator_partner_state
    : 'not_connected';
$creatorStateLabels = array(
    'not_connected' => $lclang === 'ro' ? 'Vizaje Partners nu este conectat.' : 'Vizaje Partners не подключён.',
    'connection_required' => $lclang === 'ro' ? 'Este necesară conectarea contului.' : 'Требуется подключить аккаунт.',
    'pending_check' => $lclang === 'ro' ? 'Conectat; statutul nu a fost verificat.' : 'Подключён, статус ещё не проверен.',
    'active' => $lclang === 'ro' ? 'Cont de creator activ.' : 'Аккаунт креатора активен.',
    'suspended' => $lclang === 'ro' ? 'Accesul creatorului este suspendat.' : 'Доступ креatorului este suspendat.',
    'error' => $lclang === 'ro' ? 'Conexiunea nu a putut fi verificată.' : 'Не удалось проверить подключение.',
);
$creatorMetricLabels = $lclang === 'ro'
    ? array('Accesări linkuri', 'Adăugări în coș', 'Comenzi', 'Comenzi confirmate', 'Vânzări, MDL', 'Conversie')
    : array('Переходы по ссылкам', 'Добавления в корзину', 'Заказы', 'Подтверждённые заказы', 'Продажи, MDL', 'Конверсия');
$creatorLinkTypeLabels = array(
    'product' => $lclang === 'ro' ? 'Produs' : 'Товар',
    'category' => $lclang === 'ro' ? 'Categorie' : 'Категория',
    'brand' => $lclang === 'ro' ? 'Brand' : 'Бренд',
    'homepage' => $lclang === 'ro' ? 'Pagina principală' : 'Главная',
);
?>
<div class="creator-account__card">
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
        ? 'Statistica și linkurile vor apărea după conectarea sigură cu Vizaje Partners.'
        : 'Статистика и ссылки появятся после безопасного подключения к Vizaje Partners.' ?></p>

    <div class="creator-account__partner-state creator-account__partner-state--<?= htmlspecialchars($creatorPartnerState, ENT_QUOTES, 'UTF-8') ?>" role="status">
        <span class="creator-account__state-dot" aria-hidden="true"></span>
        <span><?= $creatorStateLabels[$creatorPartnerState] ?></span>
    </div>

    <section class="creator-account__section" aria-labelledby="creator-stats-title" data-creator-dashboard data-creator-dashboard-state="empty">
        <div class="creator-account__section-heading">
            <h4 id="creator-stats-title"><?= $lclang === 'ro' ? 'Statistici' : 'Статистика' ?></h4>
            <span><?= $lclang === 'ro' ? 'Disponibile după conectare' : 'После подключения' ?></span>
        </div>
        <div class="creator-account__metrics">
            <?php foreach ($creatorMetricLabels as $metricLabel) { ?>
                <div class="creator-account__metric"><span><?= $metricLabel ?></span><strong>—</strong></div>
            <?php } ?>
        </div>
        <p class="creator-account__state" data-creator-dashboard-pane="loading" hidden><?= $lclang === 'ro' ? 'Se încarcă statisticile…' : 'Загружаем статистику…' ?></p>
        <p class="creator-account__state" data-creator-dashboard-pane="empty"><?= $lclang === 'ro' ? 'Nu există încă date statistice.' : 'Статистики пока нет.' ?></p>
        <p class="creator-account__state creator-account__state--error" data-creator-dashboard-pane="error" role="alert" hidden><?= $lclang === 'ro' ? 'Statisticile nu sunt disponibile momentan.' : 'Не удалось загрузить статистику. Попробуйте позже.' ?></p>
    </section>

    <section class="creator-account__section" id="creator-links" aria-labelledby="creator-links-title">
        <div class="creator-account__section-heading">
            <h4 id="creator-links-title"><?= $lclang === 'ro' ? 'Linkurile mele' : 'Мои ссылки' ?></h4>
            <a class="creator-account__text-button" href="#creator-link-form"><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?> <span aria-hidden="true">+</span></a>
        </div>
        <div class="creator-account__links-table-wrap">
            <table class="creator-account__links-table">
                <thead><tr>
                    <th><?= $lclang === 'ro' ? 'Tip' : 'Тип' ?></th>
                    <th><?= $lclang === 'ro' ? 'Denumire' : 'Название' ?></th>
                    <th><?= $lclang === 'ro' ? 'Link scurt' : 'Короткий адрес' ?></th>
                    <th><?= $lclang === 'ro' ? 'Creat' : 'Создана' ?></th>
                    <th><?= $lclang === 'ro' ? 'Statut' : 'Статус' ?></th>
                    <th><?= $lclang === 'ro' ? 'Acțiuni' : 'Действия' ?></th>
                </tr></thead>
                <tbody>
                <?php if (!empty($creator_links) && is_array($creator_links)) { foreach ($creator_links as $creatorLink) {
                    $creatorLinkUrl = trim((string) ($creatorLink['short_url'] ?? ''));
                    $creatorLinkType = (string) ($creatorLink['type'] ?? ''); ?>
                    <tr>
                        <td><?= htmlspecialchars($creatorLinkTypeLabels[$creatorLinkType] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($creatorLink['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($creatorLinkUrl, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($creatorLink['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($creatorLink['status_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="creator-account__link-actions">
                            <?php if ($creatorLinkUrl !== '') { ?>
                                <button type="button" data-creator-copy="<?= htmlspecialchars($creatorLinkUrl, ENT_QUOTES, 'UTF-8') ?>"><?= $lclang === 'ro' ? 'Copiază' : 'Копировать' ?></button>
                                <a href="<?= htmlspecialchars($creatorLinkUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= $lclang === 'ro' ? 'Deschide' : 'Открыть' ?></a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } } ?>
                </tbody>
            </table>
            <?php if (empty($creator_links)) { ?>
                <p class="creator-account__links-empty"><?= $lclang === 'ro' ? 'Nu ai creat încă linkuri.' : 'Вы ещё не создали ссылки.' ?></p>
            <?php } ?>
        </div>

        <form class="creator-account__link-create" id="creator-link-form" data-creator-link-form>
            <h5><?= $lclang === 'ro' ? 'Link nou' : 'Новая ссылка' ?></h5>
            <div class="creator-account__form-grid">
                <label><?= $lclang === 'ro' ? 'Denumire (opțional)' : 'Название (необязательно)' ?><input type="text" maxlength="120" data-creator-link-title></label>
                <label><?= $lclang === 'ro' ? 'Pagina țintă' : 'Целевая страница' ?><input type="url" value="<?= htmlspecialchars((string) ($full_url ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-creator-link-target></label>
                <label><?= $lclang === 'ro' ? 'Tipul paginii' : 'Тип страницы' ?><select data-creator-link-type>
                    <?php foreach ($creatorLinkTypeLabels as $type => $typeLabel) { ?><option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?></option><?php } ?>
                </select></label>
                <label><?= $lclang === 'ro' ? 'Limba linkului' : 'Язык ссылки' ?><select data-creator-link-locale>
                    <option value="ru" <?= $lclang === 'ru' ? 'selected' : '' ?>>RU</option>
                    <option value="ro" <?= $lclang === 'ro' ? 'selected' : '' ?>>RO</option>
                </select></label>
            </div>
            <button class="creator-account__button" type="button" disabled><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?></button>
            <p class="creator-account__settings-note"><?= $lclang === 'ro' ? 'Crearea va fi activată după configurarea autorizării de pe server.' : 'Создание включим после настройки серверной авторизации.' ?></p>
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
