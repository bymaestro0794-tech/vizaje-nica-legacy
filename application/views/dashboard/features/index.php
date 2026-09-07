<div class="grid">
    <div class="feature_menu">
        <div class="add_button">
            <a href="#add_type" data-toggle="modal"><i class="fa fa-plus" style="color: green;"></i><?=lang("Features add type")?></a>
        </div>
        <div class="menu">
            <ul id="type_menu">
                <?php if (isset($types) && count($types)) : ?>
                    <?php foreach ($types as $t) : ?>
                        <li <?php if ($t->active) : ?>class="active"<?php endif; ?> id="sorder[]_<?= $t->id ?>">
                            <i class="glyphicon glyphicon-menu-hamburger"></i>
                            <a href="?type=<?= $t->id ?>"> <?= $t->name ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="features">
        <div class="header">
            <h3 class="header_type_name"><?= isset($type->name) ? $type->name : lang("Features add type product") ?></h3>
            <div style="float: right;">
                <i class="fa fa-gear" href="#change_type" data-toggle="modal"
                   style="color: grey; font-size: 20px; margin-right: 10px;"></i>
                <i class="fa fa-trash-o delete_type" data-type="<?= $type->id ?>"
                   style="color: red; font-size: 20px;"></i>
            </div>
        </div>
        <div class="add_feature">
            <a href="#add_feature" data-toggle="modal"><i class="fa fa-plus" style="color: green;"></i> <?=lang('Add specification')?></a>
        </div>
        <div class="table-scrollable">
            <table class="table table-bordered table-striped table-condensed flip-content" id="feature_table">
                <thead class="flip-content">
                <tr>
                    <th></th>
                    <th> <?=lang('Specification Name')?></th>
                    <th> <?=lang('Specification values')?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($features) && count($features)) { ?>
                    <?php foreach ($features as $f_id => $feature) { ?>
                        <tr id="sorder[]_<?= $f_id ?>" data-id="<?= $f_id ?>" data-type="<?= $feature['type'] ?>">
                            <td style="text-align: center" class="align-middle"><i
                                        class="glyphicon glyphicon-menu-hamburger sort"></i></td>
                            <td class="align-middle feature_name">
                                <?php /* ID:<?= $f_id ?><br>
                                Sorder: <?= $feature['sorder'] ?><br><br> */ ?>
                                <?= $feature['name_RO'] ?>
                            </td>
                            <td>
                                <?php if ($feature['type'] == 2) { ?>
                                    <div><?=lang('Text specification (input)')?></div>
                                <?php } else { ?>
                                    <ul class="features_ul" id="sortable_values_<?= $f_id ?>">
                                        <?php if (isset($feature['values']) && count($feature['values'])) { ?>
                                            <?php foreach ($feature['values'] as $value) { ?>
                                                <li id="sorder_val[]_<?= $value['id'] ?>"
                                                    data-value_id="<?= $value['id'] ?>">
                                                    <i class="fa fa-bars"></i>
                                                    <?php if ($feature['type'] == 3) { ?>
                                                        <i class="fa fa-circle" style="color: <?= $value['color'] ?>; border: 1px solid #ddd; border-radius: 50%;"></i>
                                                    <?php } ?>
                                                    <span><?= $value['name_RO'] ?></span>
                                                    <i class="fa fa-remove delete_feature_value"
                                                       style="color: red;"></i>
                                                    <i href="#change_feature_value" data-toggle="modal"
                                                       class="fa fa-pencil-square-o" style="color: green"></i>
                                                </li>
                                            <?php } ?>
                                        <?php } ?>
                                    </ul>
                                    <div>
                                        <a href="#add_feature_value" data-toggle="modal">
                                            <i class="fa fa-plus" style="color: green;"></i>
                                            <?=lang('Add value')?>
                                        </a>
                                    </div>
                                    <?php // } ?>
                                <?php } ?>
                            </td>
                            <td style="text-align: center" class="align-middle">
                                <i class="fa fa-gear" href="#change_feature" data-toggle="modal"
                                   style="color: grey; font-size: 20px; margin-right: 10px;"></i>
                                <i class="fa fa-trash-o delete_feature" style="color: red; font-size: 20px;"></i>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<style>
    .modal.fade.in {
        top: 20% !important;
    }
</style>


<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<link href="/static/assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="/static/assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
      type="text/css"/>
<script src="/static/assets/global/plugins/bootstrap-modal/js/bootstrap-modal.js" type="text/javascript"></script>
<script src="/static/assets/global/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"
        type="text/javascript"></script>

<link href="/static/assets/global/plugins/bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css"/>
<script src="/static/assets/global/plugins/bootstrap-toastr/toastr.min.js" type="text/javascript"></script>

<link href="/static/assets/global/plugins/bootstrap-sweetalert/sweetalert.css" rel="stylesheet" type="text/css"/>
<script src="/static/assets/global/plugins/bootstrap-sweetalert/sweetalert.js" type="text/javascript"></script>

<script>
    $(document).ready(function () {
        toastrOptions();

        //***********  Sortable types ***********//
        $('#type_menu').sortable({
            items: '> li',
            forcePlaceholderSize: true,
            stop: function (event, ui) {
                var sorted = $("#type_menu").sortable("serialize", {key: "sorder[]"});
                $.post('/cp_features/sort_type_menu/', sorted, function (r) {
                    if (r.status == 'ok') {
                        toastr["success"]("<?=lang('List updated')?>");
                    } else {
                        toastr["error"]("<?=lang('An error has occurred')?>");
                    }
                }, 'json');
            }
        }).disableSelection();

        //************* Sortable features *************//
        $('#feature_table').sortable({
            distance: 5,
            opacity: 0.75,
            items: '> tbody:first > tr:visible',
            helper: fixWidthHelper,
            handle: '.sort',
            stop: function (event, ui) {
                var sorted = $("#feature_table").sortable("serialize", {key: "sorder[]"});
                $.post('/cp_features/sort_feature/', sorted, function (r) {
                    if (r.status == 'ok') {
                        toastr["success"]("<?=lang('List updated')?>");
                    } else {
                        toastr["error"]("<?=lang('An error has occurred')?>");
                    }
                }, 'json');
            }
        }).disableSelection();

        //*************** Delete type *************//
        $('body').on('click', '.delete_type', function (e) {
            e.preventDefault();

            var id = $(this).attr('data-type');
            swal({
                title: "<?=lang('Are you sure you want to delete')?>",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "<?=lang('yes')?>",
                cancelButtonText: "<?=lang('not')?>",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.post('/cp_features/delete_type/', {id: id}, function (response) {
                        if (response.status == 'ok') {
                            setTimeout(function () {
                                window.location = window.location.href.split("?")[0];
                            }, 800);
                            toastr["success"]("<?=lang('List updated')?>");
                        } else {
                            toastr["error"]("<?=lang('An error has occurred')?>");
                        }
                    }, 'json');
                }
            });
        });

        //*************** Delete feature *************//
        $('body').on('click', '.delete_feature', function (e) {
            e.preventDefault();

            var tr = $(this).closest('tr');
            var id = tr.attr('data-id');
            swal({
                title: "<?=lang('Are you sure you want to delete')?>",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "<?=lang('yes')?>",
                cancelButtonText: "<?=lang('not')?>",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.post('/cp_features/delete_feature/', {id: id}, function (response) {
                        if (response.status == 'ok') {
                            tr.remove();
                            toastr["success"]("<?=lang('List updated')?>");
                        } else {
                            toastr["error"]("<?=lang('An error has occurred')?>");
                        }
                    }, 'json');
                }
            });
        });

        //*************** Delete feature value *************//
        $('body').on('click', '.delete_feature_value', function (e) {
            e.preventDefault();

            var li = $(this).closest('li');
            var id = li.attr('data-value_id');
            swal({
                title: "<?=lang('Are you sure you want to delete')?>",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "<?=lang('yes')?>",
                cancelButtonText: "<?=lang('not')?>",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.post('/cp_features/delete_feature_value/', {id: id}, function (response) {
                        if (response.status == 'ok') {
                            li.remove();
                            toastr["success"]("<?=lang('List updated')?>");
                        } else {
                            toastr["error"]("<?=lang('An error has occurred')?>");
                        }
                    }, 'json');
                }
            });
        });

        //***********  Sortable feature values ***********//
        sortable_feature_values();

    });

    function sortable_feature_values() {
        $('[id^="sortable_values"]').sortable({
            items: '> li',
            stop: function (event, ui) {
                var sorted = $(this).closest('ul').sortable("serialize", {key: "sorder_val[]"});
                $.post('/cp_features/sort_feature_values/', sorted, function (r) {
                    if (r.status == 'ok') {
                        toastr["success"]("<?=lang('List updated')?>");
                    } else {
                        toastr["error"]("<?=lang('An error has occurred')?>");
                    }
                }, 'json');
            }
        }).disableSelection();
    }

    function toastrOptions() {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "positionClass": "toast-top-center",
            "onclick": null,
            "showDuration": "1000",
            "hideDuration": "1000",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    }

    function fixWidthHelper(e, ui) {
        ui.children().each(function () {
            $(this).width($(this).width());
        });
        return ui;
    }
