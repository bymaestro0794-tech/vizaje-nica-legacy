// Список брендов по категориям (из презентации, слайды 13–16).
// domain: используется для подтягивания логотипа с CDN (временно). ПРОВЕРИТЬ домены вручную, пустая строка = не знаем.
// Позже заменить на локальные файлы: поле logo вида '/img/brands/chanel.svg'.

export const brands = {
	fragrances: {
		title: 'Fragrances',
		headline: 'Chanel, Dior, Guerlain and more.',
		modalTitle: 'Iconic houses, one partner.',
		items: [
			{ name: 'Chanel', domain: 'chanel.com' },
			{ name: 'Dior', domain: 'dior.com' },
			{ name: 'Guerlain', domain: 'guerlain.com' },
			{ name: 'Givenchy', domain: 'givenchy.com' },
			{ name: 'Kenzo', domain: 'kenzo.com' },
			{ name: 'Tom Ford', domain: 'tomfordbeauty.com' },
			{ name: 'Dolce & Gabbana', domain: 'dolcegabbana.com' },
			{ name: 'Lalique', domain: 'lalique.com' },
			{ name: 'Giorgio Armani', domain: 'armanibeauty.com' },
			{ name: 'Lancôme', domain: 'lancome.com' },
			{ name: 'YSL', domain: 'yslbeauty.com' },
			{ name: 'Valentino', domain: 'valentino.com' },
			{ name: 'Versace', domain: 'versace.com' },
			{ name: 'Moschino', domain: 'moschino.com' },
			{ name: 'Rabanne', domain: 'rabanne.com' },
			{ name: 'Carolina Herrera', domain: 'carolinaherrera.com' },
			{ name: 'Jean Paul Gaultier', domain: 'jeanpaulgaultier.com' },
			{ name: 'Nina Ricci', domain: 'ninaricci.com' },
			{ name: 'Ferragamo', domain: 'ferragamo.com' },
			{ name: 'DKNY', domain: 'dkny.com' },
			{ name: 'Trussardi', domain: 'trussardi.com' },
			{ name: 'A. Banderas', domain: '', logo: './img/brands/a-banderas.png' }
		],
		comingSoon: []
	},

	niche: {
		title: 'Niche fragrances',
		headline: 'Byredo, Xerjoff, Roja and more.',
		modalTitle: 'Rare and collectible houses.',
		items: [
			{ name: 'Tom Ford', domain: 'tomfordbeauty.com' },
			{ name: 'Byredo', domain: 'byredo.com' },
			{ name: 'Xerjoff', domain: 'xerjoff.com' },
			{ name: 'Roja', domain: 'rojaparfums.com' },
			{ name: 'Sospiro', domain: '', logo: './img/brands/sospiro.png' },
			{ name: 'Juliette Has a Gun', domain: 'juliettehasagun.com' },
			{ name: 'Atkinsons', domain: '', logo: './img/brands/atkinsons.png' },
			{ name: 'Casamorati', domain: '', logo: './img/brands/casamorati.png' },
			{ name: 'Atelier des Ors', domain: 'atelierdesors.com' },
			{ name: 'Micaleff', domain: '', logo: './img/brands/micaleff.png' },
			{ name: 'Moresque', domain: 'moresque.com' },
			{ name: 'The House of Oud', domain: 'thehouseofoud.com', logo: './img/brands/the-house-of-oud.png' },
			{ name: 'New Notes', domain: '', logo: './img/brands/new-notes.png' },
			{ name: 'Blend Oud', domain: 'blendoud.com' },
			{ name: 'Jacques Zolty', domain: '', logo: './img/brands/jacques-zolty.png' },
			{ name: 'Vilhelm Parfumerie', domain: 'vilhelmparfumerie.com' },
			{ name: 'Ojar', domain: '', logo: './img/brands/ojar.png' },
			{ name: 'Chabaud', domain: 'chabaud.fr', logo: './img/brands/chabaud.png' },
			{ name: 'Miller & Bertaux', domain: '', logo: './img/brands/miller-bertaux.png' },
			{ name: 'Bybozo', domain: '', logo: './img/brands/bybozo.png' },
			{ name: 'Rudross', domain: '', logo: './img/brands/rudross.png' }
		],
		comingSoon: ['Amouage', 'Creed', 'Dolce & Gabbana', 'Mancera']
	},

	makeup: {
		title: 'Make-up',
		headline: 'M·A·C, Chanel, Dior and more.',
		modalTitle: 'Professional make-up and colour.',
		items: [
			{ name: 'M·A·C', domain: 'maccosmetics.com' },
			{ name: 'Chanel', domain: 'chanel.com' },
			{ name: 'Dior', domain: 'dior.com' },
			{ name: 'Dior Backstage', domain: 'dior.com' },
			{ name: 'Guerlain', domain: 'guerlain.com' },
			{ name: 'Givenchy', domain: 'givenchy.com' },
			{ name: 'Estée Lauder', domain: 'esteelauder.com' },
			{ name: 'Clinique', domain: 'clinique.com' },
			{ name: 'Clarins', domain: 'clarins.com' },
			{ name: 'Lancôme', domain: 'lancome.com' },
			{ name: 'YSL', domain: 'yslbeauty.com' },
			{ name: 'Collistar', domain: 'collistar.com' },
			{ name: 'Nouba', domain: 'nouba.it' },
			{ name: 'Pupa', domain: 'pupamilano.com' },
			{ name: 'Naj Oleari', domain: 'najoleari.com' },
			{ name: 'Artdeco', domain: 'artdeco.de' },
			{ name: 'Bourjois', domain: 'bourjois.com' },
			{ name: 'Max Factor', domain: 'maxfactor.com' },
			{ name: 'Makeup Revolution', domain: 'makeuprevolution.com', logo: './img/brands/makeup-revolution.png' },
			{ name: 'Shik Cosmetics', domain: 'shikcosmetics.com', logo: './img/brands/shik-cosmetics.png' },
			{ name: 'Lamel', domain: '', logo: './img/brands/lamel.png' },
			{ name: 'Real Techniques & Eco Tools', domain: 'realtechniques.com' }
		],
		comingSoon: ['Dolce & Gabbana']
	},

	skincare: {
		title: 'Skin care',
		headline: 'Clarins, Biotherm, Estée Lauder and more.',
		modalTitle: 'Skin care from trusted names.',
		items: [
			{ name: 'Clarins', domain: 'clarins.com' },
			{ name: 'Chanel', domain: 'chanel.com' },
			{ name: 'Dior', domain: 'dior.com' },
			{ name: 'Guerlain', domain: 'guerlain.com' },
			{ name: 'Givenchy', domain: 'givenchy.com' },
			{ name: 'Kenzo', domain: 'kenzo.com' },
			{ name: 'Estée Lauder', domain: 'esteelauder.com' },
			{ name: 'Clinique', domain: 'clinique.com' },
			{ name: 'Lancôme', domain: 'lancome.com' },
			{ name: 'Biotherm', domain: 'biotherm.com' },
			{ name: 'Collistar', domain: 'collistar.com' },
			{ name: 'Declaré', domain: '', logo: './img/brands/declare.png' },
			{ name: 'Elizabeth Arden', domain: 'elizabetharden.com' },
			{ name: 'Korres', domain: 'korres.com' },
			{ name: 'Frei Ol', domain: '', logo: './img/brands/frei-ol.png' },
			{ name: 'L’Action', domain: '', logo: './img/brands/laction.png' },
			{ name: 'Institut Arnaud', domain: '', logo: './img/brands/institut-arnaud.png' },
			{ name: 'Cottage', domain: '', logo: './img/brands/cottage.png' },
			{ name: 'Pupa', domain: 'pupamilano.com' },
			{ name: 'Artdeco', domain: 'artdeco.de' },
			{ name: 'Swiss Image', domain: '', logo: './img/brands/swiss-image.png' },
			{ name: 'Palmers', domain: '', logo: './img/brands/palmers.png' }
		],
		comingSoon: []
	}
}

// Публичный client ID Brandfetch Logo API (бесплатный тариф, рассчитан на использование в коде сайта).
const BRANDFETCH_CLIENT_ID = '1idGC1j585Xm5jQeL8q'

// Варианты картинки по порядку: локальный файл, надпись-логотип Brandfetch, квадратная иконка Brandfetch.
// Плитка пробует их по очереди, если ни один не загрузился — показывает название (sections/brands/brands.js).
export function getLogoUrls(brand) {
	const urls = []
	if (brand.logo) urls.push(brand.logo)
	if (brand.domain) {
		for (const type of ['logo', 'icon']) {
			urls.push(`https://cdn.brandfetch.io/${brand.domain}/w/256/h/256/fallback/404/type/${type}?c=${BRANDFETCH_CLIENT_ID}`)
		}
	}
	return urls
}
