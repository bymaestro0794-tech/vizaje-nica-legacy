<div class="popup popup_login login">
    <div class="popup__content">
        <div class="popup__body">
            <div class="popup__close"></div>
            <h2 class="login__title"><?= $menu['all'][12]->title ?></h2>
            <form action="/<?=$lclang?>/<?= $menu['all'][12]->uri ?>" class="login__form login_form">
                <div class="error_login"></div>
                <div class="login__row _item-input">
                    <label for="" class="login__label"><?=PHONE_NUMBER?></label>
                    <input type="text" name="number" data-value="" class="login__input _phone">
                </div>
                <div class="login__row _item-input">
                    <label for="" class="login__label"><?=PASSWORD?></label>
                    <input type="password" name="password" class="login__input">
                </div>
                <div class="login__actions">
                    <a href="#forgot" class="login__forgot _popup-link"><?=FORGOT_YOUR_PASS?></a>
                </div>
                <div class="login__footer">
                    <button type="submit" class="login__btn"><?= $menu['all'][12]->title ?></button>
                    <div class="login__proseed _popup-close"><?=CONTINUE_NO_REGISTRATION?></div>
                </div>
            </form>
        </div>
    </div>
</div>