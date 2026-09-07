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
                       
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="tab_1_1">
                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                        
                                        <tr>
                                        <td width="200"><?= lang('Title') ?> *</td>
                                        <td>
                                            <input type="text" name="name" value="<?= $item->{'name'} ?>"
                                                   class="form-control" required>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td width="200"><?= lang('DateStart') ?> *</td>
                                        <td>
                                            <input type="date" name="date_start" value="<?= $item->{'date_start'} ?>"
                                                   class="form-control" style="width: 140px;" required>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td width="200"><?= lang('DateEnd') ?> *</td>
                                        <td>
                                            <input type="date" name="date_end" value="<?= $item->{'date_end'} ?>"
                                                   class="form-control" style="width: 140px;" required>
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
