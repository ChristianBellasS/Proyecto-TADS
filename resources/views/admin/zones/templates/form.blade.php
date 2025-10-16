<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('name', 'Nombre de la Zona *') !!}
            {!! Form::text('name', null, [
                'class' => 'form-control',
                'placeholder' => 'Nombre de la zona',
                'required',
            ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-4">
        <div class="form-group">
            {!! Form::label('department_id', 'Departamento *') !!}
            <select name="department_id" id="department_id" class="form-control" required>
                <option value="">Seleccione departamento</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @if (isset($zone) && $zone->district->province->department->id == $department->id) selected @endif>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            {!! Form::label('province_id', 'Provincia *') !!}
            <select name="province_id" id="province_id" class="form-control" required>
                <option value="">Seleccione provincia</option>
            </select>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            {!! Form::label('district_id', 'Distrito *') !!}
            <select name="district_id" id="district_id" class="form-control" required>
                <option value="">Seleccione distrito</option>
            </select>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control',
        'placeholder' => 'Agregue una descripción de la zona',
        'rows' => 3,
    ]) !!}
</div>

<div class="form-group">
    {!! Form::label('', 'Coordenadas del Perímetro *') !!}


            <!-- Nuevo agregado -->
        <div class="form-group mt-3">
            <label for="addressSearch">Buscar dirección dentro del distrito</label>
            <input type="text" id="addressSearch" class="form-control" placeholder="Escribe una dirección o institución (ej: Av. Los Próceres, SUNAT Miraflores)">
            <div id="addressSuggestions" class="list-group" style="position: absolute; z-index: 9999; width: 100%; display: none;"></div>
        </div>

        <!-- <div class="form-group mt-2">
            <label for="selectedCoords">Coordenadas encontradas</label>
            <input type="text" id="selectedCoords" class="form-control" placeholder="Lat, Lng" readonly>
        </div> -->

         <!-- Final de agregado -->

    <div id="coordinates-container">



        <div class="coordinate-point mb-2">
            <div class="input-group">
                <input type="number" step="any" class="form-control coord-lat" placeholder="Latitud" required>
                <input type="number" step="any" class="form-control coord-lng" placeholder="Longitud" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger remove-coord"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-sm btn-primary" id="add-coordinate">
        <i class="fas fa-plus"></i> Agregar Coordenada
    </button>

    <!-- 🔹 Nuevo botón para limpiar -->
    <button type="button" class="btn btn-sm btn-warning" id="clear-map">
        <i class="fas fa-undo"></i> Limpiar Mapa y Coordenadas
    </button>
    <small class="form-text text-muted">Mínimo 3 coordenadas para definir un perímetro.</small>
</div>

<!-- Mapa interactivo -->
<div class="form-group mt-3">
    {!! Form::label('', 'Mapa interactivo de la zona') !!}
    <div id="zoneMap" style="height: 400px; border: 1px solid #ccc;"></div>
    <small class="form-text text-muted">
        Dibuja o ajusta el perímetro directamente en el mapa.
    </small>
</div>
<div class="form-group">
    {!! Form::label('status', 'Estado') !!}
    {!! Form::select('status', ['1' => 'Activo', '0' => 'Inactivo'], null, [
        'class' => 'form-control',
    ]) !!}
</div>



<!-- ============================================================
 DEPENDENCIAS LEAFLET
============================================================= -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>

<!-- ============================================================
 SCRIPT DEL MAPA Y COORDENADAS
