<!-- BEGIN PAGE HEADER-->
<!-- BEGIN PAGE BAR -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="/<?= ADM_CONTROLLER ?>/menu/"><?= lang('Home') ?></a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="<?= $parent_url ?>"><?= $parent_title ?></a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span><?= $title ?></span>
        </li>
    </ul>
</div>
<!-- END PAGE BAR -->
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE TITLE-->
<h1 class="page-title"><?= $title ?></h1>
<!-- END PAGE TITLE-->

<?php // Отображаем сообщения пользователю ?>
<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-block alert-success fade in">
        <button type="button" class="close" data-dismiss="alert"></button>
        <?= $_SESSION['success']; ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])) : ?>
    <div class="alert alert-block alert-danger fade in">
        <button type="button" class="close" data-dismiss="alert"></button>
        <?php foreach ($_SESSION['error'] as $error) : ?>
            <?= $error ?>
            <br/>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <div class="portlet light">
        <div class="portlet-body">
            <form method="post" enctype="multipart/form-data">
                <div class="panel-body">
                    <ul class="nav nav-pills">
                        <li class="active">
                            <a href="#tab_1_1" data-toggle="tab"><?=lang('General information')?></a>
                        </li>
                        <li>
                            <a href="#tab_1_3" data-toggle="tab"><?=lang('Products')?></a>
                        </li>
                        <li>
                            <a href="#tab_1_2" data-toggle="tab"><?=lang('SEO')?></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="tab_1_1">
                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Title') ?> <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="title<?= strtoupper($lang) ?>"
                                                       value="<?= $item->{'title' . strtoupper($lang)} ?>"
                                                       class="form-control" required>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200"><?= lang('Time From') ?></td>
                                        <td>
                                            <input type="date" name="date_from" value="<?= $item->date_from ?>"
                                                   class="form-control" style="max-width: 150px">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Time to') ?></td>
                                        <td>
                                            <input type="date" name="date_to" value="<?= $item->date_to ?>"
                                                   class="form-control" style="max-width: 150px">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Sale') ?> </td>
                                        <td>
                                            <input type="text" name="sale" class="form-control" value="<?= $item->sale ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200">Формат акции</td>
                                        <td>
                                            <?php $offerChannel = isset($item->is_online) ? (int) $item->is_online : 1; ?>
                                            <select name="is_online" class="form-control" required>
                                                <option value="1" <?= $offerChannel === 1 ? 'selected' : '' ?>>Онлайн</option>
                                                <option value="0" <?= $offerChannel === 0 ? 'selected' : '' ?>>Офлайн</option>
                                                <option value="2" <?= $offerChannel === 2 ? 'selected' : '' ?>>Онлайн + офлайн</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Location') ?> <?= strtoupper($lang) ?>
                                                *
                                            </td>
                                            <td>
                                                <input type="text" name="location<?= strtoupper($lang) ?>" value="<?= $item->{'location' . strtoupper($lang)} ?>"
                                                       class="form-control">
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Description') ?> <?= strtoupper($lang) ?></td>
                                            <td>
                                                <textarea type="text" name="desc<?= strtoupper($lang) ?>"  class="form-control ckeditor" ><?= $item->{'desc' . strtoupper($lang)} ?>
                                                </textarea>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                    <tr>
                                        <td width="200"><?= lang('Photo') ?> <?= strtoupper($lang) ?></td>
                                        <td>
                                            <input type="file" name="img<?= strtoupper($lang) ?>" id="file" class="form-control">
                                            <div class="note note-warning"
                                                 style="margin-bottom: 0px; margin-top: 10px;">
                                                <p>
                                                    <?= lang('Allowable sizes') ?>: 345x230
                                                </p>
                                            </div>
                                            <?php if (!empty($item->{'img'.strtoupper($lang)})): ?>
                                                <?php $src = newthumbs($item->{'img'.strtoupper($lang)}, $table, 250, 250, '250x250x1', 1) ?>
                                                <br>
                                                <div class="mt-element-card mt-element-overlay item">
                                                    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                                        <div class="mt-card-item">
                                                            <div class="mt-card-avatar mt-overlay-1">
                                                                <img src="<?= $src ?>"/>
                                                                <div class="mt-overlay">
                                                                    <ul class="mt-info">
                                                                        <li>
                                                                            <a class="btn red mine_delete_photo"
                                                                               data-table="<?= $table ?>"
                                                                               data-id="<?= $item->id ?>"
                                                                               data-col="img<?= strtoupper($lang) ?>"
                                                                               href="javascript:;">
                                                                                <i class="fa fa-ban"></i>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                    <tr>
                                        <td width="200"><?= lang('Banner') ?> <?= strtoupper($lang) ?></td>
                                        <td>
                                            <input type="file" name="imgB<?= strtoupper($lang) ?>" id="file" class="form-control">
                                            <div class="note note-warning"
                                                 style="margin-bottom: 0px; margin-top: 10px;">
                                                <p>
                                                    <?= lang('Allowable sizes') ?>: 1630x282
                                                </p>
                                            </div>
                                            <?php if (!empty($item->{'imgB'.strtoupper($lang)})): ?>
                                                <?php $src = newthumbs($item->{'imgB'.strtoupper($lang)}, $table, 250, 250, '250x250x1', 1) ?>
                                                <br>
                                                <div class="mt-element-card mt-element-overlay item">
                                                    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                                        <div class="mt-card-item">
                                                            <div class="mt-card-avatar mt-overlay-1">
                                                                <img src="<?= $src ?>"/>
                                                                <div class="mt-overlay">
                                                                    <ul class="mt-info">
                                                                        <li>
                                                                            <a class="btn red mine_delete_photo"
                                                                               data-table="<?= $table ?>"
                                                                               data-id="<?= $item->id ?>"
                                                                               data-col="imgB<?= strtoupper($lang) ?>"
                                                                               href="javascript:;">
                                                                                <i class="fa fa-ban"></i>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i class="fa fa-check"></i>
                                                <?= lang('Add') ?>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab_1_2">
                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                    <? foreach(language(true) as $lang){ ?>
                                        <tr>
                                            <td width="200"><?=lang('Headline')?> <?=strtoupper($lang)?></td>
                                            <td>
                                                <input type="text" name="seoTitle<?=strtoupper($lang)?>" class="form-control"
                                                       value="<?= $item->{'seoTitle'.strtoupper($lang)} ?>">
                                            </td>
                                        </tr>
                                    <?}?>
                                    <? foreach(language(true) as $lang){ ?>
                                        <tr>
                                            <td width="200"><?=lang('Keywords')?> <?=strtoupper($lang)?></td>
                                            <td>
                                                <textarea name="seoKeywords<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control"><?= $item->{'seoKeywords'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <?}?>
                                    <? foreach(language(true) as $lang){ ?>
                                        <tr>
                                            <td width="200"><?=lang('Description')?> <?=strtoupper($lang)?></td>
                                            <td>
                                                <textarea name="seoDesc<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control"><?= $item->{'seoDesc'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <?}?>
                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i class="fa fa-check"></i><?=lang('Edit')?>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab_1_3">
                            <div class="offer-product-picker" data-offer-product-picker data-offer-id="<?= (int) $item->id ?>" style="margin-bottom:20px;padding:15px;background:#f7f7f7;border:1px solid #ddd;">
                                <h4 style="margin-top:0;">Добавить товары без перезагрузки</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="search" class="form-control" data-offer-product-query placeholder="Название, SKU или ID">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" data-offer-product-brand>
                                            <option value="">Все бренды</option>
                                            <?php foreach (($product_brands ?? array()) as $brand) : ?>
                                                <option value="<?= (int) $brand->id ?>"><?= htmlspecialchars((string) $brand->title, ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" data-offer-product-category>
                                            <option value="">Все категории</option>
                                            <?php foreach (($product_categories ?? array()) as $category) : ?>
                                                <option value="<?= (int) $category->id ?>"><?= htmlspecialchars((string) ($category->titleRU ?: $category->titleRO), ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn green" data-offer-product-select-all>Выбрать все</button>
                                    </div>
                                </div>
                                <div data-offer-product-results style="margin-top:12px;max-height:320px;overflow:auto;background:#fff;border:1px solid #ddd;"></div>
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;">
                                    <span data-offer-product-count>Выбрано: 0</span>
                                    <button type="button" class="btn blue" data-offer-product-add>Добавить выбранные</button>
                                </div>
                            </div>
                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <td width="220">
                                            <input type="text" value="" class="add_products_alt form-control"
                                                   name="add_products_alt" placeholder="Ведите Артикул">
                                        </td>
                                        <td colspan="1">
                                            <button class="btn green add_block_products" id="project_save_click"><i
                                                        class="fa fa-plus"></i>
                                                Добавить Товар
                                            </button>
                                        </td>
                                        <td colspan="3">
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                                <div class="products_list" >
                                </div>
                                <div class="add_products"></div>
                                <?php if (!empty($products_alt)) {
                                    $i = 0; ?>
                                    <?php foreach ($products_alt as $key) { ?>
                                        <table class="table table-bordered table-striped table-hover"
                                               id="prodalt<?= $key->id ?>">
                                            <tbody>
                                            <tr>
                                                <td width="80">
                                                    <input type="text" name="sorder[<?=$key->id?>]" value="<?=$key->sorder?>" class="form-control" style="text-align: center;">
                                                </td>
                                                <td width="150">
                                                    SKU: <?= $key->SKU ?>
                                                </td>
                                                <td width="">
                                                    <?= $key->title ?>
                                                </td>
                                                <td width="100">
                                                    <a onclick="DeleteProdAlt(<?= $key->id ?>)"
                                                       data-op="<?= $key->id ?>"
                                                       class="btn btn-xs default btn-editable red-stripe">
                                                        <i class="glyphicon glyphicon-remove-circle"></i> <?=lang('Delete')?>
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    <?php }
                                } ?>
                                <table class="table table-bordered table-striped table-hover">
                                    <tr>
                                        <td width="200">
                                            <button type="submit" class="btn green"><i class="fa fa-check"></i> Сохранить
                                            </button>
                                        </td>
                                        <td>

                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('.add_block_products').on('click', function () {
        var product = $("input[name='add_products_alt']").val();
        $.ajax({
            url: '/cp/offers/products_alt', // путь к обработчику
            type: 'POST', // метод отправки
            data: {product: product, id: "<?=$item->id?>"},
            success: function (data) {
                console.log("УСПЕХ"); // выводим сообщение в консоль
                $(".add_products").prepend(data);
            },
            error: function (data) {
                console.log(data); // выводим ошибку в консоль
            }
        });
        return false;
    });

    function DeleteProdAlt(id) {
        var tbid = '#prodalt' + id;
        $.ajax({
            url: '/cp/offers/prodalt_delete', // путь к обработчику
            type: 'POST', // метод отправки
            data: {id: id},
            success: function (data) {
                console.log("УСПЕХ"); // выводим сообщение в консоль
                $(tbid).remove();
            },
            error: function (data) {
                console.log(data); // выводим ошибку в консоль
            }
        });
    }
</script>
<script>
    (function () {
        var picker = document.querySelector('[data-offer-product-picker]');
        if (!picker) return;

        var query = picker.querySelector('[data-offer-product-query]');
        var brand = picker.querySelector('[data-offer-product-brand]');
        var category = picker.querySelector('[data-offer-product-category]');
        var results = picker.querySelector('[data-offer-product-results]');
        var count = picker.querySelector('[data-offer-product-count]');
        var timer = null;

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>'"]/g, function (char) {
                return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char];
            });
        }

        function updateCount() {
            count.textContent = 'Выбрано: ' + results.querySelectorAll('input[type="checkbox"]:checked').length;
        }

        function loadProducts() {
            var params = new URLSearchParams({
                offer_id: picker.dataset.offerId,
                q: query.value,
                brand_id: brand.value,
                category_id: category.value
            });

            fetch('/cp/offers/product_lookup?' + params.toString(), { credentials: 'same-origin' })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    var items = payload.items || [];
                    if (!items.length) {
                        results.innerHTML = '<div style="padding:14px;color:#777;">Товары не найдены.</div>';
                        updateCount();
                        return;
                    }

                    results.innerHTML = items.map(function (item) {
                        var disabled = item.attached ? ' disabled' : '';
                        var badge = item.attached ? '<span style="color:#198754;margin-left:8px;">уже добавлен</span>' : '';
                        return '<label style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-bottom:1px solid #eee;cursor:pointer;">'
                            + '<input type="checkbox" value="' + item.id + '"' + disabled + '>'
                            + '<strong>#' + item.id + '</strong> '
                            + escapeHtml(item.title) + ' <small style="color:#777;">' + escapeHtml(item.sku) + (item.brand ? ' · ' + escapeHtml(item.brand) : '') + '</small>'
                            + badge + '</label>';
                    }).join('');
                    results.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
                        input.addEventListener('change', updateCount);
                    });
                    updateCount();
                });
        }

        function scheduleLoad() {
            window.clearTimeout(timer);
            timer = window.setTimeout(loadProducts, 250);
        }

        query.addEventListener('input', scheduleLoad);
        brand.addEventListener('change', loadProducts);
        category.addEventListener('change', loadProducts);

        picker.querySelector('[data-offer-product-select-all]').addEventListener('click', function () {
            results.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(function (input) {
                input.checked = true;
            });
            updateCount();
        });

        picker.querySelector('[data-offer-product-add]').addEventListener('click', function () {
            var selected = Array.from(results.querySelectorAll('input[type="checkbox"]:checked'));
            if (!selected.length) return;

            var body = new URLSearchParams();
            body.append('offer_id', picker.dataset.offerId);
            selected.forEach(function (input) { body.append('product_ids[]', input.value); });

            fetch('/cp/offers/products_bulk_add', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString()
            })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    (payload.items || []).reverse().forEach(function (item) {
                        var table = document.createElement('table');
                        table.className = 'table table-bordered table-striped table-hover';
                        table.id = 'prodalt' + item.id;
                        table.innerHTML = '<tbody><tr>'
                            + '<td width="80"><input type="text" name="sorder[' + item.id + ']" value="' + item.sorder + '" class="form-control" style="text-align:center;"></td>'
                            + '<td width="150">SKU: ' + escapeHtml(item.sku) + '</td>'
                            + '<td>' + escapeHtml(item.title) + '</td>'
                            + '<td width="100"><a onclick="DeleteProdAlt(' + item.id + ')" class="btn btn-xs default btn-editable red-stripe"><i class="glyphicon glyphicon-remove-circle"></i> Удалить</a></td>'
                            + '</tr></tbody>';
                        document.querySelector('.products_list').prepend(table);
                    });
                    loadProducts();
                });
        });

        loadProducts();
    }());
</script>
