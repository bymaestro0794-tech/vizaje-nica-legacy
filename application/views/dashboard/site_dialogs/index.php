<?php
defined('BASEPATH') or exit('No direct script access allowed');
$escape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
?>
<div class="page-bar"><ul class="page-breadcrumb"><li><i class="fa fa-home"></i> <a href="/<?= ADM_CONTROLLER ?>/menu/"><?= lang('Home') ?></a> <i class="fa fa-circle"></i></li><li><span>Диалоги сайта</span></li></ul></div>
<h1 class="page-title">Диалоги сайта</h1>
<?php if (isset($_SESSION['success'])) : ?><div class="alert alert-success"><?= $escape($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])) : ?><div class="alert alert-danger"><?php foreach ((array) $_SESSION['error'] as $error) : ?><?= $escape($error) ?><br><?php endforeach; unset($_SESSION['error']); ?></div><?php endif; ?>
<?php if (!empty($migration_required)) : ?>
	<div class="alert alert-warning">Чтобы включить диалоги, вручную примените SQL-патч <code>database/patches/2026-11-13-create-site-dialogs.sql</code>. До этого сайт продолжает работать без новых диалогов.</div>
<?php else : ?>
	<p class="help-block">Настраивайте информационные и рекламные окна отдельно от WELCOME18. Между автоматическими окнами действует общий интервал 1 час; закрытый диалог не появится этому посетителю 4 дня.</p>
	<p><a class="btn blue" href="/<?= ADM_CONTROLLER ?>/site_dialogs/item/0"><i class="fa fa-plus"></i> Создать диалог</a></p>
	<div class="table-scrollable"><table class="table table-striped table-bordered table-hover"><thead><tr><th>Название RU</th><th>Аудитория</th><th>Период</th><th>Задержка</th><th>Приоритет</th><th>Статус</th><th>Действия</th></tr></thead><tbody>
	<?php if (empty($objects)) : ?><tr><td colspan="7" class="text-center text-muted">Диалогов пока нет.</td></tr><?php endif; ?>
	<?php foreach ($objects as $item) : ?>
		<?php $audiences = array('all' => 'Все посетители', 'guest' => 'Гости', 'customer' => 'Клиенты'); ?>
		<tr><td><strong><?= $escape($item['title_ru']) ?></strong><?php if (!empty($item['image'])) : ?><br><small>Изображение добавлено</small><?php endif; ?></td><td><?= $escape($audiences[$item['audience']] ?? $item['audience']) ?></td><td><?= $escape($item['starts_at'] ?: 'Сразу') ?><br><?= $escape($item['ends_at'] ?: 'Без окончания') ?></td><td><?= (int) $item['display_delay_seconds'] ?> сек.</td><td><?= (int) $item['priority'] ?></td><td><span class="label <?= !empty($item['is_active']) ? 'label-success' : 'label-default' ?>"><?= !empty($item['is_active']) ? 'Включён' : 'Выключен' ?></span></td><td><a class="btn btn-sm blue" href="/<?= ADM_CONTROLLER ?>/site_dialogs/item/<?= (int) $item['id'] ?>"><i class="fa fa-pencil"></i> Изменить</a> <form method="post" action="/<?= ADM_CONTROLLER ?>/site_dialogs/toggle/<?= (int) $item['id'] ?>" style="display:inline"><input type="hidden" name="is_active" value="<?= empty($item['is_active']) ? 1 : 0 ?>"><button class="btn btn-sm default" type="submit"><?= empty($item['is_active']) ? 'Включить' : 'Выключить' ?></button></form> <form method="post" action="/<?= ADM_CONTROLLER ?>/site_dialogs/delete/<?= (int) $item['id'] ?>" style="display:inline" onsubmit="return confirm('Удалить диалог?')"><button class="btn btn-sm red" type="submit">Удалить</button></form></td></tr>
	<?php endforeach; ?>
	</tbody></table></div>
<?php endif; ?>
