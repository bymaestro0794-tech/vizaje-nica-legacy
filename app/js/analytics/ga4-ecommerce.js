;(function (window, document) {
	'use strict'

	/* ==========================================================================
	   DATA LAYER
	   ========================================================================== */

	window.dataLayer = window.dataLayer || []

	/* ==========================================================================
	   HELPERS
	   ========================================================================== */

	function toNumber(value) {
		const result = Number(value)

		return Number.isFinite(result) ? result : 0
	}

	function normalizeQuantity(value) {
		return Math.max(1, toNumber(value || 1))
	}

	function cleanString(value) {
		return String(value || '').trim()
	}

	/* ==========================================================================
	   ITEM NORMALIZATION
	   ========================================================================== */

	function cleanItem(item) {
		if (!item) {
			return null
		}

		const result = {
			item_id: cleanString(item.item_id),

			item_name: cleanString(item.item_name),

			item_brand: cleanString(item.item_brand),

			item_category: cleanString(item.item_category),

			price: toNumber(item.price),

			quantity: normalizeQuantity(item.quantity)
		}

		if (item.item_category2) {
			result.item_category2 = cleanString(item.item_category2)
		}

		if (item.item_category3) {
			result.item_category3 = cleanString(item.item_category3)
		}

		if (item.item_variant) {
			result.item_variant = cleanString(item.item_variant)
		}

		if (item.item_list_id) {
			result.item_list_id = cleanString(item.item_list_id)
		}

		if (item.item_list_name) {
			result.item_list_name = cleanString(item.item_list_name)
		}

		if (item.index !== undefined && item.index !== null) {
			result.index = toNumber(item.index)
		}

		return result
	}

	/* ==========================================================================
	   PUSH
	   ========================================================================== */

	function push(eventName, ecommerce) {
		if (!eventName) {
			return
		}

		const data = Object.assign({}, ecommerce || {})

		if (!data.currency) {
			data.currency = 'MDL'
		}

		if (data.value !== undefined) {
			data.value = toNumber(data.value)
		}

		if (data.shipping !== undefined) {
			data.shipping = toNumber(data.shipping)
		}

		if (data.tax !== undefined) {
			data.tax = toNumber(data.tax)
		}

		if (Array.isArray(data.items)) {
			data.items = data.items.map(cleanItem).filter(Boolean)
		}

		/*
		 * Сбрасываем предыдущий ecommerce object.
		 */
		window.dataLayer.push({
			ecommerce: null
		})

		window.dataLayer.push({
			event: eventName,

			ecommerce: data
		})
	}

	/* ==========================================================================
	   PRODUCT PAGE DATA
	   ========================================================================== */

	function readProductPageData() {
		const element = document.getElementById('product-page-data')

		if (!element) {
			return null
		}

		try {
			return JSON.parse(element.textContent || '{}')
		} catch (error) {
			console.error('[GA4] Invalid product-page-data', error)

			return null
		}
	}

	function getSelectedVariation(state) {
		if (!state || !Array.isArray(state.variations)) {
			return null
		}

		const page = document.querySelector('[data-product-page]')

		const selectedVariationId = Number(page?.dataset.selectedVariation || 0)

		if (selectedVariationId <= 0) {
			return null
		}

		return (
			state.variations.find(function (variation) {
				return Number(variation.id) === selectedVariationId
			}) || null
		)
	}

	/* ==========================================================================
	   CURRENT PRODUCT ITEM
	   ========================================================================== */

	function getCurrentProductItem() {
		const state = readProductPageData()

		if (!state || !state.product) {
			return null
		}

		const product = state.product

		const variation = getSelectedVariation(state)

		let price = toNumber(product.discountPrice || product.price)

		let variant = cleanString(product.volume)

		if (variation) {
			price = toNumber(variation.discountPrice || variation.price)

			const variantParts = []

			if (variation.color) {
				variantParts.push(cleanString(variation.color))
			}

			if (variation.volume) {
				variantParts.push(cleanString(variation.volume))
			}

			if (!variantParts.length && variation.title) {
				variantParts.push(cleanString(variation.title))
			}

			if (!variantParts.length && variation.sku) {
				variantParts.push(cleanString(variation.sku))
			}

			variant = variantParts.join(' · ')
		}

		return cleanItem({
			item_id: product.id,

			item_name: product.title,

			item_brand: product.brand || '',

			item_category: product.category || '',

			item_variant: variant,

			price: price,

			quantity: 1
		})
	}

	/* ==========================================================================
	   PUBLIC API
	   ========================================================================== */

	window.VNAnalytics = {
		push: push,

		item: cleanItem,

		getCurrentProductItem: getCurrentProductItem,

		getProductPageData: readProductPageData
	}

	/* ==========================================================================
	   VIEW ITEM
	   ========================================================================== */

	let viewItemSent = false

	function sendViewItem() {
		if (viewItemSent) {
			return
		}

		const item = getCurrentProductItem()

		if (!item) {
			return
		}

		viewItemSent = true

		push('view_item', {
			currency: 'MDL',

			value: item.price,

			items: [item]
		})
	}

	/* ==========================================================================
   BEGIN CHECKOUT
   ========================================================================== */

	let beginCheckoutSent = false

	function readCheckoutData() {
		const element = document.getElementById('checkout-ga4-data')

		if (!element) {
			return null
		}

		try {
			return JSON.parse(element.textContent || '{}')
		} catch (error) {
			console.error('[GA4] Invalid checkout-ga4-data', error)

			return null
		}
	}

	function sendBeginCheckout() {
		if (beginCheckoutSent) {
			return
		}

		const checkout = readCheckoutData()

		if (!checkout || !Array.isArray(checkout.items) || !checkout.items.length) {
			return
		}

		beginCheckoutSent = true

		push('begin_checkout', checkout)
	}

	/* ==========================================================================
   PURCHASE
   ========================================================================== */

	let purchaseSent = false

	function readPurchaseData() {
		const element = document.getElementById('purchase-ga4-data')

		if (!element) {
			return null
		}

		try {
			return JSON.parse(element.textContent || '{}')
		} catch (error) {
			console.error('[GA4] Invalid purchase-ga4-data', error)

			return null
		}
	}

	function sendPurchase() {
		if (purchaseSent) {
			return
		}

		const purchase = readPurchaseData()

		if (
			!purchase ||
			!purchase.transaction_id ||
			!Array.isArray(purchase.items) ||
			!purchase.items.length
		) {
			return
		}

		const transactionId = String(purchase.transaction_id)

		const storageKey = 'vn_ga4_purchase_' + transactionId

		/*
		 * Client-side защита от F5 / повторного
		 * открытия confirmation page.
		 *
		 * transaction_id дополнительно позволяет
		 * GA4 идентифицировать одну и ту же транзакцию.
		 */

		try {
			if (window.localStorage.getItem(storageKey)) {
				return
			}
		} catch (error) {
			/*
			 * localStorage может быть недоступен,
			 * но analytics не должен из-за этого падать.
			 */
		}

		purchaseSent = true

		push('purchase', purchase)

		try {
			window.localStorage.setItem(storageKey, '1')
		} catch (error) {
			// noop
		}
	}

	/* ==========================================================================
	   INIT
	   ========================================================================== */

	function initAnalyticsPageEvents() {
		sendViewItem()

		sendBeginCheckout()

		sendPurchase()
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAnalyticsPageEvents, {
			once: true
		})
	} else {
		initAnalyticsPageEvents()
	}
})(window, document)
