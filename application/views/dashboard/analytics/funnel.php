<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$funnel = isset($order_funnel) && is_array($order_funnel) ? $order_funnel : array();
$summary = isset($funnel['summary']) && is_array($funnel['summary']) ? $funnel['summary'] : array();
$periods = isset($funnel['periods']) && is_array($funnel['periods']) ? $funnel['periods'] : array();

$number = static function ($value) {
    return number_format((float) $value, 0, '.', ' ');
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

$totalOrders = (int) (isset($summary['total_orders']) ? $summary['total_orders'] : 0);
$statuses = array(
    array('label' => 'Новые', 'key' => 'new_orders'),
    array('label' => 'В обработке', 'key' => 'progress_orders'),
    array('label' => 'Завершённые', 'key' => 'finished_orders'),
    array('label' => 'Отменённые', 'key' => 'canceled_orders'),
);
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li>
        <li><a href="/<?= ADM_CONTROLLER ?>/analytics">Аналитика продаж</a><i class="fa fa-circle"></i></li>
        <li><span>Воронка продаж</span></li>
    </ul>
</div>

<h1 class="page-title">Воронка продаж</h1>

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li><a href="/<?= ADM_CONTROLLER ?>/analytics">Аналитика продаж</a></li>
    <li class="active"><a href="/<?= ADM_CONTROLLER ?>/analytics/funnel">Воронка продаж</a></li>
    <li><a href="/<?= ADM_CONTROLLER ?>/analytics/site">Аналитика сайта</a></li>
</ul>

<form method="get" action="<?= html_escape(current_url()) ?>" class="form-inline" style="margin-bottom:20px;">
    <div class="form-group">
        <label for="funnel-date-from">С</label>
        <input id="funnel-date-from" class="form-control" type="date" name="date_from"
               value="<?= html_escape($date_from) ?>">
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="funnel-date-to">По</label>
        <input id="funnel-date-to" class="form-control" type="date" name="date_to"
               value="<?= html_escape($date_to) ?>">
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="funnel-group-by">Группировка</label>
        <select id="funnel-group-by" class="form-control" name="group_by">
            <option value="month" <?= $group_by === 'month' ? 'selected' : '' ?>>По месяцам</option>
            <option value="day" <?= $group_by === 'day' ? 'selected' : '' ?>>По дням</option>
        </select>
    </div>
    <button type="submit" class="btn blue" style="margin-left:8px;">Применить</button>
</form>

<div class="note note-info">
    В отчёт попадают заказы, созданные за выбранный период. Статус показывает текущее состояние заказа;
    история переходов между статусами в legacy-базе не хранится.
</div>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat blue">
            <div class="visual"><i class="fa fa-shopping-cart"></i></div>
            <div class="details"><div class="number"><?= $number($totalOrders) ?></div><div class="desc">Все заказы</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat green">
            <div class="visual"><i class="fa fa-check"></i></div>
            <div class="details"><div class="number"><?= $number($summary['finished_orders'] ?? 0) ?></div><div class="desc">Завершённые</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat purple">
            <div class="visual"><i class="fa fa-line-chart"></i></div>
            <div class="details"><div class="number"><?= $percent($summary['finished_conversion'] ?? 0) ?></div><div class="desc">Конверсия в завершение</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat red">
            <div class="visual"><i class="fa fa-times"></i></div>
            <div class="details"><div class="number"><?= $percent($summary['cancellation_rate'] ?? 0) ?></div><div class="desc">Доля отмен</div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title"><div class="caption"><i class="icon-graph"></i> Статусы заказов</div></div>
            <div class="portlet-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Статус</th><th>Заказы</th><th>Доля от всех</th></tr></thead>
                    <tbody>
                    <?php foreach ($statuses as $status): ?>
                        <?php $statusCount = (int) ($summary[$status['key']] ?? 0); ?>
                        <tr>
                            <td><?= html_escape($status['label']) ?></td>
                            <td><?= $number($statusCount) ?></td>
                            <td><?= $percent($totalOrders > 0 ? ($statusCount / $totalOrders) * 100 : 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title"><div class="caption"><i class="icon-info"></i> Как читать отчёт</div></div>
            <div class="portlet-body">
                <p><strong>Конверсия в завершение</strong> — завершённые заказы / все созданные заказы.</p>
                <p><strong>Доля отмен</strong> — отменённые заказы / все созданные заказы.</p>
                <p class="text-muted" style="margin-bottom:0;">Заказ учитывается по дате создания, а не по дате последнего изменения статуса.</p>
            </div>
        </div>
    </div>
</div>

<div class="portlet light bordered">
    <div class="portlet-title"><div class="caption"><i class="fa fa-calendar"></i> Динамика по периодам</div></div>
    <div class="portlet-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= $group_by === 'month' ? 'Месяц' : 'Дата' ?></th>
                    <th>Все заказы</th>
                    <th>Новые</th>
                    <th>В обработке</th>
                    <th>Завершённые</th>
                    <th>Отменённые</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($periods)): ?>
                <tr><td colspan="6" class="text-muted">За выбранный период заказов нет.</td></tr>
            <?php else: ?>
                <?php foreach ($periods as $period): ?>
                    <tr>
                        <td><?= html_escape($periodLabel($period['period_key'], $group_by, $monthNames)) ?></td>
                        <td><?= $number($period['total_orders']) ?></td>
                        <td><?= $number($period['new_orders']) ?></td>
                        <td><?= $number($period['progress_orders']) ?></td>
                        <td><?= $number($period['finished_orders']) ?></td>
                        <td><?= $number($period['canceled_orders']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
