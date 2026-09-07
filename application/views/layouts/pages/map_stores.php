<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDVXDMzBfVgQ9JYbAbbSV64Oojgx5FQekQ&amp;callback=Function.prototype"></script>
<script>
    if (document.querySelector('#map')) {

        google.maps.event.addDomListener(window, 'load', init);
        var map_stores;
        var marcers_all = [];
        var locations = [
                <?php foreach ($stores as $store){
                $crd = '47.0183674,28.8516902';
                if (!empty($store->coords)) $crd = $store->coords;
                $ca = explode(',', $crd);
                $map_desc = str_replace("\n", ' ', strip_tags($store->desc));
                $map_desc = str_replace("\r", ' ', strip_tags($map_desc));
                ?>
            ['<?=$store->title?>', <?=$ca[0]?>, <?=$ca[1]?>, '<?=$store->text?>', '<?=$map_desc?>', '<?=$store->phone?>'],
            <?php } ?>
            ];

        function init() {
            var myOptions = {
                center: new google.maps.LatLng('47.017310772385', '28.8299499366223'), // Координаты, какое место отображать на карте
                zoom: 13, // Уровень риближения карты
                mapTypeId: google.maps.MapTypeId.ROADMAP // Тип карты
            };

            map_stores = new google.maps.Map(document.getElementById("map"), // В каком блоке будет отображаться карта
                myOptions);
            setMarkers(map_stores, locations);
        }

        function setMarkers(map, locations) {

            var marker, i, mark_position;
            for (i = 0; i < locations.length; i++) { // Проходимся по нашему массиву с марками

                // Тут вроде и так все понятно
                var title = locations[i][0];
                var lat = locations[i][1];
                var long = locations[i][2];
                var address = locations[i][3];
                var timework = locations[i][4];
                var phone = locations[i][5];


                mark_position = new google.maps.LatLng(lat, long); // Создаем позицию для отметки

                marker = new google.maps.Marker({ // Что будет содержаться в отметке
                    map: map, // К какой карте относиться отметка
                    title: title, // Заголовок отметки
                    position: mark_position, // Позиция отметки
                    animation: google.maps.Animation.DROP, // Анимация
                    address: address,
                    timework: timework,
                    phone: phone,
                    icon: {
                        url: '/app/img/icons/map.svg',
                        scaledSize: new google.maps.Size(38, 38)
                    }
                });
                marcers_all[i] = marker;

                // При нажатии на марку, будет отображаться контент
                google.maps.event.addListener(marker, 'click', (function (marker) {

                    return function () {
                        let infoOrderMap = document.querySelector('.info-map-order-map');
                        let infoMapStore = document.querySelector('.info-map-store');

                        if (infoOrderMap) {
                            let infoOrderMapList = infoOrderMap.closest('.map-order-map__list');
                            let infoOrderMapTitle = infoOrderMap.querySelector('.info-map-order-map__name');
                            let infoOrderMapAddress = infoOrderMap.querySelector('.info-map-order-map__address');
                            let infoOrderMapTimework = infoOrderMap.querySelector('.other-info-map-order-map__value._timework');
                            let infoOrderMapPhone = infoOrderMap.querySelector('.other-info-map-order-map__value._phone');


                            let markerTimeworks = marker.timework;
                            if (markerTimeworks) {
                                let timeworkcontent = '';
                                for (let index = 0; index < markerTimeworks.length; index++) {
                                    const markerTimework = markerTimeworks[index];
                                    if (index == 0) {
                                        timeworkcontent += markerTimework;
                                    } else {
                                        timeworkcontent += '<br>' + markerTimework;
                                    }
                                }
                                infoOrderMapTimework.innerHTML = timeworkcontent;
                            }
                            infoOrderMapTitle.textContent = marker.title;
                            infoOrderMapAddress.textContent = marker.address;
                            infoOrderMapPhone.textContent = marker.phone;
                            infoOrderMapPhone.setAttribute('href', `tel:${marker.phone.replaceAll(' ', '')}`);
                            infoOrderMapList.classList.add('_view');
                        }

                        if (infoMapStore) {
                            let infoMapStoreTitle = infoMapStore.querySelector('.info-map-store__name');
                            let infoMapStoreAddress = infoMapStore.querySelector('.info-map-store__address');
                            let infoMapStoreTimework = infoMapStore.querySelector('.other-info-map-store__value._timework');
                            let infoMapStorePhone = infoMapStore.querySelector('.other-info-map-store__value._phone');


                            let markerTimeworks = marker.timework;
                            if (markerTimeworks) {
                                let timeworkcontent = '';
                                for (let index = 0; index < markerTimeworks.length; index++) {
                                    const markerTimework = markerTimeworks[index];
                                    if (index == 0) {
                                        timeworkcontent += markerTimework;
                                    } else {
                                        timeworkcontent += '' + markerTimework;
                                    }
                                }
                                infoMapStoreTimework.innerHTML = timeworkcontent;
                            }
                            infoMapStoreTitle.textContent = marker.title;
                            infoMapStoreAddress.textContent = marker.address;
                            infoMapStorePhone.textContent = marker.phone;
                            infoMapStorePhone.setAttribute('href', `tel:${marker.phone.replaceAll(' ', '')}`);
                            infoMapStore.classList.add('_view');
                        }

                        map.setZoom(17);
                        map.panTo(marker.position);
                    };
                })(marker));
            }
            // добавить кластеры
            // new MarkerClusterer(map, marcers_all, {
            // 	imagePath:
            // 		"https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
            // });
        }
    }
</script>