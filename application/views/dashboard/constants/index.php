<style>
    .portlet>.portlet-title>.tools {
        float: right;
        display: block;
        padding: 12px 0 8px;
        width: 85%;
        position: absolute;
    }
    .portlet.box>.portlet-title>.tools>a.expand {
        background: url(/static/assets/global/img/portlet-expand-icon-white.png) no-repeat right;
        width: 100%;
    }
    .portlet.box>.portlet-title>.tools>a.collapse {
        background: url(/static/assets/global/img/portlet-collapse-icon-white.png) no-repeat right;
        width: 100%;
    }
</style>
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


<?php if (isset($constants) && is_array($constants) && !empty($constants) ) : ?>
    <form method="post">
        <?php if($constants) { ?>
            <?php foreach ($constants as $key => $constant): ?>
                <div class="row">
                    <div class="portlet box blue-hoki bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-cogs"></i>
                                <span class="caption-subject bold uppercase"><?= str_replace(["_"], " ", $key) ?></span>
                                <span class="caption-helper" style="color: #ffffff"><?//= $constant->description ?></span>
                            </div>
                            <div class="tools">
                                <a href="javascript:;" class="expand"></a>
                            </div>
                        </div>
                        <div class="portlet-body" style="display: none;">
                            <div class="table-scrollable">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="text-center">Русский (RU)</th>
                                        <th class="text-center">Romana (RO)</th>
<!--                                        <th class="text-center">English (EN)</th>-->
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if(!empty($constant)) { ?>
                                        <?php foreach($constant as $element) { ?>
                                            <?php switch ($element->fieldType) :
                                                case '0': ?>
                                                    <?php $dopclass = ''; ?>
                                                    <tr>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <input type="text" name="ru[<?= $element->id ?>]" value="<?= $element->RU ?>" class="form-control <?= $dopclass ?>">
                                                        </td>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <input type="text" name="ro[<?= $element->id ?>]" value="<?= $element->RO ?>" class="form-control <?= $dopclass ?>">
                                                        </td>
<!--                                                        <td>-->
<!--                                                            <label><b>--><?//= $element->name ?><!--</b></label>-->
<!--                                                            <input type="text" name="en[--><?//= $element->id ?><!--]" value="--><?//= $element->EN ?><!--" class="form-control --><?//= $dopclass ?><!--">-->
<!--                                                        </td>-->
                                                    </tr>
                                                    <?php break; ?>
                                                <?php case '1': ?>
                                                    <?php $dopclass = ''; ?>
                                                    <tr>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <textarea name="ru[<?= $element->id ?>]" cols="30" rows="6" class="form-control <?= $dopclass ?>"><?= $element->RU ?></textarea>
                                                        </td>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <textarea name="ro[<?= $element->id ?>]" cols="30" rows="6" class="form-control <?= $dopclass ?>"><?= $element->RO ?></textarea>
                                                        </td>
<!--                                                        <td>-->
<!--                                                            <label><b>--><?//= $element->name ?><!--</b></label>-->
<!--                                                            <textarea name="en[--><?//= $element->id ?><!--]" cols="30" rows="6" class="form-control --><?//= $dopclass ?><!--">--><?//= $element->EN ?><!--</textarea>-->
<!--                                                        </td>-->
                                                    </tr>
                                                    <?php break; ?>
                                                <?php case '2': ?>
                                                    <?php $dopclass = 'ckeditor'; ?>
                                                    <tr>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <textarea name="ru[<?= $element->id ?>]" cols="30" rows="6" class="form-control <?= $dopclass ?>"><?= $element->RU ?></textarea>
                                                        </td>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <textarea name="ro[<?= $element->id ?>]" cols="30" rows="6" class="form-control <?= $dopclass ?>"><?= $element->RO ?></textarea>
                                                        </td>
<!--                                                        <td>-->
<!--                                                            <label><b>--><?//= $element->name ?><!--</b></label>-->
<!--                                                            <textarea name="en[--><?//= $element->id ?><!--]" cols="30" rows="6" class="form-control --><?//= $dopclass ?><!--">--><?//= $element->EN ?><!--</textarea>-->
<!--                                                        </td>-->
                                                    </tr>
                                                    <?php break; ?>
                                                <?php case '4': ?>
                                                    <?php $dopclass = ''; ?>
                                                    <tr>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <input type="date" name="ru[<?= $element->id ?>]" value="<?= $element->RU ?>" class="form-control <?= $dopclass ?>">
                                                        </td>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <input type="date" name="ro[<?= $element->id ?>]" value="<?= $element->RO ?>" class="form-control <?= $dopclass ?>">
                                                        </td>
                                                    </tr>
                                                    <?php break; ?>
                                                <?php default: ?>
                                                    <?php $dopclass = 'ckeditor'; ?>
                                                    <tr>
                                                        <td>
                                                            <label><b><?= $element->name ?></b></label>
                                                            <textarea name="en[<?= $element->id ?>]" cols="30" rows="6" class="form-control <?= $dopclass ?>"><?= $element->RU ?></textarea>
                                                        </td>
                                                    </tr>
                                                    <?php break; ?>
                                                <?php endswitch; ?>
                                        <?php } ?>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php } ?>
        <button type="submit" class="btn green"><i class="fa fa-check"></i> Обновить</button>
    </form>
<?php endif; ?>
