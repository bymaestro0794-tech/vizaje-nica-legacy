<div id="add_feature" class="modal fade" data-width="600" aria-hidden="true"  style="display: none;">
    <form id="add_new_feature" action="" method="post" enctype="text/plain" >
        <input type="hidden" name="type_id" value="<?= $type->id ?>">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><?=lang('Specification Name')?></h4>
        </div>
        <div class="modal-body" style="padding: 0 15px;">
            <div class="form-group" style="margin-top: 15px;">
                <label class="control-label"><?=lang('Type')?></label>
                <select class="form-control" name="type"  required>

                    <option value="1" ><?=lang('Filters')?></option>
                    <option value="2" ><?=lang('Features')?></option>
                    <option value="3" ><?=lang('Colors')?></option>

                </select>
            </div>
        </div>
        <div class="modal-body" style="padding: 0 15px;">
            <div class="form-group" style="margin-top: 15px;">
                <label class="control-label"><?=lang('Title')?> RU</label>
                <input class="form-control" name="name_RU" type="text">
            </div>
        </div>
        <div class="modal-body" style="padding: 0 15px;">
            <div class="form-group">
                <label class="control-label"><?=lang('Title')?> RO</label>
                <input class="form-control" name="name_RO" type="text" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" data-dismiss="modal" class="btn btn-outline dark"><?=lang('Сlose')?></button>
            <button type="submit" class="btn green save"><?=lang('Add')?></button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        var $add_feature = $('#add_feature');
        $('#add_new_feature').on('submit', function(e) {
            e.preventDefault();
            var serialize = $add_feature.find("input, textarea, select").serialize();
            $.post('/cp_features/add_feature/', serialize, function(r) {
                if(r.status == 'ok') {
                    $add_feature.modal('hide');
                    $add_feature.find('input[name="name_RU"]').val('');
                    $add_feature.find('input[name="name_RO"]').val('');
                    $add_feature.find('input[name="type"]').val(0);
                    $add_feature.find('input[name="filter_type"]').val(0);

                    var html = feature_template(r);
                    $('#feature_table tbody').prepend(html);

                    var sorted = $( "#feature_table" ).sortable( "serialize", { key: "sorder[]" } );
                    $.post('/cp_features/sort_feature/', sorted, function(r){
                        if(r.status == 'ok') {
                            toastr["success"]("<?=lang('List updated')?>");
                        } else {
                            toastr["error"]("<?=lang('An error has occurred')?>");
                        }
                    },'json');

                    sortable_feature_values();
                }
            }, 'json');
        });
    });

    function feature_template(r) {
        var html = '<tr id="sorder[]_'+r.id+'" data-id="'+r.id+'" data-type="'+r.data.type+'">';
        html += '<td style="text-align: center" class="align-middle"><i class="glyphicon glyphicon-menu-hamburger sort"></i></td>';
        html += '<td class="align-middle feature_name"> '+r.data.name_RO+' </td>';

        if(r.data.type == 2) {
            html += '<td>';
            html += '<div><?=lang("Text specification (input)")?></div>';
            html += '</td>';
        } else {
            html += '<td>';
            html += '<ul class="features_ul"  id="sortable_values_'+r.id+'"></ul>';
            html += '<div><a href="#add_feature_value" data-toggle="modal"><i class="fa fa-plus" style="color: green;"></i><?=lang("Add value")?></a></div>';
            html += '</td>';
        }


        html += '<td style="text-align: center" class="align-middle">';
        html += '<i class="fa fa-gear" href="#change_feature" data-toggle="modal" style="color: grey; font-size: 20px; margin-right: 10px;"></i>';
        html += '<i class="fa fa-trash-o delete_feature" style="color: red; font-size: 20px;"></i>';
        html += '</td>';
        html += '</tr>';

        return html;
    }
</script>