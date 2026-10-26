/**
 * App Loader
 *
 * Reveal the shell as soon as the HTML is ready. Images, analytics and
 * non-critical widgets must not block the first usable screen.
 */
;(function () {
	'use strict'

	let revealed = false

	const reveal = () => {
		if (revealed) return
		revealed = true

		const loader = document.querySelector('#appLoader')
		document.body.classList.remove('is-loading')

		if (!loader) return

		loader.classList.add('is-hidden')
		window.setTimeout(() => loader.remove(), 500)
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', reveal, { once: true })
	} else {
		reveal()
	}

	// Safety net for a page with a broken or very slow DOM dependency.
	window.setTimeout(reveal, 1800)
}())
