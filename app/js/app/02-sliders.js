//BildSlider
let sliders = document.querySelectorAll('._swiper')
if (sliders) {
	for (let index = 0; index < sliders.length; index++) {
		let slider = sliders[index]
		if (!slider.classList.contains('swiper-bild')) {
			let slider_items = slider.children
			if (slider_items) {
				for (let index = 0; index < slider_items.length; index++) {
					let el = slider_items[index]
					el.classList.add('swiper-slide')
				}
			}
			let slider_content = slider.innerHTML
			let slider_wrapper = document.createElement('div')
			slider_wrapper.classList.add('swiper-wrapper')
			slider_wrapper.innerHTML = slider_content
			slider.innerHTML = ''
			slider.appendChild(slider_wrapper)
			slider.classList.add('swiper-bild')

			if (slider.classList.contains('_swiper_scroll')) {
				let sliderScroll = document.createElement('div')
				sliderScroll.classList.add('swiper-scrollbar')
				slider.appendChild(sliderScroll)
			}
		}
		if (slider.classList.contains('_gallery')) {
			//slider.data('lightGallery').destroy(true);
		}
	}
	sliders_bild_callback()
}

function sliders_bild_callback(params) {}

let sliderScrollItems = document.querySelectorAll('._swiper_scroll')
if (sliderScrollItems.length > 0) {
	for (let index = 0; index < sliderScrollItems.length; index++) {
		const sliderScrollItem = sliderScrollItems[index]
		const sliderScrollBar = sliderScrollItem.querySelector('.swiper-scrollbar')
		const sliderScroll = new Swiper(sliderScrollItem, {
			direction: 'vertical',
			slidesPerView: 'auto',
			freeMode: true,
			scrollbar: {
				el: sliderScrollBar,
				draggable: true,
				snapOnRelease: false
			},
			mousewheel: {
				releaseOnEdges: true
			}
		})
		sliderScroll.scrollbar.updateSize()
	}
}

function sliders_bild_callback(params) {}

// let bannerSlider = new Swiper('.banner__slider', {
//     observer: true,
//     effect: 'slide',
//     autoplay: {
//         delay: 8000,
//         disableOnInteraction: false,
//     },
//     observeParents: true,
//     slidesPerView: "auto",
//     spaceBetween: 0,
//     autoHeight: false,
//     speed: 800,
//     loop: true,
//     touchRatio: 1, // Ensures touch swipe is responsive
//     simulateTouch: true, // Enables touch simulation
//     // Dotts
//     pagination: {
//         el: '.banner__paggination',
//         clickable: true,
//     },
//     // Arrows
//     navigation: {
//         nextEl: '.banner-arrow-next',
//         prevEl: '.banner-arrow-prev',
//     },
// });

let bannerSW = new Swiper('.banners_home_sw', {
	observer: true,
	effect: 'slide',
	// autoplay: {
	// 	delay: 8000,
	// 	disableOnInteraction: false,
	// },
	observeParents: true,
	slidesPerView: 'auto',
	spaceBetween: 8,
	autoHeight: false,
	speed: 800,
	loop: true,
	touchRatio: 1, // Ensures touch swipe is responsive
	simulateTouch: true // Enables touch simulation
	// Dotts
})

let itemProducts = document.querySelectorAll('.products-slider')
if (itemProducts.length > 0) {
	for (let index = 0; index < itemProducts.length; index++) {
		const itemProduct = itemProducts[index]
		// let slider = itemProduct.querySelector('.products-slider__slider');
		// let itemArrows = itemProduct.querySelectorAll('.product-item__image-arrow');
		let numberList = index
		if (numberList < 10) {
			numberList = `0${index + 1}`
		}
		// if (itemArrows.length > 0) {
		// 	for (let index = 0; index < itemArrows.length; index++) {
		// 		const itemArrow = itemArrows[index];
		// 		itemArrow.classList.add(`product-${numberList}`)
		// 	}
		// }
		if (itemProduct) {
			itemProduct.classList.add(`products-${numberList}`)
		}
		itemProduct.classList.add(`products-${numberList}`)

		let productSlider = new Swiper(
			`.products-${numberList} .products-slider__slider`,
			{
				observer: true,
				observeParents: true,
				slidesPerView: 'auto',
				spaceBetween: 0,
				autoplay: {
					delay: 8000,
					disableOnInteraction: false
				},
				autoHeight: false,
				speed: 800,
				loop: true,
				// Dotts
				pagination: {
					el: `.products-${numberList} .products-slider__paggination`,
					clickable: true
				},
				// Arrows
				navigation: {
					nextEl: `.products-${numberList} .products-slider-arrow-next`,
					prevEl: `.products-${numberList} .products-slider-arrow-prev`
				}
			}
		)
	}
}

