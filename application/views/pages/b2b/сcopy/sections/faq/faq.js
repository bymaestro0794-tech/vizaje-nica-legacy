import { springs, reducedMotion, splitWords, revealOnView } from "../../js/lib/motion.js";

const { animate, inView, stagger } = Motion;

function setIcon(toggle, isOpen, instant = false) {
	const icon = toggle.querySelector(".faq-item__icon");
	animate(icon, { rotate: isOpen ? 45 : 0 }, instant || reducedMotion ? { duration: 0 } : springs.responsive);
}

// Высота панели на пружине 300/30; после анимации высота снова auto, чтобы текст переносился при ресайзе
function open(toggle) {
	const panel = document.getElementById(toggle.getAttribute("aria-controls"));
	const answer = panel.querySelector(".faq-item__answer");
	toggle.setAttribute("aria-expanded", "true");
	setIcon(toggle, true);
	// если пункт ещё закрывается, стартуем с его текущей высоты, а не с нуля
	const from = panel.hidden ? 0 : panel.offsetHeight;
	panel.hidden = false;

	if (reducedMotion) return;
	animate(panel, { height: [from, panel.scrollHeight] }, springs.responsive).then(() => {
		panel.style.height = "auto";
		ScrollTrigger.refresh();
	});
	animate(answer, { opacity: [0, 1], filter: ["blur(6px)", "blur(0px)"] }, { ...springs.responsive, delay: 0.05 }).then(
		() => (answer.style.filter = ""),
	);
}

function close(toggle) {
	const panel = document.getElementById(toggle.getAttribute("aria-controls"));
	toggle.setAttribute("aria-expanded", "false");
	setIcon(toggle, false);

	if (reducedMotion) {
		panel.hidden = true;
		return;
	}
	animate(panel, { height: [panel.offsetHeight, 0] }, springs.responsive).then(() => {
		// за время анимации пункт могли открыть снова
		if (toggle.getAttribute("aria-expanded") === "true") return;
		panel.hidden = true;
		panel.style.height = "";
		ScrollTrigger.refresh();
	});
}

function reveal(section) {
	const title = section.querySelector(".faq__title");
	const words = splitWords(title);
	const aside = [section.querySelector(".faq__lead"), section.querySelector(".faq__link")];

	if (reducedMotion) {
		animate([title, ...aside], { opacity: 1 }, { duration: 0.15 });
	} else {
		title.style.opacity = "1";
		words.forEach((word) => (word.style.transform = "translateY(100%)"));
		const stop = inView(
			title,
			() => {
				stop();
				animate(words, { y: ["100%", "0%"] }, { ...springs.responsive, delay: stagger(0.08) });
				animate(aside, { opacity: [0, 1], y: [12, 0] }, { ...springs.responsive, delay: stagger(0.08, { startDelay: 0.2 }) });
			},
			{ margin: "0px 0px -10% 0px" },
		);
	}

	revealOnView(section.querySelector(".faq__items"), section.querySelectorAll(".faq-item"), {
		y: 20,
		blur: 0,
		staggerStep: 0.06,
	});
}

export function initFaq() {
	const section = document.querySelector(".faq");
	if (!section) return;
	const toggles = [...section.querySelectorAll(".faq-item__toggle")];

	toggles.forEach((toggle) => setIcon(toggle, toggle.getAttribute("aria-expanded") === "true", true));

	// Одновременно открыт только один вопрос
	toggles.forEach((toggle) => {
		toggle.addEventListener("click", () => {
			const isOpen = toggle.getAttribute("aria-expanded") === "true";
			if (isOpen) {
				close(toggle);
				return;
			}
			toggles.filter((other) => other.getAttribute("aria-expanded") === "true").forEach(close);
			open(toggle);
		});
	});

	reveal(section);
}
