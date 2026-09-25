import { brands, getLogoUrls } from "../../js/brands.js";
import { openModal } from "../../js/lib/modal.js";
import { springs, reducedMotion, canHover, interactive, revealOnView } from "../../js/lib/motion.js";

const { animate } = Motion;

const TILES_PER_ROW = { wide: 7, narrow: 4 };

const rows = [
	[
		{ key: "fragrances", size: "wide" },
		{ key: "niche", size: "narrow" },
	],
	[
		{ key: "makeup", size: "narrow" },
		{ key: "skincare", size: "wide" },
	],
];

function chunkColumns(items, columnCount = 3) {
	const size = Math.ceil(items.length / columnCount);
	const columns = [];
	for (let i = 0; i < columnCount; i++) {
		const column = items.slice(i * size, i * size + size).map((item) => item.name);
		if (column.length) columns.push(column);
	}
	return columns;
}

function createTile(item) {
	const tile = document.createElement("div");
	tile.className = "brand-tile";

	const showName = () => {
		const name = document.createElement("p");
		name.className = "brand-tile__name";
		name.textContent = item.name;
		tile.replaceChildren(name);
	};

	const urls = getLogoUrls(item);
	if (!urls.length) {
		showName();
		return tile;
	}

	const img = document.createElement("img");
	img.alt = item.name;
	img.loading = "lazy";
	let attempt = 0;
	img.addEventListener("error", () => {
		attempt++;
		if (attempt < urls.length) img.src = urls[attempt];
		else showName();
	});
	img.src = urls[0];
	tile.append(img);

	return tile;
}

function createCard({ key, size }) {
	const category = brands[key];

	const eyebrow = document.createElement("p");
	eyebrow.className = "brand-card__eyebrow";
	eyebrow.textContent = category.title;

	const headline = document.createElement("p");
	headline.className = "brand-card__headline";
	headline.textContent = category.headline;

	const text = document.createElement("div");
	text.className = "brand-card__text";
	text.append(eyebrow, headline);

	// Плитки разложены по рядам: ряды нужны для дрейфа при скролле, row/col — для волны при наведении
	const tiles = document.createElement("div");
	tiles.className = "brand-card__tiles";
	const perRow = TILES_PER_ROW[size];
	for (let start = 0; start < category.items.length; start += perRow) {
		const row = document.createElement("div");
		row.className = "brand-card__row";
		category.items.slice(start, start + perRow).forEach((item, col) => {
			const tile = createTile(item);
			tile.dataset.row = start / perRow;
			tile.dataset.col = col;
			row.append(tile);
		});
		tiles.append(row);
	}

	const logos = document.createElement("div");
	logos.className = "brand-card__logos";
	logos.append(tiles);

	const plusIcon = document.createElement("img");
	plusIcon.src = "./img/plus.svg";
	plusIcon.alt = "";
	const plus = document.createElement("span");
	plus.className = "brand-card__plus";
	plus.append(plusIcon);

	const card = document.createElement("button");
	card.type = "button";
	card.className = `brand-card brand-card--${size} brand-card--${key}`;
	card.append(text, logos, plus);

	card.addEventListener("click", () => {
		openModal({
			eyebrow: category.title,
			title: category.modalTitle,
			columns: chunkColumns(category.items),
			comingSoon: category.comingSoon,
		});
	});

	interactive(card, {
		hoverScale: 1.02,
		tapScale: 0.98,
		onHover: (isHovered) => animate(plus, { rotate: isHovered ? 90 : 0 }, springs.responsive),
	});
	if (canHover && !reducedMotion) tileWave(tiles);

	return card;
}

// Плитка под курсором 1.05, соседние 1.02; волна расходится с шагом 0.02 с
function tileWave(tilesEl) {
	const tiles = [...tilesEl.querySelectorAll(".brand-tile")];
	const current = new Map();

	const setScale = (tile, scale, delay) => {
		if (current.get(tile) === scale) return;
		current.set(tile, scale);
		animate(tile, { scale }, { ...springs.responsive, delay });
	};

	let lastHovered = null;

	tilesEl.addEventListener("pointerover", (event) => {
		const hovered = event.target.closest(".brand-tile");
		if (!hovered || hovered === lastHovered) return;
		lastHovered = hovered;
		const row = Number(hovered.dataset.row);
		const col = Number(hovered.dataset.col);
		tiles.forEach((tile) => {
			const distance = Math.max(Math.abs(tile.dataset.row - row), Math.abs(tile.dataset.col - col));
			setScale(tile, distance === 0 ? 1.05 : distance === 1 ? 1.02 : 1, distance * 0.02);
		});
	});

	tilesEl.addEventListener("pointerleave", () => {
		lastHovered = null;
		tiles.forEach((tile) => setScale(tile, 1, 0));
	});
}

// Ряды плиток дрейфуют при скролле: нечётные вправо, чётные влево
function drift(cards) {
	gsap.matchMedia().add({ mobile: "(max-width: 767px)", desktop: "(min-width: 768px)" }, ({ conditions }) => {
		const amplitude = conditions.mobile ? 20 : 40;
		cards.forEach((card) => {
			card.querySelectorAll(".brand-card__row").forEach((row, i) => {
				const direction = i % 2 === 0 ? 1 : -1;
				gsap.fromTo(
					row,
					{ x: -amplitude * direction },
					{
						x: amplitude * direction,
						ease: "none",
						scrollTrigger: { trigger: card, start: "top bottom", end: "bottom top", scrub: 1 },
					},
				);
			});
		});
	});
}

export function initBrands() {
	const grid = document.querySelector("#brandsGrid");
	if (!grid) return;

	rows.forEach((row) => {
		const rowEl = document.createElement("div");
		rowEl.className = "brands__row";
		rowEl.append(...row.map(createCard));
		grid.append(rowEl);
	});

	const cards = grid.querySelectorAll(".brand-card");
	// Без blur: размытие больших карточек с десятками плиток слишком дорогое
	revealOnView(grid, cards, { blur: 0 });
	if (!reducedMotion) drift(cards);
}
