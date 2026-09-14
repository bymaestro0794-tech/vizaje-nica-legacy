<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

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
</ul>

<div class="note note-info">
    Экран воронки подготовлен отдельно от продаж. Реальные значения появятся после подключения собственных событий с проверкой согласия на аналитику.
</div>

<div class="portlet light bordered">
    <div class="portlet-title"><div class="caption"><i class="icon-graph"></i> Шаги воронки</div></div>
    <div class="portlet-body">
        <table class="table table-striped">
            <thead>
                <tr><th>Шаг</th><th>Событие</th><th>Статус</th></tr>
            </thead>
            <tbody>
                <tr><td>1. Начало сессии</td><td><code>session_start</code></td><td>Ожидает события</td></tr>
                <tr><td>2. Просмотр каталога</td><td><code>catalog_view</code></td><td>Ожидает события</td></tr>
                <tr><td>3. Просмотр товара</td><td><code>view_item</code></td><td>Ожидает события</td></tr>
                <tr><td>4. Добавление в корзину</td><td><code>add_to_cart</code></td><td>Ожидает события</td></tr>
                <tr><td>5. Открытие корзины</td><td><code>view_cart</code></td><td>Ожидает события</td></tr>
                <tr><td>6. Начало оформления</td><td><code>begin_checkout</code></td><td>Ожидает события</td></tr>
                <tr><td>7. Заказ создан</td><td><code>order_submitted</code></td><td>Ожидает события</td></tr>
                <tr><td>8. Покупка подтверждена</td><td><code>purchase</code></td><td>Ожидает события</td></tr>
            </tbody>
        </table>
    </div>
</div>
