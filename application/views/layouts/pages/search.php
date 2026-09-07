<div class="form-header-search__lists">
    <div class="form-header-search__column products-form-header-search">
        <div class="products-form-header-search__static-list">
            <?php if (!empty($categories)) { ?>
                <ul>
                    <?php foreach ($categories as $category) {
                        $cat_url = '/' . $lclang . '/' . $menu['all'][3]->uri . '/' . $category->uri; ?>
                        <li>
                            <a href="<?= $cat_url ?>"><?= $category->title ?></a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>
    <div class="form-header-search__column brands-form-header-search">
        <div class="brands-form-header-search__static-list">
            <?php if (!empty($brands_nav)) { ?>
                <ul>
                    <?php foreach ($brands_nav as $brand) {
                        $cat_url = '/' . $lclang . '/' . $menu['all'][3]->uri . '/' . $brand->uri; ?>
                        <li>
                            <a href="<?= $cat_url ?>"><?= $brand->title ?></a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>
</div>
<div class="form-header-search__sidebar sidebar-form-header-search">
    <div class="sidebar-form-header-search__block _list">
        <?php if (!empty($products_new)) { ?>
            <?php foreach ($products_new as $item) { ?>
                <div class="sidebar-form-header-search__products product ">
                    <div class="product__head head-product">
                        <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item->cat_uri ?>/<?= $item->uri ?>"
                           class="head-product__image">
                            <?php $src = newthumbs($item->img, 'products') ?>
                            <picture>
                                <source srcset="<?=$src?>" type="image/webp">
                                <img src="<?=$src?>" alt="Image"></picture>
                        </a>
                        <div class="head-product__modificator">
                            <?php if (!empty($item->is_new)) { ?>
                                <p class="head-product__modificator_new"><?= PRODUCTS_NEW ?></p>
                            <?php } ?>
                            <?php if (!empty($item->discount_price)) { ?>
                                <p class="head-product__modificator_sale"><?= PRODUCTS_SALE ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="product__body">
                        <div class="product__info">
                            <p class="product__brand"><?=$item->brand_title?></p>
                            <a href="/<?= $lclang ?>/<?= $menu['all'][3]->uri ?>/<?= $item->cat_uri ?>/<?= $item->uri ?>" class="product__name">
                                <?= preg_replace('/\s+/u', ' ', trim(html_entity_decode($item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>
