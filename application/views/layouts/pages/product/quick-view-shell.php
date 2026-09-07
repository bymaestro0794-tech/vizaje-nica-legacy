<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div
	class="product-quick-view"
	data-product-quick-view
	aria-hidden="true"
>
	<button
		type="button"
		class="product-quick-view__backdrop"
		data-quick-view-close
		aria-label="<?= $lclang === 'ro'
			? 'Închide'
			: 'Закрыть' ?>"
	></button>

	<aside
		class="product-quick-view__panel"
		role="dialog"
		aria-modal="true"
		aria-label="<?= $lclang === 'ro'
			? 'Vizualizare rapidă'
			: 'Быстрый просмотр' ?>"
	>
		<div
			class="product-quick-view__handle"
			aria-hidden="true"
		></div>

		<button
			type="button"
			class="product-quick-view__close"
			data-quick-view-close
			aria-label="<?= $lclang === 'ro'
				? 'Închide'
				: 'Закрыть' ?>"
		>
			<span></span>
			<span></span>
		</button>

		<div
			class="product-quick-view__loader"
			data-quick-view-loader
			hidden
		>
			<span></span>
		</div>

		<div
			class="product-quick-view__body"
			data-quick-view-body
		></div>
	</aside>
</div>