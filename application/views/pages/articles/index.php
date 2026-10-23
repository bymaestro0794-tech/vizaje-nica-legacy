<main class="page articles-page articles-page--editorial">
    <?php
        $articleBasePath = !empty($articles_base_path)
            ? $articles_base_path
            : '/' . $lclang . '/' . $menu['all'][37]->uri;
    ?>
    <section class="articles-feed">
        <div class="articles-feed__container _container">
            <header class="articles-feed__header">
                <h1 class="articles-feed__masthead">
                    <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>
                </h1>
            </header>

            <?php if (!empty($articles)) { ?>
                <div class="articles-feed__grid">
                    <?php foreach ($articles as $index => $article) { ?>
                        <?php
                            $articleUrl = $articleBasePath . '/' . $article->uri;

                            $articleTitle = !empty($article->title)
                                ? $article->title
                                : '';

                            $articleImage = !empty($article->img)
                                ? newthumbs($article->img, 'articles')
                                : '';

                            $articleExcerpt = '';
                            if (!empty($article->desc)) {
                                $plainDescription = preg_replace(
                                    '/<\/?(?:p|div|h[1-6]|li|br|tr)[^>]*>/i',
                                    ' ',
                                    $article->desc
                                );
                                $articleExcerpt = trim(preg_replace(
                                    '/\s+/u',
                                    ' ',
                                    html_entity_decode(
                                        strip_tags($plainDescription),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                ));

                                if (preg_match('/^.{181}/us', $articleExcerpt)) {
                                    $articleExcerpt = preg_replace(
                                        '/^(.{0,180}).*$/us',
                                        '$1…',
                                        $articleExcerpt
                                    );
                                }
                            }

                            $articleDate = '';
                            $articleDateTime = '';
                            if (!empty($article->date)) {
                                $timestamp = strtotime($article->date);
                                if ($timestamp) {
                                    $articleDateTime = date('Y-m-d', $timestamp);
                                    $monthIndex = (int) date('n', $timestamp);
                                    $monthNames = $lclang === 'ro'
                                        ? array(
                                            1 => 'ianuarie', 2 => 'februarie',
                                            3 => 'martie', 4 => 'aprilie',
                                            5 => 'mai', 6 => 'iunie',
                                            7 => 'iulie', 8 => 'august',
                                            9 => 'septembrie', 10 => 'octombrie',
                                            11 => 'noiembrie', 12 => 'decembrie',
                                        )
                                        : array(
                                            1 => 'января', 2 => 'февраля',
                                            3 => 'марта', 4 => 'апреля',
                                            5 => 'мая', 6 => 'июня',
                                            7 => 'июля', 8 => 'августа',
                                            9 => 'сентября', 10 => 'октября',
                                            11 => 'ноября', 12 => 'декабря',
                                        );

                                    $articleDate = date('j', $timestamp)
                                        . ' ' . $monthNames[$monthIndex]
                                        . ' ' . date('Y', $timestamp);
                                }
                            }

                            $isWideCard = $index === 0
                                || ($index >= 3 && $index < 5);
                        ?>

                        <article class="articles-feed__item <?= $isWideCard ? 'articles-feed__item--wide' : '' ?>">
                            <a
                                class="articles-feed__card"
                                href="<?= htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8') ?>"
                                aria-label="<?= htmlspecialchars($articleTitle, ENT_QUOTES, 'UTF-8') ?>"
                            >
                                <?php if ($articleImage !== '') { ?>
                                    <picture class="articles-feed__picture">
                                        <img
                                            src="<?= htmlspecialchars($articleImage, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($articleTitle, ENT_QUOTES, 'UTF-8') ?>"
                                            loading="<?= $index < 3 ? 'eager' : 'lazy' ?>"
                                            decoding="async"
                                        >
                                    </picture>
                                <?php } else { ?>
                                    <span class="articles-feed__picture articles-feed__picture--empty">
                                        Vizaje-Nica
                                    </span>
                                <?php } ?>

                                <span class="articles-feed__shade" aria-hidden="true"></span>

                                <?php if ($articleDate !== '') { ?>
                                    <time
                                        class="articles-feed__date"
                                        datetime="<?= $articleDateTime ?>"
                                    >
                                        <?= htmlspecialchars($articleDate, ENT_QUOTES, 'UTF-8') ?>
                                    </time>
                                <?php } ?>

                                <span class="articles-feed__copy">
                                    <span class="articles-feed__kind">
                                        <?= $lclang === 'ro' ? '#ARTICOL' : '#СТАТЬЯ' ?>
                                    </span>

                                    <span class="articles-feed__title">
                                        <?= htmlspecialchars($articleTitle, ENT_QUOTES, 'UTF-8') ?>
                                    </span>

                                    <?php if ($articleExcerpt !== '') { ?>
                                        <span class="articles-feed__excerpt">
                                            <?= htmlspecialchars($articleExcerpt, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php } ?>
                                </span>
                            </a>
                        </article>
                    <?php } ?>
                </div>
                <?php if (!empty($paginator) && $paginator->getNumPages() > 1) { ?>
                    <nav class="articles-feed__pagination" aria-label="<?= $lclang === 'ro' ? 'Paginare' : 'Страницы блога' ?>">
                        <?php if ($paginator->getPrevUrl()) { ?>
                            <a href="<?= htmlspecialchars($paginator->getPrevUrl(), ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= $lclang === 'ro' ? 'Pagina precedentă' : 'Предыдущая страница' ?>">←</a>
                        <?php } ?>
                        <?php foreach ($paginator->getPages() as $pageLink) { ?>
                            <?php if ($pageLink['url'] === null) { ?>
                                <span class="is-ellipsis">…</span>
                            <?php } else { ?>
                                <a href="<?= htmlspecialchars($pageLink['url'], ENT_QUOTES, 'UTF-8') ?>"
                                   class="<?= !empty($pageLink['isCurrent']) ? 'is-current' : '' ?>"
                                   <?= !empty($pageLink['isCurrent']) ? 'aria-current="page"' : '' ?>>
                                    <?= htmlspecialchars((string) $pageLink['num'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php } ?>
                        <?php } ?>
                        <?php if ($paginator->getNextUrl()) { ?>
                            <a href="<?= htmlspecialchars($paginator->getNextUrl(), ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= $lclang === 'ro' ? 'Pagina următoare' : 'Следующая страница' ?>">→</a>
                        <?php } ?>
                    </nav>
                <?php } ?>
            <?php } else { ?>
                <div class="articles-feed__empty">
                    <?= $lclang === 'ro'
                        ? 'Momentan nu există articole.'
                        : 'Пока нет опубликованных новостей.' ?>
                </div>
            <?php } ?>
        </div>
    </section>
</main>