</script>

<style>
    .grid {
        display: grid;
        grid-template-columns: 240px 4.5fr;
        min-height: 86vh;
    }

    .grid .feature_menu {
        background: #ececec;
        padding: 10px;
    }

    .grid .feature_menu .add_button {
        width: 100%;
        text-align: center;
    }

    .grid .feature_menu .menu {
        margin-top: 20px;
    }

    .grid .feature_menu .menu ul {
        list-style: none;
        margin: 0px;
        padding: 0px;
    }

    .grid .feature_menu .menu ul li {
        padding: 6px;
    }

    .grid .feature_menu .menu ul li.active {
        background: #e0dfdf;
    }

    .grid .feature_menu .menu ul li i {
        cursor: pointer;
        color: #333333;
    }

    .grid .feature_menu .menu ul li a {
        font-size: 14px;
        font-weight: bold;
        text-decoration: none;
        color: #333333;
    }

    .grid .features {
        padding: 0 10px;
    }

    .grid .features i {
        cursor: pointer;
    }

    .grid .features .header {
        display: grid;
        grid-template-columns: 5fr 80px;
    }

    .grid .features .header > div {
        text-align: center;
        padding: 8px;
        background: #e8e8e8;
        border-radius: 5px;
    }

    .grid .features h3 {
        padding: unset;
        margin: unset;
        margin-left: 10px;
    }

    .grid .features table tr:hover td {
        background: #ffffe5;
    }

    .grid .features table tr td:nth-child(1) {
        width: 5%;
    }

    .grid .features table tr td:nth-child(2) {
        width: 30%;
    }

    .grid .features table tr td:nth-child(3) {
        width: 53%;
    }

    .grid .features table tr td:nth-child(4) {
        width: 12%;
    }

    .grid .features table ul.features_ul {
        list-style: none;
        margin: 0px;
        margin-left: 20px;
        padding: 0px;
        width: 300px;
        max-height: 240px;
        overflow: auto;
    }

    .grid .features table ul.features_ul li {
        margin-left: 15px;
        padding: 2px;
        margin-right: 15px;
    }

    .grid .features table ul.features_ul li:not(:last-child) {
        border-bottom: 1px solid #e7ecf1;
    }

    .grid .features table div {
        padding: 8px 36px;
    }

    .grid .features table ul.features_ul li i:not(.fa-bars):not(.fa-circle) {
        float: right;
        margin-top: 3px;
    }

    .grid .features table ul.features_ul li i.fa-bars {
        color: #ccc;
        margin-right: 6px;
        margin-left: 0px;
    }

    .grid .features .add_feature {
        margin: 10px 0;
    }
</style>