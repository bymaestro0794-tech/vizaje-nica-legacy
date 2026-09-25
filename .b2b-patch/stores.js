import { springs, reducedMotion, canHover, interactive } from "../../js/lib/motion.js";

const { animate, inView, stagger } = Motion;

const stores = [
	{ name: "Malldova", address: "str. Arborilor 21", image: "./img/stores/store-malldova.png" },
	{ name: "Port Mall", address: "str. Mihai Sadoveanu 42/6", image: "./img/stores/store-port-mall.png" },
	{ name: "Oasis", address: "str. Bogdan Voievod 1/C", image: "./img/stores/store-oasis.png" },
	{ name: "Sun City 1", address: "str. Puskin 32", image: "./img/stores/store-sun-city-1.png" },
	{ name: "Unic", address: "bul. Stefan cel Mare 8", image: "./img/stores/store-unic.png" },
	{ name: "Soiuz", address: "str. Alecu Russo 1/5", image: "./img/stores/store-soiuz.png" },
];

const mapsUrl = ({ address }) =>
	`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`Vizaje-Nica, ${address}, Chisinau`)}`;

function createCard(store) {
	const img = document.createElement("img");
	img.src = store.image;
	img.alt = `Vizaje-Nica store at ${store.name}`;
	img.loading = "lazy";
	img.draggable = false;
	img.addEventListener("error", () => img.remove());

	const badge = document.createElement("span");
	badge.className = "store-card__badge";
	badge.textContent = "Chisinau";

	const media = document.createElement("div");
	media.className = "store-card__media";
	media.append(img, badge);

	const name = document.createElement("strong");
	name.textContent = `${store.name}.`;
	const text = document.createElement("p");
	text.className = "store-card__text";
	text.append(name, " One of our 12 beauty stores in Chisinau.");

	const link = document.createElement("a");
	link.className = "store-card__link";
	link.href = mapsUrl(store);
	link.target = "_blank";
	link.rel = "noopener";
	link.textContent = "Visit the store ›";

	const caption = document.createElement("div");
	caption.className = "store-card__caption";
	caption.append(text, link);

	// Карточка ведёт активное состояние (scale, opacity), внутренний слой — появление (x, opacity)
	const inner = document.createElement("div");
	inner.className = "store-card__inner";
	inner.append(media, caption);

	const card = document.createElement("article");
	card.className = "store-card";
	card.append(inner);
	return card;
}

function initSlider(track, prev, next) {
	const cards = [...track.children];
	let scrollAnimation = null;
	let activeIndex = -1;

	const inset = () => parseFloat(getComputedStyle(track).paddingLeft);
	const maxScroll = () => track.scrollWidth - track.clientWidth;
	// offsetLeft не зависит от scale карточки, в отличие от getBoundingClientRect
	const targetOf = (i) => Math.min(maxScroll(), Math.max(0, Math.round(cards[i].offsetLeft - inset())));
	const nearestIndex = (x) => {
		let best = 0;
		cards.forEach((_, i) => {
			if (Math.abs(targetOf(i) - x) < Math.abs(targetOf(best) - x)) best = i;
		});
		return best;
	};

	// Пока трек анимируется, scroll-snap выключен, иначе браузер тянет его к своей точке
	const scrollToX = (x, options) => {
		scrollAnimation?.stop();
		if (reducedMotion) {
			track.scrollLeft = x;
			return;
		}
		track.classList.add("is-animating");
		scrollAnimation = animate(track.scrollLeft, x, {
			...options,
			onUpdate: (value) => (track.scrollLeft = value),
			onComplete: () => track.classList.remove("is-animating"),
		});
	};

	const go = (direction) => {
		const index = Math.min(cards.length - 1, Math.max(0, nearestIndex(track.scrollLeft) + direction));
		scrollToX(targetOf(index), springs.responsive);
	};

	const setActive = (index) => {
		if (index === activeIndex) return;
		activeIndex = index;
		cards.forEach((card, i) =>
			animate(
				card,
				{ scale: i === index ? 1 : 0.96, opacity: i === index ? 1 : 0.6 },
				reducedMotion ? { duration: 0 } : springs.responsive,
			),
		);
	};

	// Фото сдвигается на ±5% ширины, пока карточка проходит через центр экрана
	const parallax = () => {
		const center = window.innerWidth / 2;
		cards.forEach((card) => {
			const img = card.querySelector(".store-card__media img");
			if (!img) return;
			const rect = card.getBoundingClientRect();
			const progress = gsap.utils.clamp(-1, 1, (rect.left + rect.width / 2 - center) / window.innerWidth);
			gsap.set(img, { x: -progress * rect.width * 0.05 });
		});
	};

	const updateArrows = () => {
		prev.disabled = track.scrollLeft <= 1;
		next.disabled = track.scrollLeft >= maxScroll() - 1;
	};

	let frameRequested = false;
	const onScroll = () => {
		if (frameRequested) return;
		frameRequested = true;
		requestAnimationFrame(() => {
			frameRequested = false;
			setActive(nearestIndex(track.scrollLeft));
			if (!reducedMotion) parallax();
			updateArrows();
		});
	};

	prev.addEventListener("click", () => go(-1));
	next.addEventListener("click", () => go(1));
	interactive(prev);
	interactive(next);

	track.tabIndex = 0;
	track.addEventListener("keydown", (event) => {
		if (event.key !== "ArrowRight" && event.key !== "ArrowLeft") return;
		event.preventDefault();
		go(event.key === "ArrowRight" ? 1 : -1);
	});

	track.addEventListener("scroll", onScroll, { passive: true });
	window.addEventListener("resize", onScroll);
	onScroll();

	if (canHover) initDrag(track, { scrollToX, targetOf, nearestIndex, stop: () => scrollAnimation?.stop() });
}

