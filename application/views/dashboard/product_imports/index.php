<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$rows = isset($import_changes) && is_array($import_changes) ? $import_changes : array();
$pageUrl = static function ($page) use ($filter_action, $filter_status, $filter_search, $filter_brand_id) {
	$params = array('status' => $filter_status);
	if ($filter_action !== '') $params['action'] = $filter_action;
	if ($filter_brand_id > 0) $params['brand_id'] = $filter_brand_id;
	if ($filter_search !== '') $params['q'] = $filter_search;
	if ((int) $page > 1) $params['page'] = (int) $page;
	return '/' . ADM_CONTROLLER . '/product_imports?' . http_build_query($params);
};
$statusUrl = static function ($status) use ($filter_action, $filter_search, $filter_brand_id) {
	$params = array('status' => $status);
	if ($filter_action !== '') $params['action'] = $filter_action;
	if ($filter_brand_id > 0) $params['brand_id'] = $filter_brand_id;
	if ($filter_search !== '') $params['q'] = $filter_search;
	return '/' . ADM_CONTROLLER . '/product_imports?' . http_build_query($params);
};
$formatValue = static function ($value) {
	if (is_array($value)) return implode(', ', array_map('strval', $value));
	if ($value === null || $value === '') return '—';
	return (string) $value;
};
$returnParams = array('status' => $filter_status);
if ($filter_action !== '') $returnParams['action'] = $filter_action;
if ($filter_brand_id > 0) $returnParams['brand_id'] = $filter_brand_id;
if ($filter_search !== '') $returnParams['q'] = $filter_search;
if ((int) $current_page > 1) $returnParams['page'] = (int) $current_page;
?>

<div class="page-bar">
	<ul class="page-breadcrumb">
		<li><a href="/<?= ADM_CONTROLLER ?>/menu/">Главная</a><i class="fa fa-circle"></i></li>
		<li><a href="/<?= ADM_CONTROLLER ?>/products/">Товары</a><i class="fa fa-circle"></i></li>
		<li><span><?= html_escape($title) ?></span></li>
	</ul>
</div>

<h1 class="page-title"><?= html_escape($title) ?></h1>

<?php if (!empty($migration_required)): ?>
	<div class="alert alert-warning">Сначала выполните SQL-патч <code>database/patches/2026-09-30-add-product-import-changes.sql</code>.</div>
<?php elseif (!empty($status_column_missing)): ?>
	<div class="alert alert-warning">Для очереди обработки выполните SQL-патч <code>database/patches/2026-10-01-track-product-import-review.sql</code>.</div>
