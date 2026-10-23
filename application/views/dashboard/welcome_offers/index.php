<?php
defined('BASEPATH') or exit('No direct script access allowed');

$escape = static function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$now = time();
$summary = array('active' => 0, 'used' => 0, 'expired' => 0);
foreach ($objects as $row) {
	if (!empty($row['used_at'])) {
		$summary['used']++;
	} elseif (!empty($row['welcome_offer_expires_at']) && strtotime($row['welcome_offer_expires_at']) > $now) {
		$summary['active']++;
	} else {
		$summary['expired']++;
	}
}
?>

<div class="page-bar">
	<ul class="page-breadcrumb">
		<li><i class="fa fa-home"></i> <a href="/<?= ADM_CONTROLLER ?>/menu/"><?= lang('Home') ?></a> <i class="fa fa-circle"></i></li>
		<li><a href="/<?= ADM_CONTROLLER ?>/promocodes/">Промокоды</a> <i class="fa fa-circle"></i></li>
		<li><span>Скидка после регистрации −18%</span></li>
	</ul>
</div>

<h1 class="page-title">Скидка после регистрации −18%</h1>

<?php if (!empty($migration_required)) : ?>
	<div class="alert alert-warning">Не найдены поля приветственной скидки. Выполните <code>database/patches/2026-10-12-add-welcome-offer.sql</code>.</div>
<?php else : ?>
	<div class="alert alert-info">
		<i class="fa fa-info-circle"></i>
		Здесь показаны только серверные факты: выдача WELCOME18 при регистрации и её фактическое использование в заказе. Удаление скидки из корзины не считается использованием.
	</div>

	<form class="form-inline margin-bottom-20" method="get" action="/<?= ADM_CONTROLLER ?>/welcome_offers/">
		<div class="form-group">
			<label class="sr-only" for="welcome-search">Поиск</label>
			<input id="welcome-search" class="form-control" type="search" name="q" value="<?= $escape($filters['q'] ?? '') ?>" placeholder="Клиент, телефон, email или заказ">
		</div>
		<div class="form-group">
			<label class="sr-only" for="welcome-state">Статус</label>
			<select id="welcome-state" class="form-control" name="state">
				<option value="">Все статусы</option>
				<option value="active" <?= ($filters['state'] ?? '') === 'active' ? 'selected' : '' ?>>Активна</option>
				<option value="used" <?= ($filters['state'] ?? '') === 'used' ? 'selected' : '' ?>>Использована</option>
				<option value="expired" <?= ($filters['state'] ?? '') === 'expired' ? 'selected' : '' ?>>Истекла</option>
			</select>
		</div>
		<button class="btn blue" type="submit"><i class="fa fa-search"></i> Найти</button>
		<a class="btn default" href="/<?= ADM_CONTROLLER ?>/welcome_offers/">Сбросить</a>
	</form>

	<div class="row margin-bottom-20">
		<div class="col-md-4"><div class="note note-info"><strong><?= (int) $summary['active'] ?></strong> активных</div></div>
		<div class="col-md-4"><div class="note note-success"><strong><?= (int) $summary['used'] ?></strong> использованных</div></div>
		<div class="col-md-4"><div class="note note-warning"><strong><?= (int) $summary['expired'] ?></strong> истекших</div></div>
	</div>

	<div class="table-scrollable">
		<table class="table table-striped table-bordered table-hover">
			<thead><tr><th>Клиент</th><th>Выдана</th><th>Действует до</th><th>Статус</th><th>Использована</th><th>Заказ</th></tr></thead>
			<tbody>
				<?php if (empty($objects)) : ?>
					<tr><td colspan="6" class="text-center text-muted">Записей не найдено.</td></tr>
				<?php endif; ?>
				<?php foreach ($objects as $row) : ?>
					<?php
					$used = !empty($row['used_at']);
					$active = !$used && !empty($row['welcome_offer_expires_at']) && strtotime($row['welcome_offer_expires_at']) > $now;
					$state = $used ? 'Использована' : ($active ? 'Активна' : 'Истекла');
					$stateClass = $used ? 'label-success' : ($active ? 'label-info' : 'label-warning');
					$clientName = trim((string) ($row['client_name'] ?? '') . ' ' . (string) ($row['client_surname'] ?? ''));
					?>
					<tr>
						<td><a href="/<?= ADM_CONTROLLER ?>/clients/item/<?= (int) $row['client_id'] ?>">#<?= (int) $row['client_id'] ?></a><br><?= $escape($clientName !== '' ? $clientName : 'Без имени') ?><br><small><?= $escape($row['client_email'] ?? '') ?><br><?= $escape($row['client_phone'] ?? '') ?></small></td>
						<td><?= $escape($row['welcome_offer_started_at'] ?? '—') ?></td>
						<td><?= $escape($row['welcome_offer_expires_at'] ?? '—') ?></td>
						<td><span class="label <?= $stateClass ?>"><?= $state ?></span></td>
						<td><?= $escape($row['used_at'] ?? '—') ?></td>
						<td><?php if (!empty($row['order_id'])) : ?><a href="/<?= ADM_CONTROLLER ?>/orders/item/<?= (int) $row['order_id'] ?>">#<?= (int) $row['order_id'] ?></a><br><small><?= $escape($row['order_status'] ?? '') ?><?= (int) ($row['order_payment'] ?? 0) === 2 ? ((int) ($row['order_pay_success'] ?? 0) === 1 ? ', Paynet оплачен' : ', Paynet ожидает') : '' ?></small><?php else : ?>—<?php endif; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<p class="help-block">Для удобства выводятся последние 500 записей.</p>
<?php endif; ?>
