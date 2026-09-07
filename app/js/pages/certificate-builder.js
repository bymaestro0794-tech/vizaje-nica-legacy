;(function () {
	'use strict'

	function initCertificateDesignSlider() {
		const builder = document.querySelector('[data-certificate-builder]')
		const slider = document.querySelector('[data-certificate-slider]')

		if (!builder || !slider) {
			return
		}

		const slides = Array.from(
			slider.querySelectorAll('[data-certificate-slide]')
		)

		const prevButton = slider.querySelector('[data-certificate-slider-prev]')

		const nextButton = slider.querySelector('[data-certificate-slider-next]')

		const selector = slider.querySelector('[data-certificate-selector]')

		const selectorItems = Array.from(
			slider.querySelectorAll('[data-certificate-selector-item]')
		)

		if (!slides.length) {
			return
		}

		const reducedMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches

		const finePointer = window.matchMedia(
			'(hover: hover) and (pointer: fine)'
		).matches

		const state = {
			activeIndex: 0,
			isAnimating: false,
			wheelLocked: false
		}

		function normalizeIndex(index) {
			const length = slides.length

			return ((index % length) + length) % length
		}

		function getPrevIndex() {
			return normalizeIndex(state.activeIndex - 1)
		}

		function getNextIndex() {
			return normalizeIndex(state.activeIndex + 1)
		}

		function getPosition(index) {
			if (index === state.activeIndex) {
				return 'active'
			}

			if (index === getPrevIndex()) {
				return 'prev'
			}

			if (index === getNextIndex()) {
				return 'next'
			}

			return 'hidden'
		}

		function syncRadio() {
			const currentSlide = slides[state.activeIndex]

			if (!currentSlide) {
				return
			}

			const input = currentSlide.querySelector(
				'input[name="design-certificate"]'
			)

			if (!input) {
				return
			}

			input.checked = true

			input.dispatchEvent(
				new Event('change', {
					bubbles: true
				})
			)
		}

		function syncTheme() {
			const currentSlide = slides[state.activeIndex]

			if (!currentSlide) {
				return
			}

			const theme = currentSlide.dataset.certificateTheme || 'design-1'

			builder.dataset.theme = theme
		}

		function syncSelector() {
			selectorItems.forEach(function (item, index) {
				let position = 'hidden'

				if (index === state.activeIndex) {
					position = 'active'
				} else if (index === getPrevIndex()) {
					position = 'left'
				} else if (index === getNextIndex()) {
					position = 'right'
				}

				item.dataset.selectorPosition = position

				const isActive = position === 'active'

				item.classList.toggle('_active', isActive)

				item.setAttribute('aria-current', isActive ? 'true' : 'false')
			})
		}

		function setSlideState(slide, index, animate) {
			const position = getPosition(index)

			slide.dataset.position = position

			slide.setAttribute(
				'aria-hidden',
				position === 'hidden' ? 'true' : 'false'
			)

			let vars

			if (position === 'active') {
				vars = {
					xPercent: -50,
					yPercent: -50,
					x: 0,
					scale: 1,
					opacity: 1,
					zIndex: 3
				}
			} else if (position === 'prev') {
				vars = {
					xPercent: -50,
					yPercent: -50,
					x: '-88%',
					scale: 0.64,
					opacity: 0.13,
					zIndex: 1
				}
			} else if (position === 'next') {
				vars = {
					xPercent: -50,
					yPercent: -50,
					x: '88%',
					scale: 0.64,
					opacity: 0.13,
					zIndex: 1
				}
			} else {
				vars = {
					xPercent: -50,
					yPercent: -50,
					x: 0,
					scale: 0.6,
					opacity: 0,
					zIndex: 0
				}
			}

			if (reducedMotion || typeof window.gsap === 'undefined') {
				slide.style.opacity = vars.opacity.toString()

				slide.style.zIndex = vars.zIndex.toString()

				return
			}

			if (!animate) {
				gsap.set(slide, vars)

				return
			}

			gsap.to(slide, {
				...vars,
				duration: 0.62,
				ease: 'power3.inOut',
				overwrite: true
			})
		}

		function render(animate) {
			slides.forEach(function (slide, index) {
				setSlideState(slide, index, animate)
			})

			syncRadio()
			syncTheme()
			syncSelector()
		}

		function goTo(index) {
			const nextIndex = normalizeIndex(index)

			if (nextIndex === state.activeIndex || state.isAnimating) {
				return
			}

			state.isAnimating = true
			state.activeIndex = nextIndex

			resetTilts()
			render(true)

			window.setTimeout(
				function () {
					state.isAnimating = false
				},
				reducedMotion ? 0 : 640
			)
		}

		function next() {
			goTo(state.activeIndex + 1)
		}

		function prev() {
			goTo(state.activeIndex - 1)
		}

		/* ---------------------------------------------
		   Slide click
		   --------------------------------------------- */

		slides.forEach(function (slide, index) {
			slide.addEventListener('click', function () {
				if (index === state.activeIndex) {
					return
				}

				goTo(index)
			})
		})

		/* ---------------------------------------------
		   Arrow navigation
		   --------------------------------------------- */

		if (prevButton) {
			prevButton.addEventListener('click', prev)
		}

		if (nextButton) {
			nextButton.addEventListener('click', next)
		}

		/* ---------------------------------------------
		   Arc navigation
		   --------------------------------------------- */

		selectorItems.forEach(function (item, index) {
			item.addEventListener('click', function () {
				goTo(index)
			})
		})

		/* ---------------------------------------------
		   Swipe
		   --------------------------------------------- */

		let pointerStartX = null
		let pointerStartY = null

		slider.addEventListener('pointerdown', function (event) {
			if (event.pointerType === 'mouse' && event.button !== 0) {
				return
			}

			pointerStartX = event.clientX
			pointerStartY = event.clientY
		})

		slider.addEventListener('pointerup', function (event) {
			if (pointerStartX === null || pointerStartY === null) {
				return
			}

			const diffX = event.clientX - pointerStartX

			const diffY = event.clientY - pointerStartY

			pointerStartX = null
			pointerStartY = null

			if (Math.abs(diffX) < 50 || Math.abs(diffX) < Math.abs(diffY)) {
				return
			}

			if (diffX < 0) {
				next()
			} else {
				prev()
			}
		})

		/* ---------------------------------------------
		   Desktop wheel on selector
		   --------------------------------------------- */

		if (selector && finePointer) {
			selector.addEventListener(
				'wheel',
				function (event) {
					if (Math.abs(event.deltaY) < 18 || state.wheelLocked) {
						return
					}

					event.preventDefault()

					state.wheelLocked = true

					if (event.deltaY > 0) {
						next()
					} else {
						prev()
					}

					window.setTimeout(function () {
						state.wheelLocked = false
					}, 720)
				},
				{
					passive: false
				}
			)
		}

		/* ---------------------------------------------
		   Gorilla glass / pointer tilt
		   --------------------------------------------- */

		function resetTilt(slide) {
			const tilt = slide.querySelector('[data-certificate-tilt]')

			const glass = slide.querySelector('[data-certificate-glass]')

			if (!tilt) {
				return
			}

			if (typeof window.gsap !== 'undefined') {
				gsap.to(tilt, {
					rotateX: 0,
					rotateY: 0,
					x: 0,
					y: 0,
					duration: 0.65,
					ease: 'power3.out',
					overwrite: true
				})
			}

			if (glass) {
				glass.style.setProperty('--glass-x', '50%')

				glass.style.setProperty('--glass-y', '50%')
			}
		}

		function resetTilts() {
			slides.forEach(resetTilt)
		}

		if (finePointer && !reducedMotion && typeof window.gsap !== 'undefined') {
			slides.forEach(function (slide, index) {
				const tilt = slide.querySelector('[data-certificate-tilt]')

				const glass = slide.querySelector('[data-certificate-glass]')

				if (!tilt) {
					return
				}

				slide.addEventListener('pointermove', function (event) {
					if (index !== state.activeIndex) {
						return
					}

					const rect = slide.getBoundingClientRect()

					const x = (event.clientX - rect.left) / rect.width

					const y = (event.clientY - rect.top) / rect.height

					const rotateY = (x - 0.5) * 7

					const rotateX = (0.5 - y) * 5

					const translateX = (x - 0.5) * 5

					const translateY = (y - 0.5) * 4

					gsap.to(tilt, {
						rotateX: rotateX,
						rotateY: rotateY,
						x: translateX,
						y: translateY,
						duration: 0.35,
						ease: 'power2.out',
						transformPerspective: 1000,
						transformOrigin: 'center center',
						overwrite: true
					})

					if (glass) {
						glass.style.setProperty('--glass-x', x * 100 + '%')

						glass.style.setProperty('--glass-y', y * 100 + '%')
					}
				})

				slide.addEventListener('pointerleave', function () {
					resetTilt(slide)
				})
			})
		}

		/* ---------------------------------------------
		   Initial state
		   --------------------------------------------- */

		render(false)
	}

	function initCertificateEntrance() {
		const builder = document.querySelector('[data-certificate-builder]')

		if (!builder || typeof window.gsap === 'undefined') {
			return
		}

		const reducedMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches

		if (reducedMotion) {
			return
		}

		const timeline = gsap.timeline({
			defaults: {
				ease: 'power3.out'
			}
		})

		timeline
			.from('.certificate-builder__step-number', {
				opacity: 0,
				y: 18,
				duration: 0.55
			})
			.from(
				'.certificate-builder__step-item',
				{
					opacity: 0,
					x: -12,
					stagger: 0.06,
					duration: 0.42
				},
				'-=0.28'
			)
			.from(
				'.certificate-builder__heading',
				{
					opacity: 0,
					y: 22,
					duration: 0.58
				},
				'-=0.35'
			)
			.from(
				'.certificate-design',
				{
					opacity: 0,
					y: 18,
					scale: 0.985,
					duration: 0.72
				},
				'-=0.28'
			)
			.from(
				'.certificate-builder__next',
				{
					opacity: 0,
					scale: 0.9,
					duration: 0.5
				},
				'-=0.32'
			)
	}

	function initDenominationCarousel(step, onSelect) {
		if (!step) {
			return null
		}

		const selector = step.querySelector('[data-denomination-selector]')

		if (!selector) {
			return null
		}

		const options = Array.from(
			selector.querySelectorAll('[data-denomination-option]')
		)

		if (!options.length) {
			return null
		}

		const reducedMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches
		const isMobile = window.matchMedia('(max-width: 767.98px)')

		const state = {
			activeIndex: 0,

			isAnimating: false,

			wheelLocked: false,

			pointerId: null,

			pointerStartX: 0,
			pointerLastX: 0,

			pointerStartY: 0,
			pointerLastY: 0,

			dragDistance: 0
		}

		/* =====================================================
	   Index helpers
	   ===================================================== */

		function clampIndex(index) {
			return Math.max(0, Math.min(index, options.length - 1))
		}

		function findDefaultIndex() {
			const customIndex = options.findIndex(function (option) {
				return option.hasAttribute('data-denomination-custom-option')
			})

			return customIndex >= 0 ? customIndex : 0
		}

		/* =====================================================
	   Geometry
	   ===================================================== */
		function getMobileGeometry(index, offset, distance) {
			const activeOption = options[state.activeIndex]

			const customIsActive =
				activeOption &&
				activeOption.hasAttribute('data-denomination-custom-option')

			const spacing = 112

			let x = offset * spacing
			let y = 0
			if (customIsActive && offset === 0) {
				x = -32
				y = -26
			}
			/*
			 * Custom input открывается под активным item,
			 * но не влияет на горизонтальное положение carousel.
			 */
			if (customIsActive && offset === 0) {
				y = -26
			}

			const scale = Math.max(0.88, 1 - distance * 0.045)

			const opacity = Math.max(0.22, 1 - distance * 0.22)

			return {
				x: x,
				y: y,
				scale: scale,
				opacity: opacity,
				rotateZ: 0,
				rotateY: 0,
				zIndex: 100 - distance
			}
		}
		function getGeometry(index) {
			const offset = index - state.activeIndex
			const distance = Math.abs(offset)

			if (isMobile.matches) {
				return getMobileGeometry(index, offset, distance)
			}

			let y = 0

			for (let i = 0; i < distance; i++) {
				y += Math.max(33, 42 - i * 2.3)
			}

			if (offset < 0) {
				y *= -1
			}

			const x = Math.pow(distance, 1.42) * 8.5

			const scale = Math.max(0.76, 1 - distance * 0.045)

			const opacity = Math.max(0, 1 - distance * 0.13)

			const rotateZ = offset * 1.65

			const rotateY = Math.min(10, distance * 1.8)

			const option = options[index]

			const activeOption = options[state.activeIndex]

			const customIsActive =
				activeOption &&
				activeOption.hasAttribute('data-denomination-custom-option')

			/*
			 * Custom sits slightly above
			 * the normal carousel focal point.
			 */
			const customOffsetY =
				option &&
				option.hasAttribute('data-denomination-custom-option') &&
				offset === 0
					? -32
					: 0

			/*
			 * When Custom is active its input expands
			 * below the label. Push all following
			 * denominations down so they don't overlap.
			 */
			const customExpandedGap = customIsActive && offset > 0 ? 54 : 0

			return {
				x: x,

				y: y + customOffsetY + customExpandedGap,

				scale: scale,

				opacity: opacity,

				rotateZ: rotateZ,

				rotateY: rotateY,

				zIndex: 100 - distance
			}
		}

		/* =====================================================
	   Render
	   ===================================================== */

		function render(animate) {
			options.forEach(function (option, index) {
				const geometry = getGeometry(index)

				const isActive = index === state.activeIndex

				option.classList.toggle('_active', isActive)

				option.setAttribute('aria-current', isActive ? 'true' : 'false')

				/*
				 * We keep a few neighbours visible.
				 * Far-away items disappear softly.
				 */
				const distance = Math.abs(index - state.activeIndex)

				const visible = isMobile.matches ? distance <= 2 : distance <= 5

				option.style.pointerEvents = visible ? 'auto' : 'none'

				const vars = {
					xPercent: isMobile.matches ? -50 : 0,

					x: geometry.x,
					y: geometry.y,

					yPercent: -50,

					scale: geometry.scale,

					opacity: visible ? geometry.opacity : 0,

					rotateZ: geometry.rotateZ,

					rotateY: geometry.rotateY,

					transformPerspective: 900,

					transformOrigin: isMobile.matches ? 'center center' : 'left center',

					zIndex: geometry.zIndex
				}

				if (reducedMotion || typeof window.gsap === 'undefined') {
					option.style.opacity = String(vars.opacity)

					if (isMobile.matches) {
						option.style.transform =
							'translate3d(calc(-50% + ' +
							vars.x +
							'px), calc(-50% + ' +
							vars.y +
							'px), 0) scale(' +
							vars.scale +
							')'
					} else {
						option.style.transform =
							'translate3d(' +
							vars.x +
							'px, calc(-50% + ' +
							vars.y +
							'px), 0) scale(' +
							vars.scale +
							')'
					}

					return
				}

				if (!animate) {
					gsap.set(option, vars)

					return
				}

				gsap.to(option, {
					...vars,

					duration: 0.55,

					ease: 'power3.out',

					overwrite: true
				})
			})
		}

		/* =====================================================
	   Selection
	   ===================================================== */

		function select(index, optionsConfig) {
			const config = optionsConfig || {}

			const nextIndex = clampIndex(index)

			if (nextIndex === state.activeIndex && !config.force) {
				return
			}

			state.activeIndex = nextIndex

			render(config.animate !== false)

			if (config.notify !== false && typeof onSelect === 'function') {
				onSelect(options[nextIndex], nextIndex)
			}
		}

		function next() {
			select(state.activeIndex + 1)
		}

		function prev() {
			select(state.activeIndex - 1)
		}

		/* =====================================================
	   Click
	   ===================================================== */

		options.forEach(function (option, index) {
			option.addEventListener('click', function (event) {
				/*
				 * Do not hijack the custom input.
				 */
				if (event.target.closest('[data-denomination-custom-input]')) {
					return
				}

				select(index)
			})
		})

		/* =====================================================
	   Wheel
	   ===================================================== */

		selector.addEventListener(
			'wheel',
			function (event) {
				const delta =
					Math.abs(event.deltaY) > Math.abs(event.deltaX)
						? event.deltaY
						: event.deltaX

				if (Math.abs(delta) < 12) {
					return
				}

				event.preventDefault()

				if (state.wheelLocked) {
					return
				}

				state.wheelLocked = true

				if (delta > 0) {
					next()
				} else {
					prev()
				}

				window.setTimeout(function () {
					state.wheelLocked = false
				}, 420)
			},
			{
				passive: false
			}
		)

		/* =====================================================
	   Pointer drag / swipe
	   ===================================================== */

		selector.addEventListener('pointerdown', function (event) {
			if (event.pointerType === 'mouse' && event.button !== 0) {
				return
			}

			if (event.target.closest('[data-denomination-custom-input]')) {
				return
			}

			state.pointerId = event.pointerId

			state.pointerStartX = event.clientX
			state.pointerLastX = event.clientX

			state.pointerStartY = event.clientY
			state.pointerLastY = event.clientY

			state.dragDistance = 0

			selector.classList.add('_dragging')

			if (selector.setPointerCapture) {
				selector.setPointerCapture(event.pointerId)
			}
		})

		selector.addEventListener('pointermove', function (event) {
			if (state.pointerId !== event.pointerId) {
				return
			}

			let delta

			if (isMobile.matches) {
				delta = event.clientX - state.pointerLastX

				state.pointerLastX = event.clientX
			} else {
				delta = event.clientY - state.pointerLastY

				state.pointerLastY = event.clientY
			}

			state.dragDistance += delta

			/*
			 * Threshold navigation while dragging.
			 *
			 * Pull upward -> move forward.
			 * Pull downward -> move backward.
			 */
			const threshold = isMobile.matches ? 54 : 42

			if (state.dragDistance <= -threshold) {
				next()

				state.dragDistance += threshold
			} else if (state.dragDistance >= threshold) {
				prev()

				state.dragDistance -= threshold
			}
		})

		function finishPointer(event) {
			if (state.pointerId === null) {
				return
			}

			if (event && event.pointerId !== state.pointerId) {
				return
			}

			selector.classList.remove('_dragging')

			if (selector.releasePointerCapture && event) {
				try {
					selector.releasePointerCapture(event.pointerId)
				} catch (error) {
					// Pointer may already be released.
				}
			}

			state.pointerId = null

			state.dragDistance = 0
		}

		selector.addEventListener('pointerup', finishPointer)

		selector.addEventListener('pointercancel', finishPointer)

		/* =====================================================
	   Keyboard
	   ===================================================== */

		selector.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
				event.preventDefault()

				next()
			}

			if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
				event.preventDefault()

				prev()
			}
		})

		/* =====================================================
	   Initial
	   ===================================================== */

		state.activeIndex = findDefaultIndex()

		render(false)

		return {
			select: select,

			next: next,

			prev: prev,

			refresh: function (animate) {
				render(animate !== false)
			},

			getActiveIndex: function () {
				return state.activeIndex
			}
		}
	}

	function initCertificateDenomination() {
		const builder = document.querySelector('[data-certificate-builder]')

		if (!builder) {
			return
		}

		const step = builder.querySelector(
			'.certificate-builder__step--denomination'
		)

		if (!step) {
			return
		}

		const backendInput = step.querySelector('[data-denomination-input]')

		const display = step.querySelector('[data-denomination-display]')

		const previewImage = step.querySelector('[data-denomination-card-image]')

		const cardTilt = step.querySelector('[data-denomination-card-tilt]')

		const cardGlass = step.querySelector('[data-denomination-card-glass]')

		const options = Array.from(
			step.querySelectorAll('[data-denomination-option]')
		)

		const presetOptions = Array.from(
			step.querySelectorAll('[data-denomination-value]')
		)

		const customOption = step.querySelector('[data-denomination-custom-option]')

		const customTrigger = step.querySelector(
			'[data-denomination-custom-trigger]'
		)

		const customBlock = step.querySelector('[data-denomination-custom]')

		const customInput = step.querySelector('[data-denomination-custom-input]')

		if (!backendInput || !display) {
			return
		}

		const reducedMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches

		const finePointer = window.matchMedia(
			'(hover: hover) and (pointer: fine)'
		).matches

		const state = {
			value: 2000,
			mode: 'preset'
		}

		/* =====================================================
	   Helpers
	   ===================================================== */

		function formatAmount(value) {
			const number = Number(value)

			if (!number || Number.isNaN(number)) {
				return '0 MDL'
			}

			return number.toLocaleString('ru-RU').replace(/\u00A0/g, ' ') + ' MDL'
		}

		function getSelectedDesignImage() {
			const selectedDesign = builder.querySelector(
				'input[name="design-certificate"]:checked'
			)

			if (!selectedDesign) {
				return ''
			}

			return selectedDesign.getAttribute('data-value') || ''
		}

		function syncPreview() {
			if (!previewImage) {
				return
			}

			const image = getSelectedDesignImage()

			if (!image) {
				return
			}

			if (previewImage.src === image) {
				return
			}

			previewImage.src = image
		}

		function clearValidationError() {
			backendInput.classList.remove('_error')

			const parent = backendInput.closest('.denomination-certificate__item')

			if (!parent) {
				return
			}

			const error = parent.querySelector('.certificate__error-message')

			if (error) {
				error.remove()
			}
		}

		function animateDisplay(value) {
			const nextText = formatAmount(value)

			if (reducedMotion || typeof window.gsap === 'undefined') {
				display.textContent = nextText
				return
			}

			gsap.to(display, {
				opacity: 0,
				y: -8,
				duration: 0.16,
				ease: 'power2.in',
				overwrite: true,

				onComplete: function () {
					display.textContent = nextText

					gsap.fromTo(
						display,
						{
							opacity: 0,
							y: 8
						},
						{
							opacity: 1,
							y: 0,
							duration: 0.28,
							ease: 'power3.out',
							overwrite: true
						}
					)
				}
			})
		}

		function setValue(value, activeOption) {
			const parsed = parseInt(value, 10)

			if (Number.isNaN(parsed)) {
				return
			}

			state.value = parsed

			backendInput.value = String(parsed)

			animateDisplay(parsed)

			clearValidationError()

			backendInput.dispatchEvent(
				new Event('input', {
					bubbles: true
				})
			)

			backendInput.dispatchEvent(
				new Event('change', {
					bubbles: true
				})
			)
		}

		/* =====================================================
	   Custom
	   ===================================================== */
		let denominationCarousel = null
		function openCustom(shouldFocus) {
			if (!customOption || !customBlock) {
				return
			}

			state.mode = 'custom'

			customOption.classList.add('_custom-active')

			if (reducedMotion || typeof window.gsap === 'undefined') {
				customBlock.style.height = 'auto'
				customBlock.style.opacity = '1'
			} else {
				gsap.to(customBlock, {
					height: 'auto',
					opacity: 1,
					duration: 0.36,
					ease: 'power3.out',
					overwrite: true
				})
			}

			if (denominationCarousel) {
				window.setTimeout(
					function () {
						denominationCarousel.refresh(true)
					},
					reducedMotion ? 0 : 40
				)
			}

			if (shouldFocus && customInput) {
				window.setTimeout(
					function () {
						customInput.focus()
					},
					reducedMotion ? 0 : 180
				)
			}
		}

		function closeCustom() {
			if (!customOption || !customBlock) {
				return
			}

			customOption.classList.remove('_custom-active')

			if (reducedMotion || typeof window.gsap === 'undefined') {
				customBlock.style.height = '0'
				customBlock.style.opacity = '0'
			} else {
				gsap.to(customBlock, {
					height: 0,
					opacity: 0,
					duration: 0.28,
					ease: 'power2.inOut',
					overwrite: true
				})
			}

			if (denominationCarousel) {
				window.setTimeout(function () {
					denominationCarousel.refresh(true)
				}, 40)
			}
		}

		denominationCarousel = initDenominationCarousel(
			step,

			function (option) {
				if (!option) {
					return
				}

				/* ---------------------------------
			   Custom
			   --------------------------------- */

				if (option.hasAttribute('data-denomination-custom-option')) {
					state.mode = 'custom'

					openCustom()

					if (customInput && customInput.value) {
						const customValue = customInput.value.replace(/\D/g, '')

						if (customValue) {
							setValue(parseInt(customValue, 10))
						}
					}

					return
				}

				/* ---------------------------------
			   Preset
			   --------------------------------- */

				const value = option.dataset.denominationValue

				if (!value) {
					return
				}

				state.mode = 'preset'

				closeCustom()

				setValue(value)
			}
		)

		if (customInput) {
			customInput.addEventListener('input', function () {
				const raw = customInput.value.replace(/\D/g, '')

				customInput.value = raw

				if (!raw) {
					backendInput.value = ''

					display.textContent = '— MDL'

					return
				}

				setValue(parseInt(raw, 10), customOption)
			})
		}

		/* =====================================================
	   Preview tilt
	   ===================================================== */

		function resetCardTilt() {
			if (!cardTilt || typeof window.gsap === 'undefined') {
				return
			}

			gsap.to(cardTilt, {
				rotateX: 0,
				rotateY: 0,
				x: 0,
				y: 0,

				duration: 0.65,
				ease: 'power3.out',
				overwrite: true
			})

			if (cardGlass) {
				cardGlass.style.setProperty('--denomination-glass-x', '50%')

				cardGlass.style.setProperty('--denomination-glass-y', '50%')
			}
		}

		if (
			cardTilt &&
			finePointer &&
			!reducedMotion &&
			typeof window.gsap !== 'undefined'
		) {
			const card = cardTilt.parentElement

			card.addEventListener('pointermove', function (event) {
				const rect = card.getBoundingClientRect()

				const x = (event.clientX - rect.left) / rect.width

				const y = (event.clientY - rect.top) / rect.height

				gsap.to(cardTilt, {
					rotateY: (x - 0.5) * 5,

					rotateX: (0.5 - y) * 3.5,

					x: (x - 0.5) * 3,

					y: (y - 0.5) * 3,

					duration: 0.35,

					ease: 'power2.out',

					transformPerspective: 1000,

					transformOrigin: 'center center',

					overwrite: true
				})

				if (cardGlass) {
					cardGlass.style.setProperty('--denomination-glass-x', x * 100 + '%')

					cardGlass.style.setProperty('--denomination-glass-y', y * 100 + '%')
				}
			})

			card.addEventListener('pointerleave', resetCardTilt)
		}

		/* =====================================================
	   Design changes
	   ===================================================== */

		builder
			.querySelectorAll('input[name="design-certificate"]')
			.forEach(function (input) {
				input.addEventListener('change', syncPreview)
			})

		/* =====================================================
	   Initial
	   ===================================================== */

		backendInput.value = ''

		display.textContent = '— MDL'

		state.value = null
		state.mode = 'custom'

		syncPreview()
		openCustom(false)
	}
	function initCertificateWizard() {
		const builder = document.querySelector('[data-certificate-builder]')

		if (!builder) {
			return
		}

		const form = builder.querySelector('#certificate-form')

		if (!form) {
			return
		}

		const blocks = Array.from(form.querySelectorAll('.certificate__block'))

		const stepItems = Array.from(
			builder.querySelectorAll('[data-builder-step-item]')
		)

		const stepNumber = builder.querySelector('[data-builder-step-number]')

		const backButtons = Array.from(
			builder.querySelectorAll('.certificate__back')
		)

		const mobileTitle = builder.querySelector('[data-certificate-mobile-title]')

		const mobileBackButton = builder.querySelector(
			'[data-certificate-mobile-back]'
		)

		const cerCard = document.getElementById('cer-card')
		const cerValue = document.getElementById('cer-value')
		const cerFrom = document.getElementById('cer-from')
		const cerTo = document.getElementById('cer-to')
		const cerText = document.getElementById('cer-text')
		const cerPhone = document.getElementById('cer-phone')
		const cerEmail = document.getElementById('cer-email')

		if (!blocks.length) {
			return
		}

		const reducedMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches

		const state = {
			currentStep: 0,
			isTransitioning: false
		}

		/* =====================================================
	   Validation helpers
	   ===================================================== */

		function removeErrors(block) {
			if (!block) {
				return
			}

			block.querySelectorAll('._error').forEach(function (element) {
				element.classList.remove('_error')
			})

			block
				.querySelectorAll('.certificate__error-message')
				.forEach(function (element) {
					element.remove()
				})
		}

		function showError(element, message) {
			if (!element) {
				return
			}

			element.classList.add('_error')

			const error = document.createElement('div')

			error.className = 'certificate__error-message'
			error.textContent = message

			const parent = element.closest(
				[
					'.data-certificate__item',
					'.denomination-certificate__item',
					'.time-send-certificate__item',
					'.recipe-certificate__row'
				].join(', ')
			)

			if (parent) {
				parent.appendChild(error)
			} else if (element.parentElement) {
				element.parentElement.appendChild(error)
			}
		}

		/* =====================================================
	   Step 1 — design
	   ===================================================== */

		function validateStep1(block) {
			removeErrors(block)

			const selected = block.querySelector(
				'input[name="design-certificate"]:checked'
			)

			if (!selected) {
				const designArea = block.querySelector('[data-certificate-slider]')

				if (designArea) {
					const error = document.createElement('div')

					error.className = 'certificate__error-message'

					error.textContent = window.CERTIFICATE_MESSAGES
						? window.CERTIFICATE_MESSAGES.design
						: ''

					designArea.appendChild(error)
				}

				return false
			}

			if (cerCard) {
				cerCard.src = selected.getAttribute('data-value') || ''
			}

			return true
		}

		/* =====================================================
	   Step 2 — denomination
	   ===================================================== */

		function validateStep2(block) {
			removeErrors(block)

			const input = block.querySelector('input[name="denomination"]')

			if (!input) {
				return false
			}

			const value = parseFloat(
				input.value.replace(/\s/g, '').replace(/[^\d.]/g, '')
			)

			if (!value || Number.isNaN(value)) {
				showError(input, window.CERTIFICATE_MESSAGES.amountEmpty)

				return false
			}

			if (value < 200) {
				showError(input, window.CERTIFICATE_MESSAGES.amountMin)

				return false
			}

			if (value > 20000) {
				showError(input, window.CERTIFICATE_MESSAGES.amountMax)

				return false
			}

			if (cerValue) {
				cerValue.textContent = value.toLocaleString('ru-RU') + ' MDL'
			}

			return true
		}

		/* =====================================================
	   Step 3 — recipient / greeting
	   ===================================================== */

		function validateStep3(block) {
			removeErrors(block)

			let isValid = true

			/*
			 * Important:
			 *
			 * Legacy backend contract is preserved:
			 *
			 * from = recipient field in current form
			 * to   = sender field in current form
			 *
			 * We don't rename them here because the
			 * certificate backend historically expects
			 * these exact POST names.
			 */

			const fromInput = block.querySelector('[name="from"]')

			const toInput = block.querySelector('[name="to"]')

			const textInput = block.querySelector('[name="text"]')

			const phoneInput = block.querySelector('[name="phone"]')

			function isEmptyLegacyInput(input) {
				if (!input) {
					return true
				}

				const value = input.value.trim()

				return !value || value === input.getAttribute('data-value')
			}

			if (isEmptyLegacyInput(fromInput)) {
				showError(fromInput, window.CERTIFICATE_MESSAGES.sender)

				isValid = false
			}

			if (isEmptyLegacyInput(toInput)) {
				showError(toInput, window.CERTIFICATE_MESSAGES.recipient)

				isValid = false
			}

			if (isEmptyLegacyInput(textInput)) {
				showError(textInput, window.CERTIFICATE_MESSAGES.greeting)

				isValid = false
			}

			if (isEmptyLegacyInput(phoneInput)) {
				showError(phoneInput, window.CERTIFICATE_MESSAGES.phone)

				isValid = false
			}

			if (cerFrom) {
				cerFrom.textContent = fromInput ? fromInput.value : ''
			}

			if (cerTo) {
				cerTo.textContent = toInput ? toInput.value : ''
			}

			if (cerText) {
				cerText.textContent = textInput ? textInput.value : ''
			}

			if (cerPhone) {
				cerPhone.textContent = phoneInput ? phoneInput.value : ''
			}

			return isValid
		}

		/* =====================================================
	   Step 4 — delivery / receipt
	   ===================================================== */

		function validateStep4(block) {
			removeErrors(block)

			let isValid = true

			const selectedTime = block.querySelector(
				'input[name="time-send"]:checked'
			)

			if (!selectedTime) {
				const timeArea = block.querySelector('.time-send-certificate')

				if (timeArea) {
					const error = document.createElement('div')

					error.className = 'certificate__error-message'

					error.textContent = window.CERTIFICATE_MESSAGES.timeMode

					timeArea.appendChild(error)
				}

				isValid = false
			}

			if (selectedTime && selectedTime.value === 'set') {
				const dateInput = block.querySelector('input[name="date"]')

				const timeInput = block.querySelector('input[name="time"]')

				if (
					!dateInput ||
					!dateInput.value ||
					dateInput.value === dateInput.getAttribute('data-value')
				) {
					showError(dateInput, window.CERTIFICATE_MESSAGES.date)

					isValid = false
				}

				if (
					!timeInput ||
					!timeInput.value ||
					timeInput.value === timeInput.getAttribute('data-value')
				) {
					showError(timeInput, window.CERTIFICATE_MESSAGES.time)

					isValid = false
				}
			}

			const emailInput = block.querySelector('input[name="cec_email"]')

			if (
				!emailInput ||
				!emailInput.value ||
				emailInput.value.trim() === '' ||
				emailInput.value === emailInput.getAttribute('data-value')
			) {
				showError(emailInput, window.CERTIFICATE_MESSAGES.email)

				isValid = false
			}

			if (cerEmail) {
				cerEmail.textContent = emailInput ? emailInput.value : ''
			}

			return isValid
		}

		function validateCurrentStep() {
			const currentBlock = blocks[state.currentStep]

			if (!currentBlock) {
				return true
			}

			switch (state.currentStep) {
				case 0:
					return validateStep1(currentBlock)

				case 1:
					return validateStep2(currentBlock)

				case 2:
					return validateStep3(currentBlock)

				case 3:
					return validateStep4(currentBlock)

				default:
					return true
			}
		}

		/* =====================================================
	   UI state
	   ===================================================== */

		function updateSidebar() {
			stepItems.forEach(function (item, index) {
				const isActive = index === state.currentStep

				const isCompleted = index < state.currentStep

				item.classList.toggle('_active', isActive)

				item.classList.toggle('_completed', isCompleted)
			})
		}

		function updateStepNumber(direction) {
			if (!stepNumber) {
				return
			}

			const value = String(state.currentStep + 1).padStart(2, '0')

			if (reducedMotion || typeof window.gsap === 'undefined') {
				stepNumber.textContent = value
				return
			}

			gsap.to(stepNumber, {
				opacity: 0,
				y: direction > 0 ? -10 : 10,
				duration: 0.18,
				ease: 'power2.in',
				onComplete: function () {
					stepNumber.textContent = value

					gsap.fromTo(
						stepNumber,
						{
							opacity: 0,
							y: direction > 0 ? 10 : -10
						},
						{
							opacity: 1,
							y: 0,
							duration: 0.28,
							ease: 'power3.out'
						}
					)
				}
			})
		}

		function updateBackButtons() {
			backButtons.forEach(function (button) {
				button.classList.toggle('_active', state.currentStep > 0)
			})
		}

		function updateMobileNavigation() {
			const activeStepItem = stepItems[state.currentStep]

			if (mobileTitle && activeStepItem) {
				const stepText = activeStepItem.querySelector(
					'.certificate-builder__step-text'
				)

				if (stepText) {
					mobileTitle.textContent = stepText.textContent.trim()
				}
			}

			if (mobileBackButton) {
				mobileBackButton.hidden = state.currentStep === 0
			}
		}

		/* =====================================================
	   Transition
	   ===================================================== */

		function goToStep(index) {
			if (
				index < 0 ||
				index >= blocks.length ||
				index === state.currentStep ||
				state.isTransitioning
			) {
				return
			}

			const direction = index > state.currentStep ? 1 : -1

			if (direction > 0 && !validateCurrentStep()) {
				return
			}

			const currentBlock = blocks[state.currentStep]

			const nextBlock = blocks[index]

			state.isTransitioning = true

			if (reducedMotion || typeof window.gsap === 'undefined') {
				currentBlock.classList.remove('_active')
				nextBlock.classList.add('_active')

				state.currentStep = index

				updateSidebar()
				updateStepNumber(direction)
				updateBackButtons()
				updateMobileNavigation()

				state.isTransitioning = false

				return
			}

			const offset = 56 * direction

			const timeline = gsap.timeline({
				onComplete: function () {
					state.isTransitioning = false
				}
			})

			timeline
				.to(currentBlock, {
					x: -offset,
					opacity: 0,
					scale: 0.985,
					duration: 0.34,
					ease: 'power2.in'
				})
				.add(function () {
					currentBlock.classList.remove('_active')

					gsap.set(currentBlock, {
						clearProps: 'transform,opacity'
					})

					state.currentStep = index

					nextBlock.classList.add('_active')

					updateSidebar()
					updateStepNumber(direction)
					updateBackButtons()
					updateMobileNavigation()

					gsap.set(nextBlock, {
						x: offset,
						opacity: 0,
						scale: 0.985
					})
				})
				.to(nextBlock, {
					x: 0,
					opacity: 1,
					scale: 1,
					duration: 0.48,
					ease: 'power3.out',
					clearProps: 'transform,opacity'
				})
		}

		/* =====================================================
	   Controls
	   ===================================================== */

		form.querySelectorAll('._next-step').forEach(function (button) {
			button.addEventListener('click', function (event) {
				event.preventDefault()

				goToStep(state.currentStep + 1)
			})
		})

		backButtons.forEach(function (button) {
			button.addEventListener('click', function (event) {
				event.preventDefault()

				goToStep(state.currentStep - 1)
			})
		})

		if (mobileBackButton) {
			mobileBackButton.addEventListener('click', function (event) {
				event.preventDefault()

				goToStep(state.currentStep - 1)
			})
		}

		/* =====================================================
	   Remove validation errors on edit
	   ===================================================== */

		blocks.forEach(function (block) {
			block.querySelectorAll('input').forEach(function (input) {
				function clearInputError() {
					input.classList.remove('_error')

					const parent = input.closest(
						[
							'.data-certificate__item',
							'.denomination-certificate__item',
							'.time-send-certificate__item',
							'.recipe-certificate__row'
						].join(', ')
					)

					if (!parent) {
						return
					}

					const error = parent.querySelector('.certificate__error-message')

					if (error) {
						error.remove()
					}
				}

				input.addEventListener('input', clearInputError)

				input.addEventListener('change', clearInputError)
			})
		})

		/* =====================================================
	   Delivery date/time
	   ===================================================== */

		const setBlock = form.querySelector('.time-send-certificate__set')

		builder
			.querySelectorAll('input[name="time-send"]')
			.forEach(function (radio) {
				radio.addEventListener('change', function () {
					const block = radio.closest('.certificate__block')

					toggleDeliveryDate(block)
				})
			})

		function toggleDeliveryDate(block) {
			if (!block) {
				return
			}

			const selected = block.querySelector('input[name="time-send"]:checked')

			const schedule = block.querySelector('.time-send-certificate__set')

			if (!schedule) {
				return
			}

			const shouldShow = selected && selected.value === 'set'

			const reducedMotion = window.matchMedia(
				'(prefers-reduced-motion: reduce)'
			).matches

			if (reducedMotion || typeof window.gsap === 'undefined') {
				schedule.style.height = shouldShow ? 'auto' : '0'

				schedule.style.opacity = shouldShow ? '1' : '0'

				schedule.classList.toggle('_open', shouldShow)

				return
			}

			if (shouldShow) {
				schedule.classList.remove('_open')

				gsap.to(schedule, {
					height: 'auto',
					opacity: 1,

					duration: 0.4,

					ease: 'power3.out',

					overwrite: true,

					onComplete: function () {
						schedule.classList.add('_open')
					}
				})

				return
			}

			schedule.classList.remove('_open')

			gsap.to(schedule, {
				height: 0,
				opacity: 0,

				duration: 0.3,

				ease: 'power2.inOut',

				overwrite: true
			})
		}

		const deliveryBlock = form.querySelector(
			'.certificate-builder__step--delivery'
		)

		if (deliveryBlock) {
			toggleDeliveryDate(deliveryBlock)
		}

		/* =====================================================
	   Initial
	   ===================================================== */

		blocks.forEach(function (block, index) {
			block.classList.toggle('_active', index === 0)
		})

		updateSidebar()
		updateBackButtons()
		updateMobileNavigation()
	}

	initCertificateDesignSlider()
	initCertificateDenomination()
	initCertificateWizard()
	initCertificateEntrance()
})()
