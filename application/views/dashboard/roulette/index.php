<?php
defined('BASEPATH') or exit('No direct script access allowed');

$escape = function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

	$prizeTypeLabels = array(
	'discount_percent' => 'Скидка, %',
	'free_shipping' => 'Бесплатная доставка',
	'bonus_points' => 'Бонусные баллы',
	'gift' => 'Подарок в корзину',
	'promo_code' => 'Промокод',
	'surprise' => 'Сюрприз',
);

?>

<div class="page-bar">
	<ul class="page-breadcrumb">
		<li><i class="fa fa-home"></i> <a href="/<?= ADM_CONTROLLER ?>/menu/"><?= lang('Home') ?></a> <i class="fa fa-circle"></i></li>
		<li><a href="/<?= ADM_CONTROLLER ?>/promocodes/">Промокоды</a> <i class="fa fa-circle"></i></li>
		<li><span>Рулетка удачи</span></li>
	</ul>
</div>

<h1 class="page-title">Настройки рулетки удачи</h1>

<?php if (isset($_SESSION['success'])) : ?>
	<div class="alert alert-success fade in">
		<button type="button" class="close" data-dismiss="alert"></button>
		<?= $escape($_SESSION['success']) ?>
	</div>
	<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])) : ?>
	<div class="alert alert-danger fade in">
		<button type="button" class="close" data-dismiss="alert"></button>
		<?php foreach ((array) $_SESSION['error'] as $error) : ?>
			<?= $escape($error) ?><br>
		<?php endforeach; ?>
	</div>
	<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="alert alert-info">
	<i class="fa fa-info-circle"></i>
	Доступ получают только зарегистрированные клиенты с минимум одним завершённым заказом. Одна прокрутка доступна в календарный период с понедельника 00:00 до следующего понедельника 00:00; выигранный приз действует 24 часа.
</div>

<?php if (!empty($migration_required)) : ?>
	<div class="alert alert-warning">
		Примените SQL-файлы по порядку:
		<code>database/patches/2026-10-16-create-roulette-admin-settings.sql</code>,
		затем <code>database/patches/2026-10-16-roulette-gameplay-v1.sql</code>,
		<code>database/patches/2026-10-22-roulette-weekly-window.sql</code> и
		<code>database/patches/2026-10-22-roulette-reward-categories.sql</code>.
	</div>
