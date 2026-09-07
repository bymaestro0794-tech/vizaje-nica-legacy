document.addEventListener('DOMContentLoaded', () => {
	const sections = document.querySelectorAll('[data-home-brand-showcase]')

	sections.forEach(section => {
		const viewport = section.querySelector('[data-home-brand-viewport]')

		const list = section.querySelector('[data-home-brand-list]')

		const prevButton = section.querySelector('[data-home-brand-prev]')

		const nextButton = section.querySelector('[data-home-brand-next]')

		if (!viewport || !list || !prevButton || !nextButton) {
			return
		}

		const getStep = () => {
			const product = list.querySelector('.home-brand-showcase__product')

			if (!product) {
				return viewport.clientWidth
			}

			const styles = window.getComputedStyle(list)

			const gap = parseFloat(styles.columnGap || styles.gap || '0')

			return product.getBoundingClientRect().width + gap
		}

		const updateButtons = () => {
			const maxScroll = viewport.scrollWidth - viewport.clientWidth

			prevButton.disabled = viewport.scrollLeft <= 2

			nextButton.disabled = viewport.scrollLeft >= maxScroll - 2
		}

		prevButton.addEventListener('click', () => {
			viewport.scrollBy({
				left: -getStep(),
				behavior: 'smooth'
			})
		})

		nextButton.addEventListener('click', () => {
			viewport.scrollBy({
				left: getStep(),
				behavior: 'smooth'
			})
		})

		viewport.addEventListener('scroll', updateButtons, {
			passive: true
		})

		window.addEventListener('resize', updateButtons)

		requestAnimationFrame(updateButtons)
	})
})
