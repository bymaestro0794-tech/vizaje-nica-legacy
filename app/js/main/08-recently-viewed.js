;(function () {
	'use strict'

	const STORAGE_KEY = 'vn_recently_viewed'

	const MAX_HISTORY = 20

	const MAX_DISPLAY = 10

	/*
	|--------------------------------------------------------------------------
	| Storage
	|--------------------------------------------------------------------------
	*/

	function readHistory() {
		try {
			const raw = localStorage.getItem(STORAGE_KEY)

			if (!raw) {
				return []
			}

			const parsed = JSON.parse(raw)

			if (!Array.isArray(parsed)) {
				return []
			}

			const result = []

			parsed.forEach(function (id) {
				id = Number(id)

				if (!Number.isInteger(id) || id <= 0 || result.includes(id)) {
					return
				}

				result.push(id)
			})

			return result.slice(0, MAX_HISTORY)
		} catch (error) {
			console.warn('Recently viewed: storage read failed', error)

			return []
		}
	}

	function writeHistory(ids) {
		try {
			localStorage.setItem(
				STORAGE_KEY,
				JSON.stringify(ids.slice(0, MAX_HISTORY))
			)
		} catch (error) {
			console.warn('Recently viewed: storage write failed', error)
		}
	}

	function rememberProduct(productId) {
		productId = Number(productId)

		if (!Number.isInteger(productId) || productId <= 0) {
			return
		}

		let history = readHistory()

		history = history.filter(function (id) {
			return id !== productId
		})

		history.unshift(productId)

		writeHistory(history)
	}

	/*
	|--------------------------------------------------------------------------
	| Current PDP
	|--------------------------------------------------------------------------
	*/

	function getCurrentProductId() {
		const pageData = document.getElementById('product-page-data')

		if (!pageData) {
			return 0
		}

		try {
			const data = JSON.parse(pageData.textContent)

			return Number(data?.product?.id) || 0
		} catch (error) {
			return 0
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Load block
	|--------------------------------------------------------------------------
	*/

	function loadRecentlyViewed() {
		const sections = document.querySelectorAll('[data-recently-viewed]')

		if (!sections.length) {
			return
		}

		let history = readHistory()

		if (!history.length) {
			return
		}

		sections.forEach(function (section) {
			const currentProductId = Number(section.dataset.currentProductId) || 0

			let ids = history.filter(function (id) {
				return id !== currentProductId
			})

			ids = ids.slice(0, MAX_DISPLAY)

			if (!ids.length) {
				return
			}

			loadSection(section, ids)
		})
	}

	async function loadSection(section, ids) {
		const track = section.querySelector('[data-recently-viewed-track]')

		if (!track) {
			return
		}

		const body = new URLSearchParams()

		ids.forEach(function (id) {
			body.append('ids[]', String(id))
		})

		try {
			const response = await fetch('/recently_viewed', {
				method: 'POST',

				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
				},

				body: body.toString()
			})

			if (!response.ok) {
				throw new Error('HTTP ' + response.status)
			}

			const result = await response.json()

			if (
				!result ||
				result.status !== 'ok' ||
				!result.html ||
				Number(result.count) <= 0
			) {
				return
			}

			buildRecentlyViewedPages(section, track, result.html)

			section.hidden = false

			if (
				window.VNProductsSlider &&
				typeof window.VNProductsSlider.init === 'function'
			) {
				window.VNProductsSlider.init(section)
			}

			if (
				!window.VNProductsSlider ||
				typeof window.VNProductsSlider.reveal !== 'function'
			) {
				section.classList.add('is-visible')

				return
			}

			if (!('IntersectionObserver' in window)) {
				window.VNProductsSlider.reveal(section)

				return
			}

			const observer = new IntersectionObserver(
				entries => {
					if (!entries[0].isIntersecting) {
						return
					}

					window.VNProductsSlider.reveal(section)

					observer.disconnect()
				},
				{
					threshold: 0.12
				}
			)

			observer.observe(section)
		} catch (error) {
			console.error('Recently viewed: load failed', error)
		}
	}

	function buildRecentlyViewedPages(section, track, html) {
		const temp = document.createElement('div')

		temp.innerHTML = html

		const products = Array.from(temp.children)

		if (!products.length) {
			return
		}

		track.innerHTML = ''

		const itemsPerPage = getRecentlyViewedItemsPerPage()

		const pages = []

		for (let index = 0; index < products.length; index += itemsPerPage) {
			pages.push(products.slice(index, index + itemsPerPage))
		}

		pages.forEach(function (pageProducts, pageIndex) {
			const page = document.createElement('div')

			page.className = 'home-products__page'

			if (pageIndex === 0) {
				page.classList.add('is-active')
			}

			page.dataset.productsPage = ''
			page.dataset.productsPageIndex = String(pageIndex)

			page.setAttribute('aria-hidden', pageIndex === 0 ? 'false' : 'true')

			pageProducts.forEach(function (product) {
				page.appendChild(product)
			})

			track.appendChild(page)
		})

		section.dataset.currentPage = '0'
		section.dataset.pagesCount = String(pages.length)

		const controls = section.querySelector('[data-recently-viewed-controls]')

		if (controls) {
			controls.hidden = pages.length <= 1
		}

		const mobilePrevious = section.querySelector(
			'[data-recently-viewed-mobile-previous]'
		)

		const mobileNext = section.querySelector(
			'[data-recently-viewed-mobile-next]'
		)

		if (mobilePrevious) {
			mobilePrevious.hidden = products.length <= 1
		}

		if (mobileNext) {
			mobileNext.hidden = products.length <= 1
		}
	}

	function getRecentlyViewedItemsPerPage() {
		if (window.matchMedia('(max-width: 767px)').matches) {
			return 1
		}

		if (window.matchMedia('(max-width: 1100px)').matches) {
			return 2
		}

		return 4
	}

	// function initRecentlyViewedNavigation(section) {
	// 	const previous = section.querySelector('[data-recently-viewed-previous]')

	// 	const next = section.querySelector('[data-recently-viewed-next]')

	// 	const mobilePrevious = section.querySelector(
	// 		'[data-recently-viewed-mobile-previous]'
	// 	)

	// 	const mobileNext = section.querySelector(
	// 		'[data-recently-viewed-mobile-next]'
	// 	)

	// 	previous?.addEventListener('click', function () {
	// 		changeRecentlyViewedPage(section, -1)
	// 	})

	// 	next?.addEventListener('click', function () {
	// 		changeRecentlyViewedPage(section, 1)
	// 	})

	// 	mobilePrevious?.addEventListener('click', function () {
	// 		changeRecentlyViewedPage(section, -1)
	// 	})

	// 	mobileNext?.addEventListener('click', function () {
	// 		changeRecentlyViewedPage(section, 1)
	// 	})

	// 	updateRecentlyViewedControls(section)
	// }

	// function changeRecentlyViewedPage(section, direction) {
	// 	const pages = Array.from(
	// 		section.querySelectorAll('[data-recently-viewed-page]')
	// 	)

	// 	if (!pages.length) {
	// 		return
	// 	}

	// 	let current = Number(section.dataset.currentPage) || 0

	// 	const next = Math.max(0, Math.min(pages.length - 1, current + direction))

	// 	if (next === current) {
	// 		return
	// 	}

	// 	pages[current]?.classList.remove('is-active')

	// 	pages[current]?.setAttribute('aria-hidden', 'true')

	// 	pages[next]?.classList.add('is-active')

	// 	pages[next]?.setAttribute('aria-hidden', 'false')

	// 	section.dataset.currentPage = String(next)

	// 	updateRecentlyViewedControls(section)
	// }

	// function updateRecentlyViewedControls(section) {
	// 	const current = Number(section.dataset.currentPage) || 0

	// 	const pagesCount = Number(section.dataset.pagesCount) || 1

	// 	const atStart = current <= 0

	// 	const atEnd = current >= pagesCount - 1

	// 	const previous = section.querySelector('[data-recently-viewed-previous]')

	// 	const next = section.querySelector('[data-recently-viewed-next]')

	// 	const mobilePrevious = section.querySelector(
	// 		'[data-recently-viewed-mobile-previous]'
	// 	)

	// 	const mobileNext = section.querySelector(
	// 		'[data-recently-viewed-mobile-next]'
	// 	)

	// 	if (previous) {
	// 		previous.disabled = atStart
	// 	}

	// 	if (next) {
	// 		next.disabled = atEnd
	// 	}

	// 	if (mobilePrevious) {
	// 		mobilePrevious.disabled = atStart
	// 	}

	// 	if (mobileNext) {
	// 		mobileNext.disabled = atEnd
	// 	}
	// }

	/*
	|--------------------------------------------------------------------------
	| Init
	|--------------------------------------------------------------------------
	*/

	function init() {
		const currentProductId = getCurrentProductId()

		/*
		 * Сначала загружаем историю
		 * БЕЗ текущего товара.
		 *
		 * И только после этого
		 * запоминаем текущий PDP.
		 *
		 * Поэтому товар никогда
		 * не рекомендует сам себя.
		 */
		loadRecentlyViewed()

		if (currentProductId > 0) {
			rememberProduct(currentProductId)
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init, {
			once: true
		})
	} else {
		init()
	}
})()
