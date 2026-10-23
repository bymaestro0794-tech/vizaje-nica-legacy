document.querySelectorAll('.shop-by-type__carousel').forEach(carousel => {
	const track = carousel.querySelector('[data-type-track]')
	const previous = carousel.querySelector('[data-type-scroll="prev"]')
	const next = carousel.querySelector('[data-type-scroll="next"]')

	if (!track || !previous || !next) return

	const updateControls = () => {
		const maxScroll = track.scrollWidth - track.clientWidth
		const canScroll = maxScroll > 2

		previous.hidden = !canScroll
		next.hidden = !canScroll
		previous.disabled = track.scrollLeft <= 2
		next.disabled = track.scrollLeft >= maxScroll - 2
	}

	previous.addEventListener('click', () => {
		track.scrollBy({ left: -track.clientWidth * 0.8, behavior: 'smooth' })
	})

	next.addEventListener('click', () => {
		track.scrollBy({ left: track.clientWidth * 0.8, behavior: 'smooth' })
	})

	track.addEventListener('scroll', updateControls, { passive: true })
	window.addEventListener('resize', updateControls, { passive: true })

	if ('ResizeObserver' in window) {
		new ResizeObserver(updateControls).observe(track)
	}

	updateControls()
})
