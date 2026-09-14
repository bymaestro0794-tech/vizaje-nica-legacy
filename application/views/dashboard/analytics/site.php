<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$siteAnalytics = isset($site_analytics) && is_array($site_analytics) ? $site_analytics : array();
$summary = isset($siteAnalytics['summary']) && is_array($siteAnalytics['summary'])
    ? $siteAnalytics['summary']
    : array();
$checkout = isset($siteAnalytics['checkout']) && is_array($siteAnalytics['checkout'])
    ? $siteAnalytics['checkout']
    : array();
$funnel = isset($siteAnalytics['funnel']) && is_array($siteAnalytics['funnel'])
    ? $siteAnalytics['funnel']
    : array();
$periods = isset($siteAnalytics['periods']) && is_array($siteAnalytics['periods'])
    ? $siteAnalytics['periods']
    : array();
$sources = isset($siteAnalytics['sources']) && is_array($siteAnalytics['sources'])
    ? $siteAnalytics['sources']
    : array();
$devices = isset($siteAnalytics['devices']) && is_array($siteAnalytics['devices'])
    ? $siteAnalytics['devices']
    : array();
$topPages = isset($siteAnalytics['top_pages']) && is_array($siteAnalytics['top_pages'])
    ? $siteAnalytics['top_pages']
    : array();
$landingPages = isset($siteAnalytics['landing_pages']) && is_array($siteAnalytics['landing_pages'])
    ? $siteAnalytics['landing_pages']
    : array();

$number = static function ($value) {
    return number_format((float) $value, 0, '.', ' ');
};

$money = static function ($value) {
    return number_format((float) $value, 2, '.', ' ') . ' MDL';
};

$percent = static function ($value) {
    return number_format((float) $value, 2, '.', ' ') . '%';
};

$monthNames = array(
    1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
    5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
    9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
);

$periodLabel = static function ($period, $groupBy, $monthNames) {
    if ($groupBy !== 'month') {
        return $period;
    }

    $date = DateTime::createFromFormat('!Y-m', (string) $period);

    if (!$date) {
        return $period;
    }

    return $monthNames[(int) $date->format('n')] . ' ' . $date->format('Y');
};

$funnelSteps = array(
    'session_start' => 'Сессии',
    'view_catalog' => 'Просмотр каталога',
    'view_item' => 'Просмотр товара',
    'add_to_cart' => 'Добавление в корзину',
    'view_cart' => 'Просмотр корзины',
    'begin_checkout' => 'Начало оформления',
    'purchase' => 'Покупка',
);

$baseSessions = (int) ($summary['sessions'] ?? 0);

$sourceNames = array(
    'instagram' => 'Instagram',
    'facebook' => 'Facebook',
    'tiktok' => 'TikTok',
    'youtube' => 'YouTube',
    'vk' => 'ВКонтакте',
    'google' => 'Google',
    'bing' => 'Bing',
    'yandex' => 'Яндекс',
    'duckduckgo' => 'DuckDuckGo',
    'direct' => 'Прямой заход',
    'referral' => 'Переход по ссылке',
    'unknown' => 'Не определён',
);

$deviceNames = array(
    'mobile' => 'Мобильный',
    'tablet' => 'Планшет',
    'desktop' => 'Компьютер',
    'unknown' => 'Не определён',
);

$selectedSource = isset($traffic_source) ? (string) $traffic_source : '';
$topPagesPagination = isset($siteAnalytics['top_pages_pagination']) && is_array($siteAnalytics['top_pages_pagination'])
    ? $siteAnalytics['top_pages_pagination']
    : array('page' => 1, 'per_page' => 20, 'total' => count($topPages), 'total_pages' => 1);
$landingPagesPagination = isset($siteAnalytics['landing_pages_pagination']) && is_array($siteAnalytics['landing_pages_pagination'])
    ? $siteAnalytics['landing_pages_pagination']
    : array('page' => 1, 'per_page' => 20, 'total' => count($landingPages), 'total_pages' => 1);

$buildPaginationUrl = static function ($page, $pageParameter) use ($date_from, $date_to, $group_by, $selectedSource, $topPagesPagination, $landingPagesPagination) {
    $query = array(
        'date_from' => $date_from,
        'date_to' => $date_to,
        'group_by' => $group_by,
        'top_pages_page' => (int) $topPagesPagination['page'],
        'landing_pages_page' => (int) $landingPagesPagination['page'],
    );

    if ($selectedSource !== '') {
        $query['traffic_source'] = $selectedSource;
    }

    $query[$pageParameter] = max(1, (int) $page);

    return current_url() . '?' . http_build_query($query);
};

