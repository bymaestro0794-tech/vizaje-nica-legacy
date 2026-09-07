<?php
defined('BASEPATH') or exit('No direct script access allowed');

$excludedBrandIds =
    !empty($excluded_brand_ids)
        ? array_map(
            'intval',
            (array) $excluded_brand_ids
        )
        : array();

$formatDate =
    static function ($value) {
        if (empty($value)) {
            return '';
        }

        $timestamp =
            strtotime(
                (string) $value
            );

        if ($timestamp === false) {
            return '';
        }

        return date(
            'Y-m-d',
            $timestamp
        );
    };

$brandIds =
    !empty($brand_ids)
        ? array_map(
            'intval',
            (array) $brand_ids
        )
        : array();
?>

<!-- BEGIN PAGE HEADER -->

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
            <a href="<?= $parent_url ?>">
                <?= htmlspecialchars(
                    $parent_title,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </a>

            <i class="fa fa-circle"></i>
        </li>

        <li>
            <span>
                <?= htmlspecialchars(
                    $title,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>
        </li>
    </ul>
</div>

<h1 class="page-title">
    <?= htmlspecialchars(
        $title,
        ENT_QUOTES,
        'UTF-8'
    ) ?>
</h1>

<!-- Messages -->

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

<?php if (isset($_SESSION['error'])) : ?>

    <div class="alert alert-block alert-danger fade in">
        <button
            type="button"
            class="close"
            data-dismiss="alert"
        ></button>

        <?php foreach ((array) $_SESSION['error'] as $error) : ?>

            <?= htmlspecialchars(
                (string) $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

            <br>

        <?php endforeach; ?>

    </div>

    <?php unset($_SESSION['error']); ?>

<?php endif; ?>

<div class="row">
    <div class="portlet light">
        <div class="portlet-body">

            <form method="post">

                <div class="panel-body">
                    <div class="table-scrollable">

                        <table
                            class="
                                table
                                table-bordered
                                table-striped
                                table-hover
                            "
                        >
                            <tbody>

                                <!-- Name -->

                                <tr>
                                    <td width="260">
                                        Название *
                                    </td>

                                    <td>
                                        <input
                                            type="text"
                                            name="name"
                                            class="form-control"
                                            value="<?= htmlspecialchars(
                                                (string) $item->name,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            required
                                        >
                                    </td>
                                </tr>

                                <!-- Code -->

                                <tr>
                                    <td>
                                        Код *
                                    </td>

                                    <td>
                                        <input
                                            type="text"
                                            name="code"
                                            class="form-control"
                                            maxlength="64"
                                            value="<?= htmlspecialchars(
                                                (string) $item->code,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            style="text-transform: uppercase;"
                                            required
                                        >
                                    </td>
                                </tr>

                                <!-- Type -->

                                <tr>
                                    <td>
                                        Тип скидки *
                                    </td>

                                    <td>
                                        <select
                                            name="type"
                                            class="
                                                form-control
                                                promo-type-select
                                            "
                                            required
                                        >
                                            <option
                                                value="percentage"
                                                <?= $item->type === 'percentage'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Процент
                                            </option>

                                            <option
                                                value="fixed"
                                                <?= $item->type === 'fixed'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Фиксированная сумма
                                            </option>
                                        </select>
                                    </td>
                                </tr>

                                <!-- Value -->

                                <tr>
                                    <td>
                                        Значение скидки *
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="value"
                                            class="form-control"
                                            min="0.01"
                                            step="0.01"
                                            value="<?= htmlspecialchars(
                                                (string) $item->value,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            required
                                        >
                                    </td>
                                </tr>

                                <!-- Minimum -->

                                <tr>
                                    <td>
                                        Минимальная сумма
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="min_amount"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            value="<?= $item->min_amount !== null
                                                ? htmlspecialchars(
                                                    (string) $item->min_amount,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                )
                                                : '' ?>"
                                        >

                                        <p
                                            class="help-block"
                                            style="margin-bottom: 0;"
                                        >
                                            Считается по товарам,
                                            подходящим под условия
                                            промокода.
                                        </p>
                                    </td>
                                </tr>

                                <!-- Max discount -->

                                <tr
                                    data-promo-max-discount-row
                                >
                                    <td>
                                        Максимальная скидка
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="max_discount"
                                            class="form-control"
                                            min="0.01"
                                            step="0.01"
                                            value="<?= $item->max_discount !== null
                                                ? htmlspecialchars(
                                                    (string) $item->max_discount,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                )
                                                : '' ?>"
                                        >
                                    </td>
                                </tr>

                                <!-- Usage limit -->

                                <tr>
                                    <td>
                                        Общий лимит использований
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="usage_limit"
                                            class="form-control"
                                            min="1"
                                            step="1"
                                            value="<?= $item->usage_limit !== null
                                                ? (int) $item->usage_limit
                                                : '' ?>"
                                        >
                                    </td>
                                </tr>

                                <!-- Per user -->

                                <tr>
                                    <td>
                                        Лимит на пользователя
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="per_user_limit"
                                            class="form-control"
                                            min="1"
                                            step="1"
                                            value="<?= $item->per_user_limit !== null
                                                ? (int) $item->per_user_limit
                                                : '' ?>"
                                        >
                                    </td>
                                </tr>

                                <!-- Starts -->

                                <tr>
                                    <td>
                                        Начало действия
                                    </td>

                                    <td>
                                        <input
																					type="date"
																					name="starts_at"
																					class="form-control"
																					value="<?= htmlspecialchars(
																							$formatDate(
																									$item->starts_at
																							),
																							ENT_QUOTES,
																							'UTF-8'
																					) ?>"
																			>
                                    </td>
                                </tr>

                                <!-- Ends -->

                                <tr>
                                    <td>
                                        Окончание действия
                                    </td>

                                    <td>
                                        <input
																					type="date"
																					name="ends_at"
																					class="form-control"
																					value="<?= htmlspecialchars(
																							$formatDate(
																									$item->ends_at
																							),
																							ENT_QUOTES,
																							'UTF-8'
																					) ?>"
																				>
                                    </td>
                                </tr>

                                <!-- Allow discounted -->

                                <tr>
                                    <td>
                                        Товары со скидкой
                                    </td>

                                    <td>
                                        <label
                                            class="
                                                mt-checkbox
                                                mt-checkbox-outline
                                            "
                                        >
                                            <input
                                                type="checkbox"
                                                name="allow_discounted"
                                                value="1"
                                                <?= !empty(
                                                    $item->allow_discounted
                                                )
                                                    ? 'checked'
                                                    : '' ?>
                                            >

                                            Разрешать применение
                                            к товарам, которые уже
                                            имеют скидку

                                            <span></span>
                                        </label>
                                    </td>
                                </tr>

																<tr>
																	<td>
																			Применение по брендам
																	</td>

																	<td>
																			<label
																					class="
																							mt-radio
																							mt-radio-outline
																					"
																					style="margin-right: 25px;"
																			>
																					<input
																							type="radio"
																							name="brand_scope"
																							value="all"
																							<?= $item->brand_scope === 'all'
																									? 'checked'
																									: '' ?>
																					>

																					Все бренды

																					<span></span>
																			</label>

																			<label
																					class="
																							mt-radio
																							mt-radio-outline
																					"
																			>
																					<input
																							type="radio"
																							name="brand_scope"
																							value="selected"
																							<?= $item->brand_scope === 'selected'
																									? 'checked'
																									: '' ?>
																					>

																					Только выбранные бренды

																					<span></span>
																			</label>
																	</td>
															</tr>

															<tr
																	data-promo-selected-brands
																	<?= $item->brand_scope === 'selected'
																			? ''
																			: 'hidden' ?>
															>
																	<td>
																			Выбранные бренды
																	</td>

																	<td>
																			<?php foreach ($brands as $brand) : ?>
																					<label
																							class="
																									mt-checkbox
																									mt-checkbox-outline
																							"
																							style="
																									display: block;
																									margin-bottom: 8px;
																							"
																					>
																							<input
																									type="checkbox"
																									name="brands[]"
																									value="<?= (int) $brand->id ?>"
																									<?= in_array(
																											(int) $brand->id,
																											$brandIds,
																											true
																									)
																											? 'checked'
																											: '' ?>
																							>

																							<?= htmlspecialchars(
																									(string) $brand->title,
																									ENT_QUOTES,
																									'UTF-8'
																							) ?>

																							<span></span>
																					</label>
																			<?php endforeach; ?>
																	</td>
															</tr>

                                <!-- Excluded brands -->

                                <tr
																		data-promo-excluded-brands
																		<?= $item->brand_scope === 'selected'
																				? 'hidden'
																				: '' ?>
																>
                                    <td>
                                        Исключённые бренды
                                    </td>

                                    <td>

                                        <?php if (!empty($brands)) : ?>

                                            <div
                                                style="
                                                    max-height: 300px;
                                                    overflow-y: auto;
                                                    border: 1px solid #e5e5e5;
                                                    padding: 12px 15px;
                                                    background: #fff;
                                                "
                                            >

                                                <?php foreach ($brands as $brand) : ?>

                                                    <label
                                                        class="
                                                            mt-checkbox
                                                            mt-checkbox-outline
                                                        "
                                                        style="
                                                            display: block;
                                                            margin-bottom: 8px;
                                                        "
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            name="excluded_brands[]"
                                                            value="<?= (int) $brand->id ?>"
                                                            <?= in_array(
                                                                (int) $brand->id,
                                                                $excludedBrandIds,
                                                                true
                                                            )
                                                                ? 'checked'
                                                                : '' ?>
                                                        >

                                                        <?= htmlspecialchars(
                                                            (string) $brand->title,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>

                                                        <span></span>
                                                    </label>

                                                <?php endforeach; ?>

                                            </div>

                                        <?php else : ?>

                                            <span class="text-muted">
                                                Бренды не найдены.
                                            </span>

                                        <?php endif; ?>

                                    </td>
                                </tr>

                                <!-- Status -->

                                <tr>
                                    <td>
                                        Статус *
                                    </td>

                                    <td>
                                        <select
                                            name="status"
                                            class="form-control"
                                            required
                                        >
                                            <option
                                                value="draft"
                                                <?= $item->status === 'draft'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Черновик
                                            </option>

                                            <option
                                                value="active"
                                                <?= $item->status === 'active'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Активен
                                            </option>

                                            <option
                                                value="disabled"
                                                <?= $item->status === 'disabled'
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                Отключён
                                            </option>
                                        </select>
                                    </td>
                                </tr>

                                <!-- Submit -->

                                <tr>
                                    <td>&nbsp;</td>

                                    <td>
                                        <button
                                            type="submit"
                                            class="btn green"
                                        >
                                            <i class="fa fa-check"></i>

                                            <?= lang('Edit') ?>
                                        </button>

                                        <a
                                            href="<?= $parent_url ?>"
                                            class="btn default"
                                        >
                                            Назад
                                        </a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const typeSelect =
            document.querySelector(
                '.promo-type-select'
            )

        const maxDiscountRow =
            document.querySelector(
                '[data-promo-max-discount-row]'
            )

        if (
            !typeSelect ||
            !maxDiscountRow
        ) {
            return
        }

        function updatePromoTypeState() {
            const input =
                maxDiscountRow.querySelector(
                    'input[name="max_discount"]'
                )

            const isPercentage =
                typeSelect.value ===
                'percentage'

            maxDiscountRow.style.display =
                isPercentage
                    ? ''
                    : 'none'

            if (
                !isPercentage &&
                input
            ) {
                input.value = ''
            }
        }

        typeSelect.addEventListener(
            'change',
            updatePromoTypeState
        )

        updatePromoTypeState()

				const scopeInputs =
    document.querySelectorAll(
        'input[name="brand_scope"]'
    )

const selectedBrandsRow =
    document.querySelector(
        '[data-promo-selected-brands]'
    )

const excludedBrandsRow =
    document.querySelector(
        '[data-promo-excluded-brands]'
    )

function updateBrandScopeState() {
    const checked =
        document.querySelector(
            'input[name="brand_scope"]:checked'
        )

    if (
        !checked ||
        !selectedBrandsRow ||
        !excludedBrandsRow
    ) {
        return
    }

    const isSelected =
        checked.value === 'selected'

    selectedBrandsRow.hidden =
        !isSelected

    excludedBrandsRow.hidden =
        isSelected
}

scopeInputs.forEach(
    function (input) {
        input.addEventListener(
            'change',
            updateBrandScopeState
        )
    }
)

updateBrandScopeState()
    }
)
</script>