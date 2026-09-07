<!-- BEGIN PAGE HEADER-->
<!-- BEGIN PAGE BAR -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="/<?= ADM_CONTROLLER ?>/menu/">Home</a>
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
<a href="/cp/subscribe/download" target="_blank">
    <button class="page-title btn btn-xs default btn-editable green-stripe" style=" position: absolute; right: 10px; top: 50px; ">Скачать xlsx</button>
</a>
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

<?php if (!empty($objects)) : ?>
    <div class="row">
        <div class="portlet bordered">
            <div class="accordion" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading" style="background-color: #fff;">
                        <h4 class="panel-title">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"
                               href="#collapse1"><i
                                        class="fa fa-plus"></i> <?=lang('Mailing list')?></a>
                        </h4>
                    </div>
                    <div id="collapse1" class="panel-collapse collapse">
                        <?php foreach ($objects as $item): ?>
                            <?php if ($item->mailing == 1) { ?>
                                <?= $item->email ?>,
                            <?php } endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="portlet light">
            <div class="portlet-body">
                <form action="<?= $o_path; ?>" method="post">
                    <div class="table-scrollable">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th width="100"> <?=lang('Number')?></th>
                                <th> <?=lang('Email')?></th>
                                <th width="180"> <?=lang('Enable sending')?></th>
                                <th width="180"><?=lang('Action')?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($objects as $item): ?>
                                <tr style="height: 51px;">
                                    <td class="align-middle">
                                        <?= $item->id ?>
                                    </td>
                                    <td class="align-middle">
                                        <?= $item->email ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php $cmod = (!empty($item->mailing)) ? 'checked' : '' ?>
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            <input data-col="mailing" data-table="<?= $table ?>"
                                                   type="checkbox" <?= $cmod ?> value="<?= $item->id ?>"
                                                   class="mine_change_check"><?=lang('Show in mailing list')?>
                                            <span></span>
                                        </label>
                                    </td>
                                    <td class="align-middle">
                                        <a href="<?= $del_path . $item->id . '/' ?>"
                                           class="btn btn-xs default btn-editable red-stripe mine_delete_row">
                                            <i class="glyphicon glyphicon-remove-circle"></i> <?=lang('Delete')?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>


