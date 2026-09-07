<div class="popup popup_forgot login">
    <div class="popup__content">
        <div class="popup__body">
            <div class="popup__close"></div>
            <h2 class="login__title"><?= $menu['all'][14]->title ?></h2>
            <p class="login__text"><?=RESET_PASS_INFO?></p>
            <form action="/<?=$lclang?>/<?= $menu['all'][14]->uri ?>" class="login__form reset_form">
                <div class="error_reset"></div>
                <div class="success_reset"></div>
                <div class="login__row _item-input">
                    <label for="" class="login__label"><?=EMAIL_LOGIN?></label>
                    <input type="email" name="email" class="login__input">
                </div>
                <div class="login__footer">
                    <button type="submit" href="#confirm" class="login__btn _sumbit reset_send"><?=SEND?></button>
                    <a href="#login" class="login__back-login _popup-link"><?=BACK?></a>
                </div>
            </form>
        </div>
    </div>
</div>