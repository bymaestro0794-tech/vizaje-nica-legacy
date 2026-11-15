<?php if (!empty($discount_price)) { ?>
    <p class="price-info-card__value"><?= mdl_format($discount_price) ?> <?= MDL ?></p>
    <p class="price-info-card__old"><?= mdl_format($price) ?> <?= MDL ?></p>
<?php } else { ?>
    <p class="price-info-card__value"><?= mdl_format($price) ?> <?= MDL ?></p>
<?php } ?>
