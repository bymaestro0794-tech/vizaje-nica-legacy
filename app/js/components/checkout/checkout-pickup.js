;(function () {
	'use strict'

	const root = document.querySelector('[data-checkout-pickup]')

	if (!root) {
		return
	}

	const mapElement = root.querySelector('#checkoutPickupMap')

	const dataElement = root.querySelector('[data-checkout-pickup-stores]')

	if (!mapElement || !dataElement) {
		return
	}

	let stores = []

	try {
		stores = JSON.parse(dataElement.textContent)
	} catch (error) {
		console.error('[Checkout pickup]', error)

		return
	}

	if (!Array.isArray(stores) || !stores.length) {
		return
	}

	const titleElement = root.querySelector('[data-pickup-title]')

	const addressElement = root.querySelector('[data-pickup-address]')

	const timeElement = root.querySelector('[data-pickup-time]')

	const numberElement = root.querySelector('[data-pickup-number]')

	const confirmButton = root.querySelector('[data-pickup-confirm]')

	const MOBILE_BREAKPOINT = 767
	const firstStore = stores[0]
	const initialCenter = [Number(firstStore.lng), Number(firstStore.lat)]

	let map = null
	let initialized = false
	let activeStore = null
	let markers = []
	let previousFocus = null

	/* ======================================================================
	   OPEN
	   ====================================================================== */

	document.addEventListener('click', function (event) {
		const trigger = event.target.closest('[data-pickup-open]')

		if (!trigger) {
			return
		}

		event.preventDefault()

		openPicker(trigger)
	})

	function openPicker(trigger) {
		previousFocus = trigger || document.activeElement

		root.classList.add('is-open')

		root.setAttribute('aria-hidden', 'false')

		document.body.classList.add('checkout-pickup-open')

		if (!initialized) {
			initMap()

			return
		}

		window.setTimeout(function () {
			if (map) {
				map.resize()
			}
		}, 50)
	}

	/* ======================================================================
	   CLOSE
	   ====================================================================== */

	document.addEventListener('click', function (event) {
		const closeButton = event.target.closest('[data-pickup-close]')

		if (!closeButton || !root.contains(closeButton)) {
			return
		}

		event.preventDefault()

		closePicker()
	})

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && root.classList.contains('is-open')) {
			closePicker()
		}
	})

	function closePicker() {
		root.classList.remove('is-open')

		root.setAttribute('aria-hidden', 'true')

		document.body.classList.remove('checkout-pickup-open')

		previousFocus?.focus?.()
	}

	/* ======================================================================
	   MAP
	   ====================================================================== */

	function initMap() {
		if (typeof maplibregl === 'undefined') {
			console.error('[Checkout pickup] MapLibre is not loaded')

			return
		}

		map = new maplibregl.Map({
			container: mapElement,
			style: 'https://tiles.openfreemap.org/styles/liberty',

			center: initialCenter,

			zoom: window.innerWidth <= MOBILE_BREAKPOINT ? 11.1 : 11.6,

			minZoom: 8,
			maxZoom: 18,
			attributionControl: true
		})

		map.addControl(
			new maplibregl.NavigationControl({
				showCompass: false,

				showZoom: true
			}),
			'bottom-right'
		)

		map.on('load', function () {
			initialized = true

			createMarkers()

			/*
			 * Если checkout уже имеет
			 * выбранный магазин —
			 * открываем его.
			 */
			const selectedId = Number(
				document.querySelector('input[name="stores"]')?.value
			)

			const initialStore =
				stores.find(function (store) {
					return Number(store.id) === selectedId
				}) || stores[0]

			selectStore(initialStore, {
				moveMap: false
			})

			window.setTimeout(function () {
				map.resize()

				fitAllStores()
			}, 100)
		})

		map.on('error', function (event) {
			if (event && event.error) {
				console.error('[Checkout pickup]', event.error)
			}
		})
	}

	function fitAllStores() {
		if (!map || !stores.length) {
			return
		}

		const bounds = new maplibregl.LngLatBounds()

		stores.forEach(function (store) {
			const lng = Number(store.lng)

			const lat = Number(store.lat)

			if (!Number.isFinite(lng) || !Number.isFinite(lat)) {
				return
			}

			bounds.extend([lng, lat])
		})

		if (bounds.isEmpty()) {
			return
		}

		map.fitBounds(bounds, {
			padding: window.innerWidth <= MOBILE_BREAKPOINT ? 48 : 90,

			maxZoom: 13,

			duration: 0
		})
	}

	function createMarkers() {
		stores.forEach(function (store) {
			const element = document.createElement('button')

			element.type = 'button'

			element.className = 'checkout-pickup-marker'

			element.setAttribute('aria-label', store.title)

			element.innerHTML = '<span>' + escapeHtml(store.number) + '</span>'

			element.addEventListener('click', function () {
				selectStore(store, {
					moveMap: false
				})
			})

			const marker = new maplibregl.Marker({
				element: element,

				anchor: 'bottom'
			})
				.setLngLat([Number(store.lng), Number(store.lat)])
				.addTo(map)

			markers.push({
				store: store,

				element: element,

				marker: marker
			})
		})
	}

	/* ======================================================================
	   STORE SELECTION
	   ====================================================================== */

	function selectStore(store, options) {
		if (!store) {
			return
		}

		const settings = Object.assign(
			{
				moveMap: false,

				immediate: false
			},
			options || {}
		)

		activeStore = store

		updatePanel(store)

		updateMarkers(store)

		if (settings.moveMap) {
			focusStore(store, settings.immediate)
		}
	}

	function updatePanel(store) {
		if (titleElement) {
			titleElement.textContent = store.title || ''
		}

		if (addressElement) {
			addressElement.textContent = store.address || ''

			addressElement.hidden = !store.address
		}

		if (timeElement) {
			timeElement.textContent = store.time || '—'
		}

		if (numberElement) {
			numberElement.textContent = store.number || ''
		}
	}

	function updateMarkers(store) {
		markers.forEach(function (markerData) {
			const active = Number(markerData.store.id) === Number(store.id)

			markerData.element.classList.toggle('is-active', active)
		})
	}

	function focusStore(store, immediate) {
		const options = {
			center: [Number(store.lng), Number(store.lat)],

			zoom: window.innerWidth <= MOBILE_BREAKPOINT ? 14.1 : 13.8,

			essential: true
		}

		if (immediate) {
			map.jumpTo(options)

			return
		}

		map.flyTo(
			Object.assign({}, options, {
				speed: 1.05,

				curve: 1.25
			})
		)
	}

	/* ======================================================================
	   CONFIRM
	   ====================================================================== */

	if (confirmButton) {
		confirmButton.addEventListener('click', function () {
			if (!activeStore) {
				return
			}

			const storeInput = document.querySelector('input[name="stores"]')

			if (storeInput) {
				storeInput.value = String(activeStore.id)
				storeInput.dispatchEvent(
					new Event('change', {
						bubbles: true
					})
				)
			}

			const titleTargets = document.querySelectorAll(
				'.stores_title, .delivery-main-order__description'
			)

			titleTargets.forEach(function (element) {
				element.textContent = activeStore.title || ''
			})

			const addressTargets = document.querySelectorAll('.stores_address')

			addressTargets.forEach(function (element) {
				element.textContent = activeStore.address || ''
			})

			const deliveryInput = document.querySelector(
				'input[name="delivery"][value="1"]'
			)

			if (deliveryInput) {
				deliveryInput.checked = true

				deliveryInput.dispatchEvent(
					new Event('change', {
						bubbles: true
					})
				)
			}

			closePicker()
		})
	}

	/* ======================================================================
	   HELPERS
	   ====================================================================== */

	function escapeHtml(value) {
		return String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;')
	}
})()
