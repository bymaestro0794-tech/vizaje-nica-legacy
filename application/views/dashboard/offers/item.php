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