<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$analytics = isset($analytics) && is_array($analytics) ? $analytics : array();
$topProducts = isset($analytics['top_products']) && is_array($analytics['top_products']) ? $analytics['top_products'] : array();
$topBrands = isset($analytics['top_brands']) && is_array($analytics['top_brands']) ? $analytics['top_brands'] : array();
$topCategories = isset($analytics['top_categories']) && is_array($analytics['top_categories']) ? $analytics['top_categories'] : array();
$periodSales = isset($analytics['period_sales']) && is_array($analytics['period_sales']) ? $analytics['period_sales'] : array();

$money = static function ($value) {
    return number_format((float) $value, 2, '.', ' ') . ' MDL';
};
$number = static function ($value) {
    return number_format((float) $value, 0, '.', ' ');
};
$periodLabel = static function ($period, $groupBy) {
    if ($groupBy !== 'month') {
        $date = DateTime::createFromFormat('!Y-m-d', (string) $period);
        return $date ? $date->format('d.m.Y') : (string) $period;
    }

    $date = DateTime::createFromFormat('!Y-m', (string) $period);
    if (!$date) {
        return (string) $period;
    }

    $monthNames = array(
        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
    );
    return $monthNames[(int) $date->format('n')] . ' ' . $date->format('Y');
};
?>

<div class="analytics-sales-results">
    <section class="analytics-sales-card">
        <header class="analytics-sales-card__heading"><i class="icon-basket" aria-hidden="true"></i><h2>Популярные товары</h2></header>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>Товар</th><th>Бренд</th><th>Количество</th><th>Выручка</th></tr></thead>
                <tbody>
                <?php if (empty($topProducts)): ?>
                    <tr><td colspan="4" class="text-muted">За выбранный период завершённых заказов нет.</td></tr>
                <?php else: foreach ($topProducts as $product): ?>
                    <tr>
                        <td><?= html_escape($product['product_title'] ?? '') ?></td>
                        <td><?= html_escape($product['brand_title'] ?? '') ?></td>
                        <td><?= $number($product['sold_quantity'] ?? 0) ?></td>
                        <td><?= $money($product['product_revenue'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="analytics-sales-card">
        <header class="analytics-sales-card__heading"><i class="icon-tag" aria-hidden="true"></i><h2>Популярные бренды</h2></header>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>Бренд</th><th>Товары</th><th>Количество</th><th>Выручка</th></tr></thead>
                <tbody>
                <?php if (empty($topBrands)): ?>
                    <tr><td colspan="4" class="text-muted">За выбранный период завершённых заказов нет.</td></tr>
                <?php else: foreach ($topBrands as $brand): ?>
                    <tr>
                        <td><?= html_escape($brand['brand_title'] ?? '') ?></td>
                        <td><?= $number($brand['distinct_products'] ?? 0) ?></td>
                        <td><?= $number($brand['sold_quantity'] ?? 0) ?></td>
                        <td><?= $money($brand['product_revenue'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="analytics-sales-card">
        <header class="analytics-sales-card__heading"><i class="icon-list" aria-hidden="true"></i><h2>Популярные категории</h2></header>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th>Категория</th><th>Заказы</th><th>Количество товаров</th><th>Выручка</th></tr></thead>
                <tbody>
                <?php if (empty($topCategories)): ?>
                    <tr><td colspan="4" class="text-muted">За выбранный период категорий в завершённых заказах нет.</td></tr>
                <?php else: foreach ($topCategories as $category): ?>
                    <tr>
                        <td><?= html_escape($category['category_title'] ?? '') ?></td>
                        <td><?= $number($category['order_count'] ?? 0) ?></td>
                        <td><?= $number($category['sold_quantity'] ?? 0) ?></td>
                        <td><?= $money($category['product_revenue'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="analytics-sales-card">
        <header class="analytics-sales-card__heading"><i class="icon-graph" aria-hidden="true"></i><h2>Продажи по периодам</h2></header>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead><tr><th><?= $group_by === 'month' ? 'Месяц' : 'Дата' ?></th><th>Завершённые заказы</th><th>Проданные товары</th><th>Выручка по товарам</th><th>Выручка заказов</th></tr></thead>
                <tbody>
                <?php if (empty($periodSales)): ?>
                    <tr><td colspan="5" class="text-muted">За выбранный период завершённых заказов нет.</td></tr>
                <?php else: foreach ($periodSales as $period): ?>
                    <tr>
                        <td><?= html_escape($periodLabel($period['period_key'] ?? '', $group_by)) ?></td>
                        <td><?= $number($period['finished_orders'] ?? 0) ?></td>
                        <td><?= $number($period['sold_quantity'] ?? 0) ?></td>
                        <td><?= $money($period['product_revenue'] ?? 0) ?></td>
                        <td><?= $money($period['order_revenue'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
