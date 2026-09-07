;(function () {
	'use strict'

	const root = document.getElementById('vnNotifications')
	const toastRegion = document.getElementById('vnToastRegion')
	const cartModal = document.getElementById('vnCartAdded')

	if (!root || !toastRegion || !cartModal) {
		return
	}

	const cartDialog = cartModal.querySelector('.vn-cart-added__dialog')
	const cartImage = cartModal.querySelector('[data-vn-cart-image]')
	const cartBrand = cartModal.querySelector('[data-vn-cart-brand]')
	const cartName = cartModal.querySelector('[data-vn-cart-name]')
	const cartVariant = cartModal.querySelector('[data-vn-cart-variant]')
	const cartPrice = cartModal.querySelector('[data-vn-cart-price]')
	const cartCount = cartModal.querySelector('[data-vn-cart-count]')
	const cartCloseButtons = cartModal.querySelectorAll('[data-vn-cart-close]')

	let lastFocusedElement = null
	let removeConfirmCallback = null
	function escapeHtml(value) {
		const element = document.createElement('div')
		element.textContent = String(value ?? '')

		return element.innerHTML
	}

	function getToastIcon(type) {
		if (type === 'error') {
			return `
				<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
					<path d="M4 4L10 10M10 4L4 10"
						stroke="currentColor"
						stroke-width="1.6"
						stroke-linecap="round"
					/>
				</svg>
			`
		}

		if (type === 'warning') {
			return `
				<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
					<path d="M7 3V7.5"
						stroke="currentColor"
						stroke-width="1.5"
						stroke-linecap="round"
					/>
					<circle cx="7" cy="10.5" r="0.8" fill="currentColor"/>
				</svg>
			`
		}

		return `
			<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
				<path d="M3 7.2L5.5 9.7L11 4.3"
					stroke="currentColor"
					stroke-width="1.7"
					stroke-linecap="round"
					stroke-linejoin="round"
				/>
			</svg>
		`
	}

	function removeToast(toast) {
		if (!toast || toast.classList.contains('is-leaving')) {
			return
		}

		toast.classList.add('is-leaving')

		window.setTimeout(function () {
			toast.remove()
		}, 250)
	}

	function showToast(options) {
		const config = Object.assign(
			{
				type: 'success',
				title: '',
				message: '',
				duration: 4500
			},
			options || {}
		)

		const toast = document.createElement('div')

		toast.className = `vn-toast vn-toast--${config.type}`
		toast.style.setProperty('--toast-duration', `${config.duration}ms`)

		toast.innerHTML = `
			<div class="vn-toast__icon" aria-hidden="true">
				${getToastIcon(config.type)}
			</div>

			<div class="vn-toast__content">
				${
					config.title
						? `<p class="vn-toast__title">${escapeHtml(config.title)}</p>`
						: ''
				}

				${
					config.message
						? `<p class="vn-toast__message">${escapeHtml(config.message)}</p>`
						: ''
				}
			</div>

			<button
				class="vn-toast__close"
				type="button"
				aria-label="Закрыть"
			>
				<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
					<path d="M4 4L14 14M14 4L4 14"
						stroke="currentColor"
						stroke-width="1.3"
						stroke-linecap="round"
					/>
				</svg>
			</button>

			<div class="vn-toast__progress"></div>
		`

		const closeButton = toast.querySelector('.vn-toast__close')

		closeButton.addEventListener('click', function () {
			removeToast(toast)
		})

		toastRegion.appendChild(toast)

		window.requestAnimationFrame(function () {
			toast.classList.add('is-visible')
		})

		window.setTimeout(function () {
			removeToast(toast)
		}, config.duration)

		return toast
	}

	function setText(element, value) {
		if (!element) {
			return
		}

		const text = String(value ?? '').trim()

		element.textContent = text
		element.hidden = text === ''
	}

	function openCartAdded(product) {
		const data = Object.assign(
			{
				image: '',
				brand: '',
				name: '',
				variant: '',
				price: '',
				count: 0
			},
			product || {}
		)

		lastFocusedElement = document.activeElement

		if (cartImage) {
			cartImage.src = data.image || ''
			cartImage.alt = data.name || ''
			cartImage.parentElement.hidden = !data.image
		}

		setText(cartBrand, data.brand)
		setText(cartName, data.name)
		setText(cartVariant, data.variant)
		setText(cartPrice, data.price)

		if (cartCount) {
			cartCount.textContent = data.count > 0 ? ` (${data.count})` : ''
		}

		cartModal.classList.add('is-open')
		cartModal.setAttribute('aria-hidden', 'false')
		document.body.classList.add('vn-notification-lock')

		const firstButton = cartDialog.querySelector('button, a')

		if (firstButton) {
			window.setTimeout(function () {
				firstButton.focus()
			}, 50)
		}
	}

	function closeCartAdded() {
		cartModal.classList.remove('is-open')
		cartModal.setAttribute('aria-hidden', 'true')
		document.body.classList.remove('vn-notification-lock')

		if (lastFocusedElement instanceof HTMLElement) {
			lastFocusedElement.focus()
		}
	}

	cartCloseButtons.forEach(function (button) {
		button.addEventListener('click', closeCartAdded)
	})

	cartModal.addEventListener('click', function (event) {
		if (event.target === cartModal) {
			closeCartAdded()
		}
	})

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && cartModal.classList.contains('is-open')) {
			closeCartAdded()
		}
	})

	window.VNNotify = {
		toast: showToast,
		cartAdded: openCartAdded,
		closeCartAdded: closeCartAdded
	}

	const removeModal = document.getElementById('vnRemoveModal')
	const removeDialog = removeModal
		? removeModal.querySelector('.vn-remove-modal__dialog')
		: null

	const removeImage = removeModal
		? removeModal.querySelector('[data-vn-remove-image]')
		: null

	const removeImageWrap = removeModal
		? removeModal.querySelector('[data-vn-remove-image-wrap]')
		: null

	const removeName = removeModal
		? removeModal.querySelector('[data-vn-remove-name]')
		: null

	const removeVariant = removeModal
		? removeModal.querySelector('[data-vn-remove-variant]')
		: null

	const removePrice = removeModal
		? removeModal.querySelector('[data-vn-remove-price]')
		: null

	const removeConfirmButton = removeModal
		? removeModal.querySelector('[data-vn-remove-confirm]')
		: null

	const removeCloseButtons = removeModal
		? removeModal.querySelectorAll('[data-vn-remove-close]')
		: []

	function openRemoveModal(product, onConfirm) {
		if (!removeModal) {
			return
		}

		var data = Object.assign(
			{
				image: '',
				name: '',
				variant: '',
				price: ''
			},
			product || {}
		)

		removeConfirmCallback = typeof onConfirm === 'function' ? onConfirm : null

		if (removeImage && removeImageWrap) {
			var imageUrl = String(data.image || '').trim()

			removeImage.src = imageUrl
			removeImage.alt = data.name || ''
			removeImageWrap.hidden = imageUrl === ''

			removeImage.onerror = function () {
				removeImageWrap.hidden = true
			}
		}

		setText(removeName, data.name)
		setText(removeVariant, data.variant)
		setText(removePrice, data.price)

		removeModal.classList.add('is-open')
		removeModal.setAttribute('aria-hidden', 'false')
		document.body.classList.add('vn-notification-lock')

		if (removeConfirmButton) {
			window.setTimeout(function () {
				removeConfirmButton.focus()
			}, 50)
		}
	}

	function closeRemoveModal() {
		if (!removeModal) {
			return
		}

		removeModal.classList.remove('is-open')
		removeModal.setAttribute('aria-hidden', 'true')
		document.body.classList.remove('vn-notification-lock')

		removeConfirmCallback = null
	}
	removeCloseButtons.forEach(function (button) {
		button.addEventListener('click', closeRemoveModal)
	})

	if (removeModal) {
		removeModal.addEventListener('click', function (event) {
			if (event.target === removeModal) {
				closeRemoveModal()
			}
		})
	}

	if (removeConfirmButton) {
		removeConfirmButton.addEventListener('click', function () {
			var callback = removeConfirmCallback

			closeRemoveModal()

			if (typeof callback === 'function') {
				callback()
			}
		})
	}
	window.VNNotify = {
		toast: showToast,
		cartAdded: openCartAdded,
		closeCartAdded: closeCartAdded,
		removeConfirm: openRemoveModal,
		closeRemoveModal: closeRemoveModal
	}
})()
