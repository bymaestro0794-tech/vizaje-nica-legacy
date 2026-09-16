<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$feedbackIsRomanian = isset($lclang) && $lclang === 'ro';
$feedbackLinkText = $feedbackIsRomanian
    ? 'Aveți o idee sau ați găsit o eroare?'
    : 'Есть идея или нашли ошибку?';
$feedbackTitle = $feedbackIsRomanian
    ? 'Ajutați-ne să îmbunătățim site-ul'
    : 'Помогите улучшить сайт';
$feedbackIntro = $feedbackIsRomanian
    ? 'Spuneți-ne despre o idee sau o eroare.'
    : 'Расскажите об идее или сообщите об ошибке.';
$feedbackTypeLabel = $feedbackIsRomanian ? 'Tipul mesajului' : 'Тип сообщения';
$feedbackMessageLabel = $feedbackIsRomanian ? 'Mesaj' : 'Сообщение';
$feedbackImagesLabel = $feedbackIsRomanian ? 'Imagini' : 'Изображения';
$feedbackOptional = $feedbackIsRomanian ? '(opțional)' : '(необязательно)';
$feedbackImagesHint = $feedbackIsRomanian
    ? 'Până la 3 fișiere, maximum 5 MB fiecare.'
    : 'До 3 файлов, максимум 5 МБ каждый.';
$feedbackPlaceholder = $feedbackIsRomanian
    ? 'Descrieți ideea sau eroarea dvs....'
    : 'Опишите идею или ошибку...';
$feedbackTypes = $feedbackIsRomanian
    ? array('idea' => 'Idee', 'bug' => 'Eroare')
    : array('idea' => 'Идея', 'bug' => 'Ошибка');
?>

<button type="button" class="vn-feedback-footer-link main-footer__link" data-vn-feedback-open>
    <?= html_escape($feedbackLinkText) ?>
</button>

<div class="vn-feedback-modal" data-vn-feedback-modal aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="vn-feedback-title">
    <div class="vn-feedback-modal__backdrop" data-vn-feedback-close></div>
    <div class="vn-feedback-modal__dialog" role="document">
        <button type="button" class="vn-feedback-modal__close" data-vn-feedback-close aria-label="Закрыть">
            <span aria-hidden="true">×</span>
        </button>
        <h2 id="vn-feedback-title"><?= html_escape($feedbackTitle) ?></h2>
        <p class="vn-feedback-modal__intro"><?= html_escape($feedbackIntro) ?></p>

        <form data-vn-feedback-form novalidate>
            <label><?= html_escape($feedbackTypeLabel) ?></label>
            <div class="vn-feedback-type-options" role="group" aria-label="<?= html_escape($feedbackTypeLabel) ?>">
                <button type="button" class="vn-feedback-type-option is-selected" data-vn-feedback-type="idea" aria-pressed="true">
                    <i class="fa fa-lightbulb-o" aria-hidden="true"></i>
                    <span><?= html_escape($feedbackTypes['idea']) ?></span>
                </button>
                <button type="button" class="vn-feedback-type-option" data-vn-feedback-type="bug" aria-pressed="false">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    <span><?= html_escape($feedbackTypes['bug']) ?></span>
                </button>
            </div>
            <input type="hidden" name="feedback_type" value="idea">

            <label for="vn-feedback-message"><?= html_escape($feedbackMessageLabel) ?></label>
            <textarea id="vn-feedback-message" name="message" rows="5" minlength="3" maxlength="5000" required placeholder="<?= html_escape($feedbackPlaceholder) ?>"></textarea>

            <label for="vn-feedback-images">
                <?= html_escape($feedbackImagesLabel) ?>
                <span class="vn-feedback-optional"><?= html_escape($feedbackOptional) ?></span>
            </label>
            <input id="vn-feedback-images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
            <small class="vn-feedback-file-hint"><?= html_escape($feedbackImagesHint) ?></small>

            <input type="text" name="website" tabindex="-1" autocomplete="off" class="vn-feedback-honeypot" aria-hidden="true">
            <input type="hidden" name="page_path" value="">
            <input type="hidden" name="locale" value="">

            <div class="vn-feedback-modal__error" data-vn-feedback-error role="alert" hidden></div>
            <div class="vn-feedback-modal__success" data-vn-feedback-success role="status" hidden>
                Спасибо! Сообщение отправлено.
            </div>

            <button type="submit" class="vn-feedback-modal__submit">Отправить</button>
        </form>
    </div>
</div>
