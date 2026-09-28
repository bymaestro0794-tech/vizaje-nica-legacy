import { reducedMotion } from "../../js/lib/motion.js";

const { animate } = Motion;

export function initHero() {
	const hero = document.querySelector(".hero");
	if (!hero) return;

	const media = hero.querySelector(".hero__media");
	if (!media || reducedMotion) return;

	animate(media, { opacity: [0.7, 1], scale: [1.025, 1] }, { duration: 1.5, ease: [0.22, 1, 0.36, 1] });
}
