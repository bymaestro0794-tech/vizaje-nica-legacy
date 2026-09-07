<div id="add_type" class="modal fade" data-width="600" aria-hidden="true"  style="display: none;">
    <form id="add_new_type" action="" method="post" enctype="text/plain" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title"><?=lang('Features add type')?></h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="control-label"><?=lang('Title')?></label>
                <input class="form-control" name="name" type="text" required>
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
        var $add_type = $('#add_type');
        $('#add_new_type').on('submit', function(e) {
            e.preventDefault();

            var serialize = $add_type.find("input, textarea, select").serialize();
            $.post('/cp_features/add_type/', serialize, function(r) {
                if(r.status == 'ok') {
                    var html = type_menu_template(r);
                    $('#type_menu').prepend(html);
                    $add_type.modal('hide');
                    $add_type.find('input').val('');
                }
            }, 'json');
        });
    });

    function type_menu_template(r) {
        var html = '<li id="sorder[]_'+r.id+'" data-id="'+r.id+'">';
        html += '<i class="glyphicon glyphicon-menu-hamburger"></i>';
        html += '<a href="?type='+r.id+'"> '+r.name+'</a>';
        html += '</li>';

        return html;
    }
</script>