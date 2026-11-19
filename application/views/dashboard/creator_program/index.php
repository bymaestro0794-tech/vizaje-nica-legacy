<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$rows = isset($applications) && is_array($applications) ? $applications : array();
$labels = array('pending' => 'На рассмотрении', 'approved' => 'Одобрена', 'rejected' => 'Отклонена');
?>
<div class="page-bar"><ul class="page-breadcrumb"><li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li><li><span>Партнёрская программа</span></li></ul></div>
<h1 class="page-title">Заявки креаторов</h1>
<form method="get" class="form-inline" style="margin:16px 0 20px"><label for="creator-status">Статус</label><select id="creator-status" class="form-control" name="status"><option value="">Все</option><?php foreach ($labels as $key => $label) { ?><option value="<?= $key ?>" <?= $selected_status === $key ? 'selected' : '' ?>><?= $label ?></option><?php } ?></select><button class="btn blue" type="submit">Показать</button></form>
<div class="portlet light"><div class="portlet-title"><div class="caption"><i class="fa fa-users"></i> Заявки (<?= count($rows) ?>)</div></div><div class="portlet-body table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>Получена</th><th>Клиент</th><th>Email</th><th>Телефон</th><th>Статус заявки</th><th>Доступ креатора</th><th></th></tr></thead><tbody>
<?php if (!$rows) { ?><tr><td colspan="7" class="text-center">Заявок нет.</td></tr><?php } ?>
<?php foreach ($rows as $row) { ?><tr><td><?= html_escape($row['created_at']) ?></td><td>#<?= (int) $row['client_id'] ?> · <?= html_escape($row['full_name']) ?></td><td><?= html_escape($row['email']) ?></td><td><?= html_escape($row['phone']) ?></td><td><span class="label label-<?= $row['status'] === 'pending' ? 'warning' : ($row['status'] === 'approved' ? 'success' : 'danger') ?>"><?= $labels[$row['status']] ?? html_escape($row['status']) ?></span></td><td><?= !empty($row['is_creator']) ? 'Включён' : 'Выключен' ?></td><td><a class="btn btn-sm blue" href="/<?= ADM_CONTROLLER ?>/creator_program/view/<?= (int) $row['id'] ?>">Открыть</a></td></tr><?php } ?>
</tbody></table></div></div>
