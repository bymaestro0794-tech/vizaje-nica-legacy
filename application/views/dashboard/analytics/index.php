<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="analytics-dashboard analytics-dashboard-sales">
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li>
            <li><span>Аналитика продаж</span></li>
        </ul>
    </div>

    <h1 class="page-title">Аналитика продаж <small>завершённые заказы</small></h1>

    <section class="analytics-period-toolbar" aria-label="Период аналитики">
        <div>
            <span class="analytics-period-toolbar__eyebrow">Период</span>
            <strong data-analytics-range-label><?= html_escape($range_label) ?></strong>
        </div>
        <form class="analytics-period-buttons" method="get" action="/<?= ADM_CONTROLLER ?>/analytics" aria-label="Выбрать период">
            <?php
            $periodOptions = array(
                'current_month' => 'Этот месяц',
                'previous_month' => 'Предыдущий месяц',
                'last_3_months' => 'Последние 3 месяца',
                'year' => 'Год',
            );
            foreach ($periodOptions as $periodKey => $periodTitle):
            ?>
                <button
                    type="submit"
                    class="analytics-period-button<?= $period === $periodKey ? ' is-active' : '' ?>"
                    name="period"
                    value="<?= html_escape($periodKey) ?>"
                    <?= $period === $periodKey ? 'aria-current="true"' : '' ?>
                ><?= html_escape($periodTitle) ?></button>
            <?php endforeach; ?>
        </form>
    </section>

    <?php $this->load->view('dashboard/analytics/_sales_data', array(
        'analytics' => $analytics,
        'group_by' => $group_by,
    )); ?>
</div>

<script>
(function () {
    function restorePageScroll() {
        if (!document.querySelector('.analytics-filter-form.is-filter-open')) {
            document.body.classList.remove('analytics-filter-scroll-locked');
        }
    }

    restorePageScroll();
    window.addEventListener('pageshow', restorePageScroll);
}());
</script>
