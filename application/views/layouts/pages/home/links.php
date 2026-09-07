    <?php
    /*
    |--------------------------------------------------------------------------
    | Quick categories
    |--------------------------------------------------------------------------
    */
    $this->load->view(
        'layouts/pages/home/categories',
        [
            'banners' => $banners ?? [],
            'lclang' => $lclang,
            'menu' => $menu,
        ]
    );
    ?>

    <?php if (!empty($products_new)) : ?>
        <?php
        $this->load->view(
            'layouts/pages/home/products-slider',
            [
                'products' => $products_new,
                'title' => $menu['all'][34]->title ?? '',
                'section_key' => 'new',
            ]
        );
        ?>

        <?php
        $this->load->view(
            'layouts/pages/home/collection',
            [
                'products' => $products_new,
                'menu_item' => $menu['all'][34] ?? null,
                'lclang' => $lclang,
                'reverse' => false,
            ]
        );
        ?>
    <?php endif; ?>

    <?php if (!empty($products_sale)) : ?>
        <?php
        $this->load->view(
            'layouts/pages/home/products-slider',
            [
                'products' => $products_sale,
                'title' => $menu['all'][35]->title ?? '',
                'section_key' => 'sale',
            ]
        );
        ?>

        <?php
        $this->load->view(
            'layouts/pages/home/collection',
            [
                'products' => $products_sale,
                'menu_item' => $menu['all'][35] ?? null,
                'lclang' => $lclang,
                'reverse' => true,
            ]
        );
        ?>
    <?php endif; ?>

    <?php if (!empty($products_order)) : ?>
        <?php
        $this->load->view(
            'layouts/pages/home/products-slider',
            [
                'products' => $products_order,
                'title' => $menu['all'][36]->title ?? '',
                'section_key' => 'popular',
            ]
        );
        ?>

        <?php
        $this->load->view(
            'layouts/pages/home/collection',
            [
                'products' => $products_order,
                'menu_item' => $menu['all'][36] ?? null,
                'lclang' => $lclang,
                'reverse' => false,
            ]
        );
        ?>
    <?php endif; ?>

    <?php
    $this->load->view(
        'layouts/pages/home/tile',
        [
            'categories_home' => $categories_home ?? [],
            'lclang' => $lclang,
            'menu' => $menu,
        ]
    );
    ?>