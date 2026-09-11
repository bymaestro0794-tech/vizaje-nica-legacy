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
