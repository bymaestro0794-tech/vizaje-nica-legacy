/* ==========================================================================
   CHECKOUT V2 — STATE
   ========================================================================== */
;(function () {
	'use strict'

	const form =
		document.querySelector('form.order__body[data-checkout]') ||
		document.querySelector('form.order__body')

	if (!form) {
		return
	}

	/* Remove the legacy checkout draft once after deployment. */
	try {
		window.localStorage.removeItem('vizaje_checkout_draft_v2')
	} catch (error) {
		// Storage can be unavailable in privacy-restricted browsers.
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
		})
	}

	/* ======================================================================
	   GLOBAL EVENTS
	   ====================================================================== */

	form.addEventListener('input', function () {
		updateSubmitState()
	})

	form.addEventListener('change', function () {
		updateSubmitState()
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
	updateSubmitState()
})()

/* ==========================================================================
   END CHECKOUT V2 — STATE
   ========================================================================== */