// Desktop: перетаскивание мышью с инерцией, после отпускания пружина 400/40 к ближайшей карточке
function initDrag(track, { scrollToX, targetOf, nearestIndex, stop }) {
	let dragging = false;
	let moved = false;
	let startX = 0;
	let startScroll = 0;
	let lastX = 0;
	let lastTime = 0;
	let velocity = 0;

	track.addEventListener("pointerdown", (event) => {
		if (event.pointerType !== "mouse" || event.button !== 0) return;
		stop();
		track.classList.remove("is-animating");
		dragging = true;
		moved = false;
		startX = lastX = event.clientX;
		startScroll = track.scrollLeft;
		lastTime = performance.now();
		velocity = 0;
	});

	track.addEventListener("pointermove", (event) => {
		if (!dragging) return;
		if (!moved && Math.abs(event.clientX - startX) > 5) {
			// захват только после реального сдвига, чтобы обычный клик по ссылке работал
			moved = true;
			track.classList.add("is-dragging");
			track.setPointerCapture(event.pointerId);
		}
		if (!moved) return;
		const now = performance.now();
		velocity = ((event.clientX - lastX) / Math.max(now - lastTime, 1)) * 1000;
		lastX = event.clientX;
		lastTime = now;
		track.scrollLeft = startScroll - (event.clientX - startX);
	});

	const end = () => {
		if (!dragging) return;
		dragging = false;
		if (!moved) return;
		track.classList.remove("is-dragging");
		const projected = track.scrollLeft - velocity * 0.25;
		scrollToX(targetOf(nearestIndex(projected)), { ...springs.precise, velocity: -velocity });
	};

	track.addEventListener("pointerup", end);
	track.addEventListener("pointercancel", end);

	// После перетаскивания клик по ссылке «Visit the store» не должен срабатывать
	track.addEventListener(
		"click",
		(event) => {
			if (!moved) return;
			event.preventDefault();
			event.stopPropagation();
			moved = false;
		},
		true,
	);
}

function reveal(track) {
	const inners = track.querySelectorAll(".store-card__inner");
	const stop = inView(
		track,
		() => {
			stop();
			if (reducedMotion) animate(inners, { opacity: 1 }, { duration: 0.15 });
			else animate(inners, { opacity: [0, 1], x: [80, 0] }, { ...springs.responsive, delay: stagger(0.1) });
		},
		{ margin: "0px 0px -10% 0px" },
	);
}

export function initStores() {
	const track = document.querySelector("#storesTrack");
	if (!track) return;

	track.append(...stores.map(createCard));

	initSlider(track, document.querySelector(".stores__arrow--prev"), document.querySelector(".stores__arrow--next"));
	reveal(track);
}
