;(function () {
	'use strict'

	var drawer = document.querySelector('[data-category-filter-drawer]')
	var form = document.querySelector('[data-category-filter-form]')
	if (!drawer || !form) return

	var backdrop = document.querySelector('.catalog-filter-backdrop[data-category-filter-close]')
	var openButton = document.querySelector('[data-category-filter-open]')
	var countTimer = null
	var defaultMin = Number(form.dataset.priceDefaultMin || 0)
	var defaultMax = Number(form.dataset.priceDefaultMax || 0)
	var drawerScrollPosition = null
	var sortScrollPosition = null
	var pendingScrollStorageKey = 'vizaje-catalog-scroll:' + window.location.pathname

	function restoreWindowScroll(x, y) {
		window.requestAnimationFrame(function () {
			var root = document.documentElement
			var previousScrollBehavior = root.style.scrollBehavior
			root.style.scrollBehavior = 'auto'
			window.scrollTo(x, y)
			root.style.scrollBehavior = previousScrollBehavior
		})
	}

	function restorePendingScroll() {
		var savedPosition
		try {
			savedPosition = JSON.parse(window.sessionStorage.getItem(pendingScrollStorageKey) || 'null')
			if (!savedPosition) return
			window.sessionStorage.removeItem(pendingScrollStorageKey)
		} catch (error) {
			return
		}

		if (savedPosition.url !== window.location.pathname + window.location.search) return
		var x = Number(savedPosition.x) || 0
		var y = Number(savedPosition.y) || 0
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () { restoreWindowScroll(x, y) })
		})
		window.addEventListener('load', function () { restoreWindowScroll(x, y) }, { once: true })
	}

	restorePendingScroll()

	function paramsFromForm() {
		var params = new URLSearchParams()
		var priceMin = form.querySelector('[name="price_min"]')
		var priceMax = form.querySelector('[name="price_max"]')
		var minChanged = priceMin && Number(priceMin.value) !== defaultMin
		var maxChanged = priceMax && Number(priceMax.value) !== defaultMax
		new FormData(form).forEach(function (value, key) {
			if (key === 'price_min' || key === 'price_max') return
			if (String(value).trim() !== '') params.append(key, value)
		})
		if (minChanged || maxChanged) {
			if (form.querySelector('[data-price-slider]')) {
				params.set('price_min', String(Number(priceMin.value)))
				params.set('price_max', String(Number(priceMax.value)))
			} else {
				if (priceMin && priceMin.value.trim() !== '') params.set('price_min', String(Number(priceMin.value)))
				if (priceMax && priceMax.value.trim() !== '') params.set('price_max', String(Number(priceMax.value)))
			}
		}
		return params
	}

	function refreshCount() {
		window.clearTimeout(countTimer)
		countTimer = window.setTimeout(function () {
			var params = paramsFromForm()
			params.set('filter_count', '1')
			var resultNodes = [
				drawer.querySelector('[data-filter-result-count]'),
				drawer.querySelector('[data-filter-apply-count]')
			]
			resultNodes.forEach(function (node) {
				if (node) node.textContent = '…'
			})

			fetch(window.location.pathname + '?' + params.toString(), {
				credentials: 'same-origin',
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
				.then(function (response) {
					if (!response.ok) throw new Error('Could not update product count')
					return response.json()
				})
				.then(function (data) {
					resultNodes.forEach(function (node) {
						if (node) node.textContent = Number(data.count || 0).toLocaleString(document.documentElement.lang || 'ru')
					})
				})
				.catch(function () {
					resultNodes.forEach(function (node) {
						if (node) node.textContent = '—'
					})
				})
		}, 220)
	}

	function openDrawer(sectionName) {
		drawerScrollPosition = { x: window.pageXOffset, y: window.pageYOffset }
		var drawerTitle = drawer.querySelector('[data-filter-drawer-title]')
		var drawerEyebrow = drawer.querySelector('[data-filter-drawer-eyebrow]')
		var focusedSection = sectionName && drawer.querySelector('[data-filter-section="' + sectionName + '"]')
		var currentTitle = focusedSection ? focusedSection.dataset.filterTitle : drawer.dataset.filterDefaultTitle
		if (drawerTitle) drawerTitle.textContent = currentTitle
		drawer.setAttribute('aria-label', currentTitle)
		if (drawerEyebrow) drawerEyebrow.hidden = Boolean(focusedSection)
		drawer.querySelectorAll('.catalog-filter-section').forEach(function (section) {
			section.hidden = Boolean(sectionName && section.getAttribute('data-filter-section') !== sectionName)
			var heading = section.querySelector('.catalog-filter-section__heading')
			var body = section.querySelector('.catalog-filter-section__body')
			if (heading && body) {
				heading.setAttribute('aria-expanded', 'false')
				body.hidden = true
			}
		})

		drawer.classList.add('is-open')
		drawer.setAttribute('aria-hidden', 'false')
		if (backdrop) backdrop.hidden = false
		document.documentElement.classList.add('category-filter-open')
		document.body.classList.add('category-filter-open')
		restoreWindowScroll(drawerScrollPosition.x, drawerScrollPosition.y)

		if (sectionName) {
			var section = drawer.querySelector('[data-filter-section="' + sectionName + '"]')
			if (section) {
				var heading = section.querySelector('.catalog-filter-section__heading')
				var body = section.querySelector('.catalog-filter-section__body')
				if (heading && body) {
					heading.setAttribute('aria-expanded', 'true')
					body.hidden = false
					var drawerContent = drawer.querySelector('.catalog-filter-drawer__content')
					if (drawerContent) drawerContent.scrollTop = 0
				}
			}
		}
	}

	function closeDrawer() {
		var scrollPosition = drawerScrollPosition || { x: window.pageXOffset, y: window.pageYOffset }
		drawer.classList.remove('is-open')
		drawer.setAttribute('aria-hidden', 'true')
		if (backdrop) backdrop.hidden = true
		document.documentElement.classList.remove('category-filter-open')
		document.body.classList.remove('category-filter-open')
		restoreWindowScroll(scrollPosition.x, scrollPosition.y)
		drawerScrollPosition = null
	}

	function applyCurrentFilters(scrollPositionOverride) {
		var params = paramsFromForm()
		var sort = document.querySelector('.catalog-sort select')
		if (sort && sort.value) params.set('sort', sort.value)
		var target = new URL(window.location.pathname, window.location.origin)
		target.search = params.toString()
		var scrollPosition = scrollPositionOverride || (drawer.classList.contains('is-open') && drawerScrollPosition
			? drawerScrollPosition
			: { x: window.pageXOffset, y: window.pageYOffset })
		try {
			window.sessionStorage.setItem(pendingScrollStorageKey, JSON.stringify({
				url: target.pathname + target.search,
				x: scrollPosition.x,
				y: scrollPosition.y
			}))
		} catch (error) {
			// Filtering should still work when session storage is unavailable.
		}
		window.location.assign(target.toString())
	}

	function updateQuickControls() {
		var activeCheckboxes = Array.prototype.slice.call(form.querySelectorAll('input[type="checkbox"]:checked'))
		var min = form.querySelector('[name="price_min"]')
		var max = form.querySelector('[name="price_max"]')
		var priceActive = min && max && (Number(min.value) !== defaultMin || Number(max.value) !== defaultMax)
		var triggerCount = document.querySelector('[data-active-filter-count]')
		var activeCount = activeCheckboxes.length + (priceActive ? 1 : 0)
		if (triggerCount) {
			triggerCount.hidden = activeCount === 0
			triggerCount.textContent = activeCount
		}

		document.querySelectorAll('[data-quick-filter]').forEach(function (control) {
			var filterName = control.dataset.quickFilter
			var section = form.querySelector('[data-filter-section="' + filterName + '"]')
			var selected = section ? Array.prototype.slice.call(section.querySelectorAll('input[type="checkbox"]:checked')).map(function (input) {
				var name = input.closest('.catalog-filter-option')
				return name ? name.querySelector('.catalog-filter-option__name').textContent.trim() : ''
			}).filter(Boolean) : []
			var isPrice = filterName === 'price'
			var active = isPrice ? priceActive : selected.length > 0
			var label = control.querySelector('[data-quick-label]')
			var clear = control.querySelector('[data-quick-clear]')
			var trigger = control.querySelector('[data-filter-open]')
			var valueLabel = ''
			if (isPrice && priceActive) {
				valueLabel = (form.dataset.priceLabel || control.dataset.defaultLabel) + ': ' + Number(min.value).toLocaleString(document.documentElement.lang || 'ru') + '–' + Number(max.value).toLocaleString(document.documentElement.lang || 'ru')
			} else if (selected.length === 1) {
				valueLabel = selected[0]
			} else if (selected.length > 1) {
				valueLabel = selected[0] + ' +' + (selected.length - 1)
			}
			if (label) label.textContent = active ? valueLabel : control.dataset.defaultLabel
			if (clear) clear.hidden = !active
			if (trigger) {
				trigger.classList.toggle('is-active', active)
				var chevron = trigger.querySelector('svg')
				if (chevron) chevron.hidden = active
			}
		})
	}

	if (openButton) openButton.addEventListener('click', function () { openDrawer() })
	document.querySelectorAll('[data-filter-open]').forEach(function (button) {
		button.addEventListener('click', function () { openDrawer(button.getAttribute('data-filter-open')) })
	})
	document.querySelectorAll('[data-quick-clear]').forEach(function (button) {
		button.addEventListener('click', function () {
			var filterName = button.closest('[data-quick-filter]').dataset.quickFilter
			if (filterName === 'price') {
				minField.value = String(defaultMin)
				maxField.value = String(defaultMax)
				syncSliderVisual()
			} else {
				var section = form.querySelector('[data-filter-section="' + filterName + '"]')
				if (section) section.querySelectorAll('input[type="checkbox"]:checked').forEach(function (input) { input.checked = false })
			}
			updateQuickControls()
			applyCurrentFilters()
		})
	})
	document.querySelectorAll('[data-category-filter-close]').forEach(function (button) {
		button.addEventListener('click', closeDrawer)
	})
	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeDrawer()
			closeSortMenu()
		}
	})

	drawer.querySelectorAll('.catalog-filter-section__heading').forEach(function (heading) {
		heading.addEventListener('click', function () {
			var expanded = heading.getAttribute('aria-expanded') === 'true'
			heading.setAttribute('aria-expanded', expanded ? 'false' : 'true')
			heading.nextElementSibling.hidden = expanded
		})
	})

	var brandSearch = drawer.querySelector('[data-brand-search]')
	if (brandSearch) brandSearch.addEventListener('input', function () {
		var query = brandSearch.value.toLocaleLowerCase()
		var showMore = drawer.querySelector('[data-show-more]')
		var expanded = showMore && showMore.getAttribute('aria-expanded') === 'true'
		drawer.querySelectorAll('[data-brand-name]').forEach(function (option) {
			option.hidden = option.getAttribute('data-brand-name').indexOf(query) === -1
			option.classList.toggle('is-expanded', expanded || query.length > 0)
		})
		drawer.querySelectorAll('.catalog-brand-group').forEach(function (group) {
			group.classList.toggle('is-expanded', expanded || query.length > 0)
			group.hidden = query.length > 0 && !group.querySelector('[data-brand-name]:not([hidden])')
		})
	})

	drawer.querySelectorAll('[data-show-more]').forEach(function (button) {
		button.addEventListener('click', function () {
			var expanded = button.getAttribute('aria-expanded') === 'true'
			button.setAttribute('aria-expanded', expanded ? 'false' : 'true')
			button.textContent = (expanded ? button.dataset.showLabel : button.dataset.hideLabel) + ' (' + button.closest('.catalog-filter-section').querySelectorAll('[data-brand-name]').length + ')'
			button.closest('.catalog-filter-section').querySelectorAll('.is-collapsed').forEach(function (item) {
				item.classList.toggle('is-expanded', !expanded || (brandSearch && brandSearch.value.length > 0))
			})
		})
	})

	var minField = form.querySelector('[name="price_min"]')
	var maxField = form.querySelector('[name="price_max"]')
	var minRange = form.querySelector('[data-price-range="min"]')
	var maxRange = form.querySelector('[data-price-range="max"]')
	var slider = form.querySelector('[data-price-slider]')
	var selectedRange = form.querySelector('[data-price-range-selected]')
	var sliderMin = slider ? Number(slider.dataset.min || 0) : 0
	var sliderMax = slider ? Number(slider.dataset.max || defaultMax) : defaultMax
	function syncSliderVisual() {
		if (!slider || !minRange || !maxRange) return
		var span = Math.max(1, sliderMax - sliderMin)
		var minPercent = Math.max(0, Math.min(100, (Number(minField.value) - sliderMin) / span * 100))
		var maxPercent = Math.max(minPercent, Math.min(100, (Number(maxField.value) - sliderMin) / span * 100))
		minRange.style.left = minPercent + '%'
		maxRange.style.left = maxPercent + '%'
		minRange.setAttribute('aria-valuemin', sliderMin)
		minRange.setAttribute('aria-valuemax', Number(maxField.value))
		minRange.setAttribute('aria-valuenow', Number(minField.value))
		maxRange.setAttribute('aria-valuemin', Number(minField.value))
		maxRange.setAttribute('aria-valuemax', sliderMax)
		maxRange.setAttribute('aria-valuenow', Number(maxField.value))
		if (selectedRange) {
			selectedRange.style.left = minPercent + '%'
			selectedRange.style.width = (maxPercent - minPercent) + '%'
		}
	}
	function syncPrice(source) {
		var lower = Math.max(sliderMin, Math.min(sliderMax, Number(minField.value || sliderMin)))
		var upper = Math.max(lower, Math.min(sliderMax, Number(maxField.value || sliderMax)))
		if (source === minRange && lower > upper) upper = lower
		if (source === maxRange && upper < lower) lower = upper
		minField.value = lower
		maxField.value = upper
		syncSliderVisual()
		updateQuickControls()
		refreshCount()
	}
	;[minField, maxField].forEach(function (control) {
		if (control) control.addEventListener('input', function () {
			if (slider) syncPrice(control)
			else {
				updateQuickControls()
				refreshCount()
			}
		})
	})

	function setPriceFromPointer(event, handle) {
		var bounds = slider.getBoundingClientRect()
		var thumbRadius = 10
		var usableWidth = Math.max(1, bounds.width - thumbRadius * 2)
		var ratio = Math.max(0, Math.min(1, (event.clientX - bounds.left - thumbRadius) / usableWidth))
		var nextValue = Math.round(sliderMin + ratio * (sliderMax - sliderMin))
		if (handle === minRange) minField.value = Math.min(nextValue, Number(maxField.value))
		else maxField.value = Math.max(nextValue, Number(minField.value))
		syncPrice(handle)
	}
	if (slider) {
		;[minRange, maxRange].forEach(function (handle) {
			handle.addEventListener('pointerdown', function (event) {
				event.preventDefault()
				handle.setPointerCapture(event.pointerId)
				handle.classList.add('is-dragging')
				setPriceFromPointer(event, handle)
			})
			handle.addEventListener('pointermove', function (event) {
				if (handle.hasPointerCapture(event.pointerId)) setPriceFromPointer(event, handle)
			})
			handle.addEventListener('pointerup', function (event) {
				if (handle.hasPointerCapture(event.pointerId)) handle.releasePointerCapture(event.pointerId)
				handle.classList.remove('is-dragging')
			})
			handle.addEventListener('pointercancel', function () { handle.classList.remove('is-dragging') })
			handle.addEventListener('keydown', function (event) {
				var step = event.shiftKey ? 10 : 1
				var current = Number(handle.getAttribute('aria-valuenow'))
				var next = current
				if (event.key === 'ArrowRight' || event.key === 'ArrowUp') next += step
				else if (event.key === 'ArrowLeft' || event.key === 'ArrowDown') next -= step
				else if (event.key === 'Home') next = handle === minRange ? sliderMin : Number(minField.value)
				else if (event.key === 'End') next = handle === minRange ? Number(maxField.value) : sliderMax
				else return
				event.preventDefault()
				if (handle === minRange) minField.value = Math.max(sliderMin, Math.min(Number(maxField.value), next))
				else maxField.value = Math.max(Number(minField.value), Math.min(sliderMax, next))
				syncPrice(handle)
			})
		})
		slider.addEventListener('pointerdown', function (event) {
			if (event.target.closest('[data-price-range]')) return
			var minDistance = Math.abs(event.clientX - minRange.getBoundingClientRect().left)
			var maxDistance = Math.abs(event.clientX - maxRange.getBoundingClientRect().left)
			var nearest = minDistance <= maxDistance ? minRange : maxRange
			nearest.focus()
			nearest.setPointerCapture(event.pointerId)
			nearest.classList.add('is-dragging')
			setPriceFromPointer(event, nearest)
		})
	}
	syncSliderVisual()
	form.addEventListener('change', function () { updateQuickControls(); refreshCount() })
	form.addEventListener('submit', function (event) {
		event.preventDefault()
		applyCurrentFilters()
	})

	var resetButton = drawer.querySelector('[data-filter-reset]')
	if (resetButton) resetButton.addEventListener('click', function () {
		form.querySelectorAll('input[type="checkbox"]').forEach(function (input) { input.checked = false })
		if (minField) minField.value = slider ? String(defaultMin) : ''
		if (maxField) maxField.value = slider ? String(defaultMax) : ''
		if (minRange && maxRange) {
			minField.value = String(sliderMin)
			maxField.value = String(sliderMax)
			syncSliderVisual()
		}
		if (brandSearch) brandSearch.value = ''
		drawer.querySelectorAll('[data-brand-name]').forEach(function (option) {
			option.hidden = false
			option.classList.remove('is-expanded')
		})
		drawer.querySelectorAll('.catalog-brand-group').forEach(function (group) {
			group.hidden = false
			group.classList.remove('is-expanded')
		})
		drawer.querySelectorAll('[data-show-more]').forEach(function (button) {
			button.setAttribute('aria-expanded', 'false')
			button.textContent = button.dataset.showLabel + ' (' + button.closest('.catalog-filter-section').querySelectorAll('[data-brand-name]').length + ')'
		})
		updateQuickControls()
		refreshCount()
	})

	var sortRoot = document.querySelector('[data-catalog-sort]')
	var sortSelect = sortRoot && sortRoot.querySelector('select')
	var sortToggle = sortRoot && sortRoot.querySelector('[data-sort-toggle]')
	var sortMenu = sortRoot && sortRoot.querySelector('[data-sort-menu]')
	var sortBackdrop = document.querySelector('[data-sort-backdrop]')
	var sortApply = sortRoot && sortRoot.querySelector('[data-sort-apply]')
	var pendingSort = sortSelect ? sortSelect.value : ''
	var sortCloseTimer = null
	var sortMenuParent = sortMenu && sortMenu.parentNode
	var sortMenuNextSibling = sortMenu && sortMenu.nextSibling
	var sortBackdropParent = sortBackdrop && sortBackdrop.parentNode
	var sortBackdropNextSibling = sortBackdrop && sortBackdrop.nextSibling
	var sortSheetPortaled = false
	function isMobileSort() {
		return window.matchMedia && window.matchMedia('(max-width: 767px)').matches
	}
	function portalSortSheet() {
		if (sortSheetPortaled || !sortMenu) return
		sortMenu.classList.add('catalog-sort__menu--sheet')
		if (sortBackdrop) document.body.appendChild(sortBackdrop)
		document.body.appendChild(sortMenu)
		sortSheetPortaled = true
	}
	function restoreSortSheet() {
		if (!sortSheetPortaled) return
		if (sortBackdropParent) {
			var backdropReference = sortBackdropNextSibling && sortBackdropNextSibling.parentNode === sortBackdropParent ? sortBackdropNextSibling : null
			sortBackdropParent.insertBefore(sortBackdrop, backdropReference)
		}
		if (sortMenuParent) {
			var menuReference = sortMenuNextSibling && sortMenuNextSibling.parentNode === sortMenuParent ? sortMenuNextSibling : null
			sortMenuParent.insertBefore(sortMenu, menuReference)
		}
		sortMenu.classList.remove('catalog-sort__menu--sheet')
		sortSheetPortaled = false
	}
	function closeSortMenu() {
		if (!sortRoot || !sortToggle || !sortMenu) return
		window.clearTimeout(sortCloseTimer)
		sortRoot.classList.remove('is-open')
		sortToggle.setAttribute('aria-expanded', 'false')
		sortMenu.setAttribute('aria-hidden', 'true')
		sortMenu.removeAttribute('aria-modal')
		sortMenu.classList.remove('is-visible')
		if (sortBackdrop) sortBackdrop.classList.remove('is-visible')
		if (sortSheetPortaled && isMobileSort()) {
			sortCloseTimer = window.setTimeout(function () {
				if (sortToggle.getAttribute('aria-expanded') === 'false') {
					sortMenu.hidden = true
					if (sortBackdrop) sortBackdrop.hidden = true
					document.body.classList.remove('catalog-sort-open')
					if (sortScrollPosition) restoreWindowScroll(sortScrollPosition.x, sortScrollPosition.y)
					restoreSortSheet()
					sortScrollPosition = null
				}
			}, 280)
		} else {
			sortMenu.hidden = true
			if (sortBackdrop) sortBackdrop.hidden = true
			document.body.classList.remove('catalog-sort-open')
			if (sortScrollPosition) restoreWindowScroll(sortScrollPosition.x, sortScrollPosition.y)
			restoreSortSheet()
			sortScrollPosition = null
		}
	}
	if (sortToggle && sortMenu) sortToggle.addEventListener('click', function () {
		var expanded = sortToggle.getAttribute('aria-expanded') === 'true'
		if (expanded) {
			closeSortMenu()
			return
		}
		window.clearTimeout(sortCloseTimer)
		if (isMobileSort()) {
			sortScrollPosition = { x: window.pageXOffset, y: window.pageYOffset }
			portalSortSheet()
		}
		pendingSort = sortSelect.value
		sortMenu.querySelectorAll('[data-sort-option]').forEach(function (option) {
			var selected = option.getAttribute('data-sort-option') === pendingSort
			option.setAttribute('aria-checked', selected ? 'true' : 'false')
			option.setAttribute('aria-selected', selected ? 'true' : 'false')
		})
		sortToggle.setAttribute('aria-expanded', 'true')
		sortMenu.setAttribute('aria-hidden', 'false')
		if (isMobileSort()) sortMenu.setAttribute('aria-modal', 'true')
		else sortMenu.removeAttribute('aria-modal')
		sortMenu.hidden = false
		sortRoot.classList.add('is-open')
		if (isMobileSort()) {
			if (sortBackdrop) sortBackdrop.hidden = false
			document.body.classList.add('catalog-sort-open')
			window.requestAnimationFrame(function () {
				if (sortToggle.getAttribute('aria-expanded') === 'true') {
					sortMenu.classList.add('is-visible')
					if (sortBackdrop) sortBackdrop.classList.add('is-visible')
				}
			})
		} else {
			sortMenu.classList.add('is-visible')
		}
	})
	if (sortRoot && sortSelect) {
		sortRoot.querySelectorAll('[data-sort-option]').forEach(function (option) {
			option.addEventListener('click', function () {
				pendingSort = option.getAttribute('data-sort-option')
				sortMenu.querySelectorAll('[data-sort-option]').forEach(function (item) {
					item.setAttribute('aria-checked', item === option ? 'true' : 'false')
					item.setAttribute('aria-selected', item === option ? 'true' : 'false')
				})
				if (isMobileSort()) return
				sortSelect.value = pendingSort
				sortRoot.querySelector('[data-sort-current]').textContent = option.querySelector('span').textContent
				closeSortMenu()
				applyCurrentFilters()
			})
		})
		if (sortApply) sortApply.addEventListener('click', function () {
			sortSelect.value = pendingSort
			var selectedOption = sortMenu.querySelector('[data-sort-option="' + pendingSort + '"]')
			if (selectedOption) sortRoot.querySelector('[data-sort-current]').textContent = selectedOption.querySelector('span').textContent
			var scrollPosition = sortScrollPosition
			closeSortMenu()
			if (isMobileSort()) window.setTimeout(function () { applyCurrentFilters(scrollPosition) }, 300)
			else applyCurrentFilters(scrollPosition)
		})
		if (sortBackdrop) sortBackdrop.addEventListener('click', closeSortMenu)
		document.addEventListener('click', function (event) {
			if (!sortRoot.contains(event.target) && !sortMenu.contains(event.target)) closeSortMenu()
		})
	}
	var stockQuickToggle = document.querySelector('[data-stock-quick-toggle]')
	var stockField = form.querySelector('[name="in_stock"]')
	var discountQuickToggle = document.querySelector('[data-discount-quick-toggle]')
	var discountField = form.querySelector('[name="discounted"]')
	if (discountQuickToggle && discountField) discountQuickToggle.addEventListener('click', function () {
		discountField.checked = !discountField.checked
		discountQuickToggle.classList.toggle('is-active', discountField.checked)
		discountQuickToggle.setAttribute('aria-pressed', discountField.checked ? 'true' : 'false')
		applyCurrentFilters()
	})
	if (stockQuickToggle && stockField) stockQuickToggle.addEventListener('click', function () {
		stockField.checked = !stockField.checked
		stockQuickToggle.classList.toggle('is-active', stockField.checked)
		stockQuickToggle.setAttribute('aria-pressed', stockField.checked ? 'true' : 'false')
		applyCurrentFilters()
	})

	updateQuickControls()
})()

