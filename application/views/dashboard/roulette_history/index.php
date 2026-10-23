<?php
defined('BASEPATH') or exit('No direct script access allowed');

$escape = static function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$labels = array(
	'active' => array('Активен', 'label-info'),
	'reserved' => array('Резерв оплаты', 'label-warning'),
	'redeemed' => array('Использован', 'label-success'),
	'expired' => array('Истёк', 'label-default'),
	'cancelled' => array('Отменён для теста', 'label-danger'),
);
$summary = array('active' => 0, 'reserved' => 0, 'redeemed' => 0, 'expired' => 0);
foreach ($objects as $row) {
	$status = (string) ($row['status'] ?? '');
	if (isset($summary[$status])) {
		$summary[$status]++;
	}
}
?>

<div class="page-bar">
	<ul class="page-breadcrumb">
		<li><i class="fa fa-home"></i> <a href="/<?= ADM_CONTROLLER ?>/menu/"><?= lang('Home') ?></a> <i class="fa fa-circle"></i></li>
		<li><a href="/<?= ADM_CONTROLLER ?>/promocodes/">Промокоды</a> <i class="fa fa-circle"></i></li>
		<li><span>История рулетки</span></li>
	</ul>
</div>

<h1 class="page-title">История рулетки</h1>

<?php if (!empty($migration_required)) : ?>
	<div class="alert alert-warning">Не найдены таблицы истории рулетки. Выполните SQL-патчи рулетки из <code>database/patches</code>.</div>
<?php else : ?>
	<div class="alert alert-info"><i class="fa fa-info-circle"></i> Здесь фиксируются все серверные попытки. Резерв — это 10-минутное ожидание оплаты Paynet; использованным приз становится только после подтверждения оплаты или при заказе с оплатой при получении.</div>
	<?php if (!empty($allow_testing_reset)) : ?>
		<div class="alert alert-warning"><i class="fa fa-flask"></i> На локальном DEV можно отменить только активный приз без заказа. Попытка останется в журнале, а для клиента сразу снимется интервал ожидания.</div>
	<?php endif; ?>

	<form class="form-inline margin-bottom-20" method="get" action="/<?= ADM_CONTROLLER ?>/roulette_history/">
		<div class="form-group">
			<label class="sr-only" for="roulette-history-search">Поиск</label>
			<input id="roulette-history-search" class="form-control" type="search" name="q" value="<?= $escape($filters['q'] ?? '') ?>" placeholder="Попытка, клиент, телефон, email или заказ">
		</div>
		<div class="form-group">
			<label class="sr-only" for="roulette-history-status">Статус</label>
			<select id="roulette-history-status" class="form-control" name="status">
				<option value="">Все статусы</option>
				<option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активен</option>
				<option value="reserved" <?= ($filters['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>Резерв оплаты</option>
				<option value="redeemed" <?= ($filters['status'] ?? '') === 'redeemed' ? 'selected' : '' ?>>Использован</option>
				<option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Истёк</option>
				<option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Отменён для теста</option>
			</select>
		</div>
		<button class="btn blue" type="submit"><i class="fa fa-search"></i> Найти</button>
		<a class="btn default" href="/<?= ADM_CONTROLLER ?>/roulette_history/">Сбросить</a>
	</form>

	<div class="row margin-bottom-20">
		<div class="col-md-3"><div class="note note-info"><strong><?= (int) $summary['active'] ?></strong> активных</div></div>
		<div class="col-md-3"><div class="note note-warning"><strong><?= (int) $summary['reserved'] ?></strong> в резерве</div></div>
		<div class="col-md-3"><div class="note note-success"><strong><?= (int) $summary['redeemed'] ?></strong> использованных</div></div>
		<div class="col-md-3"><div class="note note-default"><strong><?= (int) $summary['expired'] ?></strong> истёкших</div></div>
	</div>

	<div class="table-scrollable">
		<table class="table table-striped table-bordered table-hover">
			<thead><tr><th>Попытка</th><th>Клиент</th><th>Приз</th><th>Выигран</th><th>Срок</th><th>Статус</th><th>Заказ и оплата</th><th>Действие</th></tr></thead>
			<tbody>
				<?php if (empty($objects)) : ?>
					<tr><td colspan="8" class="text-center text-muted">Записей не найдено.</td></tr>
				<?php endif; ?>
				<?php foreach ($objects as $row) : ?>
					<?php
					$status = (string) ($row['status'] ?? '');
					$label = $labels[$status] ?? array($status ?: 'Неизвестно', 'label-default');
					$clientName = trim((string) ($row['client_name'] ?? '') . ' ' . (string) ($row['client_surname'] ?? ''));
					?>
					<tr>
						<td>#<?= (int) $row['id'] ?></td>
						<td><a href="/<?= ADM_CONTROLLER ?>/clients/item/<?= (int) $row['client_id'] ?>">#<?= (int) $row['client_id'] ?></a><br><?= $escape($clientName !== '' ? $clientName : 'Без имени') ?><br><small><?= $escape($row['client_email'] ?? '') ?><br><?= $escape($row['client_phone'] ?? '') ?></small></td>
						<td><?= $escape($row['titleRU_snapshot'] ?? '') ?><br><strong>−<?= $escape($row['reward_value_snapshot'] ?? '') ?>%</strong><br><small><code><?= $escape($row['prize_code_snapshot'] ?? '') ?></code></small></td>
						<td><?= $escape($row['spun_at'] ?? '—') ?></td>
						<td><?= $escape($row['expires_at'] ?? '—') ?></td>
						<td><span class="label <?= $label[1] ?>"><?= $label[0] ?></span><?php if (!empty($row['used_at'])) : ?><br><small><?= $escape($row['used_at']) ?></small><?php endif; ?></td>
						<td><?php if (!empty($row['order_id'])) : ?><a href="/<?= ADM_CONTROLLER ?>/orders/item/<?= (int) $row['order_id'] ?>">#<?= (int) $row['order_id'] ?></a><br><small><?= $escape($row['order_status'] ?? '') ?><?= (int) ($row['order_payment'] ?? 0) === 2 ? ((int) ($row['order_pay_success'] ?? 0) === 1 ? ', Paynet оплачен' : ', Paynet ожидает') : '' ?></small><?php if (!empty($row['reservation_status'])) : ?><br><small>Резерв: <?= $escape($row['reservation_status']) ?><?= !empty($row['reserved_until']) ? ' до ' . $escape($row['reserved_until']) : '' ?></small><?php endif; ?><?php else : ?>—<?php endif; ?></td>
						<td><?php if (!empty($allow_testing_reset) && $status === 'active' && empty($row['order_id'])) : ?><form method="post" action="/<?= ADM_CONTROLLER ?>/roulette_history/cancel/<?= (int) $row['id'] ?>" onsubmit="return confirm('Отменить этот активный приз и сразу разрешить клиенту новую тестовую попытку?');"><input type="hidden" name="action_token" value="<?= $escape($action_token ?? '') ?>"><button class="btn btn-xs red" type="submit"><i class="fa fa-times"></i> Отменить приз</button></form><?php else : ?>—<?php endif; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<p class="help-block">Для удобства выводятся последние 500 попыток.</p>
<?php endif; ?>
