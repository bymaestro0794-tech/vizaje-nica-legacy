;(function ($) {
	'use strict'

	if (document.querySelector('form.order__body[data-checkout]')) {
		return
	}

	/* ==========================================================================
	   HELPERS
	   ========================================================================== */

	function getDelivery() {
		return String($('input[name="delivery"]:checked').val() || '')
	}

	function getPayment() {
		return String($('input[name="pay"]:checked').val() || '')
	}

	function isValidPhone(value) {
		const digits = String(value || '').replace(/\D/g, '')

		/*
		 * Поддерживаем:
		 *
		 * 061234567
		 * 61234567
		 * +373 61 234 567
		 *
		 * После очистки:
		 *
		 * 8 цифр
		 * или
		 * 373 + 8 цифр
		 */

		return (
			digits.length === 8 ||
			(digits.length === 11 && digits.indexOf('373') === 0)
		)
	}

	function isValidEmail(value) {
		const email = String(value || '').trim()

		if (!email) {
			return false
		}

		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
	}

	function scrollToElement(selector) {
		const element = $(selector).first()

		if (!element.length) {
			return
		}

		$('html, body').animate(
			{
				scrollTop: element.offset().top - 130
			},
			400
		)
	}

	function setSectionError(selector, hasError) {
		$(selector).toggleClass('error', Boolean(hasError))
	}

	/* ==========================================================================
	   DELIVERY TOTALS
	   ========================================================================== */

	function getTotalValueElement() {
		return $('.sidebar-order__row._total')
			.not('.bonus_minus_total')
			.find('.sidebar-order__value')
			.first()
	}

	function getBonusTotalValueElement() {
		return $('.sidebar-order__row.bonus_minus_total')
			.find('.sidebar-order__value')
			.first()
	}

	function updateDeliverySummary(delivery) {
		const deliveryInfo = $('.delivery-info')

		const totalElement = getTotalValueElement()

		const bonusTotalElement = getBonusTotalValueElement()

		if (delivery === '2') {
			if (deliveryInfo.length) {
				deliveryInfo.html(deliveryInfo.data('del'))
			}

			if (totalElement.length) {
				totalElement.html(totalElement.data('del'))
			}

			if (bonusTotalElement.length) {
				bonusTotalElement.html(bonusTotalElement.data('del'))
			}

			return
		}

		if (delivery === '3') {
			if (deliveryInfo.length) {
				deliveryInfo.html(deliveryInfo.data('delday'))
			}

			if (totalElement.length) {
				totalElement.html(totalElement.data('delday'))
			}

			if (bonusTotalElement.length) {
				bonusTotalElement.html(bonusTotalElement.data('delday'))
			}

			return
		}

		if (deliveryInfo.length) {
			deliveryInfo.html(deliveryInfo.data('free'))
		}

		if (totalElement.length) {
			totalElement.html(totalElement.data('free'))
		}

		if (bonusTotalElement.length) {
			bonusTotalElement.html(bonusTotalElement.data('free'))
		}
	}

	/* ==========================================================================
	   DELIVERY STATE
	   ========================================================================== */

	function updateDeliveryState() {
		const delivery = getDelivery()

		const isPickup = delivery === '1'

		const isCourier = delivery === '2'

		const isUrgent = delivery === '3'

		const needsAddress = isCourier || isUrgent

		/*
		 * Address block
		 */

		$('.address_delivery_block').toggle(needsAddress)

		/*
		 * Required fields
		 */

		$('input[name="city"]').prop('required', needsAddress)

		$('input[name="address"]').prop('required', needsAddress)

		$('input[name="house"]').prop('required', needsAddress)

		/*
		 * Для обычного курьера подъезд обязателен.
		 * Для срочной доставки — нет.
		 */

		$('input[name="porch"]').prop('required', isCourier)

		$('input[name="apartament"]').prop('required', false)

		/*
		 * Summary
		 */

		updateDeliverySummary(delivery)

		/*
		 * Clear previous error once
		 * delivery selected.
		 */

		if (isPickup || isCourier || isUrgent) {
			setSectionError('.delivery_section', false)
		}
	}

	$('body').on('change', 'input[name="delivery"]', function () {
		updateDeliveryState()
	})

	/* ==========================================================================
	   PAYMENT
	   ========================================================================== */

	$('body').on('change', 'input[name="pay"]', function () {
		if (getPayment()) {
			setSectionError('.pay_section', false)
		}
	})

	/* ==========================================================================
	   TERMS
	   ========================================================================== */

	$('body').on('change', 'input[name="checkbox"]', function () {
		const checked = $('input[name="checkbox"]').is(':checked')

		setSectionError('.check_on', !checked)
	})

	/* ==========================================================================
	   RECIPIENT
	   ========================================================================== */

	$('body').on(
		'input change',
		[
			'input[name="name"]',
			'input[name="surname"]',
			'input[name="phone"]',
			'input[name="email"]'
		].join(','),
		function () {
			$('.info-order').removeClass('error')
		}
	)

	/* ==========================================================================
	   BONUS
	   ========================================================================== */

	$('body').on('change', 'input.bonus_plus', function () {
		/*
		 * Обычный total
		 */

		$('.sidebar-order__row._total').not('.bonus_minus_total').show()

		/*
		 * Total после списания
		 */

		$('.bonus_minus_total').hide()

		/*
		 * Строка "списано бонусов"
		 */

		$('.bonus_minus_info').hide()

		/*
		 * Начисление бонусов
		 */

		$('.sidebar-order__row._bonus').show()
	})

	$('body').on('change', 'input.bonus_minus', function () {
		/*
		 * Обычный total
		 */

		$('.sidebar-order__row._total').not('.bonus_minus_total').hide()

		/*
		 * Total после списания
		 */

		$('.bonus_minus_total').show()

		/*
		 * Строка списания
		 */

		$('.bonus_minus_info').show()

		/*
		 * Если списываем —
		 * начисление скрываем.
		 */

		$('.sidebar-order__row._bonus').hide()
	})

	/* ==========================================================================
	   VALIDATION
	   ========================================================================== */

	function validateDelivery() {
		const delivery = getDelivery()

		const valid = ['1', '2', '3'].indexOf(delivery) !== -1

		setSectionError('.delivery_section', !valid)

		if (!valid) {
			return false
		}

		/*
		 * Pickup
		 */

		if (delivery === '1') {
			const storeId = String($('input[name="stores"]').val() || '').trim()

			const storeValid = storeId !== '' && Number(storeId) > 0

			setSectionError('.delivery_section', !storeValid)

			return storeValid
		}

		/*
		 * Courier / urgent
		 */

		const city = String($('input[name="city"]').val() || '').trim()

		const address = String($('input[name="address"]').val() || '').trim()

		const house = String($('input[name="house"]').val() || '').trim()

		let validAddress = city !== '' && address !== '' && house !== ''

		/*
		 * Для delivery=2 подъезд
		 * пока остаётся required
		 * согласно текущему contract.
		 */

		if (delivery === '2') {
			const porch = String($('input[name="porch"]').val() || '').trim()

			validAddress = validAddress && porch !== ''
		}

		setSectionError('.delivery_section', !validAddress)

		return validAddress
	}

	function validateRecipient() {
		const name = String($('input[name="name"]').val() || '').trim()

		const surname = String($('input[name="surname"]').val() || '').trim()

		const phone = $('input[name="phone"]').val()

		const email = $('input[name="email"]').val()

		const valid =
			name !== '' &&
			surname !== '' &&
			isValidPhone(phone) &&
			isValidEmail(email)

		setSectionError('.info-order', !valid)

		return valid
	}

	function validatePayment() {
		const payment = getPayment()

		const valid = ['1', '2', '3'].indexOf(payment) !== -1

		setSectionError('.pay_section', !valid)

		return valid
	}

	function validateTerms() {
		const valid = $('input[name="checkbox"]').is(':checked')

		setSectionError('.check_on', !valid)

		return valid
	}

	/* ==========================================================================
	   SUBMIT
	   ========================================================================== */

	$('body').on('click', '.order_send', function (event) {
		const deliveryValid = validateDelivery()

		const recipientValid = validateRecipient()

		const paymentValid = validatePayment()

		const termsValid = validateTerms()

		let firstError = null

		if (!deliveryValid) {
			firstError = '.delivery_section'
		} else if (!recipientValid) {
			firstError = '.info-order'
		} else if (!paymentValid) {
			firstError = '.pay_section'
		} else if (!termsValid) {
			firstError = '.check_on'
		}

		if (firstError) {
			event.preventDefault()

			scrollToElement(firstError)

			return false
		}

		/*
		 * Если всё валидно —
		 * НЕ вызываем preventDefault().
		 *
		 * Form отправляется обычным POST.
		 */

		return true
	})

	/* ==========================================================================
	   FORM SUBMIT SAFETY
	   ========================================================================== */

	/*
	 * Проверяем также submit самой формы.
	 *
	 * Это важно на случай:
	 * - Enter в input;
	 * - будущая mobile sticky CTA;
	 * - вызов form.submit через другой UI.
	 */

	$('body').on('submit', '.order__body', function (event) {
		const deliveryValid = validateDelivery()

		const recipientValid = validateRecipient()

		const paymentValid = validatePayment()

		const termsValid = validateTerms()

		if (deliveryValid && recipientValid && paymentValid && termsValid) {
			return true
		}

		event.preventDefault()

		let firstError = '.delivery_section'

		if (deliveryValid) {
			firstError = !recipientValid
				? '.info-order'
				: !paymentValid
					? '.pay_section'
					: '.check_on'
		}

		scrollToElement(firstError)

		return false
	})

	/* ==========================================================================
	   INITIAL STATE
	   ========================================================================== */

	$(function () {
		/*
		 * Address должен быть скрыт,
		 * пока delivery не выбран.
		 */

		updateDeliveryState()

		/*
		 * Если backend/browser восстановил
		 * выбранный бонус после reload.
		 */

		if ($('input.bonus_minus').is(':checked')) {
			$('input.bonus_minus').trigger('change')
		} else if ($('input.bonus_plus').is(':checked')) {
			$('input.bonus_plus').trigger('change')
		}
	})
})(jQuery)
