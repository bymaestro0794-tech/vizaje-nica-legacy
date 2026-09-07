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
    <div class="portlet light">
        <div class="portlet-body">
            <form method="post" enctype="multipart/form-data">
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="tab_1_1">
                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                    <tr>
                                        <td width="200">Строка</td>
                                        <td>RO</td>
                                        <td>RU</td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Title')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                        <td>
                                            <input type="text" name="title1<?=strtoupper($lang)?>" value="<?= $item->{'title1'.strtoupper($lang)} ?>" class="form-control" >
                                        </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="descL1<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'descL1'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="descR1<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'descR1'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Information')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <input type="text" name="note1<?=strtoupper($lang)?>" value="<?= $item->{'note1'.strtoupper($lang)} ?>" class="form-control" >
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="text1<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'text1'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>

                                    <tr>
                                        <td width="200"><?=lang('Title')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <input type="text" name="title2<?=strtoupper($lang)?>" value="<?= $item->{'title2'.strtoupper($lang)} ?>" class="form-control" >
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="descL2<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'descL2'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="descR2<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'descR2'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>

                                    <tr>
                                        <td width="200"><?=lang('Title')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <input type="text" name="title3<?=strtoupper($lang)?>" value="<?= $item->{'title3'.strtoupper($lang)} ?>" class="form-control" >
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="descL3<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'descL3'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="descR3<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'descR3'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>

                                    <tr>
                                        <td width="200"><?=lang('Title')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <input type="text" name="title4<?=strtoupper($lang)?>" value="<?= $item->{'title4'.strtoupper($lang)} ?>" class="form-control" >
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="descL4<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'descL4'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200"><?=lang('Text')?> </td>
                                        <? foreach(language(true) as $lang){ ?>
                                            <td>
                                                <textarea name="descR4<?=strtoupper($lang)?>" cols="30" rows="3"
                                                          class="form-control ckeditor"><?= $item->{'descR4'.strtoupper($lang)} ?></textarea>
                                            </td>
                                        <?}?>
                                    </tr>
                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i class="fa fa-check"></i> <?=lang('Edit')?> </button>
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
