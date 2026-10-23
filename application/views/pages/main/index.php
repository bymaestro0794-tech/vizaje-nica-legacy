<?php
$this->load->view(
	'layouts/pages/home/index',
	[
		'sliders' => $sliders ?? [],
		'banners' => $banners ?? [],
		'lclang' => $lclang,
		'menu' => $menu,
		'home_categories' => $home_categories ?? [],
		'brand_sections_after_new' => $brand_sections_after_new ?? [],
		'brand_sections_after_sale' => $brand_sections_after_sale ?? [],
		'products_sale' => $products_sale ?? [],
		'products_order' => $products_order ?? [],
		'products_new' => $products_new ?? [],
		'home_blog_articles' => $home_blog_articles ?? [],
		'is_authenticated' => !empty($client_info->id),
	]
);
?>
