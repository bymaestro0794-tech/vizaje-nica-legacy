(() => {
	'use strict'

	const selector = 'a.head-product__image[data-catalog-hover-gallery]'
	const supportsHover = window.matchMedia
		&& window.matchMedia('(hover: hover) and (pointer: fine)').matches

	if (!supportsHover) return

	const initialized = new WeakSet()

	const preload = source => new Promise(resolve => {
		const image = new Image()
		let settled = false

		const finish = () => {
			if (settled) return
			settled = true
			resolve(image.naturalWidth > 0 ? source : null)
		}

		image.decoding = 'async'
		image.onload = finish
		image.onerror = finish
		image.src = source

		if (image.complete) finish()
	})

	const initialize = imageLink => {
		if (initialized.has(imageLink)) return

		let gallery
		try {
			gallery = JSON.parse(imageLink.dataset.catalogHoverGallery || '[]')
		} catch (error) {
			return
		}

		if (!Array.isArray(gallery)) return

		gallery = gallery
			.filter(source => typeof source === 'string' && source.trim())
			.slice(0, 4)

		const primaryImage = imageLink.querySelector(
			'[data-catalog-hover-primary]'
		)

		if (gallery.length < 2 || !primaryImage) return

		initialized.add(imageLink)

		let layers = []
		let loading = null
		let pointerInside = false
		let lastPointerX = 0

		const setActiveImage = index => {
			const activeIndex = Math.max(0, Math.min(index, layers.length))

			primaryImage.classList.toggle(
				'is-gallery-hidden',
				activeIndex > 0
			)

		layers.forEach((image, layerIndex) => {
			image.classList.toggle('is-active', layerIndex + 1 === activeIndex)
		})
		}

		const updateFromPointer = () => {
			if (!layers.length) return

			const bounds = imageLink.getBoundingClientRect()
			if (!bounds.width) return

			const relativeX = Math.max(
				0,
				Math.min(bounds.width - 1, lastPointerX - bounds.left)
			)
			const index = Math.floor(
				(relativeX / bounds.width) * (layers.length + 1)
			)

			setActiveImage(index)
		}

		const loadGallery = () => {
			if (loading) return loading

			loading = Promise.all(gallery.slice(1).map(preload)).then(sources => {
				sources.filter(Boolean).forEach(source => {
					const image = document.createElement('img')
					image.src = source
					image.alt = ''
					image.draggable = false
					image.setAttribute('aria-hidden', 'true')
					image.dataset.catalogHoverLayer = ''
					imageLink.append(image)
					layers.push(image)
				})

				if (pointerInside) updateFromPointer()
				else setActiveImage(0)
			})

			return loading
		}

		imageLink.addEventListener('pointerenter', event => {
			if (event.pointerType !== 'mouse') return

			pointerInside = true
			lastPointerX = event.clientX
			loadGallery()
			updateFromPointer()
		})

		imageLink.addEventListener('pointermove', event => {
			if (event.pointerType !== 'mouse') return

			lastPointerX = event.clientX
			updateFromPointer()
		})

		imageLink.addEventListener('pointerleave', event => {
			if (event.pointerType !== 'mouse') return

			pointerInside = false
			setActiveImage(0)
		})
	}

	const initializeWithin = root => {
		if (root instanceof Element && root.matches(selector)) {
			initialize(root)
		}

		if (root.querySelectorAll) {
			root.querySelectorAll(selector).forEach(initialize)
		}
	}

	initializeWithin(document)

	if (window.MutationObserver && document.documentElement) {
		new MutationObserver(records => {
			records.forEach(record => {
				record.addedNodes.forEach(node => {
					if (node.nodeType === Node.ELEMENT_NODE) {
						initializeWithin(node)
					}
				})
			})
		}).observe(document.documentElement, {
			childList: true,
			subtree: true,
		})
	}
})()
