<?php
$creatorCurrentUrl = trim((string) ($without_get_url ?? $full_url ?? ''));
?>
<div class="creator-account__card" data-creator-dashboard-root
    data-dashboard-url="/account/creator-link/dashboard"
    data-create-url="/account/creator-link/create"
    data-revoke-prefix="/account/creator-link/revoke/"
    data-csrf-token="<?= htmlspecialchars((string) ($creator_csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>"
    data-locale="<?= htmlspecialchars($lclang === 'ro' ? 'ro' : 'ru', ENT_QUOTES, 'UTF-8') ?>">
    <header class="creator-account__header">
        <div>
            <h3 class="creator-account__title"><?= $lclang === 'ro' ? 'Panoul creatorului' : 'Кабинет креатора' ?></h3>
        </div>
    </header>
    <p class="creator-account__description"><?= $lclang === 'ro'
        ? 'Creează și administrează linkurile de recomandare direct aici.'
        : 'Создавайте и управляйте реферальными ссылками прямо здесь.' ?></p>
    <section class="creator-account__section" id="creator-links" aria-labelledby="creator-links-title">
        <div class="creator-account__section-heading">
            <h4 id="creator-links-title"><?= $lclang === 'ro' ? 'Linkurile mele' : 'Мои ссылки' ?></h4>
            <a class="creator-account__text-button" href="#creator-link-form"><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?> <span aria-hidden="true">+</span></a>
        </div>
        <p class="creator-account__state" data-creator-links-pane="loading"><?= $lclang === 'ro' ? 'Se încarcă linkurile…' : 'Загружаем ссылки…' ?></p>
        <p class="creator-account__state" data-creator-links-pane="empty" hidden><?= $lclang === 'ro' ? 'Nu ai creat încă linkuri.' : 'Вы ещё не создали ссылки.' ?></p>
        <p class="creator-account__state creator-account__state--error" data-creator-links-pane="error" role="alert" hidden><?= $lclang === 'ro' ? 'Linkurile nu sunt disponibile momentan.' : 'Ссылки сейчас недоступны.' ?></p>
        <div class="creator-account__links-list" data-creator-links-table hidden data-creator-links></div>

        <form class="creator-account__link-create" id="creator-link-form" data-creator-link-form data-create-url="/account/creator-link/create" data-csrf-token="<?= htmlspecialchars((string) ($creator_csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-locale="<?= htmlspecialchars($lclang === 'ro' ? 'ro' : 'ru', ENT_QUOTES, 'UTF-8') ?>">
            <h5><?= $lclang === 'ro' ? 'Link nou' : 'Новая ссылка' ?></h5>
            <p class="creator-account__state"><?= $lclang === 'ro' ? 'Tipul paginii este detectat automat. Limba paginii nu schimbă atribuirea creatorului.' : 'Тип страницы определяется по адресу автоматически. Язык страницы не меняет идентификатор креатора.' ?></p>
            <div class="creator-account__form-grid">
                <label><?= $lclang === 'ro' ? 'Denumire (opțional)' : 'Название (необязательно)' ?><input type="text" maxlength="120" data-creator-link-title></label>
                <label><?= $lclang === 'ro' ? 'Pagina țintă' : 'Целевая страница' ?><input type="url" value="<?= htmlspecialchars($creatorCurrentUrl, ENT_QUOTES, 'UTF-8') ?>" required data-creator-link-target></label>
            </div>
            <div class="creator-link-widget__preview"><span><?= $lclang === 'ro' ? 'Pagina de destinație' : 'Страница назначения' ?></span><strong data-creator-link-preview><?= htmlspecialchars($creatorCurrentUrl, ENT_QUOTES, 'UTF-8') ?></strong></div>
            <button class="creator-account__button" type="submit" data-creator-link-submit disabled><?= $lclang === 'ro' ? 'Creează link' : 'Создать ссылку' ?></button>
            <p class="creator-account__state" data-creator-create-message role="status" aria-live="polite"></p>
        </form>
    </section>

    <section class="creator-account__section" id="creator-orders" aria-labelledby="creator-orders-title">
        <div class="creator-account__section-heading"><h4 id="creator-orders-title"><?= $lclang === 'ro' ? 'Comenzi recomandate' : 'Заказы по ссылкам' ?></h4></div>
        <p class="creator-account__state"><?= $lclang === 'ro' ? 'Aici apar doar comenzile cu statutul „finished”. Comenzile create, neplătite sau anulate nu sunt incluse.' : 'Здесь отображаются только заказы со статусом «finished». Созданные, неоплаченные и отменённые заказы не учитываются.' ?></p>
        <?php if (empty($creator_referral_orders_available)) { ?>
            <p class="creator-account__state"><?= $lclang === 'ro' ? 'Istoricul comenzilor recomandate va fi disponibil după aplicarea patch-ului SQL.' : 'История заказов станет доступна после применения SQL patch.' ?></p>
        <?php } elseif (empty($creator_referral_orders)) { ?>
            <p class="creator-account__state"><?= $lclang === 'ro' ? 'Nu există încă comenzi recomandate finalizate.' : 'Завершённых заказов по вашим ссылкам пока нет.' ?></p>
        <?php } else { ?>
            <div class="creator-account__links-table-wrap"><table class="creator-account__links-table"><thead><tr><th><?= $lclang === 'ro' ? 'Comandă' : 'Заказ' ?></th><th><?= $lclang === 'ro' ? 'Data finalizării' : 'Дата завершения' ?></th><th><?= $lclang === 'ro' ? 'Suma' : 'Сумма' ?></th><th><?= $lclang === 'ro' ? 'Comision' : 'Комиссия' ?></th><th><?= $lclang === 'ro' ? 'Link' : 'Ссылка' ?></th></tr></thead><tbody>
            <?php foreach ($creator_referral_orders as $referralOrder) { ?><tr><td>#<?= html_escape((string) $referralOrder['order_id']) ?></td><td><?= html_escape((string) $referralOrder['updated']) ?></td><td><?= number_format((float) $referralOrder['total'], 2, '.', ' ') ?> MDL</td><td><?= number_format((float) $referralOrder['creator_commission_amount'], 2, '.', ' ') ?> MDL <small>(<?= number_format((float) $referralOrder['creator_commission_percent'], 2, '.', ' ') ?>%)</small></td><td><?= html_escape((string) ($referralOrder['label'] ?: strtoupper($referralOrder['locale']))) ?></td></tr><?php } ?>
            </tbody></table></div>
        <?php } ?>
    </section>

    <section class="creator-account__section" id="creator-payouts" aria-labelledby="creator-payouts-title">
        <div class="creator-account__section-heading"><h4 id="creator-payouts-title"><?= $lclang === 'ro' ? 'Solicitări de plată' : 'Запросы на выплату' ?></h4></div>
        <?php
        $payoutMessages = array(
            'created' => $lclang === 'ro' ? 'Solicitarea a fost trimisă.' : 'Запрос отправлен.',
            'pending_exists' => $lclang === 'ro' ? 'Aveți deja o solicitare în curs.' : 'У вас уже есть запрос на рассмотрении.',
            'not_creator' => $lclang === 'ro' ? 'Accesul la programul creatorilor nu este activ.' : 'Доступ к программе креаторов не активен.',
            'unavailable' => $lclang === 'ro' ? 'Funcția nu este disponibilă momentan.' : 'Функция временно недоступна.',
            'error' => $lclang === 'ro' ? 'Nu am putut salva solicitarea. Încercați din nou.' : 'Не удалось сохранить запрос. Попробуйте ещё раз.',
            'invalid' => $lclang === 'ro' ? 'Verificați suma și comentariul.' : 'Проверьте сумму и комментарий.',
            'insufficient_balance' => $lclang === 'ro' ? 'Suma depășește soldul disponibil.' : 'Сумма превышает доступный баланс комиссии.',
        );
        $payoutNotice = $payoutMessages[$creator_payout_notice ?? ''] ?? '';
        if ($payoutNotice !== '') { ?><p class="creator-account__state" role="status"><?= htmlspecialchars($payoutNotice, ENT_QUOTES, 'UTF-8') ?></p><?php } ?>
        <?php if (empty($creator_payout_available)) { ?>
            <p class="creator-account__state"><?= $lclang === 'ro' ? 'Solicitările de plată nu sunt momentan disponibile.' : 'Запросы на выплату пока недоступны.' ?></p>
        <?php } else { ?>
            <?php $creatorPayoutBalance = round((float) ($creator_payout_available_balance ?? 0), 2); $creatorPayoutMaximum = number_format($creatorPayoutBalance, 2, '.', ''); ?>
            <div class="creator-account__payout-summary">
                <div><span><?= $lclang === 'ro' ? 'Sold disponibil pentru solicitare' : 'Доступно к выплате' ?></span><strong><?= number_format($creatorPayoutBalance, 2, '.', ' ') ?> MDL</strong></div>
                <p><?= $lclang === 'ro' ? 'Plata se efectuează în data de 15 a lunii următoare solicitării, după procesarea de către contabilitate.' : 'Выплата производится 15-го числа месяца после месяца запроса, после обработки бухгалтерией.' ?></p>
            </div>
            <?php $payoutLabels = array('pending' => $lclang === 'ro' ? 'În examinare' : 'На рассмотрении', 'approved' => $lclang === 'ro' ? 'Aprobată' : 'Одобрена', 'rejected' => $lclang === 'ro' ? 'Respinsă' : 'Отклонена', 'paid' => $lclang === 'ro' ? 'Plătită' : 'Выплачено'); ?>
            <?php $hasPendingPayout = false; foreach (($creator_payout_requests ?? array()) as $payout) { if ($payout['status'] === 'pending') $hasPendingPayout = true; } ?>
            <?php if (!$hasPendingPayout && (float) ($creator_payout_available_balance ?? 0) <= 0) { ?>
                <p class="creator-account__state"><?= $lclang === 'ro' ? 'Solicitările pentru plată vor fi disponibile după finalizarea comenzilor recomandate.' : 'Запросить выплату можно будет после завершения заказов по вашим ссылкам.' ?></p>
            <?php } elseif (!$hasPendingPayout) { ?>
                <details class="creator-account__payout-disclosure">
                    <summary><?= $lclang === 'ro' ? 'Solicită plata' : 'Запросить выплату' ?><span aria-hidden="true">+</span></summary>
                    <form class="creator-account__link-create creator-account__payout-form" method="post" action="/creator-payouts/request" data-payout-form>
                        <h5><?= $lclang === 'ro' ? 'Solicitare nouă' : 'Новый запрос' ?></h5>
                        <input type="hidden" name="creator_payout_csrf" value="<?= htmlspecialchars((string) ($creator_payout_csrf ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <div class="creator-account__form-grid creator-account__payout-fields">
                            <div class="creator-account__payout-amount">
                                <label><?= $lclang === 'ro' ? 'Tipul sumei' : 'Сумма запроса' ?><select data-payout-mode aria-label="<?= $lclang === 'ro' ? 'Tipul sumei' : 'Способ выбора суммы' ?>"><option value="all"><?= $lclang === 'ro' ? 'Soldul complet' : 'Вся сумма' ?></option><option value="custom"><?= $lclang === 'ro' ? 'Sumă personalizată' : 'Своя сумма' ?></option></select></label>
                                <label><?= $lclang === 'ro' ? 'Sumă, MDL' : 'Сумма, MDL' ?><input type="number" name="amount_mdl" min="0.01" max="<?= htmlspecialchars($creatorPayoutMaximum, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($creatorPayoutMaximum, ENT_QUOTES, 'UTF-8') ?>" step="0.01" inputmode="decimal" required readonly data-payout-amount></label>
                                <div class="creator-account__payout-slider" data-payout-slider hidden><input type="range" min="0.01" max="<?= htmlspecialchars($creatorPayoutMaximum, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($creatorPayoutMaximum, ENT_QUOTES, 'UTF-8') ?>" step="0.01" aria-label="<?= $lclang === 'ro' ? 'Alege suma plății' : 'Выберите сумму выплаты' ?>" data-payout-range></div>
                                <small class="creator-account__payout-amount-note" data-payout-amount-note><?= $lclang === 'ro' ? 'Se va solicita soldul disponibil integral.' : 'Будет запрошен весь доступный баланс.' ?></small>
                            </div>
                            <label><?= $lclang === 'ro' ? 'Comentariu (opțional)' : 'Комментарий (необязательно)' ?><textarea name="comment" maxlength="1000" rows="3" placeholder="<?= $lclang === 'ro' ? 'Adăugați detalii dacă este necesar' : 'Добавьте детали, если нужно' ?>"></textarea></label>
                        </div>
                        <button class="creator-account__button" type="submit"><?= $lclang === 'ro' ? 'Trimite solicitarea' : 'Отправить запрос' ?></button>
                    </form>
                </details>
            <?php } ?>
            <?php if (!empty($creator_payout_requests)) { ?>
                <div class="creator-account__payout-history">
                    <h5><?= $lclang === 'ro' ? 'Istoricul solicitărilor' : 'История запросов' ?></h5>
                    <div class="creator-account__links-table-wrap"><table class="creator-account__links-table"><thead><tr><th><?= $lclang === 'ro' ? 'Data' : 'Дата' ?></th><th><?= $lclang === 'ro' ? 'Sumă' : 'Сумма' ?></th><th><?= $lclang === 'ro' ? 'Data plății' : 'Дата выплаты' ?></th><th><?= $lclang === 'ro' ? 'Statut' : 'Статус' ?></th><th><?= $lclang === 'ro' ? 'Comentariu' : 'Комментарий' ?></th></tr></thead><tbody>
                    <?php foreach ($creator_payout_requests as $payout) { $payoutStatusClass = in_array($payout['status'], array('pending', 'approved', 'rejected', 'paid'), true) ? $payout['status'] : 'pending'; ?><tr><td><?= html_escape($payout['created_at']) ?></td><td><strong><?= number_format((float) $payout['amount_mdl'], 2, '.', ' ') ?> MDL</strong></td><td><?= html_escape((string) ($payout['expected_payment_date'] ?? '—')) ?></td><td><span class="creator-account__payout-status creator-account__payout-status--<?= $payoutStatusClass ?>"><?= html_escape($payoutLabels[$payout['status']] ?? $payout['status']) ?></span></td><td><?= nl2br(html_escape((string) ($payout['admin_comment'] ?: $payout['comment'] ?: '—'))) ?></td></tr><?php } ?>
                    </tbody></table></div>
                </div>
            <?php } ?>
        <?php } ?>
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
