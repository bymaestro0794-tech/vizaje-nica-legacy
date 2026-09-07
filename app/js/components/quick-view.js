;(function () {
	'use strict'

	const drawer = document.querySelector('[data-product-quick-view]')

	if (!drawer) {
		return
	}

	const body = drawer.querySelector('[data-quick-view-body]')

	const loader = drawer.querySelector('[data-quick-view-loader]')

	const panel = drawer.querySelector('.product-quick-view__panel')

	let controller = null
	let previousActiveElement = null
	let currentProductId = null

	/* ======================================================================
	   GLOBAL EVENTS
	   ====================================================================== */

	document.addEventListener('click', function (event) {
		const trigger = event.target.closest('[data-product-quick-view-open]')

		if (trigger) {
			event.preventDefault()

			const productId = Number(trigger.dataset.productId)

			if (productId > 0) {
				open(productId, trigger)
			}

			return
		}

		const closeButton = event.target.closest('[data-quick-view-close]')

		if (closeButton && drawer.contains(closeButton)) {
			event.preventDefault()

			close()
		}
	})

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
			close()
		}
	})

	/* ======================================================================
	   OPEN
	   ====================================================================== */

	async function open(productId, trigger) {
		if (!body) {
			return
		}

		previousActiveElement = trigger || document.activeElement

		currentProductId = productId

		/*
		|--------------------------------------------------------------------------
		| Abort previous request
		|--------------------------------------------------------------------------
		*/

		if (controller) {
			controller.abort()
		}

		controller = new AbortController()

		/*
		|--------------------------------------------------------------------------
		| Drawer state
		|--------------------------------------------------------------------------
		*/

		drawer.classList.add('is-open')

		drawer.setAttribute('aria-hidden', 'false')

		document.body.classList.add('product-quick-view-open')

		body.innerHTML = ''

		if (loader) {
			loader.hidden = false
		}

		/*
		|--------------------------------------------------------------------------
		| Request
		|--------------------------------------------------------------------------
		*/

		try {
			const lang = getCurrentLanguage()

			const requestBody = new URLSearchParams({
				product_id: String(productId),

				lang: lang
			})

			const response = await fetch('/product/quick-view', {
				method: 'POST',

				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
				},

				body: requestBody,

				signal: controller.signal
			})

			const result = await response.json()

			if (!response.ok || !result || result.status !== 'ok') {
				throw new Error(result?.code || 'QUICK_VIEW_FAILED')
			}

			/*
			| Пользователь успел открыть
			| уже другой продукт.
			*/
			if (currentProductId !== productId) {
				return
			}

			body.innerHTML = result.html

			initProduct(body.querySelector('[data-quick-view-product]'))
		} catch (error) {
			if (error.name === 'AbortError') {
				return
			}

			console.error('Quick View:', error)

			body.innerHTML =
				'<div class="product-quick-view__error">' +
				(getCurrentLanguage() === 'ro'
					? 'Produsul nu a putut fi încărcat.'
					: 'Не удалось загрузить товар.') +
				'</div>'
		} finally {
			if (loader) {
				loader.hidden = true
			}
		}
	}

	/* ======================================================================
	   CLOSE
	   ====================================================================== */

	function close() {
		if (!drawer.classList.contains('is-open')) {
			return
		}

		if (controller) {
			controller.abort()

			controller = null
		}

		currentProductId = null

		drawer.classList.remove('is-open')

		drawer.setAttribute('aria-hidden', 'true')

		document.body.classList.remove('product-quick-view-open')

		window.setTimeout(function () {
			if (!drawer.classList.contains('is-open')) {
				body.innerHTML = ''
			}
		}, 400)

		previousActiveElement?.focus?.()
	}

	/* ======================================================================
	   PRODUCT
	   ====================================================================== */

	function initProduct(root) {
		if (!root) {
			return
		}

		const dataElement = root.querySelector('[data-quick-view-data]')

		if (!dataElement) {
			return
		}

		let data

		try {
			data = JSON.parse(dataElement.textContent)
		} catch (error) {
			console.error('Quick View data:', error)

			return
		}

		const state = {
			selectedVariationId: Number(data.initial.variationId) || 0,

			images: Array.isArray(data.initial.images) ? data.initial.images : [],

			imageIndex: 0,

			isSubmitting: false
		}

		initGallery()
		initColorSelect()
		initVariations()
		initAddToCart()

		/* ==================================================================
		   GALLERY
		   ================================================================== */

		function initGallery() {
			const stage = root.querySelector('[data-quick-view-stage]')

			root
				.querySelector('[data-quick-view-prev]')
				?.addEventListener('click', function () {
					showImage(state.imageIndex - 1)
				})

			root
				.querySelector('[data-quick-view-next]')
				?.addEventListener('click', function () {
					showImage(state.imageIndex + 1)
				})

			root.addEventListener('click', function (event) {
				const thumb = event.target.closest('[data-quick-view-thumb]')

				if (!thumb) {
					return
				}

				showImage(Number(thumb.dataset.imageIndex))
			})

			if (stage) {
				initSwipe(stage)
			}

			renderGallery()
		}

		function initSwipe(stage) {
			let startX = null
			let startY = null

			stage.addEventListener('pointerdown', function (event) {
				startX = event.clientX

				startY = event.clientY
			})

			stage.addEventListener('pointerup', function (event) {
				if (startX === null || startY === null) {
					return
				}

				const distanceX = event.clientX - startX

				const distanceY = event.clientY - startY

				if (
					Math.abs(distanceX) >= 45 &&
					Math.abs(distanceX) > Math.abs(distanceY)
				) {
					showImage(distanceX < 0 ? state.imageIndex + 1 : state.imageIndex - 1)
				}

				startX = null
				startY = null
			})

			stage.addEventListener('pointercancel', function () {
				startX = null
				startY = null
			})
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

			updateGallery()
		}

		function renderGallery() {
			const thumbs = root.querySelector('[data-quick-view-thumbs]')

			if (!thumbs) {
				return
			}

			thumbs.innerHTML = ''

			state.images.forEach(function (image, index) {
				const button = document.createElement('button')

				button.type = 'button'

				button.className = 'quick-view-gallery__thumb'

				if (index === state.imageIndex) {
					button.classList.add('is-active')
				}

				button.dataset.quickViewThumb = ''

				button.dataset.imageIndex = String(index)

				const img = document.createElement('img')

				img.src = image.src

				img.alt = ''

				img.loading = 'lazy'

				img.decoding = 'async'

				button.appendChild(img)

				thumbs.appendChild(button)
			})

			updateGallery()
		}

		function updateGallery() {
			const image = root.querySelector('[data-quick-view-image]')

			const current = root.querySelector('[data-quick-view-current]')

			const total = root.querySelector('[data-quick-view-total]')

			const prev = root.querySelector('[data-quick-view-prev]')

			const next = root.querySelector('[data-quick-view-next]')

			const selected = state.images[state.imageIndex]

			if (!image || !selected) {
				return
			}

			image.classList.add('is-changing')

			window.setTimeout(function () {
				image.src = selected.src

				image.alt = selected.alt || data.product.title

				image.classList.remove('is-changing')
			}, 100)

			root
				.querySelectorAll('[data-quick-view-thumb]')
				.forEach(function (thumb, index) {
					thumb.classList.toggle('is-active', index === state.imageIndex)
				})

			if (current) {
				current.textContent = String(state.imageIndex + 1)
			}

			if (total) {
				total.textContent = String(state.images.length)
			}

			const multiple = state.images.length > 1

			if (prev) {
				prev.disabled = !multiple
			}

			if (next) {
				next.disabled = !multiple
			}
		}

		function initColorSelect() {
			const select = root.querySelector('[data-quick-view-color-select]')

			if (!select) {
				return
			}

			const trigger = select.querySelector('[data-quick-view-color-trigger]')

			const dropdown = select.querySelector('[data-quick-view-color-dropdown]')

			if (!trigger || !dropdown) {
				return
			}

			trigger.addEventListener('click', function () {
				const isOpen = trigger.getAttribute('aria-expanded') === 'true'

				setOpen(!isOpen)
			})

			document.addEventListener('pointerdown', function (event) {
				if (!select.contains(event.target)) {
					setOpen(false)
				}
			})

			function setOpen(isOpen) {
				trigger.setAttribute('aria-expanded', String(isOpen))

				dropdown.hidden = !isOpen
			}
		}

		/* ==================================================================
		   VARIATIONS
		   ================================================================== */

		function initVariations() {
			root.addEventListener('click', function (event) {
				const option = event.target.closest('[data-quick-view-variation]')

				if (!option || option.disabled) {
					return
				}

				selectVariation(Number(option.dataset.variationId))
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

			state.images =
				Array.isArray(variation.images) && variation.images.length
					? variation.images
					: data.product.images

			root.dataset.selectedVariation = String(variationId)

			updateVariationControls(variation)

			updateProductInfo(variation)

			renderGallery()
		}

		function updateVariationControls(variation) {
			root
				.querySelectorAll('[data-quick-view-variation]')
				.forEach(function (button) {
					button.classList.toggle(
						'is-selected',
						Number(button.dataset.variationId) === Number(variation.id)
					)
				})

			/*
			|--------------------------------------------------------------------------
			| Custom select value
			|--------------------------------------------------------------------------
			*/

			const value = root.querySelector('[data-quick-view-color-value]')

			if (value) {
				value.textContent = formatVariationLabel(variation)
			}

			/*
			|--------------------------------------------------------------------------
			| Swatch
			|--------------------------------------------------------------------------
			*/

			const swatch = root.querySelector('[data-quick-view-color-swatch]')

			if (swatch) {
				const background = getVariationSwatch(variation)

				if (background) {
					swatch.hidden = false

					swatch.style.setProperty('--quick-view-swatch', background)
				} else {
					swatch.hidden = true

					swatch.style.removeProperty('--quick-view-swatch')
				}
			}

			/*
			|--------------------------------------------------------------------------
			| Close dropdown
			|--------------------------------------------------------------------------
			*/

			const trigger = root.querySelector('[data-quick-view-color-trigger]')

			const dropdown = root.querySelector('[data-quick-view-color-dropdown]')

			if (trigger && dropdown) {
				trigger.setAttribute('aria-expanded', 'false')

				dropdown.hidden = true
			}
		}
		function getVariationSwatch(variation) {
			if (!variation || !Array.isArray(variation.colors)) {
				return null
			}

			const colors = variation.colors
				.map(function (color) {
					return String(color || '')
						.trim()
						.toUpperCase()
				})
				.filter(function (color) {
					return /^#[0-9A-F]{6}$/.test(color)
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
		function updateProductInfo(variation) {
			const currentPrice = root.querySelector('[data-quick-view-price-current]')

			const oldPrice = root.querySelector('[data-quick-view-price-old]')

			const sku = root.querySelector('[data-quick-view-sku]')

			const bonus = root.querySelector('[data-quick-view-bonus]')

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

			updateBonus(bonus, basePrice, discountPrice)
		}

		function updateBonus(element, basePrice, discountPrice) {
			if (!element || data.isB2B) {
				return
			}

			if (discountPrice) {
				element.textContent =
					data.language === 'ro'
						? 'Pentru produsele cu reducere nu se acordă bonusuri'
						: 'На товары со скидкой бонусы не начисляются'

				return
			}

			const percent = Number(data.bonusPercent) || 0

			if (percent <= 0) {
				return
			}

			const amount = Number(basePrice) * (percent / 100)

			element.textContent =
				'+' +
				formatNumber(amount, 1) +
				' ' +
				(data.language === 'ro' ? 'bonusuri' : 'бонусов')
		}

		/* ==================================================================
		   CART
		   ================================================================== */

		function initAddToCart() {
			root
				.querySelector('[data-quick-view-add]')
				?.addEventListener('click', addToCart)
		}

		function addToCart() {
			if (state.isSubmitting) {
				return
			}

			const button = root.querySelector('[data-quick-view-add]')

			if (!button || button.disabled) {
				return
			}

			state.isSubmitting = true

			button.disabled = true

			button.classList.add('is-loading')

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
						showCartError(response)

						return
					}

					$('.actions-main-header__counts')
						.html(response.total_items)
						.toggleClass('d_none', Number(response.total_items) <= 0)

					close()

					window.setTimeout(function () {
						if (
							window.VNCartDrawer &&
							typeof window.VNCartDrawer.open === 'function'
						) {
							window.VNCartDrawer.open({
								load: true,

								addedRowId: response.row_id || null
							})
						}
					}, 300)
				},

				error: function (xhr) {
					showCartError(xhr.responseJSON)
				},

				complete: function () {
					state.isSubmitting = false

					if (button) {
						button.disabled = false

						button.classList.remove('is-loading')
					}
				}
			})
		}

		function showCartError(response) {
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

		/* ==================================================================
		   HELPERS
		   ================================================================== */

		function formatVariationLabel(variation) {
			const main = variation.color || variation.title || ''

			const volume = variation.volume || ''

			if (main && volume) {
				if (main.toLowerCase().includes(volume.toLowerCase())) {
					return main
				}

				return main + ' · ' + volume
			}

			return main || volume || ''
		}

		function formatPrice(value) {
			return (
				new Intl.NumberFormat(data.language === 'ro' ? 'ro-MD' : 'ru-MD', {
					maximumFractionDigits: 0
				}).format(Number(value) || 0) +
				' ' +
				data.currency
			)
		}

		function formatNumber(value, digits) {
			return Number(value).toFixed(digits).replace(/\.0+$/, '')
		}
	}

	/* ======================================================================
	   HELPERS
	   ====================================================================== */

	function getCurrentLanguage() {
		const htmlLang = document.documentElement.lang?.toLowerCase()

		if (htmlLang && htmlLang.startsWith('ro')) {
			return 'ro'
		}

		const firstSegment = window.location.pathname.split('/').filter(Boolean)[0]

		return firstSegment === 'ro' ? 'ro' : 'ru'
	}

	/* ======================================================================
	   PUBLIC API
	   ====================================================================== */

	window.VNQuickView = {
		open: function (productId) {
			open(Number(productId), null)
		},

		close: close
	}
})()
