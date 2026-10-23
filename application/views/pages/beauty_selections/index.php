<main class="page beauty-selections-page">
    <section class="breadcrums" aria-label="Breadcrumbs">
        <div class="breadcrums__container _container">
            <div class="breadcrums__list">
                <div class="breadcrums__item"><a href="/<?= $lclang ?>" class="breadcrums__link"><?= $home_bc_title ?></a></div>
                <div class="breadcrums__item"><span class="breadcrums__link"><?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
        </div>
    </section>

    <section class="beauty-selections">
        <div class="beauty-selections__container _container">
            <header class="beauty-selections__header">
                <h1 class="beauty-selections__title">
                    <span><?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?></span>
                    <small>/ <?= count($selections) ?></small>
                </h1>
            </header>

            <div class="beauty-selections__grid">
                <?php foreach ($selections as $selection) : ?>
                    <a class="beauty-selection-card" href="/<?= $lclang ?>/beauty-selections/<?= htmlspecialchars($selection->uri, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="beauty-selection-card__image">
                            <?php if (!empty($selection->img)) : ?>
                                <img src="<?= newthumbs($selection->img, 'beauty_selections', 1200, 720, '1200x720x1', 1) ?>" alt="<?= htmlspecialchars($selection->title, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="beauty-selection-card__content">
                            <h2><?= htmlspecialchars($selection->title, ENT_QUOTES, 'UTF-8') ?></h2>
                            <?php if (!empty($selection->desc)) : ?>
                                <p><?= htmlspecialchars(strip_tags($selection->desc), ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
