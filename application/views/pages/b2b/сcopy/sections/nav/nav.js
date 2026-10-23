import { springs, reducedMotion, canHover, interactive, magnetic } from "../../js/lib/motion.js";

const { animate, hover, stagger } = Motion;

function playIntro(nav) {
	const items = [nav.querySelector(".nav__logo"), ...nav.querySelectorAll(".nav__links li"), nav.querySelector(".nav__partner")];
	if (reducedMotion) {
		animate(items, { opacity: 1 }, { duration: 0.15 });
		return;
	}
	animate(items, { opacity: [0, 1], y: [-12, 0] }, { ...springs.responsive, delay: stagger(0.05) });
}

// Подчёркивание видно, пока ссылка активна (секция на экране) или под курсором
function underline(link, target) {
	const line = document.createElement("span");
	line.className = "nav__underline";
	line.setAttribute("aria-hidden", "true");
	link.append(line);

	let active = false;
	let hovered = false;
	const update = () => animate(line, { scaleX: active || hovered ? 1 : 0 }, springs.responsive);

	if (canHover && !reducedMotion) {
		hover(link, () => {
			hovered = true;
			update();
			return () => {
				hovered = false;
				update();
			};
		});
	}

	if (!target) return;
	ScrollTrigger.create({
		trigger: target,
		start: "top center",
		end: "bottom center",
		onToggle: ({ isActive }) => {
			active = isActive;
			if (isActive) link.setAttribute("aria-current", "location");
			else link.removeAttribute("aria-current");
			update();
		},
	});
}

export function initNav(lenis) {
	const nav = document.querySelector(".nav");
	const backdrop = nav.querySelector(".nav__backdrop");
	const partner = nav.querySelector(".nav__partner");

	playIntro(nav);

	let hidden = false;
	let solid = false;

	const setHidden = (value) => {
		if (value === hidden) return;
		hidden = value;
		animate(nav, { y: hidden ? "-100%" : "0%" }, springs.responsive);
	};

	const setSolid = (value) => {
		if (value === solid) return;
		solid = value;
		animate(backdrop, { opacity: solid ? 1 : 0 }, reducedMotion ? { duration: 0.15 } : springs.responsive);
	};

	lenis.on("scroll", ({ scroll, direction }) => {
		setSolid(scroll > 40);
		if (reducedMotion) return;
		if (scroll > 80 && direction === 1) setHidden(true);
		else if (direction === -1 || scroll <= 80) setHidden(false);
	});

	nav.querySelectorAll(".nav__link").forEach((link) => underline(link, document.querySelector(link.getAttribute("href"))));
	underline(partner, null);

	interactive(partner);
	magnetic(partner);
}
