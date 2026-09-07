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
                                                <td><?= lang('Parent category') ?></td>
                                                <td>
                                                    <select name="parent_id" id="" class="form-control">
                                                        <option value="0"> <?= lang('Select category') ?> </option>
                                                        <?php foreach ($categories as $category) { ?>
                                                            <option value="<?= $category->id ?>"> <?= $category->titleRO ?> </option>
                                                            <?php if (!empty($category->children)) { ?>
                                                                <?php foreach ($category->children as $child) { ?>
                                                                    <option value="<?= $child->id ?>"> - <?= $child->titleRO ?> </option>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?= lang('Feature Type') ?></td>
                                                <td>
                                                    <select name="feature_type" id="" class="form-control">
                                                        <option value="0"> <?= lang('Select type') ?> </option>
                                                        <?php foreach ($types as $type) { ?>
                                                            <option value="<?= $type->id ?>"> <?= $type->name ?> </option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200">Название (для интерфейса) <?= strtoupper($lang) ?> *</td>
                                                    <td>
                                                        <input type="text" name="title<?= strtoupper($lang) ?>"
                                                               class="form-control" required>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200">H1 заголовок <?= strtoupper($lang) ?></td>
                                                    <td>
                                                        <input type="text" name="h1<?= strtoupper($lang) ?>"
                                                               class="form-control">
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200">Название для хлебных крошек <?= strtoupper($lang) ?></td>
                                                    <td>
                                                        <input type="text" name="breadcrumbTitle<?= strtoupper($lang) ?>"
                                                               class="form-control">
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <tr>
                                                <td width="200"><?= lang('Photo') ?></td>
                                                <td>
                                                    <input type="file" name="img" id="file" class="form-control">
                                                    <div class="note note-warning"
                                                         style="margin-bottom: 0px; margin-top: 10px;">
                                                        <p>
                                                            <?= lang('Allowable sizes') ?>: 820x600 px
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
                                                    <td width="200">SEO Title (&lt;title&gt;) <?= strtoupper($lang) ?></td>
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
                                                        <textarea name="seoKeywords<?= strtoupper($lang) ?>" cols="30" rows="3"
                                                                  class="form-control"></textarea>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200"><?= lang('Description') ?> <?= strtoupper($lang) ?></td>
                                                    <td>
                                                        <textarea name="seoDesc<?= strtoupper($lang) ?>" cols="30" rows="3"
                                                                  class="form-control"></textarea>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            <? foreach (language(true) as $lang) { ?>
                                                <tr>
                                                    <td width="200">SEO-текст <?= strtoupper($lang) ?></td>
                                                    <td>
                                                        <textarea name="seoText<?= strtoupper($lang) ?>" cols="30" rows="6"
                                                                  class="form-control ckeditor"></textarea>
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

<?php if (!empty($objects)) : ?>
    <div class="row">
        <div class="portlet light">
            <div class="portlet-body">
                <form action="<?= $o_path; ?>" method="post">
                    <div class="table-scrollable">
                        <table class="table table-bordered table-striped table-hover tree">
                            <thead>
                            <tr>
                                <th> <?= lang('Title') ?></th>
                                <th width="190"></th>
                                <th width="180"></th>
                                <th width="180"></th>
                                <th width="305"> <?= lang('Action') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php admin_categories_tree($objects, $e_path, $del_path, 0) ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn green"><i class="fa fa-check"></i> <?= lang('Refresh order') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<link rel="stylesheet" href="/static/assets/plugins/treeGrid/css/jquery.treegrid.css">
<script src="/static/assets/plugins/treeGrid/js/jquery.treegrid.js"></script>
<script>
    $(function () {
        $('.tree').treegrid({
            initialState: 'collapsed',
            treeColumn: 0,
        });
    });
</script>
