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
            <div class="panel-body">
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="tab_1_1">
                        <form method="post" enctype="multipart/form-data">
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="portlet yellow-crusta box">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-cogs"></i><?= lang('Information') ?>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Order') ?>:</div>
                                                    <div class="col-md-7 value"> #<?= $item->order_id ?>
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Status') ?>:</div>
                                                    <div class="col-md-7 value">
                                                        <?
                                                        $status = [
                                                            'new' => lang('new'),
                                                            'progress' => lang('progress'),
                                                            'finished' => lang('finished'),
                                                            'canceled' => lang('canceled')
                                                        ]
                                                        ?>
                                                        <select name="status" id="status" class="form-control">
                                                            <? foreach ($status as $key => $title) { ?>
                                                                <option value="<?= $key ?>" <?= ($item->status == $key) ? 'selected' : '' ?>><?= $title ?></option>
                                                            <? } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Delivery') ?>:</div>
                                                    <div class="col-md-7 value">
                                                        <? $delivery = [
                                                            '1' => lang('Delivery1'),
                                                            '2' => lang('Delivery2'),
                                                            '3' => lang('Delivery3'),
                                                        ] ?>
                                                        <?= $delivery[$item->delivery] ?>
                                                        <?php if ($item->delivery == 1) { ?>
                                                            <?php foreach ($stores as $store) { ?>
                                                                <?php if ($store->id == $item->stores) { ?>
                                                                    <br>
                                                                    <?=$store->title?> <?=$store->text?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Payment methods') ?>:</div>
                                                    <div class="col-md-7 value">
                                                        <? $pay = $pay = [
                                                            '0' => '<span class="label label-sm label-danger"> ' . lang('pay1') . '</span>',
                                                            '1' => '<span class="label label-sm label-warning"> ' . lang('pay1') . '</span>',
                                                            '2' => '<span class="label label-sm label-info"> ' . lang('pay2') . '</span>',
                                                            '3' => '<span class="label label-sm label-warning"> ' . lang('pay3') . '</span>'
                                                        ]
                                                        ?>
                                                        <?php if ($item->payment == 2 && $item->pay_success == 0){?><span class="label label-sm label-danger"> <?=lang('Payment error')?> </span><?php } else {?><?=$pay[$item->payment]?> <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Data comenzii') ?>:</div>
                                                    <div class="col-md-7 value"><?= date('d.m.Y H:i:s', strtotime($item->added)) ?></div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Delivery') ?></div>
                                                    <div class="col-md-7 value"> <?= $item->delivery_price ?> (MDL)
                                                    </div>
                                                </div>
                                                <?php if (!empty($item->voucher)) { ?>
                                                    <div class="row static-info">
                                                        <div class="col-md-5 name"> <?= lang('Voucher') ?></div>
                                                        <div class="col-md-7 value"> 10 (MDL)
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <?php if (!empty($item->promo_code) && (float) $item->promo_discount > 0) { ?>
                                                    <div class="row static-info">
                                                        <div class="col-md-5 name">
                                                            Промокод
                                                        </div>

                                                        <div class="col-md-7 value">
                                                            <?= html_escape($item->promo_code) ?>
                                                            <strong>
                                                                −<?= number_format(
                                                                    (float) $item->promo_discount,
                                                                    2,
                                                                    '.',
                                                                    ''
                                                                ) ?> MDL
                                                            </strong>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <?php if (!empty($item->bonus_plus)) { ?>
                                                    <div class="row static-info">
                                                        <div class="col-md-5 name"> <?= lang('Bonus') ?></div>
                                                        <div class="col-md-7 value"> + <?=$item->bonus_plus?>
                                                        </div>
                                                    </div>
                                                <?php }?>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Total') ?></div>
                                                    <div class="col-md-7 value"> <?= $item->total + $item->delivery_price - (!empty($item->bonus_minus)?$item->bonus_minus:0) ?>
                                                        (MDL)
                                                    </div>
                                                </div>
                                                <?php if ($item->isb2b) { ?>
                                                    <div class="row static-info text-center" style="margin-top: 40px;">
                                                        <span class="label label-sm label-primary" style="font-size: 20px;"> <?= lang('B2b Order') ?> </span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="portlet blue-hoki box">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-cogs"></i><?= lang('Customer Information') ?>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Title') ?>:</div>
                                                    <div class="col-md-7 value"> <?= $item->name ?> <?= $item->surname ?></div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Email') ?>:</div>
                                                    <div class="col-md-7 value"> <?= $item->email ?> </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Phone') ?>:</div>
                                                    <div class="col-md-7 value"> <?= $item->phone ?> </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> <?= lang('Address') ?>:</div>
                                                    <div class="col-md-7 value"> <?= $item->address ?>  </div>
                                                </div>
                                               <div class="row static-info">
                                                    <div class="col-md-5 name">
                                                        <?= lang('Notes') ?>
                                                    </div>
                                                
                                                    <div class="col-md-7 value">
                                                        <?= !empty($item->message)
                                                            ? nl2br(
                                                                html_escape(
                                                                    $item->message
                                                                )
                                                            )
                                                            : '' ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        <div class="portlet grey-cascade box">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-cogs"></i><?= lang('Basket') ?>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th><?= lang('Code') ?></th>
                                                                <th><?= lang('Barcode') ?></th>
                                                                <th>WarehouseName</th>
                                                                <th><?= lang('Product') ?></th>
                                                                <th><?= lang('Price') ?></th>
                                                                <th width="80"><?= lang('Qty') ?></th>
                                                                <th width="120">Промокод</th>
                                                                <th width="120"><?= lang('Total price') ?></th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            <?php foreach ($products as $product) { ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php if (!empty($product->product->variable)) { ?>
                                                                            <?= $product->product->variable->SKU ?>
                                                                        <?php } else { ?>
                                                                            <?= $product->product->SKU ?>
                                                                        <?php } ?>
                                                                    </td>

                                                                    <td>
                                                                        <?php if (!empty($product->product->variable)) { ?>
                                                                            <?= $product->product->variable->barcode ?>
                                                                        <?php } else { ?>
                                                                            <?= $product->product->barcode ?>
                                                                        <?php } ?>
                                                                    </td>

                                                                    <td>
                                                                        <?php if (!empty($product->product->variable)) { ?>
                                                                            <?= $product->product->variable->WarehouseName ?>
                                                                        <?php } else { ?>
                                                                            <?= $product->product->WarehouseName ?>
                                                                        <?php } ?>
                                                                    </td>

                                                                    <td>
                                                                        <?php if (!empty($product->product->variable)) { ?>
                                                                            <a
                                                                                href="/cp/products/item/<?= !empty($product->product->product_id)
                                                                                    ? $product->product->product_id
                                                                                    : $product->product_id ?>"
                                                                                target="_blank"
                                                                            >
                                                                                <?= $product->product->title ?>
                                                                            </a>

                                                                            <br>

                                                                            Cod furnizor:
                                                                            <?= $product->product->variable->SKU ?>.
                                                                            Volume:
                                                                            <?= $product->product->variable->titleRO ?>
                                                                        <?php } else { ?>
                                                                            <a
                                                                                href="/cp/products/item/<?= !empty($product->product->product_id)
                                                                                    ? $product->product->product_id
                                                                                    : $product->product_id ?>"
                                                                                target="_blank"
                                                                            >
                                                                                <?= $product->product->title ?>
                                                                            </a>
                                                                        <?php } ?>
                                                                    </td>

                                                                    <td>
                                                                        <?= number_format(
                                                                            (float) $product->price,
                                                                            2,
                                                                            '.',
                                                                            ''
                                                                        ) ?> MDL
                                                                    </td>

                                                                    <td>
                                                                        <?= (int) $product->qty ?>
                                                                    </td>

                                                                    <td>
                                                                        <?php if ((float) $product->promo_discount > 0) { ?>
                                                                            <span style="color: #b26f4a;">
                                                                                −<?= number_format(
                                                                                    (float) $product->promo_discount,
                                                                                    2,
                                                                                    '.',
                                                                                    ''
                                                                                ) ?> MDL
                                                                            </span>
                                                                        <?php } else { ?>
                                                                            —
                                                                        <?php } ?>
                                                                    </td>

                                                                    <td>
                                                                        <strong>
                                                                            <?= number_format(
                                                                                (float) $product->total,
                                                                                2,
                                                                                '.',
                                                                                ''
                                                                            ) ?> MDL
                                                                        </strong>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <button class="btn btn-primary"><?= lang('Save') ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('.return_pay').click(function () {
        let prompt = window.confirm("<?=lang('Confirm refund')?>");
        if (prompt) {
            return true;
        } else {
            return false;
        }
    });
</script>
