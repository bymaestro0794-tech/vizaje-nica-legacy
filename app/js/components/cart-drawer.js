;(function (window, document, $) {
	'use strict'

	const drawer = document.querySelector('[data-cart-drawer]')

	if (!drawer || !$) {
		return
	}

	const panel = drawer.querySelector('.cart-drawer__panel')
	const body = drawer.querySelector('[data-cart-drawer-body]')
	const count = drawer.querySelector('[data-cart-drawer-count]')

	let isOpen = false
	let isLoading = false
	let lastFocusedElement = null
	let viewCartSentForOpen = false

	let checkoutObserver = null
	let activeActionRow = null

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	*/

	function initStickyCheckout() {
		const sticky = drawer.querySelector('[data-cart-drawer-sticky]')

		const normalCheckout = drawer.querySelector(
			'.cart-drawer-summary__checkout'
		)

		const total = drawer.querySelector('[data-cart-summary-total]')

		const productDiscount = drawer.querySelector('[data-cart-summary-discount]')

		const promoDiscount = drawer.querySelector('[data-cart-summary-promo]')

		if (!sticky) {
			return
		}

		if (checkoutObserver) {
			checkoutObserver.disconnect()
			checkoutObserver = null
		}

		if (!normalCheckout) {
			sticky.hidden = true
			drawer.classList.remove('has-sticky')

			return
		}
		/*
		|--------------------------------------------------------------------------
		| Total
		|--------------------------------------------------------------------------
		*/

		const stickyTotal = sticky.querySelector('[data-cart-sticky-total]')

		if (stickyTotal && total) {
			stickyTotal.textContent = total.textContent.trim()
		}

		/*
		|--------------------------------------------------------------------------
		| Discount
		|--------------------------------------------------------------------------
		*/

		const stickyDiscountWrap = sticky.querySelector(
			'[data-cart-sticky-discount-wrap]'
		)

		const stickyDiscount = sticky.querySelector('[data-cart-sticky-discount]')

		if (stickyDiscountWrap && stickyDiscount) {
			const productDiscountValue = productDiscount
				? Math.abs(parseMoney(productDiscount.textContent))
				: 0

			const promoDiscountValue = promoDiscount
				? Math.abs(parseMoney(promoDiscount.textContent))
				: 0

			const discountTotal = productDiscountValue + promoDiscountValue

			if (discountTotal > 0) {
				stickyDiscount.textContent = '−' + formatMoney(discountTotal)

				stickyDiscountWrap.hidden = false
			} else {
				stickyDiscount.textContent = ''

				stickyDiscountWrap.hidden = true
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Visibility
		|--------------------------------------------------------------------------
		*/

		function setStickyVisibility(normalCheckoutVisible) {
			const shouldShow = !normalCheckoutVisible

			const currentlyVisible = !sticky.hidden

			if (shouldShow === currentlyVisible) {
				return
			}

			drawer.classList.toggle('has-sticky', shouldShow)

			if (!window.gsap || prefersReducedMotion()) {
				sticky.hidden = !shouldShow

				return
			}

			window.gsap.killTweensOf(sticky)

			if (shouldShow) {
				sticky.hidden = false

				window.gsap.fromTo(
					sticky,
					{
						yPercent: 100,
						opacity: 0
					},
					{
						yPercent: 0,
						opacity: 1,
						duration: 0.32,
						ease: 'power3.out'
					}
				)

				return
			}

			window.gsap.to(sticky, {
				yPercent: 100,
				opacity: 0,
				duration: 0.24,
				ease: 'power2.in',

				onComplete: function () {
					sticky.hidden = true

					window.gsap.set(sticky, {
						yPercent: 0,
						opacity: 1
					})
				}
			})
		}

		function syncInitialState() {
			const rootRect = body.getBoundingClientRect()

			const checkoutRect = normalCheckout.getBoundingClientRect()

			const isVisible =
				checkoutRect.bottom > rootRect.top && checkoutRect.top < rootRect.bottom

			setStickyVisibility(isVisible)
		}

		checkoutObserver = new IntersectionObserver(
			function (entries) {
				const entry = entries[0]

				setStickyVisibility(entry.isIntersecting)
			},
			{
				root: body,
				threshold: 0.15
			}
		)

		checkoutObserver.observe(normalCheckout)

		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(syncInitialState)
		})
	}

	function transitionToEmpty() {
		if (!body) {
			return
		}

		if (checkoutObserver) {
			checkoutObserver.disconnect()
			checkoutObserver = null
		}

		const currentContent = body.querySelector('.cart-drawer-content')

		const sticky = drawer.querySelector('[data-cart-drawer-sticky]')

		if (sticky) {
			if (window.gsap && !prefersReducedMotion()) {
				window.gsap.to(sticky, {
					yPercent: 100,
					opacity: 0,
					duration: 0.22,
					ease: 'power2.in',
					onComplete: function () {
						sticky.hidden = true

						drawer.classList.remove('has-sticky')
					}
				})
			} else {
				sticky.hidden = true

				drawer.classList.remove('has-sticky')
			}
		}

		function fetchEmptyState() {
			$.ajax({
				url: '/cart/drawer',
				type: 'GET',
				dataType: 'json',

				data: {
					lang: getLanguage()
				},

				success: function (response) {
					if (!response || response.status !== 'ok') {
						return
					}

					body.innerHTML = response.html || ''

					const sticky = drawer.querySelector('[data-cart-drawer-sticky]')

					if (sticky) {
						sticky.hidden = true

						window.gsap?.set(sticky, {
							yPercent: 0,
							opacity: 1
						})
					}

					drawer.classList.remove('has-sticky')

					const empty = body.querySelector('[data-cart-empty]')

					if (empty && window.gsap && !prefersReducedMotion()) {
						window.gsap.fromTo(
							empty,
							{
								opacity: 0,
								y: 10,
								scale: 0.985
							},
							{
								opacity: 1,
								y: 0,
								scale: 1,
								duration: 0.38,
								ease: 'power3.out'
							}
						)
					}
				}
			})
		}

		if (currentContent && window.gsap && !prefersReducedMotion()) {
			window.gsap.to(currentContent, {
				opacity: 0,
				y: -8,
				duration: 0.22,
				ease: 'power1.in',
				onComplete: fetchEmptyState
			})

			return
		}

		fetchEmptyState()
	}

	function transitionToCheckoutGate(checkoutUrl) {
		if (!body) {
			return
		}

		const template = drawer.querySelector('[data-cart-checkout-gate-template]')

		if (!(template instanceof HTMLTemplateElement)) {
			if (checkoutUrl) {
				window.location.href = checkoutUrl
			}

			return
		}

		if (checkoutObserver) {
			checkoutObserver.disconnect()
			checkoutObserver = null
		}

		const sticky = drawer.querySelector('[data-cart-drawer-sticky]')

		if (sticky) {
			sticky.hidden = true
			drawer.classList.remove('has-sticky')
		}

		const currentContent = body.querySelector('.cart-drawer-content')

		function renderGate() {
			const fragment = template.content.cloneNode(true)

			body.innerHTML = ''

			body.appendChild(fragment)

			body.scrollTop = 0

			const gate = body.querySelector('[data-cart-checkout-gate]')

			if (!gate) {
				return
			}

			const guestButton = gate.querySelector('[data-cart-checkout-guest]')

			if (guestButton && checkoutUrl) {
				guestButton.setAttribute('href', checkoutUrl)
			}

			if (window.gsap && !prefersReducedMotion()) {
				window.gsap.fromTo(
					gate,
					{
						opacity: 0,
						y: 10
					},
					{
						opacity: 1,
						y: 0,
						duration: 0.38,
						ease: 'power3.out'
					}
				)
			}
		}

		if (currentContent && window.gsap && !prefersReducedMotion()) {
			window.gsap.to(currentContent, {
				opacity: 0,
				y: -8,
				duration: 0.2,
				ease: 'power1.in',

				onComplete: renderGate
			})

			return
		}

		renderGate()
	}

	function updateSummaryCount(totalItems) {
		const summaryCount = drawer.querySelector('[data-cart-summary-count]')

		if (!summaryCount) {
			return
		}

		summaryCount.textContent = Math.max(0, Number(totalItems) || 0)
	}

	function openActionSheet(row) {
		const sheet = drawer.querySelector('[data-cart-action-sheet]')

		if (!sheet || !row) {
			return
		}

		const quantityElement = row.querySelector('[data-cart-drawer-qty]')

		if (!quantityElement) {
			return
		}

		activeActionRow = row

		const value = sheet.querySelector('[data-cart-sheet-value]')

		if (value) {
			value.textContent = quantityElement.textContent.trim()
		}

		sheet.classList.add('is-open')
		sheet.setAttribute('aria-hidden', 'false')

		if (window.gsap) {
			const panel = sheet.querySelector('.cart-drawer-action-sheet__panel')

			window.gsap.fromTo(
				panel,
				{
					yPercent: 100
				},
				{
					yPercent: 0,
					duration: 0.35,
					ease: 'power3.out'
				}
			)
		}
	}

	function closeActionSheet() {
		const sheet = drawer.querySelector('[data-cart-action-sheet]')

		if (!sheet) {
			return
		}

		function finish() {
			sheet.classList.remove('is-open')
			sheet.setAttribute('aria-hidden', 'true')

			activeActionRow = null
		}

		if (window.gsap) {
			const panel = sheet.querySelector('.cart-drawer-action-sheet__panel')

			window.gsap.to(panel, {
				yPercent: 100,
				duration: 0.25,
				ease: 'power2.inOut',
				onComplete: finish
			})

			return
		}

		finish()
	}

	function getLanguage() {
		const language = String(drawer.dataset.lang || '')
			.trim()
			.toLowerCase()

		return language === 'ro' ? 'ro' : 'ru'
	}

	function updateHeaderCartCount(totalItems) {
		const value = Math.max(0, Number(totalItems) || 0)

		document.querySelectorAll('[data-cart-count]').forEach(function (element) {
			element.textContent = value
			element.hidden = value <= 0
		})
	}

	function updateDrawerCount(totalItems) {
		if (!count) {
			return
		}

		const value = Math.max(0, Number(totalItems) || 0)

		if (value <= 0) {
			count.textContent = ''
			return
		}

		count.textContent =
			getLanguage() === 'ro' ? `/ ${value} buc.` : `/ ${value} шт.`
	}

	function showError(message) {
		if (window.VNNotify && typeof window.VNNotify.toast === 'function') {
			window.VNNotify.toast({
				type: 'error',
				title:
					getLanguage() === 'ro' ? 'A apărut o eroare' : 'Произошла ошибка',
				message:
					message ||
					(getLanguage() === 'ro'
						? 'Încercați din nou.'
						: 'Попробуйте ещё раз.'),
				duration: 5500
			})

			return
		}

		console.error(message)
	}

	function prefersReducedMotion() {
		return window.matchMedia('(prefers-reduced-motion: reduce)').matches
	}

	function parseMoney(value) {
		if (typeof value === 'number') {
			return value
		}

		return Number(String(value || '').replace(/[^\d.-]/g, '')) || 0
	}

	function formatMoney(value) {
		return (
			Math.round(Number(value) || 0)
				.toLocaleString('ru-RU')
				.replace(/\u00a0/g, ' ') + ' MDL'
		)
	}

	function animateMoney(element, nextValue, options) {
		if (!element) {
			return
		}

		const config = Object.assign(
			{
				duration: 0.34,
				prefix: ''
			},
			options || {}
		)

		const previousValue = parseMoney(element.textContent)

		const targetValue = Number(nextValue) || 0

		if (
			!window.gsap ||
			prefersReducedMotion() ||
			previousValue === targetValue
		) {
			element.textContent = config.prefix + formatMoney(targetValue)

			return
		}

		const state = {
			value: previousValue
		}

		window.gsap.killTweensOf(state)

		window.gsap.to(state, {
			value: targetValue,
			duration: config.duration,
			ease: 'power2.out',

			onUpdate: function () {
				element.textContent = config.prefix + formatMoney(state.value)
			}
		})
	}

	function animateQuantity(element, nextValue, direction) {
		if (!element) {
			return
		}

		const value = Number(nextValue) || 1

		if (!window.gsap || prefersReducedMotion()) {
			element.textContent = value
			return
		}

		const distance = direction > 0 ? -7 : 7

		window.gsap.killTweensOf(element)

		window.gsap.to(element, {
			y: distance,
			opacity: 0,
			duration: 0.11,
			ease: 'power1.in',

			onComplete: function () {
				element.textContent = value

				window.gsap.fromTo(
					element,
					{
						y: -distance,
						opacity: 0
					},
					{
						y: 0,
						opacity: 1,
						duration: 0.16,
						ease: 'power2.out'
					}
				)
			}
		})
	}

	function animateCartCount(totalItems) {
		const value = Math.max(0, Number(totalItems) || 0)

		updateHeaderCartCount(value)
		updateDrawerCount(value)

		if (!window.gsap || prefersReducedMotion()) {
			return
		}

		document.querySelectorAll('[data-cart-count]').forEach(function (element) {
			window.gsap.fromTo(
				element,
				{
					scale: 1
				},
				{
					scale: 1.16,
					duration: 0.12,
					ease: 'power2.out',
					yoyo: true,
					repeat: 1
				}
			)
		})
	}

	function updateRowState(row, rowState, direction) {
		if (!row || !rowState) {
			return
		}

		const quantityElement = row.querySelector('[data-cart-drawer-qty]')

		const currentPrice = row.querySelector('.cart-drawer-item__current-price')

		const oldPrice = row.querySelector('.cart-drawer-item__old-price')

		const discount = row.querySelector('.cart-drawer-item__discount')

		const minusButton = row.querySelector('[data-cart-drawer-minus]')

		const plusButton = row.querySelector('[data-cart-drawer-plus]')

		if (quantityElement) {
			animateQuantity(quantityElement, rowState.quantity, direction)

			quantityElement.dataset.max = rowState.max_quantity
		}

		if (currentPrice) {
			animateMoney(currentPrice, rowState.current_total)
		}

		if (oldPrice) {
			animateMoney(oldPrice, rowState.original_total)
		}

		if (discount) {
			const label = getLanguage() === 'ro' ? 'Reducere ' : 'Скидка '

			const amount = Number(rowState.discount) || 0

			if (amount > 0) {
				discount.hidden = false

				discount.textContent = label + formatMoney(amount)
			} else {
				discount.hidden = true
			}
		}

		if (minusButton) {
			minusButton.disabled = rowState.quantity <= 1
		}

		if (plusButton) {
			plusButton.disabled = rowState.quantity >= rowState.max_quantity
		}
	}

	function updateCartTotals(cartState, promoState) {
		if (!cartState) {
			return
		}

		const products = drawer.querySelector('[data-cart-summary-products]')

		const productDiscountWrap = drawer.querySelector(
			'[data-cart-summary-discount-wrap]'
		)

		const productDiscount = drawer.querySelector('[data-cart-summary-discount]')

		const promoWrap = drawer.querySelector('[data-cart-summary-promo-wrap]')

		const promoDiscount = drawer.querySelector('[data-cart-summary-promo]')

		const promoCode = drawer.querySelector('[data-cart-summary-promo-code]')

		const delivery = drawer.querySelector('[data-cart-summary-delivery]')

		const total = drawer.querySelector('[data-cart-summary-total]')

		const stickyDiscount = drawer.querySelector('[data-cart-sticky-discount]')

		const stickyDiscountWrap = drawer.querySelector(
			'[data-cart-sticky-discount-wrap]'
		)

		const stickyTotal = drawer.querySelector('[data-cart-sticky-total]')

		/*
		|--------------------------------------------------------------------------
		| Products
		|--------------------------------------------------------------------------
		*/

		animateMoney(products, cartState.products_total)

		/*
		|--------------------------------------------------------------------------
		| Product discount
		|--------------------------------------------------------------------------
		*/

		if (productDiscountWrap && productDiscount) {
			if (Number(cartState.product_discount_total) > 0) {
				productDiscountWrap.hidden = false

				animateMoney(productDiscount, cartState.product_discount_total, {
					prefix: '−'
				})
			} else {
				productDiscountWrap.hidden = true
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Promo discount
		|--------------------------------------------------------------------------
		*/

		if (promoWrap && promoDiscount) {
			if (Number(cartState.promo_discount_total) > 0) {
				promoWrap.hidden = false

				animateMoney(promoDiscount, cartState.promo_discount_total, {
					prefix: '−'
				})

				if (promoCode && promoState && promoState.code) {
					promoCode.textContent = promoState.code
				}
			} else {
				promoWrap.hidden = true
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Delivery
		|--------------------------------------------------------------------------
		*/

		if (delivery) {
			const deliveryPrice = Number(cartState.delivery_price) || 0

			delivery.textContent =
				deliveryPrice > 0
					? formatMoney(deliveryPrice)
					: getLanguage() === 'ro'
						? 'Gratuit'
						: 'Бесплатно'
		}

		/*
		|--------------------------------------------------------------------------
		| Sticky aggregate discount
		|--------------------------------------------------------------------------
		*/

		if (stickyDiscountWrap && stickyDiscount) {
			if (Number(cartState.discount_total) > 0) {
				stickyDiscountWrap.hidden = false

				animateMoney(stickyDiscount, cartState.discount_total, {
					prefix: '−'
				})
			} else {
				stickyDiscountWrap.hidden = true
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Total
		|--------------------------------------------------------------------------
		*/

		animateMoney(total, cartState.total)

		animateMoney(stickyTotal, cartState.total)
	}

	function updatePromoState(promoState, cartState) {
		const promoSection = drawer.querySelector('[data-cart-promo]')

		if (!promoSection || !promoState || !cartState) {
			return
		}

		const appliedBlock = promoSection.querySelector('[data-cart-promo-applied]')

		const currentlyApplied = Boolean(appliedBlock)
		const shouldBeApplied = Boolean(promoState.applied)

		/*
	|--------------------------------------------------------------------------
	| Promo state structurally changed
	|--------------------------------------------------------------------------
	|
	| Например:
	| applied -> minimum amount no longer satisfied
	| invalid -> applied
	|
	| View имеет разные markup states, поэтому безопаснее перерендерить drawer.
	|
	*/

		if (currentlyApplied !== shouldBeApplied) {
			load({
				skeleton: false
			})

			return
		}

		if (!shouldBeApplied) {
			return
		}

		const code = promoSection.querySelector('[data-cart-promo-code]')

		const discount = promoSection.querySelector('[data-cart-promo-discount]')

		if (code && promoState.code) {
			code.textContent = promoState.code
		}

		if (discount) {
			animateMoney(discount, cartState.promo_discount_total, {
				prefix: '−'
			})
		}
	}

	function updateShippingState(shippingState) {
		if (!shippingState) {
			return
		}

		const shipping = drawer.querySelector('[data-cart-shipping]')

		if (!shipping) {
			return
		}

		const progressState = shipping.querySelector(
			'[data-cart-shipping-progress-state]'
		)

		const successState = shipping.querySelector(
			'[data-cart-shipping-success-state]'
		)

		const progress = shipping.querySelector('[data-cart-shipping-progress]')

		const threshold = shipping.querySelector('[data-cart-shipping-threshold]')

		const nextProgress = Math.max(
			0,
			Math.min(100, Number(shippingState.progress) || 0)
		)

		const isFree = Boolean(shippingState.is_free)

		if (threshold) {
			threshold.textContent = formatMoney(shippingState.threshold)
		}

		/*
	|--------------------------------------------------------------------------
	| Reduced motion / no GSAP
	|--------------------------------------------------------------------------
	*/

		if (!window.gsap || prefersReducedMotion()) {
			if (progress) {
				progress.style.width = nextProgress + '%'
			}

			if (progressState) {
				progressState.hidden = isFree
			}

			if (successState) {
				successState.hidden = !isFree
			}

			return
		}

		/*
	|--------------------------------------------------------------------------
	| Become free
	|--------------------------------------------------------------------------
	*/

		if (isFree) {
			if (progressState && !progressState.hidden) {
				if (progress) {
					window.gsap.to(progress, {
						width: '100%',
						duration: 0.38,
						ease: 'power2.out'
					})
				}

				window.gsap.to(progressState, {
					opacity: 0,
					y: -4,
					duration: 0.2,
					delay: 0.22,
					ease: 'power1.in',

					onComplete: function () {
						progressState.hidden = true

						window.gsap.set(progressState, {
							opacity: 1,
							y: 0
						})

						if (!successState) {
							return
						}

						successState.hidden = false

						window.gsap.fromTo(
							successState,
							{
								opacity: 0,
								y: 6,
								scale: 0.97
							},
							{
								opacity: 1,
								y: 0,
								scale: 1,
								duration: 0.3,
								ease: 'power3.out'
							}
						)
					}
				})

				return
			}

			if (successState) {
				successState.hidden = false
			}

			return
		}

		/*
	|--------------------------------------------------------------------------
	| Become paid again
	|--------------------------------------------------------------------------
	*/

		if (successState && !successState.hidden) {
			window.gsap.to(successState, {
				opacity: 0,
				y: 4,
				scale: 0.97,
				duration: 0.18,
				ease: 'power1.in',

				onComplete: function () {
					successState.hidden = true

					window.gsap.set(successState, {
						opacity: 1,
						y: 0,
						scale: 1
					})

					if (!progressState) {
						return
					}

					progressState.hidden = false

					if (progress) {
						window.gsap.set(progress, {
							width: '100%'
						})
					}

					window.gsap.fromTo(
						progressState,
						{
							opacity: 0,
							y: -5
						},
						{
							opacity: 1,
							y: 0,
							duration: 0.24,
							ease: 'power2.out'
						}
					)

					if (progress) {
						window.gsap.to(progress, {
							width: nextProgress + '%',
							duration: 0.42,
							ease: 'power2.out'
						})
					}
				}
			})

			return
		}

		if (progressState) {
			progressState.hidden = false
		}

		if (progress) {
			window.gsap.to(progress, {
				width: nextProgress + '%',
				duration: 0.38,
				ease: 'power2.out'
			})
		}
	}

	function setLoadingState() {
		if (!body) {
			return
		}

		body.innerHTML = `
			<div class="cart-drawer__loading">
				<div class="cart-drawer-skeleton">
					<div class="cart-drawer-skeleton__image"></div>

					<div class="cart-drawer-skeleton__content">
						<span></span>
						<span></span>
						<span></span>
					</div>
				</div>

				<div class="cart-drawer-skeleton">
					<div class="cart-drawer-skeleton__image"></div>

					<div class="cart-drawer-skeleton__content">
						<span></span>
						<span></span>
						<span></span>
					</div>
				</div>
			</div>
		`
	}

	/*
|--------------------------------------------------------------------------
| GA4 Ecommerce
|--------------------------------------------------------------------------
*/

	function getAnalyticsItemFromRow(row) {
		if (!row) {
			return null
		}

		const quantityElement = row.querySelector('[data-cart-drawer-qty]')

		const item = {
			item_id: String(row.dataset.ga4ItemId || ''),

			item_name: String(row.dataset.ga4ItemName || ''),

			item_brand: String(row.dataset.ga4ItemBrand || ''),

			item_category: String(row.dataset.ga4ItemCategory || ''),

			price: Number(row.dataset.ga4ItemPrice || 0),

			quantity: Math.max(1, Number(quantityElement?.textContent || 1))
		}

		const variant = String(row.dataset.ga4ItemVariant || '').trim()

		if (variant) {
			item.item_variant = variant
		}

		return item
	}

	function getAnalyticsCartItems() {
		return Array.from(drawer.querySelectorAll('[data-cart-drawer-row]'))
			.map(getAnalyticsItemFromRow)
			.filter(Boolean)
	}

	function getAnalyticsCartValue(items) {
		return items.reduce(function (total, item) {
			return total + Number(item.price || 0) * Number(item.quantity || 1)
		}, 0)
	}

	function sendViewCart() {
		if (
			viewCartSentForOpen ||
			!window.VNAnalytics ||
			typeof window.VNAnalytics.push !== 'function'
		) {
			return
		}

		const items = getAnalyticsCartItems()

		if (!items.length) {
			return
		}

		viewCartSentForOpen = true

		window.VNAnalytics.push('view_cart', {
			currency: 'MDL',

			value: getAnalyticsCartValue(items),

			items: items
		})
	}

	function sendRemoveFromCart(item) {
		if (
			!item ||
			!window.VNAnalytics ||
			typeof window.VNAnalytics.push !== 'function'
		) {
			return
		}

		window.VNAnalytics.push('remove_from_cart', {
			currency: 'MDL',

			value: Number(item.price || 0) * Number(item.quantity || 1),

			items: [item]
		})
	}

	/*
	|--------------------------------------------------------------------------
	| Open / Close
	|--------------------------------------------------------------------------
	*/

	function open(options) {
		const config = Object.assign(
			{
				load: true
			},
			options || {}
		)

		if (isOpen) {
			if (config.load) {
				load()
			}

			return
		}

		isOpen = true

		lastFocusedElement =
			document.activeElement instanceof HTMLElement
				? document.activeElement
				: null

		drawer.classList.add('is-open')
		drawer.setAttribute('aria-hidden', 'false')

		document.body.classList.add('cart-drawer-lock')

		if (window.gsap && panel) {
			window.gsap.killTweensOf(panel)

			const mobile = window.matchMedia('(max-width: 767px)').matches

			window.gsap.fromTo(
				panel,
				mobile
					? {
							yPercent: 100
						}
					: {
							xPercent: 100
						},
				{
					xPercent: 0,
					yPercent: 0,
					duration: 0.42,
					ease: 'power3.out'
				}
			)
		}

		if (config.load) {
			load()
		}
	}

	function close() {
		if (!isOpen) {
			return
		}

		function finish() {
			isOpen = false
			viewCartSentForOpen = false

			drawer.classList.remove('is-open')

			drawer.setAttribute('aria-hidden', 'true')

			document.body.classList.remove('cart-drawer-lock')

			if (lastFocusedElement) {
				lastFocusedElement.focus()
			}
		}

		if (window.gsap && panel) {
			window.gsap.killTweensOf(panel)

			const mobile = window.matchMedia('(max-width: 767px)').matches

			window.gsap.to(panel, {
				xPercent: mobile ? 0 : 100,

				yPercent: mobile ? 100 : 0,

				duration: 0.3,
				ease: 'power2.inOut',
				onComplete: finish
			})

			return
		}

		finish()
	}

	/*
	|--------------------------------------------------------------------------
	| Load
	|--------------------------------------------------------------------------
	*/

	function load(options) {
		const config = Object.assign(
			{
				skeleton: true
			},
			options || {}
		)

		if (isLoading) {
			return
		}

		isLoading = true

		if (config.skeleton) {
			setLoadingState()
		}

		$.ajax({
			url: '/cart/drawer',
			type: 'GET',
			dataType: 'json',

			data: {
				lang: getLanguage()
			},

			success: function (response) {
				if (!response || response.status !== 'ok') {
					showError(
						getLanguage() === 'ro'
							? 'Coșul nu a putut fi încărcat.'
							: 'Не удалось загрузить корзину.'
					)

					return
				}

				if (body) {
					body.innerHTML = response.html || ''
				}

				updateHeaderCartCount(response.total_items)
				updateDrawerCount(response.total_items)

				/*
				|--------------------------------------------------------------------------
				| GA4 — view_cart
				|--------------------------------------------------------------------------
				*/

				sendViewCart()

				/*
				|--------------------------------------------------------------------------
				| Sticky checkout init
				|--------------------------------------------------------------------------
				*/
				window.requestAnimationFrame(function () {
					initStickyCheckout()
				})

				if (window.gsap && body) {
					window.gsap.fromTo(
						body.children,
						{
							opacity: 0,
							y: 6
						},
						{
							opacity: 1,
							y: 0,
							duration: 0.25,
							stagger: 0.025,
							ease: 'power2.out'
						}
					)
				}
			},

			error: function (xhr) {
				console.error('Cart drawer load error:', xhr)

				showError(
					getLanguage() === 'ro'
						? 'Coșul nu a putut fi încărcat.'
						: 'Не удалось загрузить корзину.'
				)
			},

			complete: function () {
				isLoading = false
			}
		})
	}

	/*
	|--------------------------------------------------------------------------
	| Quantity
	|--------------------------------------------------------------------------
	*/

	function updateQuantity(row, quantityElement, nextQuantity) {
		const rowid = quantityElement.dataset.rowid

		const max = Math.max(1, Number(quantityElement.dataset.max) || 1)

		const previousQuantity = Math.max(
			1,
			Number(quantityElement.textContent) || 1
		)

		nextQuantity = Math.max(1, Math.min(nextQuantity, max))

		if (!rowid || nextQuantity === previousQuantity) {
			return
		}

		row.classList.add('is-updating')

		row
			.querySelectorAll('[data-cart-drawer-plus], [data-cart-drawer-minus]')
			.forEach(function (button) {
				button.disabled = true
			})

		$.ajax({
			url: '/cart/update_cart',
			type: 'POST',
			dataType: 'json',

			data: {
				rowid: rowid,
				quantity: nextQuantity,
				lang: getLanguage()
			},

			success: function (response) {
				if (!response || response.status !== 'ok') {
					showError(
						getLanguage() === 'ro'
							? 'Cantitatea nu a putut fi actualizată.'
							: 'Не удалось изменить количество.'
					)

					return
				}

				if (!response.row || !response.cart) {
					load({
						skeleton: false
					})

					return
				}

				const direction =
					Number(response.row.quantity) > previousQuantity ? 1 : -1

				updateRowState(row, response.row, direction)

				updateSummaryCount(response.cart.total_items)

				updateCartTotals(response.cart, response.promo)

				updatePromoState(response.promo, response.cart)

				updateShippingState(response.shipping)

				animateCartCount(response.cart.total_items)
			},

			error: function (xhr) {
				console.error('Cart quantity error:', xhr)

				quantityElement.textContent = previousQuantity

				showError(
					getLanguage() === 'ro'
						? 'Cantitatea nu a putut fi actualizată.'
						: 'Не удалось изменить количество.'
				)

				const minusButton = row.querySelector('[data-cart-drawer-minus]')

				const plusButton = row.querySelector('[data-cart-drawer-plus]')

				if (minusButton) {
					minusButton.disabled = previousQuantity <= 1
				}

				if (plusButton) {
					plusButton.disabled = previousQuantity >= max
				}
			},

			complete: function () {
				window.setTimeout(function () {
					row.classList.remove('is-updating')
				}, 180)
			}
		})
	}

	/*
	|--------------------------------------------------------------------------
	| Remove
	|--------------------------------------------------------------------------
	*/

	function removeItem(row) {
		const rowid = row.dataset.rowid

		if (!rowid) {
			return
		}

		const analyticsItem = getAnalyticsItemFromRow(row)

		function performRemove() {
			row.classList.add('is-updating')

			$.ajax({
				url: '/cart/delete',
				type: 'POST',
				dataType: 'json',

				data: {
					rowid: rowid,
					lang: getLanguage()
				},

				success: function (response) {
					if (!response || response.status !== 'ok') {
						showError(
							getLanguage() === 'ro'
								? 'Produsul nu a putut fi șters.'
								: 'Не удалось удалить товар.'
						)

						return
					}
					/*
					|--------------------------------------------------------------------------
					| GA4 — remove_from_cart
					|--------------------------------------------------------------------------
					*/

					sendRemoveFromCart(analyticsItem)
					if (!response.cart || !response.shipping) {
						load({
							skeleton: false
						})

						return
					}

					animateCartCount(response.cart.total_items)

					updateSummaryCount(response.cart.total_items)

					if (!response.empty) {
						updateCartTotals(response.cart, response.promo)

						updatePromoState(response.promo, response.cart)

						updateShippingState(response.shipping)
					}

					if (!window.gsap || prefersReducedMotion()) {
						row.remove()

						if (response.empty) {
							transitionToEmpty()
						}

						return
					}

					const rowHeight = row.offsetHeight

					window.gsap.set(row, {
						height: rowHeight,
						overflow: 'hidden'
					})

					window.gsap
						.timeline({
							onComplete: function () {
								row.remove()

								if (response.empty) {
									transitionToEmpty()
								}
							}
						})
						.to(row, {
							opacity: 0,
							x: 14,
							duration: 0.18,
							ease: 'power1.in'
						})
						.to(
							row,
							{
								height: 0,
								paddingTop: 0,
								paddingBottom: 0,
								marginTop: 0,
								marginBottom: 0,
								borderWidth: 0,
								duration: 0.28,
								ease: 'power2.inOut'
							},
							'-=0.04'
						)
				},

				error: function (xhr) {
					console.error('Cart remove error:', xhr)

					row.classList.remove('is-updating')

					showError(
						getLanguage() === 'ro'
							? 'Produsul nu a putut fi șters.'
							: 'Не удалось удалить товар.'
					)
				}
			})
		}

		performRemove()
	}
	
	function getPromoExcludedBrandsMessage(promoState) {
    	if (
    		!promoState ||
    		!Array.isArray(promoState.excluded_cart_brands) ||
    		!promoState.excluded_cart_brands.length
    	) {
    		return ''
    	}
    
    	const brands = promoState.excluded_cart_brands
    		.map(function (brand) {
    			return String(brand?.title || '').trim()
    		})
    		.filter(Boolean)
    
    	if (!brands.length) {
    		return ''
    	}
    
    	if (getLanguage() === 'ro') {
    		return brands.length === 1
    			? 'Codul promoțional nu se aplică brandului ' + brands[0] + '.'
    			: 'Codul promoțional nu se aplică brandurilor ' +
    					brands.join(', ') +
    					'.'
    	}
    
    	return brands.length === 1
    		? 'Промокод не действует на бренд ' + brands[0] + '.'
    		: 'Промокод не действует на бренды ' + brands.join(', ') + '.'
    }

	function getPromoErrorMessage(reason) {
		const messages =
			getLanguage() === 'ro'
				? {
						PROMO_NOT_AUTHENTICATED:
							'Autentificați-vă pentru a utiliza codul promoțional.',

						PROMO_NOT_FOUND: 'Codul promoțional nu a fost găsit.',

						PROMO_NOT_STARTED: 'Codul promoțional nu este încă activ.',

						PROMO_EXPIRED: 'Codul promoțional a expirat.',

						PROMO_MINIMUM_AMOUNT:
							'Valoarea produselor eligibile este insuficientă.',

						PROMO_NOT_APPLICABLE:
							'Codul promoțional nu se aplică produselor din coș.',

						PROMO_ALREADY_USED: 'Ați utilizat deja acest cod promoțional.',

						PROMO_USAGE_LIMIT_REACHED: 'Limita de utilizare a fost atinsă.'
					}
				: {
						PROMO_NOT_AUTHENTICATED:
							'Войдите в аккаунт, чтобы использовать промокод.',

						PROMO_NOT_FOUND: 'Промокод не найден.',

						PROMO_NOT_STARTED: 'Промокод ещё не начал действовать.',

						PROMO_EXPIRED: 'Срок действия промокода закончился.',

						PROMO_MINIMUM_AMOUNT: 'Недостаточная сумма подходящих товаров.',

						PROMO_NOT_APPLICABLE:
							'Промокод не применяется к товарам в корзине.',

						PROMO_ALREADY_USED: 'Вы уже использовали этот промокод.',

						PROMO_USAGE_LIMIT_REACHED: 'Лимит использований промокода исчерпан.'
					}

		return (
			messages[reason] ||
			(getLanguage() === 'ro'
				? 'Codul promoțional nu poate fi aplicat.'
				: 'Не удалось применить промокод.')
		)
	}

	function setPromoError(message) {
		const error = drawer.querySelector('[data-cart-promo-error]')

		if (!error) {
			return
		}

		if (!message) {
			error.textContent = ''
			error.hidden = true

			return
		}

		error.textContent = message
		error.hidden = false
	}

	function applyPromo(form) {
		const input = form.querySelector('[data-cart-promo-input]')

		const submit = form.querySelector('[data-cart-promo-submit]')

		if (!input) {
			return
		}

		const code = String(input.value || '')
			.trim()
			.toUpperCase()

		if (!code) {
			setPromoError(
				getLanguage() === 'ro'
					? 'Introduceți codul promoțional.'
					: 'Введите промокод.'
			)

			return
		}

		input.value = code

		setPromoError('')

		if (submit) {
			submit.disabled = true
			submit.classList.add('is-loading')
		}

		$.ajax({
			url: '/ajax/promo_apply',
			type: 'POST',
			dataType: 'json',

			data: {
				code: code,
				lang: getLanguage()
			},

			success: function (response) {
				if (!response || response.status !== 'ok') {
					return
				}

				updateCartTotals(response.cart, response.promo)

				updateShippingState(response.shipping)

				load({
					skeleton: false
				})
			},

			error: function (xhr) {
            	const response = xhr.responseJSON || {}
            
            	const excludedBrandsMessage =
            		getPromoExcludedBrandsMessage(response.promo)
            
            	setPromoError(
            		excludedBrandsMessage ||
            			getPromoErrorMessage(
            				response.code ||
            					response.promo?.reason
            			)
            	)
            },

			complete: function () {
				if (submit) {
					submit.disabled = false
					submit.classList.remove('is-loading')
				}
			}
		})
	}

	function removePromo(button) {
		button.disabled = true
		button.classList.add('is-loading')

		$.ajax({
			url: '/ajax/promo_remove',
			type: 'POST',
			dataType: 'json',

			data: {
				lang: getLanguage()
			},

			success: function (response) {
				if (!response || response.status !== 'ok') {
					return
				}

				updateCartTotals(response.cart, response.promo)

				updateShippingState(response.shipping)

				load({
					skeleton: false
				})
			},

			error: function (xhr) {
				console.error('Promo remove error:', xhr)

				showError(
					getLanguage() === 'ro'
						? 'Codul promoțional nu a putut fi eliminat.'
						: 'Не удалось удалить промокод.'
				)
			},

			complete: function () {
				button.disabled = false
				button.classList.remove('is-loading')
			}
		})
	}

	/*
	|--------------------------------------------------------------------------
	| Delegated events
	|--------------------------------------------------------------------------
	*/

	document.addEventListener('click', function (event) {
		const checkoutButton = event.target.closest('[data-cart-checkout]')

		if (checkoutButton && drawer.contains(checkoutButton)) {
			const authRequired = checkoutButton.dataset.authRequired === 'true'

			/*
			 * Авторизован.
			 *
			 * Обычный href -> checkout.
			 */
			if (!authRequired) {
				return
			}

			event.preventDefault()

			const checkoutUrl = checkoutButton.getAttribute('href')

			transitionToCheckoutGate(checkoutUrl)

			return
		}

		const checkoutAuthButton = event.target.closest('[data-cart-checkout-auth]')

		if (checkoutAuthButton && drawer.contains(checkoutAuthButton)) {
			event.preventDefault()

			if (
				!window.authDrawer ||
				typeof window.authDrawer.openForCheckout !== 'function'
			) {
				return
			}

			const guestButton = drawer.querySelector('[data-cart-checkout-guest]')

			const checkoutUrl = guestButton ? guestButton.getAttribute('href') : ''

			close()

			window.setTimeout(function () {
				window.authDrawer.openForCheckout(checkoutUrl)
			}, 320)

			return
		}

		const openButton = event.target.closest('[data-cart-drawer-open]')

		if (openButton) {
			event.preventDefault()

			open()
			return
		}

		const closeButton = event.target.closest('[data-cart-drawer-close]')

		if (closeButton && drawer.contains(closeButton)) {
			event.preventDefault()

			close()
			return
		}

		const plusButton = event.target.closest('[data-cart-drawer-plus]')

		if (plusButton) {
			const row = plusButton.closest('[data-cart-drawer-row]')

			const quantityElement = row?.querySelector('[data-cart-drawer-qty]')

			if (!row || !quantityElement) {
				return
			}

			const current = Number(quantityElement.textContent) || 1

			updateQuantity(row, quantityElement, current + 1)

			return
		}

		const minusButton = event.target.closest('[data-cart-drawer-minus]')

		if (minusButton) {
			const row = minusButton.closest('[data-cart-drawer-row]')

			const quantityElement = row?.querySelector('[data-cart-drawer-qty]')

			if (!row || !quantityElement) {
				return
			}

			const current = Number(quantityElement.textContent) || 1

			updateQuantity(row, quantityElement, current - 1)

			return
		}

		const removeButton = event.target.closest('[data-cart-drawer-remove]')

		if (removeButton) {
			const row = removeButton.closest('[data-cart-drawer-row]')

			if (row) {
				removeItem(row)
			}
		}

		const moreButton = event.target.closest('[data-cart-drawer-more]')

		if (moreButton) {
			const row = moreButton.closest('[data-cart-drawer-row]')

			if (row) {
				openActionSheet(row)
			}

			return
		}

		const sheetClose = event.target.closest('[data-cart-action-sheet-close]')

		if (sheetClose) {
			closeActionSheet()
			return
		}

		const sheetRemove = event.target.closest('[data-cart-sheet-remove]')

		if (sheetRemove && activeActionRow) {
			const row = activeActionRow

			closeActionSheet()
			removeItem(row)

			return
		}

		const promoRemove = event.target.closest('[data-cart-promo-remove]')

		if (promoRemove) {
			event.preventDefault()

			removePromo(promoRemove)

			return
		}

		const sheetMinus = event.target.closest('[data-cart-sheet-minus]')

		if (sheetMinus && activeActionRow) {
			const quantityElement = activeActionRow.querySelector(
				'[data-cart-drawer-qty]'
			)

			if (!quantityElement) {
				return
			}

			const current = Number(quantityElement.textContent) || 1

			updateQuantity(activeActionRow, quantityElement, current - 1)

			closeActionSheet()

			return
		}

		const sheetPlus = event.target.closest('[data-cart-sheet-plus]')

		if (sheetPlus && activeActionRow) {
			const quantityElement = activeActionRow.querySelector(
				'[data-cart-drawer-qty]'
			)

			if (!quantityElement) {
				return
			}

			const current = Number(quantityElement.textContent) || 1

			updateQuantity(activeActionRow, quantityElement, current + 1)

			closeActionSheet()

			return
		}
	})

	document.addEventListener('submit', function (event) {
		const form = event.target.closest('[data-cart-promo-form]')

		if (!form || !drawer.contains(form)) {
			return
		}

		event.preventDefault()

		applyPromo(form)
	})

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && isOpen) {
			close()
		}
	})

	/*
	|--------------------------------------------------------------------------
	| Public API
	|--------------------------------------------------------------------------
	*/

	window.VNCartDrawer = {
		open: open,
		close: close,
		load: load,
		refresh: load
	}
})(window, document, window.jQuery)
