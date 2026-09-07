const sections = document.querySelectorAll('[data-home-categories]')

sections.forEach(section => {
	if (!('IntersectionObserver' in window)) {
		section.classList.add('is-visible')
		return
	}

	const observer = new IntersectionObserver(
		entries => {
			const entry = entries[0]

			if (!entry.isIntersecting) {
				return
			}

			section.classList.add('is-visible')
			observer.disconnect()
		},
		{
			threshold: 0.15
		}
	)

	observer.observe(section)
})
