function initMap() {
    var styledMapType = new google.maps.StyledMapType(
        [
            {
                "featureType": "landscape.natural",
                "stylers": [
                    { "color": "#ffe6d1" },
                    { "hue": "#ffa200" },
                    { "saturation": -40 }
                ]
            },{
            "featureType": "road.highway",
            "stylers": [
                { "color": "#f99041" },
                { "weight": 1.4 }
            ]
        },{
            "featureType": "road.local",
            "stylers": [
                { "visibility": "on" },
                { "color": "#efcfca" }
            ]
        },{
            "featureType": "transit.station.rail",
            "elementType": "labels.icon",
            "stylers": [
                { "weight": 0.1 },
                { "hue": "#ff0008" }
            ]
        }
        ],
        {name: 'Styled Map'}
    );
    map = new google.maps.Map(document.getElementById('gmap_basic'), {
        center: {lat: 36.313048, lng: 59.575237},
        zoom: 12,
        mapTypeIds: ['roadmap', 'satellite', 'hybrid', 'terrain',
            'styled_map']
    });

    map.mapTypes.set('styled_map', styledMapType);
    map.setMapTypeId('styled_map');
}