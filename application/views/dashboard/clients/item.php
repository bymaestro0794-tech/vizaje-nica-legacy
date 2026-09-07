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
                            <a
                                href="#tab_1_1"
                                data-toggle="tab"
                            >
                                <?= lang('General information') ?>
                            </a>
                        </li>

                        <li>
                            <a
                                href="#tab_1_2"
                                data-toggle="tab"
                            >
                                Промокоды
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="tab_1_1">
                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                    <tr>
                                        <td width="200"><?=lang('Name')?></td>
                                        <td>
                                            <input type="text" name="name" class="form-control" value="<?=$item->name?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Surname')?></td>
                                        <td>
                                            <input type="text" name="surname" class="form-control" value="<?=$item->surname?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Email')?></td>
                                        <td>
                                            <input type="email" name="email" class="form-control" value="<?=$item->email?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Phone')?></td>
                                        <td>
                                            <input type="text" name="phone" class="form-control" value="<?=$item->phone?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Address')?></td>
                                        <td>
                                            <input type="text" name="address" class="form-control" value="<?=$item->address?>" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('City')?></td>
                                        <td>
                                            <input type="text" name="city" class="form-control" value="<?=$item->city?>" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Password')?></td>
                                        <td>
                                            <input type="password"
                                                   name="password"
                                                   class="form-control"  minlength="8" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i class="fa fa-check"></i> <?=lang('Edit')?>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div
                            class="tab-pane fade"
                            id="tab_1_2"
                        >

                            <?php if (!empty($promocode_events)) : ?>

                                <div class="table-scrollable">
                                    <table
                                        class="
                                            table
                                            table-bordered
                                            table-striped
                                            table-hover
                                        "
                                    >
                                        <thead>
                                            <tr>
                                                <th width="160">
                                                    Дата
                                                </th>

                                                <th width="140">
                                                    Промокод
                                                </th>

                                                <th width="190">
                                                    Событие
                                                </th>

                                                <th width="110">
                                                    Заказ
                                                </th>

                                                <th width="130">
                                                    Сумма
                                                </th>

                                                <th width="130">
                                                    Скидка
                                                </th>

                                                <th width="130">
                                                    Итого
                                                </th>

                                                <th>
                                                    Причина
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            <?php foreach (
                                                $promocode_events as $event
                                            ) : ?>

                                                <?php
                                                $eventTitle =
                                                    $event->event_type;

                                                $eventClass =
                                                    'label-default';

                                                switch (
                                                    $event->event_type
                                                ) {
                                                    case 'APPLY_SUCCESS':
                                                        $eventTitle =
                                                            'Промокод применён';

                                                        $eventClass =
                                                            'label-success';
                                                        break;

                                                    case 'APPLY_FAILED':
                                                        $eventTitle =
                                                            'Ошибка применения';

                                                        $eventClass =
                                                            'label-danger';
                                                        break;

                                                    case 'REMOVE':
                                                        $eventTitle =
                                                            'Промокод удалён';

                                                        $eventClass =
                                                            'label-warning';
                                                        break;

                                                    case 'ORDER_CREATED':
                                                        $eventTitle =
                                                            'Заказ создан';

                                                        $eventClass =
                                                            'label-info';
                                                        break;

                                                    case 'PAYMENT_CONFIRMED':
                                                        $eventTitle =
                                                            'Оплата подтверждена';

                                                        $eventClass =
                                                            'label-success';
                                                        break;
                                                }

                                                $reasonTitle = '';

                                                if (!empty($event->reason)) {
                                                    switch (
                                                        $event->reason
                                                    ) {
                                                        case 'PROMO_NOT_FOUND':
                                                            $reasonTitle =
                                                                'Промокод не найден';
                                                            break;

                                                        case 'PROMO_NOT_APPLICABLE':
                                                            $reasonTitle =
                                                                'Промокод не применим';
                                                            break;

                                                        case 'PROMO_EXPIRED':
                                                            $reasonTitle =
                                                                'Срок действия истёк';
                                                            break;

                                                        default:
                                                            $reasonTitle =
                                                                $event->reason;
                                                            break;
                                                    }
                                                }
                                                ?>

                                                <tr>
                                                    <td>
                                                        <?= !empty($event->created_at)
                                                            ? date(
                                                                'd.m.Y H:i:s',
                                                                strtotime(
                                                                    $event->created_at
                                                                )
                                                            )
                                                            : '—' ?>
                                                    </td>

                                                    <td>
                                                        <strong>
                                                            <?= htmlspecialchars(
                                                                $event->code,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>
                                                        </strong>
                                                    </td>

                                                    <td>
                                                        <span
                                                            class="
                                                                label
                                                                <?= $eventClass ?>
                                                            "
                                                        >
                                                            <?= htmlspecialchars(
                                                                $eventTitle,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <?php if (
                                                            !empty($event->order_id)
                                                        ) : ?>

                                                            <a
                                                                href="/cp/orders/item/<?= (int) $event->order_id ?>"
                                                                target="_blank"
                                                            >
                                                                #<?= (int) $event->order_id ?>
                                                            </a>

                                                        <?php else : ?>

                                                            —

                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <?= number_format(
                                                            (float) $event->cart_subtotal,
                                                            2,
                                                            '.',
                                                            ' '
                                                        ) ?>
                                                        MDL
                                                    </td>

                                                    <td>
                                                        <?php if (
                                                            (float) $event->discount_amount > 0
                                                        ) : ?>

                                                            −<?= number_format(
                                                                (float) $event->discount_amount,
                                                                2,
                                                                '.',
                                                                ' '
                                                            ) ?>
                                                            MDL

                                                        <?php else : ?>

                                                            —

                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                        <strong>
                                                            <?= number_format(
                                                                (float) $event->final_total,
                                                                2,
                                                                '.',
                                                                ' '
                                                            ) ?>
                                                            MDL
                                                        </strong>
                                                    </td>

                                                    <td>
                                                        <?= $reasonTitle !== ''
                                                            ? htmlspecialchars(
                                                                $reasonTitle,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            )
                                                            : '—' ?>
                                                    </td>
                                                </tr>

                                            <?php endforeach; ?>

                                        </tbody>
                                    </table>
                                </div>

                            <?php else : ?>

                                <div
                                    class="alert alert-info"
                                    style="margin-top: 20px;"
                                >
                                    У этого пользователя пока нет истории
                                    использования промокодов.
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
