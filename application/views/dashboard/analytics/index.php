<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$analytics = isset($analytics) && is_array($analytics) ? $analytics : array();
$summary = isset($analytics['summary']) && is_array($analytics['summary'])
    ? $analytics['summary']
    : array();
$previousAnalytics = isset($previous_analytics) && is_array($previous_analytics) ? $previous_analytics : array();
$previousSummary = isset($previousAnalytics['summary']) && is_array($previousAnalytics['summary']) ? $previousAnalytics['summary'] : array();
$currentRevenue = (float) ($summary['product_revenue'] ?? 0);
$previousRevenue = (float) ($previousSummary['product_revenue'] ?? 0);
$revenueChange = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : null;
$activeFilterCount = (int) ($group_by === 'day')
    + (int) ($brand_id !== null)
    + (int) ($order_source !== '')
    + (int) ($direction === 'asc')
    + (int) ($product_sort !== 'quantity')
    + (int) ($brand_sort !== 'quantity')
    + (int) ($category_sort !== 'quantity');
$topProducts = isset($analytics['top_products']) && is_array($analytics['top_products'])
    ? $analytics['top_products']
    : array();
$topBrands = isset($analytics['top_brands']) && is_array($analytics['top_brands'])
    ? $analytics['top_brands']
    : array();
$periodSales = isset($analytics['period_sales']) && is_array($analytics['period_sales'])
    ? $analytics['period_sales']
    : array();

$money = static function ($value) {
    return number_format((float) $value, 2, '.', ' ') . ' MDL';
};

