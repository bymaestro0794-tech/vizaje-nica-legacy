import { openModal } from '../../js/lib/modal.js'
import { interactive, revealOnView, springs } from '../../js/lib/motion.js'

const { animate } = Motion

const content = {
	retail: {
		eyebrow: 'Retail',
		title: 'Beauty for everyone, closer to you.',
		image: '/app/b2b/img/our-bussines/modal/store.jpg',
		items: [
			{
				label: 'Our stores.',
				text: '12 doors in Chisinau, with one new door opening by mid 2026.'
			},
			{
				label: 'Partners.',
				text: '4 VN franchisee partners and 3 exclusive regional partners.'
			},
			{
				label: 'Beauty advisors.',
				text: 'A BA and training team of 60+ people.'
			}
		]
	},
	distribution: {
		eyebrow: 'Distribution',
		title: 'Stronger brands. Wider reach.',
		image: '/app/b2b/img/our-bussines/modal/warhouse.jpg',
		items: [
			{
				label: 'Network.',
				text: 'A strong distribution network with full market coverage.'
			},
			{
				label: 'Logistics.',
				text: 'Own warehouse and logistics, with a fully automated IT system and ordering process.'
			},
			{ label: 'Team.', text: 'A dedicated marketing and sales team.' }
		]
	},
	ecom: {
		eyebrow: 'E-com',
		title: 'Beauty anywhere, closer to you.',
		// TODO: нет отдельной картинки для модалки E-com, временно берём web.png
		image: '/app/b2b/img/our-bussines/web.jpg',
		items: [
			{
				label: 'Assortment.',
				text: 'Over 90% of our assortment is available online.'
			},
			{
				label: 'Delivery.',
				text: 'Same-day delivery and shipping country-wide.'
			},
			{
				label: 'Apps & CRM.',
				text: 'Our own shopping app, easy and effective CRM, and an omni-channel approach.'
			}
		]
	}
}

export function initOurBusiness() {
	const cards = document.querySelectorAll('.business-card')

	revealOnView(document.querySelector('.our-business__cards'), cards)

	cards.forEach(card => {
		const image = card.querySelector('.business-card__image img')
		const plus = card.querySelector('.business-card__plus')

		interactive(card, {
			hoverScale: 1.02,
			tapScale: 0.98,
			onHover: isHovered => {
				animate(image, { scale: isHovered ? 1.06 : 1 }, springs.soft)
				animate(plus, { rotate: isHovered ? 90 : 0 }, springs.responsive)
			}
		})

		card.addEventListener('click', () => {
			openModal(content[card.dataset.modal])
		})
	})
}
