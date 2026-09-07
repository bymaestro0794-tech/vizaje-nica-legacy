<div id="change_feature" class="modal fade" data-width="600" aria-hidden="true"  style="display: none;">
    <form id="change_form_feature" action="" method="post" enctype="text/plain" >
        <input type="hidden" name="feature_id" value="">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><?=lang('Specification Name')?></h4>
        </div>
        <div class="modal-body" style="padding: 0 15px;">
            <div class="form-group" style="margin-top: 15px;">
                <label class="control-label"><?=lang('Type')?></label>
                <select class="form-control" name="type" >
                    <option value="1" ><?=lang('Filters')?></option>
                    <option value="2" ><?=lang('Features')?></option>
                    <option value="3" ><?=lang('Colors')?></option>
                </select>
            </div>
        </div>
        <div class="modal-body" style="padding: 0 15px;">
            <div class="form-group" style="margin-top: 15px;">
                <label class="control-label"><?=lang('Title')?> RU</label>
                <input class="form-control" name="name_RU" type="text" value="">
            </div>
        </div>
        <div class="modal-body" style="padding: 0 15px;">
            <div class="form-group">
                <label class="control-label"><?=lang('Title')?> RO</label>
                <input class="form-control" name="name_RO" type="text" value="" required>
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
        var $change_feature = $('#change_feature');
        $('body').on('click', '[href="#change_feature"]', function() {
            var tr = $(this).closest('tr');
            var feature_id = tr.attr('data-id');

            $.post('/cp_features/get_feature/', {feature_id: feature_id}, function(r) {
                if(r.status == 'ok') {
                    $change_feature.find('[name="feature_id"]').val(r.feature.id);
                    $change_feature.find('[name="name_RU"]').val(r.feature.name_RU);
                    $change_feature.find('[name="name_RO"]').val(r.feature.name_RO);
                    $change_feature.find('[name="type"]').val(r.feature.type);
                    $change_feature.find('[name="filter_type"]').val(r.feature.filter_type);
                    if (r.feature.free_values == 1){
                        $change_feature.find('[name="free_values"]').prop('checked', true);
                    } else {
                        $change_feature.find('[name="free_values"]').prop('checked', false);
                    }
                    // $change_feature.find('[name="colon_type"]').val(r.feature.colon_type);
                } else {
                    toastr["error"]("<?=lang('An error has occurred')?>");
                    $change_feature.modal('hide');
                }
            }, 'json');
        });

        $('#change_form_feature').on('submit', function(e) {
            e.preventDefault();

            var serialize = $change_feature.find("input, textarea, select").serialize();
            $.post('/cp_features/change_feature/', serialize, function(r) {
                if(r.status == 'ok') {
                    $('#feature_table tbody > tr[data-id="'+r.id+'"] .feature_name').html(r.name);
                    $change_feature.modal('hide');
                }
            }, 'json');
        });
    });
</script>