$number = static function ($value) {
    return number_format((float) $value, 0, '.', ' ');
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

$salesPeriodsByKey = array();
foreach ($periodSales as $period) {
    $salesPeriodsByKey[(string) ($period['period_key'] ?? '')] = $period;
}
$salesChart = array('labels' => array(), 'product_revenue' => array(), 'finished_orders' => array(), 'sold_quantity' => array());
$salesChartDate = new DateTime($group_by === 'day' ? $date_from : (new DateTime($date_from))->modify('first day of this month')->format('Y-m-d'));
$salesChartEndDate = new DateTime($group_by === 'day' ? $date_to : (new DateTime($date_to))->modify('first day of this month')->format('Y-m-d'));
$salesChartStep = $group_by === 'day' ? '+1 day' : '+1 month';
while ($salesChartDate <= $salesChartEndDate) {
    $periodKey = $group_by === 'day' ? $salesChartDate->format('Y-m-d') : $salesChartDate->format('Y-m');
    $period = isset($salesPeriodsByKey[$periodKey]) ? $salesPeriodsByKey[$periodKey] : array();
    $salesChart['labels'][] = $group_by === 'day'
        ? $salesChartDate->format('d.m')
        : $periodLabel($periodKey, $group_by, $monthNames);
    $salesChart['product_revenue'][] = (float) ($period['product_revenue'] ?? 0);
    $salesChart['finished_orders'][] = (int) ($period['finished_orders'] ?? 0);
    $salesChart['sold_quantity'][] = (int) ($period['sold_quantity'] ?? 0);
    $salesChartDate->modify($salesChartStep);
}
$salesChartJson = json_encode($salesChart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="analytics-dashboard analytics-dashboard-sales">
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li>
        <li><span>Аналитика продаж</span></li>
    </ul>
</div>

<h1 class="page-title">Аналитика продаж <small>завершённые заказы</small></h1>

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li><a href="/<?= ADM_CONTROLLER ?>/analytics/site">Сайт</a></li>
    <li class="active"><a href="/<?= ADM_CONTROLLER ?>/analytics">Продажи</a></li>
</ul>

<form method="get" action="<?= html_escape(current_url()) ?>" class="analytics-filter-form" data-analytics-filter>
    <div class="analytics-filter-toolbar">
        <div class="analytics-filter-period-summary">
            <span class="analytics-filter-eyebrow">Период отчёта</span>
            <strong><i class="fa fa-calendar-o"></i> <span data-analytics-period-summary><?= html_escape($dateLabel($date_from)) ?> — <?= html_escape($dateLabel($date_to)) ?></span></strong>
        </div>
        <button type="button" class="analytics-filter-open" data-analytics-filter-open aria-haspopup="dialog" aria-expanded="false">
            <i class="fa fa-sliders"></i><span>Настроить отчёт</span>
            <?php if ($activeFilterCount > 0): ?><span class="analytics-filter-count"><?= $activeFilterCount ?></span><?php endif; ?>
        </button>
        <span class="analytics-filter-caption">Период, канал продаж и сортировка</span>
    </div>

    <div class="analytics-filter-backdrop" data-analytics-filter-close aria-hidden="true"></div>
    <aside class="analytics-filter-drawer" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="analytics-filter-title">
        <header class="analytics-filter-drawer__header">
            <div><span class="analytics-filter-eyebrow">АНАЛИТИКА ПРОДАЖ</span><h2 id="analytics-filter-title">Настройки отчёта</h2></div>
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
                    <label>С<input id="analytics-date-from" class="form-control" type="date" name="date_from" value="<?= html_escape($date_from) ?>"></label>
                    <label>По<input id="analytics-date-to" class="form-control" type="date" name="date_to" value="<?= html_escape($date_to) ?>"></label>
                </div>
            </section>

            <section class="analytics-filter-section">
                <h3><span>02</span> Состав отчёта</h3>
                <label class="analytics-filter-field">Группировать данные
                    <select id="analytics-group-by" class="form-control" name="group_by">
                        <option value="month" <?= $group_by === 'month' ? 'selected' : '' ?>>По месяцам</option>
                        <option value="day" <?= $group_by === 'day' ? 'selected' : '' ?>>По дням</option>
                    </select>
                </label>
                <label class="analytics-filter-field">Бренд
                    <select id="analytics-brand-id" class="form-control" name="brand_id">
                        <option value="">Все бренды</option>
                        <?php foreach ((array) $brands as $brand): ?>
                            <option value="<?= (int) $brand['id'] ?>" <?= (int) $brand_id === (int) $brand['id'] ? 'selected' : '' ?>><?= html_escape($brand['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="analytics-filter-field">Откуда оформлен заказ
                    <select id="analytics-order-source" class="form-control" name="order_source">
                        <option value="" <?= empty($order_source) ? 'selected' : '' ?>>Все заказы</option>
                        <option value="web" <?= $order_source === 'web' ? 'selected' : '' ?>>Сайт (Web)</option>
                        <option value="app" <?= $order_source === 'app' ? 'selected' : '' ?>>Приложение (App)</option>
                    </select>
                    <small>Старые заказы без отметки источника останутся в варианте «Все заказы».</small>
                </label>
            </section>

            <section class="analytics-filter-section">
                <h3><span>03</span> Сортировка</h3>
                <label class="analytics-filter-field">Порядок
                    <select id="analytics-direction" class="form-control" name="direction">
                        <option value="desc" <?= $direction === 'desc' ? 'selected' : '' ?>>Сначала больше</option>
                        <option value="asc" <?= $direction === 'asc' ? 'selected' : '' ?>>Сначала меньше</option>
                    </select>
                </label>
                <div class="analytics-filter-sort-grid">
                    <label class="analytics-filter-field">Товары
                        <select id="analytics-product-sort" class="form-control" name="product_sort">
                            <option value="quantity" <?= $product_sort === 'quantity' ? 'selected' : '' ?>>По количеству</option>
                            <option value="revenue" <?= $product_sort === 'revenue' ? 'selected' : '' ?>>По выручке</option>
                            <option value="orders" <?= $product_sort === 'orders' ? 'selected' : '' ?>>По заказам</option>
                            <option value="title" <?= $product_sort === 'title' ? 'selected' : '' ?>>По названию</option>
                        </select>
                    </label>
                    <label class="analytics-filter-field">Бренды
                        <select id="analytics-brand-sort" class="form-control" name="brand_sort">
                            <option value="quantity" <?= $brand_sort === 'quantity' ? 'selected' : '' ?>>По количеству</option>
                            <option value="revenue" <?= $brand_sort === 'revenue' ? 'selected' : '' ?>>По выручке</option>
                            <option value="orders" <?= $brand_sort === 'orders' ? 'selected' : '' ?>>По заказам</option>
                            <option value="title" <?= $brand_sort === 'title' ? 'selected' : '' ?>>По названию</option>
                        </select>
                    </label>
                    <label class="analytics-filter-field">Категории
                        <select id="analytics-category-sort" class="form-control" name="category_sort">
                            <option value="quantity" <?= $category_sort === 'quantity' ? 'selected' : '' ?>>По количеству</option>
                            <option value="revenue" <?= $category_sort === 'revenue' ? 'selected' : '' ?>>По выручке</option>
                            <option value="orders" <?= $category_sort === 'orders' ? 'selected' : '' ?>>По заказам</option>
                            <option value="title" <?= $category_sort === 'title' ? 'selected' : '' ?>>По названию</option>
                        </select>
                    </label>
                </div>
            </section>
        </div>

        <footer class="analytics-filter-drawer__footer">
            <a href="/<?= ADM_CONTROLLER ?>/analytics">Сбросить</a>
            <button type="submit" class="analytics-filter-apply"><i class="fa fa-check"></i> Показать отчёт</button>
        </footer>
    </aside>
</form>

<div class="analytics-sales-note" role="note">
    <i class="fa fa-info-circle" aria-hidden="true"></i>
    <span><strong>Как читать отчёт:</strong> здесь только заказы со статусом «Завершён». Выручка по товарам — сумма позиций без доставки. Сравнение идёт с предыдущим периодом такой же длины.</span>
</div>

<section class="analytics-sales-metrics" aria-label="Ключевые показатели продаж">
    <?php
    $salesMetrics = array(
        array('label' => 'Завершённые заказы', 'value' => $number($summary['finished_orders'] ?? 0), 'icon' => 'fa-check-circle', 'help' => 'Заказы, у которых сейчас статус «Завершён».', 'key' => 'finished_orders'),
        array('label' => 'Проданные товары', 'value' => $number($summary['sold_quantity'] ?? 0), 'icon' => 'fa-cubes', 'help' => 'Сумма количества товаров из завершённых заказов.', 'key' => 'sold_quantity'),
        array('label' => 'Выручка по товарам', 'value' => $money($summary['product_revenue'] ?? 0), 'icon' => 'fa-line-chart', 'help' => 'Сумма позиций завершённых заказов; доставка не включена.', 'key' => 'product_revenue'),
        array('label' => $brand_id ? 'Средний чек с брендом' : 'Средний чек', 'value' => $money($summary['average_order_value'] ?? 0), 'icon' => 'fa-calculator', 'help' => 'Средняя сумма заказа среди завершённых заказов.', 'key' => 'average_order_value'),
    );
    foreach ($salesMetrics as $index => $metric):
        $metricChange = null;
        $previousValue = (float) ($previousSummary[$metric['key']] ?? 0);
        $currentValue = (float) ($summary[$metric['key']] ?? 0);
        if ($previousValue > 0) {
            $metricChange = (($currentValue - $previousValue) / $previousValue) * 100;
        }
    ?>
        <article class="analytics-sales-metric <?= $index === 2 ? 'is-primary' : '' ?>">
            <div class="analytics-sales-metric__label" title="<?= html_escape($metric['help']) ?>"><i class="fa <?= html_escape($metric['icon']) ?>" aria-hidden="true"></i><?= html_escape($metric['label']) ?><i class="fa fa-info-circle" aria-hidden="true"></i></div>
            <strong class="analytics-sales-metric__value"><?= $metric['value'] ?></strong>
            <?php if ($metricChange !== null): ?>
                <span class="analytics-sales-metric__change <?= $metricChange >= 0 ? 'is-positive' : 'is-negative' ?>"><?= $metricChange >= 0 ? '↗' : '↘' ?> <?= number_format(abs($metricChange), 1, '.', ' ') ?>% к прошлому периоду</span>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<section class="analytics-sales-insight <?= $revenueChange !== null && $revenueChange <= -10 ? 'is-warning' : '' ?>">
    <div class="analytics-sales-insight__icon"><i class="fa fa-lightbulb-o" aria-hidden="true"></i></div>
    <div class="analytics-sales-insight__body">
        <strong>Краткий обзор</strong>
        <p>
            <?php if ($revenueChange === null): ?>
                Для сравнения выручки пока недостаточно данных за предыдущий период.
            <?php elseif ($revenueChange <= -10): ?>
                Выручка по товарам снизилась на <?= number_format(abs($revenueChange), 1, '.', ' ') ?>%: <?= $money($currentRevenue) ?> против <?= $money($previousRevenue) ?> в предыдущем периоде.
            <?php elseif ($revenueChange >= 10): ?>
                Выручка по товарам выросла на <?= number_format($revenueChange, 1, '.', ' ') ?>%: <?= $money($currentRevenue) ?> против <?= $money($previousRevenue) ?> в предыдущем периоде.
            <?php else: ?>
                Выручка по товарам почти не изменилась: <?= $money($currentRevenue) ?> (<?= ($revenueChange > 0 ? '+' : '') . number_format($revenueChange, 1, '.', ' ') ?>% к предыдущему периоду).
            <?php endif; ?>
            <?php if (!empty($topProducts)): ?> Первый товар в текущей сортировке — <strong><?= html_escape($topProducts[0]['product_title']) ?></strong> (<?= $number($topProducts[0]['sold_quantity']) ?> шт.).<?php endif; ?>
        </p>
        <small>Автоматическая сводка по правилам и текущим данным, не ИИ-анализ. Сравнение приблизительное при малом количестве заказов.</small>
    </div>
</section>

<section class="analytics-sales-chart-card" aria-labelledby="analytics-sales-chart-title">
    <div class="analytics-sales-section-heading">
        <div><h2 id="analytics-sales-chart-title">Выручка по товарам</h2><p>Динамика за выбранный период</p></div>
        <span class="analytics-sales-chart-caption"><i></i> Выручка, MDL</span>
    </div>
    <?php if (empty($periodSales)): ?>
        <div class="analytics-sales-empty">За выбранный период завершённых заказов пока нет.</div>
    <?php else: ?>
        <div class="analytics-sales-chart" data-analytics-sales-chart data-chart="<?= html_escape($salesChartJson) ?>"></div>
    <?php endif; ?>
</section>

<div class="row analytics-sales-tables">
    <div class="col-md-6">
        <div class="portlet light bordered analytics-sales-card">
            <div class="portlet-title"><div class="caption"><i class="icon-basket"></i> Популярные товары</div></div>
            <div class="portlet-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Товар</th><th>Бренд</th><th>Количество</th><th>Выручка</th></tr></thead>
                    <tbody>
                    <?php if (empty($topProducts)): ?>
                        <tr><td colspan="4" class="text-muted">За выбранный период завершённых заказов нет.</td></tr>
                    <?php else: ?>
                        <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td><?= html_escape($product['product_title']) ?></td>
                                <td><?= html_escape($product['brand_title']) ?></td>
                                <td><?= $number($product['sold_quantity']) ?></td>
                                <td><?= $money($product['product_revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="portlet light bordered analytics-sales-card">
            <div class="portlet-title"><div class="caption"><i class="icon-tag"></i> Популярные бренды</div></div>
            <div class="portlet-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Бренд</th><th>Товары</th><th>Количество</th><th>Выручка</th></tr></thead>
                    <tbody>
                    <?php if (empty($topBrands)): ?>
                        <tr><td colspan="4" class="text-muted">За выбранный период завершённых заказов нет.</td></tr>
                    <?php else: ?>
                        <?php foreach ($topBrands as $brand): ?>
                            <tr>
                                <td><?= html_escape($brand['brand_title']) ?></td>
                                <td><?= $number($brand['distinct_products']) ?></td>
                                <td><?= $number($brand['sold_quantity']) ?></td>
                                <td><?= $money($brand['product_revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="portlet light bordered analytics-sales-card">
    <div class="portlet-title"><div class="caption"><i class="icon-list"></i> Популярные категории</div></div>
    <div class="portlet-body table-responsive">
        <table class="table table-striped table-hover">
            <thead><tr><th>Категория</th><th>Заказы</th><th>Количество товаров</th><th>Выручка</th></tr></thead>
            <tbody>
            <?php $topCategories = isset($analytics['top_categories']) && is_array($analytics['top_categories']) ? $analytics['top_categories'] : array(); ?>
            <?php if (empty($topCategories)): ?>
                <tr><td colspan="4" class="text-muted">За выбранный период категорий в завершённых заказах нет.</td></tr>
            <?php else: ?>
                <?php foreach ($topCategories as $category): ?>
                    <tr>
                        <td><?= html_escape($category['category_title']) ?></td>
                        <td><?= $number($category['order_count']) ?></td>
                        <td><?= $number($category['sold_quantity']) ?></td>
                        <td><?= $money($category['product_revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="portlet light bordered analytics-sales-card">
    <div class="portlet-title"><div class="caption"><i class="icon-graph"></i> Продажи по периодам</div></div>
    <div class="portlet-body table-responsive">
        <table class="table table-striped table-hover">
            <thead><tr><th><?= $group_by === 'month' ? 'Месяц' : 'Дата' ?></th><th>Завершённые заказы</th><th>Проданные товары</th><th>Выручка по товарам</th><th>Выручка заказов</th></tr></thead>
            <tbody>
            <?php if (empty($periodSales)): ?>
                <tr><td colspan="5" class="text-muted">За выбранный период завершённых заказов нет.</td></tr>
            <?php else: ?>
                <?php foreach ($periodSales as $period): ?>
                    <tr>
                        <td><?= html_escape($periodLabel($period['period_key'], $group_by, $monthNames)) ?></td>
                        <td><?= $number($period['finished_orders']) ?></td>
                        <td><?= $number($period['sold_quantity']) ?></td>
                        <td><?= $money($period['product_revenue']) ?></td>
                        <td><?= $money($period['order_revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<script src="/static/assets/global/plugins/echarts/echarts.js"></script>
<script src="/app/js/dashboard/analytics-sales.js?v=<?= filemtime(FCPATH . 'app/js/dashboard/analytics-sales.js') ?>"></script>
<script src="/app/js/dashboard/analytics-filters.js?v=<?= filemtime(FCPATH . 'app/js/dashboard/analytics-filters.js') ?>"></script>
