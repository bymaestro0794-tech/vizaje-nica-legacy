<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$isRo = $lclang === 'ro';
$values = isset($form_values) && is_array($form_values) ? $form_values : array();
$clientName = trim((string) ($creator_client->name ?? '') . ' ' . (string) ($creator_client->surname ?? ''));
$assetBase = function_exists('base_url') ? rtrim(base_url(), '/') . '/' : '/';
$draftKey = 'vizaje.creator-application.draft.v1.' . (int) ($creator_client->id ?? 0);
if (empty($form_error)) {
    $profileBirthday = trim((string) ($creator_client->birthday ?? ''));
    if (empty($values['date_of_birth']) && preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $profileBirthday, $birthdayParts)
        && checkdate((int) $birthdayParts[2], (int) $birthdayParts[1], (int) $birthdayParts[3])) {
        $values['date_of_birth'] = $birthdayParts[3] . '-' . $birthdayParts[2] . '-' . $birthdayParts[1];
    } elseif (empty($values['date_of_birth']) && preg_match('/^\d{4}-\d{2}-\d{2}(?:\s|$)/', $profileBirthday)) {
        $values['date_of_birth'] = substr($profileBirthday, 0, 10);
    }
    if (empty($values['sex']) && preg_match('/^[12](?:\.0+)?$/', trim((string) ($creator_client->sex ?? '')))) {
        $values['sex'] = (string) (int) $creator_client->sex;
    }
}
$value = static function ($key) use ($values) { return html_escape((string) ($values[$key] ?? '')); };
?>
<link rel="stylesheet" href="<?= html_escape($assetBase) ?>static/assets/global/plugins/font-awesome/css/font-awesome.css">
<style>
.creator-application{max-width:820px;margin:40px auto 72px;padding:0 24px;color:#111;font-family:inherit}
.creator-application__card{padding:36px 40px;background:#fff;border:1px solid #e7e7e7}
.creator-application h1{margin:0 0 12px;font-size:32px;line-height:1.2}
.creator-application p{color:#666;line-height:1.6}
.creator-application__identity{margin:22px 0 30px;padding:16px 18px;background:#f7f7f7;line-height:1.8}
.creator-application__error{padding:12px 14px;background:#fff1f0;color:#9f1c14!important}
.creator-application__profile{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:30px}
.creator-application__field>label,.creator-application__group-title{display:block;margin:0 0 12px;font-size:17px;font-weight:400;color:#111}
.creator-application select{appearance:none!important;-webkit-appearance:none!important;box-sizing:border-box;width:100%;min-height:54px;padding:12px 48px 12px 14px;border:1px solid #dedede!important;border-radius:12px!important;background-color:#fff!important;background-image:url('<?= html_escape($assetBase) ?>app/img/icons/select.svg')!important;background-repeat:no-repeat!important;background-position:right 16px center!important;background-size:16px 16px!important;color:#111!important;font:inherit!important;cursor:pointer}
.creator-application__select{position:relative;width:100%}
.creator-application__select.is-enhanced .creator-application__native-select{position:absolute!important;width:1px!important;height:1px!important;min-height:0!important;padding:0!important;overflow:hidden!important;border:0!important;background:none!important;opacity:0!important;pointer-events:none!important}
.creator-application .creator-application__select-trigger{display:flex;align-items:center;justify-content:space-between;box-sizing:border-box;width:100%;min-height:56px;padding:0 48px 0 18px;border:1px solid #dedede;border-radius:12px;background:#fff url('<?= html_escape($assetBase) ?>app/img/icons/select.svg') right 16px center/16px 16px no-repeat;color:#111;font:inherit;font-size:17px;font-weight:400;text-align:left;cursor:pointer}
.creator-application__select-trigger.is-placeholder{color:#858585}
.creator-application__select-trigger:focus-visible{border-color:#111;outline:2px solid rgba(17,17,17,.12);outline-offset:1px}
.creator-application__select.has-error .creator-application__select-trigger{border-color:#b42318}
.creator-application__select-menu{position:absolute;z-index:20;top:calc(100% + 6px);left:0;box-sizing:border-box;width:100%;max-height:260px;overflow-y:auto;padding:6px;border:1px solid #dedede;border-radius:12px;background:#fff;box-shadow:0 10px 28px rgba(0,0,0,.12)}
.creator-application .creator-application__select-option{display:block;width:100%;min-height:0;padding:11px 12px;border:0;border-radius:7px;background:#fff;color:#111;font:inherit;font-size:16px;font-weight:400;text-align:left;cursor:pointer}
.creator-application__select-option:hover,.creator-application__select-option:focus-visible,.creator-application__select-option[aria-selected="true"]{outline:0;background:#f2f2f2}
.creator-application__field input,.creator-application__field textarea{box-sizing:border-box;width:100%;min-height:54px;padding:12px 14px;border:1px solid #dedede;border-radius:12px;background:#fff;color:#111;font:inherit}
.creator-application__field textarea{min-height:130px;resize:vertical;line-height:1.5}
.creator-application__socials{display:grid;gap:24px;margin:0 0 38px}
.creator-application__social-row{display:grid;grid-template-columns:56px 1fr;align-items:center;gap:24px}
.creator-application__social-icon{display:flex;align-items:center;justify-content:center;color:#111;font-size:25px}
.creator-application__social-icon img{display:block;width:24px;height:24px;object-fit:contain}
.creator-application__social-field label{display:block;margin:0 0 4px;font-size:16px;color:#111}
.creator-application__social-field input{box-sizing:border-box;width:100%;height:40px;padding:3px 0;border:0;border-bottom:1px solid #aaa;border-radius:0;background:transparent;color:#111;font:inherit;font-size:17px;outline:none}
.creator-application__social-field input:focus{border-bottom:2px solid #111}
.creator-application__social-field input::placeholder,.creator-application__field textarea::placeholder{color:#858585;opacity:1}
.creator-application__audience{margin-bottom:0}
.creator-application__audience>label{margin-bottom:16px}
.creator-application__audience select{height:56px;color:#777;font-size:17px}
.creator-application__hint{display:flex;align-items:center;gap:12px;margin:24px 0;color:#777;font-size:14px}
.creator-application__hint.is-error{color:#b42318}
.creator-application__hint i{font-size:18px;color:#888}
.creator-application__format{margin:0 0 32px;padding:0;border:0}
.creator-application__format legend{margin-bottom:16px;font-size:17px}
.creator-application__radio-list{display:flex;flex-wrap:wrap;gap:18px 30px}
.creator-application__radio{display:inline-flex!important;align-items:center;gap:12px;margin:0!important;font-size:16px!important;white-space:nowrap}
.creator-application__radio input{appearance:none!important;-webkit-appearance:none!important;box-sizing:border-box;flex:0 0 24px;width:24px;height:24px;min-height:24px;margin:0;border:1px solid #111!important;border-radius:50%;background:#fff!important;cursor:pointer}
.creator-application__radio input:checked{background:radial-gradient(circle at center,#111 0 5px,#fff 5.5px)!important}
.creator-application__field{margin-bottom:26px}
.creator-application__consent{display:flex!important;align-items:flex-start;gap:16px;margin:26px 0!important;font-size:15px!important;line-height:1.55}
.creator-application__consent input{appearance:none!important;-webkit-appearance:none!important;box-sizing:border-box;flex:0 0 24px;width:24px;height:24px;min-height:24px;margin:0;border:1px solid #111;border-radius:4px;background:#fff;cursor:pointer}
.creator-application__consent input:checked{border-color:#111!important;background:#111 url('<?= html_escape($assetBase) ?>app/img/icons/check.svg') center/14px 10px no-repeat!important}
.creator-application__consent input:focus-visible{outline:2px solid #777;outline-offset:3px}
.creator-application__consent a{position:relative;top:-1px;margin-left:4px;color:#111;text-decoration:underline}
.creator-application__actions{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-top:32px}
.creator-application__actions a{color:#333}
.creator-application button{min-height:52px;padding:0 30px;border:0;border-radius:28px;background:#111;color:#fff;font:inherit;font-weight:600;cursor:pointer}
@media(max-width:640px){.creator-application{margin:22px auto 48px;padding:0 14px}.creator-application__card{padding:24px 18px}.creator-application h1{font-size:26px}.creator-application__profile{grid-template-columns:1fr;gap:14px}.creator-application__socials{gap:20px}.creator-application__social-row{grid-template-columns:36px 1fr;gap:14px}.creator-application__radio-list{flex-direction:column;align-items:flex-start;gap:16px}.creator-application__actions{align-items:stretch;flex-direction:column-reverse}.creator-application button{width:100%}}
</style>
<main class="creator-application">
    <section class="creator-application__card">
        <h1><?= $isRo ? 'Cerere de participare' : 'Заявка на участие' ?></h1>
        <p><?= $isRo ? 'Completați formularul. Vă vom comunica decizia prin email.' : 'Заполните заявку. Мы сообщим решение на email вашего аккаунта.' ?></p>
        <div class="creator-application__identity">
            <strong><?= html_escape($clientName) ?></strong><br>
            <?= html_escape((string) ($creator_client->email ?? '')) ?><br>
            <?= html_escape((string) ($creator_client->phone ?? '')) ?>
        </div>
        <?php if (!empty($form_error)) { ?><p class="creator-application__error" role="alert"><?= html_escape($form_error) ?></p><?php } ?>
        <form method="post" action="/<?= $isRo ? 'ro/parteneri' : 'ru/partners' ?>/apply/submit" data-draft-key="<?= html_escape($draftKey) ?>" data-server-errors="<?= !empty($form_error) ? '1' : '0' ?>">
            <input type="hidden" name="creator_application_csrf" value="<?= html_escape((string) ($creator_application_csrf ?? '')) ?>">
            <div class="creator-application__profile">
                <div class="creator-application__field"><label for="date_of_birth"><?= $isRo ? 'Data nașterii' : 'Дата рождения' ?></label><input id="date_of_birth" name="date_of_birth" type="date" max="<?= date('Y-m-d') ?>" value="<?= $value('date_of_birth') ?>" required></div>
                <div class="creator-application__field"><label for="sex-trigger"><?= $isRo ? 'Gen' : 'Пол' ?></label><select id="sex" name="sex" class="creator-application__native-select"><option value=""><?= $isRo ? 'Selectați' : 'Выберите' ?></option><option value="1" <?= $value('sex') === '1' ? 'selected' : '' ?>><?= $isRo ? 'Femeie' : 'Женский' ?></option><option value="2" <?= $value('sex') === '2' ? 'selected' : '' ?>><?= $isRo ? 'Bărbat' : 'Мужской' ?></option></select></div>
            </div>
            <div class="creator-application__socials">
                <div class="creator-application__social-row"><span class="creator-application__social-icon"><img src="<?= html_escape($assetBase) ?>app/img/icons/social/inst.svg" alt=""></span><div class="creator-application__social-field"><label for="instagram">Instagram</label><input id="instagram" name="instagram" type="url" maxlength="500" value="<?= $value('instagram') ?>" placeholder="<?= $isRo ? 'Link către profil' : 'Ссылка на профиль' ?>"></div></div>
                <div class="creator-application__social-row"><span class="creator-application__social-icon"><img src="<?= html_escape($assetBase) ?>app/img/icons/social/tiktok.svg" alt=""></span><div class="creator-application__social-field"><label for="tiktok">TikTok</label><input id="tiktok" name="tiktok" type="url" maxlength="500" value="<?= $value('tiktok') ?>" placeholder="<?= $isRo ? 'Link către profil' : 'Ссылка на профиль' ?>"></div></div>
                <div class="creator-application__social-row"><span class="creator-application__social-icon" aria-hidden="true"><i class="fa fa-globe"></i></span><div class="creator-application__social-field"><label for="other_social"><?= $isRo ? 'Altă platformă' : 'Другая платформа' ?></label><input id="other_social" name="other_social" type="url" maxlength="500" value="<?= $value('other_social') ?>" placeholder="<?= $isRo ? 'Link' : 'Ссылка' ?>"></div></div>
            </div>
            <div class="creator-application__field creator-application__audience"><label for="audience_size-trigger"><?= $isRo ? 'Mărimea audienței *' : 'Размер аудитории *' ?></label><select id="audience_size" name="audience_size" class="creator-application__native-select" data-required="true"><option value=""><?= $isRo ? 'Alegeți intervalul' : 'Выберите диапазон' ?></option><?php $audiences = array('under_1000' => 'до 1 000', '1000_5000' => '1 000–5 000', '5000_10000' => '5 000–10 000', '10000_50000' => '10 000–50 000', '50000_100000' => '50 000–100 000', 'over_100000' => 'более 100 000'); foreach ($audiences as $key => $label) { ?><option value="<?= $key ?>" <?= $value('audience_size') === $key ? 'selected' : '' ?>><?= $label ?></option><?php } ?></select></div>
            <p class="creator-application__hint" aria-live="polite"><i class="fa fa-info-circle" aria-hidden="true"></i><?= $isRo ? 'Adăugați cel puțin o rețea socială.' : 'Укажите хотя бы одну социальную сеть.' ?></p>
            <fieldset class="creator-application__format"><legend><?= $isRo ? 'Format de colaborare *' : 'Формат сотрудничества *' ?></legend><div class="creator-application__radio-list"><?php $formats = array('blogger_influencer' => $isRo ? 'Blogger / Influencer' : 'Блогер / Инфлюенсер', 'ugc_creator' => $isRo ? 'Creator UGC' : 'UGC-креатор', 'both' => $isRo ? 'Ambele formate' : 'Оба формата'); foreach ($formats as $key => $label) { ?><label class="creator-application__radio"><input type="radio" name="partnership_format" value="<?= $key ?>" <?= $value('partnership_format') === $key ? 'checked' : '' ?> required><span><?= html_escape($label) ?></span></label><?php } ?></div></fieldset>
            <div class="creator-application__field"><label for="content_description"><?= $isRo ? 'Ce tip de conținut creați? *' : 'Какой контент вы создаёте? *' ?></label><textarea id="content_description" name="content_description" maxlength="1000" placeholder="<?= $isRo ? 'Descrieți formatele, temele și stilul conținutului...' : 'Расскажите о форматах, темах и стиле вашего контента...' ?>" required><?= $value('content_description') ?></textarea></div>
            <div class="creator-application__field"><label for="partnership_reason"><?= $isRo ? 'De ce doriți să colaborați cu Vizaje-Nica? *' : 'Почему хотите сотрудничать с Vizaje-Nica? *' ?></label><textarea id="partnership_reason" name="partnership_reason" maxlength="1000" placeholder="<?= $isRo ? 'Spuneți-ne ce vă atrage la programul nostru...' : 'Расскажите, что вас привлекает в нашей партнёрской программе...' ?>" required><?= $value('partnership_reason') ?></textarea></div>
            <label class="creator-application__consent"><input type="checkbox" name="consent" value="1" <?= $value('consent') === '1' ? 'checked' : '' ?> required><span><?= $isRo ? 'Sunt de acord cu prelucrarea datelor personale în conformitate cu ' : 'Я согласен(-на) на обработку персональных данных в соответствии с ' ?><a href="/<?= $lclang ?>/<?= html_escape($menu['all'][23]->uri ?? '') ?>" target="_blank" rel="noopener noreferrer"><?= html_escape($menu['all'][23]->title ?? ($isRo ? 'Politica de confidențialitate' : 'Политикой конфиденциальности')) ?></a>.</span></label>
            <div class="creator-application__actions"><a href="/<?= $lclang ?>/<?= html_escape($menu['all'][15]->uri) ?>"><?= $isRo ? 'Înapoi la cont' : 'Вернуться в аккаунт' ?></a><button type="submit"><?= $isRo ? 'Trimite cererea' : 'Отправить заявку' ?></button></div>
        </form>
    </section>
</main>
<script>
(function () {
    var root = document.querySelector('.creator-application');
    if (!root) return;
    var form = root.querySelector('form');
    var draftKey = form ? form.getAttribute('data-draft-key') : '';
    var draftFields = ['date_of_birth', 'sex', 'audience_size', 'instagram', 'tiktok', 'other_social', 'partnership_format', 'content_description', 'partnership_reason'];
    var widgets = [];
    root.querySelectorAll('select.creator-application__native-select').forEach(function (select) {
        var wrapper = document.createElement('div');
        wrapper.className = 'creator-application__select';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        wrapper.classList.add('is-enhanced');

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.id = select.id + '-trigger';
        trigger.className = 'creator-application__select-trigger';
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        var menu = document.createElement('div');
        menu.className = 'creator-application__select-menu';
        menu.id = select.id + '-options';
        menu.setAttribute('role', 'listbox');
        menu.hidden = true;
        trigger.setAttribute('aria-controls', menu.id);

        var options = [];
        Array.prototype.forEach.call(select.options, function (option) {
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'creator-application__select-option';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', option.selected ? 'true' : 'false');
            item.textContent = option.textContent;
            item.addEventListener('click', function () {
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                update();
                close();
                trigger.focus();
            });
            menu.appendChild(item);
            options.push({ native: option, button: item });
        });

        function update() {
            var selected = select.options[select.selectedIndex];
            trigger.textContent = selected ? selected.textContent : '';
            trigger.classList.toggle('is-placeholder', !select.value);
            options.forEach(function (entry) {
                entry.button.setAttribute('aria-selected', entry.native.value === select.value ? 'true' : 'false');
            });
            wrapper.classList.remove('has-error');
            trigger.removeAttribute('aria-invalid');
        }
        function close() {
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        }
        trigger.addEventListener('click', function () {
            var isOpen = trigger.getAttribute('aria-expanded') === 'true';
            menu.hidden = isOpen;
            trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            if (!isOpen) {
                var firstOption = options.find(function (entry) { return entry.native.value !== ''; });
                if (firstOption) firstOption.button.focus();
            }
        });
        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                trigger.click();
            }
        });
        menu.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                close();
                trigger.focus();
            }
        });
        select.addEventListener('change', update);
        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        select.setAttribute('aria-hidden', 'true');
        select.tabIndex = -1;
        update();
        widgets.push({ select: select, wrapper: wrapper, trigger: trigger, close: close });
    });

    document.addEventListener('click', function (event) {
        widgets.forEach(function (widget) {
            if (!widget.wrapper.contains(event.target)) widget.close();
        });
    });
    function getDraft() {
        var draft = {};
        draftFields.forEach(function (name) {
            var controls = form.querySelectorAll('[name="' + name + '"]');
            if (!controls.length) return;
            if (controls[0].type === 'radio') {
                var selected = Array.prototype.find.call(controls, function (control) { return control.checked; });
                draft[name] = selected ? selected.value : '';
            } else {
                draft[name] = controls[0].value;
            }
        });
        return draft;
    }
    function saveDraft() {
        if (!draftKey || !form) return;
        try { localStorage.setItem(draftKey, JSON.stringify({ savedAt: Date.now(), values: getDraft() })); } catch (error) {}
    }
    function restoreDraft() {
        if (!draftKey || !form || form.getAttribute('data-server-errors') === '1') return false;
        try {
            var saved = JSON.parse(localStorage.getItem(draftKey) || 'null');
            if (!saved || !saved.values || typeof saved.values !== 'object') return false;
            draftFields.forEach(function (name) {
                if (typeof saved.values[name] !== 'string') return;
                var controls = form.querySelectorAll('[name="' + name + '"]');
                if (!controls.length) return;
                if (controls[0].type === 'radio') {
                    controls.forEach(function (control) { control.checked = control.value === saved.values[name]; });
                } else if (controls[0].tagName === 'SELECT') {
                    var hasOption = Array.prototype.some.call(controls[0].options, function (option) { return option.value === saved.values[name]; });
                    if (hasOption) {
                        controls[0].value = saved.values[name];
                        controls[0].dispatchEvent(new Event('change', { bubbles: true }));
                    }
                } else {
                    var maxLength = controls[0].maxLength > 0 ? controls[0].maxLength : 1000;
                    controls[0].value = saved.values[name].slice(0, maxLength);
                }
            });
            return true;
        } catch (error) { return false; }
    }
    if (form) {
        var restoredDraft = restoreDraft();
        if (form.getAttribute('data-server-errors') === '1' || !restoredDraft) saveDraft();
        var saveTimer = null;
        form.addEventListener('input', function () {
            window.clearTimeout(saveTimer);
            saveTimer = window.setTimeout(saveDraft, 300);
        });
        form.addEventListener('change', saveDraft);
        form.addEventListener('focusout', saveDraft);
        form.addEventListener('submit', saveDraft);
        window.addEventListener('pagehide', saveDraft);
    }
    if (form) form.addEventListener('submit', function (event) {
        for (var i = 0; i < widgets.length; i++) {
            var widget = widgets[i];
            if (widget.select.dataset.required === 'true' && !widget.select.value) {
                event.preventDefault();
                widget.wrapper.classList.add('has-error');
                widget.trigger.setAttribute('aria-invalid', 'true');
                widget.trigger.focus();
                break;
            }
        }
        if (event.defaultPrevented) return;
        var socials = root.querySelectorAll('.creator-application__social-field input');
        var hasSocial = Array.prototype.some.call(socials, function (input) { return input.value.trim() !== ''; });
        var hint = root.querySelector('.creator-application__hint');
        if (!hasSocial) {
            event.preventDefault();
            if (hint) hint.classList.add('is-error');
            if (socials.length) socials[0].focus();
        }
    });
    root.querySelectorAll('.creator-application__social-field input').forEach(function (input) {
        input.addEventListener('input', function () {
            var socials = root.querySelectorAll('.creator-application__social-field input');
            var hasSocial = Array.prototype.some.call(socials, function (field) { return field.value.trim() !== ''; });
            var hint = root.querySelector('.creator-application__hint');
            if (hint && hasSocial) hint.classList.remove('is-error');
        });
    });
})();
</script>
