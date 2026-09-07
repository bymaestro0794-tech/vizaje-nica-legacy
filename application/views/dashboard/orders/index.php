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
                <!--<div class="col-md-12">-->
                <!--    <form action="/cp/orders/" method="get" enctype="text/plain">-->
                <!--        <div class="margin-bottom-5" style="text-align: right;">-->
                <!--            <?=lang('Search')?>-->
                <!--            <input type="text" style="width: 240px; padding-top: 1px; display: inline-block;"-->
                <!--                   class="form-control form-filter input-sm" name="query" value="">-->
                <!--            <button class="btn btn-sm btn-success filter-submit margin-bottom">-->
                <!--                <i class="fa fa-search"></i> <?=lang('Search')?>-->
                <!--            </button>-->
                <!--            <a href="/cp/orders/" class="btn btn-sm btn-default filter-cancel">-->
                <!--                <i class="fa fa-times"></i><?=lang('Search')?> </a>-->
                <!--        </div>-->
                <!--    </form>-->
                <!--</div>-->
                <div class="col-md-12">
                    <form action="/cp/orders/" method="get" enctype="text/plain">
                        <div class="margin-bottom-5" style="text-align: left;">
                            <div>
                                <?= lang('Search') ?>
                                <input type="text" style="width: 240px; padding-top: 1px; display: inline-block;" class="form-control form-filter input-sm" value="<?= !empty($_GET['query']) ? $_GET['query'] : '' ?>" name="query" value="">
    
                                <button class="btn btn-sm btn-success filter-submit margin-bottom">
                                    <i class="fa fa-search"></i> <?= lang('Search') ?>
                                </button>
                                <a href="/cp/orders/" class="btn btn-sm btn-default filter-cancel">
                                    <i class="fa fa-times"></i><?= lang('Search') ?> </a>
                            </div>
                            <div class="col-md-6" style="display:flex;align-items: center;margin: 20px 0; padding:0;width: 100%;max-width: 482px;">
                                <div style="flex:1;">
                                    <label class="control-label">Тип заказа</label>
                                </div>
                                <div style="flex:4;">
                                    <select name="isb2b" class="form-control select2" data-live-search="true" tabindex="-98">
                                        <option value="0">Тип заказа</option>
                                        <option value="1" <?= isset($_GET['isb2b']) ? ($_GET['isb2b'] == 1 ? 'selected' : '') : '' ?>>Оптовый заказ</option>
                                        <option value="2" <?= isset($_GET['isb2b']) ? ($_GET['isb2b'] == 2 ? 'selected' : '') : '' ?>>Не оптовый заказ</option>
    
                                    </select>
                                </div>
                            </div>
    
                        </div>
                    </form>
                </div>
                <?php if (!empty($objects)) : ?>
                <form action="<?= $o_path; ?>" method="post">
                    <div class="table-scrollable">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th width="120"> <?=lang('Date of creation')?></th>
                                <th width="120"> <?=lang('Id order')?></th>
                                <th width="80"> <?=lang('Payment')?></th>
                                <th width="60"> <?=lang('Client')?></th>
                                <th width="60"> <?=lang('Number')?></th>
                                <th width="80"> <?=lang('Status')?></th>
                                <th width="120"> <?=lang('Action')?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <? $statuses = [
	                            'new' => '<span class="label label-sm label-info">'.lang('new').' </span>',
	                            'progress' => '<span class="label label-sm label-warning"> '.lang('progress').' </span>',
	                            'finished' => '<span class="label label-sm label-danger"> '.lang('finished').' </span>',
                                'canceled' => '<span class="label label-sm label-danger"> '.lang('canceled').' </span>']
	                             ?>
                            <? $pay = [
                                '0' => '<span class="label label-sm label-danger"> '.lang('pay1'). '</span>',
                                '1' => '<span class="label label-sm label-warning"> '.lang('pay1').'</span>',
                                '2' => '<span class="label label-sm label-info"> '.lang('pay2').'</span>',
                                '3' => '<span class="label label-sm label-warning"> '.lang('pay3').'</span>'
                            ]
                            ?>
                            <?php foreach ($objects as $item): ?>
                                <tr style="height: 51px;">
                                    <td class="align-middle">
                                        <span><?=date('d.m.Y H:i:s', strtotime($item->added))?></span>
                                    </td>
                                    <td class="align-middle">
                                        <span><?=$item->order_id?></span>
                                    </td>
                                    <td class="align-middle"><?php if ($item->payment == 2 && $item->pay_success == 0){?><span class="label label-sm label-danger"> <?=lang('Payment error')?> </span><?php } else {?><?=$pay[$item->payment]?> <?php } ?></td>
                                    <td class="align-middle"><?=$item->name?> <?=$item->surname?></td>
                                    <td class="align-middle"><?=$item->phone?></td>
                                    <td class="align-middle"><?=$statuses[$item->status]?></td>

                                    <td class="align-middle">
                                        <a href="<?= $e_path . $item->id . '/' ?>"
                                           class="btn btn-xs default btn-editable green-stripe">
                                            <i class="fa fa-search"></i> <?=lang('Edit')?>
                                        </a>
                                        <!-- <a href="<?= $del_path . $item->id . '/' ?>"
                                           class="btn btn-xs default btn-editable red-stripe mine_delete_row">
                                            <i class="glyphicon glyphicon-remove-circle"></i> <?=lang('Delete')?>
                                        </a> -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<script>
    $('select[name="isb2b"]').on('change', function(e) {
        let value = $(e.target).val();
        let query = $('input[name="query"]').val();
        if (query) {
            window.location.href = '/cp/orders/?isb2b=' + value + '&query=' + query;
        } else {
            window.location.href = '/cp/orders/?isb2b=' + value;
        }

    })
</script>
