function changeLangue(obj) {
    window.location.href = obj.getAttribute('data-link');
}

function email_test(input) {
    return !/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,8})+$/.test(input.value);
}

// Dynamic Adapt v.1
// HTML data-da="where(uniq class name),when(breakpoint),position(digi)"
// e.x. data-da=".item,992,2"
// Andrikanych Yevhen 2020
// https://www.youtube.com/c/freelancerlifestyle

"use strict";


function DynamicAdapt(type) {
    this.type = type;
}

DynamicAdapt.prototype.init = function () {
    const _this = this;
    // массив объектов
    this.оbjects = [];
    this.daClassname = "_dynamic_adapt_";
    // массив DOM-элементов
    this.nodes = document.querySelectorAll("[data-da]");

    // наполнение оbjects объктами
    for (let i = 0; i < this.nodes.length; i++) {
        const node = this.nodes[i];
        const data = node.dataset.da.trim();
        const dataArray = data.split(",");
        const оbject = {};
        оbject.element = node;
        оbject.parent = node.parentNode;
        оbject.destination = document.querySelector(dataArray[0].trim());
        оbject.breakpoint = dataArray[1] ? dataArray[1].trim() : "767";
        оbject.place = dataArray[2] ? dataArray[2].trim() : "last";
        оbject.index = this.indexInParent(оbject.parent, оbject.element);
        this.оbjects.push(оbject);
    }

    this.arraySort(this.оbjects);

    // массив уникальных медиа-запросов
    this.mediaQueries = Array.prototype.map.call(this.оbjects, function (item) {
        return '(' + this.type + "-width: " + item.breakpoint + "px)," + item.breakpoint;
    }, this);
    this.mediaQueries = Array.prototype.filter.call(this.mediaQueries, function (item, index, self) {
        return Array.prototype.indexOf.call(self, item) === index;
    });

    // навешивание слушателя на медиа-запрос
    // и вызов обработчика при первом запуске
    for (let i = 0; i < this.mediaQueries.length; i++) {
        const media = this.mediaQueries[i];
        const mediaSplit = String.prototype.split.call(media, ',');
        const matchMedia = window.matchMedia(mediaSplit[0]);
        const mediaBreakpoint = mediaSplit[1];

        // массив объектов с подходящим брейкпоинтом
        const оbjectsFilter = Array.prototype.filter.call(this.оbjects, function (item) {
            return item.breakpoint === mediaBreakpoint;
        });
        matchMedia.addListener(function () {
            _this.mediaHandler(matchMedia, оbjectsFilter);
        });
        this.mediaHandler(matchMedia, оbjectsFilter);
    }
};

DynamicAdapt.prototype.mediaHandler = function (matchMedia, оbjects) {
    if (matchMedia.matches) {
        for (let i = 0; i < оbjects.length; i++) {
            const оbject = оbjects[i];
            оbject.index = this.indexInParent(оbject.parent, оbject.element);
            this.moveTo(оbject.place, оbject.element, оbject.destination);
        }
    } else {
        for (let i = 0; i < оbjects.length; i++) {
            const оbject = оbjects[i];
            if (оbject.element.classList.contains(this.daClassname)) {
                this.moveBack(оbject.parent, оbject.element, оbject.index);
            }
        }
    }
};

// Функция перемещения
DynamicAdapt.prototype.moveTo = function (place, element, destination) {
    element.classList.add(this.daClassname);
    if (place === 'last' || place >= destination.children.length) {
        destination.insertAdjacentElement('beforeend', element);
        return;
    }
    if (place === 'first') {
        destination.insertAdjacentElement('afterbegin', element);
        return;
    }
    destination.children[place].insertAdjacentElement('beforebegin', element);
}

// Функция возврата
DynamicAdapt.prototype.moveBack = function (parent, element, index) {
    element.classList.remove(this.daClassname);
    if (parent.children[index] !== undefined) {
        parent.children[index].insertAdjacentElement('beforebegin', element);
    } else {
        parent.insertAdjacentElement('beforeend', element);
    }
}

// Функция получения индекса внутри родителя
DynamicAdapt.prototype.indexInParent = function (parent, element) {
    const array = Array.prototype.slice.call(parent.children);
    return Array.prototype.indexOf.call(array, element);
};

// Функция сортировки массива по breakpoint и place
// по возрастанию для this.type = min
// по убыванию для this.type = max
DynamicAdapt.prototype.arraySort = function (arr) {
    if (this.type === "min") {
        Array.prototype.sort.call(arr, function (a, b) {
            if (a.breakpoint === b.breakpoint) {
                if (a.place === b.place) {
                    return 0;
                }

                if (a.place === "first" || b.place === "last") {
                    return -1;
                }

                if (a.place === "last" || b.place === "first") {
                    return 1;
                }

                return a.place - b.place;
            }

            return a.breakpoint - b.breakpoint;
        });
    } else {
        Array.prototype.sort.call(arr, function (a, b) {
            if (a.breakpoint === b.breakpoint) {
                if (a.place === b.place) {
                    return 0;
                }

                if (a.place === "first" || b.place === "last") {
                    return 1;
                }

                if (a.place === "last" || b.place === "first") {
                    return -1;
                }

                return b.place - a.place;
            }

            return b.breakpoint - a.breakpoint;
        });
        return;
    }
};

const da = new DynamicAdapt("max");
da.init();
//BildSlider
let sliders = document.querySelectorAll('._swiper');
if (sliders) {
    for (let index = 0; index < sliders.length; index++) {
        let slider = sliders[index];
        if (!slider.classList.contains('swiper-bild')) {
            let slider_items = slider.children;
            if (slider_items) {
                for (let index = 0; index < slider_items.length; index++) {
                    let el = slider_items[index];
                    el.classList.add('swiper-slide');
                }
            }
            let slider_content = slider.innerHTML;
            let slider_wrapper = document.createElement('div');
            slider_wrapper.classList.add('swiper-wrapper');
            slider_wrapper.innerHTML = slider_content;
            slider.innerHTML = '';
            slider.appendChild(slider_wrapper);
            slider.classList.add('swiper-bild');

            if (slider.classList.contains('_swiper_scroll')) {
                let sliderScroll = document.createElement('div');
                sliderScroll.classList.add('swiper-scrollbar');
                slider.appendChild(sliderScroll);
            }
        }
        if (slider.classList.contains('_gallery')) {
            //slider.data('lightGallery').destroy(true);
        }
    }
    sliders_bild_callback();
}

function sliders_bild_callback(params) {
}

let sliderScrollItems = document.querySelectorAll('._swiper_scroll');
if (sliderScrollItems.length > 0) {
    for (let index = 0; index < sliderScrollItems.length; index++) {
        const sliderScrollItem = sliderScrollItems[index];
        const sliderScrollBar = sliderScrollItem.querySelector('.swiper-scrollbar');
        const sliderScroll = new Swiper(sliderScrollItem, {
            direction: 'vertical',
            slidesPerView: 'auto',
            freeMode: true,
            scrollbar: {
                el: sliderScrollBar,
                draggable: true,
                snapOnRelease: false
            },
            mousewheel: {
                releaseOnEdges: true,
            },
        });
        sliderScroll.scrollbar.updateSize();
    }
}


function sliders_bild_callback(params) {
}

let bannerSlider = new Swiper('.banner__slider', {
    observer: true,
    effect: 'slide',
    autoplay: {
        delay: 8000,
        disableOnInteraction: false,
    },
    observeParents: true,
    slidesPerView: "auto",
    spaceBetween: 0,
    autoHeight: false,
    speed: 800,
    loop: true,
    touchRatio: 1, // Ensures touch swipe is responsive
    simulateTouch: true, // Enables touch simulation
    // Dotts
    pagination: {
        el: '.banner__paggination',
        clickable: true,
    },
    // Arrows
    navigation: {
        nextEl: '.banner-arrow-next',
        prevEl: '.banner-arrow-prev',
    },
});

let bannerSW = new Swiper('.banners_home_sw', {
    observer: true,
    effect: 'slide',
    // autoplay: {
    // 	delay: 8000,
    // 	disableOnInteraction: false,
    // },
    observeParents: true,
    slidesPerView: "auto",
    spaceBetween: 8,
    autoHeight: false,
    speed: 800,
    loop: true,
    touchRatio: 1, // Ensures touch swipe is responsive
    simulateTouch: true, // Enables touch simulation
    // Dotts
});

let itemProducts = document.querySelectorAll('.products-slider');
if (itemProducts.length > 0) {
    for (let index = 0; index < itemProducts.length; index++) {
        const itemProduct = itemProducts[index];
        // let slider = itemProduct.querySelector('.products-slider__slider');
        // let itemArrows = itemProduct.querySelectorAll('.product-item__image-arrow');
        let numberList = index;
        if (numberList < 10) {
            numberList = `0${index + 1}`;
        }
        // if (itemArrows.length > 0) {
        // 	for (let index = 0; index < itemArrows.length; index++) {
        // 		const itemArrow = itemArrows[index];
        // 		itemArrow.classList.add(`product-${numberList}`)
        // 	}
        // }
        if (itemProduct) {
            itemProduct.classList.add(`products-${numberList}`);
        }
        itemProduct.classList.add(`products-${numberList}`);

        let productSlider = new Swiper(`.products-${numberList} .products-slider__slider`, {
            observer: true,
            observeParents: true,
            slidesPerView: "auto",
            spaceBetween: 0,
            autoplay: {
                delay: 8000,
                disableOnInteraction: false,
            },
            autoHeight: false,
            speed: 800,
            loop: true,
            // Dotts
            pagination: {
                el: `.products-${numberList} .products-slider__paggination`,
                clickable: true,
            },
            // Arrows
            navigation: {
                nextEl: `.products-${numberList} .products-slider-arrow-next`,
                prevEl: `.products-${numberList} .products-slider-arrow-prev`,
            },
        });
    }
}

