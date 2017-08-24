function initMap() {
    var styledMapType = new google.maps.StyledMapType(
        [
            {
                "featureType": "landscape.natural",
                "stylers": [
                    {
                        "color": "#ffe6d1"
                    },
                    {
                        "hue": "#ffa200"
                    },
                    {
                        "saturation": -40
                    }
                ]
            },
            {
                "featureType": "road.highway",
                "stylers": [
                    {
                        "color": "#f99041"
                    },
                    {
                        "weight": 1.4
                    }
                ]
            },
            {
                "featureType": "road.highway",
                "elementType": "labels.text",
                "stylers": [
                    {
                        "color": "#321610"
                    },
                    {
                        "weight": 1
                    }
                ]
            },
            {
                "featureType": "road.local",
                "stylers": [
                    {
                        "color": "#efcfca"
                    }
                ]
            },
            {
                "featureType": "road.local",
                "elementType": "labels.text",
                "stylers": [
                    {
                        "color": "#365b26"
                    },
                    {
                        "weight": 1
                    }
                ]
            },
            {
                "featureType": "transit.station.rail",
                "elementType": "labels.icon",
                "stylers": [
                    {
                        "hue": "#ff0008"
                    },
                    {
                        "weight": 0.1
                    }
                ]
            }
        ],
        {name: 'Styled Map'}
    );

    var city = $('.controls-button .city .btn.active input');

    directionsService = new google.maps.DirectionsService;
    directionsDisplay = new google.maps.DirectionsRenderer;
    map = new google.maps.Map(document.getElementById('gmap_basic'), {
        center: {lat: city.data('lat'), lng: city.data('lng')},
        zoom: 12,
        mapTypeIds: ['roadmap', 'satellite', 'hybrid', 'terrain',
            'styled_map']
    });
    getCost(city.data('lng'), city.data('lat'));

    map.mapTypes.set('styled_map', styledMapType);
    map.setMapTypeId('styled_map');

    $('.controls-button .city .btn').click(function (event) {
        var lat = $(this).find('input').data('lat');
        var lng = $(this).find('input').data('lng');
        map.setCenter({lat: lat, lng: lng});
        getCost(lng, lat);
    });
}

function getCost(lng, lat) {
    $.ajax({
        url: Routing.generate('fun_pro_financial_cost_api_get_cost', {'latitude': lat, 'longitude': lng}),
        type: 'get',
        headers: {accept: 'application/json'},
        success: function (response) {
            if (supportedArea !== undefined) {
                supportedArea.setMap(null);
            }
            supportedArea = new google.maps.Circle({
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.07,
                map: map,
                center: new google.maps.LatLng({lat: response.location.latitude, lng: response.location.longitude}),
                radius: response.locationRadius
            });
        }
    });
}