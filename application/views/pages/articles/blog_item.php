<?php
$articleTitle = trim((string) ($article->title ?? ''));
$articleDescription = trim((string) ($article->desc ?? ''));
$articleDate = !empty($article->date) ? date('Y-m-d', strtotime($article->date)) : '';
$articleDateLabel = !empty($article->date) ? transformDate($article->date, $lclang) : '';
$articleImage = !empty($article->img) ? newthumbs($article->img, 'articles') : '';
$articleTags = array_filter(array_map('trim', explode(',', (string) ($article->tags ?? ''))));
$articleUrl = rtrim($site_url, '/') . '/' . $lclang . '/blog/' . rawurlencode((string) $article->uri);
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
?>

<main class="page blog-article-page" data-blog-article data-reading-content data-lang="<?= htmlspecialchars($lclang, ENT_QUOTES, 'UTF-8') ?>">
    <div
        class="blog-reading-progress"
        data-reading-progress
        role="progressbar"
        aria-label="<?= $lclang === 'ro' ? 'Progresul lecturii' : 'Прогресс чтения' ?>"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="0"
    ><span data-reading-progress-bar></span></div>

    <section class="blog-article-hero">
        <div class="blog-article-hero__image">
            <?php if ($articleImage !== '') { ?>
                <img
                    src="<?= htmlspecialchars($articleImage, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($articleTitle, ENT_QUOTES, 'UTF-8') ?>"
                    fetchpriority="high"
                    decoding="async"
                >
            <?php } ?>
            <a class="blog-article-hero__crumb" href="/<?= $lclang ?>/blog">
                <i class="icon-arrow-left" aria-hidden="true"></i>
                <span><?= $lclang === 'ro' ? 'Blog' : 'Блог' ?></span>
            </a>
        </div>

        <header class="blog-article-hero__copy">
            <div class="blog-article-meta">
                <div class="blog-article-meta__labels">
                    <?php if ($articleDateLabel !== '') { ?>
                        <time datetime="<?= htmlspecialchars($articleDate, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($articleDateLabel, ENT_QUOTES, 'UTF-8') ?>
                        </time>
                    <?php } ?>
                    <?php foreach ($articleTags as $tag) { ?>
                        <span>#<?= htmlspecialchars(ltrim($tag, '#'), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php } ?>
                </div>

                <div class="blog-article-actions">
                    <button
                        type="button"
                        class="blog-article-actions__button <?= !empty($article_liked) ? 'is-liked' : '' ?>"
                        data-article-like
                        data-endpoint="/<?= $lclang ?>/blog/like/<?= (int) $article->id ?>"
                        data-csrf-name="<?= htmlspecialchars($csrfName, ENT_QUOTES, 'UTF-8') ?>"
                        data-csrf-hash="<?= htmlspecialchars($csrfHash, ENT_QUOTES, 'UTF-8') ?>"
                        aria-pressed="<?= !empty($article_liked) ? 'true' : 'false' ?>"
                        aria-label="<?= $lclang === 'ro' ? 'Apreciază articolul' : 'Нравится статья' ?>"
                    >
                        <i class="icon-heart" aria-hidden="true"></i>
                        <span data-like-count><?= (int) $article_likes ?></span>
                    </button>
                    <button
                        type="button"
                        class="blog-article-actions__button"
                        data-article-share
                        aria-label="<?= $lclang === 'ro' ? 'Distribuie articolul' : 'Поделиться статьёй' ?>"
                    >
                        <i class="icon-share-alt" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <a href="/<?= $lclang ?>/blog" class="blog-article-section-link">
                <?= $lclang === 'ro' ? 'VIZAJE-NICA · BLOG' : 'VIZAJE-NICA · БЛОГ' ?>
            </a>
            <h1 class="blog-article-hero__title">
                <?= htmlspecialchars($articleTitle, ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <?php if ($articleDescription !== '') { ?>
                <div class="blog-article-hero__lead"><?= $articleDescription ?></div>
            <?php } ?>
        </header>
    </section>

    <div class="blog-article-body">
        <?php if (!empty($article->text)) { ?>
            <div class="blog-article-content">
                <?= $article->text ?>
            </div>
        <?php } ?>

        <?php if (!empty($article_related)) { ?>
            <section class="blog-more-reading" aria-labelledby="blog-more-title">
                <h2 id="blog-more-title">
                    <?= $lclang === 'ro' ? 'Ce mai poți citi' : 'Что ещё посмотреть' ?>
                </h2>
                <div class="blog-more-reading__grid">
                    <?php foreach ($article_related as $index => $related) { ?>
                        <?php
                            $relatedUrl = '/'
                                . $lclang
                                . '/blog/'
                                . rawurlencode((string) $related->uri);
                            $relatedImage = !empty($related->img)
                                ? newthumbs($related->img, 'articles')
                                : '';
                        ?>
                        <a class="blog-more-card" href="<?= htmlspecialchars($relatedUrl, ENT_QUOTES, 'UTF-8') ?>">
                            <?php if ($relatedImage !== '') { ?>
                                <img src="<?= htmlspecialchars($relatedImage, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($related->title, ENT_QUOTES, 'UTF-8') ?>"
                                     loading="lazy" decoding="async">
                            <?php } ?>
                            <span class="blog-more-card__copy">
                                <span><?= $lclang === 'ro' ? '#ARTICOL' : '#СТАТЬЯ' ?></span>
                                <strong><?= htmlspecialchars($related->title, ENT_QUOTES, 'UTF-8') ?></strong>
                            </span>
                        </a>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>
    </div>

    <div class="blog-share-toast" data-share-toast aria-live="polite"></div>
</main>

<script type="application/ld+json">
<?= json_encode(array(
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $articleTitle,
    'description' => trim(strip_tags($articleDescription)),
    'datePublished' => $articleDate,
    'image' => $articleImage !== '' ? rtrim($site_url, '/') . $articleImage : null,
    'mainEntityOfPage' => $articleUrl,
    'publisher' => array('@type' => 'Organization', 'name' => 'Vizaje-Nica'),
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>
