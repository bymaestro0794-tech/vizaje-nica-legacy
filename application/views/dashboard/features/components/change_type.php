<div id="change_type" class="modal fade" data-width="600" aria-hidden="true"  style="display: none;">
    <form id="change_form_type" action="" method="post" enctype="text/plain" >
        <input type="hidden" name="id" value="<?= $type->id ?>">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><?=lang('Specification Name')?></h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="control-label"><?=lang('Title')?></label>
                <input class="form-control" name="name" value="<?= isset($type->name) ? $type->name : lang('not') ?>" type="text">
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
        var $change_type = $('#change_type');

        $('#change_form_type').on('submit', function(e) {
            e.preventDefault();

            var serialize = $change_type.find("input, textarea, select").serialize();
            $.post('/cp_features/change_type/', serialize, function(r) {
                if(r.status == 'ok') {
                    $('#type_menu li[id="sorder[]_'+r.id+'"] a').html(r.name);
                    $('.header_type_name').html(r.name);
                    $('#change_type input[name="name"]').val(r.name);
                    $change_type.modal('hide');
                }
            }, 'json');
        });
    });
</script>
