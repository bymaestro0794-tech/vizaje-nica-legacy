;(function (window, document) {
	'use strict'

	var endpoint = '/analytics/event'
	var consentCookieName = 'cookie_consent'
	var visitorStorageKey = 'vn_analytics_visitor'
	var sessionStorageKey = 'vn_analytics_session'
	var sessionStarted = false
	var memoryVisitorKey = null
	var memorySessionKey = null

	var allowedEvents = {
		session_start: true,
		view_catalog: true,
		view_item: true,
		add_to_cart: true,
		remove_from_cart: true,
		cart_updated: true,
		view_cart: true,
		begin_checkout: true,
		order_submitted: true,
		purchase: true
	}

	function readCookie(name) {
		var prefix = name + '='
		var cookies = document.cookie ? document.cookie.split(';') : []

		for (var i = 0; i < cookies.length; i += 1) {
			var cookie = cookies[i].trim()

			if (cookie.indexOf(prefix) === 0) {
				return cookie.slice(prefix.length)
			}
		}

		return ''
	}

	function hasAnalyticsConsent() {
		var raw = readCookie(consentCookieName)
		var consent

		if (raw) {
			try {
				consent = JSON.parse(decodeURIComponent(raw))
			} catch (error) {
				try {
					consent = JSON.parse(raw)
				} catch (ignored) {
					consent = null
				}
			}

			if (consent && typeof consent.analytics === 'boolean') {
				return consent.analytics
			}
		}

		if (typeof window.VN_ANALYTICS_CONSENT === 'boolean') {
			return window.VN_ANALYTICS_CONSENT
		}

		return false
	}

	function createUuid() {
		if (window.crypto && typeof window.crypto.randomUUID === 'function') {
			return window.crypto.randomUUID()
		}

		if (window.crypto && window.crypto.getRandomValues) {
			var bytes = new Uint8Array(16)
			window.crypto.getRandomValues(bytes)
			bytes[6] = (bytes[6] & 0x0f) | 0x40
			bytes[8] = (bytes[8] & 0x3f) | 0x80

			var hex = Array.prototype.map
				.call(bytes, function (byte) {
					return ('0' + byte.toString(16)).slice(-2)
				})
				.join('')

			return (
				hex.slice(0, 8) +
				'-' +
				hex.slice(8, 12) +
				'-' +
				hex.slice(12, 16) +
				'-' +
				hex.slice(16, 20) +
				'-' +
				hex.slice(20)
			)
		}

		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(
			/[xy]/g,
			function (character) {
				var random = ((Date.now() + Math.random() * 16) % 16) | 0
				return (character === 'x' ? random : (random & 0x3) | 0x8).toString(16)
			}
		)
	}

	function randomKey(prefix) {
		return prefix + '_' + createUuid()
	}

	function getStorageValue(storage, key) {
		try {
			return storage.getItem(key)
		} catch (error) {
			return null
		}
	}

	function setStorageValue(storage, key, value) {
		try {
			storage.setItem(key, value)
		} catch (error) {
			// Storage can be unavailable in private/restricted browser modes.
		}
	}

	function getIdentity() {
		if (!hasAnalyticsConsent()) {
			return null
		}

		var visitorKey = getStorageValue(window.localStorage, visitorStorageKey)
		var sessionKey = getStorageValue(window.sessionStorage, sessionStorageKey)

		if (!visitorKey) {
			visitorKey = memoryVisitorKey || randomKey('visitor')
			memoryVisitorKey = visitorKey
			setStorageValue(window.localStorage, visitorStorageKey, visitorKey)
		}

		if (!sessionKey) {
			sessionKey = memorySessionKey || randomKey('session')
			memorySessionKey = sessionKey
			setStorageValue(window.sessionStorage, sessionStorageKey, sessionKey)
		}

		return {
			visitor_key: visitorKey,
			session_key: sessionKey
		}
	}

	function getCookieValue(name) {
		var value = readCookie(name)

		try {
			return decodeURIComponent(value)
		} catch (error) {
			return value
		}
	}

	function getCartKey() {
		var cartValue = getCookieValue('shopcart')

		if (
			!cartValue ||
			!window.crypto ||
			!window.crypto.subtle ||
			!window.TextEncoder
		) {
			return Promise.resolve(null)
		}

		return window.crypto.subtle
			.digest('SHA-256', new window.TextEncoder().encode(cartValue))
			.then(function (buffer) {
				return Array.prototype.map
					.call(new Uint8Array(buffer), function (byte) {
						return ('0' + byte.toString(16)).slice(-2)
					})
					.join('')
			})
			.catch(function () {
				return null
			})
	}

	function uuid() {
		return createUuid()
	}

	function cleanDetails(details) {
		details = details && typeof details === 'object' ? details : {}

		var allowed = [
			'product_id',
			'category_id',
			'order_id',
			'quantity',
			'cart_items_count',
			'cart_value',
			'event_value',
			'currency',
			'page_path'
		]
		var result = {}

		allowed.forEach(function (field) {
			if (
				details[field] !== undefined &&
				details[field] !== null &&
				details[field] !== ''
			) {
				result[field] = details[field]
			}
		})

		if (!result.page_path) {
			result.page_path = window.location.pathname || '/'
		}

		return result
	}

	function track(eventName, details) {
		if (!allowedEvents[eventName] || !hasAnalyticsConsent()) {
			return Promise.resolve(false)
		}

		var identity = getIdentity()

		if (!identity) {
			return Promise.resolve(false)
		}

		var payload = cleanDetails(details)
		payload.event_uid = uuid()
		payload.event_name = eventName
		payload.visitor_key = identity.visitor_key
		payload.session_key = identity.session_key

		return getCartKey().then(function (cartKey) {
			if (cartKey) {
				payload.cart_key = cartKey
			}

			return window
				.fetch(endpoint, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(payload),
					keepalive: true
				})
				.then(function (response) {
					return response.status === 201
				})
				.catch(function () {
					return false
				})
		})
	}

	function init() {
		if (sessionStarted || !hasAnalyticsConsent()) {
			return Promise.resolve(false)
		}

		return track('session_start').then(function (sent) {
			if (sent) {
				sessionStarted = true
			}

			return sent
		})
	}

	window.VNFirstPartyAnalytics = {
		init: init,
		refresh: init,
		track: track,
		hasConsent: hasAnalyticsConsent
	}

	document.addEventListener('DOMContentLoaded', init)
	window.addEventListener('vn:consent-changed', init)
	window.addEventListener('cookieConsentChanged', init)
})(window, document)
