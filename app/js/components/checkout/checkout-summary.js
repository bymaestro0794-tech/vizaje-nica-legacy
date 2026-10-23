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

	root.addEventListener('click', async function (event) {
		const button = event.target.closest('[data-checkout-discount-choice]')
		if (!button || !root.contains(button)) return

		const choice = String(button.dataset.checkoutDiscountChoice || '')
		if (!['promo', 'roulette'].includes(choice)) return

		const buttons = Array.from(
			root.querySelectorAll('[data-checkout-discount-choice]')
		)
		const error = root.querySelector('[data-checkout-discount-error]')
		buttons.forEach((item) => (item.disabled = true))
		if (error) {
			error.hidden = true
			error.textContent = ''
		}

		try {
			const response = await fetch('/ajax/discount_choice', {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
				},
				body: new URLSearchParams({ choice, lang })
			})
			const result = await response.json()
			if (!response.ok || result.status !== 'ok') {
				throw new Error('discount_unavailable')
			}
			window.location.reload()
		} catch (requestError) {
			buttons.forEach((item) => (item.disabled = false))
			if (error) {
				error.textContent = lang === 'ro'
					? 'Reducerea selectată nu mai este disponibilă.'
					: 'Выбранная скидка больше недоступна.'
				error.hidden = false
			}
		}
	})

	updateSummary()
})()

/* ==========================================================================
   END CHECKOUT V2 — SUMMARY
   ========================================================================== */
