<?php
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$today = date('Y-m-d');
?>
<div class="page-bar"><ul class="page-breadcrumb"><li><i class="fa fa-home"></i> <a href="/<?= ADM_CONTROLLER ?>/menu/">Главная</a> <i class="fa fa-circle"></i></li><li><span><?= $escape($title) ?></span></li></ul></div>
<h1 class="page-title"><?= $escape($title) ?></h1>
<?php if (isset($_SESSION['success'])) : ?><div class="alert alert-success"><?= $escape($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])) : ?><div class="alert alert-danger"><?php foreach ((array) $_SESSION['error'] as $error) echo $escape($error) . '<br>'; unset($_SESSION['error']); ?></div><?php endif; ?>
<?php if (!empty($migration_required)) : ?><div class="alert alert-warning">Сначала примените <code>database/patches/2026-10-23-create-gift-promotions.sql</code>.</div><?php else : ?>
<div class="row"><div class="portlet light"><div class="portlet-body"><a class="btn green" href="<?= $escape($e_path . '0') ?>">Добавить подарочную акцию</a><p class="help-block">Пример: купить 2 товара выбранных брендов → получить один подарок за 1 MDL.</p></div></div></div>
<div class="row"><div class="portlet light"><div class="portlet-body"><form action="<?= $escape($o_path) ?>" method="post"><div class="table-scrollable"><table class="table table-bordered table-striped"><thead><tr><th>Порядок</th><th>Акция</th><th>Условие</th><th>Подарок</th><th>Период / остаток</th><th>Канал</th><th>Статус</th><th></th></tr></thead><tbody>
<?php foreach ((array) $objects as $item) : ?><?php
$dateFrom = !empty($item->date_from) && $item->date_from !== '0000-00-00' ? (string) $item->date_from : '';
$dateTo = !empty($item->date_to) && $item->date_to !== '0000-00-00' ? (string) $item->date_to : '';
$remainingStock = max(0, (int) ($item->remaining_stock ?? 0));
$endedByTime = $dateTo !== '' && $dateTo < $today;
$giftUnavailable = empty($item->gift_is_enabled) || empty($item->gift_show_on_site);
$endedByStock = !$giftUnavailable && $remainingStock <= 0;
$startsLater = $dateFrom !== '' && $dateFrom > $today;
if ($endedByTime) {
    $statusLabel = 'Завершена: срок истёк';
    $statusClass = 'label-danger';
} elseif ($giftUnavailable) {
    $statusLabel = 'Подарок выключен';
    $statusClass = 'label-default';
} elseif ($endedByStock) {
    $statusLabel = 'Завершена: подарок закончился';
    $statusClass = 'label-danger';
} elseif ($startsLater) {
    $statusLabel = 'Запланирована';
    $statusClass = 'label-warning';
} elseif (empty($item->is_enabled)) {
    $statusLabel = 'Выключена';
    $statusClass = 'label-default';
} else {
    $statusLabel = 'Активна';
    $statusClass = 'label-success';
}
?><tr><td><input class="form-control" type="number" name="so[<?= (int) $item->id ?>]" value="<?= (int) $item->sorder ?>" style="width:80px"></td><td><span style="display:inline-block;background:#ff1493;color:#fff;font-size:11px;font-weight:700;padding:3px 6px;margin:0 0 5px 0;">ПОДАРОК АКЦИИ</span><br><a href="<?= $escape($e_path . (int) $item->id) ?>"><strong><?= $escape($item->titleRU) ?></strong></a><?php if (!empty($item->offer_title_ru)) : ?><br><small>Страница акции: <?= $escape($item->offer_title_ru) ?></small><?php endif; ?></td><td><?= (int) $item->condition_qty ?> товар(а) выбранных брендов</td><td><?= $escape($item->gift_title_ru ?: $item->gift_title_ro) ?></td><td><?= $dateFrom !== '' ? $escape($dateFrom) : 'Без начала' ?> — <?= $dateTo !== '' ? $escape($dateTo) : 'Без окончания' ?><br><small>Остаток подарка: <?= $remainingStock ?> шт.</small></td><td><?= (int) $item->is_online === 0 ? 'OFFLINE' : ((int) $item->is_online === 2 ? 'ONLINE + OFFLINE' : 'ONLINE') ?></td><td><span class="label <?= $statusClass ?>"><?= $escape($statusLabel) ?></span><br><?= !empty($item->show_on_site) ? '<span class="label label-info">На сайте</span>' : '<span class="label label-default">Скрыта</span>' ?></td><td><a class="btn btn-xs green-stripe" href="<?= $escape($e_path . (int) $item->id) ?>">Изменить</a> <a class="btn btn-xs red-stripe mine_delete_row" href="<?= $escape($del_path . (int) $item->id) ?>">Удалить</a></td></tr><?php endforeach; ?>
</tbody></table></div><button class="btn green" type="submit">Сохранить порядок</button></form></div></div></div>
<?php endif; ?>
