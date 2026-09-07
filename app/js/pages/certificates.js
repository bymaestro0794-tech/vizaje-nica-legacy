function initCertificatesPage() {
	initBrandAccordions()
	initCertificateLightbox()
}

function initBrandAccordions() {
	const brands = document.querySelectorAll('[data-certificates-brand]')

	if (!brands.length) {
		return
	}

	brands.forEach(brand => {
		const trigger = brand.querySelector('[data-certificates-trigger]')

		if (!trigger) {
			return
		}

		trigger.addEventListener('click', () => {
			const isOpen = brand.classList.contains('is-open')

			brand.classList.toggle('is-open', !isOpen)
			trigger.setAttribute('aria-expanded', String(!isOpen))
		})
	})
}

function initCertificateLightbox() {
	const lightbox = document.querySelector('[data-certificate-lightbox]')

	if (!lightbox) {
		return
	}

	const image = lightbox.querySelector('[data-certificate-lightbox-image]')
	const title = lightbox.querySelector('[data-certificate-lightbox-title]')
	const originalLink = lightbox.querySelector('[data-certificate-original]')
	const openButtons = document.querySelectorAll('[data-certificate-open]')
	const closeButtons = lightbox.querySelectorAll('[data-certificate-close]')

	if (!image || !title || !originalLink || !openButtons.length) {
		return
	}

	let previousActiveElement = null
	let closeTimer = null

	const openLightbox = button => {
		const imageUrl = button.dataset.certificateImage
		const certificateTitle = button.dataset.certificateTitle || ''

		if (!imageUrl) {
			return
		}

		if (closeTimer) {
			window.clearTimeout(closeTimer)
			closeTimer = null
		}

		previousActiveElement = document.activeElement

		image.src = imageUrl
		image.alt = certificateTitle

		title.textContent = certificateTitle
		originalLink.href = imageUrl

		lightbox.classList.add('is-open')
		lightbox.setAttribute('aria-hidden', 'false')

		document.body.classList.add('is-certificate-lightbox-open')

		const closeButton = lightbox.querySelector('.certificate-lightbox__close')

		closeButton?.focus()
	}

	const closeLightbox = () => {
		if (!lightbox.classList.contains('is-open')) {
			return
		}

		lightbox.classList.remove('is-open')
		lightbox.setAttribute('aria-hidden', 'true')

		document.body.classList.remove('is-certificate-lightbox-open')

		closeTimer = window.setTimeout(() => {
			image.src = ''
			image.alt = ''

			title.textContent = ''
			originalLink.href = '#'
		}, 300)

		if (previousActiveElement instanceof HTMLElement) {
			previousActiveElement.focus()
		}
	}

	openButtons.forEach(button => {
		button.addEventListener('click', () => {
			openLightbox(button)
		})
	})

	closeButtons.forEach(button => {
		button.addEventListener('click', closeLightbox)
	})

	document.addEventListener('keydown', event => {
		if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
			closeLightbox()
		}
	})
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initCertificatesPage)
} else {
	initCertificatesPage()
}
