/**
 * Vizaje-Nica Cookie Consent
 *
 * - баннер: "Принять всё" / "Отклонить всё" / "Настроить";
 * - панель настроек: чекбоксы по категориям (necessary всегда on);
 * - решение сохраняется через POST /cookie_consent (cookie_consent, 180 дней);
 * - после любого сохранения страница перезагружается, чтобы PHP заново
 *   решил, вставлять ли GTM/Clarity/Meta Pixel/Jivo в <head>.
 */

;(function () {
	'use strict'

	var SAVE_URL = '/cookie_consent'

	function lockScroll(lock) {
		document.documentElement.classList.toggle('cookie-panel-lock', lock)
	}

	function openPanel(panel) {
		panel.classList.add('is-open')
		lockScroll(true)
	}

	function closePanel(panel) {
		panel.classList.remove('is-open')
		lockScroll(false)
	}

	function syncToggles(panel) {
		/*
		 * cookie_consent — HttpOnly (как и все cookie на сайте, см.
		 * config/config.php), поэтому текущее состояние читаем не из
		 * document.cookie (он его не видит), а из data-атрибутов,
		 * которые PHP проставляет на каждом рендере страницы.
		 */
		var consent = {
			functional: panel.getAttribute('data-consent-functional') === '1',
			analytics: panel.getAttribute('data-consent-analytics') === '1',
			marketing: panel.getAttribute('data-consent-marketing') === '1'
		}

		panel.querySelectorAll('[data-cookie-toggle]').forEach(function (toggle) {
			var key = toggle.getAttribute('data-cookie-toggle')

			toggle.checked = !!consent[key]
		})
	}

	function save(categories) {
		var xhr = new XMLHttpRequest()

		xhr.open('POST', SAVE_URL, true)
		xhr.setRequestHeader(
			'Content-Type',
			'application/x-www-form-urlencoded'
		)

		xhr.onload = function () {
			window.location.reload()
		}

		xhr.onerror = function () {
			window.location.reload()
		}

		xhr.send(
			'functional=' + (categories.functional ? 1 : 0) +
			'&analytics=' + (categories.analytics ? 1 : 0) +
			'&marketing=' + (categories.marketing ? 1 : 0)
		)
	}

	function init() {
		var panel = document.querySelector('[data-cookie-panel]')

		document.addEventListener('click', function (event) {
			var actionEl = event.target.closest('[data-cookie-action]')

			if (!actionEl) {
				return
			}

			var action = actionEl.getAttribute('data-cookie-action')

			if (action === 'accept') {
				save({ functional: true, analytics: true, marketing: true })

				return
			}

			if (action === 'reject') {
				save({ functional: false, analytics: false, marketing: false })

				return
			}

			if (action === 'settings') {
				if (panel) {
					syncToggles(panel)
					openPanel(panel)
				}

				return
			}

			if (action === 'save' && panel) {
				var categories = {}

				panel
					.querySelectorAll('[data-cookie-toggle]')
					.forEach(function (toggle) {
						categories[
							toggle.getAttribute('data-cookie-toggle')
						] = toggle.checked
					})

				save(categories)
			}
		})

		if (!panel) {
			return
		}

		panel
			.querySelectorAll('[data-cookie-panel-close]')
			.forEach(function (closeEl) {
				closeEl.addEventListener('click', function () {
					closePanel(panel)
				})
			})

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && panel.classList.contains('is-open')) {
				closePanel(panel)
			}
		})
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init, { once: true })
	} else {
		init()
	}
})()
