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
    <div class="portlet bordered">
        <div class="accordion" id="accordion">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #fff;">
                    <h4 class="panel-title">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse1"><i
                                    class="fa fa-plus"></i> <?= $add ?></a>
                    </h4>
                </div>
                <div id="collapse1" class="panel-collapse collapse">
                    <form action="<?= $a_path ?>" method="post" enctype="multipart/form-data">
                        <div class="panel-body">
                            <ul class="nav nav-pills">
                                <li class="active">
                                    <a href="#tab_1_1" data-toggle="tab"><?= lang('General information') ?></a>
                                </li>
                                <li>
                                    <a href="#tab_1_2" data-toggle="tab"><?= lang('SEO') ?></a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade active in" id="tab_1_1">
                                    <div class="table-scrollable">
                                        <table class="table table-bordered table-striped table-hover">
                                            <tbody>
                                            <tr>
                                                <td><?= lang('Category') ?> *</td>
                                                <td>

                                                    <?php $selectedCategories = $itemCategories ?? []; ?>

                                                    <div class="category-tree">

                                                        <?php foreach ($for_list as $cat) { ?>
                                                            <div class="cat-item">

                                                                <?php if (!empty($cat['children'])) { ?>
                                                                    <span class="toggle" onclick="toggleCat(this)">▶</span>
                                                                <?php } else { ?>
                                                                    <span class="toggle empty"></span>
                                                                <?php } ?>

                                                                <label>
                                                                    <input type="checkbox"
                                                                           name="categories[]"
                                                                           value="<?= $cat['id'] ?>"
                                                                        <?= in_array($cat['id'], $selectedCategories) ? 'checked' : '' ?>>
                                                                    <strong><?= $cat['title'] ?></strong>
                                                                </label>

                                                                <?php if (!empty($cat['children'])) { ?>
                                                                    <div class="cat-children">

                                                                        <?php foreach ($cat['children'] as $child) { ?>
                                                                            <div class="cat-item">

                                                                                <?php if (!empty($child['children'])) { ?>
                                                                                    <span class="toggle" onclick="toggleCat(this)">▶</span>
                                                                                <?php } else { ?>
                                                                                    <span class="toggle empty"></span>
                                                                                <?php } ?>

                                                                                <label>
                                                                                    <input type="checkbox"
                                                                                           name="categories[]"
                                                                                           value="<?= $child['id'] ?>"
                                                                                        <?= in_array($child['id'], $selectedCategories) ? 'checked' : '' ?>>
                                                                                    <?= $child['title'] ?>
                                                                                </label>

                                                                                <?php if (!empty($child['children'])) { ?>
                                                                                    <div class="cat-children">

                                                                                        <?php foreach ($child['children'] as $ch) { ?>
                                                                                            <div class="cat-item">

                                                                                                <span class="toggle empty"></span>

                                                                                                <label>
                                                                                                    <input type="checkbox"
                                                                                                           name="categories[]"
                                                                                                           value="<?= $ch['id'] ?>"
                                                                                                        <?= in_array($ch['id'], $selectedCategories) ? 'checked' : '' ?>>
                                                                                                    <?= $ch['title'] ?>
                                                                                                </label>

                                                                                            </div>
                                                                                        <?php } ?>

                                                                                    </div>
                                                                                <?php } ?>

                                                                            </div>
                                                                        <?php } ?>

                                                                    </div>
                                                                <?php } ?>

                                                            </div>
                                                        <?php } ?>

                                                    </div>

                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('Brands') ?></td>
                                                <td>
                                                    <select name="brand_id" id="" class="form-control ">
                                                        <option value="" selected><?= lang('Select Brands') ?></option>
                                                        <?php foreach ($brands as $item) { ?>
                                                            <option value="<?= $item->id ?>"> <?= $item->title ?> </option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200"><?= lang('Article') ?> *</td>
                                                <td>
                                                    <input type="text" name="SKU"
                                                           class="form-control" required>
                                                </td>
                                            </tr>

                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200"><?= lang('Title') ?> <?= strtoupper($lang) ?> *</td>
                                                    <td>
                                                        <input type="text" name="title<?= strtoupper($lang) ?>"
                                                               class="form-control" required>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <tr>
                                                <td><?= lang('Qty') ?></td>
                                                <td>
                                                    <input type="text" name="on_stock"
                                                           class="form-control">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200"><?= lang('Price') ?></td>
                                                <td>
                                                    <input type="text" name="price"
                                                           class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200"><?= lang('Lower price') ?></td>
                                                <td>
                                                    <input type="text" name="discount_price"
                                                           class="form-control">
                                                </td>
                                            </tr>
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200"><?= lang('Text') ?> <?= strtoupper($lang) ?></td>
                                                    <td>
                                                        <textarea name="text<?= strtoupper($lang) ?>" cols="30" rows="3"
                                                                  class="form-control ckeditor"></textarea>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <tr>
                                                <td width="200"><?= lang('Photo') ?></td>
                                                <td>
                                                    <input type="file" name="images[]" id="file" multiple
                                                           class="form-control">
                                                    <div class="note note-warning"
                                                         style="margin-bottom: 0px; margin-top: 10px;">
                                                        <p>
                                                            <?= lang('Allowable sizes') ?>: 1020x850 2мб
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
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
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200"><?= lang('Headline') ?> <?= strtoupper($lang) ?></td>
                                                    <td>
                                                        <input type="text" name="seoTitle<?= strtoupper($lang) ?>"
                                                               class="form-control">
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200"><?= lang('Keywords') ?> <?= strtoupper($lang) ?></td>
                                                    <td>
                                                        <textarea name="seoKeywords<?= strtoupper($lang) ?>" cols="30"
                                                                  rows="3" class="form-control"></textarea>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200"><?= lang('Description') ?> <?= strtoupper($lang) ?></td>
                                                    <td>
                                                        <textarea name="seoDesc<?= strtoupper($lang) ?>" cols="30"
                                                                  rows="3" class="form-control"></textarea>
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
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="portlet bordered">
        <div class="accordion" id="accordion2">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #fff;">
                    <h4 class="panel-title">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                           href="#collapse2"><i
                                    class="fa fa-plus"></i> Фильтры</a>
                    </h4>
                </div>
                <div id="collapse2" class="panel-collapse collapse">
                    <form action="/cp/products/" method="get" enctype="multipart/form-data">
                        <?php if (!empty($_GET['cat'])) { ?>
                            <input type="hidden" name="cat"
                                   value="<?= !empty($_GET['cat']) ? $_GET['cat']: '' ?>">
                        <?php } ?>
                        <?php if (!empty($_GET['query'])) { ?>
                            <input type="hidden" name="query"
                                   value="<?= !empty($_GET['query']) ? $_GET['query'] : '' ?>">
                        <?php } ?>
                        <div class="panel-body">
                            <div class="tab-content">
                                <div class="tab-pane fade active in" id="tab_1_1">
                                    <div class="table-scrollable">
                                        <table class="table table-bordered table-striped table-hover">
                                            <tbody>
                                            <tr>
                                                <td width="200"><?= lang("Brands") ?></td>
                                                <td>
                                                    <select name="brands" class="form-control" style="width: 250px;">
                                                        <option value="" <?= empty($_GET['brands']) ? 'selected' : '' ?> ><?= lang("All") ?></option>
                                                        <?php foreach ($brands as $brand) { ?>
                                                            <option value="<?= $brand->id ?>" <?= (!empty($_GET['brands']) && $_GET['brands'] == $brand->id) ? 'selected' : '' ?>><?= $brand->title ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200"><?=lang('Sorder')?></td>
                                                <td>
                                                    <select name="sorder" class="form-control" style="width: 250px;">
                                                        <option value="sorder ASC" <?= (!empty($_GET['sorder']) && $_GET['sorder'] == "sorder ASC") ? 'selected' : '' ?> ><?=lang('Sorder')?></option>
                                                        <option value="on_stock DESC"  <?= (!empty($_GET['sorder']) && $_GET['sorder'] == "on_stock DESC") ? 'selected' : '' ?>><?= lang('Quantity') ?></option>
                                                        <option value="price DESC" <?= (!empty($_GET['sorder']) && $_GET['sorder'] == "price DESC") ? 'selected' : '' ?>><?= lang('Price') ?></option>
                                                        <option value="SKU DESC" <?= (!empty($_GET['sorder']) && $_GET['sorder'] == "SKU DESC") ? 'selected' : '' ?>><?= lang('Article') ?></option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200">&nbsp;</td>
                                                <td>
                                                    <button type="submit" class="btn green"><i class="fa fa-check"></i>
                                                        <?= lang('Search') ?>
                                                    </button>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="portlet light">
            <div class="portlet-body">
                <div id="jstree">
                    <ul>
                        <li>
                            <span><?= lang('All Categories') ?></span>
                        </li>
                    </ul>
                    <? jstree($categories) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="portlet light">
            <div class="portlet-body" id="load">
                <div class="col-md-12">
                    <form action="/cp/products/" method="get" enctype="text/plain">
                        <div class="margin-bottom-5" style="text-align: right;">
                            <?= lang('Search') ?>
                            <input type="text" style="width: 240px; padding-top: 1px; display: inline-block;"
                                   class="form-control form-filter input-sm" name="query" value="">
                            <button class="btn btn-sm btn-success filter-submit margin-bottom">
                                <i class="fa fa-search"></i> <?= lang('Search') ?>
                            </button>
                            <a href="/cp/products/" class="btn btn-sm btn-default filter-cancel">
                                <i class="fa fa-times"></i><?= lang('Search') ?> </a>
                        </div>
                    </form>
                </div>
                <form action="<?= $o_path; ?>" method="post" id="products">
                    <div class="table-scrollable">
                        <table class="table table-bordered table-striped table-hover">
                            <tr>
                                <th width="100"> <?= lang('Sorting') ?></th>
                                <th> <?= lang('Title') ?></th>
                                <th width="180"><?= lang('Article') ?></th>
                                <th width="180"></th>
                                <th width="180"></th>
                                <th width="180"></th>
                                <th width="125"> <?= lang('Action') ?></th>
                            </tr>
                            <? foreach ($objects as $object) { ?>
                                <tr <?php if (empty($object->img) || empty($object->img_variable)){?>style="background: red"<?php } ?>>
                                    <td>
                                        <input style="width:50px;margin-left: 0;margin-right: 15px;" type="text"
                                               onkeyup="this.value=this.value.replace(/[^\d]/,\'\')" min="1"
                                               class="form-control text-center sorder" value="<?= $object->sorder ?>"
                                               name="so[<?= $object->id ?>]">
                                    </td>
                                    <td><a href="<?= $e_path . $object->id . '/' ?>"><?= $object->titleRO ?></a></td>
                                    <td><a href="<?= $e_path . $object->id . '/' ?>"><?= $object->SKU ?></a></td>

                                    <td class="align-middle">
                                        <?php $cmod = (!empty($object->best_selling)) ? 'checked' : '' ?>
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            <input type="checkbox" <?= $cmod ?> value="<?= $object->id ?>"
                                                   data-col="best_selling" data-table="<?= $table ?>"
                                                   class="mine_change_check">Hit
                                            <span></span>
                                        </label>
                                    </td>
                                    <td class="align-middle">
                                        <?php $cmod = (!empty($object->is_new)) ? 'checked' : '' ?>
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            <input type="checkbox" <?= $cmod ?> value="<?= $object->id ?>"
                                                   data-col="is_new" data-table="<?= $table ?>"
                                                   class="mine_change_check">New
                                            <span></span>
                                        </label>
                                    </td>
                                    <td class="align-middle">
                                        <?php $cmod = (!empty($object->isShown)) ? 'checked' : '' ?>
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            <input type="checkbox" <?= $cmod ?> value="<?= $object->id ?>"
                                                   data-col="isShown" data-table="<?= $table ?>"
                                                   class="mine_change_check"><?= lang('Show on site') ?>
                                            <span></span>
                                        </label>
                                    </td>

                                    <td width="120" class="align-middle">
                                        <a href="<?= $e_path . $object->id . '/' ?>"
                                           class="btn btn-xs default btn-editable green-stripe">
                                            <i class="glyphicon glyphicon-edit"></i>
                                        </a>
                                        <a href="<?= $del_path . $object->id . '/' ?>"
                                           class="btn btn-xs default btn-editable red-stripe mine_delete_row">
                                            <i class="glyphicon glyphicon-remove-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            <? } ?>
                        </table>
                    </div>
                    <button type="submit" class="btn green"><i class="fa fa-check"></i> <?= lang('Refresh order') ?>
                    </button>
                </form>
                <div id="paginator" style="margin-top: 20px;"></div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/app/plugins/jstree/themes/default/style.min.css">
<script src="/app/plugins/jstree/jstree.min.js"></script>

<link rel="stylesheet" href="/app/plugins/pagination/simplePagination.css?t=<?=time()?>">
<script src="/app/plugins/pagination/jquery.simplePagination.js?t=<?=time()?>"></script>

<script>
    $(function () {
        $('#jstree').on('select_node.jstree', function (e, data) {
            location.href = '?cat=' + data.node.data.id;
        }).jstree();

        $('#paginator').pagination({
            items: <?=$count?>,
            itemsOnPage: 40,
            hrefTextPrefix: '?cat=<?=isset($_GET['cat']) ? $_GET['cat'] : ''?><?=isset($_GET['brands']) ? '&brands='.$_GET['brands'] : ''?><?=isset($_GET['sorder']) ? '&sorder='.$_GET['sorder'] : ''?>&page=',
            cssStyle: 'light-theme',
            currentPage: <?=isset($_GET['page']) ? $_GET['page'] : 1?>
        });
    });

    $('body').on('keyup', 'input[name="price"]', function () {
        $(this).val($(this).val().replace(',', '.'));
    });
    $('body').on('keyup', 'input[name="old_price"]', function () {
        $(this).val($(this).val().replace(',', '.'));
    });


</script>

<!-- Initialize the plugin: -->
<script type="text/javascript">
    $(document).ready(function () {
        $('select[name="category_more[]"]').multiselect();
    });
</script>


<script>
    function toggleCat(el) {
        const children = el.parentElement.querySelector('.cat-children');
        if (!children) return;

        if (children.style.display === 'none' || children.style.display === '') {
            children.style.display = 'block';
            el.textContent = '▼';
        } else {
            children.style.display = 'none';
            el.textContent = '▶';
        }
    }
</script>

<style>

    .category-tree {
        font-size: 14px;
    }

    .cat-item {
        margin: 4px 0;
    }

    .cat-children {
        margin-left: 20px;
        display: none;
    }

    .toggle {
        cursor: pointer;
        display: inline-block;
        width: 16px;
        color: #555;
        user-select: none;
    }

    .toggle.empty {
        visibility: hidden;
    }
    .jstree-themeicon-custom {
        color: #ffac00
    }

    .jstree-default .jstree-anchor {
        height: auto;
    }

    .jstree-default .jstree-anchor span {
        white-space: break-spaces;
    }

    ul.multiselect-container.dropdown-menu {
        min-width: 250px;
    }

    ul.multiselect-container.dropdown-menu {
        max-height: 250px;
        overflow-y: auto;
    }
</style>
