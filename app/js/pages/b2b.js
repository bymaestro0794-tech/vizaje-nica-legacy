;(function () {
	'use strict'

	const header = document.querySelector('[data-b2b-header]')
	const menu = document.querySelector('[data-b2b-mobile-menu]')
	const menuToggle = document.querySelector('[data-b2b-menu-toggle]')
	const menuCloseButtons = document.querySelectorAll('[data-b2b-menu-close]')
	const menuPanel = menu ? menu.querySelector('.b2b-mobile-menu__panel') : null
	const menuItems = menu ? menu.querySelectorAll('.b2b-mobile-menu__nav a') : []
	const mobileCta = menu ? menu.querySelector('.b2b-mobile-menu__cta') : null

	const reduceMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches

	const gsap = window.gsap
	const Motion = window.Motion
	const Lenis = window.Lenis

	let isMenuOpen = false
	let lenis = null

	/*
	 * Lenis — плавный скролл страницы.
	 */
	if (Lenis && !reduceMotion) {
		lenis = new Lenis({
			lerp: 0.08,
			smoothWheel: true,
			syncTouch: false
		})

		function raf(time) {
			lenis.raf(time)
			window.requestAnimationFrame(raf)
		}

		window.requestAnimationFrame(raf)
	}

	/*
	 * Добавляем состояние header при прокрутке.
	 */
	function updateHeaderState() {
		if (!header) {
			return
		}

		header.classList.toggle('is-scrolled', window.scrollY > 16)
	}

	updateHeaderState()

	window.addEventListener('scroll', updateHeaderState, {
		passive: true
	})

	/*
	 * GSAP timeline для mobile menu.
	 */
	let menuTimeline = null

	if (gsap && menu && menuPanel) {
		menuTimeline = gsap.timeline({
			paused: true,
			reversed: true,
			defaults: {
				ease: 'power3.out'
			}
		})

		menuTimeline
			.fromTo(
				menu,
				{
					autoAlpha: 0
				},
				{
					autoAlpha: 1,
					duration: 0.35
				}
			)
			.fromTo(
				menuPanel,
				{
					y: -18,
					scale: 0.98,
					opacity: 0
				},
				{
					y: 0,
					scale: 1,
					opacity: 1,
					duration: 0.42
				},
				0.02
			)
			.fromTo(
				menuItems,
				{
					y: 18,
					opacity: 0
				},
				{
					y: 0,
					opacity: 1,
					duration: 0.3,
					stagger: 0.055
				},
				0.18
			)
			.fromTo(
				['.b2b-mobile-menu__divider', mobileCta],
				{
					opacity: 0,
					y: 8
				},
				{
					opacity: 1,
					y: 0,
					duration: 0.28,
					stagger: 0.06
				},
				0.42
			)
	}

	function openMenu() {
		if (!menu || isMenuOpen) {
			return
		}

		isMenuOpen = true

		menu.classList.add('is-open')
		menu.setAttribute('aria-hidden', 'false')
		menuToggle?.setAttribute('aria-expanded', 'true')
		menuToggle?.setAttribute('aria-label', 'Close menu')

		document.body.classList.add('b2b-menu-lock')

		if (lenis) {
			lenis.stop()
		}

		if (menuTimeline && !reduceMotion) {
			menuTimeline.play()
		} else if (menu) {
			menu.style.visibility = 'visible'
			menu.style.opacity = '1'
		}
	}

	function closeMenu() {
		if (!menu || !isMenuOpen) {
			return
		}

		isMenuOpen = false

		menu.setAttribute('aria-hidden', 'true')
		menuToggle?.setAttribute('aria-expanded', 'false')
		menuToggle?.setAttribute('aria-label', 'Open menu')

		document.body.classList.remove('b2b-menu-lock')

		if (lenis) {
			lenis.start()
		}

		if (menuTimeline && !reduceMotion) {
			menuTimeline.reverse()

			window.setTimeout(function () {
				if (!isMenuOpen) {
					menu.classList.remove('is-open')
				}
			}, 450)
		} else {
			menu.classList.remove('is-open')
			menu.style.visibility = 'hidden'
			menu.style.opacity = '0'
		}
	}

	menuToggle?.addEventListener('click', function () {
		if (isMenuOpen) {
			closeMenu()
		} else {
			openMenu()
		}
	})

	menuCloseButtons.forEach(function (button) {
		button.addEventListener('click', closeMenu)
	})

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && isMenuOpen) {
			closeMenu()
		}
	})

	/*
	 * Закрытие mobile menu при выборе пункта.
	 */
	document.querySelectorAll('a[href^="#"]').forEach(function (link) {
		link.addEventListener('click', function (event) {
			const targetId = link.getAttribute('href')
			const target = document.querySelector(targetId)

			if (!target) {
				closeMenu()
				return
			}

			event.preventDefault()
			closeMenu()

			if (lenis) {
				lenis.scrollTo(target, {
					offset: -110,
					duration: reduceMotion ? 0 : 0.9
				})
			} else {
				target.scrollIntoView({
					behavior: reduceMotion ? 'auto' : 'smooth',
					block: 'start'
				})
			}
		})
	})

	/*
	 * Motion — лёгкая micro-анимация CTA.
	 */
	if (Motion && typeof Motion.animate === 'function' && !reduceMotion) {
		const motionButtons = document.querySelectorAll(
			'.b2b-header__cta, .b2b-mobile-menu__cta'
		)

		motionButtons.forEach(function (button) {
			button.addEventListener('pointerenter', function () {
				Motion.animate(
					button,
					{
						scale: 1.035
					},
					{
						duration: 0.22,
						ease: 'easeOut'
					}
				)
			})

			button.addEventListener('pointerleave', function () {
				Motion.animate(
					button,
					{
						scale: 1
					},
					{
						duration: 0.28,
						ease: 'easeOut'
					}
				)
			})
		})
	}

	/*
	 * Brands — automatic continuous carousels.
	 */
	if (gsap && !reduceMotion) {
		document.querySelectorAll('[data-b2b-brand-rail]').forEach(function (rail) {
			const track = rail.querySelector('[data-b2b-brand-track]')

			if (!track || track.children.length === 0) {
				return
			}

			const originalItems = Array.from(track.children)

			originalItems.forEach(function (item) {
				track.appendChild(item.cloneNode(true))
			})

			const distance = track.scrollWidth / 2

			const animation = gsap.to(track, {
				x: -distance,
				duration: 34,
				ease: 'none',
				repeat: -1
			})

			rail.addEventListener('mouseenter', function () {
				animation.pause()
			})

			rail.addEventListener('mouseleave', function () {
				animation.resume()
			})

			rail.addEventListener('focusin', function () {
				animation.pause()
			})

			rail.addEventListener('focusout', function () {
				animation.resume()
			})
		})
	}

	/*
	 * При переходе обратно на desktop закрываем mobile menu.
	 */
	window.addEventListener('resize', function () {
		if (window.innerWidth > 820 && isMenuOpen) {
			closeMenu()
		}
	})
})()
