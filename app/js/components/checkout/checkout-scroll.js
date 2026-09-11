/* ==========================================================================
   CHECKOUT V2 — SCROLL
   ========================================================================== */

window.VNCheckoutScroll = function (element) {
	if (!element) {
		return
	}

	const reducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches

	const headerOffset = window.innerWidth <= 767 ? 82 : 105

	const top =
		element.getBoundingClientRect().top + window.pageYOffset - headerOffset

	window.scrollTo({
		top: Math.max(0, top),
		behavior: reducedMotion ? 'auto' : 'smooth'
	})
}
