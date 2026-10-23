<?php
defined('BASEPATH') or exit('No direct script access allowed');

$isRomanian = ($lclang ?? 'ru') === 'ro';
$instagramUrl = 'https://www.instagram.com/vizaje_nica/?hl=ru';
$instagramPosts = [
	'https://www.instagram.com/p/Dd3O6Lst_wr/',
	'https://www.instagram.com/p/DdtzaqbtCkH/',
	'https://www.instagram.com/p/Ddtz9dmt_it/',
	'https://www.instagram.com/p/Dd3zigKtcNF/',
	'https://www.instagram.com/p/Dd1rFUltPJa/',
	'https://www.instagram.com/p/Dd0wDl0NnHZ/',
	'https://www.instagram.com/p/Dd0hgDEOJ6n/',
	'https://www.instagram.com/p/DdzPawuIBRk/',
	'https://www.instagram.com/p/DdyiLWlNfxm/',
	'https://www.instagram.com/p/DdwCtMvtJvr/',
	'https://www.instagram.com/p/Dds7UrpizQJ/',
	'https://www.instagram.com/p/DdreFGjNyXH/',
	'https://www.instagram.com/p/Ddo6QdUt6XD/',
	'https://www.instagram.com/p/DdrYjraNMyO/',
];
$instagramTitle = $isRomanian
	? 'Urmărește-ne pe Instagram'
	: 'Следите за нами в Instagram';
$instagramOpenLabel = $isRomanian
	? 'Vezi această postare pe Instagram'
	: 'Открыть публикацию в Instagram';
?>

<section class="home-instagram" aria-labelledby="home-instagram-title" data-instagram-carousel>
	<div class="home-instagram__container _container">
		<header class="home-instagram__header">
			<h2 class="home-instagram__title" id="home-instagram-title"><?= htmlspecialchars($instagramTitle, ENT_QUOTES, 'UTF-8') ?></h2>
			<p class="home-instagram__description">
				<?= $isRomanian
					? 'Idei de frumusețe, produse preferate și noutăți din lumea Vizaje-Nica.'
					: 'Идеи для красоты, любимые средства и новости мира Vizaje-Nica.' ?>
			</p>
		</header>
		<div class="home-instagram__track" aria-label="Instagram Vizaje-Nica" data-instagram-track>
			<?php foreach ($instagramPosts as $postUrl) : ?>
				<div
					class="home-instagram__post"
					data-instagram-post
					data-permalink="<?= htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8') ?>"
				>
					<a class="home-instagram__fallback" href="<?= htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
						<?= htmlspecialchars($instagramOpenLabel, ENT_QUOTES, 'UTF-8') ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<a class="home-instagram__link" href="<?= htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
			<span aria-hidden="true">◎</span> @vizaje_nica
		</a>
	</div>
</section>
