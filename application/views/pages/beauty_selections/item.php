<main class="page beauty-selection-page">
    <section class="breadcrums" aria-label="Breadcrumbs">
        <div class="breadcrums__container _container">
            <div class="breadcrums__list">
                <div class="breadcrums__item"><a href="/<?= $lclang ?>" class="breadcrums__link"><?= $home_bc_title ?></a></div>
                <div class="breadcrums__item"><a href="/<?= $lclang ?>/beauty-selections" class="breadcrums__link"><?= $lclang === 'ro' ? 'Selecții de frumusețe' : 'Бьюти-подборки' ?></a></div>
                <div class="breadcrums__item"><span class="breadcrums__link"><?= htmlspecialchars($selection->title, ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
        </div>
    </section>

    <section class="beauty-selection">
        <div class="beauty-selection__hero">
            <?php if (!empty($selection->img)) : ?>
                <img src="<?= newthumbs($selection->img, 'beauty_selections', 1800, 760, '1800x760x1', 1) ?>" alt="<?= htmlspecialchars($selection->title, ENT_QUOTES, 'UTF-8') ?>" fetchpriority="high" decoding="async">
            <?php endif; ?>
            <div class="beauty-selection__hero-copy">
                <h1><?= htmlspecialchars($selection->title, ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if (!empty($selection->desc)) : ?>
                    <p><?= htmlspecialchars(strip_tags($selection->desc), ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="beauty-selection__container _container">
            <?php if (!empty($selection->text)) : ?>
                <div class="beauty-selection__text"><?= $selection->text ?></div>
            <?php endif; ?>

            <?php if (!empty($products)) : ?>
                <div class="beauty-selection__products" aria-label="<?= htmlspecialchars($selection->title, ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($products as $product) : ?>
                        <?php
                            $product_category_uri = $menu['all'][3]->uri;
                            $is_unavailable = empty($product->on_stock);
                            include APPPATH . 'views/pages/articles/product_embed.php';
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
