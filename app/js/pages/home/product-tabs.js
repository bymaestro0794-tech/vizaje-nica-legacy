document.querySelectorAll('[data-home-product-tabs]').forEach(section => {
	const tabs = [...section.querySelectorAll('[data-home-products-tab]')]
	const panels = [...section.querySelectorAll('[data-home-products-panel]')]

	if (!tabs.length || !panels.length) {
		return
	}

	const activateTab = (key, moveFocus = false) => {
		tabs.forEach(tab => {
			const isActive = tab.dataset.homeProductsTab === key

			tab.classList.toggle('is-active', isActive)
			tab.setAttribute('aria-selected', String(isActive))
			tab.tabIndex = isActive ? 0 : -1

			if (isActive && moveFocus) {
				tab.focus()
			}
		})

		panels.forEach(panel => {
			const isActive = panel.dataset.homeProductsPanel === key

			panel.classList.toggle('is-active', isActive)
			panel.setAttribute('aria-hidden', String(!isActive))
			panel.hidden = !isActive
			panel.inert = !isActive

			if (isActive) {
				const slider = panel.querySelector('[data-products-slider]')
				window.VNProductsSlider?.reveal(slider)
				window.VNProductsSlider?.refresh(slider)
			}
		})
	}

	tabs.forEach((tab, index) => {
		tab.addEventListener('click', () => {
			activateTab(tab.dataset.homeProductsTab)
		})

		tab.addEventListener('keydown', event => {
			let nextIndex = index

			if (event.key === 'ArrowRight') {
				nextIndex = (index + 1) % tabs.length
			} else if (event.key === 'ArrowLeft') {
				nextIndex = (index - 1 + tabs.length) % tabs.length
			} else if (event.key === 'Home') {
				nextIndex = 0
			} else if (event.key === 'End') {
				nextIndex = tabs.length - 1
			} else {
				return
			}

			event.preventDefault()
			activateTab(tabs[nextIndex].dataset.homeProductsTab, true)
		})
	})
})
