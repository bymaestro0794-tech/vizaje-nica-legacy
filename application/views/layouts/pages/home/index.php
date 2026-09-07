<main class="page home-page">
    <?php
    /*
    |--------------------------------------------------------------------------
    | Hero slider
    |--------------------------------------------------------------------------
    */
    $this->load->view(
        'layouts/pages/home/hero',
        [
            'sliders' => $sliders ?? [],
            'lclang' => $lclang,
        ]
    );
    ?>

 <?php
    /*
    |--------------------------------------------------------------------------
    | Categories slider
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
				'title' => $menu['all'][34]->title ?? 'Новинки',
				'section_url' => '/' . $lclang . '/' . (
				$menu['all'][34]->uri ?? 'novinki'
			),
				'section_key' => 'new',
				'lclang' => $lclang,
			]
		);
		?>
<?php endif; ?>
<?php
$this->load->view(
    'layouts/pages/home/recently-viewed',
    array(
        'lclang' => $lclang,
        'current_product_id' => 0,
    )
);
?>
<?php if (!empty($brand_sections_after_new)) : ?>
	<?php foreach ($brand_sections_after_new as $section) : ?>
		<?php
		$this->load->view(
			'layouts/pages/home/brand-section',
			[
				'section' => $section,
				'lclang' => $lclang,
			]
		);
		?>
	<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($products_sale)) : ?>
	<?php
	$this->load->view(
		'layouts/pages/home/products-slider',
		[
			'products' => $products_sale,
			'title' => $menu['all'][35]->title ?? 'Скидки',
			'section_url' => '/' . $lclang . '/' . (
			$menu['all'][36]->uri ?? 'top-prodazh'
		),
			'section_key' => 'sale',
			'lclang' => $lclang,
		]
	);
	?>
<?php endif; ?>

<?php if (!empty($brand_sections_after_sale)) : ?>
	<?php foreach ($brand_sections_after_sale as $section) : ?>
		<?php
		$this->load->view(
			'layouts/pages/home/brand-section',
			[
				'section' => $section,
				'lclang' => $lclang,
			]
		);
		?>
	<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($products_order)) : ?>
	<?php
	$this->load->view(
		'layouts/pages/home/products-slider',
		[
			'products' => $products_order,
			'title' => $menu['all'][36]->title ?? 'Хиты',
			'section_key' => 'hits',
			'section_url' => '/' . $lclang . '/' . (
			$menu['all'][35]->uri ?? 'skidki'
		),
			'lclang' => $lclang,
		]
	);
	?>
<?php endif; ?>


</main>