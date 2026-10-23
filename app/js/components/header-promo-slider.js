;(function () {
	'use strict'

	const announcement = document.querySelector('[data-header-promo]')
	if (!announcement) return

	const language = announcement.dataset.language === 'ro' ? 'ro' : 'ru'
	const slides = Array.from(announcement.querySelectorAll('[data-header-promo-slide]'))
	const products = announcement.querySelector('[data-header-promo-products]')
	const fallback = announcement.querySelector('[data-header-promo-fallback]')
	const controls = announcement.querySelector('[data-header-promo-controls]')
	const invite = announcement.querySelector('[data-header-wheel-invite]')
	const inviteMessage = announcement.querySelector('[data-header-wheel-message]')
	const inviteButton = announcement.querySelector('[data-header-wheel-button]')
	const previousButton = announcement.querySelector('[data-header-promo-previous]')
	const nextButton = announcement.querySelector('[data-header-promo-next]')
	const accountScope = announcement.dataset.rouletteScope || ''
	const rouletteStateUrl = announcement.dataset.rouletteStateUrl || ''
	const storagePrefix = accountScope ? `vn_roulette_${accountScope}` : ''
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
	const siteHeader = announcement.closest('[data-site-header]')
	const copy = {
		ru: {
			spinMessage: 'Рулетка удачи ждёт — попробуйте, когда будете готовы',
			spinButton: 'Крутить',
			prizeBefore: 'Скидка рулетки',
			prizeAfter: 'действует ещё',
			prizeButton: 'Посмотреть приз',
		},
		ro: {
			spinMessage: 'Roata norocului te așteaptă — încearcă atunci când dorești',
			spinButton: 'Învârte',
			prizeBefore: 'Reducerea ruletei',
			prizeAfter: 'este valabilă încă',
			prizeButton: 'Vezi premiul',
		},
	}[language]

	let activeIndex = Math.max(0, slides.findIndex(slide => !slide.hidden))
	let availabilityKnown = false
	let rouletteAvailable = false
	let dismissMode = null
	let prizeTimer = null
	let activePrize = null
	let serverOffsetMs = 0

	const readSession = key => {
		try {
			return window.sessionStorage.getItem(`${storagePrefix}_${key}`)
		} catch (error) {
			return null
		}
	}

	const writeSession = (key, value) => {
		try {
			if (value) window.sessionStorage.setItem(`${storagePrefix}_${key}`, value)
			else window.sessionStorage.removeItem(`${storagePrefix}_${key}`)
		} catch (error) {
			// The announcement remains usable when browser storage is unavailable.
		}
	}

	const readDismissMode = () => {
		const mode = readSession('dismissed_mode')
		if (mode === 'spin' || mode === 'prize') return mode
		return readSession('dismissed_session') === '1' ? 'spin' : null
	}

	const showSlide = index => {
		if (!slides.length) return
		activeIndex = (index + slides.length) % slides.length
		slides.forEach((slide, slideIndex) => {
			const isActive = slideIndex === activeIndex
			slide.hidden = !isActive
			slide.setAttribute('aria-hidden', isActive ? 'false' : 'true')
		})
	}

	const showDismissInvite = mode => {
		if (!invite) return
		window.clearInterval(prizeTimer)
		prizeTimer = null
		activePrize = null
		dismissMode = mode
		siteHeader?.classList.add('header--wheel-invite')
		if (products) products.hidden = true
		if (fallback) fallback.hidden = true
		if (controls) controls.hidden = true
		invite.hidden = false
		invite.dataset.mode = mode
		if (inviteMessage) {
			inviteMessage.classList.remove('is-prize')
			inviteMessage.textContent = copy.spinMessage
		}
		if (inviteButton) inviteButton.textContent = mode === 'prize'
			? copy.prizeButton
			: copy.spinButton
		if (inviteButton) inviteButton.hidden = false
	}

	const showProductPromo = () => {
		window.clearInterval(prizeTimer)
		prizeTimer = null
		activePrize = null
		dismissMode = null
		siteHeader?.classList.remove('header--wheel-invite')
		if (invite) invite.hidden = true
		if (products) products.hidden = slides.length === 0
		if (fallback) fallback.hidden = slides.length > 0
		if (controls) controls.hidden = slides.length < 2
		if (slides.length) showSlide(activeIndex)
		inviteMessage?.classList.remove('is-prize')
	}

	const formatCountdown = expiresAt => {
		const expiration = Date.parse(expiresAt || '')
		if (!Number.isFinite(expiration)) return ''
		const remaining = expiration - (Date.now() + serverOffsetMs)
		if (remaining <= 0) return ''
		const totalSeconds = Math.floor(remaining / 1000)
		const hours = Math.floor(totalSeconds / 3600)
		const minutes = Math.floor((totalSeconds % 3600) / 60)
		const seconds = totalSeconds % 60
		const pad = value => String(value).padStart(2, '0')
		return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`
	}

	const showPrizeTimer = prize => {
		if (!invite || !prize?.expires_at) return false
		window.clearInterval(prizeTimer)
		activePrize = prize
		dismissMode = 'prize'
		siteHeader?.classList.add('header--wheel-invite')
		if (products) products.hidden = true
		if (fallback) fallback.hidden = true
		if (controls) controls.hidden = true
		invite.hidden = false
		invite.dataset.mode = 'prize'
		inviteMessage?.classList.add('is-prize')
		if (inviteButton) inviteButton.hidden = true

		const tick = () => {
			const time = formatCountdown(activePrize?.expires_at)
			if (!time) {
				writeSession('dismissed_mode', '')
				showProductPromo()
				return
			}
			if (!inviteMessage) return
			const title = activePrize.title || `−${Number(activePrize.value)}%`
			const previousExpiry = inviteMessage.dataset.rouletteExpiresAt
			let timer = inviteMessage.querySelector('.header__wheel-countdown')
			if (!timer || previousExpiry !== activePrize.expires_at) {
				const copyText = document.createElement('span')
				copyText.className = 'header__wheel-copy'
				copyText.textContent = `${copy.prizeBefore} ${title} ${copy.prizeAfter} `

				timer = document.createElement('time')
				timer.className = 'header__wheel-countdown'
				timer.dateTime = activePrize.expires_at
				inviteMessage.replaceChildren(
					copyText,
					timer
				)
				inviteMessage.dataset.rouletteExpiresAt = activePrize.expires_at
			}
			timer.textContent = time
		}

		tick()
		prizeTimer = window.setInterval(tick, 1000)
		return true
	}

	const applyDismissState = () => {
		const storedMode = readDismissMode()
		if (invite && storedMode && availabilityKnown && rouletteAvailable) {
			showDismissInvite(storedMode)
			return
		}
		showProductPromo()
	}

	const applyRouletteAvailability = detail => {
		availabilityKnown = true
		rouletteAvailable = Boolean(detail?.available)
		const serverTime = Date.parse(detail?.serverTime || '')
		if (Number.isFinite(serverTime)) serverOffsetMs = serverTime - Date.now()
		if (detail?.activePrize?.expires_at) {
			showPrizeTimer(detail.activePrize)
			return
		}
		if (!rouletteAvailable) {
			writeSession('dismissed_mode', '')
			showProductPromo()
			return
		}
		applyDismissState()
	}

	const refreshRouletteAvailability = () => {
		if (!rouletteStateUrl) return
		window.fetch(rouletteStateUrl, {
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { Accept: 'application/json' },
		})
			.then(response => response.ok ? response.json() : null)
			.then(payload => {
				if (!payload?.ok || !payload.data) return
				applyRouletteAvailability({
					available: Boolean(payload.data.can_spin || payload.data.active_prize),
					activePrize: payload.data.active_prize || null,
					serverTime: payload.data.server_time || null,
				})
			})
			.catch(() => {
				// Header promotions remain available if the optional roulette request fails.
			})
	}

	previousButton?.addEventListener('click', () => showSlide(activeIndex - 1))
	nextButton?.addEventListener('click', () => showSlide(activeIndex + 1))

	if (slides.length > 1 && !reducedMotion.matches) {
		window.setInterval(() => {
			if (
				document.hidden
				|| announcement.matches(':hover')
				|| announcement.contains(document.activeElement)
				|| (invite && !invite.hidden)
			) return
			showSlide(activeIndex + 1)
		}, 5000)
	}

	if (invite && accountScope) {
		applyDismissState()

		document.addEventListener('vn:roulette:closed', event => {
			const mode = event.detail?.hasActivePrize ? 'prize' : 'spin'
			writeSession('dismissed_mode', mode)
			if (mode === 'prize' && activePrize?.expires_at) {
				showPrizeTimer(activePrize)
				return
			}
			showDismissInvite(mode)
		})

		document.addEventListener('vn:roulette:availability', event => {
			applyRouletteAvailability(event.detail)
		})

		document.addEventListener('vn:roulette:expired', () => {
			writeSession('dismissed_mode', '')
			showProductPromo()
		})

		refreshRouletteAvailability()
	}

	showSlide(activeIndex)
})()
