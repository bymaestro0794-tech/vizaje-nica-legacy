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

	// const searchInput = document.querySelector('#head_search')

	const searchHeaderForm = document.querySelector(
		'.header-search .form-header-search'
	)

	let searchRequestTimer = null
	let previousSearchValue = ''

	if (searchInput && searchHeaderForm) {
		searchInput.addEventListener('input', function () {
			const searchInputValue = searchInput.value.trim().replace(/\s+/g, ' ')

			window.clearTimeout(searchRequestTimer)

			if (searchInputValue.length >= 3) {
				searchHeaderForm.classList.add('_results')

				if (searchInputValue === previousSearchValue) {
					return
				}

				searchRequestTimer = window.setTimeout(function () {
					previousSearchValue = searchInputValue

					searchResult(searchInputValue)
				}, 250)

				return
			}

			previousSearchValue = ''

			searchHeaderForm.classList.remove('_results')

			const searchResultContainer = document.querySelector(
				'.header-search .search_result'
			)

			if (searchResultContainer) {
				searchResultContainer.innerHTML = ''
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
