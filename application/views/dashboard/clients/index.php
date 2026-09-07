<!-- BEGIN PAGE HEADER-->
<!-- BEGIN PAGE BAR -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="/<?= ADM_CONTROLLER ?>/menu/"><?=lang('Home')?></a>
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
                            <div class="tab-content">
                                <div class="tab-pane fade active in" id="tab_1_1">
                                    <div class="table-scrollable">
                                        <table class="table table-bordered table-striped table-hover">
                                            <tbody>
                                            <tr>
                                                <td width="200"><?=lang('Name')?></td>
                                                <td>
                                                    <input type="text" name="name" class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200"><?=lang('Email')?></td>
                                                <td>
                                                    <input type="email" name="email" class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200"><?=lang('Phone')?></td>
                                                <td>
                                                    <input type="text" name="phone" class="form-control" required>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200"><?=lang('Password')?></td>
                                                <td>
                                                    <input type="password"
                                                           name="password"
                                                           class="form-control"
                                                           required="required" minlength="8" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="200">&nbsp;</td>
                                                <td>
                                                    <button type="submit" class="btn green"><i class="fa fa-check"></i>
                                                        <?=lang('Add')?>
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
                <form action="">
                    <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-2">
                            <input class="form-control" name="search" value="<?=@$_GET['search']?>" type="text" placeholder="<?=lang('Search')?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn green"><i class="fa fa-check"></i> <?=lang('Search')?></button>
                            <? if(isset($_GET['search']) && !empty($_GET['search'])) {?>
                                <a href="<?= $path; ?>" type="submit" class="btn red"><i class="fa fa-remove"></i> <?=lang('Delete')?></a>
                            <?}?>
                        </div>
                    </div>
                </form>
                <form action="<?= $o_path; ?>" method="post">
                    <div class="table-scrollable">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th> <?=lang('Title')?></th>
                                <th> <?=lang('Email')?></th>
                                <th> <?=lang('Phone')?></th>
                                <th width="180"></th>
                                <th width="180"></th>
                                <th width="305"> <?=lang('Action')?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($objects as $item): ?>
                                <tr style="height: 51px; <?php if ($item->code != '1'){?> background: red; <?php } ?>">
                                    <td class="align-middle"><a style="font-weight: 900;"
                                                                href="<?= $e_path . $item->id; ?>"><?= $item->name ?></a>
                                    </td>
                                    <td class="align-middle"><?= $item->email ?>
                                    </td>
                                    <td class="align-middle"><?= $item->phone ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php $cmod = (!empty($item->active)) ? 'checked' : '' ?>
                                        <label class="mt-checkbox mt-checkbox-outline">
                                            <input type="checkbox" <?= $cmod ?> value="<?= $item->id ?>"
                                                   data-col="active" data-table="<?= $table ?>"
                                                   class="mine_change_check"><?=lang('Active')?>
                                            <span></span>
                                        </label>
                                    </td>
                                    <td class="align-middle">
                                            <?php $cmod = (!empty($item->isb2b)) ? 'checked' : '' ?>
                                            <label class="mt-checkbox mt-checkbox-outline">
                                                <input type="checkbox" <?= $cmod ?> value="<?= $item->id ?>" data-col="isb2b" data-table="<?= $table ?>" class="mine_change_check"><?= lang('B2b') ?>
                                                <span></span>
                                            </label>
                                        </td>
                                    <td class="align-middle">
                                        <a href="<?= $e_path . $item->id . '/' ?>"
                                           class="btn btn-xs default btn-editable green-stripe">
                                            <i class="glyphicon glyphicon-edit"></i> <?=lang('Edit')?>
                                        </a>
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
                <div id="paginator" style="margin-top: 20px;"></div>
            </div>
        </div>
    </div>
<?php endif; ?>
<link rel="stylesheet" href="/app/plugins/pagination/simplePagination.css?t=<?=time()?>">
<script src="/app/plugins/pagination/jquery.simplePagination.js?t=<?=time()?>"></script>

<script>
    $(function () {

        $('#paginator').pagination({
            items: <?=$count?>,
            itemsOnPage: 40,
            hrefTextPrefix: '?page=',
            cssStyle: 'light-theme',
            currentPage: <?=isset($_GET['page']) ? $_GET['page'] : 1?>
        });
    });
</script>
