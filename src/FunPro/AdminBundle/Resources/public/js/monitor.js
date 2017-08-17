var carMarkers = [];
var serviceMarkers = [];
var carMarkerCluster;
var onlineCounter = 0;
var inServiceCounter = 0;

function setMapOnCarMarkers(map) {
    for (var i = 0; i < carMarkers.length; i++) {
        carMarkers[i].setMap(map);
    }
}

function setMapOnServiceMarkers(map) {
    for (var i = 0; i < serviceMarkers.length; i++) {
        serviceMarkers[i].setMap(map);
    }
}

function clearCarMarkers() {
    setMapOnCarMarkers(null);
    carMarkers = [];
}

function clearServiceMarkers() {
    setMapOnServiceMarkers(null);
    serviceMarkers = [];
}

function showCarMarkers() {
    setMapOnCarMarkers(map);
}

function showServiceMarkers() {
    setMapOnServiceMarkers(map);
}

function createDriverInfoWindow(wakeful) {
    var driverAvatar;
    var carImage;

    if (wakeful.car.driver.hasOwnProperty('avatar')) {
        driverAvatar = wakeful.car.driver.avatar;
    } else {
        driverAvatar = 'default_avatar.jpg';
    }

    driverAvatar = Routing.generate('liip_imagine_filter', {
        'filter': 'panel_monitoring_avatar_thumb',
        path: driverAvatar
    });


    if (wakeful.car.hasOwnProperty('image')) {
        carImage = wakeful.car.image;
    } else {
        carImage = 'default_car.jpg';
    }

    carImage = Routing.generate('liip_imagine_filter', {
        'filter': 'panel_monitoring_car_thumb',
        path: carImage
    });

    $('#driverAvatar').attr('src', driverAvatar);
    $('#driverProfile').attr('href', Routing.generate('fun_pro_admin_edit_driver', {id: wakeful.car.driver.id}));
    $('#driverName').text(wakeful.car.driver.name);
    $('#driverContractNumber').text(wakeful.car.driver.contractNumber);
    $('#driverMobileNumber').text(wakeful.car.driver.mobile);
    $('#driverNationalCode').text(wakeful.car.driver.nationalCode);
    $('#driverRate').text(wakeful.car.driver.rate);

    $('#carImage').attr('src', carImage);
    $('#carName').text(wakeful.car.type);
    $('#carColor').text(wakeful.car.color);
    $('#carRate').text(wakeful.car.rate);

    switch (wakeful.car.status) {
        case 0:
            $('#carStatus').text('آفلاین');
            $('#driverStatus').text('آفلاین');
            break;
        case 1:
            $('#carStatus').text('آنلاین');
            $('#driverStatus').text('آنلاین');
            break;
        case 2:
            $('#carStatus').text('تایید سفر');
            $('#driverStatus').text('تایید سفر');
            break;
        case 3:
            $('#carStatus').text('سفر به مبدا');
            $('#driverStatus').text('سفر به مبدا');
            break;
        case 4:
            $('#carStatus').text('انتظار برای مسافر');
            $('#driverStatus').text('انتظار برای مسافر');
            break;
        case 5:
            $('#carStatus').text('شروع سفر');
            $('#driverStatus').text('شروع سفر');
            break;
        case 6:
            $('#carStatus').text('در حال سفر');
            $('#driverStatus').text('در حال سفر');
            break;
        case 7:
            $('#carStatus').text('پایان سفر');
            $('#driverStatus').text('پایان سفر');
            break;
        case 8:
            $('#carStatus').text('تایید سفر دیگر');
            $('#driverStatus').text('تایید سفر دیگر');
            break;
        case 9:
            $('#carStatus').text('سفر به مبدا دیگر');
            $('#driverStatus').text('سفر به مبدا دیگر');
            break;
    }

    $('#driver').modal('toggle');
}

