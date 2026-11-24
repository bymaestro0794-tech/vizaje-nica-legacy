<?php
$isPrivacyPolicyPage = !empty($is_privacy_policy_page);
$isLegalDocumentPage = !empty($is_legal_document_page);
$privacyPolicyText = (string) $page->text;
$legalDocumentText = '';
$privacyTocItems = [];

if ($isPrivacyPolicyPage) {
    $privacyPolicyText = (string) (
        !empty($page->desc)
            ? $page->desc
            : $page->text
    );

    // Replace the legacy garbled creator-attribution block by its localized copy.
    $attributionMarker = '<!--creator-attribution-privacy-cookie-' . ($lclang === 'ro' ? 'ro' : 'ru') . '-v1-->';
    $markerPosition = strpos($privacyPolicyText, $attributionMarker);
    if ($markerPosition !== false) {
        $sectionStart = strripos(substr($privacyPolicyText, 0, $markerPosition), '<section');
        if ($sectionStart !== false) {
            if ($lclang === 'ro') {
                $replacementSection = '<section><h2>Atribuirea comenzilor prin linkurile creatorilor</h2>'
                    . '<p>Dacă accesați site-ul prin linkul personal al unui creator, păstrăm identificatorul linkului în sesiunea serverului. Astfel, cel mult o comandă ulterioară poate fi asociată creatorului respectiv în termen de 7 zile de la accesare. Asocierea este folosită pentru calcularea comisionului creatorului după finalizarea comenzii cu statutul „Închis”.</p>'
                    . '<p>Perioada de atribuire se încheie după prima comandă sau după 7 zile. Alegerea setărilor pentru cookie-urile de analiză și publicitate nu schimbă această atribuire. Dacă sesiunea site-ului se încheie sau cookie-urile site-ului sunt dezactivate ori șterse, accesarea poate să nu fie păstrată.</p>'
                    . '<p>Fiecare accesare a linkului scurt mărește contorul total afișat în cabinetul creatorului; acesta nu reprezintă numărul persoanelor unice. Identificatorul linkului nu este transmis platformelor externe de publicitate sau analiză. Înregistrarea atribuirii nu stochează adresa IP sau user-agent-ul. După asocierea cu o comandă, legătura cu creatorul se păstrează împreună cu datele comenzii conform termenelor de păstrare pentru comenzi și decontări.</p>'
                    . '<h3>Informații despre sesiune și cookie-uri</h3>'
                    . '<p>Pentru menținerea sesiunii, site-ul folosește propriul identificator tehnic de sesiune. Identificatorul linkului și perioada de atribuire sunt păstrate pe server; pentru atribuire nu se utilizează un cookie separat de publicitate sau analiză. Setările cookie-urilor de analiză și marketing nu dezactivează această funcție.</p></section>';
            } else {
                $replacementSection = '<section><h2>Атрибуция заказов по ссылкам креаторов</h2>'
                    . '<p>Если вы переходите на сайт по персональной ссылке креатора, мы сохраняем идентификатор ссылки в серверной сессии сайта. Это позволяет связать не более одного последующего заказа с соответствующим креатором в течение 7 дней после перехода. Связь используется для расчёта вознаграждения креатора после завершения заказа со статусом «Закрыт».</p>'
                    . '<p>Срок атрибуции истекает после первого заказа или через 7 дней. Выбор настроек аналитических и рекламных cookies не меняет эту атрибуцию. Если сессия сайта завершится или cookies сайта будут отключены либо удалены, переход может не сохраниться.</p>'
                    . '<p>Каждый переход по короткой ссылке увеличивает её общий счётчик в кабинете креатора; он не является подсчётом уникальных людей. Идентификатор ссылки не передаётся внешним рекламным или аналитическим платформам. В записи атрибуции не сохраняются IP-адрес и user-agent. После привязки к заказу связь с креатором хранится вместе с данными заказа в соответствии со сроками хранения заказов и расчётов.</p>'
                    . '<h3>Сведения о сессии и cookies</h3>'
                    . '<p>Для сохранения сессии сайт использует собственный технический идентификатор сессии. Идентификатор ссылки и срок её действия хранятся на сервере; отдельная рекламная или аналитическая cookie для атрибуции не используется. Настройки аналитических и маркетинговых cookies не отключают эту функцию.</p></section>';
            }
            $sectionEnd = strpos($privacyPolicyText, '</section>', $markerPosition);
            $sectionEnd = $sectionEnd === false ? $markerPosition : $sectionEnd + strlen('</section>');
            $privacyPolicyText = substr($privacyPolicyText, 0, $sectionStart)
                . $replacementSection
                . substr($privacyPolicyText, $sectionEnd + strlen($attributionMarker));
        }
    }

    // The policy content contains its own title and update date. They are
    // rendered in the dedicated page header below, so avoid showing them twice.
    $privacyPolicyText = preg_replace(
        '/<h1\b[^>]*>.*?<\/h1>/is',
        '',
        $privacyPolicyText,
        1
    );
    $privacyPolicyText = preg_replace(
        '/<p\b[^>]*>.*?(?:Последнее обновление|Ultima actualizare):.*?<\/p>/isu',
        '',
        $privacyPolicyText,
        1
    );

    $privacySectionNumber = 0;
    $privacyPolicyText = preg_replace_callback(
        '/<h2(\s[^>]*)?>(.*?)<\/h2>/is',
        static function ($matches) use (&$privacySectionNumber) {
            $privacySectionNumber++;
            $attributes = !empty($matches[1]) ? $matches[1] : '';

            if (preg_match('/\bid\s*=/i', $attributes)) {
                $sectionId = 'privacy-section-' . $privacySectionNumber;
                $attributes = preg_replace(
                    '/\bid\s*=\s*(["\'])[^"\']*\1/i',
                    'id="' . $sectionId . '"',
                    $attributes,
                    1
                );
            } else {
                $attributes .= ' id="privacy-section-' . $privacySectionNumber . '"';
            }

            $heading = preg_replace(
                '/^\s*\d+(?:\.\d+)*\.\s*/u',
                '',
                trim((string) $matches[2])
            );

            return '<h2' . $attributes . '>' . $heading . '</h2>';
        },
        $privacyPolicyText
    );

    // Keep the introductory operator information inside section 01, matching
    // the article layout and the table of contents hierarchy.
    $privacyPolicyText = preg_replace_callback(
        '/\A(.*?)(<h2\b[^>]*id=["\']privacy-section-1["\'][^>]*>.*?<\/h2>)/is',
        static function ($matches) {
            return $matches[2] . $matches[1];
        },
        $privacyPolicyText,
        1
    );

    preg_match_all(
        '/<h2(?:\s[^>]*)?>(.*?)<\/h2>/is',
        $privacyPolicyText,
        $privacyHeadings
    );

    foreach ($privacyHeadings[1] ?? [] as $index => $heading) {
        $label = trim(
            preg_replace(
                '/\s+/u',
                ' ',
                html_entity_decode(strip_tags($heading), ENT_QUOTES, 'UTF-8')
            )
        );
        $label = preg_replace('/^\d+(?:\.\d+)*\.\s*/u', '', $label);

        if ($label === '') {
            continue;
        }

        $privacyTocItems[] = [
            'id' => 'privacy-section-' . ($index + 1),
            'label' => $label,
        ];
    }
}

