;(function () {
	'use strict'

	function initProductPage() {
		const page = document.querySelector('[data-product-page]')
		const dataElement = document.getElementById('product-page-data')

		if (!page || !dataElement) {
			console.warn('Product page: root или data element не найден', {
				page: Boolean(page),
				dataElement: Boolean(dataElement)
			})

			return
		}

		let data

		try {
			data = JSON.parse(dataElement.textContent)
		} catch (error) {
			console.error('Не удалось прочитать данные страницы товара', error)

			return
		}

		const state = {
			selectedVariationId: Number(data.initial.variationId) || 0,

			images: Array.isArray(data.initial.images) ? data.initial.images : [],

			imageIndex: 0,

			isSubmitting: false,

			availabilityController: null,

			availabilityCache: new Map(),

			lightboxOpen: false,

			lightboxScale: 1,

			lightboxX: 0,

			lightboxY: 0
		}

		initGallery()
		initLightbox()
		initColorSelect()
		initVariationOptions()
		initAccordions()
		initExpandableTitle()
		initExpandableContent()
		initReadingDrawer()
		initCartActions()
		initStickyCart()

		/* ======================================================================
	   GALLERY
	   ====================================================================== */

		function initGallery() {
			const gallery = page.querySelector('[data-product-gallery]')

			if (!gallery) {
				return
			}

			const viewport = gallery.querySelector('[data-gallery-viewport]')

			gallery
				.querySelector('[data-gallery-prev]')
				?.addEventListener('click', function () {
					showImage(state.imageIndex - 1)
				})

			gallery
				.querySelector('[data-gallery-next]')
				?.addEventListener('click', function () {
					showImage(state.imageIndex + 1)
				})

			gallery.addEventListener('click', function (event) {
				const thumb = event.target.closest('[data-gallery-thumb]')

				if (!thumb) {
					return
				}

				showImage(Number(thumb.dataset.galleryIndex))
			})

			if (viewport) {
				initGallerySwipe(viewport)
				initGalleryZoomIndicator(viewport)
			}

			renderGallery()
		}

		function initGallerySwipe(viewport) {
			let startX = null
			let startY = null
			let pointerId = null
			let moved = false

			viewport.addEventListener('pointerdown', function (event) {
				startX = event.clientX
				startY = event.clientY
				pointerId = event.pointerId
				moved = false

				viewport.setPointerCapture?.(event.pointerId)
			})

			viewport.addEventListener('pointermove', function (event) {
				if (startX === null || pointerId !== event.pointerId) {
					return
				}

				const distanceX = event.clientX - startX

				const distanceY = event.clientY - startY

				if (Math.abs(distanceX) > 10 || Math.abs(distanceY) > 10) {
					moved = true
				}
			})

			viewport.addEventListener('pointerup', function (event) {
				if (startX === null || pointerId !== event.pointerId) {
					return
				}

				const distanceX = event.clientX - startX

				const distanceY = event.clientY - startY

				viewport.dataset.swipeDistance = String(Math.abs(distanceX))

				if (
					Math.abs(distanceX) >= 45 &&
					Math.abs(distanceX) > Math.abs(distanceY)
				) {
					showImage(distanceX < 0 ? state.imageIndex + 1 : state.imageIndex - 1)
				}

				window.setTimeout(function () {
					viewport.dataset.swipeDistance = '0'
				}, 50)

				startX = null
				startY = null
				pointerId = null
				moved = false
			})

			viewport.addEventListener('pointercancel', function () {
				startX = null
				startY = null
				pointerId = null
				moved = false
			})
		}

		function initGalleryZoomIndicator(viewport) {
			const indicator = viewport.querySelector('[data-gallery-zoom-indicator]')

			if (!indicator) {
				return
			}

			viewport.addEventListener('pointermove', function (event) {
				if (event.pointerType && event.pointerType !== 'mouse') {
					return
				}

				const rect = viewport.getBoundingClientRect()

				const x = event.clientX - rect.left

				const y = event.clientY - rect.top

				indicator.style.left = x + 'px'

				indicator.style.top = y + 'px'
			})
		}

		function renderGallery() {
			const thumbs = page.querySelector('[data-gallery-thumbs]')

			if (!thumbs) {
				return
			}

			thumbs.innerHTML = ''

			state.images.forEach(function (image, index) {
				const button = document.createElement('button')

				button.type = 'button'

				button.className = 'product-gallery__thumb'

				if (index === state.imageIndex) {
					button.classList.add('product-gallery__thumb--active')
				}

				button.dataset.galleryThumb = ''

				button.dataset.galleryIndex = String(index)

				button.setAttribute('aria-label', image.alt || data.product.title)

				const img = document.createElement('img')

				img.src = image.src
				img.alt = ''

				img.loading = index === 0 ? 'eager' : 'lazy'

				img.decoding = 'async'

				button.appendChild(img)

				thumbs.appendChild(button)
			})

			updateGalleryImage()
		}

		function showImage(index) {
			if (!state.images.length) {
				return
			}

			if (index < 0) {
				index = state.images.length - 1
			}

			if (index >= state.images.length) {
				index = 0
			}

			state.imageIndex = index

			updateGalleryImage()
		}

		function updateGalleryImage() {
			const image = page.querySelector('[data-gallery-image]')

			const current = page.querySelector('[data-gallery-current]')

			const total = page.querySelector('[data-gallery-total]')

			const previousButton = page.querySelector('[data-gallery-prev]')

			const nextButton = page.querySelector('[data-gallery-next]')

			const selected = state.images[state.imageIndex]

			if (!image || !selected) {
				return
			}

			image.classList.add('product-gallery__image--changing')

			window.setTimeout(function () {
				image.src = selected.src

				image.alt = selected.alt || data.product.title

				image.classList.remove('product-gallery__image--changing')
			}, 120)

			page
				.querySelectorAll('[data-gallery-thumb]')
				.forEach(function (thumb, index) {
					thumb.classList.toggle(
						'product-gallery__thumb--active',
						index === state.imageIndex
					)
				})

			if (current) {
				current.textContent = String(state.imageIndex + 1)
			}

			if (total) {
				total.textContent = String(state.images.length)
			}

			const hasMultiple = state.images.length > 1

			if (previousButton) {
				previousButton.disabled = !hasMultiple
			}

			if (nextButton) {
				nextButton.disabled = !hasMultiple
			}
		}

		/* ======================================================================
	   LIGHTBOX
	   ====================================================================== */

		function initLightbox() {
			const lightbox = document.querySelector('[data-product-lightbox]')

			if (!lightbox) {
				return
			}

			const viewport = lightbox.querySelector('[data-lightbox-viewport]')

			page
				.querySelector('[data-gallery-viewport]')
				?.addEventListener('click', function (event) {
					const distance = Number(
						event.currentTarget.dataset.swipeDistance || 0
					)

					if (distance >= 20) {
						return
					}

					openLightbox()
				})

			lightbox
				.querySelectorAll('[data-lightbox-close]')
				.forEach(function (button) {
					button.addEventListener('click', closeLightbox)
				})

			lightbox
				.querySelector('[data-lightbox-prev]')
				?.addEventListener('click', function () {
					showLightboxImage(state.imageIndex - 1)
				})

			lightbox
				.querySelector('[data-lightbox-next]')
				?.addEventListener('click', function () {
					showLightboxImage(state.imageIndex + 1)
				})

			lightbox
				.querySelector('[data-lightbox-reset]')
				?.addEventListener('click', resetLightboxZoom)

			lightbox.addEventListener('click', function (event) {
				const thumb = event.target.closest('[data-lightbox-thumb]')

				if (!thumb) {
					return
				}

				showLightboxImage(Number(thumb.dataset.lightboxIndex))
			})

			document.addEventListener('keydown', function (event) {
				if (!state.lightboxOpen) {
					return
				}

				if (event.key === 'Escape') {
					closeLightbox()
				}

				if (event.key === 'ArrowLeft') {
					showLightboxImage(state.imageIndex - 1)
				}

				if (event.key === 'ArrowRight') {
					showLightboxImage(state.imageIndex + 1)
				}
			})

			if (viewport) {
				initLightboxZoom(viewport)
				initLightboxSwipe(viewport)
			}
		}

		function initLightboxSwipe(viewport) {
			let startX = null
			let startY = null
			let pointerId = null

			viewport.addEventListener('pointerdown', function (event) {
				if (state.lightboxScale > 1) {
					return
				}

				startX = event.clientX
				startY = event.clientY
				pointerId = event.pointerId
			})

			viewport.addEventListener('pointerup', function (event) {
				if (
					startX === null ||
					pointerId !== event.pointerId ||
					state.lightboxScale > 1
				) {
					return
				}

				const distanceX = event.clientX - startX

				const distanceY = event.clientY - startY

				if (
					Math.abs(distanceX) >= 50 &&
					Math.abs(distanceX) > Math.abs(distanceY)
				) {
					showLightboxImage(
						distanceX < 0 ? state.imageIndex + 1 : state.imageIndex - 1
					)
				}

				startX = null
				startY = null
				pointerId = null
			})

			viewport.addEventListener('pointercancel', function () {
				startX = null
				startY = null
				pointerId = null
			})
		}

		function initLightboxZoom(viewport) {
			let isDragging = false

			let startX = 0
			let startY = 0

			let initialX = 0
			let initialY = 0

			viewport.addEventListener('dblclick', function (event) {
				event.preventDefault()

				if (state.lightboxScale > 1) {
					resetLightboxZoom()

					return
				}

				state.lightboxScale = 2

				applyLightboxTransform()
			})

			viewport.addEventListener(
				'wheel',
				function (event) {
					if (!state.lightboxOpen) {
						return
					}

					event.preventDefault()

					const direction = event.deltaY < 0 ? 1 : -1

					state.lightboxScale = clamp(
						state.lightboxScale + direction * 0.25,
						1,
						3
					)

					if (state.lightboxScale === 1) {
						state.lightboxX = 0
						state.lightboxY = 0
					}

					applyLightboxTransform()
				},
				{
					passive: false
				}
			)

			viewport.addEventListener('pointerdown', function (event) {
				if (state.lightboxScale <= 1) {
					return
				}

				isDragging = true

				startX = event.clientX
				startY = event.clientY

				initialX = state.lightboxX

				initialY = state.lightboxY

				viewport.classList.add('is-dragging')

				viewport.setPointerCapture?.(event.pointerId)
			})

			viewport.addEventListener('pointermove', function (event) {
				if (!isDragging) {
					return
				}

				state.lightboxX = initialX + event.clientX - startX

				state.lightboxY = initialY + event.clientY - startY

				applyLightboxTransform(false)
			})

			const finishDragging = function () {
				isDragging = false

				viewport.classList.remove('is-dragging')
			}

			viewport.addEventListener('pointerup', finishDragging)

			viewport.addEventListener('pointercancel', finishDragging)
		}

		function applyLightboxTransform(animate = true) {
			const viewport = document.querySelector('[data-lightbox-viewport]')

			const image = document.querySelector('[data-lightbox-image]')

			const resetButton = document.querySelector('[data-lightbox-reset]')

			if (!viewport || !image) {
				return
			}

			viewport.classList.toggle('is-zoomed', state.lightboxScale > 1)

			image.style.transition = animate ? '' : 'none'

			image.style.setProperty('--lightbox-scale', String(state.lightboxScale))

			image.style.setProperty('--lightbox-x', state.lightboxX + 'px')

			image.style.setProperty('--lightbox-y', state.lightboxY + 'px')

			if (resetButton) {
				resetButton.hidden = state.lightboxScale <= 1
			}
		}

		function resetLightboxZoom() {
			state.lightboxScale = 1
			state.lightboxX = 0
			state.lightboxY = 0

			applyLightboxTransform()
		}

		function openLightbox() {
			const lightbox = document.querySelector('[data-product-lightbox]')

			if (!lightbox) {
				return
			}

			state.lightboxOpen = true

			renderLightbox()

			resetLightboxZoom()

			lightbox.classList.add('is-open')

			lightbox.setAttribute('aria-hidden', 'false')

			document.body.classList.add('product-lightbox-open')
		}

		function closeLightbox() {
			const lightbox = document.querySelector('[data-product-lightbox]')

			if (!lightbox) {
				return
			}

			state.lightboxOpen = false

			lightbox.classList.remove('is-open')

			lightbox.setAttribute('aria-hidden', 'true')

			document.body.classList.remove('product-lightbox-open')

			resetLightboxZoom()
		}

		function renderLightbox() {
			const lightbox = document.querySelector('[data-product-lightbox]')

			const thumbs = lightbox?.querySelector('[data-lightbox-thumbs]')

			if (!lightbox || !thumbs) {
				return
			}

			const hasMultipleImages = state.images.length > 1

			lightbox.classList.toggle('product-lightbox--single', !hasMultipleImages)

			thumbs.innerHTML = ''

			state.images.forEach(function (image, index) {
				const button = document.createElement('button')

				button.type = 'button'

				button.className = 'product-lightbox__thumb'

				if (index === state.imageIndex) {
					button.classList.add('is-active')
				}

				button.dataset.lightboxThumb = ''

				button.dataset.lightboxIndex = String(index)

				const img = document.createElement('img')

				img.src = image.src
				img.alt = ''
				img.loading = 'lazy'
				img.decoding = 'async'

				button.appendChild(img)

				thumbs.appendChild(button)
			})

			updateLightboxImage()
		}

		function showLightboxImage(index) {
			if (!state.images.length) {
				return
			}

			if (index < 0) {
				index = state.images.length - 1
			}

			if (index >= state.images.length) {
				index = 0
			}

			state.imageIndex = index

			resetLightboxZoom()

			updateGalleryImage()
			updateLightboxImage()
		}

		function updateLightboxImage() {
			const lightbox = document.querySelector('[data-product-lightbox]')

			const image = lightbox?.querySelector('[data-lightbox-image]')

			const current = lightbox?.querySelector('[data-lightbox-current]')

			const total = lightbox?.querySelector('[data-lightbox-total]')

			const prev = lightbox?.querySelector('[data-lightbox-prev]')

			const next = lightbox?.querySelector('[data-lightbox-next]')

			const selected = state.images[state.imageIndex]

			if (!image || !selected) {
				return
			}

			const applyImage = function () {
				image.src = selected.src

				image.alt = selected.alt || data.product.title
			}

			if (!image.getAttribute('src')) {
				applyImage()
			} else {
				image.classList.add('is-changing')

				window.setTimeout(function () {
					applyImage()

					image.classList.remove('is-changing')
				}, 120)
			}

			lightbox
				.querySelectorAll('[data-lightbox-thumb]')
				.forEach(function (thumb, index) {
					thumb.classList.toggle('is-active', index === state.imageIndex)
				})

			if (current) {
				current.textContent = String(state.imageIndex + 1)
			}

			if (total) {
				total.textContent = String(state.images.length)
			}

			const hasMultiple = state.images.length > 1

			if (prev) {
				prev.disabled = !hasMultiple
			}

			if (next) {
				next.disabled = !hasMultiple
			}
		}

		/* ======================================================================
	   VARIATIONS
	   ====================================================================== */

		function initColorSelect() {
			const select = page.querySelector('[data-color-select]')

			if (!select) {
				return
			}

			const trigger = select.querySelector('[data-color-select-trigger]')

			const dropdown = select.querySelector('[data-color-select-dropdown]')

			if (!trigger || !dropdown) {
				return
			}

			trigger.addEventListener('click', function () {
				const isOpen = trigger.getAttribute('aria-expanded') === 'true'

				setColorSelectOpen(!isOpen)
			})

			document.addEventListener('pointerdown', function (event) {
				if (!select.contains(event.target)) {
					setColorSelectOpen(false)
				}
			})

			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape') {
					setColorSelectOpen(false)

					trigger.focus()
				}
			})

			function setColorSelectOpen(isOpen) {
				trigger.setAttribute('aria-expanded', String(isOpen))

				dropdown.hidden = !isOpen
			}
		}

		function initVariationOptions() {
			page.addEventListener('click', function (event) {
				const option = event.target.closest('[data-variation-option]')

				if (!option || option.disabled) {
					return
				}

				const variationId = Number(option.dataset.variationId)

				selectVariation(variationId)
			})
		}

		function selectVariation(variationId) {
			const variation = data.variations.find(function (item) {
				return Number(item.id) === variationId
			})

			if (!variation || !variation.available) {
				return
			}

			state.selectedVariationId = variationId

			state.imageIndex = 0

			state.images = variation.images.length
				? variation.images
				: data.product.images

			page.dataset.selectedVariation = String(variationId)

			updateSelectedOptions(variation)

			updateProductInfo(variation)

			renderGallery()

			if (state.lightboxOpen) {
				renderLightbox()
			}

			document.dispatchEvent(
				new CustomEvent('product:variation-changed', {
					detail: {
						productId: data.product.id,

						variation: variation
					}
				})
			)
		}

		function updateSelectedOptions(variation) {
			page
				.querySelectorAll('[data-variation-option]')
				.forEach(function (option) {
					const selected =
						Number(option.dataset.variationId) === Number(variation.id)

					option.classList.toggle(
						'product-color-select__option--selected',
						selected
					)

					option.classList.toggle(
						'product-volume-list__option--selected',
						selected
					)
				})

			const value = page.querySelector('[data-color-select-value]')

			const swatch = page.querySelector('[data-color-select-swatch]')

			if (value) {
				value.textContent = formatVariationLabel(variation)
			}

			if (swatch) {
				const background = getVariationSwatch(variation)

				if (background) {
					swatch.hidden = false

					swatch.style.setProperty('--product-swatch', background)
				} else {
					swatch.hidden = true

					swatch.style.removeProperty('--product-swatch')
				}
			}

			const trigger = page.querySelector('[data-color-select-trigger]')

			const dropdown = page.querySelector('[data-color-select-dropdown]')

			if (trigger && dropdown) {
				trigger.setAttribute('aria-expanded', 'false')

				dropdown.hidden = true
			}
		}

		function updateProductInfo(variation) {
			const currentPrice = page.querySelector('[data-price-current]')

			const oldPrice = page.querySelector('[data-price-old]')

			const sku = page.querySelector('[data-product-sku]')

			const volume = page.querySelector('[data-product-volume]')

			const volumeRow = page.querySelector('[data-product-volume-row]')

			const basePrice = data.isB2B ? variation.wholesalePrice : variation.price

			const discountPrice = data.isB2B ? null : variation.discountPrice

			if (currentPrice) {
				currentPrice.textContent = formatPrice(discountPrice || basePrice)
			}

			if (oldPrice) {
				if (discountPrice) {
					oldPrice.hidden = false

					oldPrice.textContent = formatPrice(basePrice)
				} else {
					oldPrice.hidden = true
					oldPrice.textContent = ''
				}
			}

			if (sku) {
				sku.textContent = variation.sku || ''
			}

			if (volume && volumeRow) {
				volume.textContent = variation.volume || ''

				volumeRow.hidden = !variation.volume
			}

			const availabilityVariant = document.querySelector(
				'[data-availability-variant]'
			)

			if (availabilityVariant) {
				const variantParts = [variation.color, variation.volume].filter(Boolean)

				availabilityVariant.textContent =
					variantParts.join(' · ') || variation.title || ''
			}

			/*
			 * Sticky price.
			 */
			const stickyPrice = document.querySelector('[data-product-sticky-price]')

			if (stickyPrice) {
				stickyPrice.textContent = formatPrice(discountPrice || basePrice)
			}
		}
		function formatVariationLabel(variation) {
			const mainLabel = variation.color || variation.title || ''

			const volume = variation.volume || ''

			if (mainLabel && volume) {
				/*
				 * Не дублируем объём,
				 * если он уже находится
				 * в названии варианта.
				 */
				if (mainLabel.toLowerCase().includes(volume.toLowerCase())) {
					return mainLabel
				}

				return mainLabel + ' · ' + volume
			}

			return mainLabel || volume || ''
		}

		function getVariationSwatch(variation) {
			if (!variation || !Array.isArray(variation.colors)) {
				return null
			}

			const colors = []

			variation.colors.forEach(function (color) {
				color = String(color || '')
					.trim()
					.toUpperCase()

				if (!/^#[0-9A-F]{6}$/.test(color)) {
					return
				}

				if (!colors.includes(color)) {
					colors.push(color)
				}
			})

			if (!colors.length) {
				return null
			}

			if (colors.length === 1) {
				return colors[0]
			}

			const step = 100 / colors.length

			const parts = colors.map(function (color, index) {
				const start = index * step

				const end = (index + 1) * step

				return color + ' ' + start + '% ' + end + '%'
			})

			return 'conic-gradient(' + parts.join(', ') + ')'
		}
		function formatPrice(value) {
			const amount = Number(value) || 0

			return (
				new Intl.NumberFormat(data.language === 'ro' ? 'ro-MD' : 'ru-MD', {
					maximumFractionDigits: 0
				}).format(amount) +
				' ' +
				data.currency
			)
		}

		/* ======================================================================
	   TITLE
	   ====================================================================== */

		function initExpandableTitle() {
			const container = page.querySelector('[data-expandable-title]')

			if (!container) {
				return
			}

			const title = container.querySelector('[data-expandable-title-text]')

			const toggle = container.querySelector('[data-expandable-title-toggle]')

			if (!title || !toggle) {
				return
			}

			let isExpanded = false

			let collapsedHeight = 0
			let fullHeight = 0

			const calculate = function () {
				container.classList.remove('product-title--expanded')

				title.style.height = 'auto'

				const computedStyle = window.getComputedStyle(title)

				const lineHeight = parseFloat(computedStyle.lineHeight)

				collapsedHeight = lineHeight * 2

				fullHeight = title.scrollHeight

				const isOverflowing = fullHeight > collapsedHeight + 2

				toggle.hidden = !isOverflowing

				if (!isOverflowing) {
					title.style.height = 'auto'

					return
				}

				title.style.height = isExpanded
					? fullHeight + 'px'
					: collapsedHeight + 'px'

				container.classList.toggle('product-title--expanded', isExpanded)
			}

			toggle.addEventListener('click', function () {
				isExpanded = !isExpanded

				title.style.height = title.getBoundingClientRect().height + 'px'

				container.classList.toggle('product-title--expanded', isExpanded)

				requestAnimationFrame(function () {
					title.style.height = isExpanded
						? fullHeight + 'px'
						: collapsedHeight + 'px'
				})

				toggle.setAttribute('aria-expanded', String(isExpanded))

				toggle.textContent = isExpanded
					? toggle.dataset.labelClose
					: toggle.dataset.labelOpen
			})

			window.addEventListener('resize', debounce(calculate, 150))

			calculate()
		}

		/* ======================================================================
	   ACCORDIONS
	   ====================================================================== */

		function initAccordions() {
			page.addEventListener('click', function (event) {
				const trigger = event.target.closest('[data-product-accordion-trigger]')

				if (!trigger) {
					return
				}

				const content = trigger.nextElementSibling

				if (!content || !content.matches('[data-product-accordion-content]')) {
					return
				}

				const isOpen = trigger.getAttribute('aria-expanded') === 'true'

				if (isOpen) {
					closeAccordion(trigger, content)
				} else {
					openAccordion(trigger, content)
				}
			})
		}

		function openAccordion(trigger, content) {
			const body = content.querySelector('.product-accordion__body')

			if (!body) {
				return
			}

			trigger.setAttribute('aria-expanded', 'true')

			content.classList.add('is-open')

			content.style.height = '0px'

			requestAnimationFrame(function () {
				content.style.height = body.scrollHeight + 'px'
			})

			const onTransitionEnd = function (event) {
				if (event.propertyName !== 'height') {
					return
				}

				content.style.height = 'auto'

				content.removeEventListener('transitionend', onTransitionEnd)
			}

			content.addEventListener('transitionend', onTransitionEnd)
		}

		function closeAccordion(trigger, content) {
			const currentHeight = content.scrollHeight

			content.style.height = currentHeight + 'px'

			content.offsetHeight

			trigger.setAttribute('aria-expanded', 'false')

			content.classList.remove('is-open')

			requestAnimationFrame(function () {
				content.style.height = '0px'
			})
		}

		/* ======================================================================
	   EXPANDABLE CONTENT
	   ====================================================================== */

		function initExpandableContent() {
			const blocks = page.querySelectorAll('[data-expandable-content]')

			blocks.forEach(function (block) {
				const viewport = block.querySelector(
					'[data-expandable-content-viewport]'
				)

				const inner = block.querySelector('[data-expandable-content-inner]')

				const toggle = block.querySelector('[data-expandable-content-toggle]')

				if (!viewport || !inner || !toggle) {
					return
				}

				const collapsedHeight = 240

				let expanded = false

				let fullHeight = 0

				const calculate = function () {
					viewport.style.height = 'auto'

					fullHeight = inner.scrollHeight

					const needsToggle = fullHeight > collapsedHeight + 10

					toggle.hidden = !needsToggle

					if (!needsToggle) {
						viewport.style.height = 'auto'

						block.classList.add('product-rich-text--expanded')

						return
					}

					viewport.style.height = expanded
						? fullHeight + 'px'
						: collapsedHeight + 'px'

					block.classList.toggle('product-rich-text--expanded', expanded)
				}

				toggle.addEventListener('click', function () {
					expanded = !expanded

					viewport.style.height = viewport.getBoundingClientRect().height + 'px'

					requestAnimationFrame(function () {
						viewport.style.height = expanded
							? fullHeight + 'px'
							: collapsedHeight + 'px'
					})

					block.classList.toggle('product-rich-text--expanded', expanded)

					toggle.setAttribute('aria-expanded', String(expanded))

					toggle.textContent = expanded
						? toggle.dataset.labelClose
						: toggle.dataset.labelOpen

					updateParentAccordionHeight(block)
				})

				window.addEventListener('resize', debounce(calculate, 150))

				calculate()
			})
		}

		function updateParentAccordionHeight(element) {
			const content = element.closest('[data-product-accordion-content]')

			if (!content || !content.classList.contains('is-open')) {
				return
			}

			const body = content.querySelector('.product-accordion__body')

			if (!body) {
				return
			}

			content.style.height = body.scrollHeight + 'px'

			const onTransitionEnd = function (event) {
				if (event.propertyName !== 'height') {
					return
				}

				content.style.height = 'auto'

				content.removeEventListener('transitionend', onTransitionEnd)
			}

			content.addEventListener('transitionend', onTransitionEnd)
		}

		/* ======================================================================
	   READING DRAWER
	   ====================================================================== */

		function initReadingDrawer() {
			const drawer = document.querySelector('[data-reading-drawer]')

			const openButton = page.querySelector('[data-reading-drawer-open]')

			if (!drawer || !openButton) {
				return
			}

			const closeButtons = drawer.querySelectorAll(
				'[data-reading-drawer-close]'
			)

			let previousActiveElement = null

			openButton.addEventListener('click', function () {
				previousActiveElement = document.activeElement

				drawer.classList.add('is-open')

				drawer.setAttribute('aria-hidden', 'false')

				document.body.classList.add('product-reading-drawer-open')

				requestAnimationFrame(function () {
					drawer.querySelector('.product-reading-drawer__close')?.focus()
				})
			})

			closeButtons.forEach(function (button) {
				button.addEventListener('click', closeReadingDrawer)
			})

			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
					closeReadingDrawer()
				}
			})

			function closeReadingDrawer() {
				drawer.classList.remove('is-open')

				drawer.setAttribute('aria-hidden', 'true')

				document.body.classList.remove('product-reading-drawer-open')

				previousActiveElement?.focus()
			}
		}

		/* ======================================================================
	   AVAILABILITY
	   ====================================================================== */

		function getCurrentExternalId() {
			const variation = data.variations.find(function (item) {
				return Number(item.id) === Number(state.selectedVariationId)
			})

			return String(
				variation?.sku || data.product.guid || data.product.sku || ''
			)
		}

		function loadCurrentAvailability() {
			const externalId = getCurrentExternalId()

			if (!externalId) {
				renderAvailabilityUnavailable()

				return
			}

			loadAvailability(externalId)
		}

		async function loadAvailability(externalId) {
			const root = page.querySelector('[data-product-availability]')

			if (!root) {
				return
			}

			const summary = root.querySelector('[data-availability-summary]')

			const loader = root.querySelector('[data-availability-loader]')

			const drawerSummary = document.querySelector(
				'[data-availability-drawer-summary]'
			)

			const list = document.querySelector('[data-availability-list]')

			const empty = document.querySelector('[data-availability-empty]')

			if (!summary || !loader || !drawerSummary || !list || !empty) {
				console.error('Не найдены элементы интерфейса наличия')

				return
			}

			if (state.availabilityCache.has(externalId)) {
				renderAvailability(state.availabilityCache.get(externalId))

				return
			}

			state.availabilityController?.abort()

			state.availabilityController = new AbortController()

			const loadingText =
				data.language === 'ro'
					? 'Verificăm disponibilitatea...'
					: 'Проверяем наличие...'

			summary.textContent = loadingText

			drawerSummary.textContent = loadingText

			loader.hidden = false

			empty.hidden = true

			list.innerHTML = ''

			try {
				const body = new URLSearchParams({
					external_id: externalId
				})

				const response = await fetch(root.dataset.url, {
					method: 'POST',

					headers: {
						'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
					},

					body: body,

					signal: state.availabilityController.signal
				})

				if (!response.ok) {
					throw new Error('Availability request failed: ' + response.status)
				}

				const result = await response.json()

				state.availabilityCache.set(externalId, result)

				renderAvailability(result)
			} catch (error) {
				if (error.name === 'AbortError') {
					return
				}

				const errorText =
					data.language === 'ro'
						? 'Disponibilitatea nu a putut fi încărcată'
						: 'Не удалось загрузить наличие'

				summary.textContent = errorText

				drawerSummary.textContent = errorText

				console.error('Ошибка загрузки наличия', error)
			} finally {
				loader.hidden = true
			}
		}

		function renderAvailability(result) {
			const root = page.querySelector('[data-product-availability]')

			if (!root) {
				return
			}

			const summary = root.querySelector('[data-availability-summary]')

			const drawerSummary = document.querySelector(
				'[data-availability-drawer-summary]'
			)

			const list = document.querySelector('[data-availability-list]')

			const empty = document.querySelector('[data-availability-empty]')

			if (!summary || !drawerSummary || !list || !empty) {
				return
			}

			const stores = Array.isArray(result.stores) ? result.stores : []

			list.innerHTML = ''

			empty.hidden = stores.length > 0

			const summaryText = stores.length
				? data.language === 'ro'
					? `Disponibil în ${stores.length} magazine`
					: `Доступно в ${stores.length} магазинах`
				: data.language === 'ro'
					? 'Indisponibil în magazine'
					: 'Нет в наличии в магазинах'

			summary.textContent = summaryText

			drawerSummary.textContent = summaryText

			if (!stores.length) {
				return
			}

			stores.forEach(function (store) {
				const item = document.createElement('article')

				item.className = 'product-availability__store'

				const statusText =
					store.status === 'low'
						? data.language === 'ro'
							? 'Stoc limitat'
							: 'Осталось мало'
						: data.language === 'ro'
							? 'În stoc'
							: 'В наличии'

				item.innerHTML = `
					<div class="product-availability__store-main">
						<h4 class="product-availability__store-title">
							${escapeHtml(store.title)}
						</h4>

						${
							store.address
								? `
									<p class="product-availability__store-address">
										${escapeHtml(store.address)}
									</p>
								`
								: ''
						}

						${
							store.time
								? `
									<p class="product-availability__store-time">
										${escapeHtml(store.time)}
									</p>
								`
								: ''
						}

						${
							store.phone
								? `
									<p class="product-availability__store-phone">
										<a href="tel:${escapeHtml(store.phone)}">
											${escapeHtml(store.phone)}
										</a>
									</p>
								`
								: ''
						}
					</div>

					<span
						class="
							product-availability__status
							product-availability__status--${store.status}
						"
					>
						${statusText}
					</span>
				`

				list.appendChild(item)
			})
		}

		function renderAvailabilityUnavailable() {
			const root = page.querySelector('[data-product-availability]')

			const summary = root?.querySelector('[data-availability-summary]')

			const drawerSummary = document.querySelector(
				'[data-availability-drawer-summary]'
			)

			const list = document.querySelector('[data-availability-list]')

			const empty = document.querySelector('[data-availability-empty]')

			const text =
				data.language === 'ro'
					? 'Disponibilitatea nu este specificată'
					: 'Данные о наличии пока отсутствуют'

			if (summary) {
				summary.textContent = text
			}

			if (drawerSummary) {
				drawerSummary.textContent = text
			}

			if (list) {
				list.innerHTML = ''
			}

			if (empty) {
				empty.hidden = false
				empty.textContent = text
			}
		}

		/* ======================================================================
	   AVAILABILITY DRAWER
	   ====================================================================== */

		/* ======================================================================
	   CART
	   ====================================================================== */

		function initCartActions() {
			page
				.querySelector('[data-product-add]')
				?.addEventListener('click', function () {
					addCurrentProduct(false)
				})
		}

		function addCurrentProduct(redirectToCheckout, checkoutUrl) {
			if (state.isSubmitting) {
				return
			}

			state.isSubmitting = true

			setButtonsLoading(true)

			$.ajax({
				url: '/cart_add',

				type: 'POST',

				dataType: 'json',

				data: {
					product_id: data.product.id,

					variable: state.selectedVariationId,

					quantity: 1
				},

				success: function (response) {
					if (!response || response.status !== 'ok') {
						console.error('Товар не был добавлен', response)

						showCartResponseError(response)

						return
					}

					/*
					|--------------------------------------------------------------------------
					| GA4 — add_to_cart
					|--------------------------------------------------------------------------
					|
					| Отправляем только после подтверждения сервера.
					| quantity берём из response.added_quantity,
					| потому что backend может ограничить количество по остатку.
					|
					*/

					if (
						window.VNAnalytics &&
						typeof window.VNAnalytics.getCurrentProductItem === 'function' &&
						typeof window.VNAnalytics.push === 'function'
					) {
						const analyticsItem = window.VNAnalytics.getCurrentProductItem()

						if (analyticsItem) {
							const addedQuantity = Math.max(
								1,
								Number(response.added_quantity || 1)
							)

							analyticsItem.quantity = addedQuantity

							window.VNAnalytics.push('add_to_cart', {
								currency: 'MDL',

								value: Number(analyticsItem.price) * addedQuantity,

								items: [analyticsItem]
							})
						}
					}

					/*
					|--------------------------------------------------------------------------
					| Header cart count
					|--------------------------------------------------------------------------
					*/

					$('.actions-main-header__counts')
						.html(response.total_items)
						.toggleClass('d_none', Number(response.total_items) <= 0)

					/*
					|--------------------------------------------------------------------------
					| Buy now
					|--------------------------------------------------------------------------
					*/

					if (redirectToCheckout && checkoutUrl) {
						window.location.href = checkoutUrl

						return
					}

					/*
					|--------------------------------------------------------------------------
					| Cart Drawer
					|--------------------------------------------------------------------------
					*/

					if (
						window.VNCartDrawer &&
						typeof window.VNCartDrawer.open === 'function'
					) {
						window.VNCartDrawer.open({
							load: true,
							addedRowId: response.row_id || null
						})

						return
					}

					console.warn('VNCartDrawer не инициализирован')
				},

				error: function (xhr) {
					console.error('Ошибка добавления товара', xhr.responseText || xhr)

					const response = xhr.responseJSON

					showCartResponseError(response)
				},

				complete: function () {
					state.isSubmitting = false

					setButtonsLoading(false)
				}
			})
		}

		function showCartResponseError(response) {
			if (window.VNNotify && typeof window.VNNotify.toast === 'function') {
				window.VNNotify.toast({
					type: 'warning',

					title:
						data.language === 'ro'
							? 'Produsul nu a fost adăugat'
							: 'Товар не добавлен',

					message:
						response?.message ||
						(data.language === 'ro'
							? 'Încearcă din nou.'
							: 'Попробуйте ещё раз.'),

					duration: 4500
				})
			}
		}

		function setButtonsLoading(isLoading) {
			page
				.querySelectorAll('[data-product-add], ' + '[data-product-buy-now]')
				.forEach(function (button) {
					button.disabled = isLoading

					button.classList.toggle('is-loading', isLoading)
				})

			const stickyButton = document.querySelector('[data-product-sticky-add]')

			if (stickyButton) {
				stickyButton.disabled = isLoading

				stickyButton.classList.toggle('is-loading', isLoading)
			}
		}

		/* ======================================================================
	   MOBILE STICKY CART
	   ====================================================================== */

		function initStickyCart() {
			const stickyBar = document.querySelector('[data-product-sticky-bar]')

			const stickyButton = document.querySelector('[data-product-sticky-add]')

			const stickyPrice = document.querySelector('[data-product-sticky-price]')

			const originalButton = page.querySelector('[data-product-add]')

			if (!stickyBar || !stickyButton || !originalButton) {
				console.warn('Product sticky cart: элементы не найдены', {
					stickyBar: Boolean(stickyBar),

					stickyButton: Boolean(stickyButton),

					originalButton: Boolean(originalButton)
				})

				return
			}

			const mobileMedia = window.matchMedia('(max-width: 900px)')

			let ticking = false

			let currentlyVisible = false

			function getCurrentPrice() {
				const currentPrice = page.querySelector('[data-price-current]')

				return currentPrice?.textContent?.trim() || ''
			}

			function syncStickyContent() {
				if (stickyPrice) {
					stickyPrice.textContent = getCurrentPrice()
				}

				stickyButton.disabled = originalButton.disabled || state.isSubmitting

				stickyButton.classList.toggle('is-loading', state.isSubmitting)
			}

			function setVisible(shouldShow) {
				if (currentlyVisible === shouldShow) {
					return
				}

				currentlyVisible = shouldShow

				stickyBar.classList.toggle('is-visible', shouldShow)

				stickyBar.setAttribute('aria-hidden', shouldShow ? 'false' : 'true')
			}

			function updateVisibility() {
				ticking = false

				if (!mobileMedia.matches) {
					setVisible(false)

					return
				}

				const rect = originalButton.getBoundingClientRect()

				/*
				 * Пока пользователь ещё НЕ дошёл
				 * до основной кнопки, она находится
				 * ниже viewport:
				 *
				 * rect.top > window.innerHeight
				 *
				 * Sticky не показываем.
				 *
				 * После прокрутки кнопка проходит
				 * верхнюю границу:
				 *
				 * rect.bottom < 0
				 *
				 * Тогда sticky появляется.
				 */
				const passedAbove = rect.bottom <= 0

				if (passedAbove) {
					syncStickyContent()
				}

				setVisible(passedAbove)
			}

			function requestUpdate() {
				if (ticking) {
					return
				}

				ticking = true

				window.requestAnimationFrame(updateVisibility)
			}

			stickyButton.addEventListener('click', function () {
				if (stickyButton.disabled || state.isSubmitting) {
					return
				}

				/*
				 * Используем ТОТ ЖЕ cart flow.
				 *
				 * Второго AJAX-обработчика
				 * здесь нет.
				 */
				addCurrentProduct(false)
			})

			const mutationObserver = new MutationObserver(function () {
				syncStickyContent()
			})

			mutationObserver.observe(originalButton, {
				attributes: true,

				attributeFilter: ['class', 'disabled']
			})

			document.addEventListener('product:variation-changed', function () {
				syncStickyContent()

				requestUpdate()
			})

			window.addEventListener('scroll', requestUpdate, {
				passive: true
			})

			window.addEventListener('resize', requestUpdate, {
				passive: true
			})

			if (mobileMedia.addEventListener) {
				mobileMedia.addEventListener('change', function () {
					requestUpdate()
				})
			}

			syncStickyContent()

			updateVisibility()
		}

		/* ======================================================================
	   HELPERS
	   ====================================================================== */

		function escapeHtml(value) {
			const element = document.createElement('div')

			element.textContent = String(value || '')

			return element.innerHTML
		}

		function clamp(value, min, max) {
			return Math.min(Math.max(value, min), max)
		}

		function debounce(callback, delay) {
			let timeoutId

			return function () {
				const args = arguments
				const context = this

				window.clearTimeout(timeoutId)

				timeoutId = window.setTimeout(function () {
					callback.apply(context, args)
				}, delay)
			}
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Safe initialization
	|--------------------------------------------------------------------------
	*/

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initProductPage, {
			once: true
		})
	} else {
		initProductPage()
	}
})()
