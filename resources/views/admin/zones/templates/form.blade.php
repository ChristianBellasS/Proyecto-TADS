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

<div class="row">
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('average_waste', 'Residuos Promedio (kg)') !!}
            {!! Form::number('average_waste', isset($zone) ? $zone->average_waste : null, [
                'class' => 'form-control',
                'placeholder' => 'Ej: 150.50',
                'step' => '0.01',
                'min' => '0',
            ]) !!}
            <small class="form-text text-muted">Cantidad promedio de residuos en kilogramos por día</small>
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('status', 'Estado') !!}
            {!! Form::select('status', ['1' => 'Activo', '0' => 'Inactivo'], null, [
                'class' => 'form-control',
            ]) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('', 'Coordenadas del Perímetro *') !!}


    <!-- Nuevo agregado -->
    <div class="form-group mt-3">
        <label for="addressSearch">Buscar dirección dentro del distrito</label>
        <input type="text" id="addressSearch" class="form-control"
            placeholder="Escribe una dirección o institución (ej: Av. Los Próceres, SUNAT Miraflores)">
        <div id="addressSuggestions" class="list-group"
            style="position: absolute; z-index: 9999; width: 100%; display: none;"></div>
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






<script>
    // let map = null;
    // let drawnItems = null;
    // let currentPolygon = null;
    // let drawControl = null;
    window.map = null;
    window.drawnItems = null;
    window.currentPolygon = null;
    window.drawControl = null;
    window.originalPolygonCoords = null;
    window.existingZones = null;
    window.currentZoneId = null;



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
            /*
            map.on(L.Draw.Event.CREATED, function(e) {
                drawnItems.clearLayers();
                const layer = e.layer;
                currentPolygon = layer;

                drawnItems.addLayer(layer);
                updateInputsFromPolygon(layer.getLatLngs()[0]);
            });
            */
            /*
            map.on(L.Draw.Event.CREATED, function(e) {
             
                 const layer = e.layer;
                 const newPolygon = layer.toGeoJSON();

                 // 🧩 Verificar superposición con zonas existentes usando turf.js
                 let intersects = false;

                 if (existingZones && existingZones.length > 0) {
                     existingZones.forEach(zone => {
                         if (zone.coords && zone.coords.length >= 3) {
                             // turf espera [ [lng, lat], ... ] dentro de un array exterior
                             const existingCoordsLonLat = zone.coords.map(coord => [coord[1], coord[0]]);
                             const existingPolygon = turf.polygon([existingCoordsLonLat]);
                             const newPolyTurf = turf.polygon([newPolygon.geometry.coordinates[0]]); // ya en [lng, lat]

                             try {
                                 if (turf.booleanIntersects(existingPolygon, newPolyTurf) || turf.booleanOverlap(existingPolygon, newPolyTurf)) {
                                     intersects = true;
                                 }
                             } catch (err) {
                                 console.error('Error turf booleanIntersects:', err);
                             }
                         }
                     });
                 }

                 if (intersects) {
                     Swal.fire('Error', 'La nueva zona se superpone con una ya existente. Por favor, dibuja en otra área.', 'error');
                     return; // No agregar el polígono
                 }

                 // Si llegó hasta aquí, no hay intersección → continuar
                 drawnItems.clearLayers();
                 currentPolygon = layer;
                 drawnItems.addLayer(layer);
                 updateInputsFromPolygon(layer.getLatLngs()[0]);
                 
                // 🔹 Código corregido sin validación de intersección
                /*
                // Convertir a formato [lng, lat]
                 let existingCoordsLonLat = zone.coords.map(coord => [coord[1], coord[0]]);

                 // 🔹 Cerrar el polígono si no está cerrado
                 if (
                     existingCoordsLonLat.length > 0 &&
                     (existingCoordsLonLat[0][0] !== existingCoordsLonLat.at(-1)[0] ||
                     existingCoordsLonLat[0][1] !== existingCoordsLonLat.at(-1)[1])
                 ) {
                     existingCoordsLonLat.push(existingCoordsLonLat[0]);
                 }

                 let newCoords = newPolygon.geometry.coordinates[0];
                 // 🔹 Cerrar también el nuevo polígono dibujado
                 if (
                     newCoords.length > 0 &&
                     (newCoords[0][0] !== newCoords.at(-1)[0] ||
                     newCoords[0][1] !== newCoords.at(-1)[1])
                 ) {
                     newCoords.push(newCoords[0]);
                 }

                 // Crear polígonos Turf válidos
                 const existingPolygon = turf.polygon([existingCoordsLonLat]);
                 const newPoly
                 */
            ///

            //});
            map.on(L.Draw.Event.CREATED, function(e) {
                const layer = e.layer;
                const newPolygon = layer.toGeoJSON();

                // 🧩 Verificar superposición con zonas existentes usando turf.js
                let intersects = false;

                if (existingZones && existingZones.length > 0) {
                    existingZones.forEach(zone => {
                        // 🔸 Omitir la zona actual en edición
                        if (currentZoneId && zone.id === currentZoneId) return;

                        if (zone.coords && zone.coords.length >= 3) {
                            // 🔹 Convertir a [lng, lat]
                            const existingCoordsLonLat = zone.coords.map(coord => [coord[1], coord[0]]);

                            // 🔹 Cerrar el polígono si no está cerrado
                            if (
                                existingCoordsLonLat.length > 0 &&
                                (
                                    existingCoordsLonLat[0][0] !== existingCoordsLonLat.at(-1)[0] ||
                                    existingCoordsLonLat[0][1] !== existingCoordsLonLat.at(-1)[1]
                                )
                            ) {
                                existingCoordsLonLat.push(existingCoordsLonLat[0]);
                            }

                            // 🔹 También cerrar el nuevo polígono dibujado
                            const newCoords = newPolygon.geometry.coordinates[0];
                            if (
                                newCoords.length > 0 &&
                                (
                                    newCoords[0][0] !== newCoords.at(-1)[0] ||
                                    newCoords[0][1] !== newCoords.at(-1)[1]
                                )
                            ) {
                                newCoords.push(newCoords[0]);
                            }

                            // // 🔹 Crear polígonos Turf válidos
                            // const existingPolygon = turf.polygon([existingCoordsLonLat]);
                            // const newPolyTurf = turf.polygon([newCoords]);

                            try {

                                // 🔹 Crear polígonos Turf válidos
                                const existingPolygon = turf.polygon([existingCoordsLonLat]);
                                const newPolyTurf = turf.polygon([newCoords]);

                                if (
                                    turf.booleanIntersects(existingPolygon, newPolyTurf) ||
                                    turf.booleanOverlap(existingPolygon, newPolyTurf)
                                ) {
                                    intersects = true;
                                }
                            } catch (err) {
                                console.error('Error turf booleanIntersects:', err);
                            }
                        }
                    });
                }

                if (intersects) {
                    Swal.fire(
                        'Error',
                        'La nueva zona se superpone con una ya existente. Por favor, dibuja en otra área.',
                        'error'
                    );
                    return;
                }

                // Si no hay intersección, agregar al mapa
                drawnItems.clearLayers();
                currentPolygon = layer;
                drawnItems.addLayer(layer);
                updateInputsFromPolygon(layer.getLatLngs()[0]);
            });

            // Cuando se edita un polígono
            /*
            map.on(L.Draw.Event.EDITED, function(e) {
                e.layers.eachLayer(function(layer) {
                    currentPolygon = layer;
                    const coords = layer.getLatLngs()[0];
                    updateInputsFromPolygon(coords);
                });
            });
            */
            // 🧩 Cuando se edita un polígono existente
            map.on(L.Draw.Event.EDITED, function(e) {
                e.layers.eachLayer(function(layer) {
                    currentPolygon = layer;
                    const editedPolygon = layer.toGeoJSON();

                    let intersects = false;

                    // 🔹 Validar contra todas las zonas existentes (excepto la actual)
                    if (existingZones && existingZones.length > 0) {
                        existingZones.forEach(zone => {
                            if (currentZoneId && zone.id === currentZoneId)
                                return; // evitar comparar consigo misma

                            if (zone.coords && zone.coords.length >= 3) {
                                const existingCoords = zone.coords.map(c => [c[1], c[0]]);

                                // cerrar anillo si hace falta
                                if (
                                    existingCoords[0][0] !== existingCoords.at(-1)[0] ||
                                    existingCoords[0][1] !== existingCoords.at(-1)[1]
                                ) {
                                    existingCoords.push(existingCoords[0]);
                                }

                                const editedCoords = editedPolygon.geometry.coordinates[0];
                                if (
                                    editedCoords[0][0] !== editedCoords.at(-1)[0] ||
                                    editedCoords[0][1] !== editedCoords.at(-1)[1]
                                ) {
                                    editedCoords.push(editedCoords[0]);
                                }

                                try {
                                    const existingPoly = turf.polygon([existingCoords]);
                                    const editedPoly = turf.polygon([editedCoords]);

                                    if (
                                        turf.booleanIntersects(existingPoly, editedPoly) ||
                                        turf.booleanOverlap(existingPoly, editedPoly)
                                    ) {
                                        intersects = true;
                                    }
                                } catch (err) {
                                    console.error('Error comprobando intersección al editar:',
                                        err);
                                }
                            }
                        });
                    }

                    if (intersects) {
                        Swal.fire(
                            'Error',
                            'La nueva forma del polígono se superpone con otra zona existente. Corrige la posición antes de guardar.',
                            'error'
                        );

                        // 🔹 Revertir cambios (volver al polígono previo)
                        /*
                        if (typeof redrawPolygon === 'function' && window.originalPolygonCoords) {
                            redrawPolygon(window.originalPolygonCoords);
                        }                       
                        */
                        /*
                        if (window.originalPolygonCoords) {
                             // Quitar el polígono editado
                             drawnItems.removeLayer(layer);

                             // Crear uno nuevo con las coordenadas originales
                             const restoredLayer = L.polygon(window.originalPolygonCoords, { color: 'blue', weight: 2, fillColor: '#3388ff', fillOpacity: 0.2 });
                             // restoredLayer._path.classList.add('polygon-restored');
                             drawnItems.addLayer(restoredLayer);

                             console.log('Restaurando polígono a coordenadas originales:', window.originalPolygonCoords);



                             // Actualizar referencias
                             currentPolygon = restoredLayer;
                             updateInputsFromPolygon(restoredLayer.getLatLngs()[0]);
                             // ✅ Actualizar backup con el estado original restaurado
                             window.originalPolygonCoords = restoredLayer.getLatLngs()[0].map(c => [c.lat, c.lng]);



                             console.log('Backup de coordenadas restaurado:', window.originalPolygonCoords);

                             Swal.fire(
                                 'Error',
                                 'La nueva forma se superpone con otra zona. Se restauró la forma original.',
                                 'error'
                             );
                             console.log('Polígono editado que causó intersección revertido a estado original.');
                         }
                             */
                        if (window.originalPolygonCoords) {
                            // 🔁 Reutiliza la función para restaurar el polígono original
                            redrawPolygon(window.originalPolygonCoords);

                            // 🔹 Actualizar inputs y respaldo
                            if (currentPolygon) {
                                updateInputsFromPolygon(currentPolygon.getLatLngs()[0]);
                                window.originalPolygonCoords = currentPolygon.getLatLngs()[0].map(c => [
                                    c.lat, c.lng
                                ]);
                            }

                            Swal.fire(
                                'Error',
                                'La nueva forma se superpone con otra zona. Se restauró la forma original.',
                                'error'
                            );

                            console.log('Polígono editado revertido mediante redrawPolygon():', window
                                .originalPolygonCoords);
                        }



                        return;
                    }

                    // ✅ Si no hay intersección, guardar cambios y actualizar coordenadas
                    const coords = layer.getLatLngs()[0];
                    updateInputsFromPolygon(coords);
                    window.originalPolygonCoords = coords.map(c => [c.lat, c.lng]); // guardar backup
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
                            window.originalPolygonCoords = currentPolygon.getLatLngs()[0].map(c => [c.lat, c
                                .lng
                            ]);

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


        // 🔹 DIBUJAR ZONAS EXISTENTES EN EL MAP
        // Mostrar zonas existentes (solo lectura)
        if (existingZones && existingZones.length > 0) {
            existingZones.forEach(zone => {
                if (zone.coords && zone.coords.length >= 3) {
                    const polygon = L.polygon(zone.coords, {
                        color: '#FF0000',
                        fillColor: '#FF6666',
                        fillOpacity: 0.25,
                        weight: 2,
                        interactive: false
                    }).addTo(map);

                    polygon.bindPopup(`<strong>${zone.name}</strong>`);
                }
            });
        }

        //  Finalmente, dibuja las zonas existentes
    }

    // 🔹 INICIALIZAR MAPA CUANDO EL MODAL SE MUESTRA
    /*
    $('#modal').on('shown.bs.modal', function() {
        setTimeout(initializeMap, 300);
    });
    */
    // Cuando el modal se muestra
    $('#modal').on('shown.bs.modal', function() {
        setTimeout(() => {
            initializeMap();
            if (map) map.invalidateSize();
        }, 400);
    });

    // Dentro de initializeMap(), al final:
    setTimeout(() => {
        if (map) map.invalidateSize();
    }, 800);


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

            const url =
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`;

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
                return Swal.fire('Atención',
                    'Debes seleccionar departamento, provincia y distrito antes de buscar.',
                    'warning');
            }

            if (query.length < 3) {
                $('#addressSuggestions').hide();
                return;
            }



            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                const location = `${query}, ${district}, ${province}, ${department}, Perú`;
                // const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}&addressdetails=1&limit=5`;
                const url =
                    `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=5&street=${encodeURIComponent(query)}&city=${encodeURIComponent(district)}&county=${encodeURIComponent(province)}&state=${encodeURIComponent(department)}&country=Peru`;

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

            /*
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
               */
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
        /*
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
         */
        // ============================================================
        // 🔹 BOTÓN PARA LIMPIAR MAPA, COORDENADAS Y BÚSQUEDA (CORREGIDO)
        // ============================================================
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

                    // 1️⃣ Eliminar todas las coordenadas y dejar un campo limpio
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

                    // 2️⃣ Eliminar completamente polígonos y marcadores del mapa
                    if (drawnItems) {
                        drawnItems.eachLayer(layer => map.removeLayer(layer));
                        drawnItems.clearLayers();
                    }
                    if (currentPolygon) {
                        map.removeLayer(currentPolygon);
                        currentPolygon = null;
                    }
                    if (typeof searchMarker !== 'undefined' && searchMarker) {
                        map.removeLayer(searchMarker);
                        searchMarker = null;
                    }

                    // 3️⃣ Resetear vista inicial del mapa (Lima o la que desees)
                    if (map) map.setView([-12.0464, -77.0428], 12);

                    // 4️⃣ Limpiar búsqueda de dirección
                    $('#addressSearch').val('');
                    $('#addressSuggestions').hide();

                    // 5️⃣ Efecto visual
                    $('#coordinates-container .coordinate-point').addClass('cleared');
                    setTimeout(() => $('.coordinate-point').removeClass('cleared'), 700);

                    Swal.fire('Limpio', 'El mapa, coordenadas y búsqueda fueron reiniciados.',
                        'success');
                }
            });
        });


        // FIn de limpiar

        // Autoseleccionar por defecto JLO
        // ============================================================
        // 🔹 SELECCIÓN AUTOMÁTICA DE UBICACIÓN POR DEFECTO
        // ============================================================
        const defaultDept = "Lambayeque";
        const defaultProv = "Chiclayo";
        const defaultDist = "Jose Leonardo Ortiz";

        // Esperar a que existan las opciones del select departamento
        setTimeout(() => {
            // Seleccionar departamento Lambayeque
            $('#department_id option').filter(function() {
                return $(this).text().trim().toLowerCase() === defaultDept.toLowerCase();
            }).prop('selected', true).trigger('change');

            // Esperar a que se carguen provincias (si se llenan por AJAX)
            const waitProvince = setInterval(() => {
                const provOptions = $('#province_id option').map(function() {
                    return $(this).text().trim().toLowerCase();
                }).get();

                if (provOptions.includes(defaultProv.toLowerCase())) {
                    clearInterval(waitProvince);
                    $('#province_id option').filter(function() {
                        return $(this).text().trim().toLowerCase() === defaultProv
                            .toLowerCase();
                    }).prop('selected', true).trigger('change');

                    // Esperar a que se carguen distritos
                    const waitDistrict = setInterval(() => {
                        const distOptions = $('#district_id option').map(function() {
                            return $(this).text().trim().toLowerCase();
                        }).get();

                        if (distOptions.includes(defaultDist.toLowerCase())) {
                            clearInterval(waitDistrict);
                            $('#district_id option').filter(function() {
                                return $(this).text().trim().toLowerCase() ===
                                    defaultDist.toLowerCase();
                            }).prop('selected', true).trigger('change');
                        }
                    }, 300);
                }
            }, 300);
        }, 500);

        // Fin de autoselección





    });

    // Mapa interactivo con Leaflet.js
</script>

<script>
    // existing zones in JSON (preparado por el controlador)
    window.existingZones = {!! $zonesJson ?? '[]' !!};
    window.currentZoneId = {!! $zone->id ?? 'null' !!};

    // console.log(existingZones);
    // const currentZoneId = {!! $zone->id ?? 'null' !!};
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
        0% {
            background-color: #ffff99;
        }

        100% {
            background-color: transparent;
        }
    }

    .coordinate-point {
        transition: all 0.2s ease-in-out;
    }

    .cleared {
        animation: fadeReset 0.5s ease-in-out;
    }

    @keyframes fadeReset {
        0% {
            background-color: #fff3cd;
        }

        100% {
            background-color: transparent;
        }
    }

    // Nuevo
    .polygon-restored {
        animation: bounceBack 0.6s ease;
    }

    @keyframes bounceBack {
        0% {
            transform: scale(1.1);
            opacity: 0.8;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
</style>
