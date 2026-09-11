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
