const fs = require('fs');
const path = require('path');
const postcss = require('postcss');

const cssFile = path.join(__dirname, 'new-styles.css');
const cssContent = fs.readFileSync(cssFile, 'utf-8');

const targetClasses = [

  'certificate',
  'certificate__container',
  'certificate__head',
  'certificate__title',
  '_title',
  'certificate__wrapper',
  'certificate__top-info',
  'certificate__back',
  'certificate__step',
  'step-certificate',
  'step-certificate__main',
  'step-certificate__text',
  'step-certificate__current',
  'step-certificate__total',
  'step-certificate__progressbar',
  'step-certificate__progress',

  'certificate__blocks',
  'certificate__block',
  '_active',
  'certificate__top',
  'certificate__name',
  'certificate__body',

  'certificate__design',
  'design-certificate',
  'design-certificate__wrapper',
  'design-certificate__slider',
  '_swiper',
  'design-certificate__item',
  'design-certificate__input',
  'design-certificate__content',
  'design-certificate__check',
  'design-certificate__circle',
  'design-certificate__image',
  'design-certificate__arrow',
  'design-certificate-arrow-prev',
  'design-certificate-arrow-next',
  'design-certificate__paggination',
  'paggination',
  'paggination__bullet',
  'paggination__bullet-active',

  'certificate__text',
  'certificate__footer',
  'certificate__button',
  '_next-step',

  'certificate__denomination',
  'denomination-certificate',
  'denomination-certificate__item',
  'input-field',
  '_req',

  'certificate__content',
  'certificate__section',
  'certificate__data',
  'data-certificate',
  'data-certificate__item',
  'data-certificate__label',
  'data-certificate__input',
  'input',
  '_phone',

  'certificate__time-send',
  'time-send-certificate',
  'time-send-certificate__options',
  'time-send-certificate__option',
  'time-send-certificate__radio',
  'time-send-certificate__content',
  'time-send-certificate__circle',
  'time-send-certificate__text',
  'time-send-certificate__set',
  'time-send-certificate__item',
  'time-send-certificate__label',
  'time-send-certificate__input',
  '_date',
  '_time',

  'certificate__recipe',
  'recipe-certificate',
  'recipe-certificate__row',
  'recipe-certificate__input',

  'certificate__warning',

  'certificate__details',
  'details-certificate',
  'details-certificate__info',
  'details-certificate__row',
  'details-certificate__name',
  'details-certificate__value',
  'details-certificate__text',
  'details-certificate__alert',

  'details-certificate__card',
  'card-details-certificate',
  'card-details-certificate__image',
  'card-details-certificate__balance',
  'card-details-certificate__value'
];

// Helper pentru a verifica dacă selectorul conține vreuna din clasele țintă
function selectorMatches(selector) {
  return targetClasses.some(cls => {
    const clsEscaped = cls.replace('.', '\\.');
    return new RegExp(`\\.${clsEscaped}(\\b|[:._])`).test(selector);
  });
}

const root = postcss.parse(cssContent);
const filteredRoot = postcss.root();

root.nodes.forEach(node => {
  if (node.type === 'rule' && selectorMatches(node.selector)) {
    filteredRoot.append(node.clone());
  } else if (node.type === 'atrule' && node.name === 'media') {
    // verificăm dacă are noduri și filtrăm doar regulile
    const mediaRules = node.nodes?.filter(inner => inner.type === 'rule' && selectorMatches(inner.selector)) || [];
    if (mediaRules.length) {
      const mediaClone = postcss.atRule({ name: 'media', params: node.params });
      mediaRules.forEach(r => mediaClone.append(r.clone()));
      filteredRoot.append(mediaClone);
    }
  }
});

fs.writeFileSync(path.join(__dirname, 'extracted.css'), filteredRoot.toString());
console.log('✅ Clasele și media queries au fost extrase corect, păstrând ordinea și structura');