<?php else: ?>
	<ul class="nav nav-tabs" style="margin-bottom:18px;">
		<li class="<?= $filter_status === 'new' ? 'active' : '' ?>"><a href="<?= html_escape($statusUrl('new')) ?>">Новые изменения</a></li>
		<li class="<?= $filter_status === 'edited' ? 'active' : '' ?>"><a href="<?= html_escape($statusUrl('edited')) ?>">Обработанные</a></li>
	</ul>

	<form method="get" action="/<?= ADM_CONTROLLER ?>/product_imports" class="form-inline" style="margin-bottom:18px;">
		<input type="hidden" name="status" value="<?= html_escape($filter_status) ?>">
		<div class="form-group">
			<label for="import-brand">Бренд</label>
			<select id="import-brand" name="brand_id" class="form-control">
				<option value="0">Все бренды</option>
				<?php foreach ((array) $brands as $brand): ?>
					<option value="<?= (int) $brand->id ?>" <?= (int) $filter_brand_id === (int) $brand->id ? 'selected' : '' ?>><?= html_escape($brand->title) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="form-group" style="margin-left:8px;">
			<label for="import-action">Тип</label>
			<select id="import-action" name="action" class="form-control">
				<option value="">Все изменения</option>
				<option value="added" <?= $filter_action === 'added' ? 'selected' : '' ?>>Добавленные товары</option>
				<option value="updated" <?= $filter_action === 'updated' ? 'selected' : '' ?>>Изменённые товары</option>
			</select>
		</div>
		<div class="form-group" style="margin-left:8px;">
			<label for="import-search">Товар / артикул</label>
			<input id="import-search" class="form-control" type="search" name="q" value="<?= html_escape($filter_search) ?>" placeholder="Название, SKU или GUID">
		</div>
		<button type="submit" class="btn blue" style="margin-left:8px;">Показать</button>
		<a class="btn btn-default" href="<?= html_escape($statusUrl($filter_status)) ?>" style="margin-left:4px;">Сбросить</a>
	</form>

	<p class="text-muted">После сохранения товара запись попадёт во вкладку «Обработанные». Если следующий импорт добавит новинки, прежние необработанные записи о добавленных товарах очистятся автоматически; сами товары и обработанная история сохраняются.</p>
	<div class="portlet light bordered">
		<div class="portlet-title">
			<div class="caption"><i class="fa fa-refresh"></i> <?= $filter_status === 'edited' ? 'Обработанные изменения' : 'Новые записи импорта' ?> <small>(<?= (int) $total_rows ?>)</small></div>
			<?php if ($filter_status === 'edited' && (int) $total_rows > 0): ?>
				<div class="actions">
					<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#clearProcessedChangesModal">
						<i class="fa fa-trash"></i> Удалить все обработанные
					</button>
				</div>
			<?php endif; ?>
		</div>
		<div class="portlet-body table-responsive">
			<table class="table table-striped table-hover">
				<thead><tr><th>Дата</th><th>Тип</th><th>Товар</th><th>Что изменилось</th><th>Действие</th></tr></thead>
				<tbody>
				<?php if (empty($rows)): ?>
					<tr><td colspan="5" class="text-muted"><?= $filter_status === 'edited' ? 'Обработанных записей пока нет.' : 'Новых изменений нет. После импорта они появятся здесь.' ?></td></tr>
				<?php else: foreach ($rows as $row): ?>
					<tr>
						<td style="white-space:nowrap;"><?= html_escape($row['created_at']) ?></td>
						<td><span class="label label-<?= $row['action'] === 'added' ? 'success' : 'info' ?>"><?= $row['action'] === 'added' ? 'Добавлен' : 'Изменён' ?></span></td>
						<td style="min-width:220px;">
							<strong><?= html_escape($row['titleRU'] ?: ($row['titleRO'] ?: 'Товар удалён')) ?></strong><br>
							<small><?= html_escape($row['brand_title'] ?: 'Без бренда') ?> · Артикул: <?= html_escape($row['SKU'] ?: '—') ?> · GUID: <?= html_escape($row['product_guid']) ?></small>
						</td>
						<td style="min-width:340px;">
							<?php foreach ($row['changed_fields'] as $field): ?>
								<div style="margin-bottom:5px;"><strong><?= html_escape($field['label'] ?? 'Поле') ?>:</strong> <?= html_escape($formatValue($field['before'] ?? null)) ?> <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?= html_escape($formatValue($field['after'] ?? null)) ?></div>
							<?php endforeach; ?>
						</td>
						<td style="min-width:155px;">
							<?php if (!empty($row['product_id'])): ?>
								<?php $editParams = array('import_change_id' => (int) $row['id']); foreach ($returnParams as $key => $value) $editParams['import_' . $key] = $value; ?>
								<a class="btn btn-sm blue" href="/<?= ADM_CONTROLLER ?>/products/item/<?= (int) $row['product_id'] ?>/?<?= html_escape(http_build_query($editParams)) ?>"><i class="fa fa-pencil"></i> Редактировать</a>
							<?php else: ?>—<?php endif; ?>
							<form method="post" action="/<?= ADM_CONTROLLER ?>/product_imports/remove/<?= (int) $row['id'] ?>" style="margin-top:6px;" onsubmit="return confirm('Удалить эту запись из журнала импорта?');">
								<?php foreach ($returnParams as $key => $value): ?><input type="hidden" name="<?= html_escape($key) ?>" value="<?= html_escape($value) ?>"><?php endforeach; ?>
								<button type="submit" class="btn btn-xs btn-default"><i class="fa fa-trash"></i> Удалить запись</button>
							</form>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
			<?php if ($total_pages > 1): ?><ul class="pagination">
				<?php for ($number = 1; $number <= $total_pages; $number++): ?><li class="<?= $number === (int) $current_page ? 'active' : '' ?>"><a href="<?= html_escape($pageUrl($number)) ?>"><?= (int) $number ?></a></li><?php endfor; ?>
			</ul><?php endif; ?>
		</div>
	</div>
	<?php if ($filter_status === 'edited' && (int) $total_rows > 0): ?>
		<div class="modal fade" id="clearProcessedChangesModal" tabindex="-1" role="dialog" aria-labelledby="clearProcessedChangesTitle">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="clearProcessedChangesTitle">Удалить обработанные записи?</h4>
					</div>
					<div class="modal-body">
						Будут удалены <strong><?= (int) $total_rows ?></strong> записей из журнала проверок<?= ($filter_action !== '' || $filter_brand_id > 0 || $filter_search !== '') ? ' с учётом выбранных фильтров' : '' ?>.
						<strong>Товары и их данные в каталоге не удаляются.</strong> Удалённые записи журнала восстановить нельзя.
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
						<form method="post" action="/<?= ADM_CONTROLLER ?>/product_imports/clear_processed" style="display:inline;">
							<?php if ($filter_action !== ''): ?><input type="hidden" name="action" value="<?= html_escape($filter_action) ?>"><?php endif; ?>
							<?php if ($filter_brand_id > 0): ?><input type="hidden" name="brand_id" value="<?= (int) $filter_brand_id ?>"><?php endif; ?>
							<?php if ($filter_search !== ''): ?><input type="hidden" name="q" value="<?= html_escape($filter_search) ?>"><?php endif; ?>
							<button type="submit" class="btn btn-danger">Да, удалить записи</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
