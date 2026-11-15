/* ==========================================================================
   Home product pages
   Static page switching
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
			nextIndex < 0 ||
			nextIndex >= pages.length ||
			nextIndex === activeIndex
		) {
			return
		}

		pages[activeIndex].classList.remove('is-active')
		pages[nextIndex].classList.add('is-active')
		activeIndex = nextIndex

		updateAccessibility()
		updateControls()
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
		section.__vnProductsSliderRefresh = updateMobileControls

		return
	}

	updateAccessibility()
	updateControls()
	section.__vnProductsSliderRefresh = updateControls
}

/* ==========================================================================
   Initial reveal
   ========================================================================== */

const revealSection = section => {
	section.classList.add('is-visible')
}
window.VNProductsSlider = {
	init: initProductsSlider,
	reveal: revealSection,
	refresh: section => section?.__vnProductsSliderRefresh?.()
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
