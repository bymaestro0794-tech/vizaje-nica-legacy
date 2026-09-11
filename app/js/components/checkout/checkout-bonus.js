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
