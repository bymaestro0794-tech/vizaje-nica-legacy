/**
 * Vizaje-Nica Header
 *
 * Требуется GSAP 3.
 *
 * Функции:
 * - premium desktop mega menu;
 * - плавное изменение высоты mega menu;
 * - crossfade между категориями;
 * - stagger-анимация колонок и ссылок;
 * - desktop user dropdown;
 * - mobile fullscreen navigation;
 * - mobile menu levels 1–3;
 * - открытие search overlay;
 * - Multisearch через #head_search;
 * - scroll-поведение desktop header;
 * - Escape и body lock.
 */

;(function () {
	'use strict'

	const SELECTORS = {
		header: '[data-site-header]',
		overlay: '[data-header-overlay]',

		mega: '[data-header-mega]',
		megaItem: '[data-mega-item]',
		megaTrigger: '[data-mega-trigger]',
		megaPanel: '[data-mega-panel]',
		megaIntro: '.header-mega__intro',
		megaColumn: '.header-mega__column',
		megaAnimatedElement: `
			.header-mega__eyebrow,
			.header-mega__title,
			.header-mega__all,
			.header-mega__column-title,
			.header-mega__list li
		`,

		mobileMenu: '[data-mobile-menu]',
		mobileOpen: '[data-mobile-menu-open]',
		mobileClose: '[data-mobile-menu-close]',
		mobileScreen: '[data-mobile-screen]',
		mobileScreenOpen: '[data-mobile-screen-open]',
		mobileScreenBack: '[data-mobile-screen-back]',
		mobileLevelTitle: '[data-mobile-level-title]',
		mobileRootLogo: '[data-mobile-root-logo]',
		mobileRootHeaderLeft: '[data-mobile-root-header-left]',
		mobileRootHeaderRight: '[data-mobile-root-header-right]',
		mobileNestedClose: '.mobile-navigation__nested-close',

		mobileHeader: '.mobile-navigation__header',
		mobileBody: '.mobile-navigation__body',
		mobileAccount: '.mobile-navigation__account',
		mobileLanguage: '.mobile-navigation__language',
		mobileCatalogItem: '.mobile-navigation__catalog-item',
		mobileSecondaryItem: '.mobile-navigation__secondary-item',
		mobileLevelItem: '.mobile-navigation__level-item',

		search: '[data-header-search]',
		searchOpen: '.search-actions-main-header__icon',
		searchClose: '[data-header-search-close]',
		searchInput: '#head_search',

		user: '[data-header-user]',
		userToggle: '[data-header-user-toggle]'
	}

	const MOBILE_BREAKPOINT = 991
	const SCROLL_THRESHOLD = 20
	const SCROLL_DIRECTION_THRESHOLD = 6

	const MEGA_OPEN_DELAY = 80
	const MEGA_CLOSE_DELAY = 180

	const MEGA_OPEN_DURATION = 0.48
	const MEGA_SWITCH_DURATION = 0.42
	const MEGA_CLOSE_DURATION = 0.34

	const CONTENT_IN_DURATION = 0.34
	const CONTENT_OUT_DURATION = 0.15

	const COLUMN_STAGGER = 0.04
	const LINK_STAGGER = 0.03

	const MOBILE_OPEN_DURATION = 0.46
	const MOBILE_CLOSE_DURATION = 0.36
	const MOBILE_SCREEN_OUT_DURATION = 0.18
	const MOBILE_SCREEN_IN_DURATION = 0.34
	const MOBILE_ITEM_STAGGER = 0.03

	function initHeader() {
		const header = document.querySelector(SELECTORS.header)

		if (!header) {
			return
		}

		if (header.dataset.headerInitialized === 'true') {
			return
		}

		header.dataset.headerInitialized = 'true'

		if (typeof window.gsap === 'undefined') {
			console.error('Vizaje-Nica Header: GSAP is not loaded.')

			return
		}

		const gsap = window.gsap

		/* ==========================================================
		   Elements
		   ========================================================== */

		const overlay = document.querySelector(SELECTORS.overlay)

		const mega = header.querySelector(SELECTORS.mega)

		const megaPanels = mega
			? Array.from(mega.querySelectorAll(SELECTORS.megaPanel))
			: []

		const megaTriggers = Array.from(
			header.querySelectorAll(SELECTORS.megaTrigger)
		)

		const mobileMenu = header.querySelector(SELECTORS.mobileMenu)

		const mobileBackButton = header.querySelector(SELECTORS.mobileScreenBack)

		const mobileLevelTitle = header.querySelector(SELECTORS.mobileLevelTitle)

		const mobileRootLogo = header.querySelector(SELECTORS.mobileRootLogo)

		const mobileRootHeaderLeft = header.querySelector(
			SELECTORS.mobileRootHeaderLeft
		)

		const mobileRootHeaderRight = header.querySelector(
			SELECTORS.mobileRootHeaderRight
		)

		const mobileNestedClose = header.querySelector(SELECTORS.mobileNestedClose)

		const mobileHeader = mobileMenu?.querySelector(SELECTORS.mobileHeader)

		const mobileBody = mobileMenu?.querySelector(SELECTORS.mobileBody)

		const search = header.querySelector(SELECTORS.search)

		const searchInput = header.querySelector(SELECTORS.searchInput)

		/* ==========================================================
		   State
		   ========================================================== */

		let activeMegaPanel = null
		let activeMegaTrigger = null
		let megaTimeline = null

		let megaOpenTimer = null
		let megaCloseTimer = null

		let mobileScreenHistory = ['root']
		let lastFocusedElement = null

		let mobileTimeline = null
		let mobileScreenTimeline = null
		let mobileIsAnimating = false

		let previousScrollY = Math.max(window.scrollY, 0)

		let scrollTicking = false
		let resizeTimer = null

		/* ==========================================================
		   Helpers
		   ========================================================== */

		const setHidden = (element, isHidden) => {
			if (!element) {
				return
			}

			element.hidden = isHidden
		}

		const isDesktop = () => {
			return window.innerWidth > MOBILE_BREAKPOINT
		}

		const isMobileMenuOpen = () => {
			return Boolean(mobileMenu?.classList.contains('is-open'))
		}

		const saveFocus = element => {
			if (element instanceof HTMLElement) {
				lastFocusedElement = element
				return
			}

			if (document.activeElement instanceof HTMLElement) {
				lastFocusedElement = document.activeElement
			}
		}

		const restoreFocus = () => {
			if (
				lastFocusedElement instanceof HTMLElement &&
				document.contains(lastFocusedElement)
			) {
				lastFocusedElement.focus({
					preventScroll: true
				})
			}

			lastFocusedElement = null
		}

		const syncBodyLock = () => {
			document.body.classList.toggle(
				'is-header-locked',
				isMobileMenuOpen() || isSearchOpen()
			)
		}

		const clearMegaTimers = () => {
			window.clearTimeout(megaOpenTimer)

			window.clearTimeout(megaCloseTimer)
		}

		const killMegaAnimation = () => {
			if (megaTimeline) {
				megaTimeline.kill()
				megaTimeline = null
			}

			if (mega) {
				gsap.killTweensOf(mega)
			}

			if (overlay) {
				gsap.killTweensOf(overlay)
			}

			megaPanels.forEach(panel => {
				gsap.killTweensOf(panel)

				gsap.killTweensOf(panel.querySelectorAll(SELECTORS.megaAnimatedElement))
			})
		}

		const killMobileAnimation = () => {
			if (mobileTimeline) {
				mobileTimeline.kill()
				mobileTimeline = null
			}

			if (mobileScreenTimeline) {
				mobileScreenTimeline.kill()
				mobileScreenTimeline = null
			}

			if (!mobileMenu) {
				return
			}

			gsap.killTweensOf([
				mobileMenu,
				mobileHeader,
				mobileBody,
				mobileLevelTitle
			])
			gsap.killTweensOf(mobileMenu.querySelectorAll(SELECTORS.mobileScreen))
			gsap.killTweensOf(
				mobileMenu.querySelectorAll(
					[
						SELECTORS.mobileAccount,
						SELECTORS.mobileLanguage,
						SELECTORS.mobileCatalogItem,
						SELECTORS.mobileSecondaryItem,
						SELECTORS.mobileLevelItem
					].join(',')
				)
			)

			mobileIsAnimating = false
		}

		const getMegaPanel = panelId => {
			if (!panelId) {
				return null
			}

			return (
				megaPanels.find(panel => {
					return panel.dataset.megaPanel === panelId
				}) || null
			)
		}

		const getPanelHeight = panel => {
			if (!panel || !mega) {
				return 0
			}

			const previousDisplay = panel.style.display

			const previousPosition = panel.style.position

			const previousVisibility = panel.style.visibility

			const previousOpacity = panel.style.opacity

			gsap.set(panel, {
				display: 'block',
				position: 'absolute',
				visibility: 'hidden',
				opacity: 0,
				pointerEvents: 'none'
			})

			const height = panel.scrollHeight || panel.offsetHeight

			panel.style.display = previousDisplay

			panel.style.position = previousPosition

			panel.style.visibility = previousVisibility

			panel.style.opacity = previousOpacity

			return height
		}

		const setTriggerState = trigger => {
			megaTriggers.forEach(item => {
				const isActive = item === trigger

				item.classList.toggle('is-mega-active', isActive)

				item.setAttribute('aria-expanded', String(isActive))
			})
		}

		/* ==========================================================
		   Overlay
		   ========================================================== */

		const showOverlay = () => {
			if (!overlay) {
				return
			}

			overlay.classList.add('is-visible')

			gsap.to(overlay, {
				opacity: 1,
				duration: 0.36,
				ease: 'power2.out',
				overwrite: true
			})
		}

		const hideOverlay = () => {
			if (!overlay) {
				return
			}

			gsap.to(overlay, {
				opacity: 0,
				duration: 0.24,
				ease: 'power1.in',
				overwrite: true,
				onComplete: () => {
					overlay.classList.remove('is-visible')
				}
			})
		}

		/* ==========================================================
		   User dropdown
		   ========================================================== */

		const closeUserPanels = exception => {
			header.querySelectorAll(`${SELECTORS.user}.is-open`).forEach(user => {
				if (user === exception) {
					return
				}

				user.classList.remove('is-open')

				user
					.querySelector(SELECTORS.userToggle)
					?.setAttribute('aria-expanded', 'false')
			})
		}

		const toggleUserPanel = user => {
			if (!user) {
				return
			}

			const shouldOpen = !user.classList.contains('is-open')

			closeMegaMenu({
				immediate: false
			})

			closeUserPanels(user)

			user.classList.toggle('is-open', shouldOpen)

			user
				.querySelector(SELECTORS.userToggle)
				?.setAttribute('aria-expanded', String(shouldOpen))
		}

		/* ==========================================================
		   Mega content animation
		   ========================================================== */

		const preparePanel = panel => {
			megaPanels.forEach(item => {
				if (item === panel) {
					gsap.set(item, {
						display: 'block',
						position: 'absolute',
						inset: '0 auto auto 0',
						width: '100%',
						visibility: 'visible',
						pointerEvents: 'auto'
					})
				} else if (item !== activeMegaPanel) {
					gsap.set(item, {
						display: 'none',
						visibility: 'hidden',
						pointerEvents: 'none'
					})
				}
			})
		}

		const animatePanelContentIn = (timeline, panel, startTime) => {
			if (!panel) {
				return
			}

			const intro = panel.querySelector(SELECTORS.megaIntro)

			const columns = Array.from(panel.querySelectorAll(SELECTORS.megaColumn))

			const introElements = intro
				? Array.from(intro.querySelectorAll(SELECTORS.megaAnimatedElement))
				: []

			gsap.set(panel, {
				opacity: 1,
				y: 0
			})

			if (introElements.length) {
				timeline.fromTo(
					introElements,
					{
						opacity: 0,
						y: 8
					},
					{
						opacity: 1,
						y: 0,
						duration: CONTENT_IN_DURATION,
						stagger: LINK_STAGGER,
						ease: 'power2.out',
						overwrite: true
					},
					startTime
				)
			}

			columns.forEach((column, columnIndex) => {
				const elements = Array.from(
					column.querySelectorAll(SELECTORS.megaAnimatedElement)
				)

				if (!elements.length) {
					return
				}

				timeline.fromTo(
					elements,
					{
						opacity: 0,
						y: 8
					},
					{
						opacity: 1,
						y: 0,
						duration: CONTENT_IN_DURATION,
						stagger: LINK_STAGGER,
						ease: 'power2.out',
						overwrite: true
					},
					startTime + 0.03 + columnIndex * COLUMN_STAGGER
				)
			})
		}

		const animatePanelOut = (timeline, panel, startTime = 0) => {
			if (!panel) {
				return
			}

			const elements = panel.querySelectorAll(SELECTORS.megaAnimatedElement)

			timeline.to(
				elements,
				{
					opacity: 0,
					y: -4,
					duration: CONTENT_OUT_DURATION,
					ease: 'power1.in',
					stagger: {
						each: 0.008,
						from: 'end'
					},
					overwrite: true
				},
				startTime
			)

			timeline.to(
				panel,
				{
					opacity: 0,
					duration: CONTENT_OUT_DURATION,
					ease: 'power1.in',
					overwrite: true
				},
				startTime
			)
		}

		/* ==========================================================
		   Mega open
		   ========================================================== */

		const openMegaMenu = trigger => {
			if (!trigger || !mega || !isDesktop()) {
				return
			}

			const panel = getMegaPanel(trigger.dataset.megaTrigger)

			if (!panel) {
				return
			}

			clearMegaTimers()

			if (
				activeMegaPanel === panel &&
				header.classList.contains('is-mega-open')
			) {
				setTriggerState(trigger)
				return
			}

			closeUserPanels()

			if (header.classList.contains('is-mega-open') && activeMegaPanel) {
				switchMegaPanel(trigger, panel)

				return
			}

			killMegaAnimation()

			activeMegaPanel = panel
			activeMegaTrigger = trigger

			preparePanel(panel)
			setTriggerState(trigger)

			const targetHeight = getPanelHeight(panel)

			header.classList.add('is-mega-open')

			mega.setAttribute('aria-hidden', 'false')

			gsap.set(mega, {
				height: 0,
				opacity: 1,
				visibility: 'visible',
				pointerEvents: 'auto',
				overflow: 'hidden'
			})

			gsap.set(panel, {
				display: 'block',
				opacity: 1,
				visibility: 'visible',
				pointerEvents: 'auto'
			})

			showOverlay()

			megaTimeline = gsap.timeline({
				defaults: {
					overwrite: true
				},
				onComplete: () => {
					gsap.set(mega, {
						height: targetHeight,
						overflow: 'hidden',
						visibility: 'visible',
						pointerEvents: 'auto'
					})

					gsap.set(panel, {
						display: 'block',
						position: 'absolute',
						inset: '0 auto auto 0',
						width: '100%',
						opacity: 1,
						visibility: 'visible',
						pointerEvents: 'auto'
					})

					megaTimeline = null
				}
			})

			megaTimeline.to(
				mega,
				{
					height: targetHeight,
					duration: MEGA_OPEN_DURATION,
					ease: 'power3.inOut'
				},
				0
			)

			animatePanelContentIn(megaTimeline, panel, 0.09)
		}

		/* ==========================================================
		   Mega switch
		   ========================================================== */

		const switchMegaPanel = (trigger, nextPanel) => {
			if (!mega || !activeMegaPanel || activeMegaPanel === nextPanel) {
				return
			}

			killMegaAnimation()

			const renderedHeight = Math.ceil(mega.getBoundingClientRect().height)

			gsap.set(mega, {
				height: renderedHeight,
				overflow: 'hidden',
				visibility: 'visible',
				pointerEvents: 'auto'
			})

			const currentPanel = activeMegaPanel

			const currentHeight = mega.getBoundingClientRect().height

			megaPanels.forEach(panel => {
				if (panel !== currentPanel && panel !== nextPanel) {
					gsap.set(panel, {
						display: 'none',
						opacity: 0,
						y: 0,
						visibility: 'hidden',
						pointerEvents: 'none'
					})
				}
			})

			preparePanel(nextPanel)

			const nextHeight = getPanelHeight(nextPanel)

			gsap.set(mega, {
				height: currentHeight,
				overflow: 'hidden'
			})

			gsap.set(nextPanel, {
				display: 'block',
				opacity: 0,
				y: 6,
				visibility: 'visible',
				pointerEvents: 'none',
				position: 'absolute',
				inset: '0 auto auto 0',
				width: '100%'
			})

			setTriggerState(trigger)

			activeMegaPanel = nextPanel
			activeMegaTrigger = trigger

			megaTimeline = gsap.timeline({
				defaults: {
					overwrite: true
				},
				onComplete: () => {
					gsap.set(currentPanel, {
						display: 'none',
						visibility: 'hidden',
						pointerEvents: 'none',
						opacity: 0,
						y: 0
					})

					gsap.set(nextPanel, {
						display: 'block',
						position: 'absolute',
						inset: '0 auto auto 0',
						width: '100%',
						opacity: 1,
						y: 0,
						visibility: 'visible',
						pointerEvents: 'auto'
					})

					gsap.set(mega, {
						height: nextHeight,
						overflow: 'hidden',
						visibility: 'visible',
						pointerEvents: 'auto'
					})

					megaTimeline = null
				}
			})

			animatePanelOut(megaTimeline, currentPanel, 0)

			megaTimeline.to(
				mega,
				{
					height: nextHeight,
					duration: MEGA_SWITCH_DURATION,
					ease: 'power3.inOut'
				},
				0
			)

			megaTimeline.to(
				nextPanel,
				{
					opacity: 1,
					y: 0,
					duration: 0.18,
					ease: 'power2.out'
				},
				0.1
			)

			animatePanelContentIn(megaTimeline, nextPanel, 0.12)
		}

		/* ==========================================================
		   Mega close
		   ========================================================== */

		function closeMegaMenu(options = {}) {
			const immediate = options.immediate === true

			clearMegaTimers()

			if (!mega || !header.classList.contains('is-mega-open')) {
				setTriggerState(null)
				return
			}

			killMegaAnimation()

			const panel = activeMegaPanel

			if (immediate) {
				header.classList.remove('is-mega-open')

				mega.setAttribute('aria-hidden', 'true')

				gsap.set(mega, {
					height: 0,
					visibility: 'hidden',
					pointerEvents: 'none',
					overflow: 'hidden'
				})

				megaPanels.forEach(item => {
					gsap.set(item, {
						display: 'none',
						visibility: 'hidden',
						pointerEvents: 'none'
					})
				})

				setTriggerState(null)
				activeMegaPanel = null
				activeMegaTrigger = null

				hideOverlay()
				return
			}

			const currentHeight = mega.getBoundingClientRect().height

			gsap.set(mega, {
				height: currentHeight,
				overflow: 'hidden'
			})

			megaTimeline = gsap.timeline({
				defaults: {
					overwrite: true
				},
				onComplete: () => {
					header.classList.remove('is-mega-open')

					mega.setAttribute('aria-hidden', 'true')

					gsap.set(mega, {
						height: 0,
						visibility: 'hidden',
						pointerEvents: 'none',
						overflow: 'hidden'
					})

					megaPanels.forEach(item => {
						gsap.set(item, {
							display: 'none',
							visibility: 'hidden',
							pointerEvents: 'none'
						})
					})

					setTriggerState(null)

					activeMegaPanel = null
					activeMegaTrigger = null
					megaTimeline = null
				}
			})

			animatePanelOut(megaTimeline, panel, 0)

			megaTimeline.to(
				mega,
				{
					height: 0,
					duration: MEGA_CLOSE_DURATION,
					ease: 'power3.inOut'
				},
				0.08
			)

			hideOverlay()
		}

		/* ==========================================================
		   Mega hover
		   ========================================================== */

		const scheduleMegaOpen = trigger => {
			window.clearTimeout(megaCloseTimer)

			window.clearTimeout(megaOpenTimer)

			if (header.classList.contains('is-mega-open')) {
				openMegaMenu(trigger)
				return
			}

			megaOpenTimer = window.setTimeout(() => {
				openMegaMenu(trigger)
			}, MEGA_OPEN_DELAY)
		}

		const scheduleMegaClose = () => {
			window.clearTimeout(megaOpenTimer)

			window.clearTimeout(megaCloseTimer)

			megaCloseTimer = window.setTimeout(() => {
				const triggerHovered = Boolean(
					header.querySelector(`${SELECTORS.megaItem}:hover`)
				)

				const megaHovered = Boolean(mega?.matches(':hover'))

				if (triggerHovered || megaHovered) {
					return
				}

				closeMegaMenu()
			}, MEGA_CLOSE_DELAY)
		}

		const initMegaMenu = () => {
			header.querySelectorAll(SELECTORS.megaItem).forEach(item => {
				const trigger = item.querySelector(SELECTORS.megaTrigger)

				if (!trigger) {
					return
				}

				trigger.setAttribute('aria-haspopup', 'true')

				trigger.setAttribute('aria-expanded', 'false')

				item.addEventListener('mouseenter', () => {
					window.clearTimeout(megaCloseTimer)

					scheduleMegaOpen(trigger)
				})

				item.addEventListener('mouseleave', event => {
					const related = event.relatedTarget

					if (related instanceof Node && mega?.contains(related)) {
						return
					}

					scheduleMegaClose()
				})

				trigger.addEventListener('focus', () => {
					openMegaMenu(trigger)
				})
			})

			mega?.addEventListener('mouseenter', () => {
				window.clearTimeout(megaCloseTimer)
			})

			mega?.addEventListener('mouseleave', event => {
				const related = event.relatedTarget

				const goingToTrigger =
					related instanceof Element &&
					Boolean(related.closest(SELECTORS.megaItem))

				if (goingToTrigger) {
					return
				}

				scheduleMegaClose()
			})
		}

		/* ==========================================================
		   Mobile screens
		   ========================================================== */

		const getMobileScreen = screenId => {
			if (!mobileMenu || !screenId) {
				return null
			}

			return (
				Array.from(mobileMenu.querySelectorAll(SELECTORS.mobileScreen)).find(
					screen => {
						return screen.dataset.mobileScreen === screenId
					}
				) || null
			)
		}

		const getMobileScreenItems = screen => {
			if (!screen) {
				return []
			}

			if (screen.dataset.mobileScreen === 'root') {
				return [
					...screen.querySelectorAll(SELECTORS.mobileAccount),
					...screen.querySelectorAll(SELECTORS.mobileLanguage),
					...screen.querySelectorAll(SELECTORS.mobileCatalogItem),
					...screen.querySelectorAll(SELECTORS.mobileSecondaryItem)
				]
			}

			return Array.from(screen.querySelectorAll(SELECTORS.mobileLevelItem))
		}

		const updateMobileHeader = () => {
			if (!mobileMenu) {
				return
			}

			const currentScreenId =
				mobileScreenHistory[mobileScreenHistory.length - 1] || 'root'

			const currentScreen = getMobileScreen(currentScreenId)

			const isRoot = currentScreenId === 'root'

			mobileMenu.classList.toggle('is-nested', !isRoot)

			setHidden(mobileBackButton, isRoot)

			setHidden(mobileLevelTitle, isRoot)

			setHidden(mobileRootLogo, !isRoot)

			setHidden(mobileRootHeaderLeft, !isRoot)

			setHidden(mobileRootHeaderRight, !isRoot)

			setHidden(mobileNestedClose, isRoot)

			if (mobileLevelTitle) {
				mobileLevelTitle.textContent = isRoot
					? ''
					: currentScreen?.dataset.mobileScreenTitle || ''
			}
		}

		const activateMobileScreen = (screenId, direction = 'forward') => {
			if (!mobileMenu) {
				return
			}

			const nextScreen = getMobileScreen(screenId)

			if (!nextScreen) {
				return
			}

			const currentScreen = mobileMenu.querySelector(
				`${SELECTORS.mobileScreen}.is-active`
			)

			if (currentScreen === nextScreen) {
				updateMobileHeader()
				return
			}

			killMobileAnimation()

			const isForward = direction === 'forward'

			const currentItems = getMobileScreenItems(currentScreen)

			const nextItems = getMobileScreenItems(nextScreen)

			mobileIsAnimating = true

			/*
			 * Подготавливаем следующий экран.
			 */
			mobileMenu.querySelectorAll(SELECTORS.mobileScreen).forEach(screen => {
				if (screen !== currentScreen && screen !== nextScreen) {
					gsap.set(screen, {
						opacity: 0,
						xPercent: 100,
						visibility: 'hidden',
						pointerEvents: 'none'
					})

					screen.classList.remove('is-active')
				}
			})

			nextScreen.classList.add('is-active')

			gsap.set(nextScreen, {
				visibility: 'visible',
				pointerEvents: 'none',
				opacity: 0,
				xPercent: isForward ? 7 : -7
			})

			gsap.set(nextItems, {
				opacity: 0,
				y: 8
			})

			mobileScreenTimeline = gsap.timeline({
				defaults: {
					overwrite: true
				},
				onComplete: () => {
					if (currentScreen) {
						currentScreen.classList.remove(
							'is-active',
							'is-leaving-left',
							'is-leaving-right'
						)

						gsap.set(currentScreen, {
							opacity: 0,
							visibility: 'hidden',
							pointerEvents: 'none',
							xPercent: isForward ? -7 : 7
						})
					}

					gsap.set(nextScreen, {
						opacity: 1,
						xPercent: 0,
						visibility: 'visible',
						pointerEvents: 'auto'
					})

					nextScreen.scrollTop = 0

					mobileIsAnimating = false
					mobileScreenTimeline = null
				}
			})

			if (currentScreen) {
				mobileScreenTimeline.to(
					currentItems,
					{
						opacity: 0,
						y: -3,
						duration: MOBILE_SCREEN_OUT_DURATION,
						stagger: {
							each: 0.008,
							from: 'end'
						},
						ease: 'power1.in'
					},
					0
				)

				mobileScreenTimeline.to(
					currentScreen,
					{
						opacity: 0,
						xPercent: isForward ? -5 : 5,
						duration: MOBILE_SCREEN_OUT_DURATION,
						ease: 'power1.in'
					},
					0
				)
			}

			/*
			 * Новый уровень начинает появляться,
			 * пока старый ещё заканчивает исчезать.
			 */
			mobileScreenTimeline.to(
				nextScreen,
				{
					opacity: 1,
					xPercent: 0,
					duration: MOBILE_SCREEN_IN_DURATION,
					ease: 'power3.out'
				},
				0.1
			)

			mobileScreenTimeline.to(
				nextItems,
				{
					opacity: 1,
					y: 0,
					duration: 0.32,
					stagger: MOBILE_ITEM_STAGGER,
					ease: 'power2.out'
				},
				0.14
			)

			updateMobileHeader()
		}

		const openMobileScreen = screenId => {
			if (!getMobileScreen(screenId)) {
				return
			}

			mobileScreenHistory.push(screenId)

			activateMobileScreen(screenId, 'forward')
		}

		const goBackMobileScreen = () => {
			if (mobileScreenHistory.length <= 1) {
				return
			}

			mobileScreenHistory.pop()

			const previousScreenId =
				mobileScreenHistory[mobileScreenHistory.length - 1] || 'root'

			activateMobileScreen(previousScreenId, 'back')
		}

		const resetMobileScreens = () => {
			if (!mobileMenu) {
				return
			}

			mobileScreenHistory = ['root']

			mobileMenu.querySelectorAll(SELECTORS.mobileScreen).forEach(screen => {
				screen.classList.remove(
					'is-active',
					'is-leaving-left',
					'is-leaving-right'
				)
			})

			getMobileScreen('root')?.classList.add('is-active')

			updateMobileHeader()
		}

		/* ==========================================================
		   Search
		   ========================================================== */

		const isSearchOpen = () => {
			return Boolean(search?.classList.contains('is-open'))
		}

		const openSearch = trigger => {
			if (!search || !searchInput) {
				console.warn(
					'Header search: search container or #head_search not found.'
				)

				return
			}

			saveFocus(trigger)

			closeMobileMenu({
				restore: false,
				immediate: true
			})

			closeMegaMenu({
				immediate: true
			})

			closeUserPanels()

			search.classList.add('is-open')

			search.setAttribute('aria-hidden', 'false')

			header.querySelectorAll(SELECTORS.searchOpen).forEach(button => {
				button.setAttribute('aria-expanded', 'true')
			})

			syncBodyLock()

			window.requestAnimationFrame(() => {
				searchInput.focus({
					preventScroll: true
				})
			})
		}

		const closeSearch = options => {
			const restore = options?.restore === true

			if (!search) {
				return
			}

			search.classList.remove('is-open')

			search.setAttribute('aria-hidden', 'true')

			header.querySelectorAll(SELECTORS.searchOpen).forEach(button => {
				button.setAttribute('aria-expanded', 'false')
			})

			syncBodyLock()

			if (restore) {
				restoreFocus()
			}
		}

		/* ==========================================================
		   Mobile open / close
		   ========================================================== */

		const closeMobileMenu = options => {
			const restore = options?.restore === true

			const immediate = options?.immediate === true

			if (!mobileMenu?.classList.contains('is-open')) {
				return
			}

			killMobileAnimation()

			const activeScreen = mobileMenu.querySelector(
				`${SELECTORS.mobileScreen}.is-active`
			)

			const activeItems = getMobileScreenItems(activeScreen)

			if (immediate) {
				mobileMenu.classList.remove('is-open')

				mobileMenu.setAttribute('aria-hidden', 'true')

				gsap.set(mobileMenu, {
					height: 0,
					opacity: 0,
					visibility: 'hidden',
					pointerEvents: 'none',
					overflow: 'hidden'
				})

				header.querySelectorAll(SELECTORS.mobileOpen).forEach(button => {
					button.setAttribute('aria-expanded', 'false')
				})

				resetMobileScreens()
				syncBodyLock()

				if (restore) {
					restoreFocus()
				}

				return
			}

			mobileIsAnimating = true

			mobileTimeline = gsap.timeline({
				defaults: {
					overwrite: true
				},
				onComplete: () => {
					mobileMenu.classList.remove('is-open')

					mobileMenu.setAttribute('aria-hidden', 'true')

					gsap.set(mobileMenu, {
						height: 0,
						opacity: 0,
						visibility: 'hidden',
						pointerEvents: 'none',
						overflow: 'hidden'
					})

					header.querySelectorAll(SELECTORS.mobileOpen).forEach(button => {
						button.setAttribute('aria-expanded', 'false')
					})

					resetMobileScreens()
					syncBodyLock()

					mobileIsAnimating = false
					mobileTimeline = null

					if (restore) {
						restoreFocus()
					}
				}
			})

			mobileTimeline.to(
				activeItems,
				{
					opacity: 0,
					y: -4,
					duration: 0.15,
					stagger: {
						each: 0.008,
						from: 'end'
					},
					ease: 'power1.in'
				},
				0
			)

			mobileTimeline.to(
				mobileMenu,
				{
					height: varMobileHeaderHeight(),
					duration: MOBILE_CLOSE_DURATION,
					ease: 'power3.inOut'
				},
				0.08
			)

			mobileTimeline.to(
				mobileMenu,
				{
					opacity: 0,
					duration: 0.12,
					ease: 'power1.in'
				},
				0.28
			)
		}

		const varMobileHeaderHeight = () => {
			const value = getComputedStyle(document.documentElement).getPropertyValue(
				'--header-mobile-height'
			)

			const parsed = Number.parseFloat(value)

			return Number.isFinite(parsed) ? parsed : 64
		}

		const openMobileMenu = trigger => {
			if (!mobileMenu) {
				return
			}

			saveFocus(trigger)

			closeSearch({
				restore: false
			})

			closeMegaMenu({
				immediate: true
			})

			closeUserPanels()
			killMobileAnimation()
			resetMobileScreens()

			const rootScreen = getMobileScreen('root')

			if (!rootScreen) {
				return
			}

			const account = rootScreen.querySelector(SELECTORS.mobileAccount)

			const language = rootScreen.querySelector(SELECTORS.mobileLanguage)

			const catalogItems = Array.from(
				rootScreen.querySelectorAll(SELECTORS.mobileCatalogItem)
			)

			const secondaryItems = Array.from(
				rootScreen.querySelectorAll(SELECTORS.mobileSecondaryItem)
			)

			const allRootItems = [
				account,
				language,
				...catalogItems,
				...secondaryItems
			].filter(Boolean)

			/*
			 * Сначала приводим все mobile screens
			 * к предсказуемому состоянию.
			 */
			mobileMenu.querySelectorAll(SELECTORS.mobileScreen).forEach(screen => {
				const isRoot = screen === rootScreen

				screen.classList.toggle('is-active', isRoot)

				gsap.set(screen, {
					display: 'block',
					opacity: isRoot ? 1 : 0,
					xPercent: isRoot ? 0 : 100,
					visibility: isRoot ? 'visible' : 'hidden',
					pointerEvents: 'none'
				})
			})

			mobileScreenHistory = ['root']
			updateMobileHeader()

			/*
			 * Открываем внешний контейнер.
			 */
			mobileMenu.classList.add('is-open')

			mobileMenu.setAttribute('aria-hidden', 'false')

			header.querySelectorAll(SELECTORS.mobileOpen).forEach(button => {
				button.setAttribute('aria-expanded', String(button === trigger))
			})

			syncBodyLock()

			gsap.set(mobileMenu, {
				display: 'flex',
				height: varMobileHeaderHeight(),
				opacity: 1,
				visibility: 'visible',
				pointerEvents: 'auto',
				overflow: 'hidden',
				y: 0
			})

			gsap.set(mobileHeader, {
				opacity: 1,
				visibility: 'visible'
			})

			gsap.set(mobileBody, {
				display: 'block',
				opacity: 1,
				visibility: 'visible'
			})

			gsap.set(rootScreen, {
				display: 'block',
				opacity: 1,
				xPercent: 0,
				visibility: 'visible',
				pointerEvents: 'none'
			})

			/*
			 * Важно: очищаем старые inline transforms,
			 * которые могли остаться после переходов.
			 */
			gsap.set(allRootItems, {
				clearProps: 'transform,opacity'
			})

			mobileIsAnimating = true

			mobileTimeline = gsap.timeline({
				defaults: {
					overwrite: 'auto'
				},
				onComplete: () => {
					gsap.set(mobileMenu, {
						height: '100dvh',
						overflow: 'hidden'
					})

					gsap.set(rootScreen, {
						opacity: 1,
						xPercent: 0,
						visibility: 'visible',
						pointerEvents: 'auto'
					})

					gsap.set(allRootItems, {
						opacity: 1,
						y: 0,
						visibility: 'visible'
					})

					mobileIsAnimating = false
					mobileTimeline = null
				}
			})

			/*
			 * Фаза 1 — расширение белого контейнера.
			 */
			mobileTimeline.to(
				mobileMenu,
				{
					height: '100dvh',
					duration: MOBILE_OPEN_DURATION,
					ease: 'power3.inOut'
				},
				0
			)

			/*
			 * Фаза 2 — профиль.
			 */
			if (account) {
				mobileTimeline.fromTo(
					account,
					{
						opacity: 0,
						y: 8,
						visibility: 'visible'
					},
					{
						opacity: 1,
						y: 0,
						duration: 0.32,
						ease: 'power2.out',
						immediateRender: false
					},
					0.08
				)
			}

			/*
			 * Фаза 3 — переключатель языка.
			 */
			if (language) {
				mobileTimeline.fromTo(
					language,
					{
						opacity: 0,
						y: 8,
						visibility: 'visible'
					},
					{
						opacity: 1,
						y: 0,
						duration: 0.32,
						ease: 'power2.out',
						immediateRender: false
					},
					0.11
				)
			}

			/*
			 * Фаза 4 — основные категории.
			 */
			if (catalogItems.length) {
				mobileTimeline.fromTo(
					catalogItems,
					{
						opacity: 0,
						y: 8,
						visibility: 'visible'
					},
					{
						opacity: 1,
						y: 0,
						duration: 0.34,
						stagger: MOBILE_ITEM_STAGGER,
						ease: 'power2.out',
						immediateRender: false
					},
					0.14
				)
			}

			/*
			 * Фаза 5 — второстепенные ссылки.
			 */
			if (secondaryItems.length) {
				mobileTimeline.fromTo(
					secondaryItems,
					{
						opacity: 0,
						y: 8,
						visibility: 'visible'
					},
					{
						opacity: 1,
						y: 0,
						duration: 0.3,
						stagger: 0.025,
						ease: 'power2.out',
						immediateRender: false
					},
					0.22
				)
			}

			window.setTimeout(() => {
				mobileMenu.querySelector(SELECTORS.mobileClose)?.focus({
					preventScroll: true
				})
			}, 180)
		}

		/* ==========================================================
		   Click delegation
		   ========================================================== */

		header.addEventListener('click', event => {
			const target = event.target

			if (!(target instanceof Element)) {
				return
			}

			const mobileOpenButton = target.closest(SELECTORS.mobileOpen)

			if (mobileOpenButton) {
				event.preventDefault()
				openMobileMenu(mobileOpenButton)
				return
			}

			if (target.closest(SELECTORS.mobileClose)) {
				event.preventDefault()

				closeMobileMenu({
					restore: true
				})

				return
			}

			const mobileScreenButton = target.closest(SELECTORS.mobileScreenOpen)

			if (mobileScreenButton) {
				event.preventDefault()

				openMobileScreen(mobileScreenButton.dataset.mobileScreenOpen)

				return
			}

			if (target.closest(SELECTORS.mobileScreenBack)) {
				event.preventDefault()
				goBackMobileScreen()
				return
			}

			const searchOpenButton = target.closest(SELECTORS.searchOpen)

			if (searchOpenButton) {
				event.preventDefault()

				openSearch(searchOpenButton)

				return
			}

			if (target.closest(SELECTORS.searchClose)) {
				event.preventDefault()

				closeSearch({
					restore: true
				})

				return
			}

			const userToggle = target.closest(SELECTORS.userToggle)

			if (userToggle) {
				event.preventDefault()

				toggleUserPanel(userToggle.closest(SELECTORS.user))
			}
		})

		/* ==========================================================
		   Outside clicks
		   ========================================================== */

		document.addEventListener('click', event => {
			const target = event.target

			if (!(target instanceof Element)) {
				return
			}

			if (!target.closest(SELECTORS.user)) {
				closeUserPanels()
			}
		})

		overlay?.addEventListener('click', () => {
			closeMegaMenu()
		})

		/* ==========================================================
		   Keyboard
		   ========================================================== */

		document.addEventListener('keydown', event => {
			if (event.key !== 'Escape') {
				return
			}
			if (isMobileMenuOpen()) {
				event.preventDefault()

				if (mobileScreenHistory.length > 1) {
					goBackMobileScreen()
				} else {
					closeMobileMenu({
						restore: true
					})
				}

				return
			}

			if (header.classList.contains('is-mega-open')) {
				event.preventDefault()
				closeMegaMenu()
				return
			}

			closeUserPanels()
		})

		/* ==========================================================
		   Scroll
		   ========================================================== */

		const updateHeaderScroll = () => {
			const currentScrollY = Math.max(window.scrollY, 0)

			const difference = currentScrollY - previousScrollY

			header.classList.toggle('_scroll', currentScrollY > SCROLL_THRESHOLD)

			if (currentScrollY <= SCROLL_THRESHOLD) {
				header.classList.remove('is-scrolling-down', 'is-scrolling-up')

				previousScrollY = currentScrollY

				scrollTicking = false
				return
			}

			if (isMobileMenuOpen()) {
				previousScrollY = currentScrollY
				scrollTicking = false
				return
			}

			if (Math.abs(difference) < SCROLL_DIRECTION_THRESHOLD) {
				scrollTicking = false
				return
			}

			const scrollingDown = difference > 0

			header.classList.toggle('is-scrolling-down', scrollingDown)

			header.classList.toggle('is-scrolling-up', !scrollingDown)

			if (scrollingDown) {
				closeMegaMenu()
				closeUserPanels()
			}

			previousScrollY = currentScrollY

			scrollTicking = false
		}

		window.addEventListener(
			'scroll',
			() => {
				if (scrollTicking) {
					return
				}

				scrollTicking = true

				window.requestAnimationFrame(updateHeaderScroll)
			},
			{
				passive: true
			}
		)

		/* ==========================================================
		   Resize
		   ========================================================== */

		window.addEventListener('resize', () => {
			window.clearTimeout(resizeTimer)

			resizeTimer = window.setTimeout(() => {
				if (isDesktop()) {
					closeMobileMenu({
						restore: false
					})
				} else {
					closeMegaMenu({
						immediate: true
					})

					closeUserPanels()
				}

				previousScrollY = Math.max(window.scrollY, 0)
			}, 100)
		})

		/* ==========================================================
		   Init
		   ========================================================== */

		if (mega) {
			gsap.set(mega, {
				height: 0,
				visibility: 'hidden',
				pointerEvents: 'none',
				overflow: 'hidden'
			})
		}

		if (mobileMenu) {
			gsap.set(mobileMenu, {
				height: 0,
				opacity: 0,
				visibility: 'hidden',
				pointerEvents: 'none',
				overflow: 'hidden'
			})
		}

		if (mobileMenu) {
			const rootScreen = getMobileScreen('root')

			mobileMenu.querySelectorAll(SELECTORS.mobileScreen).forEach(screen => {
				const isRoot = screen === rootScreen

				gsap.set(screen, {
					display: 'block',
					opacity: isRoot ? 1 : 0,
					xPercent: isRoot ? 0 : 100,
					visibility: isRoot ? 'visible' : 'hidden',
					pointerEvents: 'none'
				})
			})

			if (rootScreen) {
				gsap.set(getMobileScreenItems(rootScreen), {
					opacity: 1,
					y: 0,
					visibility: 'visible'
				})
			}
		}

		megaPanels.forEach(panel => {
			gsap.set(panel, {
				display: 'none',
				visibility: 'hidden',
				pointerEvents: 'none',
				opacity: 0
			})
		})

		if (overlay) {
			gsap.set(overlay, {
				opacity: 0
			})
		}

		resetMobileScreens()
		initMegaMenu()
		updateHeaderScroll()
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initHeader, {
			once: true
		})
	} else {
		initHeader()
	}
})()