var cardThumbs = new Swiper(".trumbs-slider-card", {
    spaceBetween: 0,
    slidesPerView: "auto",
    watchSlidesProgress: true,
    autoHeight: false,
    updateOnWindowResize: true,
    // mousewheel: true,
    breakpoints: {
        1682: {
            direction: "container-vertical",
        },
    },
});
var cardMain = new Swiper(".main-slider-card", {
    spaceBetween: 0,
    slidesPerView: "auto",
    autoHeight: false,
    updateOnWindowResize: true,
    // navigation: {
    // 	nextEl: ".swiper-button-next",
    // 	prevEl: ".swiper-button-prev",
    // },
    pagination: {
        el: '.slider-card__paggination',
        clickable: true,
    },
    thumbs: {
        swiper: cardThumbs,
    },
    loop: true,
});


$('body').on('change', 'input[name="variable"]', function (e) {
    e.preventDefault();
    let lang = $('html').attr('lang');
    var variable = $(this).val();
    var product_id = $('.actions-info-card').data('prod_id');
    $.ajax({
        url: '/' + lang + '/products/variable', // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'json',
        data: {variable: variable, product_id: product_id},
        success: function (data) {
            $('.slider_products').html(data.html);
            $('.info-card__price.price-info-card').first().html(data.price);
            $('.cod_sku').html(data.info.SKU);
            $('.barcode').html(data.info.barcode);
            $('.volume .list-info-card__value').html(data.info.VolumeVar);
            $('.alege-var').remove();
            setTimeout(start_swiper(), 2000);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
});

function start_swiper() {

    let sliders = document.querySelectorAll('._swiper');
    if (sliders) {
        for (let index = 0; index < sliders.length; index++) {
            let slider = sliders[index];
            if (!slider.classList.contains('swiper-bild')) {
                let slider_items = slider.children;
                if (slider_items) {
                    for (let index = 0; index < slider_items.length; index++) {
                        let el = slider_items[index];
                        el.classList.add('swiper-slide');
                    }
                }
                let slider_content = slider.innerHTML;
                let slider_wrapper = document.createElement('div');
                slider_wrapper.classList.add('swiper-wrapper');
                slider_wrapper.innerHTML = slider_content;
                slider.innerHTML = '';
                slider.appendChild(slider_wrapper);
                slider.classList.add('swiper-bild');

                if (slider.classList.contains('_swiper_scroll')) {
                    let sliderScroll = document.createElement('div');
                    sliderScroll.classList.add('swiper-scrollbar');
                    slider.appendChild(sliderScroll);
                }
            }
            if (slider.classList.contains('_gallery')) {
                //slider.data('lightGallery').destroy(true);
            }
        }
        sliders_bild_callback();
    }

    var cardThumbsV = new Swiper("body .trumbs-slider-card", {
        spaceBetween: 0,
        slidesPerView: "auto",
        watchSlidesProgress: true,
        autoHeight: false,
        updateOnWindowResize: true,
        // mousewheel: true,
        breakpoints: {
            1682: {
                direction: "container-vertical",
            },
        },
    });

    var cardMainV = new Swiper("body .main-slider-card", {
        spaceBetween: 0,
        slidesPerView: "auto",
        autoHeight: false,
        updateOnWindowResize: true,
        // navigation: {
        // 	nextEl: ".swiper-button-next",
        // 	prevEl: ".swiper-button-prev",
        // },
        pagination: {
            el: 'body .slider-card__paggination',
            clickable: true,
        },
        thumbs: {
            swiper: cardThumbsV,
        },
        loop: true,
    });
}

let itemGalleryMaps = document.querySelectorAll('.gallery-map-order-list');
if (itemGalleryMaps.length > 0) {
    for (let index = 0; index < itemGalleryMaps.length; index++) {
        const itemGalleryMap = itemGalleryMaps[index];
        let numberList = index;
        if (numberList < 10) {
            numberList = `0${index + 1}`;
        }
        if (itemGalleryMap) {
            itemGalleryMap.classList.add(`store-${numberList}`);
        }
        itemGalleryMap.classList.add(`store-${numberList}`);
        let mapGallerySlider = new Swiper(`.store-${numberList} .gallery-map-order-list__slider`, {
            observer: true,
            observeParents: true,
            slidesPerView: "auto",
            spaceBetween: 0,
            autoHeight: false,
            speed: 800,
            loop: true,
            // Arrows
            navigation: {
                nextEl: `.store-${numberList} .gallery-map-order-list-arrow-next`,
                prevEl: `.store-${numberList} .gallery-map-order-list-arrow-prev`,
            },
        });
    }
}

let infoMapGallerySlider = new Swiper(`.gallery-info-map-order-map__slider`, {
    observer: true,
    observeParents: true,
    slidesPerView: "auto",
    spaceBetween: 0,
    autoHeight: false,
    speed: 800,
    loop: false,
    // Arrows
    navigation: {
        nextEl: `.gallery-info-map-order-map-arrow-next`,
        prevEl: `.gallery-info-map-order-map-arrow-prev`,
    },
});

function setTabHash(tabItem) {
    if (!tabItem) {
        return;
    }

    const tabUrl = tabItem.getAttribute('data-url');
    if (!tabUrl) {
        return;
    }

    const cleanQuery = tabUrl.trim().replace(/^[?#]/, '');
    if (!cleanQuery) {
        return;
    }

    history.replaceState(null, '', '?' + cleanQuery);

    const languageItems = document.querySelectorAll('.language-main-header__item');
    if (!languageItems.length) {
        return;
    }

    for (let index = 0; index < languageItems.length; index++) {
        const languageItem = languageItems[index];
        const languageLink = languageItem.getAttribute('data-link');

        if (!languageLink) {
            continue;
        }

        const baseLanguageLink = languageLink.split('?')[0];
        languageItem.setAttribute('data-link', baseLanguageLink + '?' + cleanQuery);
    }
}


window.onload = function () {
    document.addEventListener('click', documentActions);
    document.body.style.opacity = "1";

    if (window.matchMedia('(min-width: 991px)').matches) {
        // New hover menu
        if (window.matchMedia('(hover: hover)').matches) {
            const catalogItem = document.querySelector('.navigation-header__item._catalog');
            const catalogHeaderLink = catalogItem.querySelector('.navigation-header__name');
            const catalogHeaderCatalog = document.querySelector('.navigation-header__catalog');
            const body = document.body;

            catalogItem.addEventListener('mouseenter', () => {
                if (!catalogHeaderLink.classList.contains('_active')) {
                    catalogHeaderLink.classList.add('_active');
                    _slideDown(catalogHeaderCatalog, 300);
                    body.classList.add('_catalog-open');
                }
                console.log(123);
            });

            catalogItem.addEventListener('mouseleave', () => {
                if (catalogHeaderLink.classList.contains('_active')) {
                    catalogHeaderLink.classList.remove('_active');
                    _slideUp(catalogHeaderCatalog, 300);
                    body.classList.remove('_catalog-open');
                }
                console.log(321);
            });
        }

        const tabs = document.querySelectorAll('.nav-catalog-header__item._tabs-item');
        const blocks = document.querySelectorAll('.catalog-header__block._tabs-block');

        tabs.forEach((tab, index) => {
            tab.addEventListener('mouseenter', () => {


                // убираем active у всех табов
                tabs.forEach(t => t.classList.remove('_active'));
                blocks.forEach(b => b.classList.remove('_active'));

                // добавляем active текущему
                tab.classList.add('_active');
                setTabHash(tab);
                if (blocks[index]) {
                    blocks[index].classList.add('_active');
                }
            });
        });

        const tabs2 = document.querySelectorAll('.block-catalog-header__item._tabs-item2');
        const blocks2 = document.querySelectorAll('.subblocks-catalog-header__block._tabs-block2');

        tabs2.forEach((tab, index) => {
            tab.addEventListener('mouseenter', () => {

                // снимаем active со всех
                tabs2.forEach(t => t.classList.remove('_active'));
                blocks2.forEach(b => b.classList.remove('_active'));

                // активируем текущий
                tab.classList.add('_active');
                setTabHash(tab);
                if (blocks2[index]) {
                    blocks2[index].classList.add('_active');
                }
            });
        });
    }


    function documentActions(e) {
        const targetElement = e.target;
        let body = document.querySelector('body');

        if (targetElement.closest('.navigation-header__item._catalog .navigation-header__name')) {
            if (window.matchMedia('(max-width: 991px)').matches) {
                let catalogHeaderLink = targetElement.closest('.navigation-header__item._catalog .navigation-header__name');
                let catalogHeaderCatalog = document.querySelector('.navigation-header__catalog');
                let body = document.querySelector('body');
                if (spollersGo) {
                    spollersGo = false;
                    catalogHeaderLink.classList.toggle('_active');
                    _slideToggle(catalogHeaderCatalog, 300);
                    body.classList.toggle("_catalog-open")
                    e.preventDefault();
                    setTimeout(function () {
                        spollersGo = true;
                    }, 500);
                }
            }
        } else if (!targetElement.closest('.navigation-header__catalog') && document.querySelector('.navigation-header__item._catalog .navigation-header__name._active')) {
            if (window.matchMedia('(max-width: 991px)').matches) {
                let catalogHeaderLink = document.querySelector('.navigation-header__item._catalog .navigation-header__name._active');
                let catalogHeaderCatalog = document.querySelector('.navigation-header__catalog');
                let body = document.querySelector('body');
                if (spollersGo) {
                    spollersGo = false;
                    catalogHeaderLink.classList.toggle('_active');
                    _slideToggle(catalogHeaderCatalog, 300);
                    body.classList.toggle("_catalog-open")
                    e.preventDefault();
                    setTimeout(function () {
                        spollersGo = true;
                    }, 500);
                }
            }
        } else if (targetElement.closest('.catalog-header__back')) {
            let catalogHeaderLink = document.querySelector('.navigation-header__item._catalog .navigation-header__name._active');
            let catalogHeaderCatalog = document.querySelector('.navigation-header__catalog');
            let body = document.querySelector('body');
            if (spollersGo) {
                spollersGo = false;
                catalogHeaderLink.classList.toggle('_active');
                _slideToggle(catalogHeaderCatalog, 300);
                body.classList.toggle("_catalog-open")
                e.preventDefault();
                setTimeout(function () {
                    spollersGo = true;
                }, 300);
            }
        }

        if (targetElement.closest(".icon-menu")) {
            let iconMenu = document.querySelector(".icon-menu");
            let menuBody = document.querySelector(".menu__body");
            if (unlock) {
                body_lock(200);
                iconMenu.classList.toggle("_active");
                menuBody.classList.toggle("_active");
            }
        } else if (!targetElement.closest('.menu__body') && document.querySelector('.icon-menu._active')) {
            body_lock(200);
            menu_close();
        }

        if (targetElement.closest('.user-actions-main-header .actions-main-header__icon')) {
            let userActionsHeader = targetElement.closest('.user-actions-main-header');
            if (userActionsHeader.classList.contains('_authorized')) {
                userActionsHeader.classList.toggle("_user-actions");
            } else {
                userActionsHeader.classList.toggle("_login-register");
            }

        } else if (!targetElement.closest('.login-user-actions-main-header') && document.querySelector('.user-actions-main-header._login-register')) {
            let userActionsHeader = document.querySelector('.user-actions-main-header._login-register');
            userActionsHeader.classList.remove("_login-register");
        } else if (targetElement.closest('.login-user-actions-main-header__btn')) {
            let userActionsHeader = document.querySelector('.user-actions-main-header._login-register');
            userActionsHeader.classList.remove("_login-register");
        } else if (!targetElement.closest('.user-header-info') && document.querySelector('.user-actions-main-header._user-actions')) {
            let userActionsHeader = document.querySelector('.user-actions-main-header._user-actions');
            userActionsHeader.classList.remove("_user-actions");
        }

        if (targetElement.closest('.search-actions-main-header__icon')) {
            let searchMainContent = document.querySelector('.search-actions-main-header .form-header-search');
            let searchMainIcon = targetElement.closest('.search-actions-main-header__icon');
            let body = document.querySelector('body');
            if (spollersGo) {
                spollersGo = false;
                searchMainIcon.classList.toggle('_active');
                _slideToggle(searchMainContent, 500);
                body.classList.toggle("_catalog-open")
                e.preventDefault();
                setTimeout(function () {
                    spollersGo = true;
                }, 500);
            }
        } else if (!targetElement.closest('.search-actions-main-header') && document.querySelector('.search-actions-main-header__icon._active')) {
            let searchMainContent = document.querySelector('.search-actions-main-header .form-header-search');
            let searchMainIcon = document.querySelector('.search-actions-main-header__icon');
            let body = document.querySelector('body');
            if (spollersGo) {
                spollersGo = false;
                searchMainIcon.classList.toggle('_active');
                _slideToggle(searchMainContent, 500);
                body.classList.toggle("_catalog-open")
                e.preventDefault();
                setTimeout(function () {
                    spollersGo = true;
                }, 500);
            }
        }

        if (targetElement.closest('.block-catalog-header__back')) {
            e.preventDefault();
            let blockTabs = document.querySelector('.catalog-header__content ._tabs-block._active');
            let itemTabs = document.querySelector('.catalog-header__content ._tabs-item._active');
            blockTabs.classList.remove('_active');
            itemTabs.classList.remove('_active');
        }

        if (targetElement.closest('.subblocks-catalog-header__back')) {
            e.preventDefault();
            let blockTabs2 = document.querySelector('.block-catalog-header ._tabs-block2._active');
            let itemTabs2 = document.querySelector('.block-catalog-header ._tabs-item2._active');
            blockTabs2.classList.remove('_active');
            itemTabs2.classList.remove('_active');
        }

        if (targetElement.closest('.action-product__like')) {
            e.preventDefault();
            let itemProduct = targetElement.closest('.product');
            itemProduct.classList.toggle('_liked')
        }

        if (targetElement.closest('.head-main-catalog__filter')) {
            e.preventDefault();
            document.querySelector('.sidebar-catalog').classList.add('_active');
            document.querySelector('body').classList.add('_filter-open');
            document.querySelector('.head-main-catalog__filter').classList.add('_active');
        } else if (targetElement.closest('.top-sidebar-catalog__close')) {
            document.querySelector('.sidebar-catalog').classList.remove('_active');
            document.querySelector('body').classList.remove('_filter-open');
            document.querySelector('.head-main-catalog__filter').classList.remove('_active');
        } else if (targetElement.closest('._close_filter')) {
            document.querySelector('.sidebar-catalog').classList.remove('_active');
            document.querySelector('body').classList.remove('_filter-open');
            document.querySelector('.head-main-catalog__filter').classList.remove('_active');
        } else if (!targetElement.closest('.sidebar-catalog') && document.querySelector('.head-main-catalog__filter._active')) {
            document.querySelector('.sidebar-catalog').classList.remove('_active');
            document.querySelector('body').classList.remove('_filter-open');
            document.querySelector('.head-main-catalog__filter').classList.remove('_active');
        }

        if (targetElement.closest('.head-sidebar-catalog__clear')) {
            e.preventDefault();
            let filterSection = targetElement.closest('.sidebar-catalog__section');
            let filterSectionInputChekeds = filterSection.querySelectorAll('.checkbox__input:checked');
            if (filterSectionInputChekeds) {
                for (let index = 0; index < filterSectionInputChekeds.length; index++) {
                    const filterSectionInputCheked = filterSectionInputChekeds[index];
                    if (filterSectionInputCheked.checked) {
                        filterSectionInputCheked.checked = false;
                    }
                }
            }

            if (document.querySelector('.ranger-sidebar-catalog')) {
                priceSlider.noUiSlider.set([0, 100000]);
            }
            filter_products();
        }

        if (targetElement.closest('.sidebar-catalog__head')) {
            let filterSection = targetElement.closest('.sidebar-catalog__section');
            let filterSectionHead = filterSection.querySelector('.sidebar-catalog__head');
            let filterSectionBody = filterSection.querySelector('.sidebar-catalog__body');
            if (spollersGo) {
                spollersGo = false;
                filterSectionHead.classList.toggle('_active');
                _slideToggle(filterSectionBody, 400);
                setTimeout(function () {
                    spollersGo = true;
                }, 400);
            }
        }

        if (targetElement.closest('.actions-info-card__like')) {
            e.preventDefault();
            let likeCard = targetElement.closest('.actions-info-card__like');
            likeCard.classList.toggle('_liked')
        }

        if (targetElement.closest('.actions-other-products-cart__item._like')) {
            e.preventDefault();
            let likeCartProduct = targetElement.closest('.actions-other-products-cart__item._like');
            likeCartProduct.classList.toggle('_liked');
        }

        if (targetElement.closest('.map-order-list__main')) {
            let itemMapListActive = document.querySelector('.map-order-list__item._active')
            let itemMapList = targetElement.closest('.map-order-list__item');
            if (itemMapListActive && itemMapListActive != itemMapList) {
                itemMapListActive.classList.remove('_active')
            }

            itemMapList.classList.toggle('_active')
        }

        if (targetElement.closest('.brand-letter__search')) {
            let inputSearchBrand = document.querySelector('.search-head-brands__input');
            let headerHeight = document.querySelector('header').clientHeight + 120;
            _goto(inputSearchBrand, 300, headerHeight);
            inputSearchBrand.focus();
        }
    }

    let searchInput = document.querySelector('.form-header-search__input')
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            let searchInputValue = searchInput.value.replaceAll(' ', '');
            let searchHeaderForm = document.querySelector('.form-header-search');
            if (searchInputValue.length > 2) {
                searchHeaderForm.classList.add('_results')
                searchResult()
            } else if (searchInputValue.length < 1) {
                searchHeaderForm.classList.remove('_results')
            }
        })
    }

    if (document.querySelector('.promocode-main-cart__input')) {
        let promocodeInput = document.querySelector('.promocode-main-cart__input');
        promocodeInput.addEventListener('input', function () {
            let promocodeInputValue = promocodeInput.value.replaceAll(' ', '');
            let promocodeBody = document.querySelector('.promocode-main-cart__body');
            if (promocodeInputValue.length < 1) {
                promocodeBody.classList.remove('_submit');
            } else {
                promocodeBody.classList.add('_submit');
            }
        })
    }

    let inputItems = document.querySelectorAll('._item-input input');
    if (inputItems.length > 0) {
        for (let index = 0; index < inputItems.length; index++) {
            const inputItem = inputItems[index];
            inputItem.addEventListener('focus', function () {
                console.log('focus');
                let inputItemRow = inputItem.closest('._item-input');
                inputItemRow.classList.add('_focus')

            })

            inputItem.addEventListener('blur', function () {
                let inputItemRow = inputItem.closest('._item-input');
                let inputItemValue = inputItem.value.replaceAll(" ", "");
                if (inputItemValue != "") {
                    return false;
                }
                inputItemRow.classList.remove('_focus')
            })
        }
    }

    let codeInputs = document.querySelectorAll('.code-login__input');
    if (codeInputs) {
        for (let index = 0; index < codeInputs.length; index++) {
            const codeInput = codeInputs[index];
            codeInput.addEventListener('input', function () {
                let codeColumn = codeInput.closest('.code-login__column');
                let nextCodeColumn = codeColumn.nextElementSibling;
                if (nextCodeColumn && codeInput.value.length == 1) {
                    let nextCodeInput = nextCodeColumn.querySelector('.code-login__input');
                    nextCodeInput.focus();
                }
            })
        }
    }
}

function searchResult() {
    var search = $('.form-header-search__search input[name="search"]').val();
    var lang = $('html').attr('lang');
    $.ajax({
        url: '/' + lang + '/search', // путь к обработчику
        type: 'POST', // метод отправки,
        dataType: 'html',
        data: {search: search},
        success: function (data) {
            $('.search_result').html(data);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
}

var ua = window.navigator.userAgent;
var msie = ua.indexOf("MSIE ");
var isMobile = {
    Android: function () {
        return navigator.userAgent.match(/Android/i);
    }, BlackBerry: function () {
        return navigator.userAgent.match(/BlackBerry/i);
    }, iOS: function () {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    }, Opera: function () {
        return navigator.userAgent.match(/Opera Mini/i);
    }, Windows: function () {
        return navigator.userAgent.match(/IEMobile/i);
    }, any: function () {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};

function isIE() {
    ua = navigator.userAgent;
    var is_ie = ua.indexOf("MSIE ") > -1 || ua.indexOf("Trident/") > -1;
    return is_ie;
}

if (isIE()) {
    document.querySelector('body').classList.add('ie');
}
if (isMobile.any()) {
    document.querySelector('body').classList.add('_touch');
}

function testWebP(callback) {
    var webP = new Image();
    webP.onload = webP.onerror = function () {
        callback(webP.height == 2);
    };
    webP.src = "data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA";
}

testWebP(function (support) {
    if (support == true) {
        document.querySelector('body').classList.add('_webp');
    } else {
        document.querySelector('body').classList.add('_no-webp');
    }
});

function ibg() {
    if (isIE()) {
        let ibg = document.querySelectorAll("._ibg");
        for (var i = 0; i < ibg.length; i++) {
            if (ibg[i].querySelector('img') && ibg[i].querySelector('img').getAttribute('src') != null) {
                ibg[i].style.backgroundImage = 'url(' + ibg[i].querySelector('img').getAttribute('src') + ')';
            }
        }
    }
}

ibg();

if (document.querySelector('.wrapper')) {
    document.querySelector('.wrapper').classList.add('_loaded');
}

let unlock = true;

//=================
//ActionsOnHash
if (location.hash) {
    const hsh = location.hash.replace('#', '');
    if (document.querySelector('.popup_' + hsh)) {
        popup_open(hsh);
    } else if (document.querySelector('div.' + hsh)) {
        _goto(document.querySelector('.' + hsh), 500, '');
    }
}
//=================
//Menu
// let iconMenu = document.querySelector(".icon-menu");
// if (iconMenu != null) {
// 	let delay = 300;
// 	let menuBody = document.querySelector(".menu__body");
// 	iconMenu.addEventListener("click", function (e) {
// 		if (unlock) {
// 			body_lock(delay);
// 			iconMenu.classList.toggle("_active");
// 			menuBody.classList.toggle("_active");
// 		}
// 	});
// };
function menu_close() {
    let iconMenu = document.querySelector(".icon-menu");
    let menuBody = document.querySelector(".menu__body");
    iconMenu.classList.remove("_active");
    menuBody.classList.remove("_active");
}

//=================
//BodyLock
function body_lock(delay) {
    let body = document.querySelector("body");
    if (body.classList.contains('_lock')) {
        body_lock_remove(delay);
    } else {
        body_lock_add(delay);
    }
}

function body_lock_remove(delay) {
    let body = document.querySelector("body");
    if (unlock) {
        let lock_padding = document.querySelectorAll("._lp");
        setTimeout(() => {
            for (let index = 0; index < lock_padding.length; index++) {
                const el = lock_padding[index];
                el.style.paddingRight = '0px';
            }
            body.style.paddingRight = '0px';
            body.classList.remove("_lock");
        }, delay);

        unlock = false;
        setTimeout(function () {
            unlock = true;
        }, delay);
    }
}

function body_lock_add(delay) {
    let body = document.querySelector("body");
    if (unlock) {
        let lock_padding = document.querySelectorAll("._lp");
        for (let index = 0; index < lock_padding.length; index++) {
            const el = lock_padding[index];
            // el.style.paddingRight = window.innerWidth - document.querySelector('.wrapper').offsetWidth + 'px';
        }
        // body.style.paddingRight = window.innerWidth - document.querySelector('.wrapper').offsetWidth + 'px';
        body.classList.add("_lock");

        unlock = false;
        setTimeout(function () {
            unlock = true;
        }, delay);
    }
}

//=================

// LettersAnimation
let title = document.querySelectorAll('._letter-animation');
if (title) {
    for (let index = 0; index < title.length; index++) {
        let el = title[index];
        let txt = el.innerHTML;
        let txt_words = txt.replace('  ', ' ').split(' ');
        let new_title = '';
        for (let index = 0; index < txt_words.length; index++) {
            let txt_word = txt_words[index];
            let len = txt_word.length;
            new_title = new_title + '<p>';
            for (let index = 0; index < len; index++) {
                let it = txt_word.substr(index, 1);
                if (it == ' ') {
                    it = '&nbsp;';
                }
                new_title = new_title + '<span>' + it + '</span>';
            }
            el.innerHTML = new_title;
            new_title = new_title + '&nbsp;</p>';
        }
    }
}
//=================
//Tabs
let tabs = document.querySelectorAll("._tabs");
for (let index = 0; index < tabs.length; index++) {
    let tab = tabs[index];
    let tabs_items = tab.querySelectorAll("._tabs-item");
    let tabs_blocks = tab.querySelectorAll("._tabs-block");
    for (let index = 0; index < tabs_items.length; index++) {
        let tabs_item = tabs_items[index];
        tabs_item.addEventListener("click", function (e) {
            for (let index = 0; index < tabs_items.length; index++) {
                let tabs_item = tabs_items[index];
                tabs_item.classList.remove('_active');
                tabs_blocks[index].classList.remove('_active');
            }
            tabs_item.classList.add('_active');
            setTabHash(tabs_item);
            tabs_blocks[index].classList.add('_active');
            if (window.matchMedia('(max-width: 991px)').matches) {
                e.preventDefault();
            }
        });
    }
}

if (window.matchMedia('(max-width: 991px)').matches) {
//=================
//Tabs2
    let tabs2 = document.querySelectorAll("._tabs2");
    for (let index = 0; index < tabs2.length; index++) {
        let tab = tabs2[index];
        let tabs_items = tab.querySelectorAll("._tabs-item2");
        let tabs_blocks = tab.querySelectorAll("._tabs-block2");
        for (let index = 0; index < tabs_items.length; index++) {
            let tabs_item = tabs_items[index];
            tabs_item.addEventListener("click", function (e) {
                if (tabs_item.classList.contains('_active')) {
                    for (let index = 0; index < tabs_items.length; index++) {
                        let tabs_item = tabs_items[index];
                        tabs_item.classList.remove('_active');
                        tabs_blocks[index].classList.remove('_active');
                    }
                    e.preventDefault();
                    return false;
                }
                for (let index = 0; index < tabs_items.length; index++) {
                    let tabs_item = tabs_items[index];
                    tabs_item.classList.remove('_active');
                    tabs_blocks[index].classList.remove('_active');
                }
                tabs_item.classList.add('_active');
                setTabHash(tabs_item);
                tabs_blocks[index].classList.add('_active');
                e.preventDefault();
            });
        }
    }
}

//=================
//Spollers
let spollers = document.querySelectorAll("._spoller");
let spollersGo = true;
if (spollers.length > 0) {
    for (let index = 0; index < spollers.length; index++) {
        const spoller = spollers[index];
        spoller.addEventListener("click", function (e) {
            if (spollersGo) {
                spollersGo = false;
                if (spoller.classList.contains('_spoller-992') && window.innerWidth > 992) {
                    return false;
                }
                if (spoller.classList.contains('_spoller-768') && window.innerWidth > 768) {
                    return false;
                }
                if (spoller.closest('._spollers').classList.contains('_one')) {
                    let curent_spollers = spoller.closest('._spollers').querySelectorAll('._spoller');
                    for (let i = 0; i < curent_spollers.length; i++) {
                        let el = curent_spollers[i];
                        if (el != spoller) {
                            el.classList.remove('_active');
                            _slideUp(el.nextElementSibling);
                        }
                    }
                }
                spoller.classList.toggle('_active');
                _slideToggle(spoller.nextElementSibling);
                e.preventDefault();
                setTimeout(function () {
                    spollersGo = true;
                }, 500);
            }
        });
    }
}
//=================
//Gallery
let gallery = document.querySelectorAll('._gallery');
if (gallery) {
    gallery_init();
}

function gallery_init() {
    for (let index = 0; index < gallery.length; index++) {
        const el = gallery[index];
        lightGallery(el, {
            counter: false,
            selector: 'a',
            download: false
        });
    }
}

//=================
//SearchInList
function search_in_list(input) {
    let ul = input.parentNode.querySelector('ul')
    let li = ul.querySelectorAll('li');
    let filter = input.value.toUpperCase();

    for (i = 0; i < li.length; i++) {
        let el = li[i];
        let item = el;
        txtValue = item.textContent || item.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            el.style.display = "";
        } else {
            el.style.display = "none";
        }
    }
}

//=================
//DigiFormat
function digi(str) {
    var r = str.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, "$1 ");
    return r;
}

//=================
//DiGiAnimate
function digi_animate(digi_animate) {
    if (digi_animate.length > 0) {
        for (let index = 0; index < digi_animate.length; index++) {
            const el = digi_animate[index];
            const el_to = parseInt(el.innerHTML.replace(' ', ''));
            if (!el.classList.contains('_done')) {
                digi_animate_value(el, 0, el_to, 1500);
            }
        }
    }
}

function digi_animate_value(el, start, end, duration) {
    var obj = el;
    var range = end - start;
    // no timer shorter than 50ms (not really visible any way)
    var minTimer = 50;
    // calc step time to show all interediate values
    var stepTime = Math.abs(Math.floor(duration / range));

    // never go below minTimer
    stepTime = Math.max(stepTime, minTimer);

    // get current time and calculate desired end time
    var startTime = new Date().getTime();
    var endTime = startTime + duration;
    var timer;

    function run() {
        var now = new Date().getTime();
        var remaining = Math.max((endTime - now) / duration, 0);
        var value = Math.round(end - (remaining * range));
        obj.innerHTML = digi(value);
        if (value == end) {
            clearInterval(timer);
        }
    }

    timer = setInterval(run, stepTime);
    run();

    el.classList.add('_done');
}

//=================
//Popups
let popup_link = document.querySelectorAll('._popup-link');
let popups = document.querySelectorAll('.popup');
for (let index = 0; index < popup_link.length; index++) {
    const el = popup_link[index];
    el.addEventListener('click', function (e) {
        if (unlock) {
            let item = el.getAttribute('href').replace('#', '');
            let video = el.getAttribute('data-video');
            popup_open(item, video);
        }
        e.preventDefault();
    })
}
for (let index = 0; index < popups.length; index++) {
    const popup = popups[index];
    popup.addEventListener("click", function (e) {
        if (!e.target.closest('.popup__body')) {
            popup_close(e.target.closest('.popup'));
        }
    });
}

function popup_open(item, video = '') {
    let activePopup = document.querySelectorAll('.popup._active');
    if (activePopup.length > 0) {
        popup_close('', false);
    }
    let curent_popup = document.querySelector('.popup_' + item);
    if (curent_popup && unlock) {
        if (video != '' && video != null) {
            let popup_video = document.querySelector('.popup_video');
            popup_video.querySelector('.popup__video').innerHTML = '<iframe src="https://www.youtube.com/embed/' + video + '?autoplay=1"  allow="autoplay; encrypted-media" allowfullscreen></iframe>';
        }
        if (!document.querySelector('.menu__body._active')) {
            body_lock_add(100);
        }
        curent_popup.classList.add('_active');
        history.pushState('', '', '#' + item);
    }
}

function popup_close(item, bodyUnlock = true) {
    if (unlock) {
        if (!item) {
            for (let index = 0; index < popups.length; index++) {
                const popup = popups[index];
                let video = popup.querySelector('.popup__video');
                if (video) {
                    video.innerHTML = '';
                }
                popup.classList.remove('_active');
            }
        } else {
            let video = item.querySelector('.popup__video');
            if (video) {
                video.innerHTML = '';
            }
            item.classList.remove('_active');
        }
        if (!document.querySelector('.menu__body._active') && bodyUnlock) {
            body_lock_remove(250);
        }
        history.pushState('', '', window.location.href.split('#')[0]);
    }
}

let popup_close_icon = document.querySelectorAll('.popup__close,._popup-close');
if (popup_close_icon) {
    for (let index = 0; index < popup_close_icon.length; index++) {
        const el = popup_close_icon[index];
        el.addEventListener('click', function () {
            popup_close(el.closest('.popup'));
        })
    }
}
document.addEventListener('keydown', function (e) {
    if (e.code === 'Escape') {
        popup_close();
    }
});
//=================
//SlideToggle
let _slideUp = (target, duration = 500) => {
    target.style.transitionProperty = 'height, margin, padding';
    target.style.transitionDuration = duration + 'ms';
    target.style.height = target.offsetHeight + 'px';
    target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    window.setTimeout(() => {
        target.style.display = 'none';
        target.style.removeProperty('height');
        target.style.removeProperty('padding-top');
        target.style.removeProperty('padding-bottom');
        target.style.removeProperty('margin-top');
        target.style.removeProperty('margin-bottom');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
        target.classList.remove('_slide');
    }, duration);
}
let _slideDown = (target, duration = 500) => {
    target.style.removeProperty('display');
    let display = window.getComputedStyle(target).display;
    if (display === 'none')
        display = 'block';

    target.style.display = display;
    let height = target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.offsetHeight;
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = duration + 'ms';
    target.style.height = height + 'px';
    target.style.removeProperty('padding-top');
    target.style.removeProperty('padding-bottom');
    target.style.removeProperty('margin-top');
    target.style.removeProperty('margin-bottom');
    window.setTimeout(() => {
        target.style.removeProperty('height');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
        target.classList.remove('_slide');
    }, duration);
}
let _slideToggle = (target, duration = 500) => {
    if (!target.classList.contains('_slide')) {
        target.classList.add('_slide');
        if (window.getComputedStyle(target).display === 'none') {
            return _slideDown(target, duration);
        } else {
            return _slideUp(target, duration);
        }
    }
}
//========================================
//Wrap
function _wrap(el, wrapper) {
    el.parentNode.insertBefore(wrapper, el);
    wrapper.appendChild(el);
}

//========================================
//RemoveClasses
function _removeClasses(el, class_name) {
    for (var i = 0; i < el.length; i++) {
        el[i].classList.remove(class_name);
    }
}

//========================================
//IsHidden
function _is_hidden(el) {
    return (el.offsetParent === null)
}

//========================================
//Animate
function animate({timing, draw, duration}) {
    let start = performance.now();
    requestAnimationFrame(function animate(time) {
        // timeFraction изменяется от 0 до 1
        let timeFraction = (time - start) / duration;
        if (timeFraction > 1) timeFraction = 1;

        // вычисление текущего состояния анимации
        let progress = timing(timeFraction);

        draw(progress); // отрисовать её

        if (timeFraction < 1) {
            requestAnimationFrame(animate);
        }

    });
}

function makeEaseOut(timing) {
    return function (timeFraction) {
        return 1 - timing(1 - timeFraction);
    }
}

function makeEaseInOut(timing) {
    return function (timeFraction) {
        if (timeFraction < .5)
            return timing(2 * timeFraction) / 2;
        else
            return (2 - timing(2 * (1 - timeFraction))) / 2;
    }
}

function quad(timeFraction) {
    return Math.pow(timeFraction, 2)
}

function circ(timeFraction) {
    return 1 - Math.sin(Math.acos(timeFraction));
}

/*
animate({
	duration: 1000,
	timing: makeEaseOut(quad),
	draw(progress) {
		window.scroll(0, start_position + 400 * progress);
	}
});*/

//Полифилы
(function () {
    // проверяем поддержку
    if (!Element.prototype.closest) {
        // реализуем
        Element.prototype.closest = function (css) {
            var node = this;
            while (node) {
                if (node.matches(css)) return node;
                else node = node.parentElement;
            }
            return null;
        };
    }
})();
(function () {
    // проверяем поддержку
    if (!Element.prototype.matches) {
        // определяем свойство
        Element.prototype.matches = Element.prototype.matchesSelector ||
            Element.prototype.webkitMatchesSelector ||
            Element.prototype.mozMatchesSelector ||
            Element.prototype.msMatchesSelector;
    }
})();
//let btn = document.querySelectorAll('button[type="submit"],input[type="submit"]');
let forms = document.querySelectorAll('form');
if (forms.length > 0) {
    for (let index = 0; index < forms.length; index++) {
        const el = forms[index];
        el.addEventListener('submit', form_submit);
    }
}

async function form_submit(e) {
    let btn = e.target;
    let form = btn.closest('form');
    let error = form_validate(form);
    if (error == 0) {
        let formAction = form.getAttribute('action') ? form.getAttribute('action').trim() : '#';
        let formMethod = form.getAttribute('method') ? form.getAttribute('method').trim() : 'GET';
        const message = form.getAttribute('data-message');
        const ajax = form.getAttribute('data-ajax');

        //SendForm
        if (ajax) {
            e.preventDefault();
            let formData = new FormData(form);
            form.classList.add('_sending');
            let response = await fetch(formAction, {
                method: formMethod,
                body: formData
            });
            if (response.ok) {
                let result = await response.json();
                form.classList.remove('_sending');
                if (message) {
                    popup_open('_' + message + '-message');
                }
                form_clean(form);
            } else {
                alert("Ошибка");
                form.classList.remove('_sending');
            }
        }
    } else {
        let form_error = form.querySelectorAll('._error');
        if (form_error && form.classList.contains('_goto-error')) {
            _goto(form_error[0], 1000, 50);
        }
        e.preventDefault();
    }
}

function form_validate(form) {
    let error = 0;
    let form_req = form.querySelectorAll('._req');
    if (form_req.length > 0) {
        for (let index = 0; index < form_req.length; index++) {
            const el = form_req[index];
            if (!_is_hidden(el)) {
                error += form_validate_input(el);
            }
        }
    }
    return error;
}

function form_validate_input(input) {
    let error = 0;
    let input_g_value = input.getAttribute('data-value');

    if (input.getAttribute("name") == "email" || input.classList.contains("_email")) {
        if (input.value != input_g_value) {
            let em = input.value.replace(" ", "");
            input.value = em;
        }
        if (email_test(input) || input.value == input_g_value) {
            form_add_error(input);
            error++;
        } else {
            form_remove_error(input);
        }
    } else if (input.getAttribute("type") == "checkbox" && input.checked == false) {
        form_add_error(input);
        error++;
    } else {
        if (input.value == '' || input.value == input_g_value) {
            form_add_error(input);
            error++;
        } else {
            form_remove_error(input);
        }
    }
    return error;
}

function form_add_error(input) {
    input.classList.add('_error');
    input.parentElement.classList.add('_error');

    let input_error = input.parentElement.querySelector('.form__error');
    if (input_error) {
        input.parentElement.removeChild(input_error);
    }
    let input_error_text = input.getAttribute('data-error');
    if (input_error_text && input_error_text != '') {
        input.parentElement.insertAdjacentHTML('beforeend', '<div class="form__error">' + input_error_text + '</div>');
    }
}

function form_remove_error(input) {
    input.classList.remove('_error');
    input.parentElement.classList.remove('_error');

    let input_error = input.parentElement.querySelector('.form__error');
    if (input_error) {
        input.parentElement.removeChild(input_error);
    }
}

function form_clean(form) {
    let inputs = form.querySelectorAll('input,textarea');
    for (let index = 0; index < inputs.length; index++) {
        const el = inputs[index];
        el.parentElement.classList.remove('_focus');
        el.classList.remove('_focus');
        el.value = el.getAttribute('data-value');
    }
    let checkboxes = form.querySelectorAll('.checkbox__input');
    if (checkboxes.length > 0) {
        for (let index = 0; index < checkboxes.length; index++) {
            const checkbox = checkboxes[index];
            checkbox.checked = false;
        }
    }
    let selects = form.querySelectorAll('select');
    if (selects.length > 0) {
        for (let index = 0; index < selects.length; index++) {
            const select = selects[index];
            const select_default_value = select.getAttribute('data-default');
            select.value = select_default_value;
            select_item(select);
        }
    }
}

let viewPass = document.querySelectorAll('.viewpass');
for (let index = 0; index < viewPass.length; index++) {
    const element = viewPass[index];
    element.addEventListener("click", function (e) {
        if (element.classList.contains('_active')) {
            element.parentElement.querySelector('input').setAttribute("type", "password");
        } else {
            element.parentElement.querySelector('input').setAttribute("type", "text");
        }
        element.classList.toggle('_active');
    });
}


//Select
let selects = document.getElementsByTagName('select');
if (selects.length > 0) {
    selects_init();
}

function selects_init() {
    for (let index = 0; index < selects.length; index++) {
        const select = selects[index];
        select_init(select);
    }
    //select_callback();
    document.addEventListener('click', function (e) {
        selects_close(e);
    });
    document.addEventListener('keydown', function (e) {
        if (e.code === 'Escape') {
            selects_close(e);
        }
    });
}

function selects_close(e) {
    const selects = document.querySelectorAll('.select');
    if (!e.target.closest('.select')) {
        for (let index = 0; index < selects.length; index++) {
            const select = selects[index];
            const select_body_options = select.querySelector('.select__options');
            select.classList.remove('_active');
            _slideUp(select_body_options, 100);
        }
    }
}

function select_init(select) {
    const select_parent = select.parentElement;
    const select_modifikator = select.getAttribute('class');
    const select_selected_option = select.querySelector('option:checked');
    select.setAttribute('data-default', select_selected_option.value);
    select.style.display = 'none';

    select_parent.insertAdjacentHTML('beforeend', '<div class="select select_' + select_modifikator + '"></div>');

    let new_select = select.parentElement.querySelector('.select');
    new_select.appendChild(select);
    select_item(select);
}

function select_item(select) {
    const select_parent = select.parentElement;
    const select_items = select_parent.querySelector('.select__item');
    const select_options = select.querySelectorAll('option');
    const select_selected_option = select.querySelector('option:checked');
    const select_selected_text = select_selected_option.text;
    const select_type = select.getAttribute('data-type');

    if (select_items) {
        select_items.remove();
    }

    let select_type_content = '';
    if (select_type == 'input') {
        select_type_content = '<div class="select__value icon-select-arrow"><input autocomplete="off" type="text" name="form[]" value="' + select_selected_text + '" data-error="Ошибка" data-value="' + select_selected_text + '" class="select__input"></div>';
    } else {
        select_type_content = '<div class="select__value icon-select-arrow"><span>' + select_selected_text + '</span></div>';
    }

    select_parent.insertAdjacentHTML('beforeend',
        '<div class="select__item">' +
        '<div class="select__title">' + select_type_content + '</div>' +
        '<div class="select__options">' + select_get_options(select_options) + '</div>' +
        '</div></div>');

    select_actions(select, select_parent);
}

function select_actions(original, select) {
    const select_item = select.querySelector('.select__item');
    const select_body_options = select.querySelector('.select__options');
    const select_options = select.querySelectorAll('.select__option');
    const select_type = original.getAttribute('data-type');
    const select_input = select.querySelector('.select__input');

    select_item.addEventListener('click', function () {
        let selects = document.querySelectorAll('.select');
        for (let index = 0; index < selects.length; index++) {
            const select = selects[index];
            const select_body_options = select.querySelector('.select__options');
            if (select != select_item.closest('.select')) {
                select.classList.remove('_active');
                _slideUp(select_body_options, 100);
            }
        }
        _slideToggle(select_body_options, 100);
        select.classList.toggle('_active');
    });

    for (let index = 0; index < select_options.length; index++) {
        const select_option = select_options[index];
        const select_option_value = select_option.getAttribute('data-value');
        const select_option_text = select_option.innerHTML;

        if (select_type == 'input') {
            select_input.addEventListener('keyup', select_search);
        } else {
            if (select_option.getAttribute('data-value') == original.value) {
                select_option.style.display = 'none';
            }
        }
        select_option.addEventListener('click', function () {
            for (let index = 0; index < select_options.length; index++) {
                const el = select_options[index];
                el.style.display = 'block';
            }
            if (select_type == 'input') {
                select_input.value = select_option_text;
                original.value = select_option_value;
            } else {
                select.querySelector('.select__value').innerHTML = '<span>' + select_option_text + '</span>';
                original.value = select_option_value;
                select_option.style.display = 'none';
                if (original.value != 0) {
                    let selectRow = original.closest('._item-input');
                    // selectRow.classList.add('_focus')
                }
            }
        });
    }
}

function select_get_options(select_options) {
    if (select_options) {
        let select_options_content = '';
        for (let index = 0; index < select_options.length; index++) {
            const select_option = select_options[index];
            const select_option_value = select_option.value;
            if (select_option_value != '') {
                const select_option_text = select_option.text;
                select_options_content = select_options_content + '<div data-value="' + select_option_value + '" class="select__option">' + select_option_text + '</div>';
            }
        }
        return select_options_content;
    }
}

function select_search(e) {
    let select_block = e.target.closest('.select ').querySelector('.select__options');
    let select_options = e.target.closest('.select ').querySelectorAll('.select__option');
    let select_search_text = e.target.value.toUpperCase();

    for (let i = 0; i < select_options.length; i++) {
        let select_option = select_options[i];
        let select_txt_value = select_option.textContent || select_option.innerText;
        if (select_txt_value.toUpperCase().indexOf(select_search_text) > -1) {
            select_option.style.display = "";
        } else {
            select_option.style.display = "none";
        }
    }
}

function selects_update_all() {
    let selects = document.querySelectorAll('select');
    if (selects) {
        for (let index = 0; index < selects.length; index++) {
            const select = selects[index];
            select_item(select);
        }
    }
}

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

                if (input.classList.contains('_phone')) {
                    //'+38(999) 999 9999'
                    //'+375(99)999-99-99'
                    input.classList.add('_mask');
                    Inputmask("+(373) 99 999 999", {
                        //"placeholder": '',
                        clearIncomplete: true,
                        clearMaskOnLostFocus: true,
                        onincomplete: function () {
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
let scr_body = document.querySelector('body');
let scr_blocks = document.querySelectorAll('._scr-sector');
let scr_items = document.querySelectorAll('._scr-item');
let scr_fix_block = document.querySelectorAll('._side-wrapper');
let scr_min_height = 750;

let scrolling = true;
let scrolling_full = true;

let scrollDirection = 0;

//ScrollOnScroll
window.addEventListener('scroll', scroll_scroll);

function scroll_scroll() {
    //scr_body.setAttribute('data-scroll', pageYOffset);
    let src_value = pageYOffset;
    let header = document.querySelector('header.header');
    if (header !== null) {
        if (src_value > 10) {
            header.classList.add('_scroll');
        } else {
            header.classList.remove('_scroll');
        }
    }
    if (scr_blocks.length > 0) {
        for (let index = 0; index < scr_blocks.length; index++) {
            let block = scr_blocks[index];
            let block_offset = offset(block).top;
            let block_height = block.offsetHeight;

            if ((pageYOffset > block_offset - window.innerHeight / 1.5) && pageYOffset < (block_offset + block_height) - window.innerHeight / 5) {
                block.classList.add('_scr-sector_active');
            } else {
                if (block.classList.contains('_scr-sector_active')) {
                    block.classList.remove('_scr-sector_active');
                }
            }
            if ((pageYOffset > block_offset - window.innerHeight / 2) && pageYOffset < (block_offset + block_height) - window.innerHeight / 5) {
                if (!block.classList.contains('_scr-sector_current')) {
                    block.classList.add('_scr-sector_current');
                }
            } else {
                if (block.classList.contains('_scr-sector_current')) {
                    block.classList.remove('_scr-sector_current');
                }
            }
        }
    }
    if (scr_items.length > 0) {
        for (let index = 0; index < scr_items.length; index++) {
            let scr_item = scr_items[index];
            let scr_item_offset = offset(scr_item).top;
            let scr_item_height = scr_item.offsetHeight;


            let scr_item_point = window.innerHeight - (window.innerHeight - scr_item_height / 3);
            if (window.innerHeight > scr_item_height) {
                scr_item_point = window.innerHeight - scr_item_height / 3;
            }

            if ((src_value > scr_item_offset - scr_item_point) && src_value < (scr_item_offset + scr_item_height)) {
                scr_item.classList.add('_active');
                scroll_load_item(scr_item);
            } else {
                scr_item.classList.remove('_active');
            }
            if (((src_value > scr_item_offset - window.innerHeight))) {
                if (scr_item.querySelectorAll('._lazy').length > 0) {
                    scroll_lazy(scr_item);
                }
            }
        }
    }

    if (scr_fix_block.length > 0) {
        fix_block(scr_fix_block, src_value);
    }
    let custom_scroll_line = document.querySelector('._custom-scroll__line');
    if (custom_scroll_line) {
        let window_height = window.innerHeight;
        let content_height = document.querySelector('.wrapper').offsetHeight;
        let scr_procent = (pageYOffset / (content_height - window_height)) * 100;
        let custom_scroll_line_height = custom_scroll_line.offsetHeight;
        custom_scroll_line.style.transform = "translateY(" + (window_height - custom_scroll_line_height) / 100 * scr_procent + "px)";
    }

    if (src_value > scrollDirection) {
        // downscroll code
    } else {
        // upscroll code
    }
    scrollDirection = src_value <= 0 ? 0 : src_value;
}

setTimeout(function () {
    //document.addEventListener("DOMContentLoaded", scroll_scroll);
    scroll_scroll();
}, 100);

function scroll_lazy(scr_item) {
    let lazy_src = scr_item.querySelectorAll('*[data-src]');
    if (lazy_src.length > 0) {
        for (let index = 0; index < lazy_src.length; index++) {
            const el = lazy_src[index];
            if (!el.classList.contains('_loaded')) {
                el.setAttribute('src', el.getAttribute('data-src'));
                el.classList.add('_loaded');
            }
        }
    }
    let lazy_srcset = scr_item.querySelectorAll('*[data-srcset]');
    if (lazy_srcset.length > 0) {
        for (let index = 0; index < lazy_srcset.length; index++) {
            const el = lazy_srcset[index];
            if (!el.classList.contains('_loaded')) {
                el.setAttribute('srcset', el.getAttribute('data-srcset'));
                el.classList.add('_loaded');
            }
        }
    }
}

function scroll_load_item(scr_item) {
    if (scr_item.classList.contains('_load-map') && !scr_item.classList.contains('_loaded-map')) {
        let map_item = document.getElementById('map');
        if (map_item) {
            scr_item.classList.add('_loaded-map');
            map();
        }
    }
}

//FullScreenScroll
if (scr_blocks.length > 0 && !isMobile.any()) {
    disableScroll();
    window.addEventListener('wheel', full_scroll);
}

function full_scroll(e) {
    let viewport_height = window.innerHeight;
    if (viewport_height >= scr_min_height) {
        if (scrolling_full) {
            // ВЫЧИСЛИТЬ!!!
            let current_scroll = pageYOffset;//parseInt(scr_body.getAttribute('data-scroll'));
            //
            let current_block = document.querySelector('._scr-sector._scr-sector_current');
            let current_block_pos = offset(current_block).top;
            let current_block_height = current_block.offsetHeight;
            let current_block_next = current_block.nextElementSibling;
            let current_block_prev = current_block.previousElementSibling;
            let block_pos;
            if (e.keyCode == 40 || e.keyCode == 34 || e.deltaX > 0 || e.deltaY < 0) {
                if (current_block_prev) {
                    let current_block_prev_height = current_block_prev.offsetHeight;
                    block_pos = offset(current_block_prev).top;
                    if (current_block_height <= viewport_height) {
                        if (current_block_prev_height >= viewport_height) {
                            block_pos = block_pos + (current_block_prev_height - viewport_height);
                            full_scroll_to_sector(block_pos);
                        }
                    } else {
                        enableScroll();
                        if (current_scroll <= current_block_pos) {
                            full_scroll_to_sector(block_pos);
                        }
                    }
                } else {
                    full_scroll_pagestart();
                }
            } else if (e.keyCode == 38 || e.keyCode == 33 || e.deltaX < 0 || e.deltaY > 0) {
                if (current_block_next) {
                    block_pos = offset(current_block_next).top;
                    if (current_block_height <= viewport_height) {
                        full_scroll_to_sector(block_pos);
                    } else {
                        enableScroll();
                        if (current_scroll >= block_pos - viewport_height) {
                            full_scroll_to_sector(block_pos);
                        }
                    }
                } else {
                    full_scroll_pageend();
                }
            }
        } else {
            disableScroll();
        }
    } else {
        enableScroll();
    }
}

function full_scroll_to_sector(pos) {
    disableScroll();
    scrolling_full = false;
    _goto(pos, 800);

    let scr_pause = 500;
    if (navigator.appVersion.indexOf("Mac") != -1) {
        scr_pause = 1000;
    }
    ;
    setTimeout(function () {
        scrolling_full = true;
    }, scr_pause);
}

function full_scroll_pagestart() {
}

function full_scroll_pageend() {
}

//ScrollOnClick (Navigation)
let link = document.querySelectorAll('._goto-block');
if (link) {
    let blocks = [];
    for (let index = 0; index < link.length; index++) {
        let el = link[index];
        let block_name = el.getAttribute('href').replace('#', '');
        if (block_name != '' && !~blocks.indexOf(block_name)) {
            blocks.push(block_name);
        }
        el.addEventListener('click', function (e) {
            if (document.querySelector('.menu__body._active')) {
                menu_close();
                body_lock_remove(500);
            }
            let target_block_class = el.getAttribute('href').replace('#', '');
            let target_block = document.querySelector('.' + target_block_class);
            let headerHeight = document.querySelector('header').clientHeight + 60;
            _goto(target_block, 300, headerHeight);
            e.preventDefault();
        })
    }

    window.addEventListener('scroll', function (el) {
        let old_current_link = document.querySelectorAll('._goto-block._active');
        if (old_current_link) {
            for (let index = 0; index < old_current_link.length; index++) {
                let el = old_current_link[index];
                el.classList.remove('_active');
            }
        }
        for (let index = 0; index < blocks.length; index++) {
            let block = blocks[index];
            let block_item = document.querySelector('.' + block);
            if (block_item) {
                let block_offset = offset(block_item).top;
                let block_height = block_item.offsetHeight;
                if ((pageYOffset > block_offset - window.innerHeight / 3) && pageYOffset < (block_offset + block_height) - window.innerHeight / 3) {
                    let current_links = document.querySelectorAll('._goto-block[href="#' + block + '"]');
                    for (let index = 0; index < current_links.length; index++) {
                        let current_link = current_links[index];
                        current_link.classList.add('_active');
                    }
                }
            }
        }
    })
}
//ScrollOnClick (Simple)
let goto_links = document.querySelectorAll('._goto');
if (goto_links) {
    for (let index = 0; index < goto_links.length; index++) {
        let goto_link = goto_links[index];
        goto_link.addEventListener('click', function (e) {
            let target_block_class = goto_link.getAttribute('href').replace('#', '');
            let target_block = document.querySelector('.' + target_block_class);
            _goto(target_block, 300);
            e.preventDefault();
        });
    }
}

function _goto(target_block, speed, offset = 50) {
    let header = '';
    //OffsetHeader
    //if (window.innerWidth < 992) {
    //	header = 'header';
    //}
    let options = {
        speedAsDuration: true,
        speed: speed,
        header: header,
        offset: offset,
        easing: 'easeOutQuad',
    };
    let scr = new SmoothScroll();
    scr.animateScroll(target_block, '', options);
}

//SameFunctions
function offset(el) {
    var rect = el.getBoundingClientRect(),
        scrollLeft = window.pageXOffset || document.documentElement.scrollLeft,
        scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    return {top: rect.top + scrollTop, left: rect.left + scrollLeft}
}

function disableScroll() {
    if (window.addEventListener) // older FF
        window.addEventListener('DOMMouseScroll', preventDefault, false);
    document.addEventListener('wheel', preventDefault, {passive: false}); // Disable scrolling in Chrome
    window.onwheel = preventDefault; // modern standard
    window.onmousewheel = document.onmousewheel = preventDefault; // older browsers, IE
    window.ontouchmove = preventDefault; // mobile
    document.onkeydown = preventDefaultForScrollKeys;
}

function enableScroll() {
    if (window.removeEventListener)
        window.removeEventListener('DOMMouseScroll', preventDefault, false);
    document.removeEventListener('wheel', preventDefault, {passive: false}); // Enable scrolling in Chrome
    window.onmousewheel = document.onmousewheel = null;
    window.onwheel = null;
    window.ontouchmove = null;
    document.onkeydown = null;
}

function preventDefault(e) {
    e = e || window.event;
    if (e.preventDefault)
        e.preventDefault();
    e.returnValue = false;
}

function preventDefaultForScrollKeys(e) {
    /*if (keys[e.keyCode]) {
        preventDefault(e);
        return false;
    }*/
}

function fix_block(scr_fix_block, scr_value) {
    let window_width = parseInt(window.innerWidth);
    let window_height = parseInt(window.innerHeight);
    let header_height = parseInt(document.querySelector('header').offsetHeight) + 15;
    for (let index = 0; index < scr_fix_block.length; index++) {
        const block = scr_fix_block[index];
        let block_width = block.getAttribute('data-width');
        const item = block.querySelector('._side-block');
        if (!block_width) {
            block_width = 0;
        }
        if (window_width > block_width) {
            if (item.offsetHeight < window_height - (header_height + 30)) {
                if (scr_value > offset(block).top - (header_height + 15)) {
                    item.style.cssText = "position:fixed;bottom:auto;top:" + header_height + "px;width:" + block.offsetWidth + "px;left:" + offset(block).left + "px;";
                } else {
                    gotoRelative(item);
                }
                if (scr_value > (block.offsetHeight + offset(block).top) - (item.offsetHeight + (header_height + 15))) {
                    block.style.cssText = "position:relative;";
                    item.style.cssText = "position:absolute;bottom:0;top:auto;left:0px;width:100%";
                }
            } else {
                gotoRelative(item);
            }
        }
    }

    function gotoRelative(item) {
        item.style.cssText = "position:relative;bottom:auto;top:0px;left:0px;";
    }
}

if (!isMobile.any()) {
    //custom_scroll();
    /*
    window.addEventListener('wheel', scroll_animate, {
        capture: true,
        passive: true
    });
    window.addEventListener('resize', custom_scroll, {
        capture: true,
        passive: true
    });
    */
}

function custom_scroll(event) {
    scr_body.style.overflow = 'hidden';
    let window_height = window.innerHeight;
    let custom_scroll_line = document.querySelector('._custom-scroll__line');
    let custom_scroll_content_height = document.querySelector('.wrapper').offsetHeight;
    let custom_cursor_height = Math.min(window_height, Math.round(window_height * (window_height / custom_scroll_content_height)));
    if (custom_scroll_content_height > window_height) {
        if (!custom_scroll_line) {
            let custom_scroll = document.createElement('div');
            custom_scroll_line = document.createElement('div');
            custom_scroll.setAttribute('class', '_custom-scroll');
            custom_scroll_line.setAttribute('class', '_custom-scroll__line');
            custom_scroll.appendChild(custom_scroll_line);
            scr_body.appendChild(custom_scroll);
        }
        custom_scroll_line.style.height = custom_cursor_height + 'px';
    }
}

let new_pos = pageYOffset;

function scroll_animate(event) {
    let window_height = window.innerHeight;
    let content_height = document.querySelector('.wrapper').offsetHeight;
    let start_position = pageYOffset;
    let pos_add = 100;

    if (event.keyCode == 40 || event.keyCode == 34 || event.deltaX > 0 || event.deltaY < 0) {
        new_pos = new_pos - pos_add;
    } else if (event.keyCode == 38 || event.keyCode == 33 || event.deltaX < 0 || event.deltaY > 0) {
        new_pos = new_pos + pos_add;
    }
    if (new_pos > (content_height - window_height)) new_pos = content_height - window_height;
    if (new_pos < 0) new_pos = 0;

    if (scrolling) {
        scrolling = false;
        _goto(new_pos, 1000);

        let scr_pause = 100;
        if (navigator.appVersion.indexOf("Mac") != -1) {
            scr_pause = scr_pause * 2;
        }
        ;
        setTimeout(function () {
            scrolling = true;
            _goto(new_pos, 1000);
        }, scr_pause);
    }
    //If native scroll
    //disableScroll();
}

function filter_products() {
    var lang = $('html').attr('lang');
    var sorder = $('select[name="sorder"]').val();
    var category_id = $('input[name="category_id"]').val();
    var price_min = $('input[name="price-start"]').val();
    var price_max = $('input[name="price-end"]').val();

    var url = 'filter_category';
    var get_brands = '';
    $('input[name="brands"]:checkbox:checked').each(function () {
        get_brands = get_brands + '&brand%5B%5D=' + $(this).val();
    });

    var get_sex = '';
    $('input[name="sex"]:checkbox:checked').each(function () {
        get_sex = get_sex + '&sex%5B%5D=' + $(this).val();
    });

    var get_categories = '';

    var brands_filter = $('input[name="brands_filter"]').val();
    if (brands_filter) {
        url = 'filter_brands';
        $('input[name="category"]:checkbox:checked').each(function () {
            get_categories = get_categories + '&category%5B%5D=' + $(this).val();
        });

        sorder = $('select[name="sorder_brand"]').val();
        get_brands = '&brand%5B%5D=' + $('input[name="brands"]').val();
    }

    var bests_filter = $('input[name="bests_filter"]').val();
    if(bests_filter) {
        url = 'filter_bests';
        $('input[name="category"]:checkbox:checked').each(function () {
            get_categories = get_categories + '&category%5B%5D=' + $(this).val();
        });
    }


    sorder = 'sort=' + sorder;
    if (category_id) category_id = '&category_id=' + category_id; else category_id = '';
    if (price_min) price_min = '&price_min=' + price_min; else price_min = '';
    if (price_max) price_max = '&price_max=' + price_max; else price_max = '';

    var filter = sorder + category_id + price_min + price_max + get_brands + get_categories + get_sex;
    history.pushState({}, ' ', '?' + filter);

    $.ajax({
        url: '/' + lang + '/products/' + url + '?' + filter, // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'json',
        data: {filter: filter},
        success: function (data) {
            $('.products_catalog').html(data.html);
            $('.head-main-catalog__counts').html(data.count);
            $('.head-catalog__counts').html(data.count);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
    return false;
}

$('body.catalog_page').on('click', '.head-main-catalog__select .select .select__options .select__option', function (e) {
    filter_products();
});
$('body.catalog_page').on('click', '.category_sorder .select .select__options .select__option', function (e) {
    filter_products();
});
$('body.catalog_page').on('change', 'input[name="brands"]', function (e) {
    filter_products();
});
$('body.catalog_page').on('change', 'input[name="sex"]', function (e) {
    filter_products();
});
$('body.catalog_page').on('change', 'input[name="category"]', function (e) {
    filter_products();
});
$('body.catalog_page').on('change', 'input[name="price-start"], input[name="price-end"]', function (e) {
    filter_products();
});
$('body.catalog_page').on('click', '.noUi-handle', function (e) {
    filter_products();
});

function filter_offers_products() {
    var lang = $('html').attr('lang');
    var sorder = $('select[name="sorder"]').val();
    var offer_id = $('input[name="offer_id"]').val();
    var price_min = $('input[name="price-start"]').val();
    var price_max = $('input[name="price-end"]').val();
    var get_brands = '';
    $('input[name="brands"]:checkbox:checked').each(function () {
        get_brands = get_brands + '&brand%5B%5D=' + $(this).val();
    });
    sorder = 'sort=' + sorder;
    if (offer_id) offer_id = '&offer_id=' + offer_id; else offer_id = '';
    if (price_min) price_min = '&price_min=' + price_min; else price_min = '';
    if (price_max) price_max = '&price_max=' + price_max; else price_max = '';

    var filter = sorder + offer_id + price_min + price_max + get_brands;
    history.pushState({}, ' ', '?' + filter);

    $.ajax({
        url: '/' + lang + '/products/filter_offer?' + filter, // путь к обработчику
        type: 'POST', // метод отправки
        dataType: 'json',
        data: {filter: filter},
        success: function (data) {
            $('.products_catalog').html(data.html);
            $('.head-main-catalog__counts').html(data.count);
            $('.head-catalog__counts').html(data.count);
        },
        error: function (data) {
            console.log(data); // выводим ошибку в консоль
        }
    });
    return false;
}

$('body.offers').on('click', '.head-main-catalog__select .select .select__options .select__option', function (e) {
    filter_offers_products();
});
$('body.offers').on('change', 'input[name="brands"]', function (e) {
    filter_offers_products();
});
$('body.offers').on('change', 'input[name="price-start"], input[name="price-end"]', function (e) {
    filter_offers_products();
});
$('body.offers').on('click', '.noUi-handle', function (e) {
    filter_offers_products();
});


$('body').on('click', '.search-actions-main-header__icon_search', function (e) {
    e.preventDefault();
    const $search = $('#head_search');

    $('#head_search').show();          // или addClass('active')
    $('#head_search').prop('disabled', false);

    setTimeout(() => {
        $('#head_search').focus();
    }, 100);
});


