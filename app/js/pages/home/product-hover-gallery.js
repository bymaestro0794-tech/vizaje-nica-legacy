const hasFinePointer = window.matchMedia(
	'(hover: hover) and (pointer: fine)'
).matches

if (hasFinePointer) {
	document
		.querySelectorAll(
			'.home-products .head-product__image[data-home-hover-gallery]'
		)
		.forEach(imageLink => {
			let gallery = []

			try {
				gallery = JSON.parse(
					imageLink.dataset.homeHoverGallery || '[]'
				)
			} catch (error) {
				return
			}

			if (!Array.isArray(gallery)) return

			gallery = gallery
				.filter(source => typeof source === 'string' && source.trim())
				.slice(0, 4)

			if (gallery.length < 2) return

			const primaryImage = imageLink.querySelector(
				'[data-home-hover-primary]'
			)

			if (!primaryImage) return

			let imageLayers = []
			let loadPromise = null
			let pointerInside = false
			let lastPointerX = 0

			const setActiveImage = index => {
				const activeIndex = Math.max(
					0,
					Math.min(index, imageLayers.length)
				)

				primaryImage.classList.toggle(
					'is-gallery-hidden',
					activeIndex > 0
				)

				imageLayers.forEach((image, layerIndex) => {
					image.classList.toggle(
						'is-active',
						layerIndex + 1 === activeIndex
					)
				})
			}

			const getImageIndex = clientX => {
				const bounds = imageLink.getBoundingClientRect()
				if (!bounds.width) return 0

				const relativeX = Math.max(
					0,
					Math.min(bounds.width - 1, clientX - bounds.left)
				)

				return Math.floor(
					(relativeX / bounds.width) * (imageLayers.length + 1)
				)
			}

			const updateFromPointer = () => {
				if (!imageLayers.length) return
				setActiveImage(getImageIndex(lastPointerX))
			}

			const preloadImage = source => new Promise(resolve => {
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

			const loadGallery = () => {
				if (loadPromise) return loadPromise

				loadPromise = Promise.all(
					gallery.slice(1).map(preloadImage)
				).then(sources => {
					sources
						.filter(Boolean)
						.forEach(source => {
							const image = document.createElement('img')
							image.src = source
							image.alt = ''
							image.draggable = false
							image.setAttribute('aria-hidden', 'true')
							image.dataset.homeHoverLayer = ''
							imageLink.append(image)
							imageLayers.push(image)
						})

						if (pointerInside) {
							updateFromPointer()
						} else {
							setActiveImage(0)
						}
				})
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
		})
}
