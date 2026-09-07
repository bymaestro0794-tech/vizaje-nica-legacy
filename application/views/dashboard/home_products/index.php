<!-- BEGIN PAGE BAR -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>

            <a href="/<?= ADM_CONTROLLER ?>/menu/">
                <?= lang('Home') ?>
            </a>

            <i class="fa fa-circle"></i>
        </li>

        <li>
            <span><?= htmlspecialchars(
                $title,
                ENT_QUOTES,
                'UTF-8'
            ) ?></span>
        </li>
    </ul>
</div>
<!-- END PAGE BAR -->

<h1 class="page-title">
    <?= htmlspecialchars(
        $title,
        ENT_QUOTES,
        'UTF-8'
    ) ?>

    <small>
        <?= htmlspecialchars(
            $section_title,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </small>
</h1>
<div class="portlet light bordered">
    <div class="portlet-body">
        <ul class="nav nav-tabs">
            <?php foreach ($sections as $key => $section) : ?>
                <li class="<?= $section_key === $key
                    ? 'active'
                    : '' ?>">
                    <a
                        href="<?= $path ?>?section=<?= urlencode($key) ?>"
                    >
                        <?= htmlspecialchars(
                            $section['title'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-block alert-success fade in">
        <button
            type="button"
            class="close"
            data-dismiss="alert"
        ></button>

        <?= $_SESSION['success'] ?>
    </div>

    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (
	isset($_SESSION['error']) &&
	is_array($_SESSION['error']) &&
	array_filter($_SESSION['error'])
) : ?>
	<div class="alert alert-block alert-danger fade in">
		<button
			type="button"
			class="close"
			data-dismiss="alert"
		></button>

		<?php foreach ($_SESSION['error'] as $error) : ?>
			<?php if (trim((string) $error) === '') : ?>
				<?php continue; ?>
			<?php endif; ?>

			<?= htmlspecialchars(
				$error,
				ENT_QUOTES,
				'UTF-8'
			) ?>

			<br>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php unset($_SESSION['error']); ?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-search"></i>

                    <span class="caption-subject bold uppercase">
                        Найти и добавить товар
                    </span>
                </div>
            </div>

            <div class="portlet-body">
                <form
                    action=""
                    method="get"
                    class="form-horizontal"
                >
                    <div class="form-group">
                        <div class="col-md-10">
                            <input
                                type="hidden"
                                name="section"
                                value="<?= htmlspecialchars(
                                    $section_key,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <input
                                type="text"
                                name="query"
                                class="form-control"
                                value="<?= htmlspecialchars(
                                    $query,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                placeholder="Название, ID, SKU или штрихкод"
                                autocomplete="off"
                            >
                        </div>

                        <div class="col-md-2">
                            <button
                                type="submit"
                                class="btn blue btn-block"
                            >
                                <i class="fa fa-search"></i>
                                Найти
                            </button>
                        </div>
                    </div>
                </form>

                <?php if ($query !== '') : ?>
                    <?php if (!empty($search_products)) : ?>
                        <div class="table-scrollable">
                            <table
                                class="table table-bordered table-hover"
                            >
                                <thead>
                                    <tr>
                                        <th width="80">Фото</th>
                                        <th width="80">ID</th>
                                        <th width="130">SKU</th>
                                        <th>Название</th>
                                        <th width="160">Бренд</th>
                                        <th width="120">Цена</th>
                                        <th width="150">Действие</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach (
                                        $search_products as $product
                                    ) : ?>
                                        <?php
                                        $isSelected = in_array(
                                            (int) $product->id,
                                            $selected_product_ids,
                                            true
                                        );

                                        $productImage = !empty(
                                            $product->img
                                        )
                                            ? newthumbs(
                                                $product->img,
                                                'products'
                                            )
                                            : '';
                                        ?>

                                        <tr>
                                            <td>
                                                <?php if (
                                                    $productImage !== ''
                                                ) : ?>
                                                    <img
                                                        src="<?= htmlspecialchars(
                                                            $productImage,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                        alt=""
                                                        width="56"
                                                        height="56"
                                                        style="
                                                            display: block;
                                                            object-fit: contain;
                                                            background: #f7f7f7;
                                                        "
                                                    >
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?= (int) $product->id ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $product->SKU ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $product->titleRU
                                                            ?? '',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </strong>

                                                <?php if (
                                                    !empty(
                                                        $product->titleRO
                                                    )
                                                ) : ?>
                                                    <br>

                                                    <small>
                                                        <?= htmlspecialchars(
                                                            $product->titleRO,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $product->brand_title
                                                        ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= number_format(
                                                    (float) (
                                                        $product
                                                            ->discount_price
                                                        ?: $product->price
                                                    ),
                                                    2,
                                                    '.',
                                                    ' '
                                                ) ?>

                                                MDL
                                            </td>

                                            <td>
                                                <?php if ($isSelected) : ?>
                                                    <button
                                                        type="button"
                                                        class="btn default"
                                                        disabled
                                                    >
                                                        Уже добавлен
                                                    </button>
                                                <?php else : ?>
                                                    <form
                                                        	action="<?= htmlspecialchars(
                                                        		$add_product_path,
                                                        		ENT_QUOTES,
                                                        		'UTF-8'
                                                        	) ?>"
                                                        	method="post"
                                                        >
                                                        	<input
                                                        		type="hidden"
                                                        		name="product_id"
                                                        		value="<?= (int) $product->id ?>"
                                                        	>
                                                        
                                                        	<input
                                                        		type="hidden"
                                                        		name="section_key"
                                                        		value="<?= htmlspecialchars(
                                                        			$section_key,
                                                        			ENT_QUOTES,
                                                        			'UTF-8'
                                                        		) ?>"
                                                        	>
                                                        
                                                        	<button
                                                        		type="submit"
                                                        		class="btn green"
                                                        	>
                                                        		<i class="fa fa-plus"></i>
                                                        		Добавить
                                                        	</button>
                                                        </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            Товары по запросу не найдены.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-star"></i>

                    <span class="caption-subject bold uppercase">
                        Текущая подборка «<?= htmlspecialchars(
                            $section_title,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>»
                    </span>
                </div>
            </div>

            <div class="portlet-body">
                <?php if (!empty($selected_items)) : ?>
                    <form
                        	action="<?= htmlspecialchars(
                        		$update_order_path,
                        		ENT_QUOTES,
                        		'UTF-8'
                        	) ?>"
                        	method="post"
                        >
                            <input
                                type="hidden"
                                name="section_key"
                                value="<?= htmlspecialchars($section_key, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        <div class="table-scrollable">
                            <table
                                class="table table-bordered table-hover"
                            >
                                <thead>
                                    <tr>
                                        <th width="100">Порядок</th>
                                        <th width="90">Фото</th>
                                        <th width="80">ID</th>
                                        <th>Товар</th>
                                        <th width="160">Бренд</th>
                                        <th width="120">Цена</th>
                                        <th width="160">Показывать</th>
                                        <th width="130">Действие</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach (
                                        $selected_items as $item
                                    ) : ?>
                                        <?php
                                        $itemImage = !empty(
                                            $item->img
                                        )
                                            ? newthumbs(
                                                $item->img,
                                                'products'
                                            )
                                            : '';
                                        ?>

                                        <tr>
                                            <td>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    class="form-control text-center"
                                                    name="so[<?= (int) $item->id ?>]"
                                                    value="<?= (int) $item->sorder ?>"
                                                >
                                            </td>

                                            <td>
                                                <?php if (
                                                    $itemImage !== ''
                                                ) : ?>
                                                    <img
                                                        src="<?= htmlspecialchars(
                                                            $itemImage,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                        alt=""
                                                        width="64"
                                                        height="64"
                                                        style="
                                                            display: block;
                                                            object-fit: contain;
                                                            background: #f7f7f7;
                                                        "
                                                    >
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?= (int) $item->product_id ?>
                                            </td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $item->titleRU ?? '',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </strong>

                                                <br>

                                                <small>
                                                    SKU:
                                                    <?= htmlspecialchars(
                                                        $item->SKU ?? '',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </small>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $item->brand_title ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?php if (
                                                    !empty(
                                                        $item->discount_price
                                                    )
                                                ) : ?>
                                                    <strong>
                                                        <?= number_format(
                                                            (float) $item
                                                                ->discount_price,
                                                            2,
                                                            '.',
                                                            ' '
                                                        ) ?>

                                                        MDL
                                                    </strong>

                                                    <br>

                                                    <del>
                                                        <?= number_format(
                                                            (float) $item->price,
                                                            2,
                                                            '.',
                                                            ' '
                                                        ) ?>

                                                        MDL
                                                    </del>
                                                <?php else : ?>
                                                    <?= number_format(
                                                        (float) $item->price,
                                                        2,
                                                        '.',
                                                        ' '
                                                    ) ?>

                                                    MDL
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php
                                                $checked = !empty(
                                                    $item->isShown
                                                )
                                                    ? 'checked'
                                                    : '';
                                                ?>

                                                <label
                                                    class="mt-checkbox mt-checkbox-outline"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        <?= $checked ?>
                                                        value="<?= (int) $item->id ?>"
                                                        data-col="isShown"
                                                        data-table="home_product_items"
                                                        class="mine_change_check"
                                                    >

                                                    Показывать

                                                    <span></span>
                                                </label>
                                            </td>

                                            <td>
                                               <a
                                                	href="<?= $delete_product_path
                                                		. (int) $item->id
                                                		. '?section='
                                                		. urlencode($section_key) ?>"
                                                	class="btn red mine_delete_row"
                                                >
                                                	<i class="fa fa-trash"></i>
                                                	Удалить
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <button
                            type="submit"
                            class="btn green"
                        >
                            <i class="fa fa-check"></i>
                            Сохранить порядок
                        </button>
                    </form>
                <?php else : ?>
                    <div class="alert alert-info">
                        В подборку пока не добавлено ни одного товара.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>