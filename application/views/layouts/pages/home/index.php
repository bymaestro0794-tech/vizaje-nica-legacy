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

<?php if (!empty($products_sale)) : ?>
	<?php
	$this->load->view(
		'layouts/pages/home/products-slider',
		[
			'products' => $products_sale,
			'title' => $menu['all'][35]->title ?? ($lclang === 'ro' ? 'Reduceri' : 'Скидки'),
			'section_url' => '/' . $lclang . '/' . ($menu['all'][35]->uri ?? 'skidki'),
			'section_key' => 'sale',
			'lclang' => $lclang,
			'is_catalog_product_card' => true,
		]
	);
	?>
<?php endif; ?>

<?php
$this->load->view(
	'layouts/pages/home/categories',
	[
		'home_categories' => $home_categories ?? [],
		'menu' => $menu,
		'lclang' => $lclang,
	]
);
?>

<?php if (!empty($products_new)) : ?>
	<?php
	$this->load->view(
		'layouts/pages/home/products-slider',
		[
			'products' => $products_new,
			'title' => $menu['all'][34]->title ?? ($lclang === 'ro' ? 'Noutăți' : 'Новинки'),
			'section_url' => '/' . $lclang . '/' . ($menu['all'][34]->uri ?? 'novinki'),
			'section_key' => 'new',
			'lclang' => $lclang,
			'is_catalog_product_card' => true,
		]
	);
	?>
<?php endif; ?>

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
			'title' => $menu['all'][36]->title ?? ($lclang === 'ro' ? 'Cele mai vândute' : 'Хиты'),
			'section_url' => '/' . $lclang . '/' . ($menu['all'][36]->uri ?? 'top-prodaj'),
			'section_key' => 'hits',
			'lclang' => $lclang,
			'is_catalog_product_card' => true,
		]
	);
	?>
<?php endif; ?>

<?php $this->load->view('layouts/pages/home/certificate-teaser', ['lclang' => $lclang]); ?>
<?php $this->load->view('layouts/pages/home/stores-teaser', ['lclang' => $lclang, 'menu' => $menu]); ?>
<?php
$this->load->view(
	'layouts/pages/home/blog-teaser',
	[
		'articles' => $home_blog_articles ?? [],
		'lclang' => $lclang,
	]
);
?>
<?php
$this->load->view(
	'layouts/pages/home/bonus-program',
	[
		'lclang' => $lclang,
		'menu' => $menu,
	]
);
?>
<?php
$this->load->view(
	'layouts/pages/home/about-company',
	[
		'lclang' => $lclang,
		'menu' => $menu,
	]
);
?>
<?php /* Instagram section temporarily disabled until the media API is connected.
$this->load->view('layouts/pages/home/instagram-teaser', ['lclang' => $lclang]);
*/ ?>


</main>