if ($isLegalDocumentPage && !$isPrivacyPolicyPage) {
    $legalDocumentText = trim((string) $page->desc . "\n" . (string) $page->text);
    $legalDocumentText = preg_replace(
        '/<(h1|h2|p)\b[^>]*>\s*' . preg_quote(trim((string) $page_name), '/') . '\s*<\/\1>/iu',
        '',
        $legalDocumentText,
        1
    );
}
?>
<main class="page<?= $isPrivacyPolicyPage ? ' page--privacy-policy' : ($isLegalDocumentPage ? ' page--legal-document' : '') ?>">
    <?php if (!$isPrivacyPolicyPage) : ?>
        <section class="breacrums">
            <div class="breacrums__container _container">
                <ul class="breacrums__list">
                    <li class="breacrums__item">
                        <a href="/<?= $lclang ?>" class="breadcrums__link"><?= $home_bc_title ?></a>
                    </li>
                    <li class="breacrums__item">
                        <p class="breacrums__name"><?= $page_name ?></p>
                    </li>
                </ul>
            </div>
        </section>
    <?php endif; ?>
    <section class="catalog<?= $isPrivacyPolicyPage ? ' privacy-policy-page' : ($isLegalDocumentPage ? ' legal-document-page' : '') ?>">
        <div class="catalog__container _container<?= $isPrivacyPolicyPage ? ' privacy-policy__container' : ($isLegalDocumentPage ? ' legal-document__container' : '') ?>">
            <?php if ($isPrivacyPolicyPage) : ?>
                <header class="privacy-policy__header">
                    <h1 class="privacy-policy__title"><?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="privacy-policy__updated">
                        <?= $lclang === 'ro' ? 'Ultima actualizare: 04.09.2026' : 'Последнее обновление: 04.09.2026' ?>
                    </p>
                </header>

                <div class="privacy-policy__layout">
                    <?php if (!empty($privacyTocItems)) : ?>
                        <nav
                            class="privacy-policy__toc"
                            aria-label="<?= $lclang === 'ro' ? 'Cuprins' : 'Содержание' ?>"
                        >
                            <h2 class="privacy-policy__toc-title">
                                <?= $lclang === 'ro' ? 'Cuprins' : 'Содержание' ?>
                            </h2>
                            <ol class="privacy-policy__toc-list">
                                <?php foreach ($privacyTocItems as $index => $tocItem) : ?>
                                    <li class="privacy-policy__toc-item<?= $index === 0 ? ' is-active' : '' ?>">
                                        <a
                                            class="privacy-policy__toc-link"
                                            href="#<?= htmlspecialchars($tocItem['id'], ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $index === 0 ? 'aria-current="location"' : '' ?>
                                        >
                                            <span class="privacy-policy__toc-number">
                                                <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?>
                                            </span>
                                            <span class="privacy-policy__toc-label">
                                                <?= htmlspecialchars($tocItem['label'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <article class="privacy-policy__body">
                        <?= $privacyPolicyText ?>
                    </article>
                </div>
            <?php elseif ($isLegalDocumentPage) : ?>
                <header class="legal-document__header">
                    <h1 class="legal-document__title"><?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?></h1>
                </header>
                <article class="legal-document__body">
                    <?= $legalDocumentText ?>
                </article>
            <?php else : ?>
                <div class="catalog__head head-catalog">
                    <h2 class="head-catalog__title"><?= $page_name ?></h2>
                </div>
                <div class="catalog__stock stock-catalog">
                    <div class="stock-catalog__description desc_text">
                        <?=$page->desc?>
                    </div>
                    <div class="stock-catalog__description">
                        <?=$page->text?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
