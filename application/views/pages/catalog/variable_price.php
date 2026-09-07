<?php if (!empty($discount_price)) { ?>
    <p class="price-info-card__value"><?= $discount_price?> <?= MDL ?></p>
    <p class="price-info-card__old"><?= $price ?> <?= MDL ?></p>
<?php } else { ?>
    <p class="price-info-card__value"><?= $price ?> <?= MDL ?></p>
<?php } ?>