// var cardMain = new Swiper('.main-slider-card', {
// 	spaceBetween: 0,
// 	slidesPerView: 'auto',
// 	autoHeight: false,
// 	updateOnWindowResize: true,
// 	// navigation: {
// 	// 	nextEl: ".swiper-button-next",
// 	// 	prevEl: ".swiper-button-prev",
// 	// },
// 	pagination: {
// 		el: '.slider-card__paggination',
// 		clickable: true
// 	},
// 	thumbs: {
// 		swiper: cardThumbs
// 	},
// 	loop: true
// })

// $('body').on('change', 'input[name="variable"]', function (e) {
// 	e.preventDefault()

// 	const lang = $('html').attr('lang')
// 	const variable = $(this).val()
// 	const productId = $('.actions-info-card').data('prod_id')

// 	$.ajax({
// 		url: '/' + lang + '/products/variable',
// 		type: 'POST',
// 		dataType: 'json',

// 		data: {
// 			variable: variable,
// 			product_id: productId
// 		},

// 		success: function (data) {
// 			destroyProductGallery()

// 			$('.slider_products').html(data.html)

// 			$('.info-card__price.price-info-card').first().html(data.price)

// 			$('.cod_sku').html(data.info.SKU)
// 			$('.barcode').html(data.info.barcode)

// 			$('.volume .list-info-card__value').html(data.info.VolumeVar)

// 			$('.alege-var').remove()

// 			initProductGallery()
// 		},

// 		error: function (xhr) {
// 			console.error('Product variable error:', xhr)
// 		}
// 	})
// })

function start_swiper() {
	let sliders = document.querySelectorAll('._swiper')
	if (sliders) {
		for (let index = 0; index < sliders.length; index++) {
			let slider = sliders[index]
			if (!slider.classList.contains('swiper-bild')) {
				let slider_items = slider.children
				if (slider_items) {
					for (let index = 0; index < slider_items.length; index++) {
						let el = slider_items[index]
						el.classList.add('swiper-slide')
					}
				}
				let slider_content = slider.innerHTML
				let slider_wrapper = document.createElement('div')
				slider_wrapper.classList.add('swiper-wrapper')
				slider_wrapper.innerHTML = slider_content
				slider.innerHTML = ''
				slider.appendChild(slider_wrapper)
				slider.classList.add('swiper-bild')

				if (slider.classList.contains('_swiper_scroll')) {
					let sliderScroll = document.createElement('div')
					sliderScroll.classList.add('swiper-scrollbar')
					slider.appendChild(sliderScroll)
				}
			}
			if (slider.classList.contains('_gallery')) {
				//slider.data('lightGallery').destroy(true);
			}
		}
		sliders_bild_callback()
	}
}
let productGalleryMain = null
let productGalleryThumbs = null

function destroyProductGallery() {
	if (productGalleryMain && typeof productGalleryMain.destroy === 'function') {
		productGalleryMain.destroy(true, true)
	}

	if (
		productGalleryThumbs &&
		typeof productGalleryThumbs.destroy === 'function'
	) {
		productGalleryThumbs.destroy(true, true)
	}

	productGalleryMain = null
	productGalleryThumbs = null
}