;(function () {
	'use strict'

	var page = document.querySelector('.catalog-category-page')
	if (!page) return

	var buttons = Array.prototype.slice.call(page.querySelectorAll('[data-catalog-view]'))
	if (!buttons.length) return

	var storageKey = 'vizaje-catalog-view'
	var currentView = 'standard'

	try {
		currentView = window.localStorage.getItem(storageKey) === 'list' ? 'list' : 'standard'
	} catch (error) {
		currentView = 'standard'
	}

	function setView(view) {
		currentView = view === 'list' ? 'list' : 'standard'
		page.classList.toggle('is-view-list', currentView === 'list')

		buttons.forEach(function (button) {
			var active = button.getAttribute('data-catalog-view') === currentView
			button.classList.toggle('is-active', active)
			button.setAttribute('aria-pressed', active ? 'true' : 'false')
		})
	}

	buttons.forEach(function (button) {
		button.addEventListener('click', function () {
			var requestedView = button.getAttribute('data-catalog-view')
			if (window.matchMedia && window.matchMedia('(max-width: 767px)').matches && button.classList.contains('is-active')) {
				requestedView = currentView === 'list' ? 'standard' : 'list'
			}
			setView(requestedView)
			try {
				window.localStorage.setItem(storageKey, currentView)
			} catch (error) {}
		})
	})

	setView(currentView)
})()
