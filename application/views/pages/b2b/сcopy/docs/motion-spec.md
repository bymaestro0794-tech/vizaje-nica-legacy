# Motion spec

Тот же документ, что на странице «Motion spec» в Figma. Значения стартовые, подкручиваются на живом сайте.
Сначала верстка и адаптив, анимации отдельным проходом по секциям.

## Роли библиотек

- **Lenis**: плавный скролл колесом (`lerp: 0.1`). Якоря через `lenis.scrollTo`. При открытой модалке `lenis.stop()`, после закрытия `lenis.start()`.
- **GSAP + ScrollTrigger**: всё, что зависит от прогресса скролла: scrub-таймлайны, count-up цифр, pin, сборка плиток в «What we offer».
- **Motion** (глобал `Motion`): пружины hover и tap, inView, stagger, layout-переходы карточка → модалка, drag в слайдере.
- Правило: одно свойство ведёт одна библиотека. Если transform у элемента анимирует GSAP, Motion его не трогает.

## Spring-пресеты

| Пресет | stiffness / damping | Где |
|---|---|---|
| responsive | 300 / 30 | hover, tap, стрелки слайдера, раскрытие FAQ |
| soft | 120 / 14 | появление hero, плавающие картинки, возврат после hover |
| precise | 400 / 40 | модалки, layout-переходы, возврат drag |

- Для интерактива ease и duration не используем, только spring. Ease допустим в scrub-таймлайнах GSAP (`power2.out`) и при count-up.
- `whileTap` scale 0.95 на всех кнопках, 0.98 на карточках.

## Связка Lenis и GSAP

```js
const lenis = new Lenis({ lerp: 0.1, smoothWheel: true })
lenis.on('scroll', ScrollTrigger.update)
gsap.ticker.add((t) => lenis.raf(t * 1000))
gsap.ticker.lagSmoothing(0)
```

На touch оставляем нативный скролл (`syncTouch: false`). Один общий экземпляр Lenis на страницу.

## Доступность и производительность

- `prefers-reduced-motion`: отключить parallax, scrub, magnetic, marquee и velocity-skew. Оставить fade 150 мс без сдвигов.
- Анимируем только `transform` и `opacity`. Blur точечно и убирать после завершения. `will-change` ставить на время анимации.
- Touch и mobile: без hover и magnetic. Амплитуды parallax вдвое меньше. Для разных экранов `gsap.matchMedia()`.
- Модалки: focus trap, закрытие по Esc и клику на затемнение, возврат фокуса на карточку, `aria-modal`.

## Анимации по секциям

### Nav
- Скролл вниз больше 80 px: меню уезжает вверх (`y: -100%`, spring 300/30). Скролл вверх: возвращается. После 40 px появляется фон с `backdrop-filter: blur(20px)` и нижняя граница 1px.
- «Become a partner»: magnetic, до 24 px от курсора смещается на `dx × 0.3` (spring 150/15). Tap 0.95. На touch выключено.
- Ссылки: активный пункт подсвечивается по ScrollTrigger. Подчёркивание рисуется `scaleX 0 → 1` слева. Переход по якорю: `lenis.scrollTo(target, { offset: -80 })`.
- Библиотеки: Motion, GSAP, Lenis.

### Hero
- Загрузка, stagger 0.08: eyebrow, заголовок, описание, кнопка, tagline. Заголовок по словам с маской (каждое слово в `overflow: hidden`, `y: 100% → 0`). Остальные тексты blur-in: `opacity 0, blur(10px), y 20` → чёткость (spring 300/30).
- Картинки: въезд `scale 0.9 → 1`, `y 40 → 0`, `opacity 0 → 1` (spring 120/14, задержка 0.2 с). Потом постоянное парение `y ± 8 px`, 6 с, `sine.inOut`, yoyo (GSAP), у левой и правой групп разные фазы.
- Параллакс мыши (desktop): пакеты до 12 px, коробки до 20 px, в противоположные стороны (spring 150/15).
- Скролл: пакеты `y 0 → -60`, коробки `y 0 → -120` (scrub 1). Текст `opacity 1 → 0`, `scale 1 → 0.97`. Градиент статичный.

