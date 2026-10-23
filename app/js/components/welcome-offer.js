(() => {
	'use strict'

	const DISMISS_KEY = 'vn_welcome_offer_dismissed_until'
	const DISMISS_MS = 30 * 24 * 60 * 60 * 1000
	const SHOW_DELAY_MS = 21 * 1000
	const OFFER = document.querySelector('[data-welcome-offer]')

	const readDismissal = () => {
		try {
			const value = Number(window.localStorage.getItem(DISMISS_KEY))
			if (value > Date.now()) return value
			window.localStorage.removeItem(DISMISS_KEY)
		} catch (error) {
			const match = document.cookie.match(/(?:^|; )vn_welcome_offer_dismissed_until=(\d+)/)
			const value = match ? Number(match[1]) : 0
			if (value > Date.now()) return value
		}

		return 0
	}

	const saveDismissal = () => {
		const until = Date.now() + DISMISS_MS
		try {
			window.localStorage.setItem(DISMISS_KEY, String(until))
		} catch (error) {
			document.cookie = `vn_welcome_offer_dismissed_until=${until}; Max-Age=2592000; Path=/; SameSite=Lax`
		}
	}

	const isVisible = element => {
		if (!element) return false
		const style = window.getComputedStyle(element)
		return style.display !== 'none' && style.visibility !== 'hidden' && element.getClientRects().length > 0
	}

	const cookieUiIsVisible = () => (
		isVisible(document.querySelector('[data-cookie-banner]')) ||
		Boolean(document.querySelector('.cookie-panel.is-open'))
	)

	const setCountdown = element => {
		const expiry = Number(element.dataset.expiresAt) * 1000
		const output = element.querySelector('[data-welcome-countdown-value]')
		if (!expiry || !output) return

		const remaining = expiry - Date.now()
		if (remaining <= 0) {
			if (element.matches('[data-welcome-countdown]')) {
				element.hidden = true
			} else {
				output.textContent = element.dataset.language === 'ro' ? 'expirată' : 'истекла'
			}
			return
		}

		const totalSeconds = Math.floor(remaining / 1000)
		const days = Math.floor(totalSeconds / 86400)
		const hours = Math.floor((totalSeconds % 86400) / 3600)
		const minutes = Math.floor((totalSeconds % 3600) / 60)
		const seconds = totalSeconds % 60
		output.textContent = element.dataset.language === 'ro'
			? `${days}z ${hours}h ${minutes}m ${seconds}s`
			: `${days}д ${hours}ч ${minutes}м ${seconds}с`
	}

	const updateCountdowns = () => {
		document.querySelectorAll('[data-welcome-countdown], [data-welcome-success-countdown]').forEach(setCountdown)
	}

	updateCountdowns()
	window.setInterval(updateCountdowns, 1000)

	document.addEventListener('welcome-offer:activated', event => {
		const successTimer = document.querySelector('[data-welcome-success-countdown]')
		if (!successTimer || !event.detail?.expiresAt) return
		successTimer.dataset.expiresAt = String(event.detail.expiresAt)
		successTimer.hidden = false
		setCountdown(successTimer)
	})

	if (!OFFER) return

	let dismissed = readDismissal()
	let openTimer = null
	let focusBeforeOpen = null
	let scrollYBeforeOpen = 0

	const restoreScrollPosition = () => {
		window.scrollTo(0, scrollYBeforeOpen)
	}

	const closeOffer = ({ remember = true } = {}) => {
		if (!OFFER.classList.contains('is-open')) return
		OFFER.classList.remove('is-open')
		OFFER.setAttribute('aria-hidden', 'true')
		document.documentElement.classList.remove('welcome-offer-lock')
		if (remember) {
			saveDismissal()
			dismissed = Date.now() + DISMISS_MS
		}
		if (focusBeforeOpen instanceof HTMLElement) focusBeforeOpen.focus({ preventScroll: true })
		restoreScrollPosition()
	}

	const openOffer = () => {
		if (dismissed > Date.now() || cookieUiIsVisible()) return
		if (document.querySelector('.auth-drawer.is-open')) return

		scrollYBeforeOpen = window.scrollY
		focusBeforeOpen = document.activeElement
		OFFER.classList.add('is-open')
		OFFER.setAttribute('aria-hidden', 'false')
		document.documentElement.classList.add('welcome-offer-lock')
		OFFER.querySelector('[data-welcome-offer-register]')?.focus({ preventScroll: true })
		restoreScrollPosition()
	}

	const scheduleOffer = () => {
		if (dismissed > Date.now()) return
		if (cookieUiIsVisible() || document.querySelector('.auth-drawer.is-open')) {
			openTimer = window.setTimeout(scheduleOffer, 700)
			return
		}
		openOffer()
	}

	const startOfferDelayAfterPageLoad = () => {
		if (!dismissed) openTimer = window.setTimeout(scheduleOffer, SHOW_DELAY_MS)
	}

	if (document.readyState === 'complete') {
		startOfferDelayAfterPageLoad()
	} else {
		window.addEventListener('load', startOfferDelayAfterPageLoad, { once: true })
	}

	OFFER.addEventListener('click', event => {
		const closeButton = event.target.closest('[data-welcome-offer-close]')
		if (closeButton) closeOffer()

		const registerButton = event.target.closest('[data-welcome-offer-register]')
		if (registerButton) {
			closeOffer()
			window.setTimeout(() => {
				if (window.authDrawer?.open) {
					window.authDrawer.open('register')
				}
			}, 0)
		}
	})

	document.addEventListener('keydown', event => {
		if (event.key === 'Escape') closeOffer()
	})
})()
