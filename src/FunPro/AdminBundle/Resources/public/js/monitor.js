var carMarkers = [];
var serviceMarkers = [];
var carMarkerCluster;
var serviceMarkerCluster;
var onlineCounter = 0;
var inServiceCounter = 0;
var wakefulInterval;
var serviceInterval;
var carId;

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
    if (carMarkerCluster !== undefined) {
        carMarkerCluster.clearMarkers();
    }
}

function clearServiceMarkers() {
    setMapOnServiceMarkers(null);
    serviceMarkers = [];
    if (serviceMarkerCluster !== undefined) {
        serviceMarkerCluster.clearMarkers();
    }
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
    $('#driverProfile').attr('href', Routing.generate('fun_pro_admin_cget_driver', {nationalCode: wakeful.car.driver.nationalCode}));
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

    var passenger = service.passenger;

    $('#passengerMobileNumber').text(passenger.mobile);
    $('#passengerRate').text(passenger.rate);
    $('#passengerName').text(passenger.name);

    $('#serviceStartAddress').text(service.startAddress);
    $('#serviceStartAddress').parent().data('lat', service.startPoint.latitude);
    $('#serviceStartAddress').parent().data('lng', service.startPoint.longitude);

    $('#serviceEndAddress').text(service.endAddress);
    $('#serviceEndAddress').parent().data('lat', service.endPoint.latitude);
    $('#serviceEndAddress').parent().data('lng', service.endPoint.longitude);

    $('#serviceStatus').text(service.status);
    $('#serviceDistance').text(service.distance);
    $('#servicePrice').text(service.price);

    var timestamp = moment(service.createdAt).unix();
    var date = persianDate.unix(timestamp);
    $('#serviceDate').text(date.pDate.year + '/' + date.pDate.month + '/' + date.pDate.date + ' '  + moment(service.createdAt).format('HH:mm:ss'));
    $('#serviceDiscountedPrice').text(service.discountedPrice);

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

        var getPlaque = function (plaque) {
            return plaque.cityNumber + ' ' + plaque.secondNumber + ' ' +
                plaque.areaCode + ' ' + plaque.firstNumber;
        };

        $('#serviceCarType').text(car.type);
        $('#serviceCarType').data('id', car.id);
        $('#serviceCarPlaque').text(getPlaque(car.plaque));

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
    // if (!$('#showCars').hasClass('active')) {
    //     console.log('clear cars');
    //     if (carMarkerCluster !== undefined) {
    //         carMarkerCluster.clearMarkers();
    //     }
    //     clearCarMarkers();
    //     return;
    // }

    var params = {'latitude': map.getCenter().lat(), 'longitude': map.getCenter().lng(), limit: 5000};
    if (carId) {
        params['cars'] = [carId];
    }
    $.ajax({
        url: Routing.generate('fun_pro_admin_cget_monitor_wakeful', params),
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

                if ($('#clusterMarker').hasClass('active')) {
                    carMarkerCluster = new MarkerClusterer(map, carMarkers,
                        {imagePath: '/bundles/funproadmin/map/markerclusterer/images/m'});
                }

                $('.inServiceCounter').attr('data-value', inServiceCounter);
                $('.inServiceCounter').counterUp();

                $('.onlineCounter').attr('data-value', onlineCounter);
                $('.onlineCounter').counterUp();
            }
        }
    });
}

function showService() {
    // if (!$('#showService').hasClass('active')) {
    //     console.log('clear services');
    //     if (serviceMarkerCluster !== undefined) {
    //         serviceMarkerCluster.clearMarkers();
    //     }
    //     clearServiceMarkers();
    //     return;
    // }

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
                    if (result[i].statusNumber == -1) {
                        continue;
                    } else if (result[i].statusNumber == 1) {
                        icon = map.getZoom() <= 13 ? iconMarker.newServiceSmall : iconMarker.newService;
                    } else if (result[i].statusNumber == 2) {
                        icon = map.getZoom() <= 13 ? iconMarker.acceptedServiceSmall : iconMarker.acceptedService;
                    } else if (result[i].statusNumber == 3 || result[i].statusNumber == 4) {
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

                if ($('#clusterMarker').hasClass('active')) {
                    serviceMarkerCluster = new MarkerClusterer(map, serviceMarkers,
                        {imagePath: '/bundles/funproadmin/map/markerclusterer/images/m'});
                }
            }
        }
    });
}

function stopAndClearMapService() {
    stopWakefulService();
    stopServicesService();
}

function stopWakefulService() {
    $('#showCars').removeClass('active');
    clearInterval(wakefulInterval);
    clearCarMarkers();
    wakefulInterval = null;
    carId = null;
}

function stopServicesService() {
    $('#showService').removeClass('active')
    clearInterval(serviceInterval);
    clearServiceMarkers();
    serviceInterval = null;
}

function startWakefulService() {
    wakefulInterval = window.setInterval(showWakeful, 8000);
}

function toggleWakefulService() {
    if (!wakefulInterval) {
        startWakefulService();
    } else {
        stopWakefulService();
    }
}

function startServicesService() {
    serviceInterval = window.setInterval(showService, 8000);
}

function toggleServicesService() {
    if (!serviceInterval) {
        startServicesService();
    } else {
        stopServicesService();
    }
}

function start() {
    if ($('#showCars').hasClass('active')) {
        startWakefulService();
    }

    if ($('#showService').hasClass('active')) {
        startServicesService();
    }
}

$('#showCars').click(function (event) {
    toggleWakefulService();
});

$('#showService').click(function (event) {
    toggleServicesService();
});

$(document).ready(function(){
    start();

    $('#showEndLocation, #showStartLocation').click(function (event) {
        var origin = new google.maps.LatLng($('#showStartLocation').data('lat'), $('#showStartLocation').data('lng'));
        var destination = new google.maps.LatLng($('#showEndLocation').data('lat'), $('#showEndLocation').data('lng'));

        directionsDisplay.setMap(map);
        directionsService.route({
            origin: origin,
            destination: destination,
            travelMode: 'DRIVING',
            unitSystem: google.maps.UnitSystem.METRIC,
            provideRouteAlternatives: false
        }, function(response, status) {
            if (status === 'OK') {
                stopAndClearMapService();
                directionsDisplay.setDirections(response);
                carId = $('#serviceCarType').data('id');
                startWakefulService();
            } else {
                toastr.error('ادرسی یافت نشد: ' + status);
            }
        });
    });
});