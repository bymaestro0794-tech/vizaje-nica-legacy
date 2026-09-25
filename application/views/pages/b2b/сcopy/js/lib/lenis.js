export function initLenis() {
	const lenis = new Lenis({ lerp: 0.1, smoothWheel: true, syncTouch: false });

	lenis.on("scroll", ScrollTrigger.update);

	gsap.ticker.add((time) => {
		lenis.raf(time * 1000);
	});
	gsap.ticker.lagSmoothing(0);

	document.addEventListener("click", (event) => {
		const link = event.target.closest('a[href^="#"]');
		if (!link) return;
		const hash = link.getAttribute("href");
		const target = hash === "#" ? 0 : document.querySelector(hash);
		if (target === null) return;
		event.preventDefault();
		lenis.scrollTo(target, { offset: -80 });
	});

	return lenis;
}