<?php else : ?>
	<?php if (empty($settings)) : ?>
		<div class="alert alert-warning">Запись настроек не найдена. Проверьте, что SQL-патч выполнен полностью.</div>
	<?php else : ?>
		<form method="post" action="/<?= ADM_CONTROLLER ?>/roulette/save">
			<div class="portlet light bordered">
				<div class="portlet-title">
					<div class="caption"><i class="fa fa-cog"></i> Основные настройки и тексты</div>
				</div>
				<div class="portlet-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Период прокрутки</label>
								<div class="form-control-static"><strong>Каждый понедельник в 00:00</strong></div>
								<input type="hidden" name="settings[spin_interval_hours]" value="168">
								<p class="help-block">Одна прокрутка доступна до следующего понедельника в 00:00. После выигрыша приз действует 24 часа.</p>
							</div>
						</div>
						<div class="col-md-6">
							<div class="checkbox">
								<label>
									<input type="hidden" name="settings[is_enabled]" value="0">
									<input type="checkbox" name="settings[is_enabled]" value="1" <?= !empty($settings['is_enabled']) ? 'checked' : '' ?>>
									Разрешить серверные вращения
								</label>
							</div>
							<p class="help-block">Выключатель блокирует новые попытки, но не аннулирует уже выданные призы.</p>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="roulette-title-ru">Заголовок — русский</label>
								<input id="roulette-title-ru" class="form-control" type="text" name="settings[titleRU]" maxlength="120" required value="<?= $escape($settings['titleRU'] ?? '') ?>">
							</div>
							<div class="form-group">
								<label for="roulette-description-ru">Описание — русский</label>
								<textarea id="roulette-description-ru" class="form-control" name="settings[descriptionRU]" rows="4"><?= $escape($settings['descriptionRU'] ?? '') ?></textarea>
							</div>
							<div class="form-group">
								<label for="roulette-rules-ru">Правила — русский</label>
								<textarea id="roulette-rules-ru" class="form-control" name="settings[rulesRU]" rows="4"><?= $escape($settings['rulesRU'] ?? '') ?></textarea>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="roulette-title-ro">Заголовок — română</label>
								<input id="roulette-title-ro" class="form-control" type="text" name="settings[titleRO]" maxlength="120" required value="<?= $escape($settings['titleRO'] ?? '') ?>">
							</div>
							<div class="form-group">
								<label for="roulette-description-ro">Описание — română</label>
								<textarea id="roulette-description-ro" class="form-control" name="settings[descriptionRO]" rows="4"><?= $escape($settings['descriptionRO'] ?? '') ?></textarea>
							</div>
							<div class="form-group">
								<label for="roulette-rules-ro">Reguli — română</label>
								<textarea id="roulette-rules-ro" class="form-control" name="settings[rulesRO]" rows="4"><?= $escape($settings['rulesRO'] ?? '') ?></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="portlet light bordered">
				<div class="portlet-title">
					<div class="caption"><i class="fa fa-life-ring"></i> Сектора и награды</div>
					<div class="actions"><span class="text-muted">Шесть секторов из макета</span></div>
				</div>
				<div class="portlet-body">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th>Сектор</th>
									<th>Название RU / RO</th>
									<th>Тип награды</th>
									<th>Значение</th>
									<th>Шанс, %</th>
									<th>Включён</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($prize_codes as $code) : ?>
									<?php
									$prize = null;
									foreach ($prizes as $candidate) {
										if ($candidate['code'] === $code) {
											$prize = $candidate;
											break;
										}
									}
									if ($prize === null) {
										continue;
									}
					$rewardType = (string) ($prize['reward_type'] ?? 'discount_percent');
					$supported = in_array($rewardType, array('discount_percent', 'free_shipping'), true);
					$requiresValue = in_array($rewardType, array('discount_percent', 'bonus_points'), true);
					$hasPayload = in_array($rewardType, array('gift', 'promo_code', 'surprise'), true);
					$rewardPayload = (string) ($prize['reward_payload'] ?? '');
									?>
									<tr>
										<td><code><?= $escape($code) ?></code></td>
										<td style="min-width: 260px;">
											<input class="form-control" type="text" name="prizes[<?= $escape($code) ?>][titleRU]" maxlength="120" required aria-label="Название сектора <?= $escape($code) ?> по-русски" value="<?= $escape($prize['titleRU']) ?>">
											<input class="form-control" style="margin-top: 6px;" type="text" name="prizes[<?= $escape($code) ?>][titleRO]" maxlength="120" required aria-label="Название сектора <?= $escape($code) ?> по-румынски" value="<?= $escape($prize['titleRO']) ?>">
										</td>
						<td style="min-width: 210px;">
							<select class="form-control" name="prizes[<?= $escape($code) ?>][reward_type]" aria-label="Категория выигрыша <?= $escape($code) ?>" data-reward-type>
								<?php foreach ($prizeTypeLabels as $typeCode => $typeLabel) : ?>
									<option value="<?= $escape($typeCode) ?>" <?= $rewardType === $typeCode ? 'selected' : '' ?>><?= $escape($typeLabel) ?></option>
								<?php endforeach; ?>
							</select>
							<p class="help-block" data-reward-hint <?= $supported ? 'hidden' : '' ?>>Эта категория подготовлена, но автоматическая выдача ещё подключается.</p>
						</td>
						<td style="min-width: 170px;">
							<input class="form-control" type="number" min="0.01" max="100" step="0.01" name="prizes[<?= $escape($code) ?>][reward_value]" aria-label="Размер награды <?= $escape($code) ?>" value="<?= $requiresValue && $prize['reward_value'] !== null ? $escape($prize['reward_value']) : '' ?>" <?= $requiresValue ? '' : 'disabled' ?> data-requires-value="<?= $requiresValue ? '1' : '0' ?>">
							<?php if ($hasPayload) : ?>
								<input class="form-control" style="margin-top: 6px;" type="text" maxlength="120" name="prizes[<?= $escape($code) ?>][reward_payload]" aria-label="Параметры награды <?= $escape($code) ?>" placeholder="ID подарка, код или текст" value="<?= $escape($rewardPayload) ?>" data-reward-payload>
							<?php else : ?>
								<input class="form-control" style="margin-top: 6px;" type="text" maxlength="120" name="prizes[<?= $escape($code) ?>][reward_payload]" aria-label="Параметры награды <?= $escape($code) ?>" placeholder="ID подарка, код или текст" value="<?= $escape($rewardPayload) ?>" data-reward-payload hidden disabled>
							<?php endif; ?>
										</td>
										<td style="min-width: 100px;">
			<input class="form-control" type="number" min="0" max="100" step="0.01" name="prizes[<?= $escape($code) ?>][probability]" aria-label="Вероятность сектора <?= $escape($code) ?>" placeholder="не задано" value="<?= $prize['probability'] === null ? '' : $escape($prize['probability']) ?>" data-reward-probability>
										</td>
										<td class="text-center">
							<input type="hidden" name="prizes[<?= $escape($code) ?>][is_enabled]" value="0">
			<input type="checkbox" name="prizes[<?= $escape($code) ?>][is_enabled]" value="1" aria-label="Включить сектор <?= $escape($code) ?>" <?= !empty($prize['is_enabled']) ? 'checked' : '' ?> data-reward-enabled>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
			<p class="help-block">Выберите категорию выигрыша в каждом секторе. Сейчас можно включать скидку и бесплатную доставку; подарок, промокод, бонусы и сюрприз уже подготовлены для дальнейшей автоматической выдачи. Сумма вероятностей включённых секторов должна равняться 100%.</p>
				</div>
			</div>

			<div class="margin-top-20">
				<button class="btn green" type="submit"><i class="fa fa-save"></i> Сохранить настройки</button>
			</div>
		</form>
	<?php endif; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('tr').forEach(function (row) {
		var typeSelect = row.querySelector('[data-reward-type]');
		var checkbox = row.querySelector('[data-reward-enabled]');
		var valueInput = row.querySelector('input[name$="[reward_value]"]');
		var payloadInput = row.querySelector('[data-reward-payload]');
		var probabilityInput = row.querySelector('[data-reward-probability]');
		var hint = row.querySelector('[data-reward-hint]');
		if (!typeSelect || !checkbox || !valueInput || !probabilityInput) return;

		function syncRewardFields() {
			var supported = typeSelect.value === 'discount_percent' || typeSelect.value === 'free_shipping';
			var requiresValue = typeSelect.value === 'discount_percent' || typeSelect.value === 'bonus_points';
			var hasPayload = typeSelect.value === 'gift' || typeSelect.value === 'promo_code' || typeSelect.value === 'surprise';
			valueInput.disabled = !requiresValue;
			valueInput.dataset.requiresValue = requiresValue ? '1' : '0';
			valueInput.max = typeSelect.value === 'bonus_points' ? '1000000' : '100';
			if (payloadInput) {
				payloadInput.hidden = !hasPayload;
				payloadInput.disabled = !hasPayload;
			}
			if (hint) hint.hidden = supported;
			if (!supported) checkbox.checked = false;
			probabilityInput.required = checkbox.checked;
			probabilityInput.min = checkbox.checked ? '0.01' : '0';
			valueInput.required = checkbox.checked && requiresValue;
		}

		typeSelect.addEventListener('change', syncRewardFields);
		checkbox.addEventListener('change', syncRewardFields);
		syncRewardFields();
	});
});
</script>
