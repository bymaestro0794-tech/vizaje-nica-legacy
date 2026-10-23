;(function () {
	'use strict'

	const root = document.querySelector('[data-checkout-pickup]')

	if (!root) {
		return
	}

	const dataElement = root.querySelector('[data-checkout-pickup-stores]')
	const listElement = root.querySelector('[data-pickup-list]')
	const confirmButton = root.querySelector('[data-pickup-confirm]')
	const titleElement = root.querySelector('[data-pickup-title]')
	const addressElement = root.querySelector('[data-pickup-address]')
	const timeElement = root.querySelector('[data-pickup-time]')
	const numberElement = root.querySelector('[data-pickup-number]')

	if (!dataElement || !listElement) {
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
		listElement.textContent =
			document.documentElement.lang === 'ro'
				? 'Momentan nu există magazine disponibile.'
				: 'Сейчас нет доступных магазинов.'
		if (confirmButton) confirmButton.disabled = true
		return
	}

	const lang = document.documentElement.lang === 'ro' ? 'ro' : 'ru'
	const routeLabel = lang === 'ro' ? 'Traseu' : 'Маршрут'
	const checkoutContent = document.querySelector('.order')
	let checkoutWasInert = false
	let activeStore = null
	let previousFocus = null

	renderStores()

	document.addEventListener('click', function (event) {
		if (!(event.target instanceof Element)) return

		const trigger = event.target.closest('[data-pickup-open]')
		if (trigger) {
			event.preventDefault()
			openPicker(trigger)
			return
		}

		const closeButton = event.target.closest('[data-pickup-close]')
		if (closeButton && root.contains(closeButton)) {
			event.preventDefault()
			closePicker()
		}
	})

	listElement.addEventListener('click', function (event) {
		if (!(event.target instanceof Element)) return

		const selectButton = event.target.closest('[data-pickup-select]')
		if (!selectButton) return

		const store = stores.find(function (item) {
			return String(item.id) === selectButton.dataset.pickupSelect
		})

		if (store) selectStore(store)
	})

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && root.classList.contains('is-open')) {
			closePicker()
		}
	})

	if (confirmButton) {
		confirmButton.addEventListener('click', function () {
			if (!activeStore) return

			const storeInput = document.querySelector('input[name="stores"]')
			if (storeInput) {
				storeInput.value = String(activeStore.id)
				storeInput.dispatchEvent(new Event('change', { bubbles: true }))
			}

			document.querySelectorAll('.stores_title, .delivery-main-order__description')
				.forEach(function (element) {
					element.textContent = activeStore.title || ''
				})

			document.querySelectorAll('.stores_address').forEach(function (element) {
				element.textContent = activeStore.address || ''
			})

			const deliveryInput = document.querySelector('input[name="delivery"][value="1"]')
			if (deliveryInput) {
				deliveryInput.checked = true
				deliveryInput.dispatchEvent(new Event('change', { bubbles: true }))
			}

			closePicker()
		})
	}

	function renderStores() {
		listElement.innerHTML = stores.map(function (store) {
			const hasCoordinates = store.lat !== null && store.lng !== null
			const lat = Number(store.lat)
			const lng = Number(store.lng)
			const destination = hasCoordinates && Number.isFinite(lat) && Number.isFinite(lng)
				? lat + ',' + lng
				: [store.title, store.address].filter(Boolean).join(', ')
			const routeUrl = 'https://www.google.com/maps/dir/?api=1&destination=' +
				encodeURIComponent(destination)

			return (
				'<article class="checkout-pickup__store" data-pickup-store="' + escapeHtml(store.id) + '">' +
					'<button type="button" class="checkout-pickup__store-select" data-pickup-select="' + escapeHtml(store.id) + '" aria-pressed="false">' +
						'<span class="checkout-pickup__store-number">' + escapeHtml(store.number) + '</span>' +
						'<span class="checkout-pickup__store-copy">' +
							'<span class="checkout-pickup__store-title">' + escapeHtml(store.title) + '</span>' +
							'<span class="checkout-pickup__store-address">' + escapeHtml(store.address || '') + '</span>' +
							'<span class="checkout-pickup__store-hours">' + escapeHtml(store.time || '') + '</span>' +
						'</span>' +
						'<span class="checkout-pickup__store-radio" aria-hidden="true"></span>' +
					'</button>' +
					'<a class="checkout-pickup__route" href="' + routeUrl + '" target="_blank" rel="noopener noreferrer">' + routeLabel + '</a>' +
				'</article>'
			)
		}).join('')

		const currentStoreId = Number(document.querySelector('input[name="stores"]')?.value)
		const initialStore = stores.find(function (store) {
			return Number(store.id) === currentStoreId
		}) || stores[0]

		selectStore(initialStore)
	}

	function selectStore(store) {
		activeStore = store

		listElement.querySelectorAll('[data-pickup-store]').forEach(function (item) {
			const selected = String(item.dataset.pickupStore) === String(store.id)
			item.classList.toggle('is-selected', selected)
			item.querySelector('[data-pickup-select]')?.setAttribute('aria-pressed', String(selected))
		})

		if (titleElement) titleElement.textContent = store.title || ''
		if (addressElement) {
			addressElement.textContent = store.address || ''
			addressElement.hidden = !store.address
		}
		if (timeElement) timeElement.textContent = store.time || '—'
		if (numberElement) numberElement.textContent = store.number || ''
	}

	function openPicker(trigger) {
		previousFocus = trigger || document.activeElement
		root.classList.add('is-open')
		root.setAttribute('aria-hidden', 'false')
		document.body.classList.add('checkout-pickup-open')

		if (checkoutContent) {
			checkoutWasInert = checkoutContent.inert
			checkoutContent.inert = true
		}

		root.querySelector('[data-pickup-select][aria-pressed="true"]')?.focus()
	}

	function closePicker() {
		root.classList.remove('is-open')
		root.setAttribute('aria-hidden', 'true')
		document.body.classList.remove('checkout-pickup-open')

		if (checkoutContent) checkoutContent.inert = checkoutWasInert
		previousFocus?.focus?.()
	}

	function escapeHtml(value) {
		return String(value == null ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;')
	}
})()
