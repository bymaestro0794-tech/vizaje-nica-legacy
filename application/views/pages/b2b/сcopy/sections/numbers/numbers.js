import { springs, reducedMotion, interactive, revealOnView } from "../../js/lib/motion.js";

const { animate } = Motion;

// Видимая цифра считается анимацией, а скринридер сразу получает итоговое значение
function prepareCounter(valueEl) {
	const finalText = valueEl.textContent.trim();
	const target = parseInt(finalText.replace(/\D/g, ""), 10);
	const suffix = finalText.replace(/[\d,]/g, "");

	const count = document.createElement("span");
	count.className = "number-card__count";
	count.setAttribute("aria-hidden", "true");
	count.textContent = reducedMotion ? finalText : `0${suffix}`;

	const srText = document.createElement("span");
	srText.className = "visually-hidden";
	srText.textContent = finalText;

	valueEl.replaceChildren(count, srText);

	return () => {
		if (reducedMotion) return;
		const state = { value: 0 };
		gsap.to(state, {
			value: target,
			duration: 1.6,
			ease: "power3.out",
			snap: { value: 1 },
			onUpdate: () => (count.textContent = `${state.value.toLocaleString("en-US")}${suffix}`),
		});
	};
}

export function initNumbers() {
	const section = document.querySelector(".numbers");
	if (!section) return;

	const cards = section.querySelectorAll(".number-card");
	const counters = [...section.querySelectorAll(".number-card__value")].map(prepareCounter);

	ScrollTrigger.create({
		trigger: section.querySelector(".number-card"),
		start: "top 75%",
		once: true,
		onEnter: () => counters.forEach((run) => run()),
	});

	revealOnView(cards[0], cards, { y: 40, blur: 8, staggerStep: 0.1 });
	revealOnView(cards[0], section.querySelectorAll(".number-card__label"), { y: 12, blur: 0, staggerStep: 0.1, delay: 0.2 });

	cards.forEach((card) => {
		const value = card.querySelector(".number-card__value");
		interactive(card, {
			hoverScale: 1.01,
			tapScale: 1,
			onHover: (isHovered) => animate(value, { y: isHovered ? -4 : 0 }, springs.responsive),
		});
	});
}
