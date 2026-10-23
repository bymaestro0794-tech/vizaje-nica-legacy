const carousel = document.querySelector('[data-home-blog]')

if (carousel && carousel.dataset.homeBlogReady !== 'true') {
	carousel.dataset.homeBlogReady = 'true'

	const viewport = carousel.querySelector('[data-home-blog-viewport]')
	const track = carousel.querySelector('[data-home-blog-track]')
	const previousButton = carousel.querySelector('[data-home-blog-prev]')
	const nextButton = carousel.querySelector('[data-home-blog-next]')
	const slides = Array.from(track?.querySelectorAll('[data-home-blog-slide]') ?? [])
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)')

	if (viewport && track && slides.length > 1) {
		const cloneSlide = (slide, side) => {
			const clone = slide.cloneNode(true)
			clone.dataset.homeBlogClone = side
			clone.setAttribute('aria-hidden', 'true')
			clone.querySelectorAll('a, button, [tabindex]').forEach((element) => {
				element.setAttribute('tabindex', '-1')
			})
			return clone
		}

		track.prepend(cloneSlide(slides[slides.length - 1], 'previous'))
		track.append(cloneSlide(slides[0], 'next'))

		const getStep = () => {
			const slideWidth = track.querySelector('[data-home-blog-slide]').getBoundingClientRect().width
			const gap = Number.parseFloat(window.getComputedStyle(track).columnGap) || 0
			return slideWidth + gap
		}
		let previousStep = getStep()

		const jumpToSlot = (slot) => {
			viewport.scrollLeft = slot * getStep()
		}

		const moveBy = (direction) => {
			const currentSlot = Math.round(viewport.scrollLeft / getStep())
			const targetSlot = Math.max(0, Math.min(slides.length + 1, currentSlot + direction))
			viewport.scrollTo({
				left: targetSlot * getStep(),
				behavior: reducedMotion.matches ? 'auto' : 'smooth',
			})
		}

		let settleTimer
		const settle = () => {
			window.clearTimeout(settleTimer)
			settleTimer = window.setTimeout(() => {
				const slot = Math.round(viewport.scrollLeft / getStep())
				if (slot === 0) {
					jumpToSlot(slides.length)
				} else if (slot === slides.length + 1) {
					jumpToSlot(1)
				}
			}, 120)
		}

		previousButton?.addEventListener('click', () => moveBy(-1))
		nextButton?.addEventListener('click', () => moveBy(1))
		viewport.addEventListener('scroll', settle, { passive: true })
		viewport.addEventListener('scrollend', settle)
		viewport.addEventListener('keydown', (event) => {
			if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return
			if (event.target.closest('a, button')) return
			event.preventDefault()
			moveBy(event.key === 'ArrowRight' ? 1 : -1)
		})

		let resizeTimer
		window.addEventListener('resize', () => {
			window.clearTimeout(resizeTimer)
			resizeTimer = window.setTimeout(() => {
				const slot = Math.round(viewport.scrollLeft / previousStep)
				const currentRealSlide = Math.max(0, Math.min(slides.length - 1, slot - 1))
				previousStep = getStep()
				jumpToSlot(currentRealSlide + 1)
			}, 100)
		})

		requestAnimationFrame(() => jumpToSlot(1))
	}
}
