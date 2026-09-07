<main class="page">
    <section class="breadcrums">
        <div class="breadcrums__container _container">
            <div class="breadcrums__list">
                <div class="breadcrums__item">
                    <a href="/<?= $lclang ?>" class="breadcrums__link"><?= $home_bc_title ?></a>
                </div>
                <div class="breadcrums__item">
                    <a href="/<?= $lclang ?>/<?= $menu['all'][8]->uri ?>" class="breadcrums__link"><?= $menu['all'][8]->title ?></a>
                </div>
                <div class="breadcrums__item">
                    <span class="breadcrums__link"><?= $page_name ?></span>
                </div>
            </div>
        </div>
    </section>
    <section class="blog-page">
        <div class="blog-page__container _container">
            <div class="blog-page__head">
                <h2 class="blog-page__title _title"><?= $page_name ?></h2>
                <div class="blog-page__date">
                    <picture>
                        <source srcset="/app/img/icons/date.svg" type="image/webp">
                        <img src="/app/img/icons/date.svg" alt="Icon"></picture>
                    <?php $article->date = date_create($article->date);?>
                    <span><?=date_format($article->date,"d/m/Y");?></span>
                </div>
            </div>
            <div class="blog-page__content">
  <!--              <div class="blog-page__row">
                    <?/*= $article->desc */?>
                </div>-->
                <div class="blog-page__row">
                    <?= $article->text ?>
                </div>
            </div>
            <div class="blog-page__footer">
                <a href="javascript:history.back()" class="blog-page__back">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.6016 4.49531L8.10156 8.99531L12.6016 13.4953L11.7016 15.2953L5.40156 8.99531L11.7016 2.69531L12.6016 4.49531Z"
                              fill="#424242"/>
                    </svg>
                    <?=BACK_TO_BLOG?>
                </a>
            </div>
        </div>
    </section>
</main>