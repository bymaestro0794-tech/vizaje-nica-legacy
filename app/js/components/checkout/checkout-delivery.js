/* ==========================================================================
   CHECKOUT V2 — DELIVERY
   ========================================================================== */
;(function () {
	'use strict'

	const root =
		document.querySelector('form.order__body[data-checkout]') ||
		document.querySelector('form.order__body')

	if (!root) {
		return
	}

	const deliveryInputs = Array.from(
		root.querySelectorAll('[data-delivery-option]')
	)

	if (!deliveryInputs.length) {
		return
	}

	const lang = document.documentElement.lang === 'ro' ? 'ro' : 'ru'

	const translations = {
		ru: {
			selectDelivery: 'Выберите способ доставки.',

			selectStore: 'Выберите магазин для самовывоза.',

			fillAddress: 'Заполните адрес доставки.',

			fillEntrance: 'Укажите подъезд.',

			pickup: 'Самовывоз',

			courier: 'Курьерская доставка',

			express: 'Срочная доставка',

			cityRequired: 'Укажите населённый пункт.',

			addressRequired: 'Укажите улицу.',

			houseRequired: 'Укажите номер дома.',

			porchRequired: 'Укажите подъезд.'
		},

		ro: {
			selectDelivery: 'Selectați metoda de livrare.',

			selectStore: 'Selectați magazinul pentru ridicare.',

			fillAddress: 'Completați adresa de livrare.',

			fillEntrance: 'Indicați scara.',

			pickup: 'Ridicare din magazin',

			courier: 'Livrare prin curier',

			express: 'Livrare expres',

			cityRequired: 'Indicați localitatea.',

			addressRequired: 'Indicați strada.',

			houseRequired: 'Indicați numărul casei.',

			porchRequired: 'Indicați scara.'
		}
	}

	const text = translations[lang]

	const pickupBlock = root.querySelector('[data-delivery-pickup]')

	const addressBlock = root.querySelector('[data-delivery-address]')

	const continueButton = root.querySelector('[data-checkout-delivery-continue]')

	const errorElement = root.querySelector('[data-delivery-error]')

	const deliveryStep = root.querySelector('[data-checkout-step="delivery"]')

	if (!deliveryStep) {
		return
	}

	const stepContent = deliveryStep.querySelector('[data-checkout-step-content]')

	const stepSummary = deliveryStep.querySelector(
		'[data-checkout-step-summary="delivery"]'
	)

	const editButton = deliveryStep.querySelector(
		'[data-checkout-step-edit="delivery"]'
	)

	const summaryTitle = deliveryStep.querySelector(
		'[data-delivery-summary-title]'
	)

	const summaryText = deliveryStep.querySelector('[data-delivery-summary-text]')

	/* ======================================================================
	   HELPERS
	   ====================================================================== */

	function getDelivery() {
		const checked = deliveryInputs.find(function (input) {
			return input.checked
		})

		return checked ? String(checked.value) : ''
	}

	function getValue(name) {
		const input = root.querySelector('[name="' + name + '"]')

		return input ? String(input.value || '').trim() : ''
	}

	function setRequired(name, required) {
		const input = root.querySelector('[name="' + name + '"]')

		if (!input) {
			return
		}

		input.required = Boolean(required)
	}

	function showError(message) {
		if (!errorElement) {
			return
		}

		errorElement.textContent = message

		errorElement.hidden = false
	}

	function clearError() {
		if (!errorElement) {
			return
		}

		errorElement.textContent = ''

		errorElement.hidden = true
	}

	function setDeliveryFieldError(name, message) {
		const input = root.querySelector('[name="' + name + '"]')

		const error = root.querySelector(
			'[data-delivery-field-error="' + name + '"]'
		)

		const field = input?.closest('.vn-checkout-field')

		if (message) {
			field?.classList.add('has-error')

			if (error) {
				error.textContent = message

				error.hidden = false
			}

			return
		}

		field?.classList.remove('has-error')

		if (error) {
			error.textContent = ''

			error.hidden = true
		}
	}

	function validateDeliveryField(name, showError) {
		const delivery = getDelivery()

		let required = false

		let message = ''

		if (name === 'city' || name === 'address' || name === 'house') {
			required = delivery === '2' || delivery === '3'
		}

		// Подъезд необязателен: заказы могут оформляться из сёл.
		if (name === 'porch') {
			required = false
		}

		if (name === 'city') {
			message = text.cityRequired
		}

		if (name === 'address') {
			message = text.addressRequired
		}

		if (name === 'house') {
			message = text.houseRequired
		}

		if (name === 'porch') {
			message = text.porchRequired
		}

		if (!required) {
			setDeliveryFieldError(name, '')

			return true
		}

		const valid = getValue(name) !== ''

		if (showError && !valid) {
			setDeliveryFieldError(name, message)
		} else if (valid) {
			setDeliveryFieldError(name, '')
		}

		return valid
	}

	/* ======================================================================
	   STATE
	   ====================================================================== */

	function isDeliveryComplete() {
		const delivery = getDelivery()

		if (!delivery) {
			return false
		}

		if (delivery === '1') {
			const storeId = getValue('stores')

			return storeId !== '' && Number(storeId) > 0
		}

		const city = getValue('city')

		const address = getValue('address')

		const house = getValue('house')

		if (!city || !address || !house) {
			return false
		}

		return true
	}

	function updateContinueState() {
		if (!continueButton) {
			return
		}

		continueButton.disabled = !isDeliveryComplete()
	}

	function updateDeliveryState() {
		const delivery = getDelivery()

		const isPickup = delivery === '1'

		const isCourier = delivery === '2'

		const isUrgent = delivery === '3'

		const needsAddress = isCourier || isUrgent

		if (pickupBlock) {
			pickupBlock.hidden = !isPickup
		}

		if (addressBlock) {
			addressBlock.hidden = !needsAddress
		}

		setRequired('city', needsAddress)

		setRequired('address', needsAddress)

		setRequired('house', needsAddress)

		setRequired('porch', false)

		setRequired('apartament', false)

		clearError()

		updateContinueState()
	}

	/* ======================================================================
	   VALIDATION
	   ====================================================================== */

	function validateDelivery() {
		const delivery = getDelivery()

		if (!delivery) {
			showError(text.selectDelivery)

			return false
		}

		if (delivery === '1') {
			const storeId = getValue('stores')

			if (!storeId || Number(storeId) <= 0) {
				showError(text.selectStore)

				return false
			}

			return true
		}

		const cityValid = validateDeliveryField('city', true)

		const addressValid = validateDeliveryField('address', true)

		const houseValid = validateDeliveryField('house', true)

		const porchValid = validateDeliveryField('porch', false)

		if (!cityValid || !addressValid || !houseValid || !porchValid) {
			return false
		}

		return true
	}

	/* ======================================================================
	   SUMMARY
	   ====================================================================== */

	function updateDeliverySummary() {
		if (!summaryTitle || !summaryText) {
			return
		}

		const delivery = getDelivery()

		if (delivery === '1') {
			const storeTitle = root.querySelector('[data-selected-store-title]')

			const storeAddress = root.querySelector('[data-selected-store-address]')

			summaryTitle.textContent = text.pickup

			summaryText.textContent = [
				storeTitle?.textContent?.trim(),

				storeAddress?.textContent?.trim()
			]
				.filter(Boolean)
				.join(' · ')

			return
		}

		summaryTitle.textContent = delivery === '2' ? text.courier : text.express

		summaryText.textContent = [
			getValue('city'),
			getValue('address'),
			getValue('house')
		]
			.filter(Boolean)
			.join(', ')
	}

	/* ======================================================================
	   STEP
	   ====================================================================== */

	function collapseStep() {
		updateDeliverySummary()

		deliveryStep.classList.remove('is-open')

		deliveryStep.classList.add('is-complete')

		if (stepContent) {
			stepContent.hidden = true
		}

		if (stepSummary) {
			stepSummary.hidden = false
		}

		if (editButton) {
			editButton.hidden = false
		}

		const recipientStep = root.querySelector('[data-checkout-step="recipient"]')

		if (recipientStep) {
			recipientStep.classList.add('is-open')

			const recipientContent = recipientStep.querySelector(
				'[data-checkout-step-content]'
			)

			if (recipientContent) {
				recipientContent.hidden = false
			}

			window.setTimeout(function () {
				window.VNCheckoutScroll(recipientStep)
			}, 120)
		}
	}

	function openStep() {
		deliveryStep.classList.add('is-open')

		if (stepContent) {
			stepContent.hidden = false
		}

		if (stepSummary) {
			stepSummary.hidden = true
		}

		if (editButton) {
			editButton.hidden = true
		}
	}

	/* ======================================================================
	   EVENTS
	   ====================================================================== */

	deliveryInputs.forEach(function (input) {
		input.addEventListener('change', updateDeliveryState)
	})
	;['city', 'address', 'house', 'porch'].forEach(function (name) {
		const input = root.querySelector('[name="' + name + '"]')

		if (!input) {
			return
		}

		input.addEventListener('blur', function () {
			validateDeliveryField(name, true)
		})

		input.addEventListener('input', function () {
			if (String(input.value || '').trim() !== '') {
				validateDeliveryField(name, false)
			}
		})
	})

	root.addEventListener('input', function (event) {
		if (
			!event.target.matches(
				[
					'input[name="city"]',
					'input[name="address"]',
					'input[name="house"]',
					'input[name="porch"]',
					'input[name="apartament"]'
				].join(',')
			)
		) {
			return
		}

		clearError()

		updateContinueState()
	})

	root.addEventListener('change', function (event) {
		if (event.target.matches('input[name="stores"]')) {
			updateContinueState()
		}
	})

	deliveryStep.addEventListener('click', function (event) {
		if (!deliveryStep.classList.contains('is-complete')) {
			return
		}

		const openTarget = event.target.closest('[data-checkout-step-open]')

		if (!openTarget) {
			return
		}

		openStep()

		window.setTimeout(function () {
			window.VNCheckoutScroll(deliveryStep)
		}, 40)
	})

	if (continueButton) {
		continueButton.addEventListener('click', function () {
			if (!validateDelivery()) {
				return
			}

			clearError()

			collapseStep()
		})
	}

	/* ======================================================================
	   INIT
	   ====================================================================== */

	updateDeliveryState()
})()

/* ==========================================================================
   END CHECKOUT V2 — DELIVERY
   ========================================================================== */
