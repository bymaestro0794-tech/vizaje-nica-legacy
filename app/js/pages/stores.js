/**
 * Vizaje-Nica — Stores page
 *
 * Responsibilities:
 * - initializes MapLibre with OpenFreeMap;
 * - creates numbered markers;
 * - synchronizes active store card and marker;
 * - updates selected-store panel;
 * - switches list/map views on mobile;
 * - opens Google Maps route.
 */

document.addEventListener('DOMContentLoaded', function () {
	const storesRoot = document.querySelector('.vn-stores')
	const mapElement = document.getElementById('vnStoresMap')
	if (
		!storesRoot ||
		!mapElement ||
		typeof maplibregl === 'undefined'
	) {
		return
	}

	const mobileTabs = storesRoot.querySelector('.vn-stores__mobile-tabs')
	

	/*
	 * Защита от повторной инициализации,
	 * если скрипт случайно подключится дважды.
	 */
	if (mapElement.dataset.initialized === 'true') {
		return
	}

	const storeCards = Array.from(
		storesRoot.querySelectorAll('[data-store-card]')
	)

	if (!storeCards.length) {
		return
	}

	const mapWrap = storesRoot.querySelector('[data-store-map-view]')

	const viewTabs = Array.from(storesRoot.querySelectorAll('[data-store-view]'))

	const panel = storesRoot.querySelector('[data-map-panel]')
	const panelClose = storesRoot.querySelector('[data-panel-close]')
	const panelNumber = storesRoot.querySelector('[data-panel-number]')
	const panelTitle = storesRoot.querySelector('[data-panel-title]')
	const panelAddress = storesRoot.querySelector('[data-panel-address]')
	const panelTime = storesRoot.querySelector('[data-panel-time]')
	const panelRoute = storesRoot.querySelector('[data-panel-route]')

	const MOBILE_BREAKPOINT = 991
	const CHISINAU_CENTER = [28.8353, 47.0105]
	const DEFAULT_DESKTOP_ZOOM = 11.7
	const DEFAULT_MOBILE_ZOOM = 11.2
	const SELECTED_DESKTOP_ZOOM = 14
	const SELECTED_MOBILE_ZOOM = 14.2

	let activeStoreIndex = null
	let resizeTimer = null

	const markers = []

	const stores = storeCards
		.map(function (card) {
			return {
				card: card,
				index: Number(card.dataset.index),
				number: card.dataset.number || '',
				title: card.dataset.title || '',
				address: card.dataset.address || '',
				time: card.dataset.time || '',
				phone: card.dataset.phone || '',
				phoneClean: card.dataset.phoneClean || '',
				lat: Number(card.dataset.lat),
				lng: Number(card.dataset.lng)
			}
		})
		.filter(function (store) {
			return (
				Number.isFinite(store.index) &&
				Number.isFinite(store.lat) &&
				Number.isFinite(store.lng)
			)
		})

	if (!stores.length) {
		return
	}

	mapElement.dataset.initialized = 'true'

	const map = new maplibregl.Map({
		container: mapElement,
		style: 'https://tiles.openfreemap.org/styles/liberty',
		center: CHISINAU_CENTER,
		zoom:
			window.innerWidth <= MOBILE_BREAKPOINT
				? DEFAULT_MOBILE_ZOOM
				: DEFAULT_DESKTOP_ZOOM,
		minZoom: 8,
		maxZoom: 18,
		attributionControl: true
	})

	map.addControl(
		new maplibregl.NavigationControl({
			showCompass: false,
			showZoom: true
		}),
		'top-right'
	)

	/**
	 * Возвращает магазин по его index.
	 */
	function getStoreByIndex(index) {
		return stores.find(function (store) {
			return store.index === index
		})
	}

	/**
	 * Создаёт DOM-элемент маркера.
	 */
	function createMarkerElement(store) {
		const markerElement = document.createElement('button')
		const markerNumber = document.createElement('span')

		markerElement.type = 'button'
		markerElement.className = 'vn-map-marker'
		markerElement.dataset.markerIndex = String(store.index)
		markerElement.setAttribute('aria-label', store.title || 'Vizaje-Nica')

		markerNumber.className = 'vn-map-marker__number'
		markerNumber.textContent =
			store.number || String(store.index + 1).padStart(2, '0')

		markerElement.appendChild(markerNumber)

		markerElement.addEventListener('click', function () {
			selectStore(store.index, {
				moveMap: true,
				openMapOnMobile: false
			})
		})

		return markerElement
	}

	/**
	 * Обновляет active state списка и маркеров.
	 */
	function updateActiveState(index) {
		storeCards.forEach(function (card) {
			const cardIndex = Number(card.dataset.index)

			card.classList.toggle('is-active', cardIndex === index)

			const cardButton = card.querySelector('.vn-store-card__select')

			if (cardButton) {
				cardButton.setAttribute(
					'aria-pressed',
					cardIndex === index ? 'true' : 'false'
				)
			}
		})

		markers.forEach(function (markerData) {
			const isActive = markerData.index === index

			markerData.element.classList.toggle('is-active', isActive)

			markerData.element.setAttribute(
				'aria-pressed',
				isActive ? 'true' : 'false'
			)
		})
	}

	/**
	 * Заполняет панель выбранного магазина.
	 */
	function updatePanel(store) {
		if (!panel) {
			return
		}

		panel.classList.remove('is-hidden')

		if (panelNumber) {
			panelNumber.textContent =
				store.number || String(store.index + 1).padStart(2, '0')
		}

		if (panelTitle) {
			panelTitle.textContent = store.title
		}

		if (panelAddress) {
			panelAddress.textContent = store.address
			panelAddress.hidden = !store.address
		}

		if (panelTime) {
			panelTime.textContent = store.time || '—'
		}

		if (panelRoute) {
			const destination = encodeURIComponent(store.lat + ',' + store.lng)

			panelRoute.href =
				'https://www.google.com/maps/dir/?api=1' + '&destination=' + destination
		}
	}

	/**
	 * Перемещает карту к выбранному магазину.
	 */
	function focusStore(store, immediate) {
		const mapOptions = {
			center: [store.lng, store.lat],
			zoom:
				window.innerWidth <= MOBILE_BREAKPOINT
					? SELECTED_MOBILE_ZOOM
					: SELECTED_DESKTOP_ZOOM,
			essential: true
		}

		if (immediate) {
			map.jumpTo(mapOptions)
			return
		}

		map.flyTo(
			Object.assign({}, mapOptions, {
				speed: 1.05,
				curve: 1.25
			})
		)
	}

	/**
	 * Переключает mobile-режим Список / Карта.
	 */
	 function getMobileHeaderOffset() {
    	const styles = window.getComputedStyle(storesRoot)
    
    	const value = Number.parseFloat(
    		styles.getPropertyValue('--stores-mobile-header-height')
    	)
    
    	return Number.isFinite(value) ? value : 60
    }
    function scrollToMobileSection(behavior) {
    	const headerOffset = getMobileHeaderOffset()
    
    	const sectionTop =
    		storesRoot.getBoundingClientRect().top +
    		window.pageYOffset -
    		headerOffset
    
    	window.scrollTo({
    		top: Math.max(0, sectionTop),
    		behavior: behavior || 'smooth'
    	})
    }
    function setMobileView(view, options) {
    	const settings = Object.assign(
    		{
    			scrollToSection: false,
    			focusActiveStore: false,
    			scrollBehavior: 'smooth'
    		},
    		options || {}
    	)
    
    	const showMap = view === 'map'
    
    	storesRoot.classList.toggle('is-map-view', showMap)
    	storesRoot.classList.toggle('is-list-view', !showMap)
    
    	viewTabs.forEach(function (tab) {
    		const isActive = tab.dataset.storeView === view
    
    		tab.classList.toggle('is-active', isActive)
    		tab.setAttribute(
    			'aria-selected',
    			isActive ? 'true' : 'false'
    		)
    	})
    
    	/*
    	 * Сначала браузер применяет новый layout,
    	 * затем выполняем прокрутку и resize карты.
    	 */
    	window.requestAnimationFrame(function () {
    		window.requestAnimationFrame(function () {
    			if (settings.scrollToSection) {
    				scrollToMobileSection(settings.scrollBehavior)
    			}
    
    			if (!showMap) {
    				return
    			}
    
    			map.resize()
    
    			if (!settings.focusActiveStore) {
    				return
    			}
    
    			const activeStore = getStoreByIndex(activeStoreIndex)
    
    			if (activeStore) {
    				focusStore(activeStore, true)
    			}
    		})
    	})
    }

	/**
	 * Выбирает магазин.
	 */
	function selectStore(index, options) {
		const settings = Object.assign(
			{
				moveMap: true,
				openMapOnMobile: false,
				immediate: false
			},
			options || {}
		)

		const store = getStoreByIndex(index)

		if (!store) {
			return
		}

		activeStoreIndex = store.index

		updateActiveState(store.index)
		updatePanel(store)

		if (settings.openMapOnMobile && window.innerWidth <= MOBILE_BREAKPOINT) {
			setMobileView('map', {
				scrollToSection: true,
				focusActiveStore: settings.moveMap
			})

			return
		}

		if (settings.moveMap) {
			focusStore(store, settings.immediate)
		}
	}

	/**
	 * Обработчики карточек магазинов.
	 */
	storeCards.forEach(function (card) {
		const selectButton = card.querySelector('.vn-store-card__select')

		if (!selectButton) {
			return
		}

		selectButton.addEventListener('click', function () {
			selectStore(Number(card.dataset.index), {
				moveMap: true,
				openMapOnMobile: true
			})
		})
	})

	/**
	 * Mobile tabs.
	 */
    viewTabs.forEach(function (tab) {
	tab.addEventListener('click', function () {
		const view = tab.dataset.storeView

		    setMobileView(view, {
    			scrollToSection: true,
    			focusActiveStore: view === 'map',
    			scrollBehavior: 'smooth'
		    })
	    })
    })

	/**
	 * Закрытие панели.
	 */
	if (panelClose && panel) {
		panelClose.addEventListener('click', function () {
			panel.classList.add('is-hidden')
		})
	}

	/**
	 * Инициализация маркеров после загрузки карты.
	 */
	map.on('load', function () {
		stores.forEach(function (store) {
			const markerElement = createMarkerElement(store)

			const marker = new maplibregl.Marker({
				element: markerElement,
				anchor: 'bottom'
			})
				.setLngLat([store.lng, store.lat])
				.addTo(map)

			markers.push({
				index: store.index,
				element: markerElement,
				marker: marker
			})
		})

		map.jumpTo({
			center: CHISINAU_CENTER,
			zoom:
				window.innerWidth <= MOBILE_BREAKPOINT
					? DEFAULT_MOBILE_ZOOM
					: DEFAULT_DESKTOP_ZOOM
		})

		selectStore(stores[0].index, {
			moveMap: false,
			openMapOnMobile: false,
			immediate: true
		})

		if (window.innerWidth <= MOBILE_BREAKPOINT) {
			setMobileView('list')
		}
	})

	/**
	 * Логирование ошибок карты только для DEV.
	 */
	map.on('error', function (event) {
		if (event && event.error) {
			console.error('[Stores map]', event.error)
		}
	})

	/**
	 * Корректное изменение размеров.
	 */
	window.addEventListener('resize', function () {
		window.clearTimeout(resizeTimer)

		resizeTimer = window.setTimeout(function () {
			map.resize()

			if (window.innerWidth > MOBILE_BREAKPOINT) {
				storesRoot.classList.remove('is-map-view')
				storesRoot.classList.add('is-list-view')

				viewTabs.forEach(function (tab) {
					const isList = tab.dataset.storeView === 'list'

					tab.classList.toggle('is-active', isList)
				})
			}
		}, 120)
	})
})
