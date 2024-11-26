<div id="map" class="card" style="width: 100%; height:600px";></div>
<script>
    function initMap() {
        var perimeterCoords = @json($vertice)

        if (Object.keys(perimeterCoords).length === 0) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                var mapOptions = {
                    center: {
                        lat: lat,
                        lng: lng
                    },
                    zoom: 18
                };
                var map = new google.maps.Map(document.getElementById('map'), mapOptions);

            })
        } else {
            var mapOptions = {
                zoom: 18
            }
            var map = new google.maps.Map(document.getElementById('map'), mapOptions);

            //var perimeterCoords = @json($vertice);
            // Crea un objeto de polígono con los puntos del perímetro
            var perimeterPolygon = new google.maps.Polygon({
                paths: perimeterCoords,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35
            });

            perimeterPolygon.setMap(map);

            var bounds = new google.maps.LatLngBounds();

            // Obtener los límites (bounds) del polígono
            perimeterPolygon.getPath().forEach(function(coord) {
                bounds.extend(coord);
            });

            // Obtener el centro de los límites (bounds)
            var centro = bounds.getCenter();

            // Mover el mapa para centrarse en el centro del perímetro
            map.panTo(centro);

            //});
        }
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer>
</script>