============================================================= -->
<script>
    let map = null;
    let drawnItems = null;
    let currentPolygon = null;
    let drawControl = null;

    function initializeMap() {
        // 🔹 Si el mapa no existe, inicialízalo
        if (!map) {
            map = L.map('zoneMap').setView([-12.0464, -77.0428], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            drawControl = new L.Control.Draw({
                edit: {
                    featureGroup: drawnItems,
                    edit: {
                        selectedPathOptions: {
                            maintainColor: true
                        }
                    }
                },
                draw: {
                    polygon: {
                        allowIntersection: false,
                        drawError: {
                            color: '#e1e100',
                            message: '<strong>Error:</strong> ¡No se permiten intersecciones!'
                        },
                        shapeOptions: {
                            color: '#970098'
                        },
                        showArea: true,
                        metric: true,
                        precision: 5
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    marker: false,
                    circlemarker: false
                }
            });
            map.addControl(drawControl);

            // Cuando se dibuja un nuevo polígono
            map.on(L.Draw.Event.CREATED, function(e) {
                drawnItems.clearLayers();
                const layer = e.layer;
                currentPolygon = layer;

                drawnItems.addLayer(layer);
                updateInputsFromPolygon(layer.getLatLngs()[0]);
            });

            // Cuando se edita un polígono
            map.on(L.Draw.Event.EDITED, function(e) {
                e.layers.eachLayer(function(layer) {
                    currentPolygon = layer;
                    const coords = layer.getLatLngs()[0];
                    updateInputsFromPolygon(coords);
                });
            });

            // 🔹 DIBUJAR POLÍGONO EXISTENTE SI HAY COORDENADAS (PARA EDICIÓN)
            @if (isset($zone) && $zone->coordinates->count() > 0)
                setTimeout(function() {
                    const coords = [];
                    $('.coordinate-point').each(function() {
                        const lat = parseFloat($(this).find('.coord-lat').val());
                        const lng = parseFloat($(this).find('.coord-lng').val());
                        if (!isNaN(lat) && !isNaN(lng)) {
                            coords.push([lat, lng]);
                        }
                    });

                    if (coords.length >= 3) {
                        redrawPolygon(coords);
                        // Ajustar vista del mapa al polígono
                        if (map && currentPolygon) {
                            map.fitBounds(currentPolygon.getBounds());
                        }
                    }
                }, 500);
            @endif
        } else {
            map.invalidateSize();
            // 🔹 REACTIVAR CONTROLES SI EL MAPA YA EXISTE
            if (drawControl) {
                map.removeControl(drawControl);
            }
            drawControl = new L.Control.Draw({
                edit: {
                    featureGroup: drawnItems
                },
                draw: {
                    polygon: true,
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    marker: false,
                    circlemarker: false
                }
            });
            map.addControl(drawControl);
        }
    }

    // 🔹 INICIALIZAR MAPA CUANDO EL MODAL SE MUESTRA
    $('#modal').on('shown.bs.modal', function() {
        setTimeout(initializeMap, 300);
    });

    // 🔹 LIMPIAR MAPA CUANDO EL MODAL SE CIERRA
    $('#modal').on('hidden.bs.modal', function() {
        if (map) {
            map.remove();
            map = null;
            drawnItems = null;
            currentPolygon = null;
            drawControl = null;
        }
    });

    // 🔹 Cuando cambia un input → actualiza el mapa
    $(document).on('input', '.coord-lat, .coord-lng', function() {
        const coords = getCoordsFromInputs();
        redrawPolygon(coords);
    });

    // ============================================================
    // FUNCIONES DE SINCRONIZACIÓN
    // ============================================================
    function updateInputsFromPolygon(latlngs) {
        $('#coordinates-container').empty();
        latlngs.forEach(function(coord) {
            $('#coordinates-container').append(`
            <div class="coordinate-point mb-2">
                <div class="input-group">
                    <input type="number" step="any" class="form-control coord-lat" placeholder="Latitud" value="${coord.lat}" required>
                    <input type="number" step="any" class="form-control coord-lng" placeholder="Longitud" value="${coord.lng}" required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-coord"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `);
        });
    }

    function getCoordsFromInputs() {
        const coords = [];
        $('.coordinate-point').each(function() {
            const lat = parseFloat($(this).find('.coord-lat').val());
            const lng = parseFloat($(this).find('.coord-lng').val());
            if (!isNaN(lat) && !isNaN(lng)) coords.push([lat, lng]);
        });
        return coords;
    }

    function redrawPolygon(coords) {
        if (!map) {
            console.log('Mapa no está inicializado');
            return;
        }

        // 🔹 Elimina el polígono anterior
        if (currentPolygon && drawnItems.hasLayer(currentPolygon)) {
            drawnItems.removeLayer(currentPolygon);
        }

        if (coords.length >= 3) {
            currentPolygon = L.polygon(coords, {
                color: 'blue',
                fillColor: '#3388ff',
                fillOpacity: 0.2,
                weight: 2
            });

            drawnItems.addLayer(currentPolygon);
            map.fitBounds(currentPolygon.getBounds());

            // 🔹 ACTIVAR MODO EDICIÓN (con validación)
            if (currentPolygon && currentPolygon.editing) {
                try {
                    currentPolygon.editing.enable();
                } catch (error) {
                    console.log('No se pudo activar edición:', error);
                    // No es crítico, el polígono se dibujó correctamente
                }
            } else {
                console.log('Polygon editing no disponible');
            }

            console.log('Polígono dibujado con', coords.length, 'coordenadas');
        } else {
            console.log('Coordenadas insuficientes:', coords.length);
        }
    }
</script>

<script>
    $(document).ready(function() {
        // Agregar coordenada
        $('#add-coordinate').click(function() {
            var newCoord = $(`<div class="coordinate-point mb-2">
                <div class="input-group">
                    <input type="number" step="any" class="form-control coord-lat" placeholder="Latitud" required>
                    <input type="number" step="any" class="form-control coord-lng" placeholder="Longitud" required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-coord"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>`);
            $('#coordinates-container').append(newCoord);
        });

        // Eliminar coordenada
        $(document).on('click', '.remove-coord', function() {
            if ($('.coordinate-point').length > 1) {
                $(this).closest('.coordinate-point').remove();
            } else {
                Swal.fire('Error', 'Debe haber al menos una coordenada', 'error');
            }
        });

        // Pre-cargar coordenadas existentes para edición
        @if (isset($zone) && $zone->coordinates->count() > 0)
            $('#coordinates-container').empty();
            @foreach ($zone->coordinates as $coord)
                $('#coordinates-container').append(`<div class="coordinate-point mb-2">
                    <div class="input-group">
                        <input type="number" step="any" class="form-control coord-lat" placeholder="Latitud" value="{{ $coord->latitude }}" required>
                        <input type="number" step="any" class="form-control coord-lng" placeholder="Longitud" value="{{ $coord->longitude }}" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger remove-coord"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>`);
            @endforeach
        @endif


        // --- ADICIÓN MÍNIMA para que Laravel reciba array válido ---
        let districtMarker = null; // 🔹 Define fuera del evento change

        $('#district_id').on('change', async function() {
            const department = $('#department_id option:selected').text();
            const province = $('#province_id option:selected').text();
            const district = $('#district_id option:selected').text();
            const location = `${district}, ${province}, ${department}, Perú`;

            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`;

            try {
                const response = await fetch(url);
                const results = await response.json();

                if (results.length > 0) {
                    const lat = parseFloat(results[0].lat);
                    const lon = parseFloat(results[0].lon);

                    // 🔹 Centrar el mapa
                    map.setView([lat, lon], 13);

                    // 🔹 Eliminar solo el marcador anterior, no todo drawnItems
                    if (districtMarker) {
                        map.removeLayer(districtMarker);
                    }

                    // 🔹 Agregar nuevo marcador sin borrar el featureGroup
                    districtMarker = L.marker([lat, lon])
                        .addTo(map)
                        .bindPopup(`<strong>${district}</strong>`)
                        .openPopup();

                } else {
                    console.log('No se encontró la ubicación');
                }
            } catch (error) {
                console.error('Error al obtener coordenadas:', error);
            }
        });


        ///fin
        // Nuevo de bararra de búsqueda
        let searchTimeout = null;
        let searchMarker = null;

        $('#addressSearch').on('input', function() {
            const query = $(this).val().trim();

            // 🔸 Validar que se haya elegido distrito
            const department = $('#department_id option:selected').text();
            const province = $('#province_id option:selected').text();
            const district = $('#district_id option:selected').text();

            if (!department || !province || !district) {
                $('#addressSuggestions').hide();
                return Swal.fire('Atención', 'Debes seleccionar departamento, provincia y distrito antes de buscar.', 'warning');
            }

            if (query.length < 3) {
                $('#addressSuggestions').hide();
                return;
            }

            /*

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                const location = `${query}, ${district}, ${province}, ${department}, Perú`;
                // const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}&addressdetails=1&limit=5`;
                const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=5&street=${encodeURIComponent(query)}&city=${encodeURIComponent(district)}&county=${encodeURIComponent(province)}&state=${encodeURIComponent(department)}&country=Peru`;

                try {
                    const response = await fetch(url);
                    const results = await response.json();

                    if (results.length > 0) {
                        let suggestionsHtml = '';
                        results.forEach(result => {
                            suggestionsHtml += `
                                <button type="button" class="list-group-item list-group-item-action address-suggestion"
                                    data-lat="${result.lat}" data-lon="${result.lon}">
                                    ${result.display_name}
                                </button>`;
                        });

                        $('#addressSuggestions').html(suggestionsHtml).show();
                    } else {
                        $('#addressSuggestions').hide();
                    }
                } catch (error) {
                    console.error('Error en búsqueda:', error);
                    $('#addressSuggestions').hide();
                }
            }, 400); // Espera 400 ms entre tecleos
            */
           clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(query + ', ' + district + ', ' + province + ', ' + department + ', Peru')}&limit=5`;

                try {
                    const response = await fetch(url);
                    const results = await response.json();

                    if (results.features && results.features.length > 0) {
                        let suggestionsHtml = '';
                        results.features.forEach(result => {
                            const coords = result.geometry.coordinates;
                            const displayName = [
                                result.properties.name || '',
                                result.properties.street || '',
                                result.properties.city || '',
                                result.properties.country || ''
                            ].filter(Boolean).join(', ');

                            suggestionsHtml += `
                                <button type="button" class="list-group-item list-group-item-action address-suggestion"
                                    data-lat="${coords[1]}" data-lon="${coords[0]}">
                                    ${displayName}
                                </button>`;
                        });

                        $('#addressSuggestions').html(suggestionsHtml).show();
                    } else {
                        $('#addressSuggestions').hide();
                    }
                } catch (error) {
                    console.error('Error en búsqueda (Photon):', error);
                    $('#addressSuggestions').hide();
                }
            }, 400);

        });

        // 🔸 Al hacer clic en una sugerencia
        // $(document).on('click', '.address-suggestion', function() {
        //     const lat = parseFloat($(this).data('lat'));
        //     const lon = parseFloat($(this).data('lon'));
        //     const name = $(this).text();

        //     $('#addressSearch').val(name);
        //     $('#addressSuggestions').hide();
        //     $('#selectedCoords').val(`${lat}, ${lon}`);

        //     // 🔹 Centrar el mapa
        //     if (map) {
        //         map.setView([lat, lon], 16);

        //         // 🔹 Eliminar marcador anterior
        //         if (searchMarker) map.removeLayer(searchMarker);

        //         // 🔹 Crear nuevo marcador
        //         searchMarker = L.marker([lat, lon])
        //             .addTo(map)
        //             .bindPopup(`<strong>${name}</strong>`)
        //             .openPopup();
        //     }
        // });
        // 🔸 Al hacer clic en una sugerencia
        /*
        $(document).on('click', '.address-suggestion', function() {
            const lat = parseFloat($(this).data('lat'));
            const lon = parseFloat($(this).data('lon'));
            const name = $(this).text();

            $('#addressSearch').val(name);
            $('#addressSuggestions').hide();
            $('#selectedCoords').val(`${lat}, ${lon}`);

            // 🔹 Centrar el mapa y mostrar marcador
            if (map) {
                map.setView([lat, lon], 16);

                if (searchMarker) map.removeLayer(searchMarker);

                searchMarker = L.marker([lat, lon])
                    .addTo(map)
                    .bindPopup(`<strong>${name}</strong>`)
                    .openPopup();
            }

            // 🧩 AGREGAR AUTOMÁTICAMENTE COORDENADA AL CONTENEDOR
            const coordHtml = `
                <div class="coordinate-point mb-2 highlight-coord">
                    <div class="input-group">
                        <input type="number" step="any" class="form-control coord-lat" placeholder="Latitud" value="${lat}" required>
                        <input type="number" step="any" class="form-control coord-lng" placeholder="Longitud" value="${lon}" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger remove-coord"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;

            $('#coordinates-container').append(coordHtml);

            // 🔹 Actualizar polígono si hay más de 2 coordenadas
            const coords = getCoordsFromInputs();
            redrawPolygon(coords);

            // ✨ Efecto visual para destacar la nueva coordenada
            setTimeout(() => {
                $('.highlight-coord').removeClass('highlight-coord');
            }, 1200);
        });
        */
        $(document).on('click', '.address-suggestion', function() {
            const lat = parseFloat($(this).data('lat'));
            const lon = parseFloat($(this).data('lon'));
            const name = $(this).text();

            $('#addressSearch').val(name);
            $('#addressSuggestions').hide();
            // $('#selectedCoords').val(`${lat}, ${lon}`);

            // ✅ Buscar si hay un coordinate-point vacío
            let emptyCoord = null;
            $('.coordinate-point').each(function() {
                const latVal = $(this).find('.coord-lat').val();
                const lngVal = $(this).find('.coord-lng').val();
                if (!latVal && !lngVal && !emptyCoord) {
                    emptyCoord = $(this);
                }
            });

            // ✅ Si hay uno vacío, úsalo; si no, crea uno nuevo
            if (emptyCoord) {
                emptyCoord.find('.coord-lat').val(lat);
                emptyCoord.find('.coord-lng').val(lon);
            } else {
                const newCoord = $(`
                    <div class="coordinate-point mb-2">
                        <div class="input-group">
                            <input type="number" step="any" class="form-control coord-lat" placeholder="Latitud" value="${lat}" required>
                            <input type="number" step="any" class="form-control coord-lng" placeholder="Longitud" value="${lon}" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-danger remove-coord"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>`);
                $('#coordinates-container').append(newCoord);
            }

            // 🔹 Centrar el mapa y agregar marcador
            if (map) {
                map.setView([lat, lon], 16);
                if (searchMarker) map.removeLayer(searchMarker);
                searchMarker = L.marker([lat, lon])
                    .addTo(map)
                    .bindPopup(`<strong>${name}</strong>`)
                    .openPopup();
            }

            // 🔹 Actualizar el polígono si hay 3 o más coordenadas
            const coords = getCoordsFromInputs();
            if (coords.length >= 3) {
                redrawPolygon(coords);
            }


            // Limpiar la barra de búsqueda addressSearch
            $('#addressSearch').val('');
            $('#addressSuggestions').hide();
        });


        ///

        // 🔸 Ocultar sugerencias si el usuario hace clic fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#addressSearch, #addressSuggestions').length) {
                $('#addressSuggestions').hide();
            }
        });

        $(document).on('click', '.remove-coord', function() {
            if ($('.coordinate-point').length > 1) {
                $(this).closest('.coordinate-point').remove();
                const coords = getCoordsFromInputs();
                redrawPolygon(coords);
            } else {
                Swal.fire('Error', 'Debe haber al menos una coordenada', 'error');
            }
        });

        // Fin de barra de búsqueda

        // Nuevo de botón limpiar
        // 🔹 Botón para limpiar el mapa y las coordenadas
        
        /*
        $('#clear-map').click(function() {
            Swal.fire({
                title: '¿Limpiar todo?',
                text: 'Se eliminarán todas las coordenadas y el polígono actual del mapa.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 🔸 1. Eliminar todas las coordenadas excepto una vacía
                    $('#coordinates-container').empty().append(`
                        <div class="coordinate-point mb-2">
                            <div class="input-group">
                                <input type="number" step="any" class="form-control coord-lat" placeholder="Latitud" required>
                                <input type="number" step="any" class="form-control coord-lng" placeholder="Longitud" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-coord"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    `);

                    // 🔸 2. Eliminar el polígono y marcador del mapa
                    if (drawnItems) drawnItems.clearLayers();
                    currentPolygon = null;

                    if (searchMarker) {
                        map.removeLayer(searchMarker);
                        searchMarker = null;
                    }

                    // 🔸 3. Centrar el mapa a la vista inicial (por ejemplo, Lima)
                    if (map) {
                        map.setView([-12.0464, -77.0428], 12);
                    }

                    $('#coordinates-container .coordinate-point').addClass('cleared');
                    setTimeout(() => $('.coordinate-point').removeClass('cleared'), 700);


                    Swal.fire('Limpio', 'El mapa y las coordenadas fueron reiniciados.', 'success');
                }
            });
        });
        */
       // 🔹 Botón para limpiar el mapa, coordenadas y búsqueda
        $('#clear-map').click(function() {
            Swal.fire({
                title: '¿Limpiar todo?',
                text: 'Se eliminarán todas las coordenadas, el polígono y la búsqueda actual.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {

                    // 🔸 1. Reiniciar coordenadas (deja un input vacío)
                    $('#coordinates-container').empty().append(`
                        <div class="coordinate-point mb-2">
                            <div class="input-group">
                                <input type="number" step="any" class="form-control coord-lat" placeholder="Latitud" required>
                                <input type="number" step="any" class="form-control coord-lng" placeholder="Longitud" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-coord"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    `);

                    // 🔸 2. Borrar polígono y marcador del mapa
                    if (drawnItems) drawnItems.clearLayers();
                    currentPolygon = null;

                    if (searchMarker) {
                        map.removeLayer(searchMarker);
                        searchMarker = null;
                    }

                    // 🔸 3. Centrar el mapa a vista inicial (ajusta si quieres otra ubicación)
                    if (map) {
                        map.setView([-12.0464, -77.0428], 12);
                    }

                    // 🔸 4. Limpiar búsqueda de dirección
                    $('#addressSearch').val('');
                    $('#addressSuggestions').hide();

                    // 🔸 5. Animación visual opcional
                    $('#coordinates-container .coordinate-point').addClass('cleared');
                    setTimeout(() => $('.coordinate-point').removeClass('cleared'), 700);

                    Swal.fire('Limpio', 'El mapa, coordenadas y búsqueda fueron reiniciados.', 'success');
                }
            });
        });


        // FIn de limpiar
        




    });

    // Mapa interactivo con Leaflet.js
</script>

<style>
#addressSuggestions .list-group-item {
    cursor: pointer;
}
#addressSuggestions {
    max-height: 200px;
    overflow-y: auto;
}

.highlight-coord {
    animation: flash 1.2s ease-in-out;
}
@keyframes flash {
    0% { background-color: #ffff99; }
    100% { background-color: transparent; }
}

.coordinate-point {
    transition: all 0.2s ease-in-out;
}
.cleared {
    animation: fadeReset 0.5s ease-in-out;
}
@keyframes fadeReset {
    0% { background-color: #fff3cd; }
    100% { background-color: transparent; }
}

</style>
