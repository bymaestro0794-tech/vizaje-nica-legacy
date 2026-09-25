const { animate, press, hover, inView, stagger } = Motion;

export const springs = {
	responsive: { type: "spring", stiffness: 300, damping: 30 },
	soft: { type: "spring", stiffness: 120, damping: 14 },
	precise: { type: "spring", stiffness: 400, damping: 40 },
	magnetic: { type: "spring", stiffness: 150, damping: 15 },
};

export const reducedMotion = matchMedia("(prefers-reduced-motion: reduce)").matches;
export const canHover = matchMedia("(hover: hover) and (pointer: fine)").matches;

// Каждое слово — в свою маску (.split-word > .split-word__inner); <br> и пробелы сохраняются.
// Возвращает внутренние элементы слов, которые и анимируются
export function splitWords(el) {
	const nodes = [...el.childNodes];
	const label = nodes.map((node) => (node.nodeName === "BR" ? " " : node.textContent)).join("");
	el.setAttribute("aria-label", label.replace(/\s+/g, " ").trim());
	const inners = [];
	el.replaceChildren();

	nodes.forEach((node) => {
		if (node.nodeType !== Node.TEXT_NODE) {
			el.append(node);
			return;
		}
		node.textContent.split(/(\s+)/).forEach((part) => {
			if (!part) return;
			if (/^\s+$/.test(part)) {
				el.append(" ");
				return;
			}
			const word = document.createElement("span");
			word.className = "split-word";
			word.setAttribute("aria-hidden", "true");
			const inner = document.createElement("span");
			inner.className = "split-word__inner";
			inner.textContent = part;
			word.append(inner);
			el.append(word);
			inners.push(inner);
		});
	});

	return inners;
}

// Появление при входе в экран (один раз): opacity, подъём и снятие размытия с шагом staggerStep
export function revealOnView(trigger, targets, { y = 40, blur = 10, staggerStep = 0.08, delay = 0 } = {}) {
	const items = [...targets];
	const stop = inView(
		trigger,
		() => {
			stop();
			if (reducedMotion) {
				animate(items, { opacity: 1 }, { duration: 0.15 });
				return;
			}
			const keyframes = { opacity: [0, 1], y: [y, 0] };
			if (blur) keyframes.filter = [`blur(${blur}px)`, "blur(0px)"];
			animate(items, keyframes, { ...springs.responsive, delay: stagger(staggerStep, { startDelay: delay }) }).then(() =>
				items.forEach((el) => (el.style.filter = "")),
			);
		},
		{ margin: "0px 0px -10% 0px" },
	);
}

// Hover и нажатие в одном месте: после отпускания масштаб возвращается к hover-значению, если курсор ещё над элементом
export function interactive(el, { hoverScale = 1, tapScale = 0.95, onHover } = {}) {
	if (reducedMotion) return;
	let hovered = false;
	let pressed = false;
	const update = () => animate(el, { scale: pressed ? tapScale : hovered ? hoverScale : 1 }, springs.responsive);

	// tapScale 1 — элемент не кликабельный: press не вешаем, чтобы он не попадал в порядок фокуса
	if (tapScale !== 1) {
		press(el, () => {
			pressed = true;
			update();
			return () => {
				pressed = false;
				update();
			};
		});
	}

	if (!canHover) return;
	hover(el, () => {
		hovered = true;
		update();
		onHover?.(true);
		return () => {
			hovered = false;
			update();
			onHover?.(false);
		};
	});
}

// Элемент тянется к курсору, пока тот не дальше radius px от его границ
export function magnetic(el, { radius = 24, strength = 0.3 } = {}) {
	if (reducedMotion || !canHover) return;
	let active = false;

	window.addEventListener("pointermove", (event) => {
		const rect = el.getBoundingClientRect();
		const inside =
			event.clientX > rect.left - radius &&
			event.clientX < rect.right + radius &&
			event.clientY > rect.top - radius &&
			event.clientY < rect.bottom + radius;

		if (inside) {
			active = true;
			const dx = event.clientX - (rect.left + rect.width / 2);
			const dy = event.clientY - (rect.top + rect.height / 2);
			animate(el, { x: dx * strength, y: dy * strength }, springs.magnetic);
		} else if (active) {
			active = false;
			animate(el, { x: 0, y: 0 }, springs.magnetic);
		}
	});
}
