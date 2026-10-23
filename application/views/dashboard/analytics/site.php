<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$siteAnalytics = isset($site_analytics) && is_array($site_analytics) ? $site_analytics : array();
$summary = isset($siteAnalytics['summary']) && is_array($siteAnalytics['summary'])
    ? $siteAnalytics['summary']
    : array();
$previousSiteAnalytics = isset($previous_site_analytics) && is_array($previous_site_analytics) ? $previous_site_analytics : array();
$previousSummary = isset($previousSiteAnalytics['summary']) && is_array($previousSiteAnalytics['summary']) ? $previousSiteAnalytics['summary'] : array();
$currentSessions = (int) ($summary['sessions'] ?? 0);
$previousSessions = (int) ($previousSummary['sessions'] ?? 0);
$sessionsChange = $previousSessions > 0 ? (($currentSessions - $previousSessions) / $previousSessions) * 100 : null;
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

$dateLabel = static function ($value) {
    $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
    return $date ? $date->format('d.m.Y') : (string) $value;
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

$chartRowsByPeriod = array();
foreach ($periods as $chartRow) {
    $chartRowsByPeriod[(string) ($chartRow['period_key'] ?? '')] = $chartRow;
}

$chartLabels = array();
$chartVisitors = array();
$chartSessions = array();
$chartDate = new DateTime($group_by === 'day' ? $date_from : (new DateTime($date_from))->modify('first day of this month')->format('Y-m-d'));
$chartEndDate = new DateTime($group_by === 'day' ? $date_to : (new DateTime($date_to))->modify('first day of this month')->format('Y-m-d'));
$chartStep = $group_by === 'day' ? '+1 day' : '+1 month';
while ($chartDate <= $chartEndDate) {
    $periodKey = $group_by === 'day' ? $chartDate->format('Y-m-d') : $chartDate->format('Y-m');
    $chartRow = isset($chartRowsByPeriod[$periodKey]) ? $chartRowsByPeriod[$periodKey] : array();

    if ($group_by === 'day') {
        $chartLabels[] = $chartDate->format('d.m');
    } else {
        $chartLabels[] = $periodLabel($periodKey, $group_by, $monthNames);
    }

    $chartVisitors[] = (int) ($chartRow['visitors'] ?? 0);
    $chartSessions[] = (int) ($chartRow['sessions'] ?? 0);
    $chartDate->modify($chartStep);
}
$chartDataJson = json_encode(array(
    'labels' => $chartLabels,
    'visitors' => $chartVisitors,
    'sessions' => $chartSessions,
), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$metricDelta = static function ($current, $previous) {
    $previous = (float) $previous;
    if ($previous <= 0) {
        return null;
    }

    return (((float) $current - $previous) / $previous) * 100;
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
$showAdvancedPanels = false;
$activeFilterCount = (int) ($group_by === 'day') + (int) ($selectedSource !== '');
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

    echo '<div class="text-muted" style="margin-top:10px;">Всего записей: ' . $total . '</div>';
    echo '<ul class="pagination" style="margin:10px 0 0;">';

    if ($currentPage > 1) {
        echo '<li><a aria-label="Предыдущая страница" href="' . html_escape($buildPaginationUrl($currentPage - 1, $pageParameter)) . '">‹</a></li>';
    }

    $visiblePages = array(1, $totalPages);
    $windowStart = max(2, $currentPage - 2);
    $windowEnd = min($totalPages - 1, $currentPage + 2);
    for ($page = $windowStart; $page <= $windowEnd; $page++) {
        $visiblePages[] = $page;
    }
    $visiblePages = array_values(array_unique($visiblePages));
    sort($visiblePages, SORT_NUMERIC);

    $previousPage = 0;
    foreach ($visiblePages as $page) {
        if ($previousPage > 0 && $page - $previousPage > 1) {
            echo '<li class="disabled"><span>…</span></li>';
        }

        $active = $page === $currentPage ? ' class="active"' : '';
        echo '<li' . $active . '><a href="' . html_escape($buildPaginationUrl($page, $pageParameter)) . '">' . $page . '</a></li>';
        $previousPage = $page;
    }

    if ($currentPage < $totalPages) {
        echo '<li><a aria-label="Следующая страница" href="' . html_escape($buildPaginationUrl($currentPage + 1, $pageParameter)) . '">›</a></li>';
    }

    echo '</ul>';
};
?>

<div class="analytics-dashboard analytics-dashboard-site">
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li>
        <li><span>Аналитика сайта</span></li>
    </ul>
</div>

<h1 class="page-title">Аналитика сайта <small>поведение посетителей</small></h1>

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li class="active"><a href="/<?= ADM_CONTROLLER ?>/analytics/site">Сайт</a></li>
    <li><a href="/<?= ADM_CONTROLLER ?>/analytics">Продажи</a></li>
</ul>

<form method="get" action="<?= html_escape(current_url()) ?>" class="analytics-filter-form" data-analytics-filter>
    <div class="analytics-filter-toolbar">
        <div class="analytics-filter-period-summary">
            <span class="analytics-filter-eyebrow">Период отчёта</span>
            <strong><i class="fa fa-calendar-o"></i> <span data-analytics-period-summary><?= html_escape($dateLabel($date_from)) ?> — <?= html_escape($dateLabel($date_to)) ?></span></strong>
            <?php if ($selectedSource !== ''): ?><span class="analytics-filter-scope">Источник: <?= html_escape($sourceNames[$selectedSource] ?? ucfirst($selectedSource)) ?></span><?php endif; ?>
        </div>
        <button type="button" class="analytics-filter-open" data-analytics-filter-open aria-haspopup="dialog" aria-expanded="false">
            <i class="fa fa-sliders"></i><span>Настроить отчёт</span>
            <?php if ($activeFilterCount > 0): ?><span class="analytics-filter-count"><?= $activeFilterCount ?></span><?php endif; ?>
        </button>
        <span class="analytics-filter-caption">Период и детализация</span>
    </div>

    <div class="analytics-filter-backdrop" data-analytics-filter-close aria-hidden="true"></div>
    <aside class="analytics-filter-drawer" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="analytics-filter-title-site">
        <header class="analytics-filter-drawer__header">
            <div><span class="analytics-filter-eyebrow">АНАЛИТИКА САЙТА</span><h2 id="analytics-filter-title-site">Настройки отчёта</h2></div>
            <button type="button" class="analytics-filter-close" data-analytics-filter-close aria-label="Закрыть фильтры"><i class="fa fa-times"></i></button>
        </header>

        <div class="analytics-filter-drawer__content">
            <section class="analytics-filter-section">
                <h3><span>01</span> Период</h3>
                <div class="analytics-filter-presets" aria-label="Быстрый выбор периода">
                    <button type="button" data-analytics-range="7">7 дней</button>
                    <button type="button" data-analytics-range="30">30 дней</button>
                    <button type="button" data-analytics-range="90">90 дней</button>
                    <button type="button" data-analytics-range="month">Этот месяц</button>
                </div>
                <div class="analytics-filter-date-grid">
                    <label>С<input id="site-date-from" class="form-control" type="date" name="date_from" value="<?= html_escape($date_from) ?>"></label>
                    <label>По<input id="site-date-to" class="form-control" type="date" name="date_to" value="<?= html_escape($date_to) ?>"></label>
                </div>
            </section>
            <section class="analytics-filter-section">
                <h3><span>02</span> Детализация</h3>
                <label class="analytics-filter-field">Показывать данные
                    <select id="site-group-by" class="form-control" name="group_by">
                        <option value="month" <?= $group_by === 'month' ? 'selected' : '' ?>>По месяцам</option>
                        <option value="day" <?= $group_by === 'day' ? 'selected' : '' ?>>По дням</option>
                    </select>
                </label>
                <?php if ($selectedSource !== ''): ?>
                    <input type="hidden" name="traffic_source" value="<?= html_escape($selectedSource) ?>">
                    <p class="analytics-filter-help">Страницы ограничены выбранным источником трафика.</p>
                <?php endif; ?>
            </section>
        </div>

        <footer class="analytics-filter-drawer__footer">
            <a href="/<?= ADM_CONTROLLER ?>/analytics/site">Сбросить</a>
            <button type="submit" class="analytics-filter-apply"><i class="fa fa-check"></i> Показать отчёт</button>
        </footer>
    </aside>
</form>

<?php if ($selectedSource === ''): ?>
<section class="analytics-site-metrics" aria-label="Ключевые показатели">
    <?php
    $siteMetrics = array(
        array('key' => 'visitors', 'label' => 'Посетители', 'help' => 'Уникальные браузеры, которые разрешили аналитику.'),
        array('key' => 'sessions', 'label' => 'Сессии', 'help' => 'Уникальные сессии за период; одна сессия может включать несколько событий.'),
        array('key' => 'purchases', 'label' => 'Покупки', 'help' => 'Заказы, для которых сайт получил событие покупки.'),
        array('key' => 'conversion_rate', 'label' => 'Конверсия', 'help' => 'Покупки, поделённые на количество сессий.'),
    );
    foreach ($siteMetrics as $metric):
        $currentValue = (float) ($summary[$metric['key']] ?? 0);
        $previousValue = (float) ($previousSummary[$metric['key']] ?? 0);
        $delta = $metricDelta($currentValue, $previousValue);
        $formattedValue = $metric['key'] === 'conversion_rate' ? $percent($currentValue) : $number($currentValue);
    ?>
        <div class="analytics-site-metric">
            <div class="analytics-site-metric__label" title="<?= html_escape($metric['help']) ?>"><?= html_escape($metric['label']) ?> <i class="fa fa-info-circle" aria-hidden="true"></i></div>
            <strong class="analytics-site-metric__value"><?= $formattedValue ?></strong>
            <?php if ($delta !== null): ?>
                <span class="analytics-site-delta <?= $delta >= 0 ? 'is-positive' : 'is-negative' ?>">
                    <?= $delta >= 0 ? '↗' : '↘' ?> <?= number_format(abs($delta), 1, '.', ' ') ?>% к предыдущему периоду
                </span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</section>

<div class="analytics-site-disclaimer"><i class="fa fa-info-circle" aria-hidden="true"></i> Учитываются посетители, согласившиеся на аналитические cookies; поэтому цифры могут быть ниже фактического трафика.</div>

<section class="analytics-site-chart-card" aria-labelledby="analytics-site-chart-title">
    <div class="analytics-site-section-heading">
        <div><h2 id="analytics-site-chart-title">Посетители и сессии</h2><p>Динамика за выбранный период</p></div>
        <div class="analytics-site-legend"><span><i class="is-visitors"></i> Посетители</span><span><i class="is-sessions"></i> Сессии</span></div>
    </div>
    <?php if (empty($chartLabels)): ?>
        <div class="analytics-site-empty">За этот период пока нет данных для графика.</div>
    <?php else: ?>
        <div class="analytics-site-chart" data-analytics-site-chart data-chart="<?= html_escape($chartDataJson) ?>"></div>
    <?php endif; ?>
</section>

<div class="analytics-site-insight <?= $sessionsChange !== null && $sessionsChange <= -15 ? 'is-warning' : 'is-neutral' ?>">
    <i class="fa fa-lightbulb-o" aria-hidden="true"></i>
    <span><strong>Что заметно:</strong>
        <?php if ($sessionsChange === null): ?>Для сравнения пока недостаточно данных за предыдущий период.
        <?php elseif ($sessionsChange <= -15): ?>Сессий стало меньше на <?= number_format(abs($sessionsChange), 1, '.', ' ') ?>% к предыдущему периоду.
        <?php elseif ($sessionsChange >= 15): ?>Сессий стало больше на <?= number_format($sessionsChange, 1, '.', ' ') ?>% к предыдущему периоду.
        <?php else: ?>Трафик существенно не изменился: количество сессий изменилось на <?= number_format(abs($sessionsChange), 1, '.', ' ') ?>%.
        <?php endif; ?>
        <small>Автоматическое сравнение метрик, не ИИ-анализ.</small>
    </span>
</div>
<?php endif; ?>

<?php if ($showAdvancedPanels): ?>
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
<?php endif; ?>

<?php if ($showAdvancedPanels): ?>
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
<?php endif; ?>

<?php if ($selectedSource === ''): ?>
<div class="analytics-site-lower-grid">
<section class="analytics-site-data-card">
    <div class="analytics-site-card-heading">
        <div><h2>Источники трафика</h2><p>Нажмите на источник, чтобы увидеть до 15 популярных страниц.</p></div>
        <span class="analytics-site-info" title="Прямой заход — браузер не передал источник. Переход по ссылке — переход с другого сайта. Для рекламы используйте UTM-метки." aria-label="Пояснение об источниках"><i class="fa fa-info-circle" aria-hidden="true"></i></span>
    </div>
        <div class="table-responsive">
        <table class="table analytics-site-table">
            <thead>
                <tr>
                    <th>Источник</th>
                    <th>Посетители</th>
                    <th>Сессии</th>
                    <th>Покупки</th>
                    <th>Конверсия</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($sources)): ?>
                <tr><td colspan="5" class="text-muted">Источников за выбранный период нет.</td></tr>
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
                    <?php
                    $sourceUrl = '/'. ADM_CONTROLLER . '/analytics/site?' . http_build_query(array(
                        'date_from' => $date_from,
                        'date_to' => $date_to,
                        'group_by' => $group_by,
                        'traffic_source' => $sourceKey,
                    ));
                    $mediumNames = array('referral' => 'Переход по ссылке', 'none' => 'Не размечено', 'organic' => 'Поиск', 'social' => 'Соцсети', 'paid' => 'Реклама', 'email' => 'Email');
                    $sourceLabel = $sourceNames[$sourceKey] ?? ucfirst($sourceKey);
                    $sourceMeta = $mediumNames[$source['traffic_medium']] ?? ($source['traffic_medium'] ?: 'Канал не определён');
                    if (!empty($source['traffic_campaign'])) {
                        $sourceMeta .= ' · ' . $source['traffic_campaign'];
                    }
                    ?>
                    <tr>
                        <td><a class="analytics-site-source-link" href="<?= html_escape($sourceUrl) ?>" title="Показать популярные страницы из источника"><?= html_escape($sourceLabel) ?></a><small><?= html_escape($sourceMeta) ?></small></td>
                        <td><?= $number($source['visitors']) ?></td>
                        <td><?= $number($sourceSessions) ?></td>
                        <td><?= $number($sourcePurchases) ?></td>
                        <td><?= $percent($sourceConversion) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="analytics-site-data-card analytics-site-devices">
    <div class="analytics-site-card-heading"><div><h2>Устройства</h2><p>Посетители по типу устройства</p></div></div>
    <?php if (empty($devices)): ?>
        <div class="analytics-site-empty analytics-site-empty--compact">Данных об устройствах за этот период пока нет.</div>
    <?php else: ?>
        <div class="analytics-site-device-list">
            <?php foreach ($devices as $device): ?>
                <?php
                $deviceKey = (string) ($device['device_type'] ?? 'unknown');
                $deviceVisitors = (int) ($device['visitors'] ?? 0);
                $deviceSessions = (int) ($device['sessions'] ?? 0);
                $devicePurchases = (int) ($device['purchases'] ?? 0);
                $deviceShare = (int) ($summary['visitors'] ?? 0) > 0
                    ? min(100, ($deviceVisitors / (int) $summary['visitors']) * 100)
                    : 0;
                ?>
                <div class="analytics-site-device">
                    <div class="analytics-site-device__top"><span><?= html_escape($deviceNames[$deviceKey] ?? ucfirst($deviceKey)) ?></span><strong><?= $number($deviceVisitors) ?></strong></div>
                    <div class="analytics-site-device__track" aria-label="<?= html_escape($deviceNames[$deviceKey] ?? ucfirst($deviceKey)) ?>: <?= number_format($deviceShare, 1, '.', ' ') ?>%"><span style="width:<?= number_format($deviceShare, 2, '.', '') ?>%"></span></div>
                    <small><?= $number($deviceSessions) ?> сессий · <?= $number($devicePurchases) ?> покупок</small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
</div>
<?php endif; ?>

<?php if ($selectedSource !== ''): ?>
<section class="analytics-site-data-card analytics-site-source-pages">
    <div class="analytics-site-card-heading">
        <div><h2>Популярные страницы</h2><p>Источник: <?= html_escape($sourceNames[$selectedSource] ?? ucfirst($selectedSource)) ?> · первые 15 страниц</p></div>
        <a class="analytics-site-back-link" href="<?= html_escape('/'. ADM_CONTROLLER . '/analytics/site?' . http_build_query(array('date_from' => $date_from, 'date_to' => $date_to, 'group_by' => $group_by))) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Все источники</a>
    </div>
    <div class="table-responsive">
        <table class="table analytics-site-table">
            <thead>
                <tr>
                    <th>Страница</th>
                    <th>Посетители</th>
                    <th>Сессии</th>
                    <th>Просмотры товаров</th>
                    <th>Добавления в корзину</th>
                    <th>Оформления</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($topPages)): ?>
                <tr><td colspan="6" class="text-muted">Страниц за выбранный период нет.</td></tr>
            <?php else: ?>
                <?php foreach ($topPages as $page): ?>
                    <tr>
                        <td><code><?= html_escape($page['page_path']) ?></code></td>
                        <td><?= $number($page['visitors']) ?></td>
                        <td><?= $number($page['sessions']) ?></td>
                        <td><?= $number($page['product_views']) ?></td>
                        <td><?= $number($page['add_to_cart']) ?></td>
                        <td><?= $number($page['checkouts']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<?php if ($showAdvancedPanels): ?>
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
        <?php $renderPagination($landingPagesPagination, 'landing_pages_page', $buildPaginationUrl); ?>
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
<?php endif; ?>

</div>
<?php if ($selectedSource === '' && !empty($chartLabels)): ?>
<script src="/static/assets/global/plugins/echarts/echarts.js"></script>
<script src="/app/js/dashboard/analytics-site.js?v=<?= filemtime(FCPATH . 'app/js/dashboard/analytics-site.js') ?>"></script>
<?php endif; ?>
<script src="/app/js/dashboard/analytics-filters.js?v=<?= filemtime(FCPATH . 'app/js/dashboard/analytics-filters.js') ?>"></script>
