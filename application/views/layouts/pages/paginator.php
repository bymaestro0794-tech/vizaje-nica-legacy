<?php if (!empty($paginator)) { ?>
    <div class="paggination-main-catalog__list">
            <?php if ($paginator->getNumPages() > 1) { ?>
                <?php if ($paginator->getPrevUrl()) { ?>
                    <a href="<?= $paginator->getPrevUrl(); ?>" class="paggination-main-catalog__item">
                        <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.2002 2.00039L3.2002 6.00039L7.2002 10.0004L6.4002 11.6004L0.800195 6.00039L6.4002 0.400391L7.2002 2.00039Z"
                                  fill="#2C3F52"/>
                        </svg>
                    </a>
                <?php } ?>
            <?php } ?>
            <? foreach ($paginator->getPages() as $page) { ?>
                <?php if ($page['isCurrent']) { ?>
                    <a href="javascript:;" class="paggination-main-catalog__item _active"><span><?= $page['num'] ?></span></a>
                <?php } else { ?>
                    <a href="<?= str_replace("(:num)", $page['num'], $paginator->geturlPattern()); ?>"
                       class="paggination-main-catalog__item"><span><?= $page['num'] ?></span></a>
                <?php } ?>
            <?php } ?>
            <?php if ($paginator->getNextUrl()) { ?>
                <a href="<?= $paginator->getNextUrl(); ?>" class="paggination-main-catalog__item">
                    <svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0.799805 9.99961L4.7998 5.99961L0.799804 1.99961L1.5998 0.39961L7.1998 5.99961L1.5998 11.5996L0.799805 9.99961Z"
                              fill="#2C3F52"/>
                    </svg>
                </a>
            <?php } ?>
        </div>
<?php } ?>