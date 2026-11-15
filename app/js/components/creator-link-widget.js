(function () {
    const widget = document.querySelector('[data-creator-link-widget]');
    const dialog = widget && widget.querySelector('[data-creator-widget-dialog]');
    const backdrop = widget && widget.querySelector('[data-creator-widget-backdrop]');
    const openButtons = document.querySelectorAll('[data-creator-widget-open]');
    const closeButton = widget && widget.querySelector('[data-creator-widget-close]');

    const closeWidget = () => {
        if (!dialog || !backdrop) return;
        dialog.hidden = true;
        backdrop.hidden = true;
        document.documentElement.classList.remove('creator-widget-open');
        const trigger = widget.querySelector('[data-creator-widget-open]');
        if (trigger && !widget.hidden) trigger.focus();
    };

    if (widget && dialog && backdrop && closeButton) {
        openButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                if (widget.hidden) return;
                dialog.hidden = false;
                backdrop.hidden = false;
                document.documentElement.classList.add('creator-widget-open');
                closeButton.focus();
            });
        });
        closeButton.addEventListener('click', closeWidget);
        backdrop.addEventListener('click', closeWidget);

        document.addEventListener('keydown', (event) => {
            if (dialog.hidden) return;
            if (event.key === 'Escape') {
                closeWidget();
                return;
            }
            if (event.key !== 'Tab') return;
            const focusable = Array.from(dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled])'))
                .filter((element) => element.offsetParent !== null);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    const preferenceInputs = Array.from(document.querySelectorAll('[data-creator-preference-toggle]'));
    const preferenceGroups = Array.from(document.querySelectorAll('[data-creator-preferences]'));
    const widgetLabels = document.documentElement.lang === 'ro'
        ? { saving: 'Se salvează…', saved: 'Setarea a fost salvată.', sessionSaved: 'Setarea este păstrată până la ieșire.', failed: 'Setarea nu a putut fi salvată.' }
        : { saving: 'Сохраняем…', saved: 'Настройка сохранена.', sessionSaved: 'Настройка действует до выхода из аккаунта.', failed: 'Не удалось сохранить настройку.' };

    preferenceInputs.forEach((input) => {
        input.addEventListener('change', async () => {
            const setting = input.getAttribute('data-setting');
            if (!setting) return;
            const desiredValue = input.checked;
            const relatedInputs = preferenceInputs.filter((item) => item.getAttribute('data-setting') === setting);
            const endpoint = preferenceGroups[0] && preferenceGroups[0].getAttribute('data-endpoint') || '/creator/preferences';
            relatedInputs.forEach((item) => { item.disabled = true; });
            document.querySelectorAll('[data-creator-preferences-status]').forEach((node) => { node.textContent = widgetLabels.saving; });

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams({ setting, value: desiredValue ? '1' : '0' }),
                });
                const result = await response.json();
                if (!response.ok || result.status !== 'ok' || result.setting !== setting || Boolean(result.value) !== desiredValue) {
                    throw new Error('preference_save_failed');
                }
                relatedInputs.forEach((item) => { item.checked = desiredValue; });
                if (setting === 'widget_enabled' && widget) widget.hidden = !desiredValue;
                const message = result.persistent ? widgetLabels.saved : widgetLabels.sessionSaved;
                document.querySelectorAll('[data-creator-preferences-status]').forEach((node) => { node.textContent = message; });
            } catch (error) {
                relatedInputs.forEach((item) => { item.checked = !desiredValue; });
                document.querySelectorAll('[data-creator-preferences-status]').forEach((node) => { node.textContent = widgetLabels.failed; });
            } finally {
                relatedInputs.forEach((item) => { item.disabled = false; });
            }
        });
    });

    const dashboard = document.querySelector('[data-creator-dashboard]');
    if (dashboard) {
        const renderDashboardState = (state) => {
            if (!['loading', 'empty', 'error'].includes(state)) return;
            dashboard.setAttribute('data-creator-dashboard-state', state);
            dashboard.querySelectorAll('[data-creator-dashboard-pane]').forEach((pane) => {
                pane.hidden = pane.getAttribute('data-creator-dashboard-pane') !== state;
            });
        };
        document.addEventListener('creator:dashboard-state', (event) => {
            renderDashboardState(event.detail && event.detail.state);
        });
    }

    document.querySelectorAll('[data-creator-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.getAttribute('data-creator-copy') || '';
            if (!value) return;
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(value);
                } else {
                    const temporary = document.createElement('textarea');
                    temporary.value = value;
                    temporary.setAttribute('readonly', 'readonly');
                    temporary.style.position = 'fixed';
                    temporary.style.opacity = '0';
                    document.body.appendChild(temporary);
                    temporary.select();
                    const copied = document.execCommand('copy');
                    temporary.remove();
                    if (!copied) throw new Error('clipboard_unavailable');
                }
                const original = button.textContent;
                button.textContent = document.documentElement.lang === 'ro' ? 'Copiat' : 'Скопировано';
                window.setTimeout(() => { button.textContent = original; }, 1400);
            } catch (error) {
                button.textContent = document.documentElement.lang === 'ro' ? 'Copiați manual' : 'Скопируйте вручную';
            }
        });
    });
})();
