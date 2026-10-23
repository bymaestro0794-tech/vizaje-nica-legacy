(() => {
	'use strict'

	const modal = document.querySelector('[data-roulette-modal]')
	if (!modal) return

	const language = modal.dataset.language === 'ro' ? 'ro' : 'ru'
	const accountScope = modal.dataset.accountScope || 'account'
	const storageKey = `vn_roulette_${accountScope}`
	const inviteCooldownMs = 7 * 24 * 60 * 60 * 1000
	const activeBrowseDelayMs = 25 * 1000
	const translations = {
		ru: {
			title: 'Рулетка удачи',
			description: 'Крутите колесо и узнайте, какая скидка вам выпадет. Приз определяется сервером и сохраняется в вашем аккаунте.',
			spin: 'Крутить',
			spinning: 'Колесо крутится…',
			checking: 'Проверяем доступность…',
			resultLabel: 'Ваш приз',
			resultDescription: 'Приз зафиксирован за вашим аккаунтом. Используйте его в течение 24 часов.',
			resultExpiry: 'Действует ещё',
			applyPrize: 'Применить приз',
			applyingPrize: 'Применяем…',
			appliedPrize: 'Приз применён',
			applyError: 'Не удалось применить приз. Откройте корзину и попробуйте ещё раз.',
			shop: 'Перейти к покупкам',
			rules: 'Правила',
			defaultRules: 'Одна прокрутка доступна с понедельника 00:00 до следующего понедельника 00:00. Приз действует 24 часа. Скидки не суммируются; MAC, Anastasia Beverly Hills (ABH) и SHIK исключены.',
			wheelCaption: 'Приз определяется до начала вращения',
			networkError: 'Не удалось связаться с рулеткой. Проверьте соединение и попробуйте ещё раз.',
			spinError: 'Сейчас вращение недоступно. Обновите страницу и попробуйте позже.',
			activePrize: 'У вас уже есть активный приз.',
			completedOrderRequired: 'Для участия нужен завершённый заказ.',
			weeklyWindowNotStarted: 'Рулетка откроется в понедельник в 00:00.',
			weeklySpinUsed: 'Вы уже использовали вращение этой недели. Следующее будет доступно с понедельника 00:00.',
			expired: 'Срок действия приза истёк.',
			hours: 'ч',
			minutes: 'м',
			seconds: 'с',
			gift: 'ПОДАРОК',
			freeShipping: 'ДОСТАВКА',
		},
		ro: {
			title: 'Roata norocului',
			description: 'Învârte roata și află ce reducere ți se potrivește. Premiul este ales de server și salvat în contul tău.',
			spin: 'Învârte roata',
			spinning: 'Roata se învârte…',
			checking: 'Verificăm disponibilitatea…',
			resultLabel: 'Premiul tău',
			resultDescription: 'Premiul a fost salvat în contul tău. Folosește-l în următoarele 24 de ore.',
			resultExpiry: 'Valabil încă',
			applyPrize: 'Aplică premiul',
			applyingPrize: 'Se aplică…',
			appliedPrize: 'Premiul este aplicat',
			applyError: 'Premiul nu a putut fi aplicat. Deschide coșul și încearcă din nou.',
			shop: 'Mergi la cumpărături',
			rules: 'Regulament',
			defaultRules: 'O rotire este disponibilă de luni, ora 00:00, până luni următoare la ora 00:00. Premiul este valabil 24 de ore. MAC, Anastasia Beverly Hills (ABH) și SHIK sunt excluse.',
			wheelCaption: 'Premiul este stabilit înainte de rotire',
			networkError: 'Nu ne-am putut conecta la roată. Verifică conexiunea și încearcă din nou.',
			spinError: 'Rotirea nu este disponibilă momentan. Reîncarcă pagina și încearcă mai târziu.',
			activePrize: 'Ai deja un premiu activ.',
			completedOrderRequired: 'Este necesară o comandă finalizată pentru participare.',
			weeklyWindowNotStarted: 'Roata se deschide luni la ora 00:00.',
			weeklySpinUsed: 'Ai folosit deja rotirea din această săptămână. Următoarea va fi disponibilă luni la 00:00.',
			expired: 'Perioada de valabilitate a premiului a expirat.',
			hours: 'h',
			minutes: 'm',
			seconds: 's',
			gift: 'CADOU',
			freeShipping: 'LIVRARE',
		},
	}
	const copy = translations[language]
	const dialog = modal.querySelector('.roulette-modal__dialog')
	const title = modal.querySelector('[data-roulette-title]')
	const description = modal.querySelector('[data-roulette-description]')
	const closeButtons = modal.querySelectorAll('[data-roulette-close]')
	const spinButton = modal.querySelector('[data-roulette-spin]')
	const spinLabel = modal.querySelector('[data-roulette-spin-label]')
	const error = modal.querySelector('[data-roulette-error]')
	const result = modal.querySelector('[data-roulette-result]')
	const resultLabel = modal.querySelector('[data-roulette-result-label]')
	const resultTitle = modal.querySelector('[data-roulette-result-title]')
	const countdown = modal.querySelector('[data-roulette-countdown]')
	const applyButton = modal.querySelector('[data-roulette-apply]')
	const shopLink = modal.querySelector('[data-roulette-shop]')
	const rulesToggle = modal.querySelector('[data-roulette-rules-toggle]')
	const rules = modal.querySelector('[data-roulette-rules]')
	const wheel = modal.querySelector('[data-roulette-wheel]')
	const disc = modal.querySelector('[data-roulette-disc]')
	const labels = modal.querySelector('[data-roulette-labels]')
	const wheelCaption = modal.querySelector('[data-roulette-wheel-caption]')
	const entryButtons = document.querySelectorAll('[data-roulette-open]')
	const entryCards = document.querySelectorAll('[data-roulette-entry]')
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)')

	const state = {
		api: null,
		csrfToken: '',
		serverOffsetMs: 0,
		prizes: [],
		activePrize: null,
		rotation: 0,
		requestId: null,
		spinning: false,
		modalOpen: false,
		focusBeforeOpen: null,
		interactionStarted: false,
		activeBrowseMs: 0,
		lastTick: performance.now(),
		autoOpened: false,
		prizeExpired: false,
	}

	const storage = {
		get(key, session = false) {
			try {
				return (session ? window.sessionStorage : window.localStorage).getItem(`${storageKey}_${key}`)
			} catch (storageError) {
				return null
			}
		},
		set(key, value, session = false) {
			try {
				;(session ? window.sessionStorage : window.localStorage).setItem(`${storageKey}_${key}`, String(value))
			} catch (storageError) {
				return false
			}
			return true
		},
	}
	state.interactionStarted = storage.get('interaction_started', true) === '1'
	state.activeBrowseMs = Math.max(0, Number(storage.get('active_browse_ms', true)) || 0)

	const setText = (element, value) => {
		if (element && typeof value === 'string' && value.trim()) element.textContent = value
	}

	const isVisible = element => {
		if (!element || element.hidden) return false
		const style = window.getComputedStyle(element)
		return style.display !== 'none' && style.visibility !== 'hidden' && element.getClientRects().length > 0
	}

	const cookieUiIsVisible = () => (
		isVisible(document.querySelector('[data-cookie-banner]')) ||
		isVisible(document.querySelector('.cookie_on')) ||
		isVisible(document.querySelector('.cookie-panel.is-open'))
	)

	const anotherDialogIsOpen = () => {
		const selectors = [
			'[aria-modal="true"]:not([data-roulette-modal])',
			'.welcome-offer.is-open',
			'.auth-drawer.is-open',
			'.cart-drawer.is-open',
			'.quick-view.is-open',
			'.vn-feedback-modal.is-open',
			'dialog[open]',
		]
		return selectors.some(selector => Array.from(document.querySelectorAll(selector)).some(isVisible))
	}

	const setOpen = open => {
		state.modalOpen = open
		modal.classList.toggle('is-open', open)
		modal.setAttribute('aria-hidden', open ? 'false' : 'true')
		modal.inert = !open
		document.documentElement.classList.toggle('roulette-lock', open)
		if (open) {
			state.focusBeforeOpen = document.activeElement
			window.requestAnimationFrame(() => modal.querySelector('[data-roulette-close]')?.focus({ preventScroll: true }))
			if (window.gsap && !reducedMotion.matches) {
				window.gsap.fromTo(dialog, { y: 16, scale: 0.985 }, {
					y: 0,
					scale: 1,
					duration: 0.42,
					ease: 'power3.out',
					clearProps: 'transform',
				})
			}
			return
		}
		if (state.focusBeforeOpen instanceof HTMLElement && state.focusBeforeOpen.isConnected) {
			state.focusBeforeOpen.focus({ preventScroll: true })
		}
	}

	const openModal = ({ manual = false } = {}) => {
		if (state.modalOpen || anotherDialogIsOpen() || cookieUiIsVisible()) return false
		if (!state.api && !manual) return false
		if (!manual && !state.activePrize && !state.api?.can_spin) return false
		if (state.activePrize) {
			showPrize(state.activePrize, { animate: false })
		} else {
			showSpinState()
			if (manual && !state.api) {
				showError(copy.networkError)
			} else if (manual && !state.api.can_spin) {
				const reasonMessage = {
					completed_order_required: copy.completedOrderRequired,
					active_prize: copy.activePrize,
					payment_pending: copy.spinError,
					cooldown: copy.spinError,
					weekly_window_not_started: copy.weeklyWindowNotStarted,
					weekly_spin_used: copy.weeklySpinUsed,
					roulette_hidden: copy.spinError,
					roulette_disabled: copy.spinError,
					interval_not_configured: copy.spinError,
					prizes_not_configured: copy.spinError,
				}[state.api.reason] || copy.spinError
				showError(reasonMessage)
			}
		}
		setOpen(true)
		if (!manual) storage.set('invite_at', Date.now())
		return true
	}

	const closeModal = () => {
		if (!state.modalOpen) return
		const activePrizeIsValid = Boolean(
			state.activePrize
			&& Date.parse(state.activePrize.expires_at) > Date.now() + state.serverOffsetMs
		)
		setOpen(false)
		storage.set('dismissed_session', '1', true)
		storage.set('dismissed_mode', activePrizeIsValid ? 'prize' : 'spin', true)
		storage.set('invite_at', Date.now())
		document.dispatchEvent(new CustomEvent('vn:roulette:closed', {
			detail: { hasActivePrize: activePrizeIsValid },
		}))
	}

	const formatCountdown = expiresAt => {
		const expiration = Date.parse(expiresAt)
		if (!Number.isFinite(expiration)) return ''
		const remaining = expiration - (Date.now() + state.serverOffsetMs)
		if (remaining <= 0) return copy.expired
		const secondsTotal = Math.floor(remaining / 1000)
		const hours = Math.floor(secondsTotal / 3600)
		const minutes = Math.floor((secondsTotal % 3600) / 60)
		const seconds = secondsTotal % 60
		const pad = value => String(value).padStart(2, '0')
		return `${pad(hours)}${copy.hours} ${pad(minutes)}${copy.minutes} ${pad(seconds)}${copy.seconds}`
	}

	const updateCartCountdowns = () => {
		document.querySelectorAll('[data-roulette-cart-countdown]').forEach(element => {
			const value = formatCountdown(element.dataset.rouletteExpiresAt)
			element.textContent = value || copy.expired
		})
	}

	const emitAvailability = () => {
		document.dispatchEvent(new CustomEvent('vn:roulette:availability', {
			detail: {
				available: Boolean(state.api?.can_spin || state.activePrize),
				activePrize: state.activePrize,
				serverTime: state.api?.server_time || null,
			},
		}))
	}

	const expireActivePrize = () => {
		if (state.prizeExpired) return
		state.prizeExpired = true
		state.activePrize = null
		if (state.api) state.api.active_prize = null
		updateEntries()
		emitAvailability()
		document.dispatchEvent(new CustomEvent('vn:roulette:expired'))
	}

	const updateCountdown = () => {
		if (!state.activePrize) return
		const value = formatCountdown(state.activePrize.expires_at)
		if (countdown) countdown.textContent = value
		if (!value || value === copy.expired) expireActivePrize()
	}

	const showSpinState = () => {
		state.activePrize = null
		result.hidden = true
		if (applyButton) applyButton.hidden = true
		spinButton.hidden = false
		spinButton.disabled = !state.api?.can_spin
		spinLabel.textContent = copy.spin
		setText(resultLabel, copy.resultLabel)
		setText(title, state.api?.settings?.title || copy.title)
		setText(description, state.api?.settings?.description || copy.description)
		setText(rules, state.api?.settings?.rules || copy.defaultRules)
		setText(rulesToggle, copy.rules)
		setText(wheelCaption, copy.wheelCaption)
		error.hidden = true
		wheelCaption.classList.remove('is-spinning')
		wheel?.classList.remove('is-winner')
	}

	const showPrize = (prize, { animate = false } = {}) => {
		if (!prize) return
		state.activePrize = prize
		state.prizeExpired = false
		result.hidden = false
		spinButton.hidden = true
		if (applyButton) {
			applyButton.hidden = false
			applyButton.disabled = false
			applyButton.textContent = copy.applyPrize
			applyButton.removeAttribute('data-applied')
		}
		resultLabel.textContent = copy.resultLabel
		resultTitle.textContent = prize.title || `−${Number(prize.value)}%`
		description.textContent = copy.resultDescription
		wheelCaption.textContent = copy.wheelCaption
		wheelCaption.classList.remove('is-spinning')
		wheel?.classList.add('is-winner')
		error.hidden = true
		updateCountdown()
		if (animate && window.gsap && !reducedMotion.matches) {
			window.gsap.fromTo(result, { y: 10, opacity: 0 }, { y: 0, opacity: 1, duration: 0.35, ease: 'power2.out' })
		}
	}

	const renderWheel = prizes => {
		state.prizes = Array.isArray(prizes) ? prizes.filter(prize => Number(prize.probability) > 0) : []
		labels.replaceChildren()
		if (!state.prizes.length) {
			disc.style.background = '#ededeb'
			return
		}

		let startAngle = 0
		const stops = []
		const total = state.prizes.reduce((sum, prize) => sum + Number(prize.probability), 0)
		state.prizes.forEach((prize, index) => {
			const sweep = (Number(prize.probability) / total) * 360
			const endAngle = startAngle + sweep
			const dark = index % 2 === 0
			stops.push(`${dark ? '#1d1d1d' : '#ededeb'} ${startAngle}deg ${endAngle}deg`)

			const centerAngle = startAngle + sweep / 2
			const radians = (centerAngle - 90) * Math.PI / 180
			const radius = 35
			const sector = document.createElement('div')
			sector.className = `roulette-wheel__sector${dark ? ' roulette-wheel__sector--dark' : ''}`
			sector.dataset.prizeCode = prize.code
			sector.style.left = `${50 + Math.cos(radians) * radius}%`
			sector.style.top = `${50 + Math.sin(radians) * radius}%`
			sector.dataset.centerAngle = String(centerAngle)

			const icon = document.createElement('span')
			icon.className = 'roulette-wheel__sector-icon'
			icon.setAttribute('aria-hidden', 'true')
			const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
			iconSvg.setAttribute('viewBox', '0 0 24 24')
			iconSvg.setAttribute('fill', 'none')
			iconSvg.setAttribute('stroke', 'currentColor')
			iconSvg.setAttribute('stroke-width', '1.5')
			iconSvg.setAttribute('stroke-linecap', 'round')
			iconSvg.setAttribute('stroke-linejoin', 'round')
			if (prize.type === 'gift') {
				const giftBox = document.createElementNS('http://www.w3.org/2000/svg', 'path')
				giftBox.setAttribute('d', 'M4 10h16v11H4zM2.5 6.5h19v4h-19zM12 6.5V21M12 6.5C9.5 6.5 7 5.5 7 3.5 7 1.7 9.4 1 12 6.5Zm0 0c2.5-5.5 5-4.8 5-3 0 2-2.5 3-5 3Z')
				iconSvg.appendChild(giftBox)
			} else {
				const tagPath = document.createElementNS('http://www.w3.org/2000/svg', 'path')
				tagPath.setAttribute('d', 'M20 10.59 13.41 4A2 2 0 0 0 12 3.41H5a2 2 0 0 0-2 2v7a2 2 0 0 0 .59 1.41l6.59 6.59a2 2 0 0 0 2.82 0L20 13.41a2 2 0 0 0 0-2.82Z')
				const tagHole = document.createElementNS('http://www.w3.org/2000/svg', 'circle')
				tagHole.setAttribute('cx', '7.5')
				tagHole.setAttribute('cy', '7.5')
				tagHole.setAttribute('r', '0.75')
				iconSvg.append(tagPath, tagHole)
			}
			icon.appendChild(iconSvg)
			const label = document.createElement('span')
			label.className = 'roulette-wheel__sector-label'
			label.textContent = prize.type === 'gift'
				? copy.gift
				: prize.type === 'free_shipping'
					? copy.freeShipping
					: `−${Number(prize.value)}%`
			sector.append(icon, label)
			labels.appendChild(sector)
			startAngle = endAngle
		})

		disc.style.background = `conic-gradient(${stops.join(', ')})`
		wheel.setAttribute('aria-label', state.prizes.map(prize => prize.type === 'gift'
			? (language === 'ro' ? 'Cadou' : 'Подарок')
			: prize.type === 'free_shipping'
				? (language === 'ro' ? 'Livrare gratuită' : 'Бесплатная доставка')
				: `−${Number(prize.value)}%`).join(', '))
		setRotation(0)
	}

	const setRotation = rotation => {
		state.rotation = rotation
		disc.style.transform = `rotate(${rotation}deg)`
		labels.querySelectorAll('.roulette-wheel__sector').forEach(sector => {
			sector.style.transform = `translate(-50%, -50%) rotate(${-rotation}deg)`
		})
	}

	const animateToPrize = prize => {
		const targetSector = state.prizes.find(sector => sector.code === prize.code)
		if (!targetSector) return Promise.resolve()

		let precedingProbability = 0
		for (const sector of state.prizes) {
			if (sector.code === prize.code) break
			precedingProbability += Number(sector.probability)
		}
		const sweep = (Number(targetSector.probability) / state.prizes.reduce((sum, item) => sum + Number(item.probability), 0)) * 360
		const centerAngle = (precedingProbability / 100) * 360 + sweep / 2
		const currentModulo = ((state.rotation % 360) + 360) % 360
		const desiredModulo = ((360 - centerAngle) % 360 + 360) % 360
		const delta = (desiredModulo - currentModulo + 360) % 360
		const targetRotation = state.rotation + (6 * 360) + delta

		if (!window.gsap || reducedMotion.matches) {
			setRotation(targetRotation)
			return Promise.resolve()
		}

		return new Promise(resolve => {
			const tweenState = { rotation: state.rotation }
			window.gsap.to(tweenState, {
				rotation: targetRotation,
				duration: 5,
				ease: 'power4.out',
				onUpdate: () => setRotation(tweenState.rotation),
				onComplete: resolve,
			})
		})
	}

	const uuid = () => {
		if (window.crypto?.randomUUID) return window.crypto.randomUUID()
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, char => {
			const random = Math.random() * 16 | 0
			const value = char === 'x' ? random : (random & 0x3 | 0x8)
			return value.toString(16)
		})
	}

	const getRequestId = () => {
		if (state.requestId) return state.requestId
		state.requestId = storage.get('pending_request', true) || uuid()
		storage.set('pending_request', state.requestId, true)
		return state.requestId
	}

	const fetchState = async () => {
		const response = await fetch(modal.dataset.stateUrl, {
			method: 'GET',
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { Accept: 'application/json' },
		})
		const payload = await response.json()
		if (!response.ok || !payload.ok || !payload.data) throw new Error(payload.error || 'state_unavailable')
		return payload.data
	}

	const updateEntries = () => {
		// Profile/offers links are an intentional manual entry point. The server
		// decides inside the dialog whether the weekly window is open. Automatic
		// teaser/opening remains controlled by emitAvailability and auto-open.
		const canOpenManually = Boolean(state.api?.visible)
		entryCards.forEach(entry => { entry.hidden = !canOpenManually })
	}

	const applyState = apiState => {
		state.api = apiState
		state.csrfToken = apiState.csrf_token || ''
		const serverTime = Date.parse(apiState.server_time)
		if (Number.isFinite(serverTime)) state.serverOffsetMs = serverTime - Date.now()
		setText(title, apiState.settings?.title || copy.title)
		setText(description, apiState.settings?.description || copy.description)
		setText(rules, apiState.settings?.rules || copy.defaultRules)
		setText(rulesToggle, copy.rules)
		renderWheel(apiState.prizes)
		if (apiState.active_prize) showPrize(apiState.active_prize)
		else showSpinState()
		updateEntries()
		emitAvailability()
	}

	const refreshState = async () => {
		try {
			applyState(await fetchState())
			return state.api
		} catch (fetchError) {
			state.api = null
			updateEntries()
			return null
		}
	}

	const showError = (message) => {
		error.textContent = message
		error.hidden = false
	}

	const applyPrize = async () => {
		if (!applyButton || !state.activePrize || applyButton.disabled) return
		applyButton.disabled = true
		applyButton.textContent = copy.applyingPrize
		error.hidden = true
		try {
			const response = await fetch('/ajax/discount_choice', {
				method: 'POST',
				credentials: 'same-origin',
				cache: 'no-store',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
				},
				body: new URLSearchParams({ choice: 'roulette', lang: language }),
			})
			const payload = await response.json()
			if (!response.ok || payload.status !== 'ok') throw new Error(payload.code || 'apply_failed')
			applyButton.textContent = copy.appliedPrize
			applyButton.dataset.applied = '1'
			document.dispatchEvent(new CustomEvent('vn:roulette:discount-applied', { detail: payload }))
			window.setTimeout(closeModal, 500)
		} catch (applyError) {
			applyButton.disabled = false
			applyButton.textContent = copy.applyPrize
			showError(copy.applyError)
		}
	}

	const spin = async () => {
		if (state.spinning || !state.api?.can_spin || !state.csrfToken) return
		state.spinning = true
		spinButton.disabled = true
		spinLabel.textContent = copy.spinning
		wheelCaption.textContent = copy.checking
		wheelCaption.classList.add('is-spinning')
		error.hidden = true

		try {
			const response = await fetch(modal.dataset.spinUrl, {
				method: 'POST',
				credentials: 'same-origin',
				cache: 'no-store',
				headers: {
					Accept: 'application/json',
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({ request_id: getRequestId(), csrf_token: state.csrfToken }),
			})
			const payload = await response.json()
			if (!response.ok || !payload.ok || !payload.prize) {
				if (payload.error === 'active_prize') {
					storage.set('pending_request', '', true)
					state.requestId = null
					await refreshState()
					if (state.activePrize) showPrize(state.activePrize, { animate: true })
					else showError(copy.activePrize)
					return
				}
				if (payload.error === 'completed_order_required') {
					showError(copy.completedOrderRequired)
					return
				}
			if (response.status === 409 || response.status === 403) {
				await refreshState()
				showError(state.activePrize ? copy.activePrize : copy.spinError)
				return
			}
				throw new Error(payload.error || 'spin_failed')
			}

			const prize = payload.prize
			state.requestId = null
			storage.set('pending_request', '', true)
			await animateToPrize(prize)
			showPrize(prize, { animate: true })
			if (state.api) state.api.can_spin = false
			updateEntries()
			emitAvailability()
		} catch (spinError) {
			showError(spinError instanceof TypeError ? copy.networkError : copy.spinError)
			wheelCaption.textContent = copy.wheelCaption
			wheelCaption.classList.remove('is-spinning')
		} finally {
			state.spinning = false
			if (!state.activePrize) {
				spinButton.disabled = !state.api?.can_spin
				spinLabel.textContent = copy.spin
			}
		}
	}

	const userInteracted = () => {
		state.interactionStarted = true
		storage.set('interaction_started', '1', true)
	}

	document.addEventListener('pointerdown', userInteracted, { passive: true, once: true })
	document.addEventListener('pointermove', userInteracted, { passive: true, once: true })
	document.addEventListener('touchstart', userInteracted, { passive: true, once: true })
	document.addEventListener('keydown', userInteracted, { passive: true, once: true })
	document.addEventListener('scroll', userInteracted, { passive: true, once: true })

	window.setInterval(() => {
		const now = performance.now()
		const elapsed = Math.min(now - state.lastTick, 1500)
		state.lastTick = now
		if (state.interactionStarted && !document.hidden) {
			state.activeBrowseMs += elapsed
			storage.set('active_browse_ms', state.activeBrowseMs, true)
		}

		updateCartCountdowns()
		if (state.activePrize) updateCountdown()
		if (state.autoOpened || state.activeBrowseMs < activeBrowseDelayMs || !state.api || !state.api.visible) return
		if (storage.get('dismissed_session', true)) {
			state.autoOpened = true
			return
		}
		if (!state.activePrize) {
			const lastInviteAt = Number(storage.get('invite_at')) || 0
			if (!state.api.can_spin || Date.now() - lastInviteAt < inviteCooldownMs) {
				state.autoOpened = true
				return
			}
		}
		if (anotherDialogIsOpen() || cookieUiIsVisible()) return
		state.autoOpened = openModal()
	}, 500)

	closeButtons.forEach(button => button.addEventListener('click', closeModal))
	spinButton.addEventListener('click', spin)
	applyButton?.addEventListener('click', applyPrize)
	rulesToggle.addEventListener('click', () => {
		const expanded = rulesToggle.getAttribute('aria-expanded') === 'true'
		rulesToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true')
		rules.hidden = expanded
	})
	entryButtons.forEach(button => button.addEventListener('click', async () => {
		if (!state.api) await refreshState()
		openModal({ manual: true })
	}))
	shopLink?.addEventListener('click', closeModal)

	document.addEventListener('keydown', event => {
		if (!state.modalOpen) return
		if (event.key === 'Escape') {
			event.preventDefault()
			closeModal()
			return
		}
		if (event.key !== 'Tab') return
		const focusable = Array.from(modal.querySelectorAll('button:not([disabled]):not([hidden]), a[href]:not([hidden])'))
		if (!focusable.length) return
		const first = focusable[0]
		const last = focusable[focusable.length - 1]
		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault()
			last.focus()
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault()
			first.focus()
		}
	})

	refreshState()
})()
