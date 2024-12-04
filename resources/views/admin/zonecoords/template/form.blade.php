{!! Form::hidden('zone_id', $zone_id) !!}

<div class="form-row">
    <div class="form-group col-6">
        {!! Form::label('latitude', 'Latitud') !!}
        {!! Form::text('latitude', optional($lastCoords)->lat, [
            'class' => 'form-control',
            'placeholder' => 'Latitud',
            'required',
            'readonly',
        ]) !!}
    </div>
    <div class="form-group col-6">
        {!! Form::label('longitude', 'Longitud') !!}
        {!! Form::text('longitude', optional($lastCoords)->lng, [
            'class' => 'form-control',
            'placeholder' => 'Longitud',
            'required',
            'readonly',
        ]) !!}
    </div>
</div>
<div id="map" class="card" style="width: 100%; height:400px;"></div>
<script>
    var latInput = document.getElementById('latitude');
    var lonInput = document.getElementById('longitude');
    var greenPolygons = []; // Almacena los polígonos verdes para validar superposición
    var blueMarkers = []; // Almacena los marcadores azules
    var blueMarkerData = []; // Almacena las coordenadas con zone_id
    var redPolygon = null; // Almacena el polígono rojo dinámico

    // Obtener zone_id desde el campo oculto
    var zoneId = document.querySelector('input[name="zone_id"]').value;

    function initMap() {
        var lat = parseFloat(latInput.value);
        var lng = parseFloat(lonInput.value);

        if (isNaN(lat) || isNaN(lng)) {
            navigator.geolocation.getCurrentPosition(function(position) {
                lat = position.coords.latitude;
                lng = position.coords.longitude;
                latInput.value = lat;
                lonInput.value = lng;
                displayMap(lat, lng);
            });
        } else {
            displayMap(lat, lng);
        }
    }

    function displayMap(lat, lng) {
        var mapOptions = {
            center: { lat: lat, lng: lng },
            zoom: 18,
        };

        var map = new google.maps.Map(document.getElementById('map'), mapOptions);

        // Dibujar el polígono principal en rojo
        var perimeterCoords = @json($vertice);  // Datos de la vista para el polígono rojo
        var perimeterPolygon = new google.maps.Polygon({
            paths: perimeterCoords,
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
        });

        perimeterPolygon.setMap(map);

        // Crear polígonos verdes
        var otherZones = @json($perimeter);  // Datos de las otras zonas (polígonos verdes)
        otherZones.forEach(function(zone) {
            var coords = zone.coords;

            var otherPolygon = new google.maps.Polygon({
                paths: coords,
                strokeColor: '#008000', // Verde
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#008000',
                fillOpacity: 0.35,
            });

            otherPolygon.setMap(map);
            greenPolygons.push(otherPolygon); // Almacenar para validación

            // Calcular el centro del polígono verde
            var bounds = new google.maps.LatLngBounds();
            coords.forEach(function(coord) {
                bounds.extend(coord);
            });

            var center = bounds.getCenter();

            // Crear un marcador con el nombre de la zona en el centro del polígono
            new google.maps.Marker({
                position: center,
                map: map,
                label: {
                    text: zone.name,  // Nombre de la zona
                    color: "black",
                    fontSize: "14px",
                    fontWeight: "bold"
                },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 0  // Hacer invisible el marcador de icono
                }
            });
        });

        // Crear marcador para trazar coordenadas
        var marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
            title: 'Ubicación',
            draggable: true,
        });

        // Validar al mover el marcador
        google.maps.event.addListener(marker, 'dragend', function(event) {
            var latLng = event.latLng;
            if (isPointInsideGreenPolygons(latLng)) {
                Swal.fire({
                    icon: 'error',
                    title: '¡No permitido!',
                    text: 'No puedes trazar una coordenada dentro o sobre un polígono existente.',
                    confirmButtonText: 'Entendido',
                }).then(() => {
                    marker.setPosition({ lat: lat, lng: lng }); // Regresar al punto original
                });
            } else {
                latInput.value = latLng.lat();
                lonInput.value = latLng.lng();
            }
        });

        // Evento para hacer clic en el marcador
        google.maps.event.addListener(marker, 'click', function() {
            Swal.fire({
                title: 'Agregar marcador',
                text: "¿Deseas agregar un marcador azul?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Agregar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Desplazar ligeramente el marcador azul al agregarlo
                    var offsetLat = 0.00005; // Desplazamiento en latitud
                    var offsetLng = 0.00005; // Desplazamiento en longitud
                    var newPosition = {
                        lat: marker.getPosition().lat() + offsetLat,
                        lng: marker.getPosition().lng() + offsetLng,
                    };
                    addBlueMarker(map, newPosition);
                }
            });
        });
    }

    function addBlueMarker(map, position) {
        if (isPointInsideGreenPolygons(new google.maps.LatLng(position))) {
            Swal.fire({
                icon: 'error',
                title: '¡No permitido!',
                text: 'No puedes trazar una coordenada dentro o sobre un polígono existente.',
                confirmButtonText: 'Entendido',
            });
            return;
        }

        var blueMarker = new google.maps.Marker({
            position: position,
            map: map,
            icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
            draggable: true,
        });

        blueMarkers.push(blueMarker);
        blueMarkerData.push({
            latitude: position.lat,
            longitude: position.lng,
            zone_id: zoneId,
        });

        // Mostrar los datos de los marcadores azules en consola cada vez que se agrega uno
        console.log("Datos de blueMarkerData actualizados:", blueMarkerData);

        if (blueMarkers.length > 1) {
            updateRedPolygon(map);
        }

        // Evento para mover los marcadores azules
        google.maps.event.addListener(blueMarker, 'dragend', function() {
            var newPosition = blueMarker.getPosition();

            if (isPointInsideGreenPolygons(newPosition)) {
                Swal.fire({
                    icon: 'error',
                    title: '¡No permitido!',
                    text: 'No puedes mover el marcador dentro o sobre un polígono existente.',
                    confirmButtonText: 'Entendido',
                }).then(() => {
                    // Regresar el marcador a su posición anterior
                    blueMarker.setPosition(position);
                });
            } else {
                // Actualizar las coordenadas
                var index = blueMarkers.indexOf(blueMarker);
                blueMarkerData[index] = {
                    latitude: newPosition.lat(),
                    longitude: newPosition.lng(),
                    zone_id: zoneId,
                };

                // Mostrar los datos de los marcadores azules en consola cada vez que se mueve uno
                console.log("Datos de blueMarkerData actualizados después de mover:", blueMarkerData);

                updateRedPolygon(map);
            }
        });

        // Evento para hacer clic en el marcador azul (eliminarlo)
        google.maps.event.addListener(blueMarker, 'click', function() {
            Swal.fire({
                title: 'Eliminar marcador',
                text: "¿Estás seguro de que deseas eliminar este marcador azul?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Eliminar el marcador azul
                    var index = blueMarkers.indexOf(blueMarker);
                    if (index > -1) {
                        blueMarker.setMap(null); // Quitar marcador del mapa
                        blueMarkers.splice(index, 1); // Eliminarlo del array
                        blueMarkerData.splice(index, 1); // Eliminarlo del array de datos
                    }

                    // Actualizar el polígono rojo
                    updateRedPolygon(map);

                    // Mostrar mensaje de éxito
                    Swal.fire('Eliminado', 'El marcador azul ha sido eliminado.', 'success');
                }
            });
        });
    }

    function updateRedPolygon(map) {
        var blueCoords = blueMarkers.map(function(marker) {
            return marker.getPosition();
        });

        // Eliminar el polígono rojo existente si hay uno
        if (redPolygon) {
            redPolygon.setMap(null);
        }

        // Crear un nuevo polígono rojo
        redPolygon = new google.maps.Polygon({
            paths: blueCoords,
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
        });

        redPolygon.setMap(map);
    }

    function isPointInsideGreenPolygons(point) {
        return greenPolygons.some(function(polygon) {
            return google.maps.geometry.poly.containsLocation(point, polygon);
        });
    }

    // Enviar datos al servidor
    function saveBlueMarkers() {
        console.log("Datos de blueMarkerData antes de enviar:", blueMarkerData);

        if (blueMarkerData.length === 0) {
            console.log("No hay datos para enviar.");
            return;  // Si no hay datos, no continúes con la solicitud
        }

        $.ajax({
            url: "{{ route('admin.zonecoords.store') }}", // Asegúrate de que esta ruta sea correcta
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Asegúrate de que el CSRF token esté presente
            },
            data: JSON.stringify({
                markers: blueMarkerData  // Este debe ser un array con objetos correctos
            }),
            contentType: 'application/json',
            success: function(response) {
                $("#formModal").modal("hide");
                refreshTable();
                Swal.fire('Proceso existoso', response.message, 'success');
            },
            error: function(xhr, status, error) {
                Swal.fire('Error', 'Ocurrió un error al guardar los datos.', 'error');
            }
        });
    }

    $('#zonecorddsave').click(function(event) {
        event.preventDefault();  // Prevenir el envío tradicional del formulario
        saveBlueMarkers();       // Llamar a la función para guardar los datos con AJAX
    });
</script>






<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer>
</script>
