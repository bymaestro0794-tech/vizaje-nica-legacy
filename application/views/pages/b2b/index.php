<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$b2bAssetBase = function_exists('base_url')
    ? rtrim(base_url(), '/') . '/app/b2b/'
    : '/app/b2b/';
$b2bAsset = static function ($path) use ($b2bAssetBase) {
    return htmlspecialchars($b2bAssetBase . ltrim($path, '/'), ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Vizaje-Nica — B2B</title>
		<link rel="preconnect" href="https://fonts.googleapis.com" />
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
		<link
			href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
			rel="stylesheet"
		/>
		<link rel="stylesheet" href="<?= $b2bAsset('main.css'); ?>" />
		<!-- Прячем элементы до анимации появления; если скрипты не запустились за 4 с, показываем всё как есть -->
		<script>
			document.documentElement.classList.add("js");
			setTimeout(function () {
				if (!document.documentElement.dataset.motion) document.documentElement.classList.remove("js");
			}, 4000);
		</script>

		<!-- Lenis -->
		<script
			src="https://unpkg.com/lenis@1.3.26/dist/lenis.min.js"
			defer
		></script>

		<!-- GSAP -->
		<script
			src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"
			defer
		></script>
		<script
			src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"
			defer
		></script>

		<!-- Motion (unified Motion One + Framer Motion engine: spring physics, inView, stagger) -->
		<script
			src="https://cdn.jsdelivr.net/npm/motion@13.4.2/dist/motion.js"
			defer
		></script>
	</head>
	<body>
		<div class="scroll-progress" aria-hidden="true"></div>
		<header class="nav">
			<div class="nav__backdrop" aria-hidden="true"></div>
			<div class="nav__inner">
				<a href="#" class="nav__logo">
					<img src="<?= $b2bAsset('img/logo-1.svg'); ?>" alt="Vizaje-Nica" width="172" height="24" />
				</a>
				<ul class="nav__links">
					<li><a href="#business" class="nav__link">Business</a></li>
					<li><a href="#brands" class="nav__link">Brands</a></li>
					<li><a href="#stores" class="nav__link">Stores</a></li>
					<li><a href="#contact" class="nav__link">Contact</a></li>
				</ul>
				<a href="mailto:marketing@vizaje-nica.com" class="nav__partner">Become a partner</a>
			</div>
		</header>

		<section class="hero">
			<div class="hero__bg" aria-hidden="true"></div>
			<div class="hero__text">
				<p class="hero__eyebrow">B2B / MOLDOVA</p>
				<h1 class="hero__title">Beauty retail. Distribution.<br />One local partner.</h1>
				<p class="hero__description">
					Vizaje-Nica is a second-generation family company, started in 1989 and grown into a
					market leader in beauty retail and distribution.
				</p>
				<a href="mailto:marketing@vizaje-nica.com" class="btn btn-dark hero__cta"
					>Business inquiry <span class="btn__arrow" aria-hidden="true">→</span></a
				>
				<p class="hero__tagline">Beauty brings people together.</p>
			</div>
			<div class="hero__bags" aria-hidden="true">
				<div class="hero__layer" data-hero-scroll>
					<div class="hero__layer" data-hero-float>
						<div class="hero__layer" data-hero-mouse>
							<img src="<?= $b2bAsset('img/hero/hero-bags.png'); ?>" alt="" />
						</div>
					</div>
				</div>
			</div>
			<div class="hero__boxes" aria-hidden="true">
				<div class="hero__layer" data-hero-scroll>
					<div class="hero__layer" data-hero-float>
						<div class="hero__layer" data-hero-mouse>
							<img src="<?= $b2bAsset('img/hero/hero-boxes.png'); ?>" alt="" />
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="our-business" id="business">
			<div class="our-business__inner">
				<h2 class="our-business__title">Our business</h2>
				<div class="our-business__cards">
					<button type="button" class="business-card" data-modal="retail">
						<div class="business-card__image">
							<img src="<?= $b2bAsset('img/our-bussines/store.jpg'); ?>" alt="" />
						</div>
						<div class="business-card__text">
							<p class="business-card__eyebrow">Retail</p>
							<p class="business-card__title">Moldova's #1 beauty retail chain.</p>
						</div>
						<span class="business-card__plus">
							<img src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
						</span>
					</button>
					<button type="button" class="business-card" data-modal="distribution">
						<div class="business-card__image">
							<img src="<?= $b2bAsset('img/our-bussines/warehouse.jpg'); ?>" alt="" />
						</div>
						<div class="business-card__text">
							<p class="business-card__eyebrow">Distribution</p>
							<p class="business-card__title">One partner. Full market coverage.</p>
						</div>
						<span class="business-card__plus">
							<img src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
						</span>
					</button>
					<button type="button" class="business-card" data-modal="ecom">
						<div class="business-card__image">
							<img src="<?= $b2bAsset('img/our-bussines/web.jpg'); ?>" alt="" />
						</div>
						<div class="business-card__text">
							<p class="business-card__eyebrow">E-com</p>
							<p class="business-card__title">Over 90% of our assortment is online.</p>
						</div>
						<span class="business-card__plus">
							<img src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
						</span>
					</button>
				</div>
			</div>
		</section>

		<section class="numbers">
			<div class="numbers__inner">
				<h2 class="numbers__title">Vizaje-Nica in numbers</h2>
				<div class="numbers__row numbers__row--top">
					<div class="number-card number-card--wide">
						<p class="number-card__value">35</p>
						<p class="number-card__label">
							Years of market experience.<br />
							A second-generation family company, started in 1989.
						</p>
					</div>
					<div class="number-card number-card--narrow">
						<p class="number-card__value number-card__value--red">12</p>
						<p class="number-card__label">
							Stores in Chisinau,<br />
							with a new door opening in 2026.
						</p>
					</div>
				</div>
				<div class="numbers__row numbers__row--bottom">
					<div class="number-card number-card--narrow">
						<p class="number-card__value number-card__value--blue">100+</p>
						<p class="number-card__label">Global brands in our retail portfolio.</p>
					</div>
					<div class="number-card number-card--wide">
						<p class="number-card__value">115,000+</p>
						<p class="number-card__label">
							Loyal customers in our client base.<br />
							Over 90% of our assortment is also available online.
						</p>
					</div>
				</div>
			</div>
		</section>

		<section class="brands" id="brands">
			<div class="brands__inner">
				<h2 class="brands__title">Brands we carry</h2>
				<div class="brands__grid" id="brandsGrid"></div>
			</div>
		</section>

		<section class="stores" id="stores" aria-label="Our stores">
			<div class="stores__track" id="storesTrack"></div>
			<div class="stores__arrows">
				<button type="button" class="stores__arrow stores__arrow--prev" aria-label="Previous store">
					<img src="<?= $b2bAsset('img/arrow-left.svg'); ?>" alt="" />
				</button>
				<button type="button" class="stores__arrow stores__arrow--next" aria-label="Next store">
					<img src="<?= $b2bAsset('img/arrow-right.svg'); ?>" alt="" />
				</button>
			</div>
		</section>

		<section class="offer">
			<div class="offer__inner">
				<header class="offer__header">
					<p class="offer__eyebrow">WHAT WE OFFER</p>
					<h2 class="offer__title">One partner.<br />The whole market.</h2>
					<p class="offer__lead">From import and logistics to nationwide retail distribution.</p>
				</header>

				<div class="offer-card">
					<div class="offer-card__list">
						<h3 class="offer-card__title">Everything in one place</h3>
						<ol class="offer-card__rows">
							<li class="offer-card__row">
								<span class="offer-card__num">01</span>
								<span class="offer-card__text">360° market coverage</span>
							</li>
							<li class="offer-card__row">
								<span class="offer-card__num">02</span>
								<span class="offer-card__text">One point of contact</span>
							</li>
							<li class="offer-card__row">
								<span class="offer-card__num">03</span>
								<span class="offer-card__text">Import &amp; logistics</span>
							</li>
							<li class="offer-card__row">
								<span class="offer-card__num">04</span>
								<span class="offer-card__text">Distribution to key retailers</span>
							</li>
						</ol>
					</div>
					<div class="offer-card__visual" aria-hidden="true">
						<div class="offer-card__glow"></div>
						<div class="offer-card__shadow"></div>
						<!-- Четыре копии пазла, каждая обрезана по своей части: из них «собирается» объект при скролле.
						     Целая картинка сверху проявляется в конце и скрывает швы между частями -->
						<div class="offer-card__puzzle">
							<img class="offer-card__piece offer-card__piece--top" src="<?= $b2bAsset('img/360.png'); ?>" alt="" />
							<img class="offer-card__piece offer-card__piece--right" src="<?= $b2bAsset('img/360.png'); ?>" alt="" />
							<img class="offer-card__piece offer-card__piece--bottom" src="<?= $b2bAsset('img/360.png'); ?>" alt="" />
							<img class="offer-card__piece offer-card__piece--left" src="<?= $b2bAsset('img/360.png'); ?>" alt="" />
							<img class="offer-card__whole" src="<?= $b2bAsset('img/360.png'); ?>" alt="" />
						</div>
					</div>
				</div>

				<div class="offer-growth">
					<h3 class="offer-growth__title">Built for long-term growth.</h3>
					<div class="offer-growth__principles">
						<div class="principle">
							<span class="principle__line" aria-hidden="true"></span>
							<h4 class="principle__title">Visibility</h4>
							<p class="principle__text">Maximum visibility and prestige image.</p>
						</div>
						<div class="principle">
							<span class="principle__line" aria-hidden="true"></span>
							<h4 class="principle__title">Stability</h4>
							<p class="principle__text">Financially stable and growing local partner.</p>
						</div>
						<div class="principle">
							<span class="principle__line" aria-hidden="true"></span>
							<h4 class="principle__title">Partnership</h4>
							<p class="principle__text">Long-term partnership.</p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="faq" id="faq">
			<div class="faq__inner">
				<div class="faq__heading">
					<h2 class="faq__title">Frequently asked questions</h2>
					<p class="faq__lead">Answers for brands considering a partnership with Vizaje-Nica.</p>
					<a href="mailto:marketing@vizaje-nica.com" class="faq__link">Contact us ›</a>
				</div>

				<div class="faq__items">
					<div class="faq-item">
						<h3>
							<button type="button" class="faq-item__toggle" aria-expanded="true" aria-controls="faq-1">
								<span class="faq-item__question">What is Vizaje-Nica?</span>
								<img class="faq-item__icon" src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
							</button>
						</h3>
						<div class="faq-item__panel" id="faq-1">
							<p class="faq-item__answer">
								A second-generation family company, started in 1989 and grown into the #1 retailer and
								distributor in beauty in Moldova.
							</p>
						</div>
					</div>
					<div class="faq-item">
						<h3>
							<button type="button" class="faq-item__toggle" aria-expanded="false" aria-controls="faq-2">
								<span class="faq-item__question">What do you offer to brands?</span>
								<img class="faq-item__icon" src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
							</button>
						</h3>
						<div class="faq-item__panel" id="faq-2" hidden>
							<p class="faq-item__answer">
								A 360° solution to cover the market: one point of contact, import and logistics,
								distribution to all key retailers, and maximum visibility for your brand.
							</p>
						</div>
					</div>
					<div class="faq-item">
						<h3>
							<button type="button" class="faq-item__toggle" aria-expanded="false" aria-controls="faq-3">
								<span class="faq-item__question">Which brands do you work with?</span>
								<img class="faq-item__icon" src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
							</button>
						</h3>
						<div class="faq-item__panel" id="faq-3" hidden>
							<p class="faq-item__answer">
								Over 100 global brands across fragrances, niche fragrances, make-up and skin care, and 80+
								brands in our distribution portfolio.
							</p>
						</div>
					</div>
					<div class="faq-item">
						<h3>
							<button type="button" class="faq-item__toggle" aria-expanded="false" aria-controls="faq-4">
								<span class="faq-item__question">How big is your retail network?</span>
								<img class="faq-item__icon" src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
							</button>
						</h3>
						<div class="faq-item__panel" id="faq-4" hidden>
							<p class="faq-item__answer">
								12 stores in Chisinau, with a new door opening by mid 2026, plus 4 franchise partners, 3
								exclusive regional partners and our own e-commerce store.
							</p>
						</div>
					</div>
					<div class="faq-item">
						<h3>
							<button type="button" class="faq-item__toggle" aria-expanded="false" aria-controls="faq-5">
								<span class="faq-item__question">Do you handle import and logistics?</span>
								<img class="faq-item__icon" src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
							</button>
						</h3>
						<div class="faq-item__panel" id="faq-5" hidden>
							<p class="faq-item__answer">
								Yes. We act as your import and logistics partner, with our own warehouse and a fully
								automated IT system and ordering process.
							</p>
						</div>
					</div>
					<div class="faq-item">
						<h3>
							<button type="button" class="faq-item__toggle" aria-expanded="false" aria-controls="faq-6">
								<span class="faq-item__question">How do you support brands in marketing?</span>
								<img class="faq-item__icon" src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
							</button>
						</h3>
						<div class="faq-item__panel" id="faq-6" hidden>
							<p class="faq-item__answer">
								Through 360° marketing support: dedicated beauty advisors, in-store and BTL activations, CRM
								with email, SMS and loyalty programs, PR, ATL and digital.
							</p>
						</div>
					</div>
					<div class="faq-item">
						<h3>
							<button type="button" class="faq-item__toggle" aria-expanded="false" aria-controls="faq-7">
								<span class="faq-item__question">How do we start working together?</span>
								<img class="faq-item__icon" src="<?= $b2bAsset('img/plus.svg'); ?>" alt="" />
							</button>
						</h3>
						<div class="faq-item__panel" id="faq-7" hidden>
							<p class="faq-item__answer">
								Send us a business inquiry and tell us about your brand. Our team will get back to you to
								discuss the next steps.
							</p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="cta" id="contact">
			<div class="cta__inner">
				<h2 class="cta__title">Ready to become a partner?</h2>
				<p class="cta__lead">
					Tell us about your brand and we will get back to you to discuss the next steps.
				</p>
				<a href="mailto:marketing@vizaje-nica.com" class="btn btn-dark cta__button"
					>Business inquiry <span class="btn__arrow" aria-hidden="true">→</span></a
				>
			</div>
		</section>

		<footer class="footer">
			<div class="footer__inner">
				<div class="footer__top">
					<div class="footer__brand">
						<a href="#" class="footer__logo">
							<img src="<?= $b2bAsset('img/logo.svg'); ?>" alt="Vizaje-Nica" width="172" height="24" />
						</a>
						<p class="footer__tagline">Beauty brings people together.</p>
					</div>
					<nav class="footer__col" aria-label="Company">
						<p class="footer__col-title">COMPANY</p>
						<a href="#business">Business</a>
						<a href="#company">Company</a>
						<a href="#brands">Brands</a>
						<a href="#stores">Stores</a>
					</nav>
					<div class="footer__col">
						<p class="footer__col-title">CONTACT</p>
						<a
							href="https://www.google.com/maps/search/?api=1&amp;query=Vizaje-Nica%2C%20str.%20Puskin%2032%2C%20Chisinau"
							target="_blank"
							rel="noopener"
							>32 Puskin str, Chisinau, Moldova</a
						>
						<a href="https://www.instagram.com/vizaje_nica/" target="_blank" rel="noopener">@vizaje_nica</a>
						<a href="https://www.vizaje-nica.com" target="_blank" rel="noopener">www.vizaje-nica.com</a>
					</div>
				</div>
				<div class="footer__bottom">
					<p>Copyright © 2026 Vizaje-Nica. All rights reserved.</p>
					<p>People&nbsp;&nbsp;·&nbsp;&nbsp;Beauty&nbsp;&nbsp;·&nbsp;&nbsp;Progress</p>
				</div>
			</div>
		</footer>

		<!-- data-lenis-prevent: остановленный Lenis иначе гасит колесо и свайпы и внутри модалки, и её нельзя прокрутить -->
		<dialog class="modal" id="modal" data-lenis-prevent>
			<button type="button" class="modal__close" aria-label="Close">
				<img src="<?= $b2bAsset('img/close.svg'); ?>" alt="" />
			</button>
			<div class="modal__header">
				<p class="modal__eyebrow"></p>
				<p class="modal__title"></p>
			</div>
			<div class="modal__panel"></div>
			<div class="modal__image">
				<img src="" alt="" />
			</div>
		</dialog>

		<script>
			window.B2B_ASSET_BASE = <?= json_encode($b2bAssetBase, JSON_UNESCAPED_SLASHES); ?>;
			window.B2B_ASSET_URL = (path) => new URL(path.replace(/^\.\//, ''), window.B2B_ASSET_BASE).href;
		</script>
		<script type="module" src="<?= $b2bAsset('main.js'); ?>"></script>
	</body>
</html>
