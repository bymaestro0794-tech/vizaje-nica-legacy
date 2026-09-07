<?php
$this->load->view(
	'layouts/pages/home/index',
	[
		'sliders' => $sliders ?? [],
		'banners' => $banners ?? [],
		'lclang' => $lclang,
		'menu' => $menu,
	]
);
?>