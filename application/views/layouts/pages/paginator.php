<?php if (!empty($paginator) && $paginator->getNumPages() > 1) { ?>
    <nav class="paggination-main-catalog__list" aria-label="Навигация по страницам">
        <?php $previousUrl = $paginator->getPrevUrl(); ?>
        <?php if ($previousUrl) { ?>
            <a href="<?= htmlspecialchars($previousUrl, ENT_QUOTES, 'UTF-8'); ?>"
               class="paggination-main-catalog__item _arrow"
               aria-label="Предыдущая страница">
                <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M7.2002 2.00039L3.2002 6.00039L7.2002 10.0004L6.4002 11.6004L0.800195 6.00039L6.4002 0.400391L7.2002 2.00039Z"
                          fill="currentColor"/>
                </svg>
            </a>
        <?php } else { ?>
            <span class="paggination-main-catalog__item _arrow _disabled" aria-hidden="true">
                <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.2002 2.00039L3.2002 6.00039L7.2002 10.0004L6.4002 11.6004L0.800195 6.00039L6.4002 0.400391L7.2002 2.00039Z"
                          fill="currentColor"/>
                </svg>
            </span>
        <?php } ?>

        <?php foreach ($paginator->getPages() as $page) { ?>
            <?php if ((array_key_exists('url', $page) && $page['url'] === null) || (isset($page['num']) && $page['num'] === '...')) { ?>
                <span class="paggination-main-catalog__item _more" aria-hidden="true">…</span>
            <?php } elseif (!empty($page['isCurrent'])) { ?>
                <span class="paggination-main-catalog__item _active" aria-current="page">
                    <?= htmlspecialchars((string) $page['num'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            <?php } else { ?>
                <a href="<?= htmlspecialchars(str_replace('(:num)', $page['num'], $paginator->geturlPattern()), ENT_QUOTES, 'UTF-8'); ?>"
                   class="paggination-main-catalog__item">
                    <?= htmlspecialchars((string) $page['num'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php } ?>
        <?php } ?>

        <?php $nextUrl = $paginator->getNextUrl(); ?>
        <?php if ($nextUrl) { ?>
            <a href="<?= htmlspecialchars($nextUrl, ENT_QUOTES, 'UTF-8'); ?>"
               class="paggination-main-catalog__item _arrow"
               aria-label="Следующая страница">
                <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M0.799805 9.99961L4.7998 5.99961L0.799804 1.99961L1.5998 0.39961L7.1998 5.99961L1.5998 11.5996L0.799805 9.99961Z"
                          fill="currentColor"/>
                </svg>
            </a>
        <?php } else { ?>
            <span class="paggination-main-catalog__item _arrow _disabled" aria-hidden="true">
                <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.799805 9.99961L4.7998 5.99961L0.799804 1.99961L1.5998 0.39961L7.1998 5.99961L1.5998 11.5996L0.799805 9.99961Z"
                          fill="currentColor"/>
                </svg>
            </span>
        <?php } ?>
    </nav>
<?php } ?>
