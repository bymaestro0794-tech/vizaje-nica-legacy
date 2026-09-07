//QUANTITY
let quantityButtons = document.querySelectorAll('.quantity__button');
if (quantityButtons.length > 0) {
    for (let index = 0; index < quantityButtons.length; index++) {
        const quantityButton = quantityButtons[index];
        quantityButton.addEventListener("click", function (e) {
            let value = parseInt(quantityButton.closest('.quantity').querySelector('input').value);
            let max = parseInt(quantityButton.closest('.quantity').querySelector('input').dataset.max);
            console.log();
            if (quantityButton.classList.contains('quantity__button_plus')) {
                value++;
                if (max < value) {
                    value = max;
                }
            } else {
                value = value - 1;
                if (value < 1) {
                    value = 1
                }
            }
            quantityButton.closest('.quantity').querySelector('input').value = value;
        });
    }
}

//RANGE
const priceSlider = document.querySelector('.ranger-sidebar-catalog__slider');
if (priceSlider) {

    const priceStart = document.getElementById('price-start');
    const priceEnd = document.getElementById('price-end');

    var prStart = Number(priceStart.value);
    var prEnd = Number(priceEnd.value);

    noUiSlider.create(priceSlider, {
        start: [prStart, prEnd],
        connect: true,
        tooltips: [wNumb({decimals: 0}), wNumb({decimals: 0})],
        range: {
            'min': [prStart],
            'max': [prEnd]
        }
    });

    const inputs = [priceStart, priceEnd];

    priceSlider.noUiSlider.on('update', function (values, handle) {
        inputs[handle].value = parseInt(values[handle]);
    });

    priceStart.addEventListener('change', function () { // при изменении меньшего значения в input - меняем положение соответствующего элемента управления
        priceSlider.noUiSlider.set([this.value, null]);
    });

    priceEnd.addEventListener('change', function () { // при изменении большего значения в input - меняем положение соответствующего элемента управления
        priceSlider.noUiSlider.set([null, this.value]);
    });
}
