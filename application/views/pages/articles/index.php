<main class="page articles-page">
    <section class="breacrums">
        <div class="breacrums__container _container">
            <ul class="breacrums__list">
                <li class="breacrums__item">
                    <a
                        href="/<?= $lclang ?>"
                        class="breadcrums__link"
                    >
                        <?= $home_bc_title ?>
                    </a>
                </li>

                <li class="breacrums__item">
                    <span class="breacrums__name">
                        <?= $page_name ?>
                    </span>
                </li>
            </ul>
        </div>
    </section>

    <section class="stock">
        <div class="stock__container _container">

            <header class="stock__header">
                <div>
                    <p class="stock__eyebrow">
                        <?= $lclang === 'ro'
                            ? 'Vizaje-Nica'
                            : 'Vizaje-Nica' ?>
                    </p>

                    <h1 class="stock__title">
                        <?= $page_name ?>
                    </h1>
                </div>

                <?php if (!empty($articles)) { ?>
                    <p class="stock__count">
                        <?= count($articles) ?>

                        <?= $lclang === 'ro'
                            ? 'articole'
                            : 'материалов' ?>
                    </p>
                <?php } ?>
            </header>

            <?php if (!empty($articles)) { ?>
                <div class="stock__content">
                    <?php foreach ($articles as $article) { ?>
                        <?php
                            $articleUrl =
                                '/'
                                . $lclang
                                . '/'
                                . $menu['all'][37]->uri
                                . '/'
                                . $article->uri;

                            $articleTitle = !empty($article->title)
                                ? $article->title
                                : '';

                            $src = !empty($article->img)
                                ? newthumbs($article->img, 'articles')
                                : '';
                        ?>

                        <article class="stock__item">
                            <a
                                href="<?= $articleUrl ?>"
                                class="stock__image"
                                aria-label="<?= htmlspecialchars(
                                    $articleTitle,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <?php if (!empty($src)) { ?>
                                    <picture>
                                        <source
                                            srcset="<?= $src ?>"
                                            type="image/webp"
                                        >

                                        <img
                                            src="<?= $src ?>"
                                            alt="<?= htmlspecialchars(
                                                $articleTitle,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </picture>
                                <?php } else { ?>
                                    <span class="stock__image-placeholder">
                                        Vizaje-Nica
                                    </span>
                                <?php } ?>

                                <span class="stock__image-overlay"></span>

                                <span class="stock__read-more">
                                    <?= $lclang === 'ro'
                                        ? 'Citește'
                                        : 'Читать' ?>

                                    <svg
                                        width="17"
                                        height="17"
                                        viewBox="0 0 17 17"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M3.5 8.5H13.5M9.5 4.5L13.5 8.5L9.5 12.5"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </span>
                            </a>

                            <div class="stock__body">
                                <?php if (!empty($article->date)) { ?>
                                    <time class="stock__date">
                                        <?= transformDate(
                                            $article->date,
                                            $lclang
                                        ) ?>
                                    </time>
                                <?php } ?>

                                <h2 class="stock__name">
                                    <a href="<?= $articleUrl ?>">
                                        <?= $articleTitle ?>
                                    </a>
                                </h2>

                                <a
                                    href="<?= $articleUrl ?>"
                                    class="stock__link"
                                >
                                    <span>
                                        <?= $lclang === 'ro'
                                            ? 'Vezi articolul'
                                            : 'Открыть статью' ?>
                                    </span>

                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 18 18"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M4 9H14M10 5L14 9L10 13"
                                            stroke="currentColor"
                                            stroke-width="1.4"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="stock__empty">
                    <?= $lclang === 'ro'
                        ? 'Momentan nu există articole.'
                        : 'Пока нет опубликованных новостей.' ?>
                </div>
            <?php } ?>

        </div>
    </section>
</main>