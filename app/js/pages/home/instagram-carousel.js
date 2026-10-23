const carousel = document.querySelector('[data-instagram-carousel]')

if (carousel) {
	const track = carousel.querySelector('[data-instagram-track]')
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
	const posts = [...track.querySelectorAll('[data-instagram-post]')]
	const pixelsPerSecond = 92
	let lastFrameTime = 0
	let animationFrame = 0
	let pointerIsDown = false
	let isVisible = !('IntersectionObserver' in window)

	const loadInstagramEmbed = (post) => {
		if (post.dataset.embedLoaded === 'true') return

		const permalink = post.dataset.permalink
		const fallback = post.querySelector('.home-instagram__fallback')
		const embed = document.createElement('blockquote')
		embed.className = 'instagram-media'
		embed.dataset.instgrmPermalink = permalink
		embed.dataset.instgrmVersion = '14'
		embed.style.background = '#fff'
		embed.style.border = '0'
		embed.style.borderRadius = '3px'
		embed.style.boxShadow = 'none'
		embed.style.margin = '0'
		embed.style.maxWidth = '100%'
		embed.style.minWidth = '0'
		embed.style.padding = '0'
		embed.style.width = '100%'
		if (fallback) embed.append(fallback)
		post.replaceChildren(embed)
		post.dataset.embedLoaded = 'true'

		if (window.instgrm?.Embeds) {
			window.instgrm.Embeds.process()
			return
		}

		let script = document.querySelector('script[data-instagram-embed-script]')
		if (script) return

		script = document.createElement('script')
		script.async = true
		script.src = 'https://www.instagram.com/embed.js'
		script.dataset.instagramEmbedScript = 'true'
		script.onload = () => window.instgrm?.Embeds?.process()
		document.body.append(script)
	}

	if ('IntersectionObserver' in window) {
		const embedObserver = new IntersectionObserver((entries, observer) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return
				loadInstagramEmbed(entry.target)
				observer.unobserve(entry.target)
			})
		}, { rootMargin: '100% 0px' })

		posts.forEach((post) => embedObserver.observe(post))
	} else {
		posts.slice(0, 3).forEach(loadInstagramEmbed)
	}

	const animate = (time) => {
		animationFrame = 0
		if (document.hidden || reducedMotion.matches || pointerIsDown || track.matches(':focus-within')) {
			lastFrameTime = 0
			return
		}

		if (lastFrameTime) {
			const elapsedSeconds = Math.min((time - lastFrameTime) / 1000, 0.05)
			track.scrollLeft += pixelsPerSecond * elapsedSeconds

			const firstPost = track.firstElementChild
			if (firstPost) {
				const styles = getComputedStyle(track)
				const gap = parseFloat(styles.columnGap) || parseFloat(styles.gap) || 0
				const loopDistance = firstPost.getBoundingClientRect().width + gap
				if (loopDistance > 0 && track.scrollLeft >= loopDistance) {
					track.append(firstPost)
					track.scrollLeft -= loopDistance
				}
			}
		}

		lastFrameTime = time
		animationFrame = requestAnimationFrame(animate)
	}

	const startAnimation = () => {
		if (reducedMotion.matches || document.hidden || !isVisible || animationFrame) return
		lastFrameTime = 0
		animationFrame = requestAnimationFrame(animate)
	}

	const stopAnimation = () => {
		if (animationFrame) cancelAnimationFrame(animationFrame)
		animationFrame = 0
		lastFrameTime = 0
	}

	track.addEventListener('pointerdown', () => {
		pointerIsDown = true
		stopAnimation()
	})
	window.addEventListener('pointerup', () => {
		pointerIsDown = false
		startAnimation()
	})
	track.addEventListener('focusin', stopAnimation)
	track.addEventListener('focusout', () => requestAnimationFrame(startAnimation))
	document.addEventListener('visibilitychange', () => document.hidden ? stopAnimation() : startAnimation())
	reducedMotion.addEventListener?.('change', () => reducedMotion.matches ? stopAnimation() : startAnimation())
	if ('IntersectionObserver' in window) {
		const visibilityObserver = new IntersectionObserver(([entry]) => {
			isVisible = entry.isIntersecting
			isVisible ? startAnimation() : stopAnimation()
		}, { rootMargin: '120px 0px' })
		visibilityObserver.observe(carousel)
	}
	startAnimation()
}
