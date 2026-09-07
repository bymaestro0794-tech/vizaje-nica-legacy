<?php
    $articleTitle = !empty($article->title)
        ? $article->title
        : $page_name;

    $articleDescription = !empty($article->desc)
        ? $article->desc
        : '';

    $articleText = !empty($article->text)
        ? $article->text
        : '';

    $articleDate = !empty($article->date)
        ? transformDate($article->date, $lclang)
        : '';
?>

<main class="page articles-item-page">

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
                    <a
                        href="/<?= $lclang ?>/<?= $menu['all'][37]->uri ?>"
                        class="breadcrums__link"
                    >
                        <?= $menu['all'][37]->title ?>
                    </a>
                </li>

                <li class="breacrums__item">
                    <span class="breacrums__name">
                        <?= htmlspecialchars(
                            $articleTitle,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>
                </li>
            </ul>
        </div>
    </section>

    <article class="article-page">
        <div class="article-page__container _container">

            <header class="article-page__header">

                <a
                    href="/<?= $lclang ?>/<?= $menu['all'][37]->uri ?>"
                    class="article-page__category"
                >
                    <?= $menu['all'][37]->title ?>
                </a>

                <h1 class="article-page__title">
                    <?= $articleTitle ?>
                </h1>

                <?php if (!empty($articleDescription)) { ?>
                    <div class="article-page__lead">
                        <?= $articleDescription ?>
                    </div>
                <?php } ?>

                <?php if (!empty($articleDate)) { ?>
                    <time class="article-page__date">
                        <?= $articleDate ?>
                    </time>
                <?php } ?>

            </header>

            <?php if (!empty($article->img)) { ?>
                <?php $src = newthumbs($article->img, 'articles'); ?>

                <figure class="article-page__media">
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
                            class="article-page__image"
                            decoding="async"
                        >
                    </picture>
                </figure>
            <?php } ?>

            <?php if (!empty($articleText)) { ?>
                <div class="article-page__content article-content">
                    <?= $articleText ?>
                </div>
            <?php } ?>

            <footer class="article-page__footer">
                <a
                    href="/<?= $lclang ?>/<?= $menu['all'][37]->uri ?>"
                    class="article-page__back"
                >
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 18 18"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M11.75 3.75L6.5 9L11.75 14.25"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <span>
                        <?= $lclang === 'ro'
                            ? 'Înapoi la noutăți'
                            : 'Вернуться к новостям' ?>
                    </span>
                </a>
            </footer>

        </div>
    </article>

</main>