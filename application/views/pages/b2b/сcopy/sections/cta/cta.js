import { springs, reducedMotion, splitWords, interactive, magnetic } from "../../js/lib/motion.js";

const { animate, inView, stagger } = Motion;

export function initCta() {
	const section = document.querySelector(".cta");
	if (!section) return;

	const title = section.querySelector(".cta__title");
	const lead = section.querySelector(".cta__lead");
	const button = section.querySelector(".cta__button");
	const arrow = button.querySelector(".btn__arrow");
	const words = splitWords(title);

	if (reducedMotion) {
		animate([title, lead, button], { opacity: 1 }, { duration: 0.15 });
	} else {
		title.style.opacity = "1";
		words.forEach((word) => (word.style.transform = "translateY(100%)"));
		const stop = inView(
			section,
			() => {
				stop();
				animate(words, { y: ["100%", "0%"] }, { ...springs.responsive, delay: stagger(0.08) });
				animate([lead, button], { opacity: [0, 1], y: [16, 0] }, { ...springs.responsive, delay: stagger(0.08, { startDelay: 0.3 }) });
			},
			{ margin: "0px 0px -15% 0px" },
		);
	}

	interactive(button, {
		hoverScale: 1.03,
		onHover: (isHovered) => animate(arrow, { x: isHovered ? 4 : 0 }, springs.responsive),
	});
	magnetic(button);
}
