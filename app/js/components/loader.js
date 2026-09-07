/**
 * App Loader
 *
 * Keeps the page hidden until full load,
 * then fades the loader out and reveals the page.
 */
window.addEventListener('load', function () {
	const loader = document.querySelector('#appLoader')

	if (!loader) {
		document.body.classList.remove('is-loading')
		return
	}

	setTimeout(function () {
		document.body.classList.remove('is-loading')
		loader.classList.add('is-hidden')
	}, 650)

	setTimeout(function () {
		loader.remove()
	}, 1200)
})
