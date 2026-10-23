(function () {
	'use strict'

	var forms = document.querySelectorAll('[data-analytics-filter]')

	if (!forms.length) {
		return
	}

	var formatDate = function (date) {
		var year = date.getFullYear()
		var month = String(date.getMonth() + 1).padStart(2, '0')
		var day = String(date.getDate()).padStart(2, '0')
		return year + '-' + month + '-' + day
	}

	var displayDate = function (value) {
		var parts = (value || '').split('-')
		return parts.length === 3 ? parts[2] + '.' + parts[1] + '.' + parts[0] : value
	}

	Array.prototype.forEach.call(forms, function (form) {
		var openButton = form.querySelector('[data-analytics-filter-open]')
		var drawer = form.querySelector('.analytics-filter-drawer')
		var closeButtons = form.querySelectorAll('[data-analytics-filter-close]')
		var fromInput = form.querySelector('input[name="date_from"]')
		var toInput = form.querySelector('input[name="date_to"]')
		var periodSummary = form.querySelector('[data-analytics-period-summary]')
		var lastFocused = null

		if (!openButton || !drawer) {
			return
		}

		form.classList.add('has-analytics-filter-js')

		var getFocusable = function () {
			return Array.prototype.slice.call(drawer.querySelectorAll(
				'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
			)).filter(function (element) {
				return element.offsetWidth || element.offsetHeight || element === document.activeElement
			})
		}

		var closeDrawer = function () {
			form.classList.remove('is-filter-open')
			drawer.setAttribute('aria-hidden', 'true')
			openButton.setAttribute('aria-expanded', 'false')
			document.body.classList.remove('analytics-filter-scroll-locked')
			if (lastFocused) {
				lastFocused.focus()
			}
		}

		openButton.addEventListener('click', function () {
			lastFocused = document.activeElement
			form.classList.add('is-filter-open')
			drawer.setAttribute('aria-hidden', 'false')
			openButton.setAttribute('aria-expanded', 'true')
			document.body.classList.add('analytics-filter-scroll-locked')
			var closeButton = drawer.querySelector('.analytics-filter-close')
			if (closeButton) {
				closeButton.focus()
			}
		})

		Array.prototype.forEach.call(closeButtons, function (button) {
			button.addEventListener('click', closeDrawer)
		})

		form.addEventListener('keydown', function (event) {
			if (!form.classList.contains('is-filter-open')) {
				return
			}

			if (event.key === 'Escape') {
				event.preventDefault()
				closeDrawer()
				return
			}

			if (event.key === 'Tab') {
				var focusable = getFocusable()
				if (!focusable.length) {
					return
				}
				var first = focusable[0]
				var last = focusable[focusable.length - 1]
				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault()
					last.focus()
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault()
					first.focus()
				}
			}
		})

		Array.prototype.forEach.call(form.querySelectorAll('[data-analytics-range]'), function (preset) {
			preset.addEventListener('click', function () {
				if (!fromInput || !toInput) {
					return
				}

				var today = new Date()
				today.setHours(0, 0, 0, 0)
				var from = new Date(today)
				var range = preset.getAttribute('data-analytics-range')

				if (range === 'month') {
					from.setDate(1)
				} else {
					from.setDate(from.getDate() - (parseInt(range, 10) - 1))
				}

				fromInput.value = formatDate(from)
				toInput.value = formatDate(today)
				fromInput.dispatchEvent(new Event('change', { bubbles: true }))
				toInput.dispatchEvent(new Event('change', { bubbles: true }))
				Array.prototype.forEach.call(form.querySelectorAll('[data-analytics-range]'), function (button) {
					button.classList.toggle('is-selected', button === preset)
				})
			})
		})

		if (fromInput && toInput) {
			var updatePeriodSummary = function () {
				if (periodSummary) {
					periodSummary.textContent = displayDate(fromInput.value) + ' — ' + displayDate(toInput.value)
				}
			}

			var validateDates = function () {
				toInput.setCustomValidity(fromInput.value && toInput.value && fromInput.value > toInput.value
					? 'Дата «По» должна быть не раньше даты «С».'
					: '')
			}
			fromInput.addEventListener('change', validateDates)
			toInput.addEventListener('change', validateDates)
			fromInput.addEventListener('change', updatePeriodSummary)
			toInput.addEventListener('change', updatePeriodSummary)
			form.addEventListener('submit', function (event) {
				validateDates()
				if (!form.checkValidity()) {
					event.preventDefault()
					form.reportValidity()
				}
			})
		}
	})
})()
