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
                                                       class="form-control"
                                                       value='<?= $item->{'title' . strtoupper($lang)} ?>' required>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200"><?= lang('Color Text') ?></td>
                                        <td>
                                            <input type="color" name="ColorText" style="max-width: 80px"
                                                   value="<?= $item->ColorText ?>"
                                                   class="form-control">
                                        </td>
                                    </tr>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Desc') ?> <?= strtoupper($lang) ?></td>
                                            <td>
                                                <input type="text" name="desc<?= strtoupper($lang) ?>"
                                                       class="form-control"
                                                       value="<?= $item->{'desc' . strtoupper($lang)} ?>">
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Button') ?> <?= strtoupper($lang) ?> </td>
                                            <td>
                                                <input type="text" name="button<?= strtoupper($lang) ?>"
                                                       class="form-control"
                                                       value="<?= $item->{'button' . strtoupper($lang)} ?>">
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200"><?= lang('Color Button') ?></td>
                                        <td>
                                            <input type="color" name="ColorButton" style="max-width: 80px"
                                                   value="<?= $item->ColorButton ?>"
                                                   class="form-control">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Color Button Text') ?></td>
                                        <td>
                                            <input type="color" name="ColorButtonText" style="max-width: 80px"
                                                   value="<?= $item->ColorButtonText ?>"
                                                   class="form-control">
                                        </td>
                                    </tr>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Url') ?> <?= strtoupper($lang) ?></td>
                                            <td>
                                                <input type="text" name="uri<?= strtoupper($lang) ?>"
                                                       class="form-control"
                                                       value="<?= $item->{'uri' . strtoupper($lang)} ?>">
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200"><?= lang('Youtube') ?></td>
                                        <td>
                                            <input type="text" name="youtube" value="<?= $item->youtube ?>"
                                                   class="form-control">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Photo') ?></td>
                                        <td>
                                            <input type="file" name="img" id="file" class="form-control">
                                            <div class="note note-warning"
                                                 style="margin-bottom: 0px; margin-top: 10px;">
                                                <p>
                                                    <?= lang('Allowable sizes') ?>: 1900x720
                                                </p>
                                            </div>
                                            <?php if (!empty($item->img)): ?>
                                                <?php if (strpos($item->img, '.mp4')) { ?>
                                                    <div class="mt-element-card mt-element-overlay item">
                                                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                                            <div class="mt-card-item">
                                                                <video width="400" controls style=" max-width: 100%;">
                                                                    <source src="/public/menu/video/<?= $item->img ?>"
                                                                            type="video/mp4">
                                                                    <source src="/public/menu/video/<?= $item->img ?>"
                                                                            type="video/ogg">
                                                                </video>
                                                                <a class="btn red mine_delete_photo"
                                                                   data-table="<?= $table ?>"
                                                                   data-id="<?= $item->id ?>"
                                                                   data-col="img"
                                                                   href="javascript:;">
                                                                    <i class="fa fa-ban"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } elseif (strpos($item->img, '.gif')) { ?>
                                                    <?php $src = '/public/menu/video/' . $item->img ?>
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
                                                                                   data-col="img"
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
                                                <?php } else { ?>
                                                    <?php $src = newthumbs($item->img, $table, 250, 250, '250x250x1', 1) ?>
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
                                                                                   data-col="img"
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
                                                <?php } endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Photo') ?> Mob</td>
                                        <td>
                                            <input type="file" name="imgMob" id="file" class="form-control">
                                            <div class="note note-warning"
                                                 style="margin-bottom: 0px; margin-top: 10px;">
                                                <p>
                                                    <?= lang('Allowable sizes') ?>: 420x480
                                                </p>
                                            </div>
                                            <?php if (!empty($item->imgMob)): ?>
                                                <?php if (strpos($item->imgMob, '.mp4')) { ?>
                                                    <div class="mt-element-card mt-element-overlay item">
                                                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                                            <div class="mt-card-item">
                                                                <video width="400" controls style=" max-width: 100%;">
                                                                    <source src="/public/menu/video/<?= $item->imgMob ?>"
                                                                            type="video/mp4">
                                                                    <source src="/public/menu/video/<?= $item->imgMob ?>"
                                                                            type="video/ogg">
                                                                </video>
                                                                <a class="btn red mine_delete_photo"
                                                                   data-table="<?= $table ?>"
                                                                   data-id="<?= $item->id ?>"
                                                                   data-col="imgMob"
                                                                   href="javascript:;">
                                                                    <i class="fa fa-ban"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } elseif (strpos($item->imgMob, '.gif')) { ?>
                                                    <?php $src = '/public/menu/video/' . $item->imgMob ?>
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
                                                                                   data-col="imgMob"
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
                                                <?php } else { ?>
                                                    <?php $src = newthumbs($item->imgMob, $table, 250, 250, '250x250x1', 1) ?>
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
                                                                                   data-col="imgMob"
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
                                                <?php } endif; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td width="200"><?= lang('Video') ?></td>
                                        <td>
                                            <input type="file" name="video" id="file" class="form-control">
                                            <div class="note note-warning"
                                                 style="margin-bottom: 0px; margin-top: 10px;">
                                                <p>
                                                    <?= lang('Allowable sizes') ?>: .mp4 420x480px
                                                </p>
                                            </div>
                                            <?php if (!empty($item->video)): ?>
                                                <?php if (strpos($item->video, '.mp4')) { ?>
                                                    <div class="mt-element-card mt-element-overlay item">
                                                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                                            <div class="mt-card-item">
                                                                <video width="400" controls style=" max-width: 100%;">
                                                                    <source src="/public/sliders/<?= $item->video ?>"
                                                                            type="video/mp4">
                                                                    <source src="/public/sliders/<?= $item->video ?>"
                                                                            type="video/ogg">
                                                                </video>
                                                                <a class="btn red mine_delete_photo"
                                                                   data-table="<?= $table ?>"
                                                                   data-id="<?= $item->id ?>"
                                                                   data-col="video"
                                                                   href="javascript:;">
                                                                    <i class="fa fa-ban"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i
                                                        class="fa fa-check"></i><?= lang('Edit') ?>
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
