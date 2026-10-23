;(function (window, document) {
	'use strict'

	var modal = document.querySelector('[data-vn-feedback-modal]')
	var form = document.querySelector('[data-vn-feedback-form]')
	var openers = document.querySelectorAll('[data-vn-feedback-open]')

	if (!modal || !form) {
		return
	}

	var cooldownKey = 'vn_feedback_auto_hidden_until_v2'
	var visitStartedKey = 'vn_feedback_visit_started_at_v2'
	var autoShownKey = 'vn_feedback_auto_shown_v2'
	var cooldownDays = 14
	var autoDelayMs = 60 * 1000
	var autoShown = false
	var lastFocusedElement = null
	var errorBox = form.querySelector('[data-vn-feedback-error]')
	var successBox = form.querySelector('[data-vn-feedback-success]')
	var submitButton = form.querySelector('[type="submit"]')
	var pagePathInput = form.querySelector('[name="page_path"]')
	var localeInput = form.querySelector('[name="locale"]')
	var typeInput = form.querySelector('[name="feedback_type"]')
	var typeButtons = form.querySelectorAll('[data-vn-feedback-type]')
	var imageInput = form.querySelector('[name="images[]"]')

	function getCookie(name) {
		var prefix = name + '='
		var cookies = document.cookie ? document.cookie.split(';') : []
		for (var i = 0; i < cookies.length; i += 1) {
			var cookie = cookies[i].trim()
			if (cookie.indexOf(prefix) === 0) {
				return decodeURIComponent(cookie.slice(prefix.length))
			}
		}
		return ''
	}

	function getHiddenUntil() {
		var value = ''
		try {
			value = window.localStorage.getItem(cooldownKey) || ''
		} catch (error) {
			value = ''
		}
		return parseInt(value || getCookie(cooldownKey), 10) || 0
	}

	function setHiddenForCooldown() {
		var until = Date.now() + cooldownDays * 24 * 60 * 60 * 1000
		try {
			window.localStorage.setItem(cooldownKey, String(until))
		} catch (error) {
			// Cookie fallback keeps the behaviour when localStorage is blocked.
		}
		document.cookie =
			cooldownKey +
			'=' +
			encodeURIComponent(String(until)) +
			'; path=/; max-age=' +
			cooldownDays * 24 * 60 * 60
	}

	function isAutoSuppressed() {
		return getHiddenUntil() > Date.now()
	}

	function isExcludedPage() {
		var path = window.location.pathname || ''
		return /\/checkout(?:\/|$)|\/order-success(?:\/|$)|\/cp(?:\/|$)/i.test(path)
	}

	function getSessionValue(key) {
		try {
			return window.sessionStorage.getItem(key) || ''
		} catch (error) {
			return ''
		}
	}

	function setSessionValue(key, value) {
		try {
			window.sessionStorage.setItem(key, value)
		} catch (error) {
			// The current page timer remains a safe fallback.
		}
	}

	function getVisitStartedAt() {
		var stored = parseInt(getSessionValue(visitStartedKey), 10)
		if (stored > 0) {
			return stored
		}

		var startedAt = Date.now()
		setSessionValue(visitStartedKey, String(startedAt))
		return startedAt
	}

	function removeFeedbackHash() {
		if (
			window.location.hash !== '#vn-feedback-modal' ||
			!window.history ||
			!window.history.replaceState
		) {
			return
		}

		window.history.replaceState(
			null,
			document.title,
			window.location.pathname + window.location.search
		)
	}

	function openModal(isAutomatic) {
		if (
			isAutomatic &&
			(autoShown ||
				getSessionValue(autoShownKey) === '1' ||
				isAutoSuppressed() ||
				isExcludedPage() ||
				modal.classList.contains('is-open'))
		) {
			return
		}

		lastFocusedElement = document.activeElement
		pagePathInput.value = window.location.pathname || '/'
		localeInput.value = (document.documentElement.lang || '')
			.toLowerCase()
			.slice(0, 10)
		errorBox.hidden = true
		successBox.hidden = true
		form.querySelector('[name="message"]').disabled = false
		submitButton.disabled = false
		modal.classList.add('is-open')
		modal.setAttribute('aria-hidden', 'false')
		if (isAutomatic) {
			autoShown = true
			setSessionValue(autoShownKey, '1')
		}
		window.setTimeout(function () {
			if (typeButtons.length > 0) {
				typeButtons[0].focus()
			}
		}, 0)
	}

	function closeModal(rememberChoice) {
		modal.classList.remove('is-open')
		modal.setAttribute('aria-hidden', 'true')
		removeFeedbackHash()
		if (rememberChoice) {
			setHiddenForCooldown()
		}
		if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
			lastFocusedElement.focus()
		}
	}

	function showError(message) {
		errorBox.textContent = message
		errorBox.hidden = false
	}

	function setFeedbackType(selectedType) {
		typeInput.value = selectedType
		for (
			var buttonIndex = 0;
			buttonIndex < typeButtons.length;
			buttonIndex += 1
		) {
			var isSelected =
				typeButtons[buttonIndex].getAttribute('data-vn-feedback-type') ===
				selectedType
			typeButtons[buttonIndex].classList.toggle('is-selected', isSelected)
			typeButtons[buttonIndex].setAttribute(
				'aria-pressed',
				isSelected ? 'true' : 'false'
			)
		}
	}

	for (var i = 0; i < openers.length; i += 1) {
		openers[i].addEventListener('click', function (event) {
			event.preventDefault()
			openModal(false)
		})
	}

	for (var k = 0; k < typeButtons.length; k += 1) {
		typeButtons[k].addEventListener('click', function () {
			setFeedbackType(this.getAttribute('data-vn-feedback-type'))
		})
	}

	var closers = modal.querySelectorAll('[data-vn-feedback-close]')
	for (var j = 0; j < closers.length; j += 1) {
		closers[j].addEventListener('click', function () {
			closeModal(true)
		})
	}

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && modal.classList.contains('is-open')) {
			closeModal(true)
		}
	})

	form.addEventListener('submit', function (event) {
		event.preventDefault()
		var message = form.querySelector('[name="message"]').value.trim()
		if (message.length < 3) {
			showError('Напишите сообщение минимум из 3 символов.')
			return
		}

		if (imageInput && imageInput.files.length > 3) {
			showError('Можно выбрать не более 3 изображений.')
			return
		}
		if (imageInput) {
			for (
				var imageIndex = 0;
				imageIndex < imageInput.files.length;
				imageIndex += 1
			) {
				if (imageInput.files[imageIndex].size > 5 * 1024 * 1024) {
					showError('Каждое изображение должно быть не больше 5 МБ.')
					return
				}
			}
		}

		errorBox.hidden = true
		submitButton.disabled = true

		var formData = new FormData(form)
		fetch('/feedback/submit', {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			body: formData
		})
			.then(function (response) {
				return response
					.json()
					.catch(function () {
						return {}
					})
					.then(function (payload) {
						if (!response.ok) {
							throw new Error(
								payload.error || 'Не удалось отправить сообщение.'
							)
						}
						return payload
					})
			})
			.then(function () {
				successBox.hidden = false
				form.querySelector('[name="message"]').disabled = true
				setHiddenForCooldown()
				window.setTimeout(function () {
					closeModal(false)
					form.reset()
					setFeedbackType(typeInput.value || 'idea')
				}, 1800)
			})
			.catch(function (error) {
				submitButton.disabled = false
				showError(
					error && error.message
						? error.message
						: 'Не удалось отправить сообщение. Попробуйте ещё раз.'
				)
			})
	})

	var visitStartedAt = getVisitStartedAt()
	var timeUntilAutoOpen = Math.max(
		0,
		autoDelayMs - (Date.now() - visitStartedAt)
	)
	window.setTimeout(function () {
		openModal(true)
	}, timeUntilAutoOpen)
})(window, document)
