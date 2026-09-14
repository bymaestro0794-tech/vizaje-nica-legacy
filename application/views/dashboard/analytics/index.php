<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$analytics = isset($analytics) && is_array($analytics) ? $analytics : array();
$summary = isset($analytics['summary']) && is_array($analytics['summary'])
    ? $analytics['summary']
    : array();
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
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li>
        <li><span>Аналитика продаж</span></li>
    </ul>
</div>

<h1 class="page-title">Аналитика продаж <small>завершённые заказы</small></h1>

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li class="active"><a href="/<?= ADM_CONTROLLER ?>/analytics">Аналитика продаж</a></li>
    <li><a href="/<?= ADM_CONTROLLER ?>/analytics/funnel">Воронка продаж</a></li>
    <li><a href="/<?= ADM_CONTROLLER ?>/analytics/site">Аналитика сайта</a></li>
</ul>

<form method="get" action="<?= html_escape(current_url()) ?>" class="form-inline" style="margin-bottom:20px;">
    <div class="form-group">
        <label for="analytics-date-from">С</label>
        <input id="analytics-date-from" class="form-control" type="date" name="date_from"
               value="<?= html_escape($date_from) ?>">
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="analytics-date-to">По</label>
        <input id="analytics-date-to" class="form-control" type="date" name="date_to"
               value="<?= html_escape($date_to) ?>">
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="analytics-group-by">Группировка</label>
        <select id="analytics-group-by" class="form-control" name="group_by">
            <option value="month" <?= $group_by === 'month' ? 'selected' : '' ?>>По месяцам</option>
            <option value="day" <?= $group_by === 'day' ? 'selected' : '' ?>>По дням</option>
        </select>
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="analytics-brand-id">Бренд</label>
        <select id="analytics-brand-id" class="form-control" name="brand_id">
            <option value="">Все бренды</option>
            <?php foreach ((array) $brands as $brand): ?>
                <option value="<?= (int) $brand['id'] ?>" <?= (int) $brand_id === (int) $brand['id'] ? 'selected' : '' ?>>
                    <?= html_escape($brand['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="analytics-direction">Порядок</label>
        <select id="analytics-direction" class="form-control" name="direction">
            <option value="desc" <?= $direction === 'desc' ? 'selected' : '' ?>>По убыванию</option>
            <option value="asc" <?= $direction === 'asc' ? 'selected' : '' ?>>По возрастанию</option>
        </select>
    </div>
    <button type="submit" class="btn blue" style="margin-left:8px;">Применить</button>

    <div style="margin-top:12px;">
        <label for="analytics-product-sort">Сортировка товаров</label>
        <select id="analytics-product-sort" class="form-control" name="product_sort" style="display:inline-block;width:auto;margin-left:8px;">
            <option value="quantity" <?= $product_sort === 'quantity' ? 'selected' : '' ?>>По количеству</option>
            <option value="revenue" <?= $product_sort === 'revenue' ? 'selected' : '' ?>>По выручке</option>
            <option value="orders" <?= $product_sort === 'orders' ? 'selected' : '' ?>>По заказам</option>
            <option value="title" <?= $product_sort === 'title' ? 'selected' : '' ?>>По названию</option>
        </select>

        <label for="analytics-brand-sort" style="margin-left:16px;">Сортировка брендов</label>
        <select id="analytics-brand-sort" class="form-control" name="brand_sort" style="display:inline-block;width:auto;margin-left:8px;">
            <option value="quantity" <?= $brand_sort === 'quantity' ? 'selected' : '' ?>>По количеству</option>
            <option value="revenue" <?= $brand_sort === 'revenue' ? 'selected' : '' ?>>По выручке</option>
            <option value="orders" <?= $brand_sort === 'orders' ? 'selected' : '' ?>>По заказам</option>
            <option value="title" <?= $brand_sort === 'title' ? 'selected' : '' ?>>По названию</option>
        </select>

        <label for="analytics-category-sort" style="margin-left:16px;">Сортировка категорий</label>
        <select id="analytics-category-sort" class="form-control" name="category_sort" style="display:inline-block;width:auto;margin-left:8px;">
            <option value="quantity" <?= $category_sort === 'quantity' ? 'selected' : '' ?>>По количеству</option>
            <option value="revenue" <?= $category_sort === 'revenue' ? 'selected' : '' ?>>По выручке</option>
            <option value="orders" <?= $category_sort === 'orders' ? 'selected' : '' ?>>По заказам</option>
            <option value="title" <?= $category_sort === 'title' ? 'selected' : '' ?>>По названию</option>
        </select>
    </div>
</form>

<div class="note note-info">
    В отчёт попадают только заказы со статусом <strong>finished</strong>.
    Выручка по товарам рассчитывается по позициям заказа и не включает доставку.
</div>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat blue">
            <div class="visual"><i class="fa fa-shopping-cart"></i></div>
            <div class="details"><div class="number"><?= $number($summary['finished_orders'] ?? 0) ?></div><div class="desc">Завершённые заказы</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat green">
            <div class="visual"><i class="fa fa-cubes"></i></div>
            <div class="details"><div class="number"><?= $number($summary['sold_quantity'] ?? 0) ?></div><div class="desc">Проданные товары</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat purple">
            <div class="visual"><i class="fa fa-line-chart"></i></div>
            <div class="details"><div class="number"><?= $money($summary['product_revenue'] ?? 0) ?></div><div class="desc">Выручка по товарам</div></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="dashboard-stat red">
            <div class="visual"><i class="fa fa-calculator"></i></div>
            <div class="details"><div class="number"><?= $money($summary['average_order_value'] ?? 0) ?></div><div class="desc"><?= $brand_id ? 'Средний чек заказов с брендом' : 'Средний чек' ?></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="portlet light bordered">
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
        <div class="portlet light bordered">
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

<div class="portlet light bordered">
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

<div class="portlet light bordered">
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
