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
                                                       value="<?= $item->{'title' . strtoupper($lang)} ?>" required>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200"><?= lang('Phone') ?></td>
                                        <td>
                                            <input type="text" name="phone"
                                                   class="form-control" value="<?= $item->phone ?>">
                                        </td>
                                    </tr>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Working hours') ?> <?= strtoupper($lang) ?></td>
                                            <td>
                                                        <textarea name="desc<?= strtoupper($lang) ?>" cols="30"
                                                                  rows="3" class="form-control"><?= $item->{'desc' . strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Address') ?> <?= strtoupper($lang) ?></td>
                                            <td>
                                                        <textarea name="text<?= strtoupper($lang) ?>" cols="10"
                                                                  rows="1" class="form-control"><?= $item->{'text' . strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td width="200"><?= lang('Photo') ?>
                                        </td>
                                        <td>
                                            <input type="file" name="images[]" id="file" class="form-control"
                                                   multiple>
                                            <div class="note note-warning"
                                                 style="margin-bottom: 0px; margin-top: 10px;">
                                                <p>
                                                    <?= lang('Allowable sizes') ?>: 1020x850 2мб
                                                </p>
                                            </div>
                                            <?php if (!empty($item->images)):
                                                foreach ($item->images as $image) {
                                                    if (empty($image->img)) continue; ?>
                                                    <?php $src = newthumbs($image->img, 'stores', 250, 250, '250x250x1', 1) ?>
                                                    <div class="mt-element-card mt-element-overlay margin-top-10">
                                                        <div class="col-lg-3 col-md-1">
                                                            <div class="mt-card-item">
                                                                <div class="mt-card-avatar mt-overlay-1">
                                                                    <img src="<?= $src ?>"/>
                                                                    <div class="mt-overlay">
                                                                        <ul class="mt-info">
                                                                            <li>
                                                                               <a
                                                                                    class="btn red mine_delete_photo"
                                                                                    data-table="stores_img"
                                                                                    data-path="stores"
                                                                                    data-col="img"
                                                                                    data-id="<?= (int) $image->id ?>"
                                                                                    href="javascript:;"
                                                                                >
                                                                                    <i class="fa fa-ban"></i>
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <input type="text" name="image_order[<?= $image->id ?>]"
                                                                   value="<?= $image->sorder ?>"
                                                                   class="form-control image_order"
                                                                   style="text-align: center">
                                                        </div>
                                                    </div>
                                                <? } ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?= lang('Coordinates') ?></td>
                                        <td>
                                            <?
                                            $crd = '47.0183674,28.8516902';
                                            if (!empty($item->coords)) $crd = $item->coords;
                                            $ca = explode(',', $crd);
                                            ?>
                                            <div id="map" style="width:100%;height:400px;"></div>
                                            <script>
                                                //var map;
                                                var marker;

                                                function initMap() {
                                                    var uluru = {lat: <?=$ca[0]?>, lng: <?=$ca[1]?>};
                                                    var map = new google.maps.Map(document.getElementById('map'), {
                                                        zoom: 12,
                                                        center: uluru
                                                    });
                                                    marker = new google.maps.Marker({
                                                        position: uluru,
                                                        map: map
                                                    });

                                                    map.addListener('click', function (e) {
                                                        placeMarker(e.latLng, map);
                                                        coords = e.latLng.toString().replace('(', '').replace(')', '').replace(' ', '');
                                                        $('#coordval').val(coords);
                                                    });

                                                }

                                                function placeMarker(position, map) {
                                                    if (marker == null) {
                                                        marker = new google.maps.Marker({
                                                            position: position,
                                                            map: map
                                                        });
                                                    } else {
                                                        marker.setPosition(position);
                                                    }
                                                    map.panTo(position);
                                                }


                                            </script>
                                            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUy8G6VYtwV6uLiehHWZCFeDaLjB7zv7s&callback=initMap"></script>


                                            <input type="hidden" name="coords" id="coordval" value="<?=$item->coords?>">
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
