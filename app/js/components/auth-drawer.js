/**
 * Vizaje-Nica Auth Drawer
 *
 * Возможности:
 * - открытие и закрытие drawer;
 * - GSAP-анимация панели и экранов;
 * - AJAX-вход;
 * - AJAX-регистрация;
 * - подтверждение SMS-кода;
 * - восстановление пароля;
 * - вывод ошибок backend;
 * - loading-состояния кнопок;
 * - показ и скрытие пароля;
 * - перенос телефона в SMS-экран;
 * - управление шестью полями SMS-кода;
 * - Escape, overlay, focus restore;
 * - блокировка прокрутки страницы.
 */

;(function () {
	'use strict'

	const SELECTORS = {
		drawer: '[data-auth-drawer]',
		panel: '.auth-drawer__panel',
		overlay: '.auth-drawer__overlay',

		trigger: '[data-auth-trigger]',
		close: '[data-auth-close]',
		screen: '[data-auth-screen]',
		screenOpen: '[data-auth-open]',

		formLogin: '.login_form',
		formRegister: '.register_form',
		formCode: '.login_formcode',
		formReset: '.reset_form',

		error: '.auth-drawer__message--error',
		success: '.auth-drawer__message--success',

		passwordToggle: '[data-password-toggle]',

		codeBlock: '[data-auth-code]',
		codeInput: '.auth-code__input',
		codeValue: '[data-auth-code-value]',
		codePhone: '[data-auth-code-phone]',
		codePhonePreview: '[data-auth-phone-preview]',
		codeTimer: '[data-auth-code-timer]',
		codeResend: '[data-auth-code-resend]',

		primaryButton: '.auth-drawer__primary-button',
		phoneInput: '[data-auth-phone-input]'
	}

	const SCREEN_NAMES = {
		login: 'login',
		register: 'register',
		forgot: 'forgot',
		forgotSent: 'forgot-sent',
		code: 'sms-code',
		registerSuccess: 'register-success'
	}

	const DRAWER_OPEN_DURATION = 0.58
	const DRAWER_CLOSE_DURATION = 0.4

	const SCREEN_OUT_DURATION = 0.2
	const SCREEN_IN_DURATION = 0.36

	const SCREEN_ITEM_DURATION = 0.42
	const SCREEN_ITEM_STAGGER = 0.035

	const CODE_LENGTH = 6

	function initAuthDrawer() {
		const drawer = document.querySelector(SELECTORS.drawer)

		if (!drawer) {
			return
		}

		if (drawer.dataset.authInitialized === 'true') {
			return
		}

		drawer.dataset.authInitialized = 'true'

		const panel = drawer.querySelector(SELECTORS.panel)

		const overlay = drawer.querySelector(SELECTORS.overlay)

		const screens = Array.from(drawer.querySelectorAll(SELECTORS.screen))

		if (!panel || !screens.length) {
			console.warn('Auth drawer: required elements not found.')

			return
		}

		const gsapAvailable = typeof window.gsap !== 'undefined'

		const gsap = gsapAvailable ? window.gsap : null

		let activeScreen =
			drawer.querySelector(`${SELECTORS.screen}.is-active`) || screens[0]

		let lastTrigger = null
		let isDrawerAnimating = false
		let isScreenAnimating = false

		let authIntent = null
		let authIntentUrl = null

		/* ==========================================================
		   Helpers
		   ========================================================== */

		const isOpen = () => {
			return drawer.classList.contains('is-open')
		}
		const clearAuthIntent = () => {
			authIntent = null
			authIntentUrl = null
		}

		const completeAuthIntent = () => {
			if (authIntent === 'checkout' && authIntentUrl) {
				const url = authIntentUrl

				clearAuthIntent()

				window.location.href = url

				return true
			}

			return false
		}

		const getScreen = screenName => {
			if (!screenName) {
				return null
			}

			return drawer.querySelector(`[data-auth-screen="${screenName}"]`) || null
		}

		const getScreenItems = screen => {
			if (!screen) {
				return []
			}

			return Array.from(
				screen.querySelectorAll(`
					.auth-drawer__back,
					.auth-drawer__status-icon,
					.auth-drawer__header,
					.auth-drawer__message,
					.auth-drawer__field,
					.auth-drawer__form-meta,
					.auth-drawer__checks,
					.auth-code,
					.auth-drawer__primary-button,
					.auth-drawer__secondary-button,
					.auth-drawer__text-link,
					.auth-drawer__switch,
					.auth-drawer__status-link
				`)
			)
		}

		const setScreenAccessibility = (screen, isActive) => {
			if (!screen) {
				return
			}

			screen.setAttribute('aria-hidden', String(!isActive))

			const focusableElements = screen.querySelectorAll(
				['a[href]', 'button', 'input', 'select', 'textarea', '[tabindex]'].join(
					','
				)
			)

			focusableElements.forEach(element => {
				if (!(element instanceof HTMLElement)) {
					return
				}

				if (isActive) {
					if (element.dataset.authPreviousTabindex !== undefined) {
						const previousValue = element.dataset.authPreviousTabindex

						if (previousValue === '') {
							element.removeAttribute('tabindex')
						} else {
							element.setAttribute('tabindex', previousValue)
						}

						delete element.dataset.authPreviousTabindex
					}

					return
				}

				if (element.dataset.authPreviousTabindex === undefined) {
					element.dataset.authPreviousTabindex =
						element.getAttribute('tabindex') || ''
				}

				element.setAttribute('tabindex', '-1')
			})
		}

		const syncScreens = active => {
			screens.forEach(screen => {
				const isActive = screen === active

				screen.classList.toggle('is-active', isActive)

				setScreenAccessibility(screen, isActive)
			})
		}

		const focusFirstElement = screen => {
			if (!screen) {
				return
			}

			const element = screen.querySelector(
				[
					'input:not([type="hidden"]):not([disabled])',
					'button:not([disabled])',
					'a[href]'
				].join(',')
			)

			if (element instanceof HTMLElement) {
				window.setTimeout(() => {
					element.focus({
						preventScroll: true
					})
				}, 80)
			}
		}

		const saveFocus = trigger => {
			if (trigger instanceof HTMLElement) {
				lastTrigger = trigger
				return
			}

			if (document.activeElement instanceof HTMLElement) {
				lastTrigger = document.activeElement
			}
		}

		const restoreFocus = () => {
			if (
				lastTrigger instanceof HTMLElement &&
				document.contains(lastTrigger)
			) {
				lastTrigger.focus({
					preventScroll: true
				})
			}

			lastTrigger = null
		}

		const clearMessages = container => {
			if (!container) {
				return
			}

			container
				.querySelectorAll(`${SELECTORS.error}, ${SELECTORS.success}`)
				.forEach(message => {
					message.textContent = ''
					message.classList.remove('is-visible')
				})
		}

		const showMessage = (form, type, text) => {
			if (!form || !text) {
				return
			}

			const selector = type === 'success' ? SELECTORS.success : SELECTORS.error

			const message = form.querySelector(selector)

			if (!message) {
				return
			}

			message.textContent = String(text)
			message.classList.add('is-visible')

			if (gsapAvailable) {
				gsap.killTweensOf(message)

				gsap.fromTo(
					message,
					{
						opacity: 0,
						y: -5
					},
					{
						opacity: 1,
						y: 0,
						duration: 0.28,
						ease: 'power2.out',
						clearProps: 'transform,opacity'
					}
				)
			}
		}

		const normalizePhone = value => {
			return String(value || '')
				.replace(/\D/g, '')
				.slice(-8)
		}

		const formatPhone = value => {
			const digits = normalizePhone(value)

			if (digits.length !== 8) {
				return digits ? `+373 ${digits}` : '+373'
			}

			return [
				'+373',
				digits.slice(0, 2),
				digits.slice(2, 5),
				digits.slice(5, 8)
			].join(' ')
		}

		const formatLocalPhoneInput = value => {
			const digits = String(value || '')
				.replace(/\D/g, '')
				.slice(0, 8)

			const parts = []

			if (digits.length > 0) {
				parts.push(digits.slice(0, 2))
			}

			if (digits.length > 2) {
				parts.push(digits.slice(2, 5))
			}

			if (digits.length > 5) {
				parts.push(digits.slice(5, 8))
			}

			return parts.join(' ')
		}

		const initializePhoneInputs = () => {
			drawer.querySelectorAll(SELECTORS.phoneInput).forEach(input => {
				if (!(input instanceof HTMLInputElement)) {
					return
				}

				input.addEventListener('input', () => {
					const formatted = formatLocalPhoneInput(input.value)

					input.value = formatted
				})

				input.addEventListener('paste', event => {
					event.preventDefault()

					const pastedValue = event.clipboardData?.getData('text') || ''

					input.value = formatLocalPhoneInput(pastedValue)

					input.dispatchEvent(
						new Event('input', {
							bubbles: true
						})
					)
				})
			})
		}

		const setButtonLoading = (button, isLoading) => {
			if (!(button instanceof HTMLButtonElement)) {
				return
			}

			if (isLoading) {
				if (button.dataset.originalText === undefined) {
					button.dataset.originalText = button.textContent.trim()
				}

				button.disabled = true
				button.classList.add('is-loading')
				button.setAttribute('aria-busy', 'true')

				return
			}

			button.disabled = false
			button.classList.remove('is-loading')
			button.removeAttribute('aria-busy')

			if (button.dataset.originalText !== undefined) {
				button.textContent = button.dataset.originalText

				delete button.dataset.originalText
			}
		}

		const parseJsonResponse = async response => {
			const text = await response.text()

			let data = null

			try {
				data = JSON.parse(text)
			} catch (error) {
				throw new Error('Сервер вернул некорректный ответ.')
			}

			if (!response.ok) {
				throw new Error(data?.error || `HTTP ${response.status}`)
			}

			return data
		}

		const submitForm = async form => {
			const controller = new AbortController()

			const timeoutId = window.setTimeout(() => {
				controller.abort()
			}, 20000)

			try {
				const response = await fetch(form.action, {
					method: 'POST',
					body: new FormData(form),

					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						Accept: 'application/json'
					},

					credentials: 'same-origin',
					redirect: 'follow',
					signal: controller.signal
				})

				return await parseJsonResponse(response)
			} catch (error) {
				if (error instanceof DOMException && error.name === 'AbortError') {
					throw new Error('Сервер слишком долго отвечает. Попробуйте ещё раз.')
				}

				throw error
			} finally {
				window.clearTimeout(timeoutId)
			}
		}

		/* ==========================================================
		   Screen animation
		   ========================================================== */

		const animateScreenItems = screen => {
			if (!screen || !gsapAvailable) {
				return
			}

			const items = getScreenItems(screen)

			if (!items.length) {
				return
			}

			gsap.killTweensOf(items)

			gsap.fromTo(
				items,
				{
					opacity: 0,
					y: 14
				},
				{
					opacity: 1,
					y: 0,
					duration: SCREEN_ITEM_DURATION,
					stagger: SCREEN_ITEM_STAGGER,
					ease: 'power3.out',
					clearProps: 'transform,opacity'
				}
			)
		}

		const showScreen = (screenName, options = {}) => {
			const nextScreen = getScreen(screenName)

			if (!nextScreen) {
				console.warn(`Auth drawer: screen "${screenName}" not found.`)

				return
			}

			const direction = options.direction === -1 ? -1 : 1

			const shouldFocus = options.focus !== false

			const previousScreen = activeScreen

			if (nextScreen === previousScreen) {
				if (shouldFocus) {
					focusFirstElement(nextScreen)
				}

				return
			}

			if (isScreenAnimating) {
				return
			}

			isScreenAnimating = true

			clearMessages(nextScreen)

			/*
			 * При переходе на экран сбрасываем его прокрутку.
			 */
			nextScreen.scrollTop = 0

			if (!previousScreen || !gsapAvailable) {
				activeScreen = nextScreen
				syncScreens(nextScreen)

				isScreenAnimating = false

				if (shouldFocus) {
					focusFirstElement(nextScreen)
				}

				return
			}

			const previousItems = getScreenItems(previousScreen)

			const nextItems = getScreenItems(nextScreen)

			gsap.killTweensOf([
				previousScreen,
				nextScreen,
				...previousItems,
				...nextItems
			])

			/*
			 * Сначала подготавливаем следующий экран,
			 * пока он ещё не отображён пользователю.
			 */
			gsap.set(nextScreen, {
				display: 'block',
				visibility: 'hidden',
				pointerEvents: 'none',
				opacity: 0,
				xPercent: direction > 0 ? 8 : -8
			})

			if (nextItems.length) {
				gsap.set(nextItems, {
					opacity: 0,
					y: 10
				})
			}

			/*
			 * Только после подготовки делаем экран активным.
			 */
			nextScreen.classList.add('is-active')

			setScreenAccessibility(nextScreen, true)

			gsap.set(nextScreen, {
				visibility: 'visible'
			})

			const timeline = gsap.timeline({
				defaults: {
					overwrite: true
				},

				onComplete: () => {
					previousScreen.classList.remove('is-active')

					setScreenAccessibility(previousScreen, false)

					gsap.set(previousScreen, {
						display: 'none',
						visibility: 'hidden',
						pointerEvents: 'none',
						opacity: 0,
						xPercent: 0
					})

					if (previousItems.length) {
						gsap.set(previousItems, {
							clearProps: 'transform,opacity'
						})
					}

					gsap.set(nextScreen, {
						display: 'block',
						visibility: 'visible',
						pointerEvents: 'auto',
						opacity: 1,
						xPercent: 0
					})

					if (nextItems.length) {
						gsap.set(nextItems, {
							clearProps: 'transform,opacity'
						})
					}

					activeScreen = nextScreen
					isScreenAnimating = false

					if (shouldFocus) {
						focusFirstElement(nextScreen)
					}
				}
			})

			/*
			 * Старый экран уходит.
			 */
			timeline.to(
				previousScreen,
				{
					opacity: 0,
					xPercent: direction > 0 ? -6 : 6,
					duration: 0.22,
					ease: 'power2.in'
				},
				0
			)

			if (previousItems.length) {
				timeline.to(
					previousItems,
					{
						opacity: 0,
						y: -5,
						duration: 0.16,
						stagger: {
							each: 0.008,
							from: 'end'
						},
						ease: 'power1.in'
					},
					0
				)
			}

			/*
			 * Новый экран начинает появляться ещё до того,
			 * как старый исчез полностью.
			 */
			timeline.to(
				nextScreen,
				{
					opacity: 1,
					xPercent: 0,
					duration: 0.38,
					ease: 'power3.out'
				},
				0.12
			)

			if (nextItems.length) {
				timeline.to(
					nextItems,
					{
						opacity: 1,
						y: 0,
						duration: 0.36,
						stagger: SCREEN_ITEM_STAGGER,
						ease: 'power3.out'
					},
					0.16
				)
			}
		}

		/* ==========================================================
		   Drawer open / close
		   ========================================================== */

		const openDrawer = (screenName = SCREEN_NAMES.login, trigger = null) => {
			saveFocus(trigger)

			const targetScreen =
				getScreen(screenName) || getScreen(SCREEN_NAMES.login)

			if (!targetScreen) {
				return
			}

			if (isOpen()) {
				showScreen(screenName)
				return
			}

			if (isDrawerAnimating) {
				return
			}

			isDrawerAnimating = true

			activeScreen = targetScreen
			syncScreens(targetScreen)
			clearMessages(drawer)

			document.documentElement.classList.add('auth-drawer-lock')

			if (!gsapAvailable) {
				drawer.classList.add('is-open')
				drawer.setAttribute('aria-hidden', 'false')

				isDrawerAnimating = false
				focusFirstElement(targetScreen)

				return
			}

			gsap.killTweensOf([drawer, panel, overlay])

			const screenItems = getScreenItems(targetScreen)

			/*
			 * Сначала полностью подготавливаем скрытое состояние.
			 * Drawer ещё не имеет класса is-open.
			 */
			gsap.set(drawer, {
				visibility: 'visible',
				pointerEvents: 'auto'
			})

			gsap.set(panel, {
				xPercent: -100
			})

			if (overlay) {
				gsap.set(overlay, {
					opacity: 0
				})
			}

			/*
			 * Также заранее скрываем содержимое,
			 * чтобы оно не мелькало до появления панели.
			 */
			if (screenItems.length) {
				gsap.set(screenItems, {
					opacity: 0,
					y: 14
				})
			}

			/*
			 * Только после подготовки разрешаем
			 * визуальное открытие компонента.
			 */
			drawer.classList.add('is-open')

			drawer.setAttribute('aria-hidden', 'false')

			const timeline = gsap.timeline({
				defaults: {
					overwrite: true
				},
				onComplete: () => {
					isDrawerAnimating = false

					gsap.set(panel, {
						xPercent: 0
					})

					if (screenItems.length) {
						gsap.set(screenItems, {
							clearProps: 'transform,opacity'
						})
					}

					focusFirstElement(targetScreen)
				}
			})

			if (overlay) {
				timeline.to(
					overlay,
					{
						opacity: 1,
						duration: 0.34,
						ease: 'power2.out'
					},
					0
				)
			}

			timeline.to(
				panel,
				{
					xPercent: 0,
					duration: DRAWER_OPEN_DURATION,
					ease: 'power4.out'
				},
				0
			)

			if (screenItems.length) {
				timeline.to(
					screenItems,
					{
						opacity: 1,
						y: 0,
						duration: 0.4,
						stagger: SCREEN_ITEM_STAGGER,
						ease: 'power3.out'
					},
					0.16
				)
			}
		}

		const closeDrawer = options => {
			const restore = options?.restore !== false

			if (!isOpen() || isDrawerAnimating) {
				return
			}

			isDrawerAnimating = true

			const completeClose = () => {
				drawer.classList.remove('is-open')

				drawer.setAttribute('aria-hidden', 'true')

				document.documentElement.classList.remove('auth-drawer-lock')

				if (gsapAvailable) {
					gsap.set(drawer, {
						visibility: 'hidden',
						pointerEvents: 'none'
					})
				}

				isDrawerAnimating = false

				if (restore) {
					restoreFocus()
				}
			}

			if (!gsapAvailable) {
				completeClose()
				return
			}

			gsap.killTweensOf([panel, overlay])

			const timeline = gsap.timeline({
				defaults: {
					overwrite: true
				},
				onComplete: completeClose
			})

			timeline.to(
				panel,
				{
					xPercent: -100,
					duration: DRAWER_CLOSE_DURATION,
					ease: 'power3.in'
				},
				0
			)

			if (overlay) {
				timeline.to(
					overlay,
					{
						opacity: 0,
						duration: 0.25,
						ease: 'power2.in'
					},
					0.12
				)
			}
		}

		/* ==========================================================
		   Password visibility
		   ========================================================== */

		const togglePassword = button => {
			if (!(button instanceof HTMLButtonElement)) {
				return
			}

			const inputId = button.getAttribute('aria-controls')

			const input = inputId
				? document.getElementById(inputId)
				: button.closest('.auth-password')?.querySelector('input')

			if (!(input instanceof HTMLInputElement)) {
				return
			}

			const shouldShow = input.type === 'password'

			input.type = shouldShow ? 'text' : 'password'

			button.setAttribute('aria-pressed', String(shouldShow))

			button.classList.toggle('is-active', shouldShow)

			button.setAttribute(
				'aria-label',
				shouldShow ? 'Скрыть пароль' : 'Показать пароль'
			)
		}

		/* ==========================================================
		   SMS code
		   ========================================================== */

		const syncCodeValue = codeBlock => {
			if (!codeBlock) {
				return ''
			}

			const inputs = Array.from(codeBlock.querySelectorAll(SELECTORS.codeInput))

			const hiddenInput = codeBlock.querySelector(SELECTORS.codeValue)

			const code = inputs
				.map(input => input.value)
				.join('')
				.slice(0, CODE_LENGTH)

			if (hiddenInput instanceof HTMLInputElement) {
				hiddenInput.value = code
			}

			return code
		}

		const clearCode = () => {
			drawer.querySelectorAll(SELECTORS.codeBlock).forEach(codeBlock => {
				codeBlock.querySelectorAll(SELECTORS.codeInput).forEach(input => {
					input.value = ''
				})

				syncCodeValue(codeBlock)
			})
		}

		const setCodePhone = number => {
			const normalized = normalizePhone(number)

			drawer.querySelectorAll(SELECTORS.codePhone).forEach(input => {
				if (input instanceof HTMLInputElement) {
					input.value = normalized
				}
			})

			drawer.querySelectorAll(SELECTORS.codePhonePreview).forEach(element => {
				element.textContent = formatPhone(normalized)
			})
		}

		const initializeCodeInputs = () => {
			drawer.querySelectorAll(SELECTORS.codeBlock).forEach(codeBlock => {
				const inputs = Array.from(
					codeBlock.querySelectorAll(SELECTORS.codeInput)
				)

				inputs.forEach((input, index) => {
					input.addEventListener('input', () => {
						input.value = input.value.replace(/\D/g, '').slice(0, 1)

						syncCodeValue(codeBlock)

						if (input.value && inputs[index + 1]) {
							inputs[index + 1].focus()
						}
					})

					input.addEventListener('keydown', event => {
						if (
							event.key === 'Backspace' &&
							!input.value &&
							inputs[index - 1]
						) {
							inputs[index - 1].focus()
						}

						if (event.key === 'ArrowLeft' && inputs[index - 1]) {
							event.preventDefault()

							inputs[index - 1].focus()
						}

						if (event.key === 'ArrowRight' && inputs[index + 1]) {
							event.preventDefault()

							inputs[index + 1].focus()
						}
					})

					input.addEventListener('paste', event => {
						event.preventDefault()

						const pasted =
							event.clipboardData
								?.getData('text')
								.replace(/\D/g, '')
								.slice(0, CODE_LENGTH) || ''

						pasted.split('').forEach((digit, digitIndex) => {
							if (inputs[digitIndex]) {
								inputs[digitIndex].value = digit
							}
						})

						syncCodeValue(codeBlock)

						const nextIndex = Math.min(pasted.length, inputs.length - 1)

						inputs[nextIndex]?.focus()
					})
				})
			})
		}

		/* ==========================================================
		   Form validation
		   ========================================================== */

		const validateForm = form => {
			if (!form) {
				return false
			}

			if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
				form.reportValidity()
				return false
			}

			return true
		}

		const validateCodeForm = form => {
			const codeBlock = form.querySelector(SELECTORS.codeBlock)

			const code = syncCodeValue(codeBlock)

			if (code.length !== CODE_LENGTH) {
				showMessage(form, 'error', 'Введите полный код из SMS.')

				codeBlock?.querySelector(SELECTORS.codeInput)?.focus()

				return false
			}

			return true
		}

		const validatePhoneInput = form => {
			const input = form.querySelector(SELECTORS.phoneInput)

			if (!(input instanceof HTMLInputElement)) {
				return true
			}

			const phone = normalizePhone(input.value)

			if (phone.length !== 8) {
				showMessage(form, 'error', 'Введите корректный номер телефона.')

				input.focus()

				return false
			}

			return true
		}

		/* ==========================================================
		   AJAX handlers
		   ========================================================== */

		const handleLogin = async form => {
			clearMessages(form)

			if (!validateForm(form)) {
				return
			}

			if (!validatePhoneInput(form)) {
				return
			}

			const button = form.querySelector(SELECTORS.primaryButton)

			setButtonLoading(button, true)

			try {
				const response = await submitForm(form)

				if (response?.success) {
					if (completeAuthIntent()) {
						return
					}

					window.location.reload()

					return
				}

				if (response?.code_auth) {
					setCodePhone(response.number)

					clearCode()

					showScreen(SCREEN_NAMES.code)

					return
				}

				showMessage(
					form,
					'error',
					response?.error || 'Не удалось выполнить вход.'
				)
			} catch (error) {
				showMessage(
					form,
					'error',
					error.message || 'Ошибка соединения с сервером.'
				)
			} finally {
				setButtonLoading(button, false)
			}
		}

		const handleRegister = async form => {
			clearMessages(form)

			if (!validateForm(form)) {
				return
			}

			if (!validatePhoneInput(form)) {
				return
			}

			const password = form.querySelector('[name="password"]')

			const passwordCheck = form.querySelector('[name="password_check"]')

			if (
				password instanceof HTMLInputElement &&
				passwordCheck instanceof HTMLInputElement &&
				password.value !== passwordCheck.value
			) {
				showMessage(form, 'error', 'Пароли не совпадают.')

				passwordCheck.focus()
				return
			}

			const button = form.querySelector(SELECTORS.primaryButton)

			setButtonLoading(button, true)

			try {
				const response = await submitForm(form)

				if (response?.code_auth) {
					setCodePhone(response.number)

					clearCode()

					showScreen(SCREEN_NAMES.code)

					return
				}

				showMessage(
					form,
					'error',
					response?.error || 'Не удалось зарегистрироваться.'
				)
			} catch (error) {
				showMessage(
					form,
					'error',
					error.message || 'Ошибка соединения с сервером.'
				)
			} finally {
				setButtonLoading(button, false)
			}
		}

		const handleCode = async form => {
			clearMessages(form)

			if (!validateCodeForm(form)) {
				return
			}

			const button = form.querySelector(SELECTORS.primaryButton)

			setButtonLoading(button, true)

			try {
				const response = await submitForm(form)

				if (response?.success) {
					showScreen(SCREEN_NAMES.registerSuccess)

					return
				}

				showMessage(
					form,
					'error',
					response?.error || 'Неверный код подтверждения.'
				)
			} catch (error) {
				showMessage(
					form,
					'error',
					error.message || 'Ошибка соединения с сервером.'
				)
			} finally {
				setButtonLoading(button, false)
			}
		}

		const handleReset = async form => {
			clearMessages(form)

			if (!validateForm(form)) {
				return
			}

			const button = form.querySelector(SELECTORS.primaryButton)

			setButtonLoading(button, true)

			try {
				const response = await submitForm(form)

				if (response?.success) {
					showScreen(SCREEN_NAMES.forgotSent)

					return
				}

				showMessage(
					form,
					'error',
					response?.error || 'Не удалось отправить письмо.'
				)
			} catch (error) {
				showMessage(
					form,
					'error',
					error.message || 'Ошибка соединения с сервером.'
				)
			} finally {
				setButtonLoading(button, false)
			}
		}

		/* ==========================================================
		   Submit delegation
		   ========================================================== */

		drawer.addEventListener('submit', event => {
			const form = event.target

			if (!(form instanceof HTMLFormElement)) {
				return
			}

			if (form.matches(SELECTORS.formLogin)) {
				event.preventDefault()
				handleLogin(form)
				return
			}

			if (form.matches(SELECTORS.formRegister)) {
				event.preventDefault()
				handleRegister(form)
				return
			}

			if (form.matches(SELECTORS.formCode)) {
				event.preventDefault()
				handleCode(form)
				return
			}

			if (form.matches(SELECTORS.formReset)) {
				event.preventDefault()
				handleReset(form)
			}
		})

		/* ==========================================================
		   Click delegation
		   ========================================================== */

		document.addEventListener('click', event => {
			const target = event.target

			if (!(target instanceof Element)) {
				return
			}

			const trigger = target.closest(SELECTORS.trigger)

			if (trigger) {
				event.preventDefault()

				clearAuthIntent()

				const screenName =
					trigger.getAttribute('data-auth-trigger') || SCREEN_NAMES.login

				openDrawer(screenName, trigger)

				return
			}

			if (!drawer.contains(target)) {
				return
			}

			const closeButton = target.closest(SELECTORS.close)

			if (closeButton) {
				event.preventDefault()

				closeDrawer({
					restore: true
				})

				return
			}

			const screenButton = target.closest(SELECTORS.screenOpen)

			if (screenButton) {
				event.preventDefault()

				const screenName = screenButton.getAttribute('data-auth-open')

				const direction = screenButton.hasAttribute('data-auth-back') ? -1 : 1

				showScreen(screenName, {
					direction
				})

				return
			}

			const guestCheckoutButton = target.closest('[data-auth-guest-checkout]')

			if (guestCheckoutButton) {
				event.preventDefault()

				if (authIntent === 'checkout' && authIntentUrl) {
					completeAuthIntent()

					return
				}

				closeDrawer({
					restore: true
				})

				return
			}

			const passwordButton = target.closest(SELECTORS.passwordToggle)

			if (passwordButton) {
				event.preventDefault()
				togglePassword(passwordButton)
			}
		})

		/* ==========================================================
		   Success screen
		   ========================================================== */

		drawer.addEventListener('click', event => {
			const target = event.target

			if (!(target instanceof Element)) {
				return
			}

			const successButton = target.closest('[data-auth-success-shopping]')

			if (!successButton) {
				return
			}

			event.preventDefault()

			if (completeAuthIntent()) {
				return
			}

			window.location.reload()
		})

		/* ==========================================================
		   Keyboard
		   ========================================================== */

		document.addEventListener('keydown', event => {
			if (event.key !== 'Escape' || !isOpen()) {
				return
			}

			event.preventDefault()

			closeDrawer({
				restore: true
			})
		})
		
		/* ==========================================================
		   reset password
		   ========================================================== */

		document.addEventListener('click', event => {
			const button = event.target.closest('[data-reset-password-toggle]')

			if (!button) {
				return
			}

			const inputId = button.getAttribute('aria-controls')

			const input = document.getElementById(inputId)

			if (!(input instanceof HTMLInputElement)) {
				return
			}

			const show = input.type === 'password'

			input.type = show ? 'text' : 'password'

			button.classList.toggle('is-active', show)
		})

		/* ==========================================================
		   Init states
		   ========================================================== */

		syncScreens(activeScreen)

		screens.forEach(screen => {
			if (screen === activeScreen) {
				return
			}

			if (gsapAvailable) {
				gsap.set(screen, {
					display: 'none',
					visibility: 'hidden',
					pointerEvents: 'none',
					opacity: 0
				})
			}
		})

		if (gsapAvailable) {
			gsap.set(drawer, {
				visibility: 'hidden',
				pointerEvents: 'none'
			})

			gsap.set(panel, {
				xPercent: -100
			})

			if (overlay) {
				gsap.set(overlay, {
					opacity: 0
				})
			}
		}

		initializeCodeInputs()
		initializePhoneInputs()

		window.authDrawer = {
			open: (screenName = SCREEN_NAMES.login) => {
				clearAuthIntent()

				openDrawer(screenName)
			},

			openForCheckout: checkoutUrl => {
				if (!checkoutUrl) {
					return
				}

				authIntent = 'checkout'
				authIntentUrl = checkoutUrl

				openDrawer(SCREEN_NAMES.login)
			},

			close: () => {
				clearAuthIntent()

				closeDrawer({
					restore: false
				})
			},

			show: (screenName, options = {}) => {
				if (!isOpen()) {
					openDrawer(screenName)
					return
				}

				showScreen(screenName, options)
			},

			setPhone: setCodePhone
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAuthDrawer, {
			once: true
		})
	} else {
		initAuthDrawer()
	}
})()
