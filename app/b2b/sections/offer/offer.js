import { reducedMotion, splitWords } from "../../js/lib/motion.js";

// Слова заголовка поднимаются из маски по мере скролла
function titleWords(section) {
	const words = splitWords(section.querySelector(".offer__title"));
	gsap.fromTo(
		words,
		{ yPercent: 100 },
		{
			yPercent: 0,
			ease: "none",
			stagger: 0.1,
			scrollTrigger: { trigger: section.querySelector(".offer__title"), start: "top 80%", end: "top 40%", scrub: 1 },
		},
	);
}

// Главный эффект: четыре части пазла разведены, повёрнуты и размыты, по скроллу сходятся в один объект.
// Параллельно подсвечивается строка списка 01–04, соответствующая части
function assemble(section) {
	const card = section.querySelector(".offer-card");
	const pieces = ["top", "right", "bottom", "left"].map((name) => card.querySelector(`.offer-card__piece--${name}`));
	const offsets = [
		{ x: 0, y: -40, rotation: -6 },
		{ x: 40, y: 0, rotation: 6 },
		{ x: 0, y: 40, rotation: -6 },
		{ x: -40, y: 0, rotation: 6 },
	];
	const rows = card.querySelectorAll(".offer-card__row");
	const nums = card.querySelectorAll(".offer-card__num");
	// GSAP интерполирует только настоящие цвета, не var(--…)
	const tokens = getComputedStyle(document.documentElement);
	const numFrom = tokens.getPropertyValue("--text-secondary").trim();
	const numTo = tokens.getPropertyValue("--accent-blue").trim();

	gsap.set(card.querySelector(".offer-card__whole"), { opacity: 0 });

	const tl = gsap.timeline({
		defaults: { ease: "power2.out" },
		scrollTrigger: { trigger: card, start: "top 70%", end: "bottom 40%", scrub: 1 },
	});

	pieces.forEach((piece, i) => {
		const at = i * 0.15;
		tl.fromTo(
			piece,
			{ ...offsets[i], opacity: 0.6, filter: "blur(6px)" },
			{ x: 0, y: 0, rotation: 0, opacity: 1, filter: "blur(0px)", duration: 0.55 },
			at,
		);
		tl.fromTo(rows[i], { opacity: 0.4 }, { opacity: 1, duration: 0.3 }, at + 0.1);
		tl.fromTo(nums[i], { color: numFrom }, { color: numTo, duration: 0.3 }, at + 0.1);
	});

	tl.fromTo(card.querySelector(".offer-card__shadow"), { scale: 0.8, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.4 }, 0.6)
		.fromTo(card.querySelector(".offer-card__glow"), { opacity: 0 }, { opacity: 1, duration: 0.2 }, 0.8)
		.to(card.querySelector(".offer-card__whole"), { opacity: 1, duration: 0.05 }, 0.95);
}

// «Built for long-term growth»: линии прорисовываются слева, заголовки и описания появляются следом
function principles(section) {
	const block = section.querySelector(".offer-growth__principles");
	const lines = block.querySelectorAll(".principle__line");
	const titles = block.querySelectorAll(".principle__title");
	const texts = block.querySelectorAll(".principle__text");

	gsap.set(lines, { scaleX: 0 });
	gsap.set([...titles, ...texts], { opacity: 0, y: 16 });

	gsap
		.timeline({ scrollTrigger: { trigger: block, start: "top 80%", once: true } })
		.to(lines, { scaleX: 1, duration: 0.8, ease: "power2.out", stagger: 0.12 })
		.to(titles, { opacity: 1, y: 0, duration: 0.6, ease: "power2.out", stagger: 0.12 }, 0.2)
		.to(texts, { opacity: 1, y: 0, duration: 0.6, ease: "power2.out", stagger: 0.12 }, 0.4);
}

export function initOffer() {
	const section = document.querySelector(".offer");
	if (!section || reducedMotion) return;

	titleWords(section);
	assemble(section);
	principles(section);
}