$renderPagination = static function ($pagination, $pageParameter, $buildPaginationUrl) {
    $currentPage = (int) ($pagination['page'] ?? 1);
    $totalPages = (int) ($pagination['total_pages'] ?? 1);
    $total = (int) ($pagination['total'] ?? 0);

    if ($totalPages <= 1) {
        return;
    }

    echo '<div class="text-muted" style="margin-top:10px;">Всего страниц: ' . $total . '</div>';
    echo '<ul class="pagination" style="margin:10px 0 0;">';

    if ($currentPage > 1) {
        echo '<li><a href="' . html_escape($buildPaginationUrl($currentPage - 1, $pageParameter)) . '">← Назад</a></li>';
    }

    for ($page = 1; $page <= $totalPages; $page++) {
        $active = $page === $currentPage ? ' class="active"' : '';
        echo '<li' . $active . '><a href="' . html_escape($buildPaginationUrl($page, $pageParameter)) . '">' . $page . '</a></li>';
    }

    if ($currentPage < $totalPages) {
        echo '<li><a href="' . html_escape($buildPaginationUrl($currentPage + 1, $pageParameter)) . '">Далее →</a></li>';
    }

    echo '</ul>';
};
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li>
        <li><span>Аналитика сайта</span></li>
    </ul>
</div>

<h1 class="page-title">Аналитика сайта <small>first-party события</small></h1>

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li><a href="/<?= ADM_CONTROLLER ?>/analytics">Аналитика продаж</a></li>
    <li><a href="/<?= ADM_CONTROLLER ?>/analytics/funnel">Воронка продаж</a></li>
    <li class="active"><a href="/<?= ADM_CONTROLLER ?>/analytics/site">Аналитика сайта</a></li>
</ul>

<form method="get" action="<?= html_escape(current_url()) ?>" class="form-inline" style="margin-bottom:20px;">
    <div class="form-group">
        <label for="site-date-from">С</label>
        <input id="site-date-from" class="form-control" type="date" name="date_from" value="<?= html_escape($date_from) ?>">
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="site-date-to">По</label>
        <input id="site-date-to" class="form-control" type="date" name="date_to" value="<?= html_escape($date_to) ?>">
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="site-group-by">Группировка</label>
        <select id="site-group-by" class="form-control" name="group_by">
            <option value="month" <?= $group_by === 'month' ? 'selected' : '' ?>>По месяцам</option>
            <option value="day" <?= $group_by === 'day' ? 'selected' : '' ?>>По дням</option>
        </select>
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="site-traffic-source">Источник</label>
        <select id="site-traffic-source" class="form-control" name="traffic_source">
            <option value="" <?= $selectedSource === '' ? 'selected' : '' ?>>Все источники</option>
            <?php foreach ($sourceNames as $sourceKey => $sourceLabel): ?>
                <?php if ($sourceKey === 'unknown') { continue; } ?>
                <option value="<?= html_escape($sourceKey) ?>" <?= $selectedSource === $sourceKey ? 'selected' : '' ?>>
                    <?= html_escape($sourceLabel) ?>
                </option>
            <?php endforeach; ?>
            <option value="unknown" <?= $selectedSource === 'unknown' ? 'selected' : '' ?>>Не определён</option>
        </select>
    </div>
    <button type="submit" class="btn blue" style="margin-left:8px;">Применить</button>
</form>

<div class="note note-info">
    Данные считаются по событиям с согласованной аналитикой. Посетители и сессии считаются уникально за выбранный период.
</div>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat blue">
            <div class="visual"><i class="fa fa-users"></i></div>
            <div class="details"><div class="number"><?= $number($summary['visitors'] ?? 0) ?></div><div class="desc">Посетители</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat green">
            <div class="visual"><i class="fa fa-refresh"></i></div>
            <div class="details"><div class="number"><?= $number($summary['sessions'] ?? 0) ?></div><div class="desc">Сессии</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat purple">
            <div class="visual"><i class="fa fa-shopping-bag"></i></div>
            <div class="details"><div class="number"><?= $number($summary['purchases'] ?? 0) ?></div><div class="desc">Покупки</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat red">
            <div class="visual"><i class="fa fa-line-chart"></i></div>
            <div class="details"><div class="number"><?= $percent($summary['conversion_rate'] ?? 0) ?></div><div class="desc">Конверсия в покупку</div></div>
        </div>
    </div>
</div>