function showWakeful() {
    if (!$('.autoRefresh label').hasClass('active')) {
        return;
    }

    $.ajax({
        url: Routing.generate('fun_pro_admin_cget_monitor_wakeful', {'latitude': map.getCenter().lat(), 'longitude': map.getCenter().lng(), limit: 5000}),
        type: 'GET',
        beforeSend: function(xhr){xhr.setRequestHeader('Accept', 'application/json');},

        success: function(result, status, xhr) {
            if (xhr.status == 200) {
                inServiceCounter = 0;
                onlineCounter = 0;
                clearCarMarkers();
                var icon = '';

                for (var i=0; i < result.length; i++) {
                    onlineCounter++;
                    if (result[i].car.status == 1 || result[i].car.status == 7) {
                        icon = map.getZoom() <= 13 ? blueMarkerSmall : blueMarker;
                    } else {
                        inServiceCounter ++;
                        icon = map.getZoom() <= 13 ? purpleMarkerSmall : purpleMarker;
                    }

                    var carMarker = new google.maps.Marker({
                        position: {lat: result[i].point.latitude, lng: result[i].point.longitude},
                        map: map,
                        icon: icon
                    });
                    carMarkers.push(carMarker);

                    (function(marker, wakeful) {
                        marker.addListener('click', function() {
                            createDriverInfoWindow(wakeful);
                        });
                    })(carMarker, result[i])
                }

                if (carMarkerCluster !== undefined) {
                    carMarkerCluster.clearMarkers();
                }

                carMarkerCluster = new MarkerClusterer(map, carMarkers,
                    {imagePath: '/bundles/funproadmin/map/markerclusterer/images/m'});

                $('.inServiceCounter').attr('data-value', inServiceCounter);
                $('.inServiceCounter').counterUp();

                $('.onlineCounter').attr('data-value', onlineCounter);
                $('.onlineCounter').counterUp();
            }
        }
    });
}

function showService() {
    if (!$('.showService label').hasClass('active')) {
        return;
    }

    var from = new Date();
    from.setMinutes(from.getMinutes() - 30);
    moment(from).format('YYYY-MM-DD HH:mm:ss');

    var url = Routing.generate('fun_pro_service_api_cget_service', {
        'latitude': map.getCenter().lat(),
        'longitude': map.getCenter().lng(),
        limit: 5000,
        from: from,
        order: 'createdAt'
    });

    $.ajax({
        url: url,
        type: 'GET',
        beforeSend: function(xhr){xhr.setRequestHeader('Accept', 'application/json');},
        success: function(result, status, xhr) {
            console.log(result);
            if (xhr.status == 200) {

                clearServiceMarkers();
                var icon = '';

                console.log(result);
                for (var i=0; i < result.data.length; i++) {
                    if (result[i].car.status == 1 || result[i].car.status == 7) {
                        icon = map.getZoom() <= 13 ? blueMarkerSmall : blueMarker;
                    } else {
                        inServiceCounter ++;
                        icon = map.getZoom() <= 13 ? purpleMarkerSmall : purpleMarker;
                    }

                    var carMarker = new google.maps.Marker({
                        position: {lat: result[i].point.latitude, lng: result[i].point.longitude},
                        map: map,
                        icon: icon
                    });
                    markers.push(carMarker);

                    (function(marker, wakeful) {
                        marker.addListener('click', function() {
                            createInfoWindow(wakeful);
                        });
                    })(carMarker, result[i])
                }

                // if (carMarkerCluster !== undefined) {
                //     carMarkerCluster.clearMarkers();
                // }
                //
                // carMarkerCluster = new MarkerClusterer(map, markers,
                //     {imagePath: '/bundles/funproadmin/map/markerclusterer/images/m'});
                //
                // $('.inServiceCounter').attr('data-value', inServiceCounter);
                // $('.inServiceCounter').counterUp();
                //
                // $('.onlineCounter').attr('data-value', onlineCounter);
                // $('.onlineCounter').counterUp();
            }
        }
    });
}

$(document).ready(function(){
    window.setInterval(showWakeful, 8000);
    window.setInterval(showService, 8000);
});