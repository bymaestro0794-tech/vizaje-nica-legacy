function setTabHash(tabItem) {
	if (!tabItem) {
		return
	}

	const tabUrl = tabItem.getAttribute('data-url')
	if (!tabUrl) {
		return
	}

	const cleanQuery = tabUrl.trim().replace(/^[?#]/, '')
	if (!cleanQuery) {
		return
	}

	history.replaceState(null, '', '?' + cleanQuery)

	const languageItems = document.querySelectorAll('.language-main-header__item')
	if (!languageItems.length) {
		return
	}

	for (let index = 0; index < languageItems.length; index++) {
		const languageItem = languageItems[index]
		const languageLink = languageItem.getAttribute('data-link')

		if (!languageLink) {
			continue
		}

		const baseLanguageLink = languageLink.split('?')[0]
		languageItem.setAttribute('data-link', baseLanguageLink + '?' + cleanQuery)
	}
}

window.onload = function () {
	document.addEventListener('click', documentActions)
	document.body.style.opacity = '1'

	if (window.matchMedia('(hover: hover)').matches) {
		const catalogItem = document.querySelector(
			'.navigation-header__item._catalog'
		)

		if (catalogItem) {
			const catalogHeaderLink = catalogItem.querySelector(
				'.navigation-header__name'
			)

			const catalogHeaderCatalog = document.querySelector(
				'.navigation-header__catalog'
			)

			const body = document.body

			if (catalogHeaderLink && catalogHeaderCatalog) {
				catalogItem.addEventListener('mouseenter', () => {
					if (!catalogHeaderLink.classList.contains('_active')) {
						catalogHeaderLink.classList.add('_active')

						_slideDown(catalogHeaderCatalog, 300)

						body.classList.add('_catalog-open')
					}
				})

				catalogItem.addEventListener('mouseleave', () => {
					if (catalogHeaderLink.classList.contains('_active')) {
						catalogHeaderLink.classList.remove('_active')

						_slideUp(catalogHeaderCatalog, 300)

						body.classList.remove('_catalog-open')
					}
				})
			}
		}
	}

	function documentActions(e) {
		const targetElement = e.target
		let body = document.querySelector('body')

		if (
			targetElement.closest(
				'.navigation-header__item._catalog .navigation-header__name'
			)
		) {
			if (window.matchMedia('(max-width: 991px)').matches) {
				let catalogHeaderLink = targetElement.closest(
					'.navigation-header__item._catalog .navigation-header__name'
				)
				let catalogHeaderCatalog = document.querySelector(
					'.navigation-header__catalog'
				)
				let body = document.querySelector('body')
				if (spollersGo) {
					spollersGo = false
					catalogHeaderLink.classList.toggle('_active')
					_slideToggle(catalogHeaderCatalog, 300)
					body.classList.toggle('_catalog-open')
					e.preventDefault()
					setTimeout(function () {
						spollersGo = true
					}, 500)
				}
			}
		} else if (
			!targetElement.closest('.navigation-header__catalog') &&
			document.querySelector(
				'.navigation-header__item._catalog .navigation-header__name._active'
			)
		) {
			if (window.matchMedia('(max-width: 991px)').matches) {
				let catalogHeaderLink = document.querySelector(
					'.navigation-header__item._catalog .navigation-header__name._active'
				)
				let catalogHeaderCatalog = document.querySelector(
					'.navigation-header__catalog'
				)
				let body = document.querySelector('body')
				if (spollersGo) {
					spollersGo = false
					catalogHeaderLink.classList.toggle('_active')
					_slideToggle(catalogHeaderCatalog, 300)
					body.classList.toggle('_catalog-open')
					e.preventDefault()
					setTimeout(function () {
						spollersGo = true
					}, 500)
				}
			}
		} else if (targetElement.closest('.catalog-header__back')) {
			let catalogHeaderLink = document.querySelector(
				'.navigation-header__item._catalog .navigation-header__name._active'
			)
			let catalogHeaderCatalog = document.querySelector(
				'.navigation-header__catalog'
			)
			let body = document.querySelector('body')
			if (spollersGo) {
				spollersGo = false
				catalogHeaderLink.classList.toggle('_active')
				_slideToggle(catalogHeaderCatalog, 300)
				body.classList.toggle('_catalog-open')
				e.preventDefault()
				setTimeout(function () {
					spollersGo = true
				}, 300)
			}
		}

		if (targetElement.closest('.icon-menu')) {
			let iconMenu = document.querySelector('.icon-menu')
			let menuBody = document.querySelector('.menu__body')
			if (unlock) {
				body_lock(200)
				iconMenu.classList.toggle('_active')
				menuBody.classList.toggle('_active')
			}
		} else if (
			!targetElement.closest('.menu__body') &&
			document.querySelector('.icon-menu._active')
		) {
			body_lock(200)
			menu_close()
		}

		if (
			targetElement.closest(
				'.user-actions-main-header .actions-main-header__icon'
			)
		) {
			let userActionsHeader = targetElement.closest('.user-actions-main-header')
			if (userActionsHeader.classList.contains('_authorized')) {
				userActionsHeader.classList.toggle('_user-actions')
			} else {
				userActionsHeader.classList.toggle('_login-register')
			}
		} else if (
			!targetElement.closest('.login-user-actions-main-header') &&
			document.querySelector('.user-actions-main-header._login-register')
		) {
			let userActionsHeader = document.querySelector(
				'.user-actions-main-header._login-register'
			)
			userActionsHeader.classList.remove('_login-register')
		} else if (targetElement.closest('.login-user-actions-main-header__btn')) {
			let userActionsHeader = document.querySelector(
				'.user-actions-main-header._login-register'
			)
			userActionsHeader.classList.remove('_login-register')
		} else if (
			!targetElement.closest('.user-header-info') &&
			document.querySelector('.user-actions-main-header._user-actions')
		) {
			let userActionsHeader = document.querySelector(
				'.user-actions-main-header._user-actions'
			)
			userActionsHeader.classList.remove('_user-actions')
		}

		if (targetElement.closest('.block-catalog-header__back')) {
			e.preventDefault()
			let blockTabs = document.querySelector(
				'.catalog-header__content ._tabs-block._active'
			)
			let itemTabs = document.querySelector(
				'.catalog-header__content ._tabs-item._active'
			)
			blockTabs.classList.remove('_active')
			itemTabs.classList.remove('_active')
		}

		if (targetElement.closest('.subblocks-catalog-header__back')) {
			e.preventDefault()
			let blockTabs2 = document.querySelector(
				'.block-catalog-header ._tabs-block2._active'
			)
			let itemTabs2 = document.querySelector(
				'.block-catalog-header ._tabs-item2._active'
			)
			blockTabs2.classList.remove('_active')
			itemTabs2.classList.remove('_active')
		}

		if (targetElement.closest('.action-product__like')) {
			e.preventDefault()
			let itemProduct = targetElement.closest('.product')
			itemProduct.classList.toggle('_liked')
		}

		if (targetElement.closest('.head-main-catalog__filter')) {
			e.preventDefault()
			document.querySelector('.sidebar-catalog').classList.add('_active')
			document.querySelector('body').classList.add('_filter-open')
			document
				.querySelector('.head-main-catalog__filter')
				.classList.add('_active')
		} else if (targetElement.closest('.top-sidebar-catalog__close')) {
			document.querySelector('.sidebar-catalog').classList.remove('_active')
			document.querySelector('body').classList.remove('_filter-open')
			document
				.querySelector('.head-main-catalog__filter')
				.classList.remove('_active')
		} else if (targetElement.closest('._close_filter')) {
			document.querySelector('.sidebar-catalog').classList.remove('_active')
			document.querySelector('body').classList.remove('_filter-open')
			document
				.querySelector('.head-main-catalog__filter')
				.classList.remove('_active')
		} else if (
			!targetElement.closest('.sidebar-catalog') &&
			document.querySelector('.head-main-catalog__filter._active')
		) {
			document.querySelector('.sidebar-catalog').classList.remove('_active')
			document.querySelector('body').classList.remove('_filter-open')
			document
				.querySelector('.head-main-catalog__filter')
				.classList.remove('_active')
		}

		if (targetElement.closest('.head-sidebar-catalog__clear')) {
			e.preventDefault()
			let filterSection = targetElement.closest('.sidebar-catalog__section')
			let filterSectionInputChekeds = filterSection.querySelectorAll(
				'.checkbox__input:checked'
			)
			if (filterSectionInputChekeds) {
				for (let index = 0; index < filterSectionInputChekeds.length; index++) {
					const filterSectionInputCheked = filterSectionInputChekeds[index]
					if (filterSectionInputCheked.checked) {
						filterSectionInputCheked.checked = false
					}
				}
			}

			if (document.querySelector('.ranger-sidebar-catalog')) {
				priceSlider.noUiSlider.set([0, 100000])
			}
			filter_products()
		}

		if (targetElement.closest('.sidebar-catalog__head')) {
			let filterSection = targetElement.closest('.sidebar-catalog__section')
			let filterSectionHead = filterSection.querySelector(
				'.sidebar-catalog__head'
			)
			let filterSectionBody = filterSection.querySelector(
				'.sidebar-catalog__body'
			)
			if (spollersGo) {
				spollersGo = false
				filterSectionHead.classList.toggle('_active')
				_slideToggle(filterSectionBody, 400)
				setTimeout(function () {
					spollersGo = true
				}, 400)
			}
		}

		if (targetElement.closest('.actions-info-card__like')) {
			e.preventDefault()
			let likeCard = targetElement.closest('.actions-info-card__like')
			likeCard.classList.toggle('_liked')
		}

		if (targetElement.closest('.actions-other-products-cart__item._like')) {
			e.preventDefault()
			let likeCartProduct = targetElement.closest(
				'.actions-other-products-cart__item._like'
			)
			likeCartProduct.classList.toggle('_liked')
		}

		if (targetElement.closest('.map-order-list__main')) {
			let itemMapListActive = document.querySelector(
				'.map-order-list__item._active'
			)
			let itemMapList = targetElement.closest('.map-order-list__item')
			if (itemMapListActive && itemMapListActive != itemMapList) {
				itemMapListActive.classList.remove('_active')
			}

			itemMapList.classList.toggle('_active')
		}

		if (targetElement.closest('.brand-letter__search')) {
			let inputSearchBrand = document.querySelector(
				'.search-head-brands__input'
			)
			let headerHeight = document.querySelector('header').clientHeight + 120
			_goto(inputSearchBrand, 300, headerHeight)
			inputSearchBrand.focus()
		}
	}

	const searchRoot = document.querySelector('[data-header-search]')
	const searchForm = searchRoot?.querySelector('.form-header-search')
	const searchInput = searchRoot?.querySelector('#head_search')
	const searchResults = searchRoot?.querySelector('.form-header-search__body')

	const searchHistoryStorageKey = 'vn_recent_searches'
	const recentlyViewedStorageKey = 'vn_recently_viewed'
	let searchRequestTimer = null
	let activeSearchRequest = null
	let searchRequestNumber = 0
	let renderedSearchQuery = null
	let pendingSearchQuery = null

	function readStoredList(key, maxItems) {
		try {
			const parsed = JSON.parse(window.localStorage.getItem(key) || '[]')
			return Array.isArray(parsed) ? parsed.slice(0, maxItems) : []
		} catch (error) {
			return []
		}
	}

	function getRecentSearches() {
		const result = []
		readStoredList(searchHistoryStorageKey, 20).forEach(value => {
			if (typeof value !== 'string') return
			const query = value.trim().replace(/\s+/g, ' ')
			if (query.length >= 2 && !result.includes(query)) result.push(query)
		})
		return result.slice(0, 3)
	}

	function recordSearchActivity(activity, query) {
		if (query.length < 2) return
		const analytics = window.VNFirstPartyAnalytics
		if (!analytics || typeof analytics.hasConsent !== 'function' || !analytics.hasConsent()) return

		fetch('/analytics/search-query', {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: new URLSearchParams({ query, activity }),
			keepalive: true
		}).catch(function () {
			// Search remains usable when aggregate analytics is unavailable.
		})
	}

	function rememberSearch(query, recordAggregate = true) {
		if (query.length < 2) return
		const history = getRecentSearches().filter(item => item.toLowerCase() !== query.toLowerCase())
		history.unshift(query)
		try {
			window.localStorage.setItem(searchHistoryStorageKey, JSON.stringify(history.slice(0, 3)))
		} catch (error) {
			// Search still works when the browser blocks local storage.
		}

		if (recordAggregate) recordSearchActivity('search', query)
	}

	function ensureHeaderSearchLanding() {
		if (!searchRoot || !searchInput || !searchResults || searchInput.value.trim()) return
		if (renderedSearchQuery === '' || pendingSearchQuery === '') return
		loadHeaderSearch('')
	}

	async function loadHeaderSearch(query) {
		if (!searchRoot || !searchForm || !searchInput || !searchResults) return

		const currentRequestNumber = ++searchRequestNumber
		if (activeSearchRequest) {
			activeSearchRequest.abort()
			activeSearchRequest = null
		}

		const normalizedQuery = query.trim().replace(/\s+/g, ' ')
		if (normalizedQuery.length === 1) {
			pendingSearchQuery = null
			searchResults.classList.remove('is-search-updating')
			return
		}
		pendingSearchQuery = normalizedQuery

		const language = document.documentElement.lang.toLowerCase().startsWith('ro')
			? 'ro'
			: 'ru'
		const body = new URLSearchParams({ search: normalizedQuery })
		readStoredList(recentlyViewedStorageKey, 20).forEach(id => {
			if (Number.isInteger(Number(id)) && Number(id) > 0) {
				body.append('recent_viewed[]', String(Number(id)))
			}
		})
		getRecentSearches().forEach(recentQuery => {
			body.append('recent_queries[]', recentQuery)
		})

		searchForm.classList.add('_results')
		activeSearchRequest = new AbortController()
		let responseRendered = false

		try {
			const response = await fetch('/' + language + '/search', {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
					'X-Requested-With': 'XMLHttpRequest'
				},
				body,
				signal: activeSearchRequest.signal
			})

			if (!response.ok) {
				throw new Error('Search request failed with HTTP ' + response.status)
			}

			const html = await response.text()
			if (currentRequestNumber === searchRequestNumber) {
				searchResults.innerHTML = html
				renderedSearchQuery = normalizedQuery
				responseRendered = true
				pendingSearchQuery = null
				syncSearchCompletionFromResults()
				window.requestAnimationFrame(function () {
					window.requestAnimationFrame(function () {
						if (currentRequestNumber === searchRequestNumber) {
							searchResults.classList.remove('is-search-updating')
						}
					})
				})
			}
		} catch (error) {
			if (error.name !== 'AbortError') {
				console.error('Header search failed:', error)
			}
		} finally {
			if (currentRequestNumber === searchRequestNumber) {
				activeSearchRequest = null
				pendingSearchQuery = null
				if (!responseRendered) {
					searchResults.classList.remove('is-search-updating')
				}
			}
		}
	}

	function rememberSearchProductForCart(query, productId) {
		const analytics = window.VNFirstPartyAnalytics
		const normalizedQuery = String(query || '').trim().replace(/\s+/g, ' ')
		const normalizedProductId = Number.parseInt(productId, 10)
		if (
			!normalizedQuery ||
			normalizedQuery.length < 2 ||
			!Number.isInteger(normalizedProductId) ||
			normalizedProductId <= 0 ||
			!analytics ||
			typeof analytics.hasConsent !== 'function' ||
			!analytics.hasConsent()
		) return

		try {
			window.sessionStorage.setItem(
				'vn_search_cart_attribution',
				JSON.stringify({
					query: normalizedQuery,
					productId: normalizedProductId,
					expiresAt: Date.now() + 30 * 60 * 1000
				})
			)
		} catch (error) {
			// Attribution is optional; the search and cart should work without storage.
		}
	}

	if (searchRoot && searchForm && searchInput && searchResults) {
		const fullSearchForm = searchRoot.querySelector('.form-header-search__search')
		const searchClear = fullSearchForm?.querySelector('.form-header-search__clear')
		const searchCompletion = fullSearchForm?.querySelector('.form-header-search__completion')
		const mobileSearchSuggestion = searchRoot.querySelector('.form-header-search__mobile-suggestion')
		let activeSearchCompletion = ''
		const setSearchCompletion = function (candidate) {
			activeSearchCompletion = ''
			searchCompletion?.replaceChildren()
			if (mobileSearchSuggestion) {
				mobileSearchSuggestion.replaceChildren()
				mobileSearchSuggestion.hidden = true
			}

			const typed = searchInput.value.replace(/\s+/g, ' ')
			const normalizedTyped = typed.toLocaleLowerCase()
			const completionCandidate = String(candidate || '').trim().replace(/\s+/g, ' ')
			const normalizedCandidate = completionCandidate.toLocaleLowerCase()
			if (!typed || !normalizedCandidate.startsWith(normalizedTyped) || normalizedCandidate.length <= normalizedTyped.length) return false

			const typedCharacters = Array.from(typed)
			const candidateCharacters = Array.from(completionCandidate)
			if (candidateCharacters.length <= typedCharacters.length) return false

			const prefix = document.createElement('span')
			prefix.className = 'form-header-search__completion-prefix'
			prefix.textContent = typed
			const suffix = document.createElement('span')
			suffix.textContent = candidateCharacters.slice(typedCharacters.length).join('')
			searchCompletion?.append(prefix, suffix)
			activeSearchCompletion = completionCandidate
			if (mobileSearchSuggestion && window.matchMedia('(max-width: 560px)').matches) {
				const button = document.createElement('button')
				button.type = 'button'
				button.className = 'form-header-search__mobile-suggestion-button'
				button.textContent = completionCandidate
				mobileSearchSuggestion.append(button)
				mobileSearchSuggestion.hidden = false
			}
			return true
		}
		const syncSearchCompletionFromResults = function () {
			const panel = searchResults.querySelector('[data-search-inline-completion]')
			const isPhone = window.matchMedia('(max-width: 560px)').matches
			if (!isPhone && setSearchCompletion(panel?.dataset.searchInlineCompletion || '')) return

			const typed = searchInput.value.trim().replace(/\s+/g, ' ').toLocaleLowerCase()
			if (!typed) {
				setSearchCompletion('')
				return
			}
			const visibleSuggestions = isPhone
				? [
					...searchResults.querySelectorAll('.header-search-panel__section--popular-queries [data-search-query]'),
					...searchResults.querySelectorAll('.header-search-panel__section--links-brands .header-search-panel__link-list a'),
					...searchResults.querySelectorAll('.header-search-panel__section--links-categories .header-search-panel__link-list a'),
					...searchResults.querySelectorAll('.header-search-panel__products .product__name')
				]
				: searchResults.querySelectorAll('[data-search-query], .header-search-panel__link-list a')
			for (const suggestion of visibleSuggestions) {
				const candidate = (suggestion.dataset.searchQuery || suggestion.textContent || '')
					.trim()
					.replace(/\s+/g, ' ')
				if (candidate.toLocaleLowerCase().startsWith(typed) && setSearchCompletion(candidate)) {
					return
				}
			}
			setSearchCompletion('')
		}
		const syncSearchControls = () => {
			const hasQuery = Boolean(searchInput.value.trim())
			fullSearchForm?.classList.toggle('has-query', hasQuery)
			if (searchClear) searchClear.hidden = !hasQuery
		}

		syncSearchControls()
		searchClear?.addEventListener('click', function () {
			searchInput.value = ''
			syncSearchControls()
			searchInput.dispatchEvent(new Event('input', { bubbles: true }))
			searchInput.focus({ preventScroll: true })
		})

		if (fullSearchForm) {
			fullSearchForm.addEventListener('submit', function () {
				const query = searchInput.value.trim().replace(/\s+/g, ' ')
				rememberSearch(query, false)
			})
		}

		window.addEventListener('vn:header-search-opened', ensureHeaderSearchLanding)
		searchInput.addEventListener('focus', ensureHeaderSearchLanding)

		searchInput.addEventListener('input', function () {
			const query = searchInput.value.trim().replace(/\s+/g, ' ')
			syncSearchControls()
			setSearchCompletion('')

			window.clearTimeout(searchRequestTimer)
			searchRequestNumber += 1
			if (activeSearchRequest) {
				activeSearchRequest.abort()
				activeSearchRequest = null
			}
			pendingSearchQuery = null

			if (query.length === 1) {
				searchResults.classList.remove('is-search-updating')
				return
			}

			searchResults.classList.add('is-search-updating')

			searchRequestTimer = window.setTimeout(async function () {
				loadHeaderSearch(query)
			}, 250)
		})

		searchInput.addEventListener('keydown', function (event) {
			if (!activeSearchCompletion || !['ArrowRight', 'Tab'].includes(event.key)) return
			if (searchInput.selectionStart !== searchInput.value.length || searchInput.selectionEnd !== searchInput.value.length) return

			event.preventDefault()
			searchInput.value = activeSearchCompletion
			setSearchCompletion('')
			syncSearchControls()
			searchInput.dispatchEvent(new Event('input', { bubbles: true }))
		})

		mobileSearchSuggestion?.addEventListener('click', function (event) {
			const button = event.target.closest('.form-header-search__mobile-suggestion-button')
			if (!button) return

			event.preventDefault()
			searchInput.value = activeSearchCompletion
			setSearchCompletion('')
			syncSearchControls()
			searchInput.dispatchEvent(new Event('input', { bubbles: true }))
		})

		searchResults.addEventListener('click', function (event) {
			const productLink = event.target.closest(
				'.header-search-panel__products .head-product__image, .header-search-panel__products .product__name'
			)
			if (productLink) {
				const query = searchInput.value.trim().replace(/\s+/g, ' ')
				const productCard = productLink.closest('.main-catalog__product')
				// Selecting a result while typing (for example, "clar") should not
				// turn that unfinished prefix into a recent or popular search query.
				recordSearchActivity('result_click', query)
				rememberSearchProductForCart(query, productCard?.dataset.prod_id)
				return
			}

			const addToCartAction = event.target.closest(
				'.header-search-panel__products .head-product__cart'
			)
			if (addToCartAction) {
				const query = searchInput.value.trim().replace(/\s+/g, ' ')
				const productCard = addToCartAction.closest('.main-catalog__product')
				rememberSearchProductForCart(query, productCard?.dataset.prod_id)
			}

			const queryButton = event.target.closest('[data-search-query]')
			if (!queryButton) {
				if (event.target.closest('a')) {
					const isAllResultsLink = event.target.closest('.header-search-panel__all-results')
					rememberSearch(
						searchInput.value.trim().replace(/\s+/g, ' '),
						!isAllResultsLink
					)
				}
				return
			}
			event.preventDefault()
			searchInput.value = queryButton.dataset.searchQuery || ''
			rememberSearch(searchInput.value.trim().replace(/\s+/g, ' '))
			searchInput.dispatchEvent(new Event('input', { bubbles: true }))
			searchInput.focus()
		})
	}

	const fullSearchPage = document.querySelector('[data-search-page]')
	if (fullSearchPage) {
		const fullPageQuery = (fullSearchPage.dataset.searchQuery || '').trim().replace(/\s+/g, ' ')
		if (fullSearchPage.dataset.searchTrackZeroResult === '1') {
			recordSearchActivity('zero_result', fullPageQuery)
		}

		fullSearchPage.addEventListener('click', function (event) {
			const productLink = event.target.closest(
				'.products_catalog .main-catalog__product .head-product__image, .products_catalog .main-catalog__product .product__name'
			)
			if (productLink) {
				const productCard = productLink.closest('.main-catalog__product')
				recordSearchActivity('result_click', fullPageQuery)
				rememberSearchProductForCart(fullPageQuery, productCard?.dataset.prod_id)
				return
			}

			const addToCartAction = event.target.closest(
				'.products_catalog .main-catalog__product .head-product__cart'
			)
			if (addToCartAction) {
				const productCard = addToCartAction.closest('.main-catalog__product')
				rememberSearchProductForCart(fullPageQuery, productCard?.dataset.prod_id)
			}
		})
	}

	if (document.querySelector('.promocode-main-cart__input')) {
		let promocodeInput = document.querySelector('.promocode-main-cart__input')
		promocodeInput.addEventListener('input', function () {
			let promocodeInputValue = promocodeInput.value.replaceAll(' ', '')
			let promocodeBody = document.querySelector('.promocode-main-cart__body')
			if (promocodeInputValue.length < 1) {
				promocodeBody.classList.remove('_submit')
			} else {
				promocodeBody.classList.add('_submit')
			}
		})
	}

	let inputItems = document.querySelectorAll('._item-input input')
	if (inputItems.length > 0) {
		for (let index = 0; index < inputItems.length; index++) {
			const inputItem = inputItems[index]
			inputItem.addEventListener('focus', function () {
				console.log('focus')
				let inputItemRow = inputItem.closest('._item-input')
				inputItemRow.classList.add('_focus')
			})

			inputItem.addEventListener('blur', function () {
				let inputItemRow = inputItem.closest('._item-input')
				let inputItemValue = inputItem.value.replaceAll(' ', '')
				if (inputItemValue != '') {
					return false
				}
				inputItemRow.classList.remove('_focus')
			})
		}
	}

	let codeInputs = document.querySelectorAll('.code-login__input')
	if (codeInputs) {
		for (let index = 0; index < codeInputs.length; index++) {
			const codeInput = codeInputs[index]
			codeInput.addEventListener('input', function () {
				let codeColumn = codeInput.closest('.code-login__column')
				let nextCodeColumn = codeColumn.nextElementSibling
				if (nextCodeColumn && codeInput.value.length == 1) {
					let nextCodeInput = nextCodeColumn.querySelector('.code-login__input')
					nextCodeInput.focus()
				}
			})
		}
	}
}

let activeSearchRequest = null
