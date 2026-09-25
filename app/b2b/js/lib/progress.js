import { reducedMotion } from "./motion.js";

const { animate } = Motion;

// Полоса догоняет прогресс скролла с пружинным сглаживанием 200/30
const smoothing = { type: "spring", stiffness: 200, damping: 30 };

export function initProgress(lenis) {
	const bar = document.querySelector(".scroll-progress");
	if (!bar) return;
	lenis.on("scroll", ({ progress }) => {
		animate(bar, { scaleX: progress }, reducedMotion ? { duration: 0 } : smoothing);
	});
}
