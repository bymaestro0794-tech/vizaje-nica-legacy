document.addEventListener('DOMContentLoaded', function () {
	const brandsPage = document.querySelector('.brands')

	if (!brandsPage) {
		return
	}

	const searchForm = brandsPage.querySelector('.search-head-brands')
	const searchInput = brandsPage.querySelector('.search-head-brands__input')

	const brandSections = Array.from(
		brandsPage.querySelectorAll('[data-brand-section]')
	)

	const brandItems = Array.from(brandsPage.querySelectorAll('.brands__item'))

	const letterLinks = Array.from(
		brandsPage.querySelectorAll('[data-brand-letter]')
	)

	const emptyMessage = brandsPage.querySelector('.brands__empty')
	const alphabet = brandsPage.querySelector('.brand-letter')
	/**
	 * Задержка поиска после завершения печати.
	 */
	const SEARCH_DELAY = 300

	let searchTimer = null
	let isLetterNavigation = false

	/**
	 * Нормализация текста для поиска.
	 */
	function normalizeValue(value) {
		return String(value || '')
			.toLocaleLowerCase()
			.trim()
	}

	/**
	 * Удаляет active state со всех буквенных блоков.
	 */
	function clearActiveSections() {
		brandSections.forEach(function (section) {
			section.classList.remove('_active')
		})
	}

	/**
	 * Удаляет active state со всех брендов.
	 */
	function clearActiveBrands() {
		brandItems.forEach(function (item) {
			item.classList.remove('_active')
		})
	}

	/**
	 * Устанавливает active state для буквы
	 * и соответствующего блока брендов.
	 */
	function setActiveLetter(letter) {
		if (!letter) {
			return
		}

		letterLinks.forEach(function (link) {
			const isActive = link.dataset.brandLetter === letter

			link.classList.toggle('_active', isActive)
		})

		brandSections.forEach(function (section) {
			const isActive = section.dataset.brandSection === letter

			section.classList.toggle('_active', isActive)
		})
	}

	/**
	 * Показывает все бренды и буквенные секции.
	 */
	function resetBrands() {
		brandItems.forEach(function (item) {
			item.hidden = false
			item.classList.remove('_active')
		})

		brandSections.forEach(function (section) {
			section.hidden = false
			section.classList.remove('_search-active')
		})

		if (emptyMessage) {
			emptyMessage.hidden = true
		}
	}

	/**
	 * Фильтрация брендов.
	 *
	 * Запускается только после debounce,
	 * а не после каждого введённого символа.
	 */
	function filterBrands() {
		const query = normalizeValue(searchInput ? searchInput.value : '')

		let visibleBrandsCount = 0
		let firstVisibleSection = null

		clearActiveBrands()
		clearActiveSections()

		/**
		 * Когда поиск очищен, возвращаем весь список.
		 */
		if (!query) {
			resetBrands()

			if (letterLinks.length) {
				setActiveLetter(letterLinks[0].dataset.brandLetter)
			}

			return
		}

		brandSections.forEach(function (section) {
			const sectionItems = Array.from(section.querySelectorAll('.brands__item'))

			let visibleSectionItems = 0

			sectionItems.forEach(function (item) {
				const brandName = normalizeValue(
					item.dataset.brandName || item.textContent
				)

				const isVisible = brandName.indexOf(query) !== -1

				item.hidden = !isVisible
				item.classList.toggle('_active', isVisible)

				if (isVisible) {
					visibleSectionItems += 1
					visibleBrandsCount += 1
				}
			})

			const hasVisibleBrands = visibleSectionItems > 0

			section.hidden = !hasVisibleBrands
			section.classList.toggle('_search-active', hasVisibleBrands)

			if (hasVisibleBrands && !firstVisibleSection) {
				firstVisibleSection = section
			}
		})

		if (emptyMessage) {
			emptyMessage.hidden = visibleBrandsCount > 0
		}

		/**
		 * Делаем активной первую найденную буквенную секцию.
		 */
		if (firstVisibleSection) {
			setActiveLetter(firstVisibleSection.dataset.brandSection)
		} else {
			letterLinks.forEach(function (link) {
				link.classList.remove('_active')
			})
		}
	}

	/**
	 * Debounce.
	 */
	function scheduleSearch() {
		window.clearTimeout(searchTimer)

		searchTimer = window.setTimeout(function () {
			filterBrands()
		}, SEARCH_DELAY)
	}

	/**
	 * Не разрешаем форме перезагружать страницу.
	 *
	 * При Enter выполняем поиск сразу,
	 * не ожидая окончания debounce.
	 */
	if (searchForm) {
		searchForm.addEventListener('submit', function (event) {
			event.preventDefault()

			window.clearTimeout(searchTimer)
			filterBrands()
		})
	}

	/**
	 * Запускаем поиск через 300 мс
	 * после завершения ввода.
	 */
	if (searchInput) {
		searchInput.addEventListener('input', function () {
			scheduleSearch()
		})
	}

	/**
	 * Навигация по буквам.
	 */
	letterLinks.forEach(function (link) {
		link.addEventListener('click', function (event) {
			const targetSelector = link.getAttribute('href')

			const target = targetSelector
				? document.querySelector(targetSelector)
				: null

			if (!target) {
				return
			}

			event.preventDefault()

			/**
			 * При выборе буквы очищаем поиск,
			 * чтобы выбранная секция точно была видна.
			 */
			if (searchInput && searchInput.value) {
				searchInput.value = ''
				resetBrands()
			}

			window.clearTimeout(searchTimer)

			isLetterNavigation = true

			setActiveLetter(link.dataset.brandLetter)

			target.scrollIntoView({
				behavior: 'smooth',
				block: 'start'
			})

			/**
			 * Временно отключаем реакцию IntersectionObserver,
			 * пока завершается плавный scroll.
			 */
			window.setTimeout(function () {
				isLetterNavigation = false
			}, 700)
		})
	})

	/**
	 * Автоматический active state при прокрутке.
	 */
	if ('IntersectionObserver' in window) {
		const observer = new IntersectionObserver(
			function (entries) {
				if (isLetterNavigation) {
					return
				}

				const searchValue = normalizeValue(searchInput ? searchInput.value : '')

				/**
				 * Во время поиска active state уже управляется
				 * функцией filterBrands().
				 */
				if (searchValue) {
					return
				}

				const visibleEntries = entries
					.filter(function (entry) {
						return entry.isIntersecting
					})
					.sort(function (firstEntry, secondEntry) {
						return (
							Math.abs(firstEntry.boundingClientRect.top) -
							Math.abs(secondEntry.boundingClientRect.top)
						)
					})

				if (!visibleEntries.length) {
					return
				}

				const currentSection = visibleEntries[0].target

				setActiveLetter(currentSection.dataset.brandSection)
			},
			{
				rootMargin: '-145px 0px -65% 0px',
				threshold: 0
			}
		)

		brandSections.forEach(function (section) {
			observer.observe(section)
		})
	}

	/**
	 * Начальный active state.
	 */
	if (letterLinks.length) {
		setActiveLetter(letterLinks[0].dataset.brandLetter)
	}

	function updateMobileAlphabetState() {
		if (!alphabet) {
			return
		}

		if (window.innerWidth > 767) {
			alphabet.classList.remove('_mobile-sticky')
			return
		}

		const brandsTop = brandsPage.getBoundingClientRect().top

		alphabet.classList.toggle('_mobile-sticky', brandsTop <= -70)
	}

	window.addEventListener('scroll', updateMobileAlphabetState, {
		passive: true
	})

	window.addEventListener('resize', updateMobileAlphabetState)

	updateMobileAlphabetState()
})
