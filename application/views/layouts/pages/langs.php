<?php if (!empty($langs_array)) : ?>
    <?php foreach ($langs_array as $lang => $link): ?>
        <?php if($lang == 'en') continue;?>
        <?php
        $langLink = $protocol . $host . '/' . \strtolower($lang);
        $langLink .= (isset($page_uri)) ? '/' . $page_uri : '';
        if (is_array($link)) {
            foreach ($link as $item) {
                $langLink .= (!empty($item)) ? '/' . $item : '';
            }
        } else {
            $langLink .= (!empty($link)) ? '/' . $link : '';
        }
        $data_link_without_get = $langLink;
        $langLink .= (!empty($get_data)) ? '?' . $get_data : '';
        ?>
        <a href="javascript:;"
           class="language-main-header__item <?= uri(1) == $lang || $_SESSION['lang'] == $lang ? '_active' : '' ?>"
           data-link="<?= $data_link_without_get ?>"
           title="<?= $lang ?>" onclick="changeLangue(this)">
            <?= $lang_title[$lang] ?>
        </a>
    <?php endforeach; ?>
<?php endif; ?>
