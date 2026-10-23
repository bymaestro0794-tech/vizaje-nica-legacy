<main class="page offers-page articles-page">
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

    <section class="stock stock--offers">
        <div class="stock__container _container">

            <header class="stock__header">
                <h1 class="stock__title">
                    <?= htmlspecialchars($page_name, ENT_QUOTES, 'UTF-8') ?>

                    <?php if (!empty($offers)) { ?>
                        <span class="stock__title-count">
                            / <?= count($offers) ?>
                        </span>
                    <?php } ?>
                </h1>
            </header>

            <?php if (!empty($offers)) { ?>
                <div class="stock__content">
                    <?php foreach ($offers as $index => $offer) { ?>
                        <?php
                            $offerUrl =
                                '/'
                                . $lclang
                                . '/'
                                . $menu['all'][9]->uri
                                . '/'
                                . $offer->uri;

                            $offerTitle = !empty($offer->title)
                                ? $offer->title
                                : '';

                            $src = !empty($offer->img)
                                ? newthumbs($offer->img, 'offers')
                                : '';

                            $offerDescription = '';
                            if (!empty($offer->desc)) {
                                $descriptionText = preg_replace(
                                    '/<\/?(?:p|div|h[1-6]|li|br|tr)[^>]*>/i',
                                    ' ',
                                    $offer->desc
                                );
                                $offerDescription = trim(preg_replace(
                                    '/\s+/u',
                                    ' ',
                                    html_entity_decode(
                                        strip_tags($descriptionText),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    )
                                ));

                                if (preg_match('/^.{151}/us', $offerDescription)) {
                                    $offerDescription = preg_replace(
                                        '/^(.{0,150}).*$/us',
                                        '$1…',
                                        $offerDescription
                                    );
                                }
                            }

                            $isAboveTheFold = $index < 3;
                            $offerChannel = isset($offer->is_online)
                                ? (int) $offer->is_online
                                : 1;
                            $offerIsOnline = $offerChannel === 1;
                            $offerIsOffline = $offerChannel === 0;
                            $offerBadgeLabel = $offerIsOnline
                                ? ($lclang === 'ro' ? 'Online' : 'Онлайн')
                                : ($offerIsOffline
                                    ? ($lclang === 'ro' ? 'Offline' : 'Офлайн')
                                    : ($lclang === 'ro' ? 'Online și offline' : 'Онлайн и офлайн'));
                        ?>

                        <article class="stock__item stock__item--offer">
                            <a
                                href="<?= $offerUrl ?>"
                                class="stock__image"
                                aria-label="<?= htmlspecialchars(
                                    $offerTitle,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                <?php if (!empty($src)) { ?>
                                    <picture>
                                        <img
                                            src="<?= $src ?>"
                                            alt="<?= htmlspecialchars(
                                                $offerTitle,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            loading="<?= $isAboveTheFold
                                                ? 'eager'
                                                : 'lazy' ?>"
                                            fetchpriority="<?= $index === 0
                                                ? 'high'
                                                : 'auto' ?>"
                                            decoding="async"
                                        >
                                    </picture>
                                <?php } else { ?>
                                    <span class="stock__image-placeholder">
                                        Vizaje-Nica
                                    </span>
                                <?php } ?>

                                <?php if ($offerChannel === 2) { ?>
                                    <span class="stock__offer-badges" aria-label="<?= htmlspecialchars($offerBadgeLabel, ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="stock__offer-badge stock__offer-badge--online">ON<br>LINE</span>
                                        <span class="stock__offer-badge stock__offer-badge--offline">OFF<br>LINE</span>
                                    </span>
                                <?php } else { ?>
                                    <span class="stock__offer-badge <?= $offerIsOnline ? 'stock__offer-badge--online' : 'stock__offer-badge--offline' ?>" aria-label="<?= htmlspecialchars($offerBadgeLabel, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= $offerIsOnline ? 'ON<br>LINE' : 'OFF<br>LINE' ?>
                                    </span>
                                <?php } ?>

                            </a>

                            <div class="stock__body">
                                <h2 class="stock__name">
                                    <a href="<?= htmlspecialchars($offerUrl, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($offerTitle, ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h2>

                                <?php if ($offerDescription !== '') { ?>
                                    <p class="stock__offer-description">
                                        <?= htmlspecialchars($offerDescription, ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="stock__empty">
                    <?= $lclang === 'ro'
                        ? 'Momentan nu există promoții active.'
                        : 'Сейчас нет активных акций.' ?>
                </div>
            <?php } ?>

        </div>
    </section>
</main>
