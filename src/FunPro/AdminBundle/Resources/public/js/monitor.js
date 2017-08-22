var carMarkers = [];
var serviceMarkers = [];
var carMarkerCluster;
var serviceMarkerCluster;
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

function createServiceInfoWindow(service) {
    var driverAvatar;
    var car = service.car;

    if (car !== null) {
        if (car.driver.hasOwnProperty('avatar')) {
            driverAvatar = car.driver.avatar;
        } else {
            driverAvatar = 'default_avatar.jpg';
        }

        driverAvatar = Routing.generate('liip_imagine_filter', {
            'filter': 'panel_monitoring_avatar_thumb',
            path: driverAvatar
        });

        $('#serviceDriverAvatar').attr('src', driverAvatar);
        $('#serviceDriverProfile').attr('href', Routing.generate('fun_pro_admin_edit_driver', {id: car.driver.id}));
        $('#serviceDriverName').text(car.driver.name);
        $('#serviceDriverContractNumber').text(car.driver.contractNumber);
        $('#serviceDriverMobileNumber').text(car.driver.mobile);
        $('#serviceDriverNationalCode').text(car.driver.nationalCode);
        $('#serviceDriverRate').text(car.driver.rate);

        $('#carName').text(car.type);
        $('#carColor').text(car.color);
        $('#carRate').text(car.rate);

        switch (car.status) {
            case 0:
                $('#carStatus').text('آفلاین');
                $('#serviceDriverStatus').text('آفلاین');
                break;
            case 1:
                $('#carStatus').text('آنلاین');
                $('#serviceDriverStatus').text('آنلاین');
                break;
            case 2:
                $('#carStatus').text('تایید سفر');
                $('#serviceDriverStatus').text('تایید سفر');
                break;
            case 3:
                $('#carStatus').text('سفر به مبدا');
                $('#serviceDriverStatus').text('سفر به مبدا');
                break;
            case 4:
                $('#carStatus').text('انتظار برای مسافر');
                $('#serviceDriverStatus').text('انتظار برای مسافر');
                break;
            case 5:
                $('#carStatus').text('شروع سفر');
                $('#serviceDriverStatus').text('شروع سفر');
                break;
            case 6:
                $('#carStatus').text('در حال سفر');
                $('#serviceDriverStatus').text('در حال سفر');
                break;
            case 7:
                $('#carStatus').text('پایان سفر');
                $('#serviceDriverStatus').text('پایان سفر');
                break;
            case 8:
                $('#carStatus').text('تایید سفر دیگر');
                $('#serviceDriverStatus').text('تایید سفر دیگر');
                break;
            case 9:
                $('#carStatus').text('سفر به مبدا دیگر');
                $('#serviceDriverStatus').text('سفر به مبدا دیگر');
                break;
        }
    }

    $('#service').modal('toggle');
}

function showWakeful() {
    if (!$('.autoRefresh label').hasClass('active')) {
        console.log('clear cars');
        if (carMarkerCluster !== undefined) {
            carMarkerCluster.clearMarkers();
        }
        clearCarMarkers();
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
                        icon = map.getZoom() <= 13 ? iconMarker.freeCarSmall : iconMarker.freeCar;
                    } else {
                        inServiceCounter ++;
                        icon = map.getZoom() <= 13 ? iconMarker.busyCarSmall : iconMarker.busyCar;
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
        console.log('clear services');
        if (serviceMarkerCluster !== undefined) {
            serviceMarkerCluster.clearMarkers();
        }
        clearServiceMarkers();
        return;
    }

    var from = new Date();
    /** FIXME: change to 180 min */
    from.setMinutes(from.getMinutes() - 180000);
    from = moment(from).format('YYYY-MM-DD HH:mm:ss');

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
            result = result.data;
            if (xhr.status == 200) {

                clearServiceMarkers();
                var icon = '';
                
                for (var i=0; i < result.length; i++) {
                    console.log(result[i].status);
                    if (result[i].status == -1) {
                        continue;
                    } else if (result[i].status == 1) {
                        icon = map.getZoom() <= 13 ? iconMarker.newServiceSmall : iconMarker.newService;
                    } else if (result[i].status == 2) {
                        icon = map.getZoom() <= 13 ? iconMarker.acceptedServiceSmall : iconMarker.acceptedService;
                    } else if (result[i].status == 3 || result[i].status == 4) {
                        icon = map.getZoom() <= 13 ? iconMarker.startedServiceSmall : iconMarker.startedService;
                    } else {
                        icon = map.getZoom() <= 13 ? iconMarker.finishedServiceSmall : iconMarker.finishedService;
                    }

                    var serviceMarker = new google.maps.Marker({
                        position: {lat: result[i].startPoint.latitude, lng: result[i].startPoint.longitude},
                        map: map,
                        icon: icon
                    });
                    serviceMarkers.push(serviceMarker);

                    (function(marker, service) {
                        marker.addListener('click', function() {
                            createServiceInfoWindow(service);
                        });
                    })(serviceMarker, result[i])
                }

                if (serviceMarkerCluster !== undefined) {
                    serviceMarkerCluster.clearMarkers();
                }

                serviceMarkerCluster = new MarkerClusterer(map, serviceMarkers,
                    {imagePath: '/bundles/funproadmin/map/markerclusterer/images/m'});
            }
        }
    });
}

$(document).ready(function(){
    window.setInterval(showWakeful, 8000);
    window.setInterval(showService, 8000);
});