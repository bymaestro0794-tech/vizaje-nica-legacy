/* ==========================================================================
   CHECKOUT V2 — SCROLL
   ========================================================================== */

window.VNCheckoutScroll = function (element) {
	if (!element) {
		return
	}

	const reducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches

	const headerOffset = window.innerWidth <= 767 ? 82 : 105

	const top =
		element.getBoundingClientRect().top + window.pageYOffset - headerOffset

	window.scrollTo({
		top: Math.max(0, top),
		behavior: reducedMotion ? 'auto' : 'smooth'
	})
}

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

		if (name === 'porch') {
			required = delivery === '2'
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

		if (delivery === '2' && !getValue('porch')) {
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

		setRequired('porch', isCourier)

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

		const porchValid = validateDeliveryField('porch', true)

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

/* ==========================================================================
   CHECKOUT V2 — RECIPIENT
   ========================================================================== */
;(function () {
	'use strict'

	const root =
		document.querySelector('form.order__body[data-checkout]') ||
		document.querySelector('form.order__body')

	if (!root) {
		return
	}

	const lang = document.documentElement.lang === 'ro' ? 'ro' : 'ru'

	const recipientStep = root.querySelector('[data-checkout-step="recipient"]')

	if (!recipientStep) {
		return
	}

	const recipientContent = recipientStep.querySelector(
		'[data-checkout-step-content]'
	)

	const recipientSummary = recipientStep.querySelector(
		'[data-checkout-step-summary="recipient"]'
	)

	const recipientEditButton = recipientStep.querySelector(
		'[data-checkout-step-edit="recipient"]'
	)

	const recipientContinueButton = recipientStep.querySelector(
		'[data-checkout-recipient-continue]'
	)

	const recipientError = recipientStep.querySelector('[data-recipient-error]')

	const recipientSummaryName = recipientStep.querySelector(
		'[data-recipient-summary-name]'
	)

	const recipientSummaryContact = recipientStep.querySelector(
		'[data-recipient-summary-contact]'
	)

	const phoneRoot = recipientStep.querySelector('[data-checkout-phone]')

	const phoneInput = phoneRoot?.querySelector('[data-checkout-phone-input]')

	const phoneHidden = phoneRoot?.querySelector('[data-checkout-phone-value]')

	const phoneConfirm = phoneRoot?.querySelector('[data-checkout-phone-confirm]')

	const phoneError = phoneRoot?.querySelector('[data-checkout-phone-error]')

	const nameInput = recipientStep.querySelector('input[name="name"]')

	const surnameInput = recipientStep.querySelector('input[name="surname"]')

	const nameError = recipientStep.querySelector('[data-name-error]')

	const surnameError = recipientStep.querySelector('[data-surname-error]')

	const termsInput = recipientStep.querySelector('input[name="checkbox"]')

	const termsError = recipientStep.querySelector('[data-recipient-terms-error]')

	/* ======================================================================
	   PHONE
	   ====================================================================== */

	function normalizeMoldovaPhone(value) {
		let digits = String(value || '').replace(/\D/g, '')

		/*
		 * Пользователь по привычке вводит 373,
		 * хотя +373 уже показан отдельно.
		 */
		if (digits.indexOf('373') === 0) {
			digits = digits.slice(3)
		}

		/*
		 * Пользователь вводит:
		 * 067...
		 */
		if (digits.charAt(0) === '0') {
			digits = digits.slice(1)
		}

		return digits.slice(0, 8)
	}

	function normalizeExistingPhone(value) {
		let digits = String(value || '').replace(/\D/g, '')

		if (digits.indexOf('373') === 0) {
			digits = digits.slice(3)
		}

		if (digits.length === 9 && digits.charAt(0) === '0') {
			digits = digits.slice(1)
		}

		return digits.slice(0, 8)
	}

	function isValidMoldovaPhone(digits) {
		return /^[67]\d{7}$/.test(String(digits || ''))
	}

	function formatMoldovaPhone(digits) {
		const value = String(digits || '')

		return [value.slice(0, 2), value.slice(2, 5), value.slice(5, 8)]
			.filter(Boolean)
			.join(' ')
	}

	/* ======================================================================
	   HELPERS
	   ====================================================================== */

	function setRecipientError(message) {
		if (!recipientError) {
			return
		}

		recipientError.textContent = message

		recipientError.hidden = !message
	}

	function getRecipientValue(name) {
		const input = recipientStep.querySelector('[name="' + name + '"]')

		return input ? String(input.value || '').trim() : ''
	}

	function isRecipientComplete() {
		const name = getRecipientValue('name')

		const surname = getRecipientValue('surname')

		const email = getRecipientValue('email')

		const phone = String(phoneHidden?.value || '').replace(/\D/g, '')

		const terms = Boolean(
			recipientStep.querySelector('[name="checkbox"]')?.checked
		)

		const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)

		const phoneValid = /^373[67]\d{7}$/.test(phone)

		return (
			name.length >= 2 &&
			surname.length >= 2 &&
			emailValid &&
			phoneValid &&
			terms
		)
	}

	function updateRecipientContinueState() {
		if (!recipientContinueButton) {
			return
		}

		recipientContinueButton.disabled = !isRecipientComplete()
	}

	function setFieldError(input, errorElement, message) {
		if (!input) {
			return
		}

		const field = input.closest('.vn-checkout-field')

		if (message) {
			field?.classList.add('has-error')

			if (errorElement) {
				errorElement.textContent = message

				errorElement.hidden = false
			}

			return
		}

		field?.classList.remove('has-error')

		if (errorElement) {
			errorElement.textContent = ''

			errorElement.hidden = true
		}
	}

	function validateName(showError) {
		const value = String(nameInput?.value || '').trim()

		const valid = value.length >= 2

		if (showError && !valid) {
			setFieldError(
				nameInput,
				nameError,
				lang === 'ro' ? 'Introduceți prenumele.' : 'Введите имя.'
			)
		} else {
			setFieldError(nameInput, nameError, '')
		}

		return valid
	}

	function validateSurname(showError) {
		const value = String(surnameInput?.value || '').trim()

		const valid = value.length >= 2

		if (showError && !valid) {
			setFieldError(
				surnameInput,
				surnameError,
				lang === 'ro' ? 'Introduceți numele.' : 'Введите фамилию.'
			)
		} else {
			setFieldError(surnameInput, surnameError, '')
		}

		return valid
	}

	/* ======================================================================
	   PHONE STATE
	   ====================================================================== */

	function updatePhoneState() {
		if (!phoneInput || !phoneHidden) {
			return
		}

		const digits = normalizeMoldovaPhone(phoneInput.value)

		/*
		 * Неверная первая цифра.
		 */
		if (
			digits.length > 0 &&
			digits.charAt(0) !== '6' &&
			digits.charAt(0) !== '7'
		) {
			phoneHidden.value = ''

			if (phoneConfirm) {
				phoneConfirm.hidden = true
			}

			if (phoneError) {
				phoneError.textContent =
					lang === 'ro'
						? 'Numărul mobil trebuie să înceapă cu 6 sau 7.'
						: 'Мобильный номер должен начинаться с 6 или 7.'

				phoneError.hidden = false
			}

			updateRecipientContinueState()

			return
		}

		phoneInput.value = formatMoldovaPhone(digits)

		const valid = isValidMoldovaPhone(digits)

		/*
		 * Номер ещё просто не дописан.
		 */
		if (!valid) {
			phoneHidden.value = ''

			if (phoneConfirm) {
				phoneConfirm.hidden = true
			}

			if (phoneError) {
				phoneError.hidden = true
			}

			updateRecipientContinueState()

			return
		}

		const fullPhone = '+373' + digits

		phoneHidden.value = fullPhone

		if (phoneConfirm) {
			phoneConfirm.textContent =
				(lang === 'ro' ? 'Telefonul dvs.: ' : 'Ваш телефон: ') +
				'+373 ' +
				formatMoldovaPhone(digits)

			phoneConfirm.hidden = false
		}

		if (phoneError) {
			phoneError.hidden = true
		}

		updateRecipientContinueState()
	}

	/* ======================================================================
	   PHONE BLUR VALIDATION
	   ====================================================================== */

	if (phoneInput) {
		phoneInput.addEventListener('blur', function () {
			const digits = normalizeMoldovaPhone(phoneInput.value)

			if (!digits.length) {
				if (phoneError) {
					phoneError.textContent =
						lang === 'ro'
							? 'Introduceți numărul de telefon.'
							: 'Введите номер телефона.'

					phoneError.hidden = false
				}

				return
			}

			if (!isValidMoldovaPhone(digits)) {
				if (phoneError) {
					phoneError.textContent =
						lang === 'ro'
							? 'Introduceți numărul complet de telefon.'
							: 'Введите полный номер телефона.'

					phoneError.hidden = false
				}

				return
			}

			if (phoneError) {
				phoneError.textContent = ''
				phoneError.hidden = true
			}
		})
	}

	/* ======================================================================
	   SUMMARY
	   ====================================================================== */

	function updateRecipientSummary() {
		if (!recipientSummaryName || !recipientSummaryContact) {
			return
		}

		const name = getRecipientValue('name')

		const surname = getRecipientValue('surname')

		const email = getRecipientValue('email')

		const phone = phoneHidden ? phoneHidden.value : ''

		recipientSummaryName.textContent = [name, surname].filter(Boolean).join(' ')

		recipientSummaryContact.textContent = [phone, email]
			.filter(Boolean)
			.join(' · ')
	}

	/* ======================================================================
	   STEP
	   ====================================================================== */

	function openRecipientStep() {
		recipientStep.classList.add('is-open')

		if (recipientContent) {
			recipientContent.hidden = false
		}

		if (recipientSummary) {
			recipientSummary.hidden = true
		}

		if (recipientEditButton) {
			recipientEditButton.hidden = true
		}
	}

	function collapseRecipientStep() {
		updateRecipientSummary()

		recipientStep.classList.remove('is-open')

		recipientStep.classList.add('is-complete')

		if (recipientContent) {
			recipientContent.hidden = true
		}

		if (recipientSummary) {
			recipientSummary.hidden = false
		}

		if (recipientEditButton) {
			recipientEditButton.hidden = false
		}

		/*
		 * Позже здесь откроем
		 * PAYMENT 03 / 03.
		 */
		const paymentStep = root.querySelector('[data-checkout-step="payment"]')

		if (paymentStep) {
			paymentStep.classList.add('is-open')

			const paymentContent = paymentStep.querySelector(
				'[data-checkout-step-content]'
			)

			if (paymentContent) {
				paymentContent.hidden = false
			}

			window.setTimeout(function () {
				window.VNCheckoutScroll(paymentStep)
			}, 120)
		}
	}

	/* ======================================================================
	   EVENTS
	   ====================================================================== */

	recipientStep.addEventListener('input', function (event) {
		if (event.target === phoneInput) {
			updatePhoneState()

			updateTermsState()

			return
		}

		setRecipientError('')

		updateRecipientContinueState()

		updateTermsState()
	})

	recipientStep.addEventListener('change', function () {
		setRecipientError('')

		updateRecipientContinueState()

		updateTermsState()
	})

	recipientStep.addEventListener('click', function (event) {
		if (!recipientStep.classList.contains('is-complete')) {
			return
		}

		if (!event.target.closest('[data-checkout-step-open]')) {
			return
		}

		openRecipientStep()

		window.setTimeout(function () {
			window.VNCheckoutScroll(recipientStep)
		}, 40)
	})

	if (recipientContinueButton) {
		recipientContinueButton.addEventListener('click', function () {
			if (!isRecipientComplete()) {
				setRecipientError(
					lang === 'ro'
						? 'Verificați datele destinatarului.'
						: 'Проверьте данные получателя.'
				)

				return
			}

			setRecipientError('')

			collapseRecipientStep()
		})
	}

	/* ======================================================================
	   EXISTING PHONE INIT
	   ====================================================================== */

	if (phoneInput && phoneHidden && phoneHidden.value) {
		const existingDigits = normalizeExistingPhone(phoneHidden.value)

		if (isValidMoldovaPhone(existingDigits)) {
			phoneInput.value = formatMoldovaPhone(existingDigits)

			updatePhoneState()
		} else {
			/*
			 * Старое значение клиента
			 * не считаем валидным.
			 */
			phoneHidden.value = ''
		}
	}

	if (nameInput) {
		nameInput.addEventListener('blur', function () {
			validateName(true)
		})

		nameInput.addEventListener('input', function () {
			if (String(nameInput.value || '').trim().length >= 2) {
				validateName(false)
			}
		})
	}

	if (surnameInput) {
		surnameInput.addEventListener('blur', function () {
			validateSurname(true)
		})

		surnameInput.addEventListener('input', function () {
			if (String(surnameInput.value || '').trim().length >= 2) {
				validateSurname(false)
			}
		})
	}

	function updateTermsState() {
		if (!termsInput || !termsError) {
			return
		}

		const otherFieldsValid =
			String(nameInput?.value || '').trim().length >= 2 &&
			String(surnameInput?.value || '').trim().length >= 2 &&
			/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(getRecipientValue('email')) &&
			/^373[67]\d{7}$/.test(String(phoneHidden?.value || '').replace(/\D/g, ''))

		if (otherFieldsValid && !termsInput.checked) {
			termsError.textContent =
				lang === 'ro'
					? 'Acceptați termenii și condițiile pentru a continua.'
					: 'Согласитесь с условиями и правилами, чтобы продолжить.'

			termsError.hidden = false

			return
		}

		termsError.hidden = true
	}

	/* ======================================================================
	   INIT
	   ====================================================================== */

	updateRecipientContinueState()
})()

/* ==========================================================================
   END CHECKOUT V2 — RECIPIENT
   ========================================================================== */
/* ==========================================================================
   CHECKOUT V2 — PAYMENT
   ========================================================================== */
;(function () {
	'use strict'

	const root =
		document.querySelector('form.order__body[data-checkout]') ||
		document.querySelector('form.order__body')

	if (!root) {
		return
	}

	const lang = document.documentElement.lang === 'ro' ? 'ro' : 'ru'

	const paymentStep = root.querySelector('[data-checkout-step="payment"]')

	if (!paymentStep) {
		return
	}

	const paymentInputs = Array.from(
		paymentStep.querySelectorAll('[data-payment-option]')
	)

	const paymentContent = paymentStep.querySelector(
		'[data-checkout-step-content]'
	)

	const paymentSummary = paymentStep.querySelector(
		'[data-checkout-step-summary="payment"]'
	)

	const paymentEditButton = paymentStep.querySelector(
		'[data-checkout-step-edit="payment"]'
	)

	const paymentContinueButton = paymentStep.querySelector(
		'[data-checkout-payment-continue]'
	)

	const paymentError = paymentStep.querySelector('[data-payment-error]')

	const paymentSummaryTitle = paymentStep.querySelector(
		'[data-payment-summary-title]'
	)

	const paymentSummaryText = paymentStep.querySelector(
		'[data-payment-summary-text]'
	)

	const translations = {
		ru: {
			cash: 'Наличными при получении',

			cardDelivery: 'Картой при получении',

			online: 'Оплата онлайн',

			cashDescription: 'Оплата при получении заказа',

			cardDeliveryDescription: 'Оплата через терминал',

			onlineDescription: 'Visa · Mastercard · Apple Pay · Google Pay',

			selectPayment: 'Выберите способ оплаты.'
		},

		ro: {
			cash: 'Numerar la primire',

			cardDelivery: 'Cu cardul la curier',

			online: 'Plată online',

			cashDescription: 'Plată la primirea comenzii',

			cardDeliveryDescription: 'Plată prin terminal',

			onlineDescription: 'Visa · Mastercard · Apple Pay · Google Pay',

			selectPayment: 'Selectați metoda de plată.'
		}
	}

	const text = translations[lang]

	/* ======================================================================
	   HELPERS
	   ====================================================================== */

	function getPayment() {
		const checked = paymentInputs.find(function (input) {
			return input.checked
		})

		return checked ? String(checked.value) : ''
	}

	function isPaymentComplete() {
		return ['1', '2', '3'].includes(getPayment())
	}

	function updatePaymentContinueState() {
		if (!paymentContinueButton) {
			return
		}

		paymentContinueButton.disabled = !isPaymentComplete()
	}

	function setPaymentError(message) {
		if (!paymentError) {
			return
		}

		paymentError.textContent = message

		paymentError.hidden = !message
	}

	/* ======================================================================
	   SUMMARY
	   ====================================================================== */

	function updatePaymentSummary() {
		if (!paymentSummaryTitle || !paymentSummaryText) {
			return
		}

		const payment = getPayment()

		if (payment === '1') {
			paymentSummaryTitle.textContent = text.cash

			paymentSummaryText.textContent = text.cashDescription

			return
		}

		if (payment === '3') {
			paymentSummaryTitle.textContent = text.cardDelivery

			paymentSummaryText.textContent = text.cardDeliveryDescription

			return
		}

		paymentSummaryTitle.textContent = text.online

		paymentSummaryText.textContent = text.onlineDescription
	}

	/* ======================================================================
	   STEP
	   ====================================================================== */

	function openPaymentStep() {
		paymentStep.classList.add('is-open')

		if (paymentContent) {
			paymentContent.hidden = false
		}

		if (paymentSummary) {
			paymentSummary.hidden = true
		}

		if (paymentEditButton) {
			paymentEditButton.hidden = true
		}
	}

	function collapsePaymentStep() {
		updatePaymentSummary()

		paymentStep.classList.remove('is-open')

		paymentStep.classList.add('is-complete')

		if (paymentContent) {
			paymentContent.hidden = true
		}

		if (paymentSummary) {
			paymentSummary.hidden = false
		}

		if (paymentEditButton) {
			paymentEditButton.hidden = false
		}
	}

	/* ======================================================================
	   EVENTS
	   ====================================================================== */

	paymentInputs.forEach(function (input) {
		input.addEventListener('change', function () {
			setPaymentError('')

			updatePaymentContinueState()
		})
	})

	paymentStep.addEventListener('click', function (event) {
		if (!paymentStep.classList.contains('is-complete')) {
			return
		}

		if (!event.target.closest('[data-checkout-step-open]')) {
			return
		}

		openPaymentStep()

		window.setTimeout(function () {
			window.VNCheckoutScroll(paymentStep)
		}, 40)
	})

	if (paymentContinueButton) {
		paymentContinueButton.addEventListener('click', function () {
			if (!isPaymentComplete()) {
				setPaymentError(text.selectPayment)

				return
			}

			setPaymentError('')

			collapsePaymentStep()
		})
	}

	/* ======================================================================
	   INIT
	   ====================================================================== */

	updatePaymentContinueState()
})()

/* ==========================================================================
   END CHECKOUT V2 — PAYMENT
   ========================================================================== */
/* ==========================================================================
   CHECKOUT V2 — BONUS
   ========================================================================== */
;(function () {
	'use strict'

	const root =
		document.querySelector('form.order__body[data-checkout]') ||
		document.querySelector('form.order__body')

	if (!root) {
		return
	}

	const bonus = root.querySelector('[data-checkout-bonus]')

	if (!bonus) {
		return
	}

	const modes = Array.from(bonus.querySelectorAll('input[name="bonus"]'))

	const writeOffBlock = bonus.querySelector('[data-bonus-writeoff]')

	const amountInput = bonus.querySelector('[data-bonus-amount]')

	const rangeInput = bonus.querySelector('[data-bonus-range]')

	const maxButton = bonus.querySelector('[data-bonus-max-button]')

	const maxWriteOff = Math.max(
		0,
		Math.floor(Number(bonus.dataset.bonusMax || 0))
	)

	function getMode() {
		const selected = modes.find(function (input) {
			return input.checked
		})

		return selected ? String(selected.value) : ''
	}

	function clampAmount(value) {
		return Math.min(maxWriteOff, Math.max(0, Math.floor(Number(value) || 0)))
	}

	function setAmount(value) {
		const amount = clampAmount(value)

		if (amountInput) {
			amountInput.value = String(amount)
		}

		if (rangeInput) {
			rangeInput.value = String(amount)
		}

		root.dispatchEvent(
			new CustomEvent('checkout:bonus-change', {
				bubbles: true,

				detail: {
					mode: getMode(),

					amount: amount
				}
			})
		)
	}

	function updateMode() {
		const mode = getMode()

		const isWriteOff = mode === '2'

		if (writeOffBlock) {
			writeOffBlock.hidden = !isWriteOff
		}

		/*
		 * При накоплении гарантированно
		 * отправляем bonus_amount=0.
		 */
		if (!isWriteOff) {
			setAmount(0)

			return
		}

		/*
		 * При первом переключении на
		 * списание оставляем 0.
		 * Пользователь сам выбирает сумму.
		 */
		setAmount(amountInput ? amountInput.value : 0)
	}

	modes.forEach(function (input) {
		input.addEventListener('change', updateMode)
	})

	if (amountInput) {
		amountInput.addEventListener('input', function () {
			setAmount(amountInput.value)
		})

		amountInput.addEventListener('blur', function () {
			setAmount(amountInput.value)
		})
	}

	if (rangeInput) {
		rangeInput.addEventListener('input', function () {
			setAmount(rangeInput.value)
		})
	}

	if (maxButton) {
		maxButton.addEventListener('click', function () {
			setAmount(maxWriteOff)
		})
	}

	updateMode()
})()

/* ==========================================================================
   END CHECKOUT V2 — BONUS
   ========================================================================== */
/* ==========================================================================
   CHECKOUT V2 — SUMMARY
   ========================================================================== */
;(function () {
	'use strict'

	const root =
		document.querySelector('form.order__body[data-checkout]') ||
		document.querySelector('form.order__body')

	if (!root) {
		return
	}

	const summary = root.querySelector('[data-checkout-summary]')

	if (!summary) {
		return
	}

	const deliveryValue = summary.querySelector('[data-summary-delivery]')

	const totalValue = summary.querySelector('[data-summary-total]')

	const bonusRow = summary.querySelector('[data-summary-bonus-row]')

	const bonusValue = summary.querySelector('[data-summary-bonus]')

	const bonusEarn = summary.querySelector('[data-summary-bonus-earn]')

	const lang = document.documentElement.lang === 'ro' ? 'ro' : 'ru'

	const cartTotal = Number(summary.dataset.cartTotal || 0)

	const courierPrice = Number(summary.dataset.courierPrice || 0)

	const expressPrice = Number(summary.dataset.expressPrice || 100)

	const maxBonusWriteOff = Number(summary.dataset.bonusWriteoff || 0)

	function formatMoney(value) {
		const number = Math.max(0, Number(value) || 0)

		return (
			new Intl.NumberFormat(lang === 'ro' ? 'ro-MD' : 'ru-RU', {
				maximumFractionDigits: 2
			}).format(number) + ' MDL'
		)
	}

	function getDelivery() {
		return String(
			root.querySelector('input[name="delivery"]:checked')?.value || ''
		)
	}

	function getDeliveryPrice() {
		const delivery = getDelivery()

		if (delivery === '2') {
			return courierPrice
		}

		if (delivery === '3') {
			return expressPrice
		}

		return 0
	}

	function getBonusMode() {
		const selected = root.querySelector('input[name="bonus"]:checked')

		if (!selected) {
			return {
				type: 'none',
				amount: 0
			}
		}

		const mode = String(selected.value || '')

		if (mode === '2') {
			const amountInput = root.querySelector('input[name="bonus_amount"]')

			const requestedAmount = Math.max(
				0,
				Math.floor(Number(amountInput?.value || 0))
			)

			return {
				type: 'writeoff',

				amount: Math.min(requestedAmount, maxBonusWriteOff)
			}
		}

		if (mode === '1') {
			return {
				type: 'earn',
				amount: 0
			}
		}

		return {
			type: 'none',
			amount: 0
		}
	}

	function updateDelivery() {
		if (!deliveryValue) {
			return
		}

		const delivery = getDelivery()

		if (!delivery) {
			deliveryValue.textContent = '—'

			return
		}

		const price = getDeliveryPrice()

		if (price <= 0) {
			deliveryValue.textContent = lang === 'ro' ? 'Gratuit' : 'Бесплатно'

			return
		}

		deliveryValue.textContent = formatMoney(price)
	}

	function updateBonus() {
		const bonus = getBonusMode()

		if (bonusRow) {
			bonusRow.hidden = bonus.type !== 'writeoff'
		}

		if (bonus.type === 'writeoff' && bonusValue) {
			bonusValue.textContent = '−' + formatMoney(bonus.amount)
		}

		if (bonusEarn) {
			bonusEarn.hidden = bonus.type !== 'earn'
		}
	}

	function updateTotal() {
		if (!totalValue) {
			return
		}

		const delivery = getDeliveryPrice()

		const bonus = getBonusMode()

		const bonusWriteOff = bonus.type === 'writeoff' ? bonus.amount : 0

		const total = cartTotal + delivery - bonusWriteOff

		totalValue.textContent = formatMoney(total)
	}

	function updateSummary() {
		updateDelivery()

		updateBonus()

		updateTotal()
	}

	root.addEventListener('change', function (event) {
		if (
			event.target.matches(
				['input[name="delivery"]', 'input[name="bonus"]'].join(',')
			)
		) {
			updateSummary()
		}
	})
	root.addEventListener('checkout:bonus-change', function () {
		updateSummary()
	})
	root.addEventListener('input', function (event) {
		if (event.target.matches('input[name="bonus_amount"]')) {
			updateSummary()
		}
	})

	updateSummary()
})()

/* ==========================================================================
   END CHECKOUT V2 — SUMMARY
   ========================================================================== */

/* ==========================================================================
   CHECKOUT V2 — STATE & DRAFT
   ========================================================================== */
;(function () {
	'use strict'

	const form =
		document.querySelector('form.order__body[data-checkout]') ||
		document.querySelector('form.order__body')

	if (!form) {
		return
	}

	const root = form

	const lang = document.documentElement.lang === 'ro' ? 'ro' : 'ru'

	const STORAGE_KEY = 'vizaje_checkout_draft_v2'

	const deliveryStep = form.querySelector('[data-checkout-step="delivery"]')

	const recipientStep = form.querySelector('[data-checkout-step="recipient"]')

	const paymentStep = form.querySelector('[data-checkout-step="payment"]')

	const submitButton = form.querySelector('[data-checkout-submit]')

	const submitText = form.querySelector('[data-checkout-submit-text]')

	const submitHint = form.querySelector('[data-checkout-submit-hint]')

	const emailInput = form.querySelector('input[name="email"]')

	const emailError = form.querySelector('[data-email-error]')

	const termsInput = form.querySelector('input[name="checkbox"]')

	const termsError = form.querySelector('[data-recipient-terms-error]')

	let restoring = false

	let submitting = false

	/* ======================================================================
	   TRANSLATIONS
	   ====================================================================== */

	const translations = {
		ru: {
			delivery: 'Выберите способ доставки',

			recipient: 'Заполните данные получателя',

			payment: 'Выберите способ оплаты',

			ready: 'Все данные заполнены',

			email: 'Введите корректный адрес электронной почты.',

			terms: 'Необходимо согласиться с условиями и правилами.',

			submitting: 'Оформляем заказ…'
		},

		ro: {
			delivery: 'Selectați metoda de livrare',

			recipient: 'Completați datele destinatarului',

			payment: 'Selectați metoda de plată',

			ready: 'Toate datele sunt completate',

			email: 'Introduceți o adresă de email validă.',

			terms: 'Este necesar să acceptați termenii și condițiile.',

			submitting: 'Procesăm comanda…'
		}
	}

	const text = translations[lang]

	/* ======================================================================
	   HELPERS
	   ====================================================================== */

	function getInput(name) {
		return form.querySelector('[name="' + name + '"]')
	}

	function getValue(name) {
		const input = getInput(name)

		return input ? String(input.value || '').trim() : ''
	}

	function isValidEmail(value) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim())
	}

	function isValidPhone() {
		const phone = getValue('phone').replace(/\D/g, '')

		return /^373[67]\d{7}$/.test(phone)
	}

	function scrollToStep(step) {
		window.VNCheckoutScroll(step)
	}

	/* ======================================================================
	   STEP VALIDITY
	   ====================================================================== */

	function isDeliveryComplete() {
		const delivery = form.querySelector('input[name="delivery"]:checked')?.value

		if (!delivery) {
			return false
		}

		if (String(delivery) === '1') {
			return Number(getValue('stores')) > 0
		}

		if (!getValue('city') || !getValue('address') || !getValue('house')) {
			return false
		}

		if (String(delivery) === '2' && !getValue('porch')) {
			return false
		}

		return true
	}

	function isRecipientComplete() {
		return (
			getValue('name').length >= 2 &&
			getValue('surname').length >= 2 &&
			isValidEmail(getValue('email')) &&
			isValidPhone() &&
			Boolean(termsInput?.checked)
		)
	}

	function isPaymentComplete() {
		return Boolean(form.querySelector('input[name="pay"]:checked'))
	}

	function getCheckoutState() {
		const delivery = isDeliveryComplete()

		const recipient = isRecipientComplete()

		const payment = isPaymentComplete()

		return {
			delivery: delivery,
			recipient: recipient,
			payment: payment,
			complete: delivery && recipient && payment
		}
	}

	/* ======================================================================
	   SIDEBAR CTA
	   ====================================================================== */

	function updateSubmitState() {
		if (!submitButton) {
			return
		}

		const state = getCheckoutState()

		if (submitting) {
			submitButton.disabled = true

			return
		}

		submitButton.disabled = !state.complete

		if (!submitHint) {
			return
		}

		if (!state.delivery) {
			submitHint.textContent = text.delivery

			return
		}

		if (!state.recipient) {
			submitHint.textContent = text.recipient

			return
		}

		if (!state.payment) {
			submitHint.textContent = text.payment

			return
		}

		submitHint.textContent = text.ready
	}

	/* ======================================================================
	   EMAIL VALIDATION
	   ====================================================================== */

	function validateEmail(showError) {
		if (!emailInput) {
			return true
		}

		const value = String(emailInput.value || '').trim()

		const valid = isValidEmail(value)

		const field = emailInput.closest('.vn-checkout-field')

		if (showError && value === '') {
			field?.classList.add('has-error')

			if (emailError) {
				emailError.textContent =
					lang === 'ro' ? 'Introduceți adresa de email.' : 'Введите email.'

				emailError.hidden = false
			}

			return false
		}

		if (showError && !valid) {
			field?.classList.add('has-error')

			if (emailError) {
				emailError.textContent =
					lang === 'ro'
						? 'Introduceți o adresă de email validă.'
						: 'Введите корректный адрес электронной почты.'

				emailError.hidden = false
			}

			return false
		}

		field?.classList.remove('has-error')

		if (emailError) {
			emailError.textContent = ''
			emailError.hidden = true
		}

		return valid
	}

	if (emailInput) {
		emailInput.addEventListener('input', function () {
			validateEmail(false)

			updateSubmitState()

			saveDraft()
		})

		emailInput.addEventListener('blur', function () {
			validateEmail(true)
		})
	}

	/* ======================================================================
	   TERMS VALIDATION
	   ====================================================================== */

	function validateTerms(showError) {
		if (!termsInput) {
			return true
		}

		const valid = Boolean(termsInput.checked)

		if (termsError && showError && !valid) {
			termsError.textContent = text.terms

			termsError.hidden = false
		} else if (termsError) {
			termsError.hidden = true
		}

		return valid
	}

	if (termsInput) {
		termsInput.addEventListener('change', function () {
			validateTerms(false)

			updateSubmitState()

			saveDraft()
		})
	}

	/* ======================================================================
	   DRAFT
	   ====================================================================== */

	function collectDraft() {
		const delivery =
			form.querySelector('input[name="delivery"]:checked')?.value || ''

		const payment = form.querySelector('input[name="pay"]:checked')?.value || ''

		const bonus = form.querySelector('input[name="bonus"]:checked')?.value || ''

		return {
			delivery: delivery,

			stores: getValue('stores'),

			storeTitle:
				form
					.querySelector('[data-selected-store-title]')
					?.textContent?.trim() || '',

			storeAddress:
				form
					.querySelector('[data-selected-store-address]')
					?.textContent?.trim() || '',

			city: getValue('city'),

			address: getValue('address'),

			house: getValue('house'),

			porch: getValue('porch'),

			apartament: getValue('apartament'),

			name: getValue('name'),

			surname: getValue('surname'),

			phone: getValue('phone'),

			email: getValue('email'),

			message: getValue('message'),

			checkbox: Boolean(getInput('checkbox')?.checked),

			newsletters: Boolean(getInput('newsletters')?.checked),

			pay: payment,

			bonus: bonus,

			bonusAmount: getValue('bonus_amount'),

			steps: {
				delivery: deliveryStep?.classList.contains('is-complete') || false,

				recipient: recipientStep?.classList.contains('is-complete') || false,

				payment: paymentStep?.classList.contains('is-complete') || false
			}
		}
	}

	function saveDraft() {
		if (restoring || submitting) {
			return
		}

		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(collectDraft()))
		} catch (error) {
			console.warn('[Checkout draft]', error)
		}
	}

	function getDraft() {
		try {
			const raw = localStorage.getItem(STORAGE_KEY)

			if (!raw) {
				return null
			}

			const draft = JSON.parse(raw)

			return draft && typeof draft === 'object' ? draft : null
		} catch (error) {
			return null
		}
	}

	function setValue(name, value) {
		const input = getInput(name)

		if (!input) {
			return
		}

		input.value = value ?? ''
	}

	function checkRadio(name, value) {
		if (value === '' || value === null || value === undefined) {
			return
		}

		const input = form.querySelector(
			'input[name="' + name + '"][value="' + CSS.escape(String(value)) + '"]'
		)

		if (!input) {
			return
		}

		input.checked = true

		input.dispatchEvent(
			new Event('change', {
				bubbles: true
			})
		)
	}

	function restoreStep(step, complete) {
		if (!step) {
			return
		}

		const content = step.querySelector('[data-checkout-step-content]')

		const summary = step.querySelector('[data-checkout-step-summary]')

		const edit = step.querySelector('[data-checkout-step-edit]')

		if (complete) {
			step.classList.remove('is-open')

			step.classList.add('is-complete')

			if (content) {
				content.hidden = true
			}

			if (summary) {
				summary.hidden = false
			}

			if (edit) {
				edit.hidden = false
			}

			return
		}

		step.classList.remove('is-complete')
	}

	function restoreVisualSummaries() {
		/* ==============================================================
	   DELIVERY
	   ============================================================== */

		const delivery = String(
			form.querySelector('input[name="delivery"]:checked')?.value || ''
		)

		const deliverySummaryTitle = form.querySelector(
			'[data-delivery-summary-title]'
		)

		const deliverySummaryText = form.querySelector(
			'[data-delivery-summary-text]'
		)

		if (deliverySummaryTitle && deliverySummaryText) {
			if (delivery === '1') {
				deliverySummaryTitle.textContent =
					lang === 'ro' ? 'Ridicare din magazin' : 'Самовывоз'

				const storeTitle =
					form
						.querySelector('[data-selected-store-title]')
						?.textContent?.trim() || ''

				const storeAddress =
					form
						.querySelector('[data-selected-store-address]')
						?.textContent?.trim() || ''

				deliverySummaryText.textContent = [storeTitle, storeAddress]
					.filter(Boolean)
					.join(' · ')
			}

			if (delivery === '2') {
				deliverySummaryTitle.textContent =
					lang === 'ro' ? 'Livrare prin curier' : 'Курьерская доставка'

				deliverySummaryText.textContent = [
					getValue('city'),
					getValue('address'),
					getValue('house')
				]
					.filter(Boolean)
					.join(', ')
			}

			if (delivery === '3') {
				deliverySummaryTitle.textContent =
					lang === 'ro' ? 'Livrare expres' : 'Срочная доставка'

				deliverySummaryText.textContent = [
					getValue('city'),
					getValue('address'),
					getValue('house')
				]
					.filter(Boolean)
					.join(', ')
			}
		}

		/* ==============================================================
	   RECIPIENT
	   ============================================================== */

		const recipientSummaryName = form.querySelector(
			'[data-recipient-summary-name]'
		)

		const recipientSummaryContact = form.querySelector(
			'[data-recipient-summary-contact]'
		)

		if (recipientSummaryName && recipientSummaryContact) {
			recipientSummaryName.textContent = [getValue('name'), getValue('surname')]
				.filter(Boolean)
				.join(' ')

			recipientSummaryContact.textContent = [
				getValue('phone'),
				getValue('email')
			]
				.filter(Boolean)
				.join(' · ')
		}

		/* ==============================================================
	   PAYMENT
	   ============================================================== */

		const payment = String(
			form.querySelector('input[name="pay"]:checked')?.value || ''
		)

		const paymentSummaryTitle = form.querySelector(
			'[data-payment-summary-title]'
		)

		const paymentSummaryText = form.querySelector('[data-payment-summary-text]')

		if (paymentSummaryTitle && paymentSummaryText) {
			if (payment === '1') {
				paymentSummaryTitle.textContent =
					lang === 'ro' ? 'Numerar la primire' : 'Наличными при получении'

				paymentSummaryText.textContent =
					lang === 'ro'
						? 'Plată la primirea comenzii'
						: 'Оплата при получении заказа'
			}

			if (payment === '3') {
				paymentSummaryTitle.textContent =
					lang === 'ro' ? 'Cu cardul la curier' : 'Картой при получении'

				paymentSummaryText.textContent =
					lang === 'ro' ? 'Plată prin terminal' : 'Оплата через терминал'
			}

			if (payment === '2') {
				paymentSummaryTitle.textContent =
					lang === 'ro' ? 'Plată online' : 'Оплата онлайн'

				paymentSummaryText.textContent =
					'Visa · Mastercard · Apple Pay · Google Pay'
			}
		}
	}

	function restoreDraft() {
		const draft = getDraft()

		if (!draft) {
			return
		}

		restoring = true

		setValue('stores', draft.stores)

		const selectedStoreTitle = form.querySelector('[data-selected-store-title]')

		const selectedStoreAddress = form.querySelector(
			'[data-selected-store-address]'
		)

		if (selectedStoreTitle && draft.storeTitle) {
			selectedStoreTitle.textContent = draft.storeTitle
		}

		if (selectedStoreAddress && draft.storeAddress) {
			selectedStoreAddress.textContent = draft.storeAddress
		}

		setValue('city', draft.city)

		setValue('address', draft.address)

		setValue('house', draft.house)

		setValue('porch', draft.porch)

		setValue('apartament', draft.apartament)

		setValue('name', draft.name)

		setValue('surname', draft.surname)

		setValue('email', draft.email)

		setValue('message', draft.message)

		if (getInput('checkbox')) {
			getInput('checkbox').checked = Boolean(draft.checkbox)
		}

		if (getInput('newsletters')) {
			getInput('newsletters').checked = draft.newsletters !== false
		}

		checkRadio('delivery', draft.delivery)

		checkRadio('pay', draft.pay)

		checkRadio('bonus', draft.bonus)

		if (draft.bonusAmount !== undefined) {
			setValue('bonus_amount', draft.bonusAmount)

			const range = form.querySelector('[data-bonus-range]')

			if (range) {
				range.value = draft.bonusAmount || 0

				range.dispatchEvent(
					new Event('input', {
						bubbles: true
					})
				)
			}
		}

		/*
		 * Восстановление телефона.
		 * Визуальный input у нас отдельный
		 * от hidden name="phone".
		 */
		const phoneHidden = form.querySelector('[data-checkout-phone-value]')

		const phoneInput = form.querySelector('[data-checkout-phone-input]')

		if (phoneHidden && draft.phone) {
			phoneHidden.value = draft.phone

			let digits = String(draft.phone).replace(/\D/g, '')

			if (digits.indexOf('373') === 0) {
				digits = digits.slice(3)
			}

			if (phoneInput && /^[67]\d{7}$/.test(digits)) {
				phoneInput.value = [
					digits.slice(0, 2),
					digits.slice(2, 5),
					digits.slice(5, 8)
				].join(' ')

				phoneInput.dispatchEvent(
					new Event('input', {
						bubbles: true
					})
				)
			}
		}

		restoreVisualSummaries()

		const state = getCheckoutState()

		restoreStep(deliveryStep, Boolean(draft.steps?.delivery && state.delivery))

		restoreStep(
			recipientStep,
			Boolean(draft.steps?.recipient && state.recipient)
		)

		restoreStep(paymentStep, Boolean(draft.steps?.payment && state.payment))

		/*
		 * Если всё заполнено —
		 * оставляем все шаги закрытыми.
		 *
		 * Иначе открываем первый
		 * незавершённый.
		 */
		if (!state.delivery) {
			openStep(deliveryStep)
		} else if (!state.recipient) {
			openStep(recipientStep)
		} else if (!state.payment) {
			openStep(paymentStep)
		}

		restoring = false

		updateSubmitState()
	}

	function openStep(step) {
		if (!step) {
			return
		}

		step.classList.add('is-open')

		const content = step.querySelector('[data-checkout-step-content]')

		const summary = step.querySelector('[data-checkout-step-summary]')

		const edit = step.querySelector('[data-checkout-step-edit]')

		if (content) {
			content.hidden = false
		}

		if (summary) {
			summary.hidden = true
		}

		if (edit) {
			edit.hidden = true
		}
	}

	/* ======================================================================
	   GLOBAL EVENTS
	   ====================================================================== */

	form.addEventListener('input', function () {
		updateSubmitState()

		saveDraft()
	})

	form.addEventListener('change', function () {
		updateSubmitState()

		saveDraft()
	})

	/*
	 * После Continue состояние класса
	 * is-complete меняется не в input/change,
	 * поэтому сохраняем ещё и клики.
	 */
	form.addEventListener('click', function (event) {
		if (
			event.target.closest(
				[
					'[data-checkout-delivery-continue]',
					'[data-checkout-recipient-continue]',
					'[data-checkout-payment-continue]'
				].join(',')
			)
		) {
			window.setTimeout(function () {
				updateSubmitState()

				saveDraft()
			}, 30)
		}
	})

	/* ======================================================================
	   FINAL SUBMIT
	   ====================================================================== */

	form.addEventListener('submit', function (event) {
		const state = getCheckoutState()

		if (!state.complete) {
			event.preventDefault()

			if (!state.delivery) {
				openStep(deliveryStep)

				scrollToStep(deliveryStep)

				return
			}

			if (!state.recipient) {
				validateEmail(true)
				validateTerms(true)

				openStep(recipientStep)

				scrollToStep(recipientStep)

				return
			}

			if (!state.payment) {
				openStep(paymentStep)

				scrollToStep(paymentStep)

				return
			}
		}

		if (submitting) {
			event.preventDefault()

			return
		}

		submitting = true

		/*
		 * ВАЖНО:
		 * draft здесь не удаляем.
		 *
		 * Если online payment завершится
		 * ошибкой, пользователь сможет
		 * вернуться с заполненной формой.
		 *
		 * Очистим draft только
		 * после success page.
		 */
		if (submitButton) {
			submitButton.disabled = true

			submitButton.classList.add('is-loading')
		}

		if (submitText) {
			submitText.textContent = text.submitting
		}

		if (submitHint) {
			submitHint.hidden = true
		}
	})

	/* ======================================================================
	   INIT
	   ====================================================================== */

	restoreDraft()

	updateSubmitState()
})()

/* ==========================================================================
   END CHECKOUT V2 — STATE & DRAFT
   ========================================================================== */
