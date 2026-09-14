;(function (window, document) {
	'use strict'

	var analytics = window.VNFirstPartyAnalytics
	var pageEventsSent = {}
	var purchaseStoragePrefix = 'vn_analytics_purchase_'

	if (!analytics || typeof analytics.track !== 'function') {
		return
	}

	function positiveInt(value) {
		var number = parseInt(value, 10)
		return number > 0 ? number : null
	}

	function nonNegativeInt(value) {
		var number = parseInt(value, 10)
		return number >= 0 ? number : null
	}

	function money(value) {
		if (value === undefined || value === null || value === '') {
			return null
		}

		var normalized = String(value)
			.replace(/[^0-9,.]/g, '')
			.replace(',', '.')

		return /^\d{1,10}(\.\d{1,2})?$/.test(normalized) ? normalized : null
	}

	function readStorage(key) {
		try {
			return window.localStorage.getItem(key)
		} catch (error) {
			return null
		}
	}

	function writeStorage(key, value) {
		try {
			window.localStorage.setItem(key, value)
		} catch (error) {
			// Storage can be unavailable in private/restricted browser modes.
		}
	}

	function sendOnce(name, details) {
		if (pageEventsSent[name]) {
			return
		}

		pageEventsSent[name] = true
		analytics.track(name, details || {})
	}

	function sendPageEvent() {
		var productPage = document.querySelector(
			'[data-product-page][data-product-id]'
		)
		var path = window.location.pathname || '/'
		var isCheckout = /\/(checkout|oformlenie-zakaza)(\/|$)/i.test(path)
		var isCart = /\/(cart|korzina|cos)(\/|$)/i.test(path)
		var isCatalog = /\/(catalog|katalog)(\/|$)/i.test(path)

		if (productPage) {
			sendOnce('view_item', {
				product_id: positiveInt(productPage.getAttribute('data-product-id'))
			})
			return
		}

		if (isCheckout) {
			sendOnce('begin_checkout')
			return
		}

		if (isCart) {
			sendOnce('view_cart')
			return
		}

		if (isCatalog) {
			sendOnce('view_catalog')
		}
	}

	function sendPurchase() {
		var dataElement = document.getElementById('purchase-ga4-data')
		var purchase
		var orderId
		var storageKey
		var details

		if (!dataElement) {
			return
		}

		try {
			purchase = JSON.parse(
				dataElement.textContent || dataElement.innerText || '{}'
			)
		} catch (error) {
			return
		}

		orderId = positiveInt(purchase && purchase.transaction_id)

		if (!orderId) {
			return
		}

		storageKey = purchaseStoragePrefix + orderId

		if (pageEventsSent[storageKey] || readStorage(storageKey) === '1') {
			return
		}

		details = {
			order_id: orderId,
			event_value: money(purchase.value),
			currency: /^[A-Z]{3}$/.test(String(purchase.currency || '').toUpperCase())
				? String(purchase.currency).toUpperCase()
				: 'MDL'
		}

		analytics.track('purchase', details).then(function (sent) {
			if (sent) {
				pageEventsSent[storageKey] = true
				writeStorage(storageKey, '1')
			}
		})
	}

	function requestData(settings) {
		if (!settings || !settings.data) {
			return {}
		}

		if (typeof settings.data === 'object') {
			return settings.data
		}

		return String(settings.data)
			.split('&')
			.reduce(function (result, pair) {
				var parts = pair.split('=')
				var key = decodeURIComponent(parts[0] || '')
				var value = decodeURIComponent(parts.slice(1).join('=') || '')
				result[key] = value
				return result
			}, {})
	}

	function bindCartAjax() {
		if (!window.jQuery || !window.jQuery.fn || window.__VNAnalyticsCartBound) {
			return
		}

		window.__VNAnalyticsCartBound = true

		window
			.jQuery(document)
			.ajaxSuccess(function (event, xhr, settings, response) {
				if (
					!settings ||
					String(settings.type || 'GET').toUpperCase() !== 'POST'
				) {
					return
				}

				if (!response || response.status !== 'ok') {
					return
				}

				var url = String(settings.url || '').split('?')[0]
				var payload = requestData(settings)

				if (/\/cart_add$/.test(url)) {
					analytics.track('add_to_cart', {
						product_id: positiveInt(payload.product_id),
						quantity: positiveInt(payload.quantity) || 1,
						currency: 'MDL'
					})
					return
				}

				if (/\/cart\/update_cart$/.test(url)) {
					analytics.track('cart_updated', {
						cart_items_count: nonNegativeInt(response.total_items),
						cart_value: money(response.total),
						currency: 'MDL'
					})
					return
				}

				if (/\/cart\/delete$/.test(url)) {
					analytics.track('remove_from_cart')
					analytics.track('cart_updated', {
						cart_items_count: nonNegativeInt(response.total_items),
						cart_value: money(response.total),
						currency: 'MDL'
					})
				}
			})
	}

	function start() {
		sendPageEvent()
		bindCartAjax()
		sendPurchase()
	}

	window.addEventListener('vn:consent-changed', sendPurchase)
	window.addEventListener('cookieConsentChanged', sendPurchase)

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start)
	} else {
		start()
	}
})(window, document)
