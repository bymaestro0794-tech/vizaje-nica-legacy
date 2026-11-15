(() => {
	'use strict'

	const LAST_SHOWN_KEY = 'vn_site_dialog_last_shown_at'
	const GLOBAL_GAP_MS = 60 * 60 * 1000
	const DISMISS_MS = 4 * 24 * 60 * 60 * 1000
	const pending = new Map()
	let pumpTimer = 0

	const readNumber = key => {
		try {
			const value = Number(window.localStorage.getItem(key)) || 0
			if (value) return value
		} catch (error) {}
		const escapedKey = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
		const match = document.cookie.match(new RegExp(`(?:^|; )${escapedKey}=(\\d+)`))
		return match ? Number(match[1]) || 0 : 0
	}
	const writeNumber = (key, value) => {
		try {
			window.localStorage.setItem(key, String(value))
		} catch (error) {
			const maxAge = key === LAST_SHOWN_KEY ? 3600 : Math.floor(DISMISS_MS / 1000)
			document.cookie = `${key}=${Number(value) || 0}; Max-Age=${maxAge}; Path=/; SameSite=Lax`
		}
	}
	const isVisible = element => {
		if (!element || element.hidden) return false
		const style = window.getComputedStyle(element)
		return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0' && element.getClientRects().length > 0
	}
	const cookieUiIsVisible = () => (
		isVisible(document.querySelector('[data-cookie-banner]')) ||
		isVisible(document.querySelector('.cookie_on')) ||
		isVisible(document.querySelector('.cookie-panel.is-open'))
	)
	const dialogIsOpen = () => Array.from(document.querySelectorAll('[aria-modal="true"], dialog[open]')).some(isVisible)
	const dismissalKey = id => `vn_site_dialog_dismissed_until_${String(id).replace(/[^a-zA-Z0-9_-]/g, '_')}`

	const isDismissed = id => {
		const key = dismissalKey(id)
		const until = readNumber(key)
		if (until > Date.now()) return true
		if (until) {
			try { window.localStorage.removeItem(key) } catch (error) {}
		}
		return false
	}
	const dismissUntil = (id, until) => writeNumber(dismissalKey(id), Math.max(Date.now(), Number(until) || 0))
	const dismiss = id => dismissUntil(id, Date.now() + DISMISS_MS)
	const cancel = id => {
		const request = pending.get(String(id))
		if (!request) return false
		pending.delete(String(id))
		if (typeof request.onExpire === 'function') request.onExpire()
		return true
	}

	const expirePending = () => {
		const now = Date.now()
		pending.forEach((request, id) => {
			if (request.expiresAt <= now || isDismissed(id)) {
				pending.delete(id)
				if (typeof request.onExpire === 'function') request.onExpire()
			}
		})
	}

	const pump = () => {
		pumpTimer = 0
		expirePending()
		if (!pending.size) return

		const remainingGap = GLOBAL_GAP_MS - (Date.now() - readNumber(LAST_SHOWN_KEY))
		if (remainingGap > 0 || cookieUiIsVisible() || dialogIsOpen() || document.hidden || document.readyState !== 'complete') {
			pumpTimer = window.setTimeout(pump, Math.max(350, Math.min(1000, remainingGap > 0 ? remainingGap : 1000)))
			return
		}

		const now = Date.now()
		const ready = Array.from(pending.entries())
			.filter(([, request]) => request.readyAt <= now)
			.sort((left, right) => right[1].priority - left[1].priority || left[1].sequence - right[1].sequence)
		if (ready.length) {
			const [id, request] = ready[0]
			pending.delete(id)
			let opened = false
			try { opened = request.open() === true } catch (error) {}
			if (opened) {
				writeNumber(LAST_SHOWN_KEY, Date.now())
				document.dispatchEvent(new CustomEvent('vn:site-dialog:shown', { detail: { id } }))
			} else if (typeof request.onExpire === 'function') {
				request.onExpire()
			}
		}
		if (pending.size) pumpTimer = window.setTimeout(pump, 500)
	}

	const requestAutoOpen = (id, open, options = {}) => {
		id = String(id)
		if (typeof open !== 'function' || isDismissed(id)) return false
		if (pending.has(id)) return true
		const now = Date.now()
		pending.set(id, {
			open,
			priority: Number(options.priority) || 0,
			readyAt: now + Math.max(0, Number(options.delayMs) || 0),
			expiresAt: now + Math.max(1000, Number(options.ttlMs) || 15 * 60 * 1000),
			sequence: now + Math.random(),
			onExpire: options.onExpire,
		})
		if (!pumpTimer) pumpTimer = window.setTimeout(pump, Math.max(0, Number(options.delayMs) || 0))
		return true
	}
	const markShown = id => {
		writeNumber(LAST_SHOWN_KEY, Date.now())
		document.dispatchEvent(new CustomEvent('vn:site-dialog:shown', { detail: { id: String(id), manual: true } }))
	}

	window.VNSiteDialogManager = {
		requestAutoOpen,
		cancel,
		isDismissed,
		dismiss,
		dismissUntil,
		markShown,
		isDialogOpen: dialogIsOpen,
		globalGapMs: GLOBAL_GAP_MS,
		dismissMs: DISMISS_MS,
	}
})()
