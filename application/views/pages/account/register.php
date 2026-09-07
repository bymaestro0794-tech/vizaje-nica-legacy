<div class="popup popup_register login">
    <div class="popup__content">
        <div class="popup__body">
            <div class="popup__close"></div>
            <h2 class="login__title"><?= $menu['all'][27]->title ?></h2>
            <form action="#" class="login__form">
                <div class="login__row _item-input">
                    <label for="" class="login__label"><?=PHONE_NUMBER?></label>
                    <input type="text" name="number" data-value="" class="login__input _phone _mask" inputmode="text">
                </div>
                <div class="login__row _item-input">
                    <label for="" class="login__label"><?=EMAIL_LOGIN?></label>
                    <input type="text" name="email" class="login__input">
                </div>
                <div class="login__actions">
                    <div class="login__checks">
                        <div class="login__check">
                            <label class="checkbox _focus">
                                <input class="checkbox__input _focus" type="checkbox" value="1" name="register_check" required>
                                <span class="checkbox__text"><span><?=AGREE_WITH?> <a href="/<?=$lclang?>/<?=$menu['all'][22]->uri?>" target="_blank"><?=$menu['all'][22]->title?></a>.</span></span>
                            </label>
                        </div>
                        <div class="login__check">
                            <label class="checkbox _focus">
                                <input class="checkbox__input _focus" type="checkbox" value="1" name="register_newsletters">
                                <span class="checkbox__text"><span><?=RECEIVE_PROMO_REG?></span></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="login__under under-login">
                    <a href="#login" class="under-login__btn _popup-link _login"><?=HAVE_ACCOUNT?></a>
                    <button type="submit" class="under-login__btn _sumbit"><?=REGISTER?></button>
                </div>
            </form>
        </div>
    </div>
</div>