function buildProductGalleryMarkup(root) {
	if (!root) {
		return
	}

	const sliders = root.querySelectorAll(
		'.product-gallery__thumbs, .product-gallery__stage'
	)

	sliders.forEach(function (slider) {
		if (!slider.classList.contains('_swiper')) {
			return
		}

		if (slider.classList.contains('swiper-bild')) {
			return
		}

		const children = Array.from(slider.children)

		if (!children.length) {
			return
		}

		const wrapper = document.createElement('div')
		wrapper.classList.add('swiper-wrapper')

		children.forEach(function (child) {
			child.classList.add('swiper-slide')
			wrapper.appendChild(child)
		})

		slider.innerHTML = ''
		slider.appendChild(wrapper)
		slider.classList.add('swiper-bild')
	})
}

function initProductGallery() {
	const galleryRoot = document.querySelector('[data-product-gallery]')

	if (!galleryRoot) {
		return
	}

	destroyProductGallery()
	buildProductGalleryMarkup(galleryRoot)

	const mainElement = galleryRoot.querySelector(
		'.product-gallery__stage._swiper'
	)

	const thumbsElement = galleryRoot.querySelector(
		'.product-gallery__thumbs._swiper'
	)

	const mainSlides = mainElement
		? mainElement.querySelectorAll('.product-gallery__slide')
		: []

	/*
	|--------------------------------------------------------------------------
	| 0 или 1 изображение
	|--------------------------------------------------------------------------
	|
	| Swiper не нужен. Поэтому не будет клонирования картинки,
	| лишних wrapper-элементов и неправильной высоты.
	|
	*/

	if (!mainElement || mainSlides.length <= 1) {
		return
	}

	if (thumbsElement) {
		productGalleryThumbs = new Swiper(thumbsElement, {
			direction: 'vertical',
			slidesPerView: 'auto',
			spaceBetween: 12,
			watchSlidesProgress: true,
			watchOverflow: true,
			loop: false,
			observer: true,
			observeParents: true
		})
	}

	const paginationElement = document.querySelector(
		'.product-gallery__pagination'
	)

	productGalleryMain = new Swiper(mainElement, {
		slidesPerView: 1,
		spaceBetween: 0,
		speed: 450,
		autoHeight: false,
		watchOverflow: true,
		loop: false,
		observer: true,
		observeParents: true,

		thumbs: productGalleryThumbs
			? {
					swiper: productGalleryThumbs
				}
			: undefined,

		pagination: paginationElement
			? {
					el: paginationElement,
					clickable: true
				}
			: undefined
	})
}

$(function () {
	initProductGallery()
})
let itemGalleryMaps = document.querySelectorAll('.gallery-map-order-list')
if (itemGalleryMaps.length > 0) {
	for (let index = 0; index < itemGalleryMaps.length; index++) {
		const itemGalleryMap = itemGalleryMaps[index]
		let numberList = index
		if (numberList < 10) {
			numberList = `0${index + 1}`
		}
		if (itemGalleryMap) {
			itemGalleryMap.classList.add(`store-${numberList}`)
		}
		itemGalleryMap.classList.add(`store-${numberList}`)
		let mapGallerySlider = new Swiper(
			`.store-${numberList} .gallery-map-order-list__slider`,
			{
				observer: true,
				observeParents: true,
				slidesPerView: 'auto',
				spaceBetween: 0,
				autoHeight: false,
				speed: 800,
				loop: true,
				// Arrows
				navigation: {
					nextEl: `.store-${numberList} .gallery-map-order-list-arrow-next`,
					prevEl: `.store-${numberList} .gallery-map-order-list-arrow-prev`
				}
			}
		)
	}
}

let infoMapGallerySlider = new Swiper(`.gallery-info-map-order-map__slider`, {
	observer: true,
	observeParents: true,
	slidesPerView: 'auto',
	spaceBetween: 0,
	autoHeight: false,
	speed: 800,
	loop: false,
	// Arrows
	navigation: {
		nextEl: `.gallery-info-map-order-map-arrow-next`,
		prevEl: `.gallery-info-map-order-map-arrow-prev`
	}
})
