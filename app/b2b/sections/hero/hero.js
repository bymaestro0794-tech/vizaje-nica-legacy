import { springs, reducedMotion, canHover, interactive, splitWords } from "../../js/lib/motion.js";

const { animate } = Motion;

// Медленная пружина без перелёта для фона: scale не уходит ниже 1 и не открывает края
const backgroundSpring = { type: "spring", stiffness: 60, damping: 16 };

function playIntro(hero) {
	const title = hero.querySelector(".hero__title");
	const words = splitWords(title);
	title.style.opacity = "1";

	const [eyebrow, description, cta, tagline] = [".hero__eyebrow", ".hero__description", ".hero__cta", ".hero__tagline"].map(
		(selector) => hero.querySelector(selector),
	);
	const images = hero.querySelectorAll(".hero__bags img, .hero__boxes img");
	const background = hero.querySelector(".hero__bg");

	if (reducedMotion) {
		animate([background, eyebrow, description, cta, tagline, ...images], { opacity: 1 }, { duration: 0.15 });
		return;
	}

	animate(background, { opacity: [0, 1], scale: [1.15, 1] }, backgroundSpring);
	words.forEach((word) => (word.style.transform = "translateY(100%)"));

	const blurIn = (el, delay) =>
		animate(el, { opacity: [0, 1], y: [20, 0], filter: ["blur(10px)", "blur(0px)"] }, { ...springs.responsive, delay }).then(
			() => (el.style.filter = ""),
		);

	blurIn(eyebrow, 0);
	words.forEach((word, i) => animate(word, { y: ["100%", "0%"] }, { ...springs.responsive, delay: 0.08 + i * 0.03 }));
	blurIn(description, 0.16);
	blurIn(cta, 0.24);
	blurIn(tagline, 0.32);

	animate(images, { opacity: [0, 1], scale: [0.9, 1], y: [40, 0] }, { ...springs.soft, delay: 0.2 });
}

function float(hero) {
	const [bags, boxes] = hero.querySelectorAll("[data-hero-float]");
	const loop = { duration: 3, ease: "sine.inOut", repeat: -1, yoyo: true };
	gsap.fromTo(bags, { y: -8 }, { y: 8, ...loop });
	gsap.fromTo(boxes, { y: 8 }, { y: -8, ...loop });
}

function mouseParallax(hero) {
	const [bags, boxes] = hero.querySelectorAll("[data-hero-mouse]");

	hero.addEventListener("pointermove", (event) => {
		const rect = hero.getBoundingClientRect();
		const nx = ((event.clientX - rect.left) / rect.width - 0.5) * 2;
		const ny = ((event.clientY - rect.top) / rect.height - 0.5) * 2;
		animate(bags, { x: nx * 12, y: ny * 12 }, springs.magnetic);
		animate(boxes, { x: -nx * 20, y: -ny * 20 }, springs.magnetic);
	});

	hero.addEventListener("pointerleave", () => {
		animate([bags, boxes], { x: 0, y: 0 }, springs.magnetic);
	});
}

function scrollParallax(hero) {
	const [bags, boxes] = hero.querySelectorAll("[data-hero-scroll]");
	const text = hero.querySelector(".hero__text");

	gsap.matchMedia().add({ mobile: "(max-width: 767px)", desktop: "(min-width: 768px)" }, ({ conditions }) => {
		const k = conditions.mobile ? 0.5 : 1;
		gsap
			.timeline({ scrollTrigger: { trigger: hero, start: "top top", end: "bottom top", scrub: 1 } })
			.to(bags, { y: -60 * k, ease: "none" }, 0)
			.to(boxes, { y: -120 * k, ease: "none" }, 0)
			.to(text, { opacity: 0, scale: 0.97, ease: "none" }, 0);
	});
}

export function initHero() {
	const hero = document.querySelector(".hero");
	if (!hero) return;

	playIntro(hero);

	const cta = hero.querySelector(".hero__cta");
	const arrow = cta.querySelector(".btn__arrow");
	interactive(cta, {
		hoverScale: 1.03,
		onHover: (isHovered) => animate(arrow, { x: isHovered ? 4 : 0 }, springs.responsive),
	});

	if (reducedMotion) return;
	float(hero);
	scrollParallax(hero);
	if (canHover) mouseParallax(hero);
}
