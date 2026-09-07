<div class="row">
    <div class="portlet light">
        <div class="portlet-body">
            <div class="panel-body">

                <ul class="nav nav-pills">
                    <li class="active">
                        <a href="#tab_1_1" data-toggle="tab">
                            <?= lang('General information') ?>
                        </a>
                    </li>

                    <li>
                        <a href="#tab_1_2" data-toggle="tab">
                            <?= lang('SEO') ?>
                        </a>
                    </li>

                    <li>
                        <a href="#tab_1_3" data-toggle="tab">
                            Сертификаты
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- =====================================================
                         Общая информация
                         ===================================================== -->
                    <div class="tab-pane fade active in" id="tab_1_1">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="form_type" value="general">

                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>

                                    <tr>
                                        <td width="200"><?= lang('Title') ?> *</td>
                                        <td>
                                            <input
                                                type="text"
                                                name="title"
                                                class="form-control"
                                                value="<?= htmlspecialchars(
                                                    $item->title,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                required
                                            >
                                        </td>
                                    </tr>

                                    <?php foreach (language(true) as $lang) : ?>
                                        <?php $langUpper = strtoupper($lang); ?>

                                        <tr>
                                            <td width="200">
                                                H1 заголовок <?= $langUpper ?>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="h1<?= $langUpper ?>"
                                                    class="form-control"
                                                    value="<?= htmlspecialchars(
                                                        $item->{'h1' . $langUpper},
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php foreach (language(true) as $lang) : ?>
                                        <?php $langUpper = strtoupper($lang); ?>

                                        <tr>
                                            <td width="200">
                                                Название для хлебных крошек <?= $langUpper ?>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="breadcrumbTitle<?= $langUpper ?>"
                                                    class="form-control"
                                                    value="<?= htmlspecialchars(
                                                        $item->{'breadcrumbTitle' . $langUpper},
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php foreach (language(true) as $lang) : ?>
                                        <?php $langUpper = strtoupper($lang); ?>

                                        <tr>
                                            <td width="200">
                                                <?= lang('Text') ?> <?= $langUpper ?>
                                            </td>
                                            <td>
                                                <textarea
                                                    name="text<?= $langUpper ?>"
                                                    cols="30"
                                                    rows="3"
                                                    class="form-control"
                                                ><?= htmlspecialchars(
                                                    $item->{'text' . $langUpper},
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?></textarea>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <tr>
                                        <td width="200"><?= lang('Photo') ?></td>
                                        <td>
                                            <input
                                                type="file"
                                                name="img"
                                                id="file"
                                                class="form-control"
                                                accept=".jpg,.jpeg,.png,.webp"
                                            >

                                            <div
                                                class="note note-warning"
                                                style="margin-bottom: 0; margin-top: 10px;"
                                            >
                                                <p>
                                                    <?= lang('Allowable sizes') ?>:
                                                    400x400px
                                                </p>
                                            </div>

                                            <?php if (!empty($item->img)) : ?>
                                                <?php
                                                $src = newthumbs(
                                                    $item->img,
                                                    $table,
                                                    250,
                                                    250,
                                                    '250x250x1',
                                                    1
                                                );
                                                ?>

                                                <br>

                                                <div class="mt-element-card mt-element-overlay item">
                                                    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                                        <div class="mt-card-item">
                                                            <div class="mt-card-avatar mt-overlay-1">
                                                                <img
                                                                    src="<?= $src ?>"
                                                                    alt="<?= htmlspecialchars(
                                                                        $item->title,
                                                                        ENT_QUOTES,
                                                                        'UTF-8'
                                                                    ) ?>"
                                                                >

                                                                <div class="mt-overlay">
                                                                    <ul class="mt-info">
                                                                        <li>
                                                                            <a
                                                                                class="btn red mine_delete_photo"
                                                                                data-table="<?= $table ?>"
                                                                                data-id="<?= $item->id ?>"
                                                                                data-col="img"
                                                                                href="javascript:;"
                                                                            >
                                                                                <i class="fa fa-ban"></i>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green">
                                                <i class="fa fa-check"></i>
                                                <?= lang('Edit') ?>
                                            </button>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                    <!-- =====================================================
                         SEO
                         ===================================================== -->
                    <div class="tab-pane fade" id="tab_1_2">
                        <form method="post">
                            <input type="hidden" name="form_type" value="seo">

                            <div class="table-scrollable">
                                <table class="table table-bordered table-striped table-hover">
                                    <tbody>

                                    <?php foreach (language(true) as $lang) : ?>
                                        <?php $langUpper = strtoupper($lang); ?>

                                        <tr>
                                            <td width="200">
                                                SEO Title (&lt;title&gt;) <?= $langUpper ?>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="seoTitle<?= $langUpper ?>"
                                                    class="form-control"
                                                    value="<?= htmlspecialchars(
                                                        $item->{'seoTitle' . $langUpper},
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php foreach (language(true) as $lang) : ?>
                                        <?php $langUpper = strtoupper($lang); ?>

                                        <tr>
                                            <td width="200">
                                                <?= lang('Keywords') ?> <?= $langUpper ?>
                                            </td>
                                            <td>
                                                <textarea
                                                    name="seoKeywords<?= $langUpper ?>"
                                                    cols="30"
                                                    rows="3"
                                                    class="form-control"
                                                ><?= htmlspecialchars(
                                                    $item->{'seoKeywords' . $langUpper},
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?></textarea>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php foreach (language(true) as $lang) : ?>
                                        <?php $langUpper = strtoupper($lang); ?>

                                        <tr>
                                            <td width="200">
                                                <?= lang('Description') ?> <?= $langUpper ?>
                                            </td>
                                            <td>
                                                <textarea
                                                    name="seoDesc<?= $langUpper ?>"
                                                    cols="30"
                                                    rows="3"
                                                    class="form-control"
                                                ><?= htmlspecialchars(
                                                    $item->{'seoDesc' . $langUpper},
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?></textarea>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php foreach (language(true) as $lang) : ?>
                                        <?php $langUpper = strtoupper($lang); ?>

                                        <tr>
                                            <td width="200">
                                                SEO-текст <?= $langUpper ?>
                                            </td>
                                            <td>
                                                <textarea
                                                    name="seoText<?= $langUpper ?>"
                                                    cols="30"
                                                    rows="6"
                                                    class="form-control ckeditor"
                                                ><?= $item->{'seoText' . $langUpper} ?></textarea>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <tr>
                                        <td width="200">&nbsp;</td>
                                        <td>
                                            <button type="submit" class="btn green">
                                                <i class="fa fa-check"></i>
                                                <?= lang('Edit') ?>
                                            </button>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>

                    <!-- =====================================================
                         Сертификаты
                         ===================================================== -->
                    <div class="tab-pane fade" id="tab_1_3">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong>Добавить сертификат</strong>
                            </div>

                            <div class="panel-body">
                                <form
                                    action="/cp/brands/certificate_put/<?= $item->id ?>"
                                    method="post"
                                    enctype="multipart/form-data"
                                >
                                    <div class="table-scrollable">
                                        <table class="table table-bordered table-striped">
                                            <tbody>

                                            <tr>
                                                <td width="200">Название RU</td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        name="titleRU"
                                                        class="form-control"
                                                        placeholder="Например: Письмо об авторизации"
                                                    >
                                                </td>
                                            </tr>

                                            <tr>
                                                <td width="200">Название RO</td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        name="titleRO"
                                                        class="form-control"
                                                        placeholder="Например: Scrisoare de autorizare"
                                                    >
                                                </td>
                                            </tr>

                                            <tr>
                                                <td width="200">Документ *</td>
                                                <td>
                                                    <input
                                                        type="file"
                                                        name="image"
                                                        class="form-control"
                                                        accept=".jpg,.jpeg,.png,.webp"
                                                        required
                                                    >

                                                    <div
                                                        class="note note-warning"
                                                        style="margin-top: 10px; margin-bottom: 0;"
                                                    >
                                                        <p>
                                                            Поддерживаются JPG, PNG и WebP.
                                                            Загружайте оригинал документа
                                                            в хорошем качестве.
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td width="200">Порядок</td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        name="sorder"
                                                        class="form-control"
                                                        value="0"
                                                        min="0"
                                                    >
                                                </td>
                                            </tr>

                                            <tr>
                                                <td width="200">Выводить на сайте</td>
                                                <td>
                                                    <label class="mt-checkbox mt-checkbox-outline">
                                                        <input
                                                            type="checkbox"
                                                            name="isShown"
                                                            value="1"
                                                            checked
                                                        >
                                                        Показывать сертификат
                                                        <span></span>
                                                    </label>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td width="200">&nbsp;</td>
                                                <td>
                                                    <button type="submit" class="btn green">
                                                        <i class="fa fa-check"></i>
                                                        Добавить сертификат
                                                    </button>
                                                </td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <?php if (!empty($certificates)) : ?>
                            <form
                                action="/cp/brands/certificates_update_order/<?= $item->id ?>"
                                method="post"
                            >
                                <div class="table-scrollable">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th width="90">Порядок</th>
                                            <th width="120">Превью</th>
                                            <th>Название</th>
                                            <th width="180">На сайте</th>
                                            <th width="260">Действия</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        <?php foreach ($certificates as $certificate) : ?>
                                            <tr>
                                                <td class="align-middle">
                                                    <input
                                                        type="text"
                                                        name="so[<?= $certificate->id ?>]"
                                                        value="<?= (int) $certificate->sorder ?>"
                                                        class="form-control text-center"
                                                        onkeyup="this.value=this.value.replace(/[^\d]/g,'')"
                                                    >
                                                </td>

                                                <td class="align-middle">
                                                    <?php
                                                    $certificateSrc = newthumbs(
                                                        $certificate->image,
                                                        'brand_certificates',
                                                        100,
                                                        140,
                                                        '100x140x1',
                                                        1
                                                    );
                                                    ?>

                                                    <a
                                                        href="<?= $certificateSrc ?>"
                                                        target="_blank"
                                                        rel="noopener"
                                                    >
                                                        <img
                                                            src="<?= $certificateSrc ?>"
                                                            alt="<?= htmlspecialchars(
                                                                $certificate->titleRU ?: 'Сертификат',
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>"
                                                            style="
                                                                width: 70px;
                                                                height: 90px;
                                                                object-fit: contain;
                                                                background: #fff;
                                                                border: 1px solid #ddd;
                                                            "
                                                        >
                                                    </a>
                                                </td>

                                                <td class="align-middle">
                                                    <strong>
                                                        <?= !empty($certificate->titleRU)
                                                            ? htmlspecialchars(
                                                                $certificate->titleRU,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            )
                                                            : 'Без названия' ?>
                                                    </strong>

                                                    <?php if (!empty($certificate->titleRO)) : ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars(
                                                                $certificate->titleRO,
                                                                ENT_QUOTES,
                                                                'UTF-8'
                                                            ) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="align-middle">
                                                    <?php
                                                    $checked = !empty($certificate->isShown)
                                                        ? 'checked'
                                                        : '';
                                                    ?>

                                                    <label class="mt-checkbox mt-checkbox-outline">
                                                        <input
                                                            type="checkbox"
                                                            class="mine_change_check"
                                                            data-table="brand_certificates"
                                                            data-col="isShown"
                                                            value="<?= $certificate->id ?>"
                                                            <?= $checked ?>
                                                        >
                                                        Выводить на сайте
                                                        <span></span>
                                                    </label>
                                                </td>

                                                <td class="align-middle">
                                                    <a
                                                        href="/cp/brands/certificate_update/<?= $item->id ?>/<?= $certificate->id ?>"
                                                        class="btn btn-xs default btn-editable green-stripe"
                                                    >
                                                        <i class="glyphicon glyphicon-edit"></i>
                                                        Редактировать
                                                    </a>

                                                    <a
                                                        href="/cp/brands/certificate_delete/<?= $item->id ?>/<?= $certificate->id ?>"
                                                        class="btn btn-xs default btn-editable red-stripe mine_delete_row"
                                                    >
                                                        <i class="glyphicon glyphicon-remove-circle"></i>
                                                        Удалить
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <button type="submit" class="btn green">
                                    <i class="fa fa-check"></i>
                                    Обновить порядок
                                </button>
                            </form>
                        <?php else : ?>
                            <div class="alert alert-info">
                                Для этого бренда сертификаты ещё не добавлены.
                            </div>
                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>