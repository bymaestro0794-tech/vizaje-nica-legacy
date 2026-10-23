<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = ($lclang ?? 'ru') === 'ro';
?>

<section class="home-about" aria-labelledby="home-about-title">
	<div class="home-about__container _container">
		<div class="home-about__media">
			<img
				class="home-about__image"
				src="/app/img/b2b/home-store.png"
				alt="<?= $isRomanian ? 'Magazin Vizaje-Nica' : 'Магазин Vizaje-Nica' ?>"
				width="1448"
				height="1086"
				loading="lazy"
				decoding="async"
			>
		</div>

		<div class="home-about__content">
			<p class="home-about__eyebrow">
				<?= $isRomanian ? 'DESPRE VIZAJE-NICA' : 'О VIZAJE-NICA' ?>
			</p>
			<h2 class="home-about__title" id="home-about-title">
				<?= $isRomanian
					? 'Din 1990, vă ajutăm să descoperiți lumea frumuseții.'
					: 'С 1990 года мы помогаем открывать мир красоты.' ?>
			</h2>

			<div class="home-about__body">
				<p>
					<?= $isRomanian
						? 'Vizaje-Nica este o rețea de magazine cu produse originale de cosmetică și parfumerie din Moldova. De peste trei decenii, le prezentăm clienților branduri internaționale cunoscute, noutăți din industria frumuseții și produse care devin parte din rutina de îngrijire de zi cu zi.'
						: 'Vizaje-Nica — сеть магазинов оригинальной косметики и парфюмерии в Молдове. Более трёх десятилетий мы знакомим покупателей с известными мировыми брендами, новинками индустрии красоты и средствами, которые становятся частью повседневного ухода.' ?>
				</p>
				<p>
					<?= $isRomanian
						? 'În sortimentul nostru găsiți parfumerie pentru femei și bărbați, cosmetice decorative, produse pentru îngrijirea tenului și a corpului, pentru păr, precum și carduri cadou. Reunim branduri din categorii de preț diferite — de la cosmetice accesibile pentru fiecare zi până la produse premium și parfumerie de nișă.'
						: 'В нашем ассортименте представлены женская и мужская парфюмерия, декоративная косметика, средства для ухода за кожей лица и тела, волосами, а также подарочные сертификаты. Мы объединяем бренды разных ценовых категорий — от доступной косметики на каждый день до премиальных средств и нишевой парфюмерии.' ?>
				</p>
				<p>
					<?= $isRomanian
						? 'În spatele fiecărui magazin Vizaje-Nica se află oameni care îi ajută pe clienți să navigheze prin varietatea de produse și să găsească ceea ce corespunde nevoilor și preferințelor lor. Continuăm să ne dezvoltăm sortimentul, să descoperim branduri noi și să facem cumpărăturile mai simple — atât în magazinele noastre, cât și online.'
						: 'За каждым магазином Vizaje-Nica стоят люди, которые помогают покупателям ориентироваться в разнообразии продуктов и находить то, что соответствует их потребностям и предпочтениям. Мы продолжаем развивать ассортимент, знакомиться с новыми брендами и делать покупки удобнее — как в наших магазинах, так и онлайн.' ?>
				</p>
			</div>

		</div>
	</div>
</section>
