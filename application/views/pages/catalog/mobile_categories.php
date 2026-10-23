<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = $lclang === 'ro';
$catalogUri = $menu['all'][3]->uri ?? ($isRomanian ? 'catalog' : 'katalog');
$catalogBaseUrl = '/' . rawurlencode($lclang) . '/' . trim($catalogUri, '/');
$visibleCategories = array_values($categories ?? []);
$storyCategories = array_values(array_filter(
	$story_categories ?? [],
	static function ($category) {
		return !empty($category->title)
			&& !empty($category->url)
			&& !empty($category->img);
	}
));
$shopByType = [];

foreach ($visibleCategories as $parentCategory) {
    foreach (($parentCategory->children ?? []) as $childCategory) {
        if (empty($childCategory->title) || empty($childCategory->uri)) continue;
        $shopByType[] = [
            'category' => $childCategory,
        ];
    }
}

$categoryImage = function ($category, $width = 1000, $height = 1200) {
    if (!empty($category->img)) {
        return newthumbs($category->img, 'categories', $width, $height, $width . 'x' . $height . 'x1', 1);
    }
    if (!empty($category->banner->img)) {
        return newthumbs($category->banner->img, 'category_banner', $width, $height, $width . 'x' . $height . 'x1', 1);
    }
    if (!empty($category->imgNav)) {
        return newthumbs($category->imgNav, 'categories', $width, $height, $width . 'x' . $height . 'x1', 1);
    }
    return '';
};
?>
<main class="page collections-page">
    <div class="collections-page__container _container">
        <h1 class="collections-page__sr-title"><?= $isRomanian ? 'Toate colecțiile' : 'Все коллекции' ?></h1>

        <?php if (!empty($storyCategories)) : ?>
            <section class="collections-featured" aria-labelledby="collections-featured-title">
                <h2 class="collections-featured__title" id="collections-featured-title">
                    <?= $isRomanian ? 'Cumpără după categorii' : 'Покупайте по категориям' ?>
                </h2>
                <div class="collections-grid" aria-label="<?= $isRomanian ? 'Toate categoriile' : 'Все категории' ?>">
                <?php foreach ($storyCategories as $category) : ?>
                    <?php
                    $image = newthumbs($category->img, 'home_categories', 1000, 1200, '1000x1200x1', 1);
                    ?>
                    <a class="collection-card" href="<?= htmlspecialchars($category->url, ENT_QUOTES, 'UTF-8') ?>">
                        <img class="collection-card__image" src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" decoding="async">
                        <span class="collection-card__shade" aria-hidden="true"></span>
                        <span class="collection-card__title"><?= htmlspecialchars($category->title, ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($shopByType)) : ?>
                <section class="shop-by-type" aria-labelledby="shop-by-type-title">
                    <div class="shop-by-type__heading">
                        <h2 class="shop-by-type__title" id="shop-by-type-title"><?= $isRomanian ? 'Cumpără după tip' : 'Покупайте по типу' ?></h2>
                    </div>
                    <div class="shop-by-type__carousel">
                        <button class="shop-by-type__arrow shop-by-type__arrow--prev" type="button" data-type-scroll="prev" aria-label="<?= $isRomanian ? 'Derulează înapoi' : 'Прокрутить назад' ?>">
                            <span aria-hidden="true">‹</span>
                        </button>
                        <div class="shop-by-type__track" data-type-track tabindex="0">
                        <?php foreach ($shopByType as $typeItem) : ?>
                            <?php
                            $type = $typeItem['category'];
                            $typeUrl = $catalogBaseUrl . '/' . rawurlencode(trim($type->uri, '/'));
                            $typeImage = $categoryImage($type, 600, 700);
                            ?>
                            <a class="type-card<?= $typeImage === '' ? ' type-card--no-image' : '' ?>" href="<?= htmlspecialchars($typeUrl, ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($typeImage !== '') : ?>
                                    <img class="type-card__image" src="<?= htmlspecialchars($typeImage, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" decoding="async">
                                <?php endif; ?>
                                <span class="type-card__shade" aria-hidden="true"></span>
                                <span class="type-card__link-label">
                                    <span class="type-card__title"><?= htmlspecialchars($type->title, ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="type-card__arrow" aria-hidden="true"><img src="/app/img/category-arrow-up-right.svg" alt=""></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                        </div>
                        <button class="shop-by-type__arrow shop-by-type__arrow--next" type="button" data-type-scroll="next" aria-label="<?= $isRomanian ? 'Derulează înainte' : 'Прокрутить вперёд' ?>">
                            <span aria-hidden="true">›</span>
                        </button>
                    </div>
                </section>
        <?php endif; ?>
    </div>
</main>
