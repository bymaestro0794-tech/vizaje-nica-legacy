<?php
defined('BASEPATH') or exit('No direct script access allowed');

$termsContent = trim((string) ($page->desc ?? '') . "\n" . (string) ($page->text ?? ''));
$termsContent = preg_replace(
    '/<(h1|h2|p)\b[^>]*>\s*' . preg_quote(trim((string) $page_name), '/') . '\s*<\/\1>/iu',
    '',
    $termsContent,
    1
);
$termsContent = preg_replace('#<(script|style|iframe|object|embed|form)\b[^>]*>.*?</\1\s*>#is', '', $termsContent);
$allowedTermsTags = array('p', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'a', 'strong', 'b', 'em', 'i', 'u', 'br', 'hr', 'table', 'thead', 'tbody', 'tr', 'th', 'td');
$termsContent = preg_replace_callback(
    '/<(\/?)\s*([a-z][a-z0-9]*)([^>]*)>/i',
    static function ($matches) use ($allowedTermsTags) {
        $tag = strtolower($matches[2]);
        if (!in_array($tag, $allowedTermsTags, true)) {
            return '';
        }
        if ($matches[1] === '/') {
            return '</' . $tag . '>';
        }
        if ($tag !== 'a') {
            return '<' . $tag . ($tag === 'br' || $tag === 'hr' ? ' />' : '>');
        }

        if (preg_match('/\bhref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $matches[3], $hrefMatch)) {
            $href = html_entity_decode($hrefMatch[1] !== '' ? $hrefMatch[1] : ($hrefMatch[2] !== '' ? $hrefMatch[2] : $hrefMatch[3]), ENT_QUOTES, 'UTF-8');
            if (preg_match('#^(https?://|mailto:|tel:|/)#i', $href)) {
                return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
            }
        }

        return '<a>';
    },
    $termsContent
);
?>
<main class="page page--legal-document legal-file-page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a href="/<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>" class="breadcrums__link"><?= htmlspecialchars($home_bc_title, ENT_QUOTES, 'UTF-8') ?></a>
                </li>
                <li class="breacrums__item">
                    <p class="breacrums__name"><?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?></p>
                </li>
            </ul>
        </div>
    </section>

    <section class="legal-document-page">
        <div class="catalog__container _container legal-document__container">
            <header class="legal-document__header">
                <h1 class="legal-document__title"><?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?></h1>
            </header>

            <article class="legal-document__body terms-content">
                <?= $termsContent ?>
            </article>
        </div>
    </section>
</main>
