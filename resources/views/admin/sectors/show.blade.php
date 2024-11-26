<div id="map" class="card" style="width: 100%; height:600px";></div>

<script>
    var perimeters = @json($perimeter);

    function initMap() {
        navigator.geolocation.getCurrentPosition(function(position) {
            var userLat = position.coords.latitude;
            var userLng = position.coords.longitude;

            var mapOptions = {
                center: {
                    lat: userLat,
                    lng: userLng
                },
                zoom: 18
            };

            var map = new google.maps.Map(document.getElementById('map'), mapOptions);
            var bounds = new google.maps.LatLngBounds(); // Objeto para ajustar los límites del mapa

            var colors = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF'];

            // Verifica si hay perímetros
            if (perimeters.length > 0) {
                perimeters.forEach(function(perimeter, index) {
                    console.log(perimeter);
                    var perimeterCoords = perimeter.coords.map(coord => new google.maps.LatLng(coord
                        .lat, coord.lng));
                    var color = colors[index % colors
                    .length]; // Obtiene un color de la matriz de colores

                    // Crea un objeto de polígono con los puntos del perímetro
                    var perimeterPolygon = new google.maps.Polygon({
                        paths: perimeterCoords,
                        strokeColor: color,
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: color,
                        fillOpacity: 0.35,
                        map: map // Asigna el mapa al polígono para mostrarlo
                    });

                    // Extiende los límites del mapa para incluir el polígono
                    perimeterCoords.forEach(function(coord) {
                        bounds.extend(coord);
                    });
                });

                // Ajusta el mapa a los límites de los polígonos
                map.fitBounds(bounds);
            } else {
                // Si no hay polígonos, centra el mapa en la ubicación del usuario
                map.setCenter({
                    lat: userLat,
                    lng: userLng
                });
                map.setZoom(18);
            }
        }, function(error) {
            console.error("Error al obtener la ubicación del usuario:", error);
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer>
</script>
