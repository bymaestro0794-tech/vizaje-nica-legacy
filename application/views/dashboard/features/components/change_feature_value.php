<div id="change_feature_value" class="modal fade" data-width="600" aria-hidden="true"  style="display: none;">
    <form id="change_form_feature_value" action="" method="post" enctype="text/plain" >
        <input type="hidden" name="value_id" value="">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><?=lang("Add value")?></h4>
        </div>
        <div class="modal-body" style="padding: 0 15px;">
            <div class="form-group" style="margin-top: 15px;">
                <label class="control-label"><?=lang("Name")?> RU</label>
                <input class="form-control" name="name_RU" type="text">
            </div>
        </div>
        <div class="modal-body" style="padding: 0 15px;">
            <div class="form-group">
                <label class="control-label"><?=lang("Name")?> RO</label>
                <input class="form-control" name="name_RO" type="text" required>
            </div>
        </div>
        <div class="modal-body color" style="padding: 0 15px; display: none;">
            <div class="form-group">
                <label class="control-label"><?=lang("Colors")?></label>
                <input type="text" name="color" data-control="wheel" class="colorpicker minicolors-input form-control">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-outline dark"><?=lang('Сlose')?></button>
            <button type="submit" class="btn green save"><?=lang('Add')?></button>
        </div>
    </form>
</div>

<link href="/static/assets/global/plugins/jquery-minicolors/jquery.minicolors.css" rel="stylesheet" type="text/css"/>
<script src="/static/assets/global/plugins/jquery-minicolors/jquery.minicolors.js" type="text/javascript"></script>

<script>
    $(document).ready(function() {
        $('.colorpicker').minicolors({control: 'wheel', theme: 'bootstrap', position: 'top left'});

        var $change_feature_value = $('#change_feature_value');
        $('body').on('click', '[href="#change_feature_value"]', function() {
            var li = $(this).closest('li');
            var value_id = li.attr('data-value_id');
            var feature_type = li.closest('tr').attr('data-type');

            if(feature_type == 3) {
                $change_feature_value.find('.modal-body.color').show();
            } else {
                $change_feature_value.find('.modal-body.color').hide();
            }

            $change_feature_value.find('[name="value_id"]').val(value_id);

            $.post('/cp_features/get_feature_value/', {value_id: value_id}, function(r) {
                if(r.status == 'ok') {
                    $change_feature_value.find('[name="name_RU"]').val(r.value.name_RU);
                    $change_feature_value.find('[name="name_RO"]').val(r.value.name_RO);
                    $change_feature_value.find('[name="color"]').val(r.value.color);

                    $('.colorpicker').minicolors('value', r.value.color);
                } else {
                    toastr["error"]("<?=lang('An error has occurred')?>");
                    $change_feature_value.modal('hide');
                    $change_feature_value.find('.modal-body.color').hide();
                }
            }, 'json');

        });

        $('#change_form_feature_value').on('submit', function(e) {
            e.preventDefault();

            var serialize = $change_feature_value.find("input, textarea, select").serialize();
            $.post('/cp_features/change_feature_value/', serialize, function(r) {
                if(r.status == 'ok') {
                    $change_feature_value.modal('hide');
                    $change_feature_value.find('input[name="value_id"]').val('');
                    $change_feature_value.find('input[name="name_RU"]').val('');
                    $change_feature_value.find('input[name="name_RO"]').val('');

                    $change_feature_value.find('.modal-body.color').hide();

                    $('.features_ul li[data-value_id="'+r.id+'"] span').html(r.name);

                    if($('.features_ul li[data-value_id="'+r.id+'"] .fa-circle').length) {
                        $('.features_ul li[data-value_id="'+r.id+'"] .fa-circle').css('color', r.color);
                    }
                }
            }, 'json');
        });
    });
</script>