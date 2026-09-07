<?php
defined('BASEPATH') or exit('No direct script access allowed');
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

<!-- ================================================================
     ADD PROMOCODE
     ================================================================ -->

<div class="row">
    <div class="portlet bordered">
        <div
            class="accordion"
            id="promocodes-accordion"
        >
            <div class="panel panel-default">

                <div
                    class="panel-heading"
                    style="background-color: #ffffff;"
                >
                    <h4 class="panel-title">
                        <a
                            class="accordion-toggle"
                            data-toggle="collapse"
                            data-parent="#promocodes-accordion"
                            href="#promocode-add"
                        >
                            <i class="fa fa-plus"></i>

                            <?= htmlspecialchars(
                                $add,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </a>
                    </h4>
                </div>

                <div
                    id="promocode-add"
                    class="panel-collapse collapse"
                >
                    <form
                        action="<?= $a_path ?>"
                        method="post"
                    >
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
                                                    placeholder="SUMMER20"
                                                    maxlength="64"
                                                    required
                                                    style="text-transform: uppercase;"
                                                >

                                                <p
                                                    class="help-block"
                                                    style="margin-bottom: 0;"
                                                >
                                                    Только латинские буквы,
                                                    цифры, "-" и "_".
                                                </p>
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
                                                    >
                                                        Процент
                                                    </option>

                                                    <option
                                                        value="fixed"
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
                                                    required
                                                >
                                            </td>
                                        </tr>

                                        <!-- Minimum amount -->

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
                                                    placeholder="Например: 500"
                                                >

                                                <p
                                                    class="help-block"
                                                    style="margin-bottom: 0;"
                                                >
                                                    Считается только по товарам,
                                                    на которые может действовать
                                                    промокод.
                                                </p>
                                            </td>
                                        </tr>

                                        <!-- Maximum discount -->

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
                                                    placeholder="Не ограничено"
                                                >

                                                <p
                                                    class="help-block"
                                                    style="margin-bottom: 0;"
                                                >
                                                    Используется для
                                                    процентного промокода.
                                                </p>
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
                                                    placeholder="Без ограничений"
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
                                                    value="1"
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
																											checked
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
																									>

																									Только выбранные бренды

																									<span></span>
																							</label>
																					</td>
																			</tr>

																			<tr data-promo-selected-brands hidden>
																				<td>
																						Выбранные бренды
																				</td>

																				<td>
																						<div
																								style="
																										max-height: 280px;
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
																														name="brands[]"
																														value="<?= (int) $brand->id ?>"
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
																				</td>
																		</tr>
                                        <!-- Excluded brands -->

                                       <tr data-promo-excluded-brands>
                                            <td>
                                                Исключённые бренды
                                            </td>

                                            <td>
                                                <?php if (!empty($brands)) : ?>

                                                    <div
                                                        style="
                                                            max-height: 280px;
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
                                                    <option value="draft">
                                                        Черновик
                                                    </option>

                                                    <option
                                                        value="active"
                                                        selected
                                                    >
                                                        Активен
                                                    </option>

                                                    <option value="disabled">
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

                                                    <?= lang('Add') ?>
                                                </button>
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
    </div>
</div>

<!-- ================================================================
     PROMOCODES LIST
     ================================================================ -->

<?php if (!empty($objects)) : ?>

    <div class="row">
        <div class="portlet light">
            <div class="portlet-body">

                <div class="table-scrollable">

                    <table
                        class="
                            table
                            table-bordered
                            table-striped
                            table-hover
                        "
                    >
                        <thead>
                            <tr>
                                <th>
                                    Название
                                </th>

                                <th width="160">
                                    Код
                                </th>

                                <th width="120">
                                    Скидка
                                </th>

                                <th width="150">
                                    Мин. сумма
                                </th>

                                <th width="190">
                                    Период
                                </th>

                                <th width="150">
                                    Использовано
                                </th>

                                <th width="120">
                                    Статус
                                </th>

                                <th width="220">
                                    <?= lang('Action') ?>
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($objects as $item) : ?>

                                <?php
                                $statusLabel = 'Черновик';
                                $statusClass = 'label-default';

                                if ($item->status === 'active') {
                                    $statusLabel = 'Активен';
                                    $statusClass = 'label-success';

                                    if (
                                        !empty($item->ends_at)
                                        && strtotime($item->ends_at) < time()
                                    ) {
                                        $statusLabel = 'Истёк';
                                        $statusClass = 'label-warning';
                                    } elseif (
                                        !empty($item->starts_at)
                                        && strtotime($item->starts_at) > time()
                                    ) {
                                        $statusLabel = 'Запланирован';
                                        $statusClass = 'label-info';
                                    }
                                } elseif (
                                    $item->status === 'disabled'
                                ) {
                                    $statusLabel = 'Отключён';
                                    $statusClass = 'label-danger';
                                }

                                $discountLabel = '';

                                if (
                                    $item->type === 'percentage'
                                ) {
                                    $discountLabel =
                                        rtrim(
                                            rtrim(
                                                number_format(
                                                    (float) $item->value,
                                                    2,
                                                    '.',
                                                    ''
                                                ),
                                                '0'
                                            ),
                                            '.'
                                        )
                                        . '%';
                                } else {
                                    $discountLabel =
                                        number_format(
                                            (float) $item->value,
                                            0,
                                            '.',
                                            ' '
                                        )
                                        . ' MDL';
                                }

                                $periodStart =
                                    !empty($item->starts_at)
                                        ? date(
                                            'd.m.Y',
                                            strtotime($item->starts_at)
                                        )
                                        : '—';

                                $periodEnd =
                                    !empty($item->ends_at)
                                        ? date(
                                            'd.m.Y',
                                            strtotime($item->ends_at)
                                        )
                                        : '—';
                                ?>

                                <tr>

                                    <td>
                                        <a
                                            href="<?= $e_path
                                                . (int) $item->id
                                                . '/' ?>"
                                            style="font-weight: 700;"
                                        >
                                            <?= htmlspecialchars(
                                                (string) $item->name,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </a>
                                    </td>

                                    <td>
                                        <code>
                                            <?= htmlspecialchars(
                                                (string) $item->code,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </code>
                                    </td>

                                    <td>
                                        <?= $discountLabel ?>
                                    </td>

                                    <td>
                                        <?php if (
                                            $item->min_amount !== null
                                        ) : ?>

                                            <?= number_format(
                                                (float) $item->min_amount,
                                                0,
                                                '.',
                                                ' '
                                            ) ?>
                                            MDL

                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= $periodStart ?>

                                        <br>

                                        <?= $periodEnd ?>
                                    </td>

                                    <td>
                                        <?= (int) $item->used_count ?>

                                        /

                                        <?= $item->usage_limit !== null
                                            ? (int) $item->usage_limit
                                            : '∞' ?>
                                    </td>

                                    <td>
                                        <span
                                            class="
                                                label
                                                <?= $statusClass ?>
                                            "
                                        >
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>

                                    <td>
                                        <a
                                            href="<?= $e_path
                                                . (int) $item->id
                                                . '/' ?>"
                                            class="btn blue"
                                        >
                                            <i class="fa fa-pencil"></i>

                                            <?= lang('Edit') ?>
                                        </a>

                                        <a
                                            href="<?= $del_path
                                                . (int) $item->id
                                                . '/' ?>"
                                            class="
                                                btn
                                                red
                                                mine_delete_row
                                            "
                                        >
                                            <i class="fa fa-trash"></i>

                                            <?= lang('Delete') ?>
                                        </a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>

<?php else : ?>

    <div class="alert alert-info">
        Промокоды пока не добавлены.
    </div>

<?php endif; ?>

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