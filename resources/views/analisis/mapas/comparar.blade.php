<x-app-layout>

    <nav class="flex mb-3" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg aria-hidden="true" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                        </path>
                    </svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('mapas.index') }}"
                        class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">Mapas
                        de Calor</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Visualizando</span>
                </div>
            </li>
        </ol>
    </nav>
    <div class="mt-2">
        <h1 class="text-xl mb-2 font-semibold text-gray-900 sm:text-2xl dark:text-white">
            Mapa de Calor
        </h1>
        <div class="items-center justify-between block sm:flex pb-4">

            <div class="flex items-center ml-auto space-x-2 sm:space-x-3">
                <button type="button" data-refresh onclick="window.location.href = '{{ route('mapas.index') }}'"
                    class="inline-flex items-center justify-center w-1/2 px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 sm:w-auto dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Volver
                </button>

            </div>
        </div>
        <div class="flex">
            {{-- Map 1 --}}
            <div class="w-1/2 p-4">
                <h2>Map 1: {{ $mapa1->name }}</h2>
                <div id="map1" style="width: 100%; height: 500px;"></div>
                <textarea id="countsTextArea1" rows="10" cols="30"
                style="width: 100%; height: 410px;"></textarea>
            </div>
        
            {{-- Map 2 --}}
            <div class="w-1/2 p-4">
                <h2>Map 2: {{ $mapa2->name }}</h2>
                <div id="map2" style="width: 100%; height: 500px;"></div>
                <textarea id="countsTextArea2" rows="10" cols="30"
                style="width: 100%; height: 410px;"></textarea>
            </div>
        </div>
    </div>

    <x-views>
        Vistas: {{ getPageViews('mapas_ver') }}
    </x-views>

    @push('scripts')
        <script>
            let map, heatmap;

            window.onload = function() {
                initMap1();
                initMap2();
            }

            function buscarUbi() {
                const input = document.getElementById("search-input");
                searchLocation(input.value);
            }

            function initMap1() {
                var lati = {!! $mapa1->latitud !!}
                var lngi = {!! $mapa1->longitud !!}
                var x = 1;
                coordinates1 = {!! json_encode($puntos1) !!};
                map = new google.maps.Map(document.getElementById("map1"), {
                    zoom: 13,
                    center: {
                        lat: lati,
                        lng: lngi
                    },
                    mapTypeId: "satellite",
                });
                loadGeoJson(map);

                map.data.setStyle(function(feature) {
                    var districtName = feature.getProperty('district');
                    var color = getDistrictColor(districtName);

                    return {
                        fillColor: 'transparent',
                        strokeColor: color,
                        strokeWeight: 5,
                    };
                });

                heatmap = new google.maps.visualization.HeatmapLayer({
                    data: getPoints(coordinates1),
                    map: map,
                });

                heatmap.set("radius", heatmap.get("radius") ? null : 20);

                // Use setInterval to check if all features are loaded and trigger calculation
                var interval = setInterval(function () {
                    var loadedFeatures = 0;
                    map.data.forEach(function () {
                        loadedFeatures++;
                    });

                    if (loadedFeatures === 15) {
                        clearInterval(interval);
                        calculateAndDisplayCounts(x,coordinates1);
                    }
                }, 50);

            }

            function initMap2() {
                var lati = {!! $mapa2->latitud !!}
                var lngi = {!! $mapa2->longitud !!}
                var x = 2;
                coordinates2 = {!! json_encode($puntos2) !!};
                map2 = new google.maps.Map(document.getElementById("map2"), {
                    zoom: 13,
                    center: {
                        lat: lati,
                        lng: lngi
                    },
                    mapTypeId: "satellite",
                });
                loadGeoJson(map2);

                map2.data.setStyle(function(feature) {
                    var districtName = feature.getProperty('district');
                    var color = getDistrictColor(districtName);

                    return {
                        fillColor: 'transparent',
                        strokeColor: color,
                        strokeWeight: 5,
                    };
                });

                heatmap = new google.maps.visualization.HeatmapLayer({
                    data: getPoints(coordinates2),
                    map: map2,
                });

                heatmap.set("radius", heatmap.get("radius") ? null : 20);

                // Use setInterval to check if all features are loaded and trigger calculation
                var interval = setInterval(function () {
                    var loadedFeatures = 0;
                    map2.data.forEach(function () {
                        loadedFeatures++;
                    });

                    if (loadedFeatures === 15) {
                        clearInterval(interval);
                        calculateAndDisplayCounts(x, coordinates2);
                    }
                }, 100);

            }

            function loadGeoJson(map) {
                // Load GeoJSON file
                map.data.loadGeoJson('/js/analisis/mapas/map.geojson');
            }

            function getDistrictColor(districtName) {
                // Assign different colors based on district name
                switch (districtName) {
                    case "Distrito 1":
                        return 'red';
                    case "Distrito 2":
                        return 'blue';
                    case "Distrito 3":
                        return 'yellow';
                    case "Distrito 4":
                        return 'green';
                    case "Distrito 5":
                        return 'pink';
                    case "Distrito 6":
                        return 'black';
                    case "Distrito 7":
                        return 'white';
                    case "Distrito 8":
                        return 'purple';
                    case "Distrito 9":
                        return 'orange';
                    case "Distrito 10":
                        return 'brown';
                    case "Distrito 11":
                        return 'gray';
                    case "Distrito 12":
                        return 'charcoal';
                    case "Distrito 13":
                        return 'cyan';
                    case "Distrito 14":
                        return 'teal';
                    case "Distrito 15":
                        return 'coral';

                    default:
                        return 'gray';
                }
            }

            function getPoints(coordinates) {
                var points = [];
                for (var i = 0; i < coordinates.length; i++) {
                    var latLng = new google.maps.LatLng(coordinates[i].latitud, coordinates[i].longitud);
                    points.push(latLng);
                }
                return points;
            }

            function searchLocation(location) {
                if (location.trim() === "") {
                    return;
                }

                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    address: location
                }, (results, status) => {
                    if (status === "OK" && results.length > 0) {
                        // Limpiar marcadores existentes y dibujos
                        heatmap.setMap(null);
                        clearDrawings();

                        // Mostrar el nuevo lugar en el mapa
                        map.setCenter(results[0].geometry.location);
                        heatmap = new google.maps.visualization.HeatmapLayer({
                            data: [results[0].geometry.location],
                            map: map,
                        });
                        heatmap.set("radius", heatmap.get("radius") ? null : 20);

                        // Dibujar el área buscada con líneas segmentadas
                        const bounds = results[0].geometry.viewport;
                        const rectangle = new google.maps.Rectangle({
                            bounds: bounds,
                            strokeColor: "#000000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#000000",
                            fillOpacity: 0,
                            map: map,
                            clickable: false,
                            strokeDashArray: "10 5" // Líneas segmentadas
                        });
                        drawings.push(rectangle);

                        // Cargar los puntos nuevamente
                        points = getPoints();
                        heatmap.setData(points);

                    } else {
                        alert("No se pudo encontrar la ubicación.");
                    }
                });
            }

            function clearDrawings() {
                for (let i = 0; i < drawings.length; i++) {
                    drawings[i].setMap(null);
                }
                drawings = [];
            }

            function calculateAndDisplayCounts(x, coordinates) {
                var counts = {};
                map.data.forEach(function (feature) {
                    console.log('hi');
                    var districtName = feature.getProperty('district');
                    var pointsInDistrict = countPointsInDistrict(feature, coordinates);
                    counts[districtName] = pointsInDistrict;    
                });
                updateTextArea(counts,x);
            }

            
            
            function countPointsInDistrict(feature, coordinates) {
                var districtName = feature.getProperty('district');
                console.log(districtName);
                var pointsInDistrict = 0;

                var geometry  = feature.getGeometry();
                var polygonCoords = geometry.getArray()[0].getArray();
                var newPolygon = new google.maps.Polygon({
                    paths: polygonCoords,
                });

                for (var i = 0; i < coordinates.length; i++) {
                    var latLng = new google.maps.LatLng(coordinates[i].latitud, coordinates[i].longitud);
                    if (google.maps.geometry.poly.containsLocation(latLng, newPolygon)) {
                        pointsInDistrict++;
                    }
                }
                console.log(pointsInDistrict);
                return pointsInDistrict;
            }

            function updateTextArea(counts,x) {
                var text = '';
                console.log(x);
                var totalCases = 0;

                // Display counts in the text area
                for (var districtName in counts) {
                    text += districtName + ': ' + counts[districtName] + '\n';
                    totalCases += counts[districtName];
                }
                text += 'Numero de Casos: ' + totalCases;
                
                if(x == 1){
                    var textArea = document.getElementById('countsTextArea1');
                    textArea.value = text;
                }
                if(x == 2){
                    var textArea = document.getElementById('countsTextArea2');
                    textArea.value = text;
                }
                
            }

            let drawings = [];
            
        </script>
    @endpush
</x-app-layout>
