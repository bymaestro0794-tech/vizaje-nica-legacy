<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';
$pageTitle = trim((string) $page_name);
?>
<main class="page page--legal-document legal-file-page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>" class="breadcrums__link"><?= htmlspecialchars($home_bc_title, ENT_QUOTES, 'UTF-8') ?></a>
                </li>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></p>
                </li>
            </ul>
        </div>
    </section>

    <section class="legal-document-page">
        <div class="catalog__container _container legal-document__container">
            <header class="legal-document__header">
                <h1 class="legal-document__title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            </header>

            <article class="legal-document__body consumer-info-card">
                <p class="consumer-info-card__intro">
                    <?= $isRomanian
                        ? 'Autoritatea abilitată cu funcții de protecție a consumatorilor:'
                        : 'Уполномоченным органом, наделённым функциями по защите прав потребителей, является:' ?>
                </p>

                <h2><?= $isRomanian
                    ? 'Agenția pentru Protecția Consumatorilor și Supravegherea Pieței'
                    : 'Агентство по защите прав потребителей и надзору за рынком' ?></h2>

                <dl class="consumer-info-card__details">
                    <div>
                        <dt><?= $isRomanian ? 'Adresă' : 'Адрес' ?></dt>
                        <dd><?= $isRomanian
                            ? 'mun. Chișinău, str. Vasile Alexandri, 78'
                            : 'г. Кишинёв, ул. Василе Александри, 78' ?></dd>
                    </div>
                    <div>
                        <dt><?= $isRomanian ? 'Telefon' : 'Телефон' ?></dt>
                        <dd><a href="tel:+37322515151">(022) 51-51-51</a>; <a href="tel:080028028">0-800-28-028</a></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><a href="mailto:consumator@apc.gov.md">consumator@apc.gov.md</a></dd>
                    </div>
                    <div>
                        <dt><?= $isRomanian ? 'Site' : 'Сайт' ?></dt>
                        <dd><a href="https://consumator.gov.md" target="_blank" rel="noopener noreferrer">consumator.gov.md</a></dd>
                    </div>
                </dl>

                <p class="consumer-info-card__note">
                    <?= $isRomanian
                        ? 'Reclamația este examinată la prezentarea bonului fiscal sau a unui alt document care confirmă achiziționarea produsului ori serviciului.'
                        : 'Рекламация рассматривается при предъявлении кассового чека или другого документа, подтверждающего покупку товара или услуги.' ?>
                </p>
            </article>
        </div>
    </section>
</main>
