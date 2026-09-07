/* ==========================================================================
   Home Hero
   Custom slider
   ========================================================================== */

const initHero = hero => {
	const viewport = hero.querySelector('[data-hero-viewport]')
	const slides = [...hero.querySelectorAll('[data-hero-slide]')]

	if (!viewport || !slides.length) {
		return
	}

	const previousButton = hero.querySelector('[data-hero-previous]')
	const nextButton = hero.querySelector('[data-hero-next]')
	const cursor = hero.querySelector('[data-hero-cursor]')
	const current = hero.querySelector('[data-hero-current]')
	const total = hero.querySelector('[data-hero-total]')
	const progress = hero.querySelector('[data-hero-progress]')

	const delay = Number(hero.dataset.heroDuration) || 7000
	const transitionDuration = Number(hero.dataset.heroTransitionDuration) || 760

	const hasFinePointer = window.matchMedia(
		'(hover: hover) and (pointer: fine)'
	).matches

	const reducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches

	let activeIndex = 0
	let autoplayTimer = null
	let progressFrame = null
	let transitionTimer = null

	let isVisible = true
	let isAnimating = false

	let startX = 0
	let startY = 0
	let suppressClick = false

	const formatNumber = number => String(number).padStart(2, '0')

	const normalizeIndex = index => (index + slides.length) % slides.length

	const updateSlide = () => {
		slides.forEach((slide, index) => {
			const isActive = index === activeIndex
			const link = slide.querySelector('[data-hero-link]')

			slide.classList.toggle('is-active', isActive)
			slide.setAttribute('aria-hidden', String(!isActive))

			if (link) {
				link.tabIndex = isActive ? 0 : -1
			}
		})

		if (current) {
			current.textContent = formatNumber(activeIndex + 1)
		}

		if (total) {
			total.textContent = formatNumber(slides.length)
		}

		const theme = slides[activeIndex].dataset.cursorTheme || 'light'

		hero.dataset.progressTheme = theme

		if (cursor) {
			cursor.dataset.theme = theme
		}
	}

	const setProgress = value => {
		if (progress) {
			progress.style.transform = `scaleX(${value})`
		}
	}

	const stopAutoplay = () => {
		clearTimeout(autoplayTimer)
		cancelAnimationFrame(progressFrame)

		autoplayTimer = null
		progressFrame = null
	}

	const canAutoplay = () =>
		slides.length > 1 && isVisible && !document.hidden && !reducedMotion

	const startAutoplay = () => {
		stopAutoplay()
		setProgress(0)

		if (!canAutoplay()) {
			return
		}

		const startedAt = performance.now()

		const animateProgress = time => {
			const value = Math.min((time - startedAt) / delay, 1)

			setProgress(value)

			if (value < 1 && canAutoplay()) {
				progressFrame = requestAnimationFrame(animateProgress)
			}
		}

		progressFrame = requestAnimationFrame(animateProgress)

		autoplayTimer = setTimeout(() => {
			showSlide(activeIndex + 1, false)
			startAutoplay()
		}, delay)
	}

	const showSlide = (index, restart = true) => {
		if (isAnimating || slides.length < 2) {
			return
		}

		const nextIndex = normalizeIndex(index)

		if (nextIndex === activeIndex) {
			return
		}

		const outgoing = slides[activeIndex]
		const incoming = slides[nextIndex]

		isAnimating = true
		activeIndex = nextIndex

		clearTimeout(transitionTimer)

		outgoing.classList.remove('is-active')
		outgoing.classList.add('is-leaving')

		incoming.classList.remove('is-leaving')
		incoming.classList.add('is-active')

		updateSlide()

		const finish = () => {
			outgoing.classList.remove('is-leaving')
			isAnimating = false
		}

		if (reducedMotion) {
			finish()
		} else {
			transitionTimer = setTimeout(finish, transitionDuration)
		}

		if (restart) {
			startAutoplay()
		}
	}

	const showPrevious = () => {
		showSlide(activeIndex - 1)
	}

	const showNext = () => {
		showSlide(activeIndex + 1)
	}

	previousButton?.addEventListener('click', event => {
		event.preventDefault()
		event.stopPropagation()
		showPrevious()
	})

	nextButton?.addEventListener('click', event => {
		event.preventDefault()
		event.stopPropagation()
		showNext()
	})

	hero.addEventListener('keydown', event => {
		if (event.key === 'ArrowLeft') {
			event.preventDefault()
			showPrevious()
		}

		if (event.key === 'ArrowRight') {
			event.preventDefault()
			showNext()
		}
	})

	if (hasFinePointer && cursor) {
		const moveCursor = (event, mode) => {
			const rect = viewport.getBoundingClientRect()

			cursor.style.left = `${event.clientX - rect.left}px`

			cursor.style.top = `${event.clientY - rect.top}px`

			cursor.classList.toggle('is-previous', mode === 'previous')

			cursor.classList.toggle('is-link', mode === 'link')
		}

		const bindCursorZone = (element, mode) => {
			if (!element) {
				return
			}

			element.addEventListener('mouseenter', event => {
				moveCursor(event, mode)
				cursor.classList.add('is-visible')
			})

			element.addEventListener('mousemove', event => {
				moveCursor(event, mode)
			})

			element.addEventListener('mouseleave', () => {
				cursor.classList.remove('is-visible')
				cursor.classList.remove('is-link')
			})
		}

		bindCursorZone(previousButton, 'previous')

		bindCursorZone(nextButton, 'next')

		viewport.addEventListener('mousemove', event => {
			const rect = viewport.getBoundingClientRect()
			const x = event.clientX - rect.left
			const leftBoundary = rect.width * 0.32
			const rightBoundary = rect.width * 0.68

			if (x > leftBoundary && x < rightBoundary) {
				moveCursor(event, 'link')
				cursor.classList.add('is-visible')
			}
		})

		viewport.addEventListener('mouseleave', () => {
			cursor.classList.remove('is-visible')
			cursor.classList.remove('is-link')
		})
	}

	viewport.addEventListener('pointerdown', event => {
		if (event.pointerType === 'mouse' && event.button !== 0) {
			return
		}

		startX = event.clientX
		startY = event.clientY
	})

	viewport.addEventListener('pointerup', event => {
		const deltaX = event.clientX - startX
		const deltaY = event.clientY - startY

		const isHorizontal = Math.abs(deltaX) > Math.abs(deltaY)

		const isSwipe = isHorizontal && Math.abs(deltaX) >= 50

		if (!isSwipe) {
			return
		}

		suppressClick = true

		if (deltaX < 0) {
			showNext()
		} else {
			showPrevious()
		}

		setTimeout(() => {
			suppressClick = false
		}, 100)
	})

	viewport.addEventListener(
		'click',
		event => {
			if (!suppressClick) {
				return
			}

			event.preventDefault()
			event.stopPropagation()
		},
		true
	)

	document.addEventListener('visibilitychange', () => {
		if (document.hidden) {
			stopAutoplay()
		} else {
			startAutoplay()
		}
	})

	if ('IntersectionObserver' in window) {
		const observer = new IntersectionObserver(
			entries => {
				isVisible =
					entries[0].isIntersecting && entries[0].intersectionRatio >= 0.2

				if (isVisible) {
					startAutoplay()
				} else {
					stopAutoplay()
				}
			},
			{
				threshold: [0, 0.2]
			}
		)

		observer.observe(hero)
	} else {
		startAutoplay()
	}

	hero.classList.add('is-initialized')
	hero.tabIndex = 0

	updateSlide()

	if (slides.length === 1) {
		setProgress(1)
	}
}

document.querySelectorAll('[data-home-hero]').forEach(hero => {
	if (hero.dataset.heroInitialized === 'true') {
		return
	}

	hero.dataset.heroInitialized = 'true'
	initHero(hero)
})
