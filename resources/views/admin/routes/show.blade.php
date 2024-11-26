<div id="map" class="card" style="width: 100%; height:600px";></div>
<script>
    var perimeters = @json($perimeter);
    var routecoords = @json($routecoord);
    var selectedRoute = @json($route); // Ruta seleccionada

    function getConvexHull(points) {
        points.sort((a, b) => a.lat - b.lat || a.lng - b.lng);

        const cross = (o, a, b) => (a.lat - o.lat) * (b.lng - o.lng) - (a.lng - o.lng) * (b.lat - o.lat);

        const lower = [];
        for (const point of points) {
            while (lower.length >= 2 && cross(lower[lower.length - 2], lower[lower.length - 1], point) <= 0) {
                lower.pop();
            }
            lower.push(point);
        }

        const upper = [];
        for (const point of points.reverse()) {
            while (upper.length >= 2 && cross(upper[upper.length - 2], upper[upper.length - 1], point) <= 0) {
                upper.pop();
            }
            upper.push(point);
        }

        upper.pop();
        lower.pop();
        return lower.concat(upper);
    }

    function initMap() {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                var latStart = position.coords.latitude;
                var lngStart = position.coords.longitude;

                var latEnd = latStart + 0.001;
                var lngEnd = lngStart + 0.001;

                initMapWithCoordinates(latStart, lngStart, latEnd, lngEnd);
            },
            function (error) {
                alert('No se pudo obtener la ubicación actual. Verifica los permisos de geolocalización.');
            }
        );
    }

    function initMapWithCoordinates(latStart, lngStart, latEnd, lngEnd) {
        var mapOptions = {
            center: { lat: latStart, lng: lngStart },
            zoom: 18,
        };

        var map = new google.maps.Map(document.getElementById('map'), mapOptions);
        var bounds = new google.maps.LatLngBounds();

        // Dibuja zonas y sectores
        if (perimeters.length > 0) {
            perimeters.forEach(function (perimeter) {
                let sectorCoords = [];

                perimeter.zones.forEach(function (zone) {
                    var zoneCoords = zone.coords.map(coord => new google.maps.LatLng(coord.lat, coord.lng));
                    sectorCoords = sectorCoords.concat(zone.coords);

                    var zonePolygon = new google.maps.Polygon({
                        paths: zoneCoords,
                        strokeColor: '#008000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '#008000',
                        fillOpacity: 0.35,
                        map: map,
                    });

                    var latSum = 0, lngSum = 0;
                    zoneCoords.forEach(coord => {
                        latSum += coord.lat();
                        lngSum += coord.lng();
                    });

                    var zoneCenter = new google.maps.LatLng(latSum / zoneCoords.length, lngSum / zoneCoords.length);

                    new google.maps.Marker({
                        position: zoneCenter,
                        map: map,
                        label: {
                            text: zone.zone,
                            color: '#008000',
                            fontSize: '12px',
                            fontWeight: 'bold',
                        },
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 0,
                        },
                    });

                    zoneCoords.forEach(coord => bounds.extend(coord));
                });

                var sectorHull = getConvexHull(sectorCoords);
                var sectorPolygonCoords = sectorHull.map(coord => new google.maps.LatLng(coord.lat, coord.lng));

                var sectorPolygon = new google.maps.Polygon({
                    paths: sectorPolygonCoords,
                    strokeColor: '#0000FF',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#0000FF',
                    fillOpacity: 0.15,
                    map: map,
                });

                var sectorLatSum = 0, sectorLngSum = 0;
                sectorPolygonCoords.forEach(coord => {
                    sectorLatSum += coord.lat();
                    sectorLngSum += coord.lng();
                });

                var sectorCenter = new google.maps.LatLng(sectorLatSum / sectorPolygonCoords.length, sectorLngSum / sectorPolygonCoords.length);

                new google.maps.Marker({
                    position: sectorCenter,
                    map: map,
                    label: {
                        text: perimeter.sector,
                        color: '#0000FF',
                        fontSize: '16px',
                        fontWeight: 'bold',
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 0,
                    },
                });
            });
        }

        // Dibuja las demás rutas (en rojo)
        if (routecoords.length > 0) {
            routecoords.forEach(function(route) {
                var routeLine = new google.maps.Polyline({
                    path: [
                        { lat: route.latStart, lng: route.lngStart },
                        { lat: route.latEnd, lng: route.lngEnd }
                    ],
                    geodesic: true,
                    strokeColor: '#FF0000', // Rojo
                    strokeOpacity: 1.0,
                    strokeWeight: 3,
                });

                routeLine.setMap(map);
            });
        }

        // Ahora, dibujamos la ruta seleccionada (en verde) al final
        if (selectedRoute && selectedRoute.latStart && selectedRoute.lngStart && selectedRoute.latEnd && selectedRoute.lngEnd) {
            // Marcar inicio y fin de la ruta seleccionada (verde)
            var startMarker = new google.maps.Marker({
                position: { lat: selectedRoute.latStart, lng: selectedRoute.lngStart },
                map: map,
                title: 'Inicio de Ruta Seleccionada',
                icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
            });

            var endMarker = new google.maps.Marker({
                position: { lat: selectedRoute.latEnd, lng: selectedRoute.lngEnd },
                map: map,
                title: 'Fin de Ruta Seleccionada',
                icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
            });

            bounds.extend(new google.maps.LatLng(selectedRoute.latStart, selectedRoute.lngStart));
            bounds.extend(new google.maps.LatLng(selectedRoute.latEnd, selectedRoute.lngEnd));

            // Dibuja la ruta seleccionada (en verde)
            var selectedRouteLine = new google.maps.Polyline({
                path: [
                    { lat: selectedRoute.latStart, lng: selectedRoute.lngStart },
                    { lat: selectedRoute.latEnd, lng: selectedRoute.lngEnd }
                ],
                geodesic: true,
                strokeColor: '#008000', // Verde
                strokeOpacity: 1.0,
                strokeWeight: 3,
            });

            selectedRouteLine.setMap(map);

            // Crear la caja flotante con el nombre de la ruta (corrigiendo la alineación)
            var routeInfoWindow = new google.maps.InfoWindow({
                content: '<div style="font-size: 14px; font-weight: bold; line-height: 1.5; text-align: center;">' + selectedRoute.name + '</div>',
            });

            // Mostrar la caja flotante (InfoWindow) sobre el centro de la ruta seleccionada
            var routeCenter = new google.maps.LatLng(
                (selectedRoute.latStart + selectedRoute.latEnd) / 2,
                (selectedRoute.lngStart + selectedRoute.lngEnd) / 2
            );

            routeInfoWindow.setPosition(routeCenter);
            routeInfoWindow.open(map);
        }

        map.fitBounds(bounds);
    }
</script>



<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer>
</script>
