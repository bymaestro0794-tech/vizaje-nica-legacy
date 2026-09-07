<div id="add_feature_value" class="modal fade" data-width="600" aria-hidden="true"  style="display: none;">
    <form id="add_new_feature_value" action="" method="post" enctype="text/plain" >
        <input type="hidden" name="feature_id" value="">
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

        var $add_feature_value = $('#add_feature_value');
        $('body').on('click', '[href="#add_feature_value"]', function() {
            var tr = $(this).closest('tr');
            var feature_id = tr.attr('data-id');
            var feature_type = tr.attr('data-type');
            $add_feature_value.find('[name="feature_id"]').val(feature_id);

            if(feature_type == 3) {
                $add_feature_value.find('.modal-body.color').show();
                $('.colorpicker').minicolors('value', '#ffffff');
            } else {
                $add_feature_value.find('.modal-body.color').hide();
            }
        });

        $('#add_new_feature_value').on('submit', function(e) {
            e.preventDefault();

            var serialize = $add_feature_value.find("input, textarea, select").serialize();
            $.post('/cp_features/add_feature_value/', serialize, function(r) {
                if(r.status == 'ok') {
                    $add_feature_value.modal('hide');
                    $add_feature_value.find('input[name="feature_id"]').val('');
                    $add_feature_value.find('input[name="name_RU"]').val('');
                    $add_feature_value.find('input[name="name_RO"]').val('');
                    $add_feature_value.find('input[name="color"]').val('');

                    $add_feature_value.find('.modal-body.color').hide();

                    var html = feature_value_template(r);
                    $('#feature_table tbody tr[data-id="'+r.data.feature_id+'"] .features_ul').prepend(html);

                    $('.colorpicker').minicolors({control: 'wheel', theme: 'bootstrap', position: 'top left'});
                }
            }, 'json');
        });
    });

    function feature_value_template(r) {
        var html = '<li id="sorder_val[]_'+r.id+'" data-value_id="'+r.id+'">';
        html += '<i class="fa fa-bars"></i>';

        if(r.feature.type == 3) {
            html += '<i class="fa fa-circle" style="color: '+r.data.color+'; border: 1px solid #ddd; border-radius: 50%; margin: 0 4px;"></i>';
        }

        html += '<span>' + r.data.name_RO + '</span>';
        html += '<i class="fa fa-remove delete_feature_value" style="color: red;"></i>';
        html += '<i href="#change_feature_value" data-toggle="modal" class="fa fa-pencil-square-o" style="color: green"></i>';
        html += '</li>';

        return html;
    }
</script>