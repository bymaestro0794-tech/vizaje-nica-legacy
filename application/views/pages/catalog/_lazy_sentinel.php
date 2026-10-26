<?php
defined('BASEPATH') or exit('No direct script access allowed');

$lazyPage = (int) ($catalog_lazy_page ?? 1);
$lazyTotalPages = max(1, (int) ($catalog_lazy_total_pages ?? 1));

if ($lazyPage < $lazyTotalPages) : ?>
    <div
        class="catalog-lazy-sentinel"
        data-catalog-lazy-sentinel
        data-next-page="<?= $lazyPage + 1 ?>"
        data-total-pages="<?= $lazyTotalPages ?>"
        aria-live="polite"
        aria-label="<?= $lclang === 'ro' ? 'Se încarcă mai multe produse' : 'Загрузка товаров' ?>"
    ></div>
<?php endif; ?>
