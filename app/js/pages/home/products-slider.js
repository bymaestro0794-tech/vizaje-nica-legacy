/* ==========================================================================
   Home product pages
   Vertical reveal transition
   ========================================================================== */

const initProductsSlider = section => {
	const pages = [...section.querySelectorAll('[data-products-page]')]

	const previousButton = section.querySelector('[data-products-previous]')

	const nextButton = section.querySelector('[data-products-next]')

	const track = section.querySelector('[data-products-track]')

	const mobilePreviousButton = section.querySelector(
		'[data-products-mobile-previous]'
	)

	const mobileNextButton = section.querySelector('[data-products-mobile-next]')

	if (!pages.length) {
		return
	}

	const isMobile = window.matchMedia('(max-width: 767px)').matches

	const reduceMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches

	let activeIndex = 0
	let isAnimating = false

	const updateControls = () => {
		if (previousButton) {
			previousButton.disabled = activeIndex === 0
		}

		if (nextButton) {
			nextButton.disabled = activeIndex === pages.length - 1
		}
	}

	const updateAccessibility = () => {
		pages.forEach((page, index) => {
			const active = index === activeIndex

			page.setAttribute('aria-hidden', String(!active))

			page
				.querySelectorAll('a, button, input, select, textarea')
				.forEach(element => {
					element.tabIndex = active ? 0 : -1
				})
		})
	}

	const showPage = nextIndex => {
		if (
			isAnimating ||
			nextIndex < 0 ||
			nextIndex >= pages.length ||
			nextIndex === activeIndex
		) {
			return
		}

		const direction = nextIndex > activeIndex ? 1 : -1

		const currentPage = pages[activeIndex]
		const nextPage = pages[nextIndex]

		const currentCards = [...currentPage.children]

		const nextCards = [...nextPage.children]

		isAnimating = true

		gsap.killTweensOf([currentPage, nextPage, currentCards, nextCards])

		/*
	|--------------------------------------------------------------------------
	| Обе сетки занимают одинаковые позиции
	|--------------------------------------------------------------------------
	*/

		currentPage.classList.add('is-transitioning')

		nextPage.classList.add('is-transitioning')

		gsap.set(currentPage, {
			position: 'relative',
			zIndex: 1,
			visibility: 'visible'
		})

		gsap.set(nextPage, {
			position: 'absolute',
			inset: 0,
			zIndex: 2,
			visibility: 'visible',
			pointerEvents: 'none'
		})

		/*
	|--------------------------------------------------------------------------
	| Каждая новая карточка раскрывается отдельно
	|--------------------------------------------------------------------------
	*/

		nextCards.forEach(card => {
			gsap.set(card, {
				clipPath: reduceMotion
					? 'none'
					: direction > 0
						? 'inset(0 100% 0 0)'
						: 'inset(0 0 0 100%)',

				opacity: reduceMotion ? 0 : 1
			})

			const cardContent = card.firstElementChild

			if (cardContent) {
				gsap.set(cardContent, {
					x: reduceMotion ? 0 : 32 * direction
				})
			}
		})

		const timeline = gsap.timeline({
			defaults: {
				overwrite: 'auto'
			},

			onComplete: () => {
				currentPage.classList.remove('is-active', 'is-transitioning')

				nextPage.classList.remove('is-transitioning')

				nextPage.classList.add('is-active')

				activeIndex = nextIndex

				gsap.set([currentPage, nextPage, ...currentCards, ...nextCards], {
					clearProps: 'all'
				})

				currentCards.forEach(card => {
					if (card.firstElementChild) {
						gsap.set(card.firstElementChild, {
							clearProps: 'all'
						})
					}
				})

				nextCards.forEach(card => {
					if (card.firstElementChild) {
						gsap.set(card.firstElementChild, {
							clearProps: 'all'
						})
					}
				})

				updateAccessibility()
				updateControls()

				isAnimating = false
			}
		})

		if (reduceMotion) {
			timeline.to(nextCards, {
				opacity: 1,
				duration: 0.14,
				stagger: 0,
				ease: 'none'
			})

			return
		}

		/*
	|--------------------------------------------------------------------------
	| Старые карточки мягко уходят
	|--------------------------------------------------------------------------
	*/

		timeline.to(
			currentCards,
			{
				x: -22 * direction,
				opacity: 0,
				duration: 0.3,

				stagger: {
					each: 0.035,
					from: direction > 0 ? 'start' : 'end'
				},

				ease: 'power2.in'
			},
			0
		)

		/*
	|--------------------------------------------------------------------------
	| Новые карточки раскрываются по отдельности
	|--------------------------------------------------------------------------
	*/

		timeline.to(
			nextCards,
			{
				clipPath: 'inset(0 0% 0 0%)',
				duration: 0.58,

				stagger: {
					each: 0.055,
					from: direction > 0 ? 'start' : 'end'
				},

				ease: 'power3.inOut'
			},
			0.08
		)

		/*
	|--------------------------------------------------------------------------
	| Контент внутри карточек слегка движется
	|--------------------------------------------------------------------------
	*/

		timeline.to(
			nextCards.map(card => card.firstElementChild).filter(Boolean),
			{
				x: 0,
				duration: 0.62,

				stagger: {
					each: 0.055,
					from: direction > 0 ? 'start' : 'end'
				},

				ease: 'power3.out'
			},
			0.08
		)
	}

	previousButton?.addEventListener('click', () => {
		showPage(activeIndex - 1)
	})

	nextButton?.addEventListener('click', () => {
		showPage(activeIndex + 1)
	})

	/*
	|--------------------------------------------------------------------------
	| Mobile
	|--------------------------------------------------------------------------
	*/

	if (isMobile) {
		pages.forEach(page => {
			page.classList.add('is-active')
			page.setAttribute('aria-hidden', 'false')

			page
				.querySelectorAll('a, button, input, select, textarea')
				.forEach(element => {
					element.tabIndex = 0
				})
		})

		const getFirstItem = () => {
			return track?.querySelector(
				'.products-slider__products, ' + '[data-products-catalog-link]'
			)
		}

		const getScrollStep = () => {
			const item = getFirstItem()

			if (!item || !track) {
				return 220
			}

			const trackStyles = window.getComputedStyle(track)

			const gap =
				parseFloat(trackStyles.columnGap) || parseFloat(trackStyles.gap) || 12

			return item.getBoundingClientRect().width + gap
		}

		const updateMobileControls = () => {
			if (!track) {
				return
			}

			const maxScroll = track.scrollWidth - track.clientWidth

			if (mobilePreviousButton) {
				mobilePreviousButton.disabled = track.scrollLeft <= 4
			}

			if (mobileNextButton) {
				mobileNextButton.disabled = track.scrollLeft >= maxScroll - 4
			}
		}

		const scrollProducts = direction => {
			if (!track) {
				return
			}

			track.scrollBy({
				left: getScrollStep() * direction,

				behavior: reduceMotion ? 'auto' : 'smooth'
			})
		}

		mobilePreviousButton?.addEventListener('click', () => {
			scrollProducts(-1)
		})

		mobileNextButton?.addEventListener('click', () => {
			scrollProducts(1)
		})

		track?.addEventListener('scroll', updateMobileControls, {
			passive: true
		})

		window.addEventListener('resize', updateMobileControls)

		updateMobileControls()

		return
	}

	updateAccessibility()
	updateControls()
}

/* ==========================================================================
   Initial reveal
   ========================================================================== */

const revealSection = section => {
	section.classList.add('is-visible')
}
window.VNProductsSlider = {
	init: initProductsSlider,
	reveal: revealSection
}
document.querySelectorAll('[data-products-slider]').forEach(section => {
	initProductsSlider(section)

	if (!('IntersectionObserver' in window)) {
		revealSection(section)
		return
	}

	const observer = new IntersectionObserver(
		entries => {
			if (!entries[0].isIntersecting) {
				return
			}

			revealSection(section)
			observer.disconnect()
		},
		{
			threshold: 0.12
		}
	)

	observer.observe(section)
})
