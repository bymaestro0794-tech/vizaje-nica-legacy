<?php if (empty($categories_home)) : ?>
    <?php return; ?>
<?php endif; ?>

<section class="tile">
    <div class="tile__container _container">
        <div class="tile__content">
            <?php foreach ($categories_home as $key => $category) : ?>
                <?php
                $image = newthumbs(
                    $category->img,
                    'categories'
                );
                ?>

                <a
                    href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= htmlspecialchars(
                        $category->uri,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    id="tile-<?= $key + 1 ?>"
                    class="tile__item"
                >
                    <div class="tile__image">
                        <img
                            src="<?= htmlspecialchars(
                                $image,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            loading="lazy"
                            decoding="async"
                            alt="<?= htmlspecialchars(
                                $category->title,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                    </div>

                    <p class="tile__name">
                        <?= htmlspecialchars(
                            $category->title,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>