<div class="portlet light bordered">
    <div class="portlet-title"><div class="caption"><i class="icon-check"></i> Потери на оформлении</div></div>
    <div class="portlet-body">
        <div class="note note-info">
            Потерянные checkout-сессии — это начавшие оформление сессии, в которых за выбранный период не зафиксирована покупка. Это расчётный показатель, а не причина ухода.
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div style="padding:16px;border-right:1px solid #eee;">
                    <div class="text-muted">Начали оформление</div>
                    <div style="font-size:30px;font-weight:300;"><?= $number($checkout['checkout_sessions'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="padding:16px;border-right:1px solid #eee;">
                    <div class="text-muted">Завершили покупкой</div>
                    <div style="font-size:30px;font-weight:300;"><?= $number($checkout['purchase_sessions'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="padding:16px;border-right:1px solid #eee;">
                    <div class="text-muted">Потеряно</div>
                    <div style="font-size:30px;font-weight:300;"><?= $number($checkout['dropoffs'] ?? 0) ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="padding:16px;">
                    <div class="text-muted">Конверсия checkout</div>
                    <div style="font-size:30px;font-weight:300;"><?= $percent($checkout['conversion_rate'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title"><div class="caption"><i class="icon-graph"></i> Воронка сайта</div></div>
            <div class="portlet-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Этап</th><th>Сессии</th><th>От всех сессий</th></tr></thead>
                    <tbody>
                    <?php foreach ($funnelSteps as $eventName => $label): ?>
                        <?php $stepSessions = (int) ($funnel[$eventName]['sessions'] ?? 0); ?>
                        <?php $stepRate = $baseSessions > 0 ? ($stepSessions / $baseSessions) * 100 : 0; ?>
                        <tr>
                            <td><?= html_escape($label) ?></td>
                            <td><?= $number($stepSessions) ?></td>
                            <td><?= $percent($stepRate) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title"><div class="caption"><i class="icon-pie-chart"></i> События</div></div>
            <div class="portlet-body">
                <div style="font-size:34px;font-weight:300;margin-bottom:6px;"><?= $number($summary['total_events'] ?? 0) ?></div>
                <div class="text-muted">Всего событий за выбранный период</div>
                <hr>
                <div><strong>Просмотры товаров:</strong> <?= $number($summary['product_view_sessions'] ?? 0) ?> сессий</div>
                <div><strong>Выручка покупок:</strong> <?= $money($summary['purchase_revenue'] ?? 0) ?></div>
                <div><strong>Покупки:</strong> <?= $number($summary['purchases'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="portlet light bordered">
    <div class="portlet-title"><div class="caption"><i class="icon-share"></i> Источники трафика</div></div>
    <div class="portlet-body table-responsive">
        <div class="note note-info">
            Instagram и Google определяются по UTM-меткам или домену перехода. Для точной рекламы используй ссылки с `utm_source`, `utm_medium` и `utm_campaign`.
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Источник</th>
                    <th>Канал</th>
                    <th>Кампания</th>
                    <th>Посетители</th>
                    <th>Сессии</th>
                    <th>Покупки</th>
                    <th>Конверсия</th>
                    <th>Выручка</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($sources)): ?>
                <tr><td colspan="8" class="text-muted">Источников за выбранный период нет.</td></tr>
            <?php else: ?>
                <?php foreach ($sources as $source): ?>
                    <?php
                    $sourceKey = (string) ($source['traffic_source'] ?? 'unknown');
                    $sourceSessions = (int) ($source['sessions'] ?? 0);
                    $sourcePurchases = (int) ($source['purchases'] ?? 0);
                    $sourceConversion = $sourceSessions > 0
                        ? ($sourcePurchases / $sourceSessions) * 100
                        : 0;
                    ?>
                    <tr>
                        <td><?= html_escape($sourceNames[$sourceKey] ?? ucfirst($sourceKey)) ?></td>
                        <td><?= html_escape($source['traffic_medium'] ?: '—') ?></td>
                        <td><?= html_escape($source['traffic_campaign'] ?: '—') ?></td>
                        <td><?= $number($source['visitors']) ?></td>
                        <td><?= $number($sourceSessions) ?></td>
                        <td><?= $number($sourcePurchases) ?></td>
                        <td><?= $percent($sourceConversion) ?></td>
                        <td><?= $money($source['purchase_revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <?php $renderPagination($topPagesPagination, 'top_pages_page', $buildPaginationUrl); ?>
    </div>
</div>

<div class="portlet light bordered">
    <div class="portlet-title"><div class="caption"><i class="icon-screen-smartphone"></i> Устройства</div></div>
    <div class="portlet-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Устройство</th>
                    <th>Посетители</th>
                    <th>Сессии</th>
                    <th>Просмотры товаров</th>
                    <th>Покупки</th>
                    <th>Конверсия</th>
                    <th>Выручка</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($devices)): ?>
                <tr><td colspan="7" class="text-muted">Данных об устройствах за выбранный период нет.</td></tr>
            <?php else: ?>
                <?php foreach ($devices as $device): ?>
                    <?php
                    $deviceKey = (string) ($device['device_type'] ?? 'unknown');
                    $deviceSessions = (int) ($device['sessions'] ?? 0);
                    $devicePurchases = (int) ($device['purchases'] ?? 0);
                    $deviceConversion = $deviceSessions > 0
                        ? ($devicePurchases / $deviceSessions) * 100
                        : 0;
                    ?>
                    <tr>
                        <td><?= html_escape($deviceNames[$deviceKey] ?? ucfirst($deviceKey)) ?></td>
                        <td><?= $number($device['visitors']) ?></td>
                        <td><?= $number($deviceSessions) ?></td>
                        <td><?= $number($device['product_views']) ?></td>
                        <td><?= $number($devicePurchases) ?></td>
                        <td><?= $percent($deviceConversion) ?></td>
                        <td><?= $money($device['purchase_revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <?php $renderPagination($landingPagesPagination, 'landing_pages_page', $buildPaginationUrl); ?>
    </div>
</div>

<div class="portlet light bordered">
    <div class="portlet-title"><div class="caption"><i class="icon-globe"></i> Популярные страницы</div></div>
    <div class="portlet-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Страница</th>
                    <th>Посетители</th>
                    <th>Сессии</th>
                    <th>События</th>
                    <th>Просмотры товаров</th>
                    <th>Добавления в корзину</th>
                    <th>Оформления</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($topPages)): ?>
                <tr><td colspan="7" class="text-muted">Страниц за выбранный период нет.</td></tr>
            <?php else: ?>
                <?php foreach ($topPages as $page): ?>
                    <tr>
                        <td><code><?= html_escape($page['page_path']) ?></code></td>
                        <td><?= $number($page['visitors']) ?></td>
                        <td><?= $number($page['sessions']) ?></td>
                        <td><?= $number($page['page_events']) ?></td>
                        <td><?= $number($page['product_views']) ?></td>
                        <td><?= $number($page['add_to_cart']) ?></td>
                        <td><?= $number($page['checkouts']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="portlet light bordered">
    <div class="portlet-title"><div class="caption"><i class="icon-login"></i> Страницы входа</div></div>
    <div class="portlet-body table-responsive">
        <div class="note note-info">
            Страница входа — первая зафиксированная страница сессии. Конверсия считается как покупки этой группы сессий, делённые на её сессии.
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Первая страница</th>
                    <th>Посетители</th>
                    <th>Сессии</th>
                    <th>Покупки</th>
                    <th>Конверсия</th>
                    <th>Выручка</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($landingPages)): ?>
                <tr><td colspan="6" class="text-muted">Страниц входа за выбранный период нет.</td></tr>
            <?php else: ?>
                <?php foreach ($landingPages as $page): ?>
                    <?php
                    $landingSessions = (int) ($page['sessions'] ?? 0);
                    $landingPurchases = (int) ($page['purchases'] ?? 0);
                    $landingConversion = $landingSessions > 0
                        ? ($landingPurchases / $landingSessions) * 100
                        : 0;
                    ?>
                    <tr>
                        <td><code><?= html_escape($page['landing_page']) ?></code></td>
                        <td><?= $number($page['visitors']) ?></td>
                        <td><?= $number($landingSessions) ?></td>
                        <td><?= $number($landingPurchases) ?></td>
                        <td><?= $percent($landingConversion) ?></td>
                        <td><?= $money($page['purchase_revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="portlet light bordered">
    <div class="portlet-title"><div class="caption"><i class="icon-calendar"></i> Динамика по периодам</div></div>
    <div class="portlet-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= $group_by === 'month' ? 'Месяц' : 'Дата' ?></th>
                    <th>Посетители</th>
                    <th>Сессии</th>
                    <th>Просмотры товаров</th>
                    <th>Добавления в корзину</th>
                    <th>Оформления</th>
                    <th>Покупки</th>
                    <th>Выручка</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($periods)): ?>
                <tr><td colspan="8" class="text-muted">Событий за выбранный период нет.</td></tr>
            <?php else: ?>
                <?php foreach ($periods as $period): ?>
                    <tr>
                        <td><?= html_escape($periodLabel($period['period_key'], $group_by, $monthNames)) ?></td>
                        <td><?= $number($period['visitors']) ?></td>
                        <td><?= $number($period['sessions']) ?></td>
                        <td><?= $number($period['product_views']) ?></td>
                        <td><?= $number($period['add_to_cart']) ?></td>
                        <td><?= $number($period['checkouts']) ?></td>
                        <td><?= $number($period['purchases']) ?></td>
                        <td><?= $money($period['purchase_revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
