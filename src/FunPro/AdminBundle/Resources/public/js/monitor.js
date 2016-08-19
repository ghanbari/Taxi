var markers = [];
var onlineCounter = 0;
var inServiceCounter = 0;

function setMapOnAll(map) {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(map);
    }
}

function clearMarkers() {
    setMapOnAll(null);
    markers = [];
}

function showMarkers() {
    setMapOnAll(map);
}

function createInfoWindow(wakeful) {
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
    $('#driverName').text(wakeful.car.driver.name);
    $('#driverStatus').text(wakeful.car.statusName);
    $('#driverContractNumber').text(wakeful.car.driver.contractNumber);
    $('#driverMobileNumber').text(wakeful.car.driver.mobile);
    $('#driverNationalCode').text(wakeful.car.driver.nationalCode);
    $('#driverRate').text(wakeful.car.driver.rate);

    $('#carImage').attr('src', carImage);
    $('#carName').text(wakeful.car.name);
    $('#carStatus').text(wakeful.car.statusName);
    $('#carBrand').text(wakeful.car.brand);
    $('#carModel').text(wakeful.car.model);
    $('#carColor').text(wakeful.car.color);
    $('#carColor').css('color', wakeful.car.color);
    $('#carRate').text(wakeful.car.rate);

    $('#driver').modal('toggle');
}

function showWakeful() {
    if (!$('.autoRefresh').hasClass('active')) {
        return;
    }

    $.ajax({
        url: Routing.generate('fun_pro_admin_cget_monitor_wakeful', {'latitude': 36.313048, 'longitude': 59.575237}),
        type: 'GET',
        beforeSend: function(xhr){xhr.setRequestHeader('Accept', 'application/json');},

        success: function(result, status, xhr) {
            if (xhr.status == 200) {
                inServiceCounter = 0;
                onlineCounter = 0;
                clearMarkers();
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
                    markers.push(carMarker);

                    (function(marker, wakeful) {
                        marker.addListener('click', function() {
                            createInfoWindow(wakeful);
                        });
                    })(carMarker, result[i])
                }

                $('.inServiceCounter').attr('data-value', inServiceCounter);
                $('.inServiceCounter').counterUp();

                $('.onlineCounter').attr('data-value', onlineCounter);
                $('.onlineCounter').counterUp();
            }
        }
    });
}

$(document).ready(function(){
    window.setInterval(showWakeful, 8000);
});