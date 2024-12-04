

<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre de la ruta', 'requerid']) !!}
</div>

<div class="form-group">
    {!! Form::label('zone_id', 'Zonas') !!}
    {!! Form::select('zone_id[]', $zones, isset($zone_ids) ? $zone_ids : [], [
        'class' => 'form-control select2',
        'multiple' => 'multiple',
        'data-placeholder' => 'Selecciona una o más zonas',
        'style' => 'width: 100%;',
    ]) !!}
</div>
<div class="form-check">
    {!! Form::hidden('status', 0) !!} <!-- Campo oculto para enviar 0 cuando está desmarcado -->
    {!! Form::checkbox('status', 1, old('status', $route->status ?? 0) == 1, ['class' => 'form-check-input']) !!}
    {!! Form::label('status', 'Activo') !!}
</div>
<div class="form-row">
    <div class="form-group col-6">
        {!! Form::label('latitud_start', 'Latitud Inicio') !!}
        {!! Form::text('latitud_start', optional($route)->latStart, [
            'class' => 'form-control',
            'placeholder' => 'Latitud',
            'required',
            'readonly',
        ]) !!}
    </div>
    <div class="form-group col-6">
        {!! Form::label('longitude_start', 'Longitud Incio') !!}
        {!! Form::text('longitude_start',optional($route)->lngStart, [
            'class' => 'form-control',
            'placeholder' => 'Longitud',
            'required',
            'readonly',
        ]) !!}
    </div>
</div>
<div class="form-row">
    <div class="form-group col-6">
        {!! Form::label('latitude_end', 'Latitud Fin') !!}
        {!! Form::text('latitude_end', optional($route)->latEnd, [
            'class' => 'form-control',
            'placeholder' => 'Latitud',
            'required',
            'readonly',
        ]) !!}
    </div>
    <div class="form-group col-6">
        {!! Form::label('longitude_end', 'Longitud Fin') !!}
        {!! Form::text('longitude_end',optional($route)->lngEnd, [
            'class' => 'form-control',
            'placeholder' => 'Longitud',
            'required',
            'readonly',
        ]) !!}
    </div>
</div>
<div id="map" class="card" style="width: 100%; height:400px;"></div>

<script>
     $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Selecciona una o más zonas',
            allowClear: true
        });
    });
</script>
<script>

    var perimeters = @json($perimeter);

    var routecoords = @json($routecoord);
    var routevertices = @json($routevertice);

    var latStartInput = document.getElementById('latitud_start');
    var lonStartInput = document.getElementById('longitude_start');
    var latEndInput = document.getElementById('latitude_end');
    var lonEndInput = document.getElementById('longitude_end');

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

            @if($isEdit)
                var latStart = {{ $route->latStart }};
                var lngStart = {{ $route->lngStart }};
                var latEnd = {{ $route->latEnd }};
                var lngEnd = {{ $route->lngEnd }};
                initMapWithCoordinates(latStart, lngStart, latEnd, lngEnd, true);
            @else
                // Verifica si existe un registro de ruta (routevertice), si no, no usa esta variable
                if (routevertices && routevertices.latEnd && routevertices.lngEnd) {
                    var latStart  = parseFloat(routevertices.latEnd);  // Coordenada de fin (latitud)
                    var lngStart  = parseFloat(routevertices.lngEnd);  // Coordenada de fin (longitud)

                    // Calcula el marcador verde (latEnd, lngEnd) y el marcador rojo (un poco desplazado)
                    var latEnd = latStart + 0.001;  // Desplazamiento para marcador rojo (un poco al norte)
                    var lngEnd = lngStart + 0.001;  // Desplazamiento para marcador rojo (un poco al este)

                    latStartInput.value = latStart;
                    lonStartInput.value = lngStart;

                    initMapWithCoordinates(latStart, lngStart, latEnd, lngEnd, false);

                }else{
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            var latStart = position.coords.latitude;
                            var lngStart = position.coords.longitude;

                            latStartInput.value = latStart;
                            lonStartInput.value = lngStart;

                            var latEnd = latStart + 0.001;
                            var lngEnd = lngStart + 0.001;

                            latEndInput.value = latEnd;
                            lonEndInput.value = lngEnd;

                            initMapWithCoordinates(latStart, lngStart, latEnd, lngEnd, false);
                        },
                        function (error) {
                            alert('No se pudo obtener la ubicación actual. Verifica los permisos de geolocalización.');
                        }
                    );
                }
            @endif
        
    }

    function initMapWithCoordinates(latStart, lngStart, latEnd, lngEnd, isEdit) {
        var mapOptions = {
            center: { lat: latStart, lng: lngStart },
            zoom: 18,
        };

        var map = new google.maps.Map(document.getElementById('map'), mapOptions);
        var bounds = new google.maps.LatLngBounds();

        // Marcar inicio (verde)
        var startMarker = new google.maps.Marker({
            position: { lat: latStart, lng: lngStart },
            map: map,
            title: 'Inicio',
            icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
            draggable: true,
        });

        // Marcar fin (rojo)
        var endMarker = new google.maps.Marker({
            position: { lat: latEnd, lng: lngEnd },
            map: map,
            title: 'Fin',
            icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
            draggable: true,
        });

        // Actualizar posiciones al arrastrar
        google.maps.event.addListener(startMarker, 'dragend', function (event) {
            latStartInput.value = event.latLng.lat();
            lonStartInput.value = event.latLng.lng();
        });

        google.maps.event.addListener(endMarker, 'dragend', function (event) {
            latEndInput.value = event.latLng.lat();
            lonEndInput.value = event.latLng.lng();
        });

        bounds.extend(new google.maps.LatLng(latStart, lngStart));
        bounds.extend(new google.maps.LatLng(latEnd, lngEnd));

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

        // Verifica si hay registros de rutas
        if (routecoords.length > 0) {
            // Dibuja las líneas entre las coordenadas de cada ruta
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

        if (isEdit) {
            // Dibuja línea roja entre inicio y fin solo en modo edición
            var routeLine = new google.maps.Polyline({
                path: [
                    { lat: latStart, lng: lngStart },
                    { lat: latEnd, lng: lngEnd },
                ],
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 1.0,
                strokeWeight: 3,
            });

            routeLine.setMap(map);
        }

        map.fitBounds(bounds);
    }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer>
</script>

<style>
    .select2-container .select2-selection--multiple {
        background-color: #f0f8ff;
        border-color: #007bff;
        font-size: 14px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        color: #007bff;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        color: #fff;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
    }
</style>
