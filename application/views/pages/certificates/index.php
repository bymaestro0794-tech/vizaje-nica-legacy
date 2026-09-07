<?php
defined('BASEPATH') or exit('No direct script access allowed');

$certificateBrands = !empty($certificates_by_brand)
    ? $certificates_by_brand
    : [];
?>

<main class="certificates-page">

    <section class="certificates-page__hero">
        <div class="certificates-page__container">

            <p class="certificates-page__eyebrow">
                <?= $lclang === 'ro'
                    ? 'Produse originale'
                    : 'Оригинальная продукция' ?>
            </p>

            <h1 class="certificates-page__title">
                <?= $lclang === 'ro'
                    ? 'Certificate de autenticitate'
                    : 'Сертификаты подлинности' ?>
            </h1>

            <p class="certificates-page__description">
                <?= $lclang === 'ro'
                    ? 'Documente oficiale care confirmă autenticitatea produselor și statutul distribuitorilor autorizați.'
                    : 'Официальные документы, подтверждающие подлинность продукции и статус авторизованных дистрибьюторов.' ?>
            </p>

        </div>
    </section>

    <section class="certificates-page__content">
        <div class="certificates-page__container">

            <?php if (!empty($certificateBrands)) : ?>

                <div class="certificates-list">

                    <?php foreach ($certificateBrands as $brandIndex => $brand) : ?>

                        <?php
                        $brandNumber = str_pad(
                            (string) ($brandIndex + 1),
                            2,
                            '0',
                            STR_PAD_LEFT
                        );
                        ?>

                        <article
                            class="certificates-brand"
                            data-certificates-brand
                        >
                            <button
                                type="button"
                                class="certificates-brand__trigger"
                                data-certificates-trigger
                                aria-expanded="false"
                            >
                                <span class="certificates-brand__info">

                                    <span class="certificates-brand__number">
                                        <?= $brandNumber ?>
                                    </span>

                                    <span class="certificates-brand__name">
                                        <?= htmlspecialchars(
                                            $brand['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>

                                </span>

                                <span class="certificates-brand__meta">

                                    <span class="certificates-brand__count">
                                        <?= count($brand['certificates']) ?>

                                        <?= $lclang === 'ro'
                                            ? 'documente'
                                            : 'документа' ?>
                                    </span>

                                    <span
                                        class="certificates-brand__icon"
                                        aria-hidden="true"
                                    ></span>

                                </span>
                            </button>

                            <div class="certificates-brand__panel">
                                <div class="certificates-brand__panel-inner">

                                    <div class="certificates-grid">

                                        <?php foreach (
                                            $brand['certificates']
                                            as $certificateIndex => $certificate
                                        ) : ?>

                                            <?php
                                            $certificateNumber = str_pad(
                                                (string) ($certificateIndex + 1),
                                                2,
                                                '0',
                                                STR_PAD_LEFT
                                            );

                                            $imageUrl =
                                                '/public/brand_certificates/'
                                                . rawurlencode(
                                                    $certificate['image']
                                                );

                                            $certificateTitle =
                                                !empty($certificate['title'])
                                                    ? $certificate['title']
                                                    : $brand['title'];
                                            ?>

                                            <button
                                                type="button"
                                                class="certificate-card"
                                                data-certificate-open
                                                data-certificate-image="<?= htmlspecialchars(
                                                    $imageUrl,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                data-certificate-title="<?= htmlspecialchars(
                                                    $certificateTitle,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >
                                                <span class="certificate-card__preview">

                                                    <img
                                                        src="<?= htmlspecialchars(
                                                            $imageUrl,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                        alt="<?= htmlspecialchars(
                                                            $certificateTitle,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>"
                                                        loading="lazy"
                                                        decoding="async"
                                                    >

                                                    <span class="certificate-card__overlay">
                                                        <span class="certificate-card__view">
                                                            <?= $lclang === 'ro'
                                                                ? 'Deschide documentul'
                                                                : 'Открыть документ' ?>
                                                        </span>
                                                    </span>

                                                </span>

                                                <span class="certificate-card__content">

                                                    <span class="certificate-card__index">
                                                        <?= $certificateNumber ?>
                                                    </span>

                                                    <strong class="certificate-card__title">
                                                        <?= htmlspecialchars(
                                                            $certificateTitle,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </strong>

                                                </span>
                                            </button>

                                        <?php endforeach; ?>

                                    </div>

                                </div>
                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else : ?>

                <div class="certificates-empty">

                    <h2 class="certificates-empty__title">
                        <?= $lclang === 'ro'
                            ? 'Certificatele vor apărea în curând'
                            : 'Сертификаты скоро появятся' ?>
                    </h2>

                    <p class="certificates-empty__text">
                        <?= $lclang === 'ro'
                            ? 'Pregătim documentele oficiale pentru publicare.'
                            : 'Мы подготавливаем официальные документы к публикации.' ?>
                    </p>

                </div>

            <?php endif; ?>

        </div>
    </section>

    <div
        class="certificate-lightbox"
        data-certificate-lightbox
        aria-hidden="true"
        role="dialog"
        aria-modal="true"
        aria-labelledby="certificate-lightbox-title"
    >
        <button
            type="button"
            class="certificate-lightbox__backdrop"
            data-certificate-close
            aria-label="<?= $lclang === 'ro'
                ? 'Închide'
                : 'Закрыть' ?>"
        ></button>

        <div class="certificate-lightbox__dialog">

            <div class="certificate-lightbox__header">

                <h2
                    class="certificate-lightbox__title"
                    id="certificate-lightbox-title"
                    data-certificate-lightbox-title
                ></h2>

                <a
                    href="#"
                    class="certificate-lightbox__original"
                    data-certificate-original
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?= $lclang === 'ro'
                        ? 'Deschide originalul'
                        : 'Открыть оригинал' ?>
                </a>

            </div>

            <div class="certificate-lightbox__body">
                <img
                    src=""
                    alt=""
                    data-certificate-lightbox-image
                >
            </div>

            <button
                type="button"
                class="certificate-lightbox__close"
                data-certificate-close
                aria-label="<?= $lclang === 'ro'
                    ? 'Închide'
                    : 'Закрыть' ?>"
            >
                <span></span>
                <span></span>
            </button>

        </div>
    </div>

</main>

<script src="/app/js/pages/certificates.js" defer></script>