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

	/*
	 * Flexible checkout phone mode:
	 * - keep only digits and an optional leading "+";
	 * - accept any country/code/length;
	 * - require at least one digit before submit;
	 * - do not force the Moldova prefix, country code or mobile prefix.
	 */
	function sanitizeCheckoutPhone(value) {
		const raw = String(value || '')
		const hasLeadingPlus = raw.trim().charAt(0) === '+'
		const digits = raw.replace(/\D/g, '')

		return (hasLeadingPlus ? '+' : '') + digits
	}

	function getPhoneDigits(value) {
		return String(value || '').replace(/\D/g, '')
	}

	function isValidCheckoutPhone(value) {
		return getPhoneDigits(value).length >= 1
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

		const phone = String(phoneHidden?.value || '')

		const terms = Boolean(
			recipientStep.querySelector('[name="checkbox"]')?.checked
		)

		const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)

		const phoneValid = isValidCheckoutPhone(phone)

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

		const sanitizedPhone = sanitizeCheckoutPhone(phoneInput.value)
		const valid = isValidCheckoutPhone(sanitizedPhone)

		phoneInput.value = sanitizedPhone

		/*
		 * Номер ещё просто не дописан.
		 */
		if (!valid) {
			// Keep the sanitized value while the user is still typing.
			// The minimum-length rule is enforced by checkout state and the server.
			phoneHidden.value = sanitizedPhone

			if (phoneConfirm) {
				phoneConfirm.hidden = true
			}

			if (phoneError) {
				phoneError.hidden = true
			}

			updateRecipientContinueState()

			return
		}

		phoneHidden.value = sanitizedPhone

		if (phoneConfirm) {
			phoneConfirm.textContent =
				(lang === 'ro' ? 'Telefonul dvs.: ' : 'Ваш телефон: ') +
				sanitizedPhone

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
			const sanitizedPhone = sanitizeCheckoutPhone(phoneInput.value)
			const digits = getPhoneDigits(sanitizedPhone)

			phoneInput.value = sanitizedPhone
			phoneHidden.value = sanitizedPhone

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

			if (!isValidCheckoutPhone(sanitizedPhone)) {
				if (phoneError) {
					phoneError.textContent =
						lang === 'ro'
						? 'Introduceți cifre pentru numărul de telefon.'
						: 'Введите цифры номера телефона.'

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
		const existingPhone = sanitizeCheckoutPhone(phoneHidden.value)

		if (isValidCheckoutPhone(existingPhone)) {
			phoneInput.value = existingPhone

			updatePhoneState()
		} else {
			/*
			 * Пустое или слишком короткое старое значение
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
			isValidCheckoutPhone(phoneHidden?.value || '')

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
