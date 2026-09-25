import { initLenis } from "./js/lib/lenis.js";
import { initModal } from "./js/lib/modal.js";
import { initProgress } from "./js/lib/progress.js";
import { initNav } from "./sections/nav/nav.js";
import { initHero } from "./sections/hero/hero.js";
import { initOurBusiness } from "./sections/our-business/our-business.js";
import { initNumbers } from "./sections/numbers/numbers.js";
import { initBrands } from "./sections/brands/brands.js";
import { initStores } from "./sections/stores/stores.js";
import { initOffer } from "./sections/offer/offer.js";
import { initFaq } from "./sections/faq/faq.js";
import { initCta } from "./sections/cta/cta.js";
import { initFooter } from "./sections/footer/footer.js";

document.documentElement.dataset.motion = "ready";

const lenis = initLenis();
initModal(lenis);
initProgress(lenis);
initHero();
initOurBusiness();
initNumbers();
initBrands();
initStores();
initOffer();
initFaq();
initCta();
initFooter();
initNav(lenis);
