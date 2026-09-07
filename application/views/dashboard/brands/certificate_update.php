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
            <a href="<?= $parent_url ?>">
                <?= $parent_title ?>
            </a>

            <i class="fa fa-circle"></i>
        </li>

        <li>
            <span><?= $title ?></span>
        </li>
    </ul>
</div>

<h1 class="page-title">
    <?= $title ?>
</h1>

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

        <?php foreach ($_SESSION['error'] as $error) : ?>
            <?= htmlspecialchars(
                $error,
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

            <form
                method="post"
                enctype="multipart/form-data"
            >
                <div class="table-scrollable">
                    <table
                        class="table table-bordered table-striped table-hover"
                    >
                        <tbody>

                        <tr>
                            <td width="220">
                                Бренд
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                        $brand->title,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </strong>
                            </td>
                        </tr>

                        <tr>
                            <td width="220">
                                Название RU
                            </td>

                            <td>
                                <input
                                    type="text"
                                    name="titleRU"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $certificate->titleRU,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                            </td>
                        </tr>

                        <tr>
                            <td width="220">
                                Название RO
                            </td>

                            <td>
                                <input
                                    type="text"
                                    name="titleRO"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $certificate->titleRO,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                            </td>
                        </tr>

                        <tr>
                            <td width="220">
                                Текущий документ
                            </td>

                            <td>
                                <?php if (!empty($certificate->image)) : ?>
                                    <?php
                                    $certificateSrc = newthumbs(
                                        $certificate->image,
                                        'brand_certificates',
                                        260,
                                        360,
                                        '260x360x1',
                                        1
                                    );
                                    ?>

                                    <img
                                        src="<?= $certificateSrc ?>"
                                        alt=""
                                        style="
                                            display: block;
                                            width: 180px;
                                            height: 240px;
                                            object-fit: contain;
                                            background: #fff;
                                            border: 1px solid #ddd;
                                            padding: 8px;
                                        "
                                    >
                                <?php else : ?>
                                    <span class="text-muted">
                                        Документ отсутствует
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <td width="220">
                                Заменить документ
                            </td>

                            <td>
                                <input
                                    type="file"
                                    name="image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >

                                <div
                                    class="note note-warning"
                                    style="
                                        margin-top: 10px;
                                        margin-bottom: 0;
                                    "
                                >
                                    <p>
                                        Оставьте поле пустым,
                                        чтобы сохранить текущий документ.
                                    </p>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td width="220">
                                Порядок
                            </td>

                            <td>
                                <input
                                    type="number"
                                    name="sorder"
                                    class="form-control"
                                    min="0"
                                    value="<?= (int) $certificate->sorder ?>"
                                >
                            </td>
                        </tr>

                        <tr>
                            <td width="220">
                                Выводить на сайте
                            </td>

                            <td>
                                <label
                                    class="mt-checkbox mt-checkbox-outline"
                                >
                                    <input
                                        type="checkbox"
                                        name="isShown"
                                        value="1"
                                        <?= !empty($certificate->isShown)
                                            ? 'checked'
                                            : '' ?>
                                    >

                                    Показывать сертификат

                                    <span></span>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <td width="220"></td>

                            <td>
                                <button
                                    type="submit"
                                    class="btn green"
                                >
                                    <i class="fa fa-check"></i>
                                    Сохранить
                                </button>

                                <a
                                    href="<?= $parent_url ?>"
                                    class="btn default"
                                >
                                    Отмена
                                </a>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </form>

        </div>
    </div>
</div>