<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$rows = isset($creators) && is_array($creators) ? $creators : array();
$states = array('all' => 'Все', 'active' => 'Активные аккаунты', 'inactive' => 'Неактивные аккаунты');
?>
<div class="page-bar"><ul class="page-breadcrumb"><li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li><li><span>Партнёрская программа</span></li><li><span>Креаторы</span></li></ul></div>
<h1 class="page-title">Креаторы</h1>
<?php if (empty($creator_flag_supported)) { ?>
    <div class="alert alert-warning">Сначала примените миграцию с полем clients.is_creator.</div>
<?php } else { ?>
    <form method="get" class="form-inline" style="margin:16px 0 20px">
        <label for="creator-search">Клиент</label>
        <input id="creator-search" class="form-control" type="search" name="search" value="<?= html_escape($search) ?>" placeholder="ID, имя, email или телефон">
        <label for="creator-state">Аккаунт</label>
        <select id="creator-state" class="form-control" name="state">
            <?php foreach ($states as $key => $label) { ?><option value="<?= $key ?>" <?= $selected_state === $key ? 'selected' : '' ?>><?= $label ?></option><?php } ?>
        </select>
        <button class="btn blue" type="submit">Найти</button>
    </form>
    <div class="portlet light">
        <div class="portlet-title"><div class="caption"><i class="fa fa-users"></i> Креаторы (<?= count($rows) ?><?= count($rows) >= 200 ? '+' : '' ?>)</div></div>
        <div class="portlet-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead><tr><th>Клиент</th><th>Email</th><th>Телефон</th><th>Аккаунт</th><th>Режим B2C</th><th>Виджет</th><th></th></tr></thead>
                <tbody>
                <?php if (!$rows) { ?><tr><td colspan="7" class="text-center">Креаторы не найдены.</td></tr><?php } ?>
                <?php foreach ($rows as $row) { ?>
                    <tr>
                        <td>#<?= (int) $row['id'] ?> · <?= html_escape(trim((string) $row['name'] . ' ' . (string) ($row['surname'] ?? ''))) ?></td>
                        <td><?= html_escape((string) ($row['email'] ?? '')) ?></td>
                        <td><?= html_escape((string) ($row['phone'] ?? '')) ?></td>
                        <td><span class="label label-<?= (int) $row['active'] === 1 ? 'success' : 'default' ?>"><?= (int) $row['active'] === 1 ? 'Активен' : 'Неактивен' ?></span></td>
                        <td><?= !empty($row['creator_b2c_mode']) ? 'Включён' : 'Выключен' ?></td>
                        <td><?= !array_key_exists('creator_widget_enabled', $row) ? '—' : (!empty($row['creator_widget_enabled']) ? 'Включён' : 'Выключен') ?></td>
                        <td><a class="btn btn-sm blue" href="/<?= ADM_CONTROLLER ?>/clients/item/<?= (int) $row['id'] ?>">Карточка клиента</a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php if (count($rows) >= 200) { ?><p class="text-muted">Показаны первые 200 результатов. Уточните поиск для просмотра остальных.</p><?php } ?>
        </div>
    </div>
<?php } ?>
