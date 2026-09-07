<!-- BEGIN PAGE HEADER-->

<!-- BEGIN PAGE BAR -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="/<?= ADM_CONTROLLER ?>/menu/">Home</a>
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
                        <ul class="nav nav-pills">
                            <li class="active">
                                <a href="#tab_1_1" data-toggle="tab"><?= lang('General information') ?></a>
                            </li>
                            <?php if (!empty($category->feature_type)) { ?>
                                <li class="">
                                    <a href="#tab_1_2" data-toggle="tab"><?= lang('Filters') ?></a>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="#tab_1_3" data-toggle="tab" class="tab_variable_off">Вариации товаров</a>
                            </li>
                            <li>
                                <a href="#tab_1_4" data-toggle="tab"><?= lang('SEO') ?></a>
                            </li>
                            <li>
                                <a href="#tab_1_5" data-toggle="tab"><?= lang('Related products') ?></a>
                            </li>
                            <li>
                                <a href="#tab_1_6" data-toggle="tab"><?= lang('More products') ?></a>
                            </li>
                        </ul>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="tab_1_1">
                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>
                                    <tr>
                                        <td><?= lang('Category') ?> *</td>
                                        <td>

                                            <?php $selectedCategories = $itemCategories ?? []; ?>

                                            <div class="category-tree">

                                                <?php foreach ($for_list as $cat) { ?>
                                                    <div class="cat-item">

                                                        <?php if (!empty($cat['children'])) { ?>
                                                            <span class="toggle" onclick="toggleCat(this)">▶</span>
                                                        <?php } else { ?>
                                                            <span class="toggle empty"></span>
                                                        <?php } ?>

                                                        <label>
                                                            <input type="checkbox"
                                                                   name="categories[]"
                                                                   value="<?= $cat['id'] ?>"
                                                                <?= in_array($cat['id'], $selectedCategories) ? 'checked' : '' ?>>
                                                            <strong><?= $cat['title'] ?></strong>
                                                        </label>

                                                        <?php if (!empty($cat['children'])) { ?>
                                                            <div class="cat-children">

                                                                <?php foreach ($cat['children'] as $child) { ?>
                                                                    <div class="cat-item">

                                                                        <?php if (!empty($child['children'])) { ?>
                                                                            <span class="toggle" onclick="toggleCat(this)">▶</span>
                                                                        <?php } else { ?>
                                                                            <span class="toggle empty"></span>
                                                                        <?php } ?>

                                                                        <label>
                                                                            <input type="checkbox"
                                                                                   name="categories[]"
                                                                                   value="<?= $child['id'] ?>"
                                                                                <?= in_array($child['id'], $selectedCategories) ? 'checked' : '' ?>>
                                                                            <?= $child['title'] ?>
                                                                        </label>

                                                                        <?php if (!empty($child['children'])) { ?>
                                                                            <div class="cat-children">

                                                                                <?php foreach ($child['children'] as $ch) { ?>
                                                                                    <div class="cat-item">

                                                                                        <span class="toggle empty"></span>

                                                                                        <label>
                                                                                            <input type="checkbox"
                                                                                                   name="categories[]"
                                                                                                   value="<?= $ch['id'] ?>"
                                                                                                <?= in_array($ch['id'], $selectedCategories) ? 'checked' : '' ?>>
                                                                                            <?= $ch['title'] ?>
                                                                                        </label>

                                                                                    </div>
                                                                                <?php } ?>

                                                                            </div>
                                                                        <?php } ?>

                                                                    </div>
                                                                <?php } ?>

                                                            </div>
                                                        <?php } ?>

                                                    </div>
                                                <?php } ?>

                                            </div>

                                        </td>
                                    </tr>


                                    <tr>
                                        <td><?= lang('Brands') ?></td>
                                        <td>
                                            <select name="brand_id" id="" class="form-control ">
                                                <option value=""><?= lang('Select Brands') ?></option>
                                                <?php foreach ($brands as $brand) { ?>
                                                    <option value="<?= $brand->id ?>" <?= $item->brand_id == $brand->id ? 'selected' : '' ?>> <?= $brand->title ?> </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Article') ?> *</td>
                                        <td>
                                            <input type="text" name="SKU" value="<?= $item->SKU ?>"
                                                   class="form-control" required>
                                        </td>
                                    </tr>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">Название (для интерфейса) <?= strtoupper($lang) ?> *</td>
                                            <td>
                                                <input type="text" name="title<?= strtoupper($lang) ?>"
                                                       value="<?= $item->{'title' . strtoupper($lang)} ?>"
                                                       class="form-control" required>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">H1 заголовок <?= strtoupper($lang) ?></td>
                                            <td>
                                                <input type="text" name="h1<?= strtoupper($lang) ?>"
                                                       value="<?= $item->{'h1' . strtoupper($lang)} ?>"
                                                       class="form-control">
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">Название для хлебных крошек <?= strtoupper($lang) ?></td>
                                            <td>
                                                <input type="text" name="breadcrumbTitle<?= strtoupper($lang) ?>"
                                                       value="<?= $item->{'breadcrumbTitle' . strtoupper($lang)} ?>"
                                                       class="form-control">
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <tr>
                                        <td><?= lang('Quantity') ?></td>
                                        <td>
                                            <input type="text" name="on_stock"
                                                   class="form-control" value="<?= $item->on_stock ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Price') ?></td>
                                        <td>
                                            <input type="text" name="price" value="<?= $item->price ?>"
                                                   class="form-control" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Lower price') ?></td>
                                        <td>
                                            <input type="text" name="discount_price"
                                                   value="<?= $item->discount_price ?>"
                                                   class="form-control">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200"><?= lang('Volume') ?></td>
                                        <td>
                                            <input type="text" name="volume" value="<?= $item->volume ?>"
                                                   class="form-control">
                                        </td>
                                    </tr>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">Instruction <?= strtoupper($lang) ?></td>
                                            <td>
                                                        <textarea name="instruction<?= strtoupper($lang) ?>" cols="30" rows="3"
                                                                  class="form-control "><?= $item->{'instruction' . strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200">Components <?= strtoupper($lang) ?></td>
                                            <td>
                                                        <textarea name="components<?= strtoupper($lang) ?>" cols="30" rows="3"
                                                                  class="form-control "><?= $item->{'components' . strtoupper($lang)} ?></textarea>
                                            </td>
                                        </tr>
                                    <? } ?>
                                    <? foreach (language(true) as $lang) { ?>
                                        <tr>
                                            <td width="200"><?= lang('Text') ?> <?= strtoupper($lang) ?></td>
                                            <td>
                                                        <textarea name="text<?= strtoupper($lang) ?>" cols="30" rows="3"
                                                                  class="form-control ckeditor"><?= $item->{'text' . strtoupper($lang)} ?></textarea>
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
                                            <?php if (!empty($item->images)) : ?>
                                                <?php foreach ($item->images as $image) : ?>
                                                    <?php if (empty($image->img)) continue; ?>
                                                    <?php $src = newthumbs($image->img, 'products', 250, 250, '250x250x1', 1); ?>
                                                    <div class="mt-element-card mt-element-overlay margin-top-10">
                                                        <div class="col-lg-3 col-md-1 item">
                                                            <div class="mt-card-item">
                                                                <div class="mt-card-avatar mt-overlay-1">
                                                                    <img src="<?= $src ?>"/>
                                                                    <div class="mt-overlay">
                                                                        <ul class="mt-info">
                                                                            <li>
                                                                                <a class="btn red mine_delete_photo"
                                                                                   data-table="products_img"
                                                                                   data-path="products"
                                                                                   data-col="img"
                                                                                   data-id="<?= $image->id ?>"
                                                                                   href="javascript:;">
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
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green"><i class="fa fa-check"></i>
                                                <?= lang('Add') ?>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if (!empty($category->feature_type)) { ?>
                            <div class="tab-pane fade" id="tab_1_2">
                                <div class="table-scrollable">
                                    <div style="padding: 15px;" id="caracteristics_list">
                                        <?php if (!empty($caracteristics_list)) { ?>
                                            <?php foreach ($caracteristics_list as $element) { ?>
                                                <?php if (!empty($element['feature_values'])) { ?>
                                                    <div class="col-md-3"
                                                         style="height: 120px; overflow-y: scroll;margin-bottom: 15px;">
                                                        <div style="padding: 10px; border-radius: 5px;background-color: #8080801a; width: 100%;">
                                                            <p style="padding: 0px; margin: 0px;"><b>
                                                                    <?= $element['feature_name'] ?>
                                                                    (<?= count($element['feature_values']) ?>)</b></p>
                                                            <?php foreach ($element['feature_values'] as $value) { ?>
                                                                <?php
                                                                $checked = '';
                                                                if (!empty($selected_features)) {
                                                                    foreach ($selected_features as $check) {
                                                                        if ($check->feature_id == $element['feature_id'] && $check->feature_value_id == $value['value_id']) {
                                                                            $checked = 'checked';
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                                <?php if ($element['feature_type'] == 3) { ?>
                                                                    <input id="<?= $element['feature_id'] ?>-<?= $value['value_id'] ?>"
                                                                           type="checkbox"
                                                                           name="features[<?= $element['feature_id'] ?>][][<?= $value['value_id'] ?>]"
                                                                           value="<?= $value['value_id'] ?>"
                                                                        <?= $checked ?>
                                                                    >
                                                                    <label for="<?= $element['feature_id'] ?>-<?= $value['value_id'] ?>">
                                                                        <i class="fa fa-circle"
                                                                           style="color: <?= $value['color'] ?>; border: 1px solid #ddd; border-radius: 50%;"></i>
                                                                        <?= $value['value_name'] ?>
                                                                    </label>
                                                                    <br>
                                                                <?php } else { ?>
                                                                    <input id="<?= $element['feature_id'] ?>-<?= $value['value_id'] ?>"
                                                                           type="checkbox"
                                                                           name="features[<?= $element['feature_id'] ?>][][<?= $value['value_id'] ?>]"
                                                                           value="<?= $value['value_id'] ?>"
                                                                        <?= $checked ?>
                                                                    >
                                                                    <label for="<?= $element['feature_id'] ?>-<?= $value['value_id'] ?>"><?= $value['value_name'] ?></label>
                                                                    <br>
                                                                <?php } ?>
                                                            <?php } ?>
                                                            <?php if (!empty($element['feature_free_values'])) { ?>
                                                                <input type="hidden"
                                                                       name="feature[<?= $element['feature_id'] ?>][feature_id]"
                                                                       value="<?= $element['feature_id'] ?>">
                                                                <input type="text" class="form-control"
                                                                       name="feature[<?= $element['feature_id'] ?>][name_RU]"
                                                                       value="" placeholder="Значение RU">
                                                                <input type="text" class="form-control"
                                                                       name="feature[<?= $element['feature_id'] ?>][name_RO]"
                                                                       value="" placeholder="Значение RO">
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="col-md-12">
                                        <br>
                                        <button type="submit" class="btn green"><i class="fa fa-check"></i>
                                            Изменить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="tab-pane fade" id="tab_1_3">

    <?php
    /*
    |--------------------------------------------------------------------------
    | Variant color helper
    |--------------------------------------------------------------------------
    |
    | color_data может прийти:
    | - NULL
    | - JSON string
    | - уже декодированным array из model
    |
    */

    $decodeVariantColors = static function ($colorData) {
        if (empty($colorData)) {
            return array();
        }

        if (is_array($colorData)) {
            /*
             * Model может вернуть непосредственно:
             * ['#FFFFFF', '#000000']
             *
             * или:
             * ['colors' => [...]]
             */
            if (
                isset($colorData['colors'])
                && is_array($colorData['colors'])
            ) {
                $colors = $colorData['colors'];
            } else {
                $colors = $colorData;
            }
        } else {
            $decoded = json_decode(
                (string) $colorData,
                true
            );

            if (
                !is_array($decoded)
                || empty($decoded['colors'])
                || !is_array($decoded['colors'])
            ) {
                return array();
            }

            $colors = $decoded['colors'];
        }

        $normalized = array();

        foreach ($colors as $color) {
            $color = strtoupper(
                trim(
                    (string) $color
                )
            );

            if (
                !preg_match(
                    '/^#[0-9A-F]{6}$/',
                    $color
                )
            ) {
                continue;
            }

            if (
                !in_array(
                    $color,
                    $normalized,
                    true
                )
            ) {
                $normalized[] = $color;
            }

            if (count($normalized) >= 6) {
                break;
            }
        }

        return $normalized;
    };
    ?>

    <div class="table-scrollable">

        <!-- =============================================================
             ADD VARIATION
             ============================================================= -->

        <table class="table table-bordered table-striped table-hover">
            <tbody>
                <tr>
                    <td width="200">
                        Добавить вариацию
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn green check_characters"
                            id="add_variable"
                        >
                            <i class="fa fa-plus"></i>
                            Добавить
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- =============================================================
             VARIATIONS
             ============================================================= -->

        <table
            class="
                table
                table-bordered
                table-striped
                table-hover
                product-variants-table
            "
        >
            <tbody id="table_variable">

                <?php if (!empty($item->variable)) { ?>

                    <?php foreach ($item->variable as $variable) { ?>

                        <?php
                        /*
                         * Цвета конкретно ЭТОГО варианта.
                         */
                        $variantColors = $decodeVariantColors(
                            isset($variable->color_data)
                                ? $variable->color_data
                                : null
                        );

                        $variantColorMode = 'none';

                        if (count($variantColors) === 1) {
                            $variantColorMode = 'single';
                        } elseif (count($variantColors) > 1) {
                            $variantColorMode = 'multi';
                        }
                        ?>

                        <tr id="variable<?= (int) $variable->id ?>">

                            <!-- SORT -->

                            <td width="80">
                                <label class="variant-admin-label">
                                    Сортировка
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->sorder,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][sorder]"
                                >
                            </td>

                            <!-- TITLE RU -->

                            <td>
                                <label class="variant-admin-label">
                                    Название RU
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->titleRU,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][titleRU]"
                                >
                            </td>

                            <!-- TITLE RO -->

                            <td>
                                <label class="variant-admin-label">
                                    Название RO
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->titleRO,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][titleRO]"
                                >
                            </td>

                            <!-- SKU -->

                            <td>
                                <label class="variant-admin-label">
                                    SKU
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->SKU,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][SKU]"
                                >
                            </td>

                            <!-- COLOR NAME RU -->

                            <td>
                                <label class="variant-admin-label">
                                    Цвет RU
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->colorRU,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][colorRU]"
                                >
                            </td>

                            <!-- COLOR NAME RO -->

                            <td>
                                <label class="variant-admin-label">
                                    Цвет RO
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->colorRO,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][colorRO]"
                                >
                            </td>

                            <!-- =================================================
                                 VISUAL COLOR
                                 ================================================= -->

                            <td
                                width="280"
                                class="variant-color-cell"
                                data-variant-color
                                data-variant-id="<?= (int) $variable->id ?>"
                            >
                                <label class="variant-admin-label">
                                    Визуальный цвет
                                </label>

                                <select
                                    class="
                                        form-control
                                        variant-color-mode
                                    "
                                    data-variant-color-mode
                                >
                                    <option
                                        value="none"
                                        <?= $variantColorMode === 'none'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Без цвета
                                    </option>

                                    <option
                                        value="single"
                                        <?= $variantColorMode === 'single'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Один цвет
                                    </option>

                                    <option
                                        value="multi"
                                        <?= $variantColorMode === 'multi'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Несколько цветов
                                    </option>
                                </select>

                                <div
                                    class="variant-color-list"
                                    data-variant-color-list
                                >
                                    <?php foreach ($variantColors as $color) { ?>

                                        <div
                                            class="variant-color-row"
                                            data-variant-color-row
                                        >
                                            <input
                                                type="color"
                                                class="variant-color-picker"
                                                data-variant-color-picker
                                                value="<?= htmlspecialchars(
                                                    $color,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >

                                            <input
                                                type="text"
                                                class="
                                                    form-control
                                                    variant-color-value
                                                "
                                                data-variant-color-value
                                                name="variable[<?= (int) $variable->id ?>][colors][]"
                                                value="<?= htmlspecialchars(
                                                    $color,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                maxlength="7"
                                                placeholder="#D8CFC9"
                                            >

                                            <button
                                                type="button"
                                                class="
                                                    btn
                                                    btn-xs
                                                    red
                                                    variant-color-remove
                                                "
                                                data-variant-color-remove
                                                title="Удалить цвет"
                                            >
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>

                                    <?php } ?>
                                </div>

                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-xs
                                        default
                                        variant-color-add
                                    "
                                    data-variant-color-add
                                >
                                    <i class="fa fa-plus"></i>
                                    Добавить цвет
                                </button>

                                <small class="variant-color-help">
                                    Максимум 6 цветов
                                </small>
                            </td>

                            <!-- VOLUME -->

                            <td>
                                <label class="variant-admin-label">
                                    Объём
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->VolumeVar,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][VolumeVar]"
                                >
                            </td>

                            <!-- PRICE -->

                            <td>
                                <label class="variant-admin-label">
                                    Цена
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->price,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][price]"
                                >
                            </td>

                            <!-- DISCOUNT PRICE -->

                            <td>
                                <label class="variant-admin-label">
                                    Цена со скидкой
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->discount_price,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][discount_price]"
                                >
                            </td>

                            <!-- STOCK -->

                            <td>
                                <label class="variant-admin-label">
                                    На складе
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        (string) $variable->qty,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    name="variable[<?= (int) $variable->id ?>][qty]"
                                >
                            </td>

                            <!-- ACTIVE -->

                            <td width="70">
                                <label class="variant-admin-label">
                                    Активен
                                </label>

                                <input
                                    type="checkbox"
                                    value="1"
                                    class="variant-active-checkbox"
                                    name="variable[<?= (int) $variable->id ?>][isShown]"
                                    <?= (int) $variable->isShown === 1
                                        ? 'checked'
                                        : '' ?>
                                >
                            </td>

                            <!-- IMAGES -->

                            <td
                                width="260"
                                class="variant-images-cell"
                            >
                                <label class="variant-admin-label">
                                    Фото
                                </label>

                                <input
                                    type="file"
                                    name="variable_images[<?= (int) $variable->id ?>][]"
                                    multiple
                                    class="form-control"
                                >

                                <?php if (!empty($variable->img)) { ?>

                                    <div class="variant-images-list">

                                        <?php foreach ($variable->img as $img_var) { ?>

                                            <?php
                                            $src = newthumbs(
                                                $img_var->img,
                                                'products_variable_img',
                                                50,
                                                50,
                                                '50x50x1',
                                                1
                                            );
                                            ?>

                                            <div class="variant-image-item">

                                                <div class="variant-image-preview">

                                                    <img
                                                        src="<?= htmlspecialchars(
                                                            $src,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                        alt=""
                                                    >

                                                    <a
                                                        class="
                                                            btn
                                                            red
                                                            mine_delete_photo
                                                            variant-image-delete
                                                        "
                                                        data-table="products_variable_img"
                                                        data-path="products_variable_img"
                                                        data-col="img"
                                                        data-id="<?= (int) $img_var->id ?>"
                                                        href="javascript:;"
                                                    >
                                                        <i class="fa fa-ban"></i>
                                                    </a>

                                                </div>

                                                <input
                                                    type="text"
                                                    name="variable_images[<?= (int) $img_var->id ?>][sorder]"
                                                    value="<?= htmlspecialchars(
                                                        (string) $img_var->sorder,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                    class="
                                                        form-control
                                                        image_order
                                                    "
                                                >

                                            </div>

                                        <?php } ?>

                                    </div>

                                <?php } ?>
                            </td>

                            <!-- DELETE -->

                            <td width="100">

                                <button
                                    type="button"
                                    onclick="DeleteVariable(<?= (int) $variable->id ?>)"
                                    class="
                                        btn
                                        btn-xs
                                        default
                                        btn-editable
                                        red-stripe
                                        variant-delete-button
                                    "
                                >
                                    <i class="glyphicon glyphicon-remove-circle"></i>

                                    Удалить
                                </button>

                            </td>

                        </tr>

                    <?php } ?>

                <?php } ?>

            </tbody>
        </table>

        <!-- =============================================================
             SAVE
             ============================================================= -->

        <table class="table table-bordered table-striped table-hover">
            <tbody>
                <tr>
                    <td width="200">
                        &nbsp;
                    </td>

                    <td>
                        <button
                            type="submit"
                            class="btn green check_characters"
                        >
                            <i class="fa fa-check"></i>
                            Изменить
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>
                        <div class="tab-pane fade" id="tab_1_4">
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
                        <div class="tab-pane fade" id="tab_1_5">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if (!empty($products)) { ?>
                                        <select multiple="multiple" id="my-select" name="related_products[]">
                                            <?php foreach ($products as $product) { ?>
                                                <?php if ($product->id != $item->id) { ?>
                                                    <option value='<?= $product->id ?>'
                                                        <?= in_array($product->id, $products_related) ? 'selected' : '' ?>>
                                                        <?php $code = !empty($product->code) ? $product->code . " - " : '' ?>
                                                        <?= $code . trim($product->{'title' . get_language_for_admin(true)}) ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn green"><i class="fa fa-check"></i>
                                        <?= lang('Edit') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab_1_6">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if (!empty($products)) { ?>
                                        <select multiple="multiple" id="my-select_more" name="more_products[]">
                                            <?php foreach ($products as $product) { ?>
                                                <?php if ($product->id != $item->id) { ?>
                                                    <option value='<?= $product->id ?>'
                                                        <?= in_array($product->id, $more_products) ? 'selected' : '' ?>>
                                                        <?php $code = !empty($product->code) ? $product->code . " - " : '' ?>
                                                        <?= $code . trim($product->{'title' . get_language_for_admin(true)}) ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn green"><i class="fa fa-check"></i>
                                        <?= lang('Edit') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('body').on('keyup', 'input[name="price"]', function () {
        $(this).val($(this).val().replace(',', '.'));
    });
    $('body').on('keyup', 'input[name="discount_price"]', function () {
        $(this).val($(this).val().replace(',', '.'));
    });

    $('#add_variable').on('click', function () {
        $.ajax({
            url: '/cp/products/add_variable', // путь к обработчику
            type: 'POST', // метод отправки
            data: {id: "<?=$item->id?>"},
            success: function (data) {
                var $row = $(data);

                $('#table_variable').prepend(
                    $row
                );

                document.dispatchEvent(
                    new CustomEvent(
                        'variant:created',
                        {
                            detail: {
                                row: $row[0]
                            }
                        }
                    )
                );
            },
            error: function (data) {
                console.log(data); // выводим ошибку в консоль
            }
        });
        return false;
    });

    function DeleteVariable(id) {
        var tbid = '#variable' + id;
        if (confirm('Вы уверены, что хотите удалить этот элемент?')) {
            $.ajax({
                url: '/cp/products/add_variable_delete', // путь к обработчику
                type: 'POST', // метод отправки
                data: {id: id},
                success: function (data) {
                    console.log("УСПЕХ"); // выводим сообщение в консоль
                    $(tbid).remove();
                },
                error: function (data) {
                    console.log(data); // выводим ошибку в консоль
                }
            });
        }
    }

</script>
<script>
(function () {
    'use strict';

    var MAX_COLORS = 6;
    var DEFAULT_SINGLE_COLOR = '#D9D9D9';
    var DEFAULT_SECOND_COLOR = '#BDBDBD';

    /* ================================================================
       HELPERS
       ================================================================ */

    function normalizeHex(value) {
        value = String(value || '')
            .trim()
            .toUpperCase();

        return /^#[0-9A-F]{6}$/.test(value)
            ? value
            : null;
    }

    function getContainer(element) {
        return element
            ? element.closest('[data-variant-color]')
            : null;
    }

    function getVariantId(container) {
        if (!container) {
            return '';
        }

        return String(
            container.dataset.variantId || ''
        );
    }

    function getMode(container) {
        var select = container.querySelector(
            '[data-variant-color-mode]'
        );

        return select
            ? select.value
            : 'none';
    }

    function getList(container) {
        return container.querySelector(
            '[data-variant-color-list]'
        );
    }

    function getAddButton(container) {
        return container.querySelector(
            '[data-variant-color-add]'
        );
    }

    function getRows(container) {
        return Array.prototype.slice.call(
            container.querySelectorAll(
                '[data-variant-color-row]'
            )
        );
    }

    /* ================================================================
       CREATE ROW
       ================================================================ */

    function createColorRow(
        container,
        color
    ) {
        var variantId =
            getVariantId(container);

        if (!variantId) {
            return null;
        }

        var normalized =
            normalizeHex(color)
            || DEFAULT_SINGLE_COLOR;

        var row =
            document.createElement('div');

        row.className =
            'variant-color-row';

        row.setAttribute(
            'data-variant-color-row',
            ''
        );

        var picker =
            document.createElement('input');

        picker.type = 'color';
        picker.className =
            'variant-color-picker';

        picker.setAttribute(
            'data-variant-color-picker',
            ''
        );

        picker.value =
            normalized;

        var valueInput =
            document.createElement('input');

        valueInput.type = 'text';

        valueInput.className =
            'form-control variant-color-value';

        valueInput.setAttribute(
            'data-variant-color-value',
            ''
        );

        valueInput.name =
            'variable['
            + variantId
            + '][colors][]';

        valueInput.value =
            normalized;

        valueInput.maxLength = 7;

        valueInput.placeholder =
            '#D8CFC9';

        var removeButton =
            document.createElement('button');

        removeButton.type =
            'button';

        removeButton.className =
            'btn btn-xs red variant-color-remove';

        removeButton.setAttribute(
            'data-variant-color-remove',
            ''
        );

        removeButton.title =
            'Удалить цвет';

        removeButton.innerHTML =
            '<i class="fa fa-times"></i>';

        row.appendChild(picker);
        row.appendChild(valueInput);
        row.appendChild(removeButton);

        return row;
    }

    /* ================================================================
       ADD COLOR
       ================================================================ */

    function addColor(
        container,
        color
    ) {
        var list =
            getList(container);

        if (!list) {
            return;
        }

        var rows =
            getRows(container);

        if (
            rows.length
            >= MAX_COLORS
        ) {
            alert(
                'Можно добавить максимум '
                + MAX_COLORS
                + ' цветов.'
            );

            return;
        }

        var row =
            createColorRow(
                container,
                color
            );

        if (!row) {
            return;
        }

        list.appendChild(row);

        updateUI(container);
    }

    /* ================================================================
       UPDATE UI
       ================================================================ */

    function updateUI(container) {
        if (!container) {
            return;
        }

        var mode =
            getMode(container);

        var list =
            getList(container);

        var addButton =
            getAddButton(container);

        if (
            !list
            || !addButton
        ) {
            return;
        }

        var rows =
            getRows(container);

        /*
         * NONE
         */
        if (mode === 'none') {
            list.innerHTML = '';

            list.style.display =
                'none';

            addButton.style.display =
                'none';

            container.classList.remove(
                'is-single',
                'is-multi'
            );

            container.classList.add(
                'is-none'
            );

            return;
        }

        /*
         * SINGLE
         */
        if (mode === 'single') {
            container.classList.remove(
                'is-none',
                'is-multi'
            );

            container.classList.add(
                'is-single'
            );

            list.style.display =
                '';

            if (!rows.length) {
                var row =
                    createColorRow(
                        container,
                        DEFAULT_SINGLE_COLOR
                    );

                if (row) {
                    list.appendChild(row);
                }
            }

            rows =
                getRows(container);

            while (
                rows.length > 1
            ) {
                rows[
                    rows.length - 1
                ].remove();

                rows =
                    getRows(container);
            }

            /*
             * Один цвет удалять нельзя,
             * поэтому крестик скрываем.
             */
            rows.forEach(
                function (row) {
                    var remove =
                        row.querySelector(
                            '[data-variant-color-remove]'
                        );

                    if (remove) {
                        remove.style.display =
                            'none';
                    }
                }
            );

            addButton.style.display =
                'none';

            return;
        }

        /*
         * MULTI
         */
        if (mode === 'multi') {
            container.classList.remove(
                'is-none',
                'is-single'
            );

            container.classList.add(
                'is-multi'
            );

            list.style.display =
                '';

            rows =
                getRows(container);

            if (!rows.length) {
                var first =
                    createColorRow(
                        container,
                        DEFAULT_SINGLE_COLOR
                    );

                var second =
                    createColorRow(
                        container,
                        DEFAULT_SECOND_COLOR
                    );

                if (first) {
                    list.appendChild(first);
                }

                if (second) {
                    list.appendChild(second);
                }
            } else if (
                rows.length === 1
            ) {
                var secondRow =
                    createColorRow(
                        container,
                        DEFAULT_SECOND_COLOR
                    );

                if (secondRow) {
                    list.appendChild(
                        secondRow
                    );
                }
            }

            rows =
                getRows(container);

            rows.forEach(
                function (row) {
                    var remove =
                        row.querySelector(
                            '[data-variant-color-remove]'
                        );

                    if (remove) {
                        remove.style.display =
                            '';
                    }
                }
            );

            addButton.style.display =
                rows.length >= MAX_COLORS
                    ? 'none'
                    : '';

            return;
        }
    }

    /* ================================================================
       INITIALIZATION
       ================================================================ */

    function initializeContainer(
        container
    ) {
        if (!container) {
            return;
        }

        updateUI(container);
    }

    function initializeAll() {
        document
            .querySelectorAll(
                '[data-variant-color]'
            )
            .forEach(
                function (container) {
                    initializeContainer(
                        container
                    );
                }
            );
    }

    /* ================================================================
       MODE CHANGE
       ================================================================ */

    document.addEventListener(
        'change',
        function (event) {
            if (
                !event.target.matches(
                    '[data-variant-color-mode]'
                )
            ) {
                return;
            }

            var container =
                getContainer(
                    event.target
                );

            updateUI(container);
        }
    );

    /* ================================================================
       COLOR PICKER -> TEXT
       ================================================================ */

    document.addEventListener(
        'input',
        function (event) {
            if (
                !event.target.matches(
                    '[data-variant-color-picker]'
                )
            ) {
                return;
            }

            var row =
                event.target.closest(
                    '[data-variant-color-row]'
                );

            if (!row) {
                return;
            }

            var valueInput =
                row.querySelector(
                    '[data-variant-color-value]'
                );

            if (!valueInput) {
                return;
            }

            valueInput.value =
                String(
                    event.target.value
                ).toUpperCase();
        }
    );

    /* ================================================================
       TEXT -> PICKER
       ================================================================ */

    document.addEventListener(
        'input',
        function (event) {
            if (
                !event.target.matches(
                    '[data-variant-color-value]'
                )
            ) {
                return;
            }

            /*
             * Разрешаем пользователю нормально
             * печатать, валидируем окончательно
             * на change.
             */
            event.target.value =
                String(
                    event.target.value
                )
                    .toUpperCase()
                    .slice(0, 7);
        }
    );

    document.addEventListener(
        'change',
        function (event) {
            if (
                !event.target.matches(
                    '[data-variant-color-value]'
                )
            ) {
                return;
            }

            var row =
                event.target.closest(
                    '[data-variant-color-row]'
                );

            if (!row) {
                return;
            }

            var picker =
                row.querySelector(
                    '[data-variant-color-picker]'
                );

            var value =
                normalizeHex(
                    event.target.value
                );

            if (!value) {
                var fallback =
                    picker
                        ? picker.value
                        : DEFAULT_SINGLE_COLOR;

                fallback =
                    String(
                        fallback
                    ).toUpperCase();

                event.target.value =
                    fallback;

                return;
            }

            event.target.value =
                value;

            if (picker) {
                picker.value =
                    value;
            }
        }
    );

    /* ================================================================
       ADD
       ================================================================ */

    document.addEventListener(
        'click',
        function (event) {
            var button =
                event.target.closest(
                    '[data-variant-color-add]'
                );

            if (!button) {
                return;
            }

            event.preventDefault();

            var container =
                getContainer(button);

            if (!container) {
                return;
            }

            if (
                getMode(container)
                !== 'multi'
            ) {
                return;
            }

            addColor(
                container,
                DEFAULT_SINGLE_COLOR
            );
        }
    );

    /* ================================================================
       REMOVE
       ================================================================ */

    document.addEventListener(
        'click',
        function (event) {
            var button =
                event.target.closest(
                    '[data-variant-color-remove]'
                );

            if (!button) {
                return;
            }

            event.preventDefault();

            var container =
                getContainer(button);

            var row =
                button.closest(
                    '[data-variant-color-row]'
                );

            if (
                !container
                || !row
            ) {
                return;
            }

            var mode =
                getMode(container);

            var rows =
                getRows(container);

            /*
             * В single удалить единственный
             * цвет нельзя.
             */
            if (
                mode === 'single'
            ) {
                return;
            }

            /*
             * В multi должно оставаться
             * минимум 2 цвета.
             */
            if (
                mode === 'multi'
                && rows.length <= 2
            ) {
                return;
            }

            row.remove();

            updateUI(container);
        }
    );

    /* ================================================================
       AJAX VARIANT CREATED
       ================================================================ */

    document.addEventListener(
        'variant:created',
        function (event) {
            var row =
                event.detail
                    ? event.detail.row
                    : null;

            if (!row) {
                return;
            }

            var container =
                row.querySelector(
                    '[data-variant-color]'
                );

            initializeContainer(
                container
            );
        }
    );

    /* ================================================================
       START
       ================================================================ */

    if (
        document.readyState
        === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            initializeAll,
            {
                once: true
            }
        );
    } else {
        initializeAll();
    }

})();
</script>
<script>
    function toggleCat(el) {
        const children = el.parentElement.querySelector('.cat-children');
        if (!children) return;

        if (children.style.display === 'none' || children.style.display === '') {
            children.style.display = 'block';
            el.textContent = '▼';
        } else {
            children.style.display = 'none';
            el.textContent = '▶';
        }
    }
</script>


<script type="text/javascript">
    $(document).ready(function () {
        $('select[name="category_more[]"]').multiselect();
    });
</script>

<style>

    .category-tree {
        font-size: 14px;
    }

    .cat-item {
        margin: 4px 0;
    }

    .cat-children {
        margin-left: 20px;
        display: none;
    }

    .toggle {
        cursor: pointer;
        display: inline-block;
        width: 16px;
        color: #555;
        user-select: none;
    }

    .toggle.empty {
        visibility: hidden;
    }


    ul.multiselect-container.dropdown-menu {
        min-width: 350px;
    }

    ul.multiselect-container.dropdown-menu {
        max-height: 250px;
        overflow-y: auto;
    }
   /* ==========================================================================
   PRODUCT VARIATIONS ADMIN
   ========================================================================== */

.product-variants-table {
	min-width: 1650px;
}

.product-variants-table > tbody > tr > td {
	vertical-align: top;
	padding: 10px 8px;
}

.variant-admin-label {
	display: block;
	margin-bottom: 6px;
	color: #444;
	font-size: 12px;
	font-weight: 500;
	line-height: 1.3;
}

.variant-active-checkbox {
	display: block;
	width: 24px;
	height: 24px;
	margin-top: 7px;
}

/* ==========================================================================
   VARIANT COLOR
   ========================================================================== */

.variant-color-cell {
	min-width: 260px;
}

.variant-color-mode {
	width: 100%;
	min-width: 210px;
}

.variant-color-list {
	display: flex;
	flex-direction: column;
	gap: 7px;
	margin-top: 9px;
}

.variant-color-row {
	display: grid;
	grid-template-columns:
		40px
		minmax(105px, 1fr)
		30px;
	align-items: center;
	gap: 7px;
}

.variant-color-picker {
	display: block;
	width: 40px;
	height: 36px;
	padding: 2px;
	border: 1px solid #cfd5df;
	border-radius: 4px;
	background: #fff;
	cursor: pointer;
}

.variant-color-picker::-webkit-color-swatch-wrapper {
	padding: 2px;
}

.variant-color-picker::-webkit-color-swatch {
	border: 0;
	border-radius: 3px;
}

.variant-color-value {
	height: 36px !important;
	padding: 6px 8px !important;
	font-family: Consolas, Monaco, monospace;
	font-size: 12px;
	text-transform: uppercase;
}

.variant-color-remove {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 30px;
	height: 30px;
	margin: 0;
	padding: 0;
}

.variant-color-add {
	margin-top: 9px;
}

.variant-color-help {
	display: block;
	margin-top: 7px;
	margin-bottom: 0;
	color: #999;
	font-size: 11px;
}

/* ==========================================================================
   VARIANT IMAGES
   ========================================================================== */

.variant-images-cell {
	min-width: 250px;
}

.variant-images-list {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	margin-top: 10px;
}

.variant-image-item {
	display: flex;
	flex-direction: column;
	gap: 5px;
	width: 66px;
}

.variant-image-preview {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 66px;
	height: 66px;
	border: 1px solid #e3e3e3;
	background: #fff;
}

.variant-image-preview img {
	display: block;
	max-width: 100%;
	max-height: 100%;
	object-fit: contain;
}

.variant-image-delete {
	position: absolute;
	top: 2px;
	right: 2px;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 22px;
	height: 22px;
	padding: 0;
	opacity: 0;
	transition: opacity 0.15s ease;
}

.variant-image-preview:hover
	.variant-image-delete {
	opacity: 1;
}

.variant-image-item .image_order {
	height: 30px;
	padding: 4px;
	text-align: center;
}

.variant-delete-button {
	margin-top: 25px;
}
}
</style>
