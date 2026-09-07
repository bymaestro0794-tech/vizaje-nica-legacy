//Placeholers
let inputs = document.querySelectorAll('input,textarea');
inputs_init(inputs);

function inputs_init(inputs) {
    if (inputs.length > 0) {
        for (let index = 0; index < inputs.length; index++) {
            const input = inputs[index];
            const input_g_value = input.getAttribute('data-value');
            input_placeholder_add(input);
            if (input.value != '' && input.value != input_g_value) {
                input_focus_add(input);
            }
            input.addEventListener('focus', function (e) {
                if (input.value == input_g_value) {
                    input_focus_add(input);
                    input.value = '';
                }
                if (input.getAttribute('data-type') === "pass") {
                    input.setAttribute('type', 'password');
                }

                // if (input.classList.contains('_phone')) {
                //     //'+38(999) 999 9999'
                //     //'+375(99)999-99-99'
                //     input.classList.add('_mask');
                //     Inputmask("+(373) 99 999 999", {
                //         //"placeholder": '',
                //         clearIncomplete: true,
                //         clearMaskOnLostFocus: true,
                //         onincomplete: function () {
                //             input_clear_mask(input, input_g_value);
                //         }
                //     }).mask(input);
                // }
                if (input.classList.contains('_phone')) {
                    input.classList.add('_mask');
                    Inputmask("+(373) r9 999 999", {
                        definitions: {
                            'r': {
                                validator: "[67]",
                                cardinality: 1
                            }
                        },
                        clearIncomplete: true,
                        clearMaskOnLostFocus: true,
                        oncomplete: function () {
                            let confirmEl = input.parentElement.querySelector('.info-main-order__phone-confirm');
                            if (confirmEl) {
                                confirmEl.textContent = 'Ваш номер: ' + input.value;
                                confirmEl.style.display = 'block';
                            }
                        },
                        onincomplete: function () {
                            let confirmEl = input.parentElement.querySelector('.info-main-order__phone-confirm');
                            if (confirmEl) {
                                confirmEl.style.display = 'none';
                            }
                            input_clear_mask(input, input_g_value);
                        }
                    }).mask(input);
                }
                if (input.classList.contains('_digital')) {
                    input.classList.add('_mask');
                    Inputmask("9{1,}", {
                        "placeholder": '',
                        clearIncomplete: true,
                        clearMaskOnLostFocus: true,
                        onincomplete: function () {
                            input_clear_mask(input, input_g_value);
                        }
                    }).mask(input);
                }
                form_remove_error(input);
            });
            input.addEventListener('blur', function (e) {
                if (input.value == '') {
                    input.value = input_g_value;
                    input_focus_remove(input);
                    if (input.classList.contains('_mask')) {
                        input_clear_mask(input, input_g_value);
                    }
                    if (input.getAttribute('data-type') === "pass") {
                        input.setAttribute('type', 'text');
                    }
                }
            });
            if (input.classList.contains('_date')) {
                datepicker(input, {
                    customDays: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
                    customMonths: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"],
                    formatter: (input, date, instance) => {
                        const value = date.toLocaleDateString()
                        input.value = value
                    },
                    onSelect: function (input, instance, date) {
                        input_focus_add(input.el);
                    }
                });
            }
        }
    }
}

function input_placeholder_add(input) {
    const input_g_value = input.getAttribute('data-value');
    if (input.value == '' && input_g_value != '') {
        input.value = input_g_value;
    }
}

function input_focus_add(input) {
    input.classList.add('_focus');
    input.parentElement.classList.add('_focus');
    console.log('focus alternativ');
}

function input_focus_remove(input) {
    input.classList.remove('_focus');
    input.parentElement.classList.remove('_focus');
        console.log('remove focus alternativ');
}

function input_clear_mask(input, input_g_value) {
    input.inputmask.remove();
    input.value = input_g_value;
    input_focus_remove(input);
}