### Our business (карточки и модалка)
- Появление: inView, once, margin -10%. Три карточки stagger 0.08: `opacity 0, blur(10px), y 40` → чёткость.
- Hover (desktop): карточка scale 1.02 (300/30). Картинка внутри scale 1.06 (120/14, `overflow: hidden`). «+» поворачивается на 90°. Tap 0.98.
- Клик: layout-переход, картинка карточки «перелетает» в верх модалки (spring 400/40). Затемнение `opacity 0 → 0.6` за 250 мс. Строки панели stagger 0.05.
- Desktop: `scale 0.96 → 1`, `y 24 → 0`. Mobile: выезжает снизу (`y: 100% → 0`), закрытие drag вниз (порог 120 px или velocity 500).
- Пока модалка открыта: `lenis.stop()`.

### Vizaje-Nica in numbers
- ScrollTrigger: start `top 75%`, once.
- Count-up: 35, 12, 100+, 115,000+. GSAP tween, duration 1.6, `power3.out`, `snap: 1`. Формат через `toLocaleString`, «+» добавляется в конце. Для screen reader сразу итоговое значение (`aria-label`).
- Карточки stagger 0.1: `opacity 0, y 40, blur(8px)` → чёткость. Подпись на 0.2 с позже.
- Hover: scale 1.01, цифра `y -4` (300/30).

### Brands we carry
- Появление: inView, stagger 0.08 для четырёх карточек.
- Плитки: ряды медленно дрейфуют по горизонтали в разные стороны при скролле (нечётные `x -40 → 40`, чётные `x 40 → -40`, ScrollTrigger scrub 1). На mobile вдвое меньше.
- Hover: карточка scale 1.02. Плитка под курсором 1.05, соседние 1.02 (волна, stagger 0.02). «+» поворачивается на 90°.
- Клик: как в Our business, модалка со списком, имена появляются каждые 0.03 с. `lenis.stop()`.

### Слайдер магазинов
- Drag по горизонтали с инерцией (`bounceStiffness 400`, `bounceDamping 40`). На mobile нативный свайп со scroll-snap. Стрелки на одну карточку (300/30). Клавиши ← →.
- Активная карточка scale 1, opacity 1. Соседние scale 0.96, opacity 0.6. Стрелка на краю: opacity 0.4, клик не срабатывает.
- Параллакс фото внутри: смещение -5%…5% по мере прохождения через центр экрана.
- Появление слайдера: карточки stagger 0.1 справа налево (`x 80 → 0`, `opacity 0 → 1`).
- Видео (если будет): автоплей когда карточка в центре, пауза когда уходит, кнопка повтора по окончании.

### What we offer
- Заголовок «One partner. The whole market.»: слова поднимаются `y 100% → 0` в маске по мере скролла (scrub 1, start `top 80%`, end `top 40%`).
- Сборка визуала (главный эффект): четыре элемента (Import, Logistics, Distribution, Retail) смещены на ±40 px, повёрнуты на ±6°, `opacity 0.6`, `blur 6px`. По scrub-таймлайну (start `top 70%`, end `bottom 40%`, ease `power2.out`) сходятся в один объект. В конце тень под объектом `scale 0.8 → 1` и лёгкий glow. Если визуал единая картинка (`img/360.png`), эффект делается на четырёх слоях PNG.
- Связь со списком: пока элемент собирается, строка 01–04 подсвечивается (`opacity 0.4 → 1`, номер меняет цвет).
- «Built for long-term growth»: линия над принципом `scaleX 0 → 1` (origin left, 0.8 с). Слова Visibility, Stability, Partnership stagger 0.12, описание позже.
- Опционально: `skewY` заголовка по velocity скролла (максимум ±3°).

### FAQ
- Высота ответа анимируется spring 300/30 (или `grid-template-rows: 0fr → 1fr`). Соседние пункты едут плавно (layout-анимация).
- Иконка «+» поворачивается на 45° и становится «×». Ответ `opacity 0 → 1` с `blur(6px) → 0`, задержка 0.05 с.
- Первый вопрос открыт, одновременно открыт один пункт. Enter и Space, `aria-expanded`.
- Заголовок слева по словам, пункты справа stagger 0.06 (inView).

### Final CTA и Footer
- Заголовок «Ready to become a partner?» по словам (маска, stagger 0.08). Кнопка «Business inquiry →» magnetic (150/15), стрелка на hover `x +4`.
- Footer: fade-up `y 20 → 0`. Подчёркивание ссылок `scaleX 0 → 1` на hover.
- Progress bar сверху: `scaleX` по scrollYProgress через сглаживание (spring 200/30).

### Опционально: marquee
- Бесконечная лента логотипов брендов (например, над Brands). Скорость 40 px/с, на hover замедляется до 25%, направление зависит от velocity скролла. GSAP horizontalLoop или CSS `translateX(-50%)`. При reduced motion статичная сетка.
