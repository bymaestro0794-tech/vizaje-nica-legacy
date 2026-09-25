import { springs, reducedMotion } from "./motion.js";

const { animate, stagger } = Motion;

const dialog = document.querySelector("#modal");
const eyebrowEl = dialog.querySelector(".modal__eyebrow");
const titleEl = dialog.querySelector(".modal__title");
const panelEl = dialog.querySelector(".modal__panel");
const imageWrapEl = dialog.querySelector(".modal__image");
const imageEl = imageWrapEl.querySelector("img");
const mobileQuery = matchMedia("(max-width: 767px)");

let lenis;
let closing = false;
let dragged = false;

function animateIn(hasColumns) {
	if (reducedMotion) {
		animate(dialog, { opacity: [0, 1] }, { duration: 0.15 });
		return;
	}

	if (mobileQuery.matches) animate(dialog, { y: ["100%", "0%"] }, springs.precise);
	else animate(dialog, { opacity: [0, 1], scale: [0.96, 1], y: [24, 0] }, springs.precise);

	const rise = { opacity: [0, 1], y: [12, 0] };
	animate([eyebrowEl, titleEl], rise, { ...springs.responsive, delay: stagger(0.05) });
	animate(panelEl.querySelectorAll("p"), rise, {
		...springs.responsive,
		delay: stagger(hasColumns ? 0.03 : 0.05, { startDelay: 0.1 }),
	});
	if (!imageWrapEl.hidden) {
		animate(imageWrapEl, { opacity: [0, 1], scale: [1.04, 1] }, { ...springs.precise, delay: 0.2 });
	}
}

export function closeModal() {
	if (!dialog.open || closing) return;
	closing = true;
	dialog.classList.add("is-closing");

	const finish = () => {
		dialog.close();
		dialog.classList.remove("is-closing");
		closing = false;
	};

	if (reducedMotion) animate(dialog, { opacity: 0 }, { duration: 0.15 }).then(finish);
	else if (mobileQuery.matches) animate(dialog, { y: "100%" }, springs.precise).then(finish);
	else animate(dialog, { opacity: 0, scale: 0.96, y: 24 }, springs.precise).then(finish);
}

// Mobile: bottom sheet закрывается свайпом вниз, если модалка прокручена в самый верх
function initSwipeToClose() {
	let startY = 0;
	let lastY = 0;
	let lastTime = 0;
	let velocity = 0;
	let dy = 0;
	let tracking = false;

	dialog.addEventListener(
		"touchstart",
		(event) => {
			if (!mobileQuery.matches || dialog.scrollTop > 0 || reducedMotion) return;
			tracking = true;
			dragged = false;
			startY = lastY = event.touches[0].clientY;
			lastTime = performance.now();
			dy = 0;
			velocity = 0;
		},
		{ passive: true },
	);

	dialog.addEventListener(
		"touchmove",
		(event) => {
			if (!tracking) return;
			const y = event.touches[0].clientY;
			const now = performance.now();
			velocity = ((y - lastY) / Math.max(now - lastTime, 1)) * 1000;
			lastY = y;
			lastTime = now;
			dy = y - startY;
			if (dy <= 0) {
				dy = 0;
				return;
			}
			dragged = true;
			event.preventDefault();
			animate(dialog, { y: dy }, { duration: 0 });
		},
		{ passive: false },
	);

	dialog.addEventListener("touchend", () => {
		if (!tracking) return;
		tracking = false;
		if (dy > 120 || velocity > 500) closeModal();
		else if (dy > 0) animate(dialog, { y: 0 }, springs.precise);
	});
}

dialog.querySelector(".modal__close").addEventListener("click", closeModal);

dialog.addEventListener("click", (event) => {
	if (dragged) {
		dragged = false;
		return;
	}
	const rect = dialog.getBoundingClientRect();
	const insideDialog =
		event.clientX >= rect.left &&
		event.clientX <= rect.right &&
		event.clientY >= rect.top &&
		event.clientY <= rect.bottom;
	if (!insideDialog) closeModal();
});

dialog.addEventListener("cancel", (event) => {
	event.preventDefault();
	closeModal();
});

dialog.addEventListener("close", () => {
	document.documentElement.classList.remove("no-scroll");
	lenis?.start();
});

initSwipeToClose();

export function initModal(lenisInstance) {
	lenis = lenisInstance;
}

export function openModal({ eyebrow, title, items, columns, comingSoon, image }) {
	eyebrowEl.textContent = eyebrow;
	titleEl.textContent = title;

	panelEl.classList.toggle("modal__panel--columns", Boolean(columns));
	panelEl.replaceChildren();

	if (items) {
		panelEl.append(
			...items.map(({ label, text }) => {
				const p = document.createElement("p");
				const strong = document.createElement("strong");
				strong.textContent = label;
				p.append(strong, ` ${text}`);
				return p;
			}),
		);
	}

	if (columns) {
		const columnsEl = document.createElement("div");
		columnsEl.className = "modal__columns";
		columnsEl.append(
			...columns.map((names) => {
				const column = document.createElement("div");
				column.className = "modal__column";
				column.append(
					...names.map((name) => {
						const p = document.createElement("p");
						p.textContent = name;
						return p;
					}),
				);
				return column;
			}),
		);
		panelEl.append(columnsEl);

		if (comingSoon?.length) {
			const p = document.createElement("p");
			p.className = "modal__coming-soon";
			p.textContent = `Coming soon: ${comingSoon.join(", ")}.`;
			panelEl.append(p);
		}
	}

	imageWrapEl.hidden = !image;
	if (image) imageEl.src = image;

	dialog.scrollTop = 0;

	document.documentElement.classList.add("no-scroll");
	lenis?.stop();
	if (!dialog.open) {
		dialog.showModal();
		animateIn(Boolean(columns));
	}
}
