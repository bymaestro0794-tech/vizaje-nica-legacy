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
        <div class="analytics-period-buttons" role="group" aria-label="Выбрать период">
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
                    type="button"
                    class="analytics-period-button<?= $period === $periodKey ? ' is-active' : '' ?>"
                    data-analytics-period="<?= html_escape($periodKey) ?>"
                    aria-pressed="<?= $period === $periodKey ? 'true' : 'false' ?>"
                ><?= html_escape($periodTitle) ?></button>
            <?php endforeach; ?>
        </div>
    </section>

    <div
        class="analytics-sales-lazy"
        data-analytics-lazy
        data-url="<?= html_escape($analytics_data_url) ?>"
        data-period="<?= html_escape($period) ?>"
        aria-live="polite"
    >
        <div class="analytics-sales-loading" data-analytics-loading>
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="analytics-sales-loading__card">
                    <span></span><span></span><span></span><span></span>
                </div>
            <?php endfor; ?>
        </div>
        <div class="analytics-sales-lazy__error" data-analytics-error hidden>
            <span>Не удалось загрузить аналитику.</span>
            <button type="button" data-analytics-retry>Повторить</button>
        </div>
        <div data-analytics-content></div>
    </div>
</div>

<script src="/app/js/dashboard/analytics-lazy.js?v=<?= filemtime(FCPATH . 'app/js/dashboard/analytics-lazy.js') ?>" defer></script>
