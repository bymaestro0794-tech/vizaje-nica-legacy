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
                    <ul class="nav nav-pills">
                        <li class="active">
                            <a href="#tab_1_1" data-toggle="tab"><?= lang('General information') ?></a>
                        </li>
                        <li>
                            <a href="#tab_1_2" data-toggle="tab"><?= lang('SEO') ?></a>
                        </li>
                        <?php if ($item->step == 2){?>
                        <li>
                            <a href="#tab_1_3" data-toggle="tab"><?= lang('Banner') ?></a>
                        </li>
                        <?php } ?>
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
                                                <option value="0"><?= lang('Select category') ?></option>
                                                <?php foreach ($categories as $category) { ?>
                                                    <?php if ($category->id != $item->id) { ?>
                                                        <option value="<?= $category->id ?>" <?= $category->id == $item->parent_id ? 'selected' : '' ?>> <?= $category->titleRO ?> </option>
                                                        <?php if (!empty($category->children)) { ?>
                                                            <?php foreach ($category->children as $child) { ?>
                                                                <?php if ($child->id != $item->id) { ?>
                                                                    <option value="<?= $child->id ?>" <?= $child->id == $item->parent_id ? 'selected' : '' ?>>
                                                                        - <?= $child->titleRO ?> </option>
                                                                <?php } ?>
                                                            <?php } ?>
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
                                                    <option value="<?= $type->id ?>" <?= $type->id == $item->feature_type ? 'selected' : '' ?>> <?= $type->name ?> </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">Название (для интерфейса) <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="title<?= strtoupper($lang) ?>"
                                                       class="form-control"
                                                       value="<?= $item->{'title' . strtoupper($lang)} ?>" required>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">H1 заголовок <?= strtoupper($lang) ?></td>
                                            <td>
                                                <input type="text" name="h1<?= strtoupper($lang) ?>"
                                                       class="form-control"
                                                       value="<?= $item->{'h1' . strtoupper($lang)} ?>">
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">Название для хлебных крошек <?= strtoupper($lang) ?></td>
                                            <td>
                                                <input type="text" name="breadcrumbTitle<?= strtoupper($lang) ?>"
                                                       class="form-control"
                                                       value="<?= $item->{'breadcrumbTitle' . strtoupper($lang)} ?>">
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
                                            <?php if (!empty($item->img)): ?>
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
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i
                                                        class="fa fa-check"></i> <?= lang('Edit') ?>
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
                                                       class="form-control"
                                                       value="<?= $item->{'seoTitle' . strtoupper($lang)} ?>">
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Keywords') ?> <?= strtoupper($lang) ?></td>
                                            <td>
                                                <textarea name="seoKeywords<?= strtoupper($lang) ?>" cols="30" rows="3"
                                                          class="form-control"><?= $item->{'seoKeywords' . strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Description') ?> <?= strtoupper($lang) ?></td>
                                            <td>
                                                <textarea name="seoDesc<?= strtoupper($lang) ?>" cols="30" rows="3"
                                                          class="form-control"><?= $item->{'seoDesc' . strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">SEO-текст <?= strtoupper($lang) ?></td>
                                            <td>
                                                <textarea name="seoText<?= strtoupper($lang) ?>" cols="30" rows="6"
                                                          class="form-control ckeditor"><?= $item->{'seoText' . strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <? } ?>
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
                        <div class="tab-pane fade" id="tab_1_3">
                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Title') ?> <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="banner[title<?= strtoupper($lang) ?>]"
                                                       class="form-control"
                                                       value="<?= $item->banner->{'title' . strtoupper($lang)} ?>" >
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Desc') ?> <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="banner[desc<?= strtoupper($lang) ?>]"
                                                       class="form-control"
                                                       value="<?= $item->banner->{'desc' . strtoupper($lang)} ?>" >
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Url') ?> <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="banner[uri<?= strtoupper($lang) ?>]"
                                                       class="form-control"
                                                       value="<?= $item->banner->{'title' . strtoupper($lang)} ?>" >
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200"><?= lang('Photo') ?></td>
                                        <td>
                                            <input type="file" name="banner" id="file" class="form-control">
                                            <div class="note note-warning"
                                                 style="margin-bottom: 0px; margin-top: 10px;">
                                                <p>
                                                    <?= lang('Allowable sizes') ?>: 250x400 px
                                                </p>
                                            </div>
                                            <?php if (!empty($item->banner->img)): ?>
                                                <?php $src = newthumbs($item->banner->img, $table, 250, 250, '250x250x1', 1) ?>
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
                                                                               data-table="category_banner"
                                                                               data-id="<?= $item->banner->id ?>"
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
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i
                                                        class="fa fa-check"></i> <?= lang('Edit') ?>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Title') ?> <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="banner2[title<?= strtoupper($lang) ?>]"
                                                       class="form-control"
                                                       value="<?= $item->banner2->{'title' . strtoupper($lang)} ?>" >
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Desc') ?> <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="banner2[desc<?= strtoupper($lang) ?>]"
                                                       class="form-control"
                                                       value="<?= $item->banner2->{'desc' . strtoupper($lang)} ?>" >
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Url') ?> <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="banner2[uri<?= strtoupper($lang) ?>]"
                                                       class="form-control"
                                                       value="<?= $item->banner2->{'uri' . strtoupper($lang)} ?>" >
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200"><?= lang('Photo') ?></td>
                                        <td>
                                            <input type="file" name="banner2" id="file" class="form-control">
                                            <div class="note note-warning"
                                                 style="margin-bottom: 0px; margin-top: 10px;">
                                                <p>
                                                    <?= lang('Allowable sizes') ?>: 250x400 px
                                                </p>
                                            </div>
                                            <?php if (!empty($item->banner2->img)): ?>
                                                <?php $src = newthumbs($item->banner2->img, $table, 250, 250, '250x250x1', 1) ?>
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
                                                                               data-table="category_banner"
                                                                               data-id="<?= $item->banner2->id ?>"
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
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i
                                                        class="fa fa-check"></i> <?= lang('Edit') ?>
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
