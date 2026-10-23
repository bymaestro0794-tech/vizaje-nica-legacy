<?php
$cardProduct = isset($item) && is_object($item)
    ? $item
    : (isset($product) && is_object($product) ? $product : null);

if ($cardProduct) {
    $CI =& get_instance();
    if (!isset($CI->products_model)) {
        $CI->load->model('products_model');
    }

    $cardLanguage = isset($lclang) ? strtoupper((string) $lclang) : 'RU';
    $cardOptions = $CI->products_model->get_product_card_options(
        $cardProduct,
        $cardLanguage
    );

    if (!empty($cardOptions['items'])) {
        $optionType = $cardOptions['type'];
        $optionItems = $cardOptions['items'];
        $optionCount = count($optionItems);
        $isStacked = $optionCount > 3;
        $visibleItems = $optionType === 'color' && $isStacked
            ? array_slice($optionItems, 0, 3)
            : $optionItems;
        $optionLabel = $optionType === 'volume'
            ? ($cardLanguage === 'RO' ? 'Volum' : 'Объём')
            : ($cardLanguage === 'RO' ? 'Culori disponibile' : 'Доступные оттенки');
        ?>
        <div class="product-card-options product-card-options--<?= $optionType ?>"
             role="group"
             aria-label="<?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8') ?>">
            <div class="product-card-options__list <?= $isStacked ? 'product-card-options__list--stacked' : '' ?>">
                <?php foreach ($visibleItems as $index => $option) { ?>
                    <?php if ($optionType === 'volume') { ?>
                        <span class="product-card-options__volume"
                              style="z-index: <?= max(1, count($visibleItems) - $index) ?>">
                            <?= htmlspecialchars((string) $option, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php } elseif (!empty($option['hex'])) { ?>
                        <?php
                            $hexColors = array_values($option['hex']);
                            $swatchBackground = $hexColors[0];
                            if (count($hexColors) > 1) {
                                $stops = array();
                                $segmentSize = 360 / count($hexColors);
                                foreach ($hexColors as $hexIndex => $hexColor) {
                                    $start = (int) round($hexIndex * $segmentSize);
                                    $end = (int) round(($hexIndex + 1) * $segmentSize);
                                    $stops[] = $hexColor . ' ' . $start . 'deg ' . $end . 'deg';
                                }
                                $swatchBackground = 'conic-gradient(' . implode(', ', $stops) . ')';
                            }
                            $swatchLabel = trim((string) ($option['label'] ?? ''));
                            if ($swatchLabel === '') {
                                $swatchLabel = implode(', ', $hexColors);
                            }
                        ?>
                        <span class="product-card-options__swatch"
                              role="img"
                              aria-label="<?= htmlspecialchars($swatchLabel, ENT_QUOTES, 'UTF-8') ?>"
                              title="<?= htmlspecialchars($swatchLabel, ENT_QUOTES, 'UTF-8') ?>"
                              style="z-index: <?= max(1, count($visibleItems) - $index) ?>; background: <?= htmlspecialchars($swatchBackground, ENT_QUOTES, 'UTF-8') ?>"></span>
                    <?php } else { ?>
                        <span class="product-card-options__text"
                              style="z-index: <?= max(1, count($visibleItems) - $index) ?>">
                            <?= htmlspecialchars((string) ($option['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php } ?>
                <?php } ?>

                <?php if ($optionType === 'color' && $isStacked) { ?>
                    <span class="product-card-options__more">
                        +<?= $optionCount - 3 ?>
                    </span>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}
?>
