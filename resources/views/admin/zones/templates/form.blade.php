<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('name', 'Nombre de la Zona *') !!}
            {!! Form::text('name', null, [
                'class' => 'form-control', 
                'placeholder' => 'Nombre de la zona',
                'required'
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
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
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
        'class' => 'form-control'
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

$('#modal').on('shown.bs.modal', function () {

    // 🔹 Si el mapa no existe, inicialízalo
    if (!map) {
        map = L.map('zoneMap').setView([-12.0464, -77.0428], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            edit: { featureGroup: drawnItems },
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

        // Cuando se dibuja un nuevo polígono
        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            const layer = e.layer;
            currentPolygon = layer; // 🔹 Guardamos referencia al polígono actual

            drawnItems.addLayer(layer);
            

            updateInputsFromPolygon(layer.getLatLngs()[0]);
        });

        // Cuando se edita un polígono
        // map.on(L.Draw.Event.EDITED, function (e) {
        //     const layer = e.layers.getLayers()[0];
        //     if (layer) updateInputsFromPolygon(layer.getLatLngs()[0]);
        // });

        map.on(L.Draw.Event.EDITED, function (e) {
        e.layers.eachLayer(function (layer) {
            currentPolygon = layer; // 🔹 Actualiza la referencia
            const coords = layer.getLatLngs()[0];
            updateInputsFromPolygon(coords);
            });
        });

        if (map && currentPolygon) {
            const editToolbar = new L.EditToolbar.Edit(map, {
                featureGroup: drawnItems
            });
            editToolbar.enable();
        }

        //
    } else {
        map.invalidateSize();
    }
});

// 🔹 Cuando cambia un input → actualiza el mapa
$(document).on('input', '.coord-lat, .coord-lng', function () {
    const coords = getCoordsFromInputs();
    redrawPolygon(coords);
});

// 🔹 Agregar coordenada manualmente
/*
$('#add-coordinate').click(function () {
    const newCoord = $(`
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
    $('#coordinates-container').append(newCoord);
});


// 🔹 Eliminar coordenada
$(document).on('click', '.remove-coord', function () {
    if ($('.coordinate-point').length > 1) {
        $(this).closest('.coordinate-point').remove();
        const coords = getCoordsFromInputs();
        redrawPolygon(coords);
    } else {
        Swal.fire('Error', 'Debe haber al menos una coordenada', 'error');
    }
});
*/
// ============================================================
// FUNCIONES DE SINCRONIZACIÓN
// ============================================================
function updateInputsFromPolygon(latlngs) {
    $('#coordinates-container').empty();
    latlngs.forEach(function (coord) {
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
    $('.coordinate-point').each(function () {
        const lat = parseFloat($(this).find('.coord-lat').val());
        const lng = parseFloat($(this).find('.coord-lng').val());
        if (!isNaN(lat) && !isNaN(lng)) coords.push([lat, lng]);
    });
    return coords;
}

// function redrawPolygon(coords) {
//     if (!map) return;
//     if (currentPolygon) map.removeLayer(currentPolygon);
//     if (coords.length >= 3) {
//         currentPolygon = L.polygon(coords, { color: 'blue' }).addTo(map);
//         map.fitBounds(currentPolygon.getBounds());
//     }
// }

function redrawPolygon(coords) {
    if (!map) return;
    
    // 🔹 Elimina el polígono anterior solo si existe dentro del grupo
    if (currentPolygon && drawnItems.hasLayer(currentPolygon)) {
        drawnItems.removeLayer(currentPolygon);
    }

    if (coords.length >= 3) {
        currentPolygon = L.polygon(coords, { color: 'blue' });
        drawnItems.addLayer(currentPolygon);
        map.fitBounds(currentPolygon.getBounds());
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
        @if(isset($zone) && $zone->coordinates->count() > 0)
            $('#coordinates-container').empty();
            @foreach($zone->coordinates as $coord)
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
        



    });

    // Mapa interactivo con Leaflet.js


</script>

