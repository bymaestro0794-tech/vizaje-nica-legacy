import { springs, reducedMotion, canHover, revealOnView } from "../../js/lib/motion.js";

const { animate, hover } = Motion;

export function initFooter() {
	const footer = document.querySelector(".footer");
	if (!footer) return;

	revealOnView(
		footer,
		[...footer.querySelectorAll(".footer__brand, .footer__col"), footer.querySelector(".footer__bottom")],
		{ y: 20, blur: 0, staggerStep: 0.06 },
	);

	if (!canHover || reducedMotion) return;
	footer.querySelectorAll(".footer__col a").forEach((link) => {
		const line = document.createElement("span");
		line.className = "footer__underline";
		line.setAttribute("aria-hidden", "true");
		link.append(line);
		hover(link, () => {
			animate(line, { scaleX: 1 }, springs.responsive);
			return () => animate(line, { scaleX: 0 }, springs.responsive);
		});
	});
}
