(() => {
	'use strict'

	const manager = window.VNSiteDialogManager
	const dialogs = Array.from(document.querySelectorAll('[data-site-dialog]'))
	if (!dialogs.length || !manager) return

	const setOpen = (dialog, open) => {
		dialog.classList.toggle('is-open', open)
		dialog.setAttribute('aria-hidden', open ? 'false' : 'true')
		dialog.inert = !open
		document.documentElement.classList.toggle('site-dialog-lock', open)
		if (open) window.requestAnimationFrame(() => dialog.querySelector('.site-dialog__panel')?.focus({ preventScroll: true }))
	}

	dialogs.forEach(dialog => {
		const id = `campaign-${dialog.dataset.siteDialog}`
		let previousFocus = null
		const open = () => {
			if (manager.isDialogOpen()) return false
			previousFocus = document.activeElement
			setOpen(dialog, true)
			return true
		}
		const close = remember => {
			if (!dialog.classList.contains('is-open')) return
			setOpen(dialog, false)
			if (remember) manager.dismiss(id)
			if (previousFocus instanceof HTMLElement && previousFocus.isConnected) previousFocus.focus({ preventScroll: true })
		}

		dialog.addEventListener('click', event => {
			if (event.target.closest('[data-site-dialog-close]')) close(true)
			if (event.target.closest('[data-site-dialog-action]')) close(true)
		})
		document.addEventListener('keydown', event => {
			if (event.key === 'Escape' && dialog.classList.contains('is-open')) close(true)
		})

		manager.requestAutoOpen(id, open, {
			delayMs: Number(dialog.dataset.delayMs) || 0,
			priority: 40 + Math.max(-100, Math.min(100, Number(dialog.dataset.priority) || 0)),
			ttlMs: 2 * 60 * 60 * 1000,
		})
	})
})()
