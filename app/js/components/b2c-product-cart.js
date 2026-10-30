;(function (window, document, $) {
	'use strict'

	if (!$ || document.body?.dataset?.b2cCartReady === 'true') {
		return
	}

	document.body.dataset.b2cCartReady = 'true'

	const language = document.documentElement.lang?.toLowerCase().startsWith('ro')
		? 'ro'
		: 'ru'

	const labels = language === 'ro'
		? {
			minus: 'Scade cantitatea',
			plus: 'Adaugă cantitatea',
			inCart: 'În coș'
		}
		: {
			minus: 'Уменьшить количество',
			plus: 'Увеличить количество',
			inCart: 'В корзине'
		}

	const source = window.VNProductCartState || {}
	const items = Object.create(null)
	const pendingRows = new Set()

	Object.keys(source.items || {}).forEach(function (key) {
		const item = source.items[key]

		if (!item || !item.rowId || Number(item.productId) <= 0) {
			return
		}

		items[key] = normalizeItem(item)
	})

	function normalizeItem(item) {
		return {
			productId: Number(item.productId) || 0,
			variantId: Number(item.variantId) || 0,
			rowId: String(item.rowId || ''),
			quantity: Math.max(1, Number(item.quantity) || 1),
			maxQuantity: Math.max(0, Number(item.maxQuantity) || 0)
		}
	}

	function itemKey(productId, variantId) {
		return String(Number(productId) || 0) + ':' + String(Number(variantId) || 0)
	}

	function findItemByRowId(rowId) {
		const target = String(rowId || '')

		return Object.keys(items)
			.map(function (key) { return items[key] })
			.find(function (item) { return item.rowId === target }) || null
	}

	function findItem(productId, variantId) {
		const exact = items[itemKey(productId, variantId)]

		if (exact) {
			return exact
		}

		/*
		 * A product card cannot know which variant a customer selected in a
		 * quick view. If several variants are in the cart, show their total and
		 * keep the first row as the +/- target. The product page uses the exact
		 * selected variant and never enters this fallback.
		 */
		if (Number(variantId) !== 0) {
			return null
		}

		const productItems = Object.keys(items)
			.map(function (key) { return items[key] })
			.filter(function (item) {
				return item.productId === Number(productId)
			})

		if (!productItems.length) {
			return null
		}

		if (productItems.length === 1) {
			return productItems[0]
		}

		return {
			productId: Number(productId),
			variantId: 0,
			rowId: productItems[0].rowId,
			quantity: productItems.reduce(function (total, item) {
				return total + item.quantity
			}, 0),
			maxQuantity: productItems[0].maxQuantity,
			isAggregate: true
		}
	}

	function productPage() {
		return document.querySelector('[data-product-page]')
	}

	function productPageTarget() {
		const page = productPage()

		if (!page) {
			return null
		}

		return {
			productId: Number(page.dataset.productId) || 0,
			variantId: Number(page.dataset.selectedVariation) || 0
		}
	}

	function rememberOriginalControl(root) {
		if (!root.dataset.b2cOriginalClass) {
			root.dataset.b2cOriginalClass = root.className
		}

		if (typeof root.__b2cOriginalHTML === 'undefined') {
			root.__b2cOriginalHTML = root.innerHTML
		}

		if (!root.dataset.b2cOriginalAriaLabel) {
			root.dataset.b2cOriginalAriaLabel = root.getAttribute('aria-label') || ''
		}
	}

	function renderStepper(root, item, isProductPageControl) {
		if (
			root.dataset.b2cCartStepper === 'true'
			&& root.dataset.b2cRowId === item.rowId
			&& Number(root.dataset.b2cQuantity) === item.quantity
			&& Number(root.dataset.b2cMaxQuantity || 0) === item.maxQuantity
		) {
			return
		}

		rememberOriginalControl(root)

		root.classList.remove('add_cart', 'has-variable')
		root.classList.add('b2c-cart-stepper')

		if (isProductPageControl) {
			root.classList.add('b2c-cart-stepper--page')
		}

		root.dataset.b2cCartStepper = 'true'
		root.dataset.b2cProductId = String(item.productId)
		root.dataset.b2cVariantId = String(item.variantId)
		root.dataset.b2cRowId = item.rowId
		root.dataset.b2cQuantity = String(item.quantity)
		root.dataset.b2cMaxQuantity = String(item.maxQuantity || '')
		root.setAttribute(
			'aria-label',
			labels.inCart + ': ' + item.quantity
		)

		root.innerHTML =
			'<span class="b2c-cart-stepper__action"' +
			' data-b2c-cart-stepper-minus role="button" tabindex="0"' +
			' aria-label="' + labels.minus + '">−</span>' +
			'<span class="b2c-cart-stepper__value"' +
			' data-b2c-cart-stepper-value aria-live="polite">' +
			item.quantity +
			'</span>' +
			'<span class="b2c-cart-stepper__action"' +
			' data-b2c-cart-stepper-plus role="button" tabindex="0"' +
			' aria-label="' + labels.plus + '">+</span>'

		const plus = root.querySelector('[data-b2c-cart-stepper-plus]')

		if (plus && item.maxQuantity > 0 && item.quantity >= item.maxQuantity) {
			plus.setAttribute('aria-disabled', 'true')
			plus.classList.add('is-disabled')
		}
	}

	function restoreControl(root) {
		if (!root.dataset.b2cOriginalClass) {
			return
		}

		root.className = root.dataset.b2cOriginalClass
		root.innerHTML = typeof root.__b2cOriginalHTML === 'string'
			? root.__b2cOriginalHTML
			: ''
		root.removeAttribute('data-b2c-cart-stepper')
		root.removeAttribute('data-b2c-product-id')
		root.removeAttribute('data-b2c-variant-id')
		root.removeAttribute('data-b2c-row-id')
		root.removeAttribute('data-b2c-quantity')
		root.removeAttribute('data-b2c-max-quantity')

		if (root.dataset.b2cOriginalAriaLabel) {
			root.setAttribute('aria-label', root.dataset.b2cOriginalAriaLabel)
		} else {
			root.removeAttribute('aria-label')
		}
	}

	function renderCard(card) {
		const root = card.querySelector(
			'.head-product__cart, .action-product__cart'
		)

		if (!root) {
			return
		}

		const productId = Number(card.getAttribute('data-prod_id')) || 0
		const variantId = Number(card.dataset.productVariant) || 0
		const item = findItem(productId, variantId)

		if (item) {
			renderStepper(root, item, false)
		} else if (root.dataset.b2cCartStepper === 'true') {
			restoreControl(root)
		}
	}

	function renderProductPage() {
		const target = productPageTarget()

		if (!target) {
			return
		}

		const item = findItem(target.productId, target.variantId)

		document
			.querySelectorAll(
				'[data-product-add], [data-product-add-top], [data-product-sticky-add]'
			)
			.forEach(function (root) {
				if (item) {
					renderStepper(root, item, true)
				} else if (root.dataset.b2cCartStepper === 'true') {
					restoreControl(root)
				}
			})
	}

	let renderFrame = null

	function renderAll() {
		if (renderFrame !== null) {
			return
		}

		renderFrame = window.requestAnimationFrame(function () {
			renderFrame = null

			document.querySelectorAll('.b2c-product-card[data-prod_id]').forEach(renderCard)
			renderProductPage()
		})
	}

	function updateHeaderCount(totalItems) {
		const count = Math.max(0, Number(totalItems) || 0)

		document.querySelectorAll('[data-cart-count]').forEach(function (element) {
			element.textContent = String(count)
			element.hidden = count <= 0
		})

		document.querySelectorAll('.actions-main-header__counts').forEach(function (element) {
			element.textContent = String(count)
			element.classList.toggle('d_none', count <= 0)
		})
	}

	function removeItemByRowId(rowId) {
		const target = String(rowId || '')

		Object.keys(items).forEach(function (key) {
			if (items[key].rowId === target) {
				delete items[key]
			}
		})
	}

	function onCartAdd(payload) {
		if (!payload || Number(payload.productId) <= 0 || !payload.rowId) {
			return
		}

		removeItemByRowId(payload.rowId)

		const item = normalizeItem({
			productId: payload.productId,
			variantId: payload.variantId,
			rowId: payload.rowId,
			quantity: payload.quantity,
			maxQuantity: payload.maxQuantity
		})

		items[itemKey(item.productId, item.variantId)] = item
		renderAll()
	}

	function onCartUpdate(payload) {
		if (!payload || !payload.rowId) {
			return
		}

		removeItemByRowId(payload.rowId)

		if (Number(payload.quantity) > 0 && Number(payload.productId) > 0) {
			const item = normalizeItem(payload)
			items[itemKey(item.productId, item.variantId)] = item
		}

		renderAll()
	}

	function onCartRemove(rowId) {
		removeItemByRowId(rowId)
		renderAll()
	}

	function updateStepper(root, delta) {
		const rowId = root.dataset.b2cRowId
		const current = Math.max(1, Number(root.dataset.b2cQuantity) || 1)
		const next = Math.max(0, current + delta)

		if (!rowId || pendingRows.has(rowId)) {
			return
		}

		pendingRows.add(rowId)
		root.classList.add('is-updating')

		$.ajax({
			url: '/cart/update_cart',
			type: 'POST',
			dataType: 'json',
			data: {
				rowid: rowId,
				quantity: next,
				lang: language
			},
			success: function (response) {
				if (!response || response.status !== 'ok') {
					return
				}

				if (response.row && Number(response.row.quantity) > 0) {
					onCartUpdate({
						rowId: response.row.row_id,
						productId: response.row.product_id,
						variantId: response.row.variable_id,
						quantity: response.row.quantity,
						maxQuantity: response.row.max_quantity
					})
				} else {
					onCartRemove(rowId)
				}

				updateHeaderCount(response.cart?.total_items)

				if (Array.isArray(response.gift_promotions)) {
					window.VNCartDrawer?.refresh?.()
				}
			},
			error: function (xhr) {
				console.error('B2C product quantity error:', xhr)
			},
			complete: function () {
				pendingRows.delete(rowId)
				root.classList.remove('is-updating')
			}
		})
	}

	function handleStepperClick(event) {
		const action = event.target.closest(
			'[data-b2c-cart-stepper-minus], [data-b2c-cart-stepper-plus]'
		)

		if (!action) {
			return
		}

		const root = action.closest('[data-b2c-cart-stepper="true"]')

		if (!root) {
			return
		}

		event.preventDefault()
		event.stopPropagation()

		updateStepper(
			root,
			action.hasAttribute('data-b2c-cart-stepper-plus') ? 1 : -1
		)
	}

	function handleStepperKeydown(event) {
		if (event.key !== 'Enter' && event.key !== ' ') {
			return
		}

		const action = event.target.closest(
			'[data-b2c-cart-stepper-minus], [data-b2c-cart-stepper-plus]'
		)

		if (!action) {
			return
		}

		event.preventDefault()
		event.stopPropagation()
		action.click()
	}

	document.addEventListener('click', handleStepperClick, true)
	document.addEventListener('keydown', handleStepperKeydown, true)

	new MutationObserver(renderAll).observe(document.body, {
		childList: true,
		subtree: true,
		attributes: true,
		attributeFilter: ['data-selected-variation']
	})

	window.VNB2CProductCart = {
		onCartAdd: onCartAdd,
		onCartUpdate: onCartUpdate,
		onCartRemove: onCartRemove,
		render: renderAll
	}

	renderAll()
})(window, document, window.jQuery)
