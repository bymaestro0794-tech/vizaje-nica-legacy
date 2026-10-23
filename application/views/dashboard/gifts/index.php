<?php
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
?>
<div class="page-bar"><ul class="page-breadcrumb"><li><i class="fa fa-home"></i> <a href="/<?= ADM_CONTROLLER ?>/menu/">Главная</a> <i class="fa fa-circle"></i></li><li><span><?= $escape($title) ?></span></li></ul></div>
<h1 class="page-title"><?= $escape($title) ?></h1>
<?php if (isset($_SESSION['success'])) : ?><div class="alert alert-success"><?= $escape($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error'])) : ?><div class="alert alert-danger"><?php foreach ((array) $_SESSION['error'] as $error) echo $escape($error) . '<br>'; unset($_SESSION['error']); ?></div><?php endif; ?>

<?php if (!empty($migration_required)) : ?>
    <div class="alert alert-warning">Сначала примените SQL-патчи <code>database/patches/2026-10-23-create-gifts.sql</code> и <code>database/patches/2026-10-23-gift-visibility.sql</code>, затем обновите страницу.</div>
<?php else : ?>
    <div class="row"><div class="portlet bordered"><div class="panel-heading"><h4><a data-toggle="collapse" href="#gift-add"><i class="fa fa-plus"></i> <?= $escape($add) ?></a></h4></div>
        <div id="gift-add" class="panel-collapse collapse"><form action="<?= $escape($a_path) ?>" method="post"><div class="panel-body">
            <?php foreach (array('RU', 'RO') as $lang) : ?>
                <div class="form-group"><label>Название <?= $lang ?> *</label><input class="form-control" name="title<?= $lang ?>" maxlength="255" required></div>
                <div class="form-group"><label>Описание <?= $lang ?></label><textarea class="form-control" name="description<?= $lang ?>" rows="2"></textarea></div>
            <?php endforeach; ?>
            <div class="form-group"><label>Товар подарка *</label><div class="gift-product-picker" data-lookup="<?= $escape($path) ?>product_lookup" data-variants="<?= $escape($path) ?>variant_lookup/"><input class="form-control" type="search" data-product-search placeholder="Найти товар по названию, артикулу или ID"><div data-product-results style="margin-top:8px"></div><div data-product-selected style="margin-top:8px;color:#333"></div><input type="hidden" name="product_id" data-product-id required><select class="form-control" name="variant_id" data-variant style="margin-top:8px" disabled><option value="">Весь товар / без варианта</option></select></div></div>
            <div class="form-group"><label>Порядок</label><input class="form-control" type="number" name="sorder" value="0" style="max-width:120px"></div>
            <label class="mt-checkbox mt-checkbox-outline"><input type="checkbox" name="is_enabled" value="1" checked> Активен<span></span></label><br>
            <label class="mt-checkbox mt-checkbox-outline"><input type="checkbox" name="show_on_site" value="1" checked> Показывать на сайте<span></span></label>
            <p><button class="btn green" type="submit"><i class="fa fa-check"></i> Создать</button></p>
        </div></form></div>
    </div></div>

    <?php if (!empty($objects)) : ?><div class="row"><div class="portlet light"><div class="portlet-body"><form action="<?= $escape($o_path) ?>" method="post"><div class="table-scrollable"><table class="table table-bordered table-striped table-hover"><thead><tr><th style="width:90px">Порядок</th><th>ID / название</th><th>Товар</th><th>Статус</th><th>Действия</th></tr></thead><tbody>
        <?php foreach ($objects as $item) : ?><tr><td><input class="form-control" type="number" name="so[<?= (int) $item->id ?>]" value="<?= (int) $item->sorder ?>" style="width:80px"></td><td><strong>#<?= (int) $item->id ?></strong><br><a href="<?= $escape($e_path . (int) $item->id) ?>"><?= $escape($item->titleRU) ?></a><br><small><?= $escape($item->titleRO) ?></small></td><td><?= $escape($item->product_title_ru ?: $item->product_title_ro ?: 'Товар не выбран') ?><?php if (!empty($item->product_sku)) : ?><br><small>SKU: <?= $escape($item->product_sku) ?></small><?php endif; ?><?php if (!empty($item->variant_id)) : ?><br><small>Вариант #<?= (int) $item->variant_id ?></small><?php endif; ?></td><td><?= !empty($item->is_enabled) ? '<span class="label label-success">Активен</span>' : '<span class="label label-default">Скрыт</span>' ?><br><?= !empty($item->show_on_site) ? '<span class="label label-info">На сайте</span>' : '<span class="label label-default">Не показывать</span>' ?></td><td><a class="btn btn-xs green-stripe" href="<?= $escape($e_path . (int) $item->id) ?>">Изменить</a> <a class="btn btn-xs red-stripe mine_delete_row" href="<?= $escape($del_path . (int) $item->id) ?>">Удалить</a></td></tr><?php endforeach; ?>
    </tbody></table></div><button class="btn green" type="submit">Сохранить порядок</button></form></div></div></div><?php endif; ?>
<?php endif; ?>

<script>
(function () {
    var root = document.querySelector('.gift-product-picker');
    if (!root) return;
    var input = root.querySelector('[data-product-search]'), results = root.querySelector('[data-product-results]'), selected = root.querySelector('[data-product-selected]'), productId = root.querySelector('[data-product-id]'), variant = root.querySelector('[data-variant]');
    function loadVariants(id) { variant.innerHTML = '<option value="">Весь товар / без варианта</option>'; variant.disabled = !id; if (!id) return; fetch(root.dataset.variants + encodeURIComponent(id)).then(function (r) { return r.json(); }).then(function (list) { list.forEach(function (v) { var o = document.createElement('option'); o.value = v.id; o.textContent = '#' + v.id + ' ' + (v.titleRU || v.titleRO || v.SKU || 'Вариант'); variant.appendChild(o); }); }); }
    function choose(p) { productId.value = p.id; selected.textContent = (p.titleRU || p.titleRO || 'Товар') + ' (#' + p.id + ')'; input.value = ''; results.innerHTML = ''; loadVariants(p.id); }
    input.addEventListener('input', function () { var q = input.value.trim(); if (q.length < 2) { results.innerHTML = ''; return; } fetch(root.dataset.lookup + '?q=' + encodeURIComponent(q)).then(function (r) { return r.json(); }).then(function (list) { results.innerHTML = list.map(function (p) { return '<button type="button" class="btn btn-xs default" data-id="' + p.id + '" style="margin:0 5px 5px 0">' + (p.titleRU || p.titleRO || 'Товар') + ' (#' + p.id + ')</button>'; }).join(''); results.querySelectorAll('[data-id]').forEach(function (button) { button.onclick = function () { choose(list.find(function (p) { return String(p.id) === button.dataset.id; })); }; }); }); });
}());
</script>
