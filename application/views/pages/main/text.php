<?php
$isPrivacyPolicyPage = !empty($is_privacy_policy_page);
$privacyPolicyText = (string) $page->text;
$privacyTocItems = [];

if ($isPrivacyPolicyPage) {
    $privacyPolicyText = (string) (
        !empty($page->desc)
            ? $page->desc
            : $page->text
    );

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
?>
<main class="page<?= $isPrivacyPolicyPage ? ' page--privacy-policy' : '' ?>">
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
    <section class="catalog<?= $isPrivacyPolicyPage ? ' privacy-policy-page' : '' ?>">
        <div class="catalog__container _container<?= $isPrivacyPolicyPage ? ' privacy-policy__container' : '' ?>">
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
