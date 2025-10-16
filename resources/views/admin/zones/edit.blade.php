{!! Form::model($zone, ['route' => ['admin.zones.update', $zone->id], 'method' => 'PUT', 'id' => 'zoneForm']) !!}
@include('admin.zones.templates.form')
<div class="form-group text-right">
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save"></i> Actualizar
    </button>
    <button type="button" class="btn btn-danger" data-dismiss="modal">
        <i class="fas fa-window-close"></i> Cancelar
    </button>
</div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        // Pre-cargar datos de ubicación
        @if ($zone->district)
            var departmentId = {{ $zone->district->province->department->id }};
            var provinceId = {{ $zone->district->province->id }};

            // Cargar provincias
            $.get('{{ route('admin.get.provinces', '') }}/' + departmentId, function(data) {
                $('#province_id').empty().append('<option value="">Seleccione provincia</option>');
                $.each(data, function(key, value) {
                    $('#province_id').append('<option value="' + value.id + '" ' + (value.id ==
                        provinceId ? 'selected' : '') + '>' + value.name + '</option>');
                });

                // Cargar distritos
                $.get('{{ route('admin.get.districts', '') }}/' + provinceId, function(data) {
                    $('#district_id').empty().append(
                        '<option value="">Seleccione distrito</option>');
                    $.each(data, function(key, value) {
                        $('#district_id').append('<option value="' + value.id + '" ' + (
                            value.id == {{ $zone->district_id }} ? 'selected' :
                            '') + '>' + value.name + '</option>');
                    });
                });
            });
        @endif

        $('#modal').on('shown.bs.modal', function () {
            setTimeout(function() {
                // Verificar si hay coordenadas y dibujar el polígono
                @if($zone->coordinates->count() > 0)
                    const coords = [];
                    $('.coordinate-point').each(function() {
                        const lat = parseFloat($(this).find('.coord-lat').val());
                        const lng = parseFloat($(this).find('.coord-lng').val());
                        if (!isNaN(lat) && !isNaN(lng)) {
                            coords.push([lat, lng]);
                        }
                    });
                    
                    if (coords.length >= 3) {
                        // Usar la función redrawPolygon del form.blade.php
                        if (typeof redrawPolygon === 'function') {
                            redrawPolygon(coords);
                            
                            // Ajustar vista del mapa al polígono
                            if (map && currentPolygon) {
                                map.fitBounds(currentPolygon.getBounds());
                            }
                        }
                    }
                @endif
            }, 1000);
        });

        // Manejar cambios en departamento y provincia (igual que en create)
        $('#department_id').change(function() {
            var departmentId = $(this).val();
            if (departmentId) {
                $.get('{{ route('admin.get.provinces', '') }}/' + departmentId, function(data) {
                    $('#province_id').empty().append(
                        '<option value="">Seleccione provincia</option>');
                    $('#district_id').empty().append(
                        '<option value="">Seleccione distrito</option>');
                    $.each(data, function(key, value) {
                        $('#province_id').append('<option value="' + value.id + '">' +
                            value.name + '</option>');
                    });
                });
            }
        });

        $('#province_id').change(function() {
            var provinceId = $(this).val();
            if (provinceId) {
                $.get('{{ route('admin.get.districts', '') }}/' + provinceId, function(data) {
                    $('#district_id').empty().append(
                        '<option value="">Seleccione distrito</option>');
                    $.each(data, function(key, value) {
                        $('#district_id').append('<option value="' + value.id + '">' +
                            value.name + '</option>');
                    });
                });
            }
        });

        // Manejar envío del formulario
        $('#zoneForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('status', $('#status').val());

            // Agregar coordenadas al formData
            var coordinates = [];
            $('.coordinate-point').each(function() {
                var lat = $(this).find('.coord-lat').val();
                var lng = $(this).find('.coord-lng').val();
                if (lat && lng) {
                    coordinates.push({
                        latitude: lat,
                        longitude: lng
                    });
                }
            });

            // Validar mínimo 3 coordenadas
            if (coordinates.length < 3) {
                Swal.fire({
                    title: "Error!",
                    text: 'Se requieren al menos 3 coordenadas para definir un perímetro',
                    icon: "error"
                });
                return;
            }

            // Agregar coordenadas como array para Laravel
            coordinates.forEach(function(coord, index) {
                formData.append('coordinates[' + index + '][latitude]', coord.latitude);
                formData.append('coordinates[' + index + '][longitude]', coord.longitude);
            });

            // Agregar método PUT para Laravel
            formData.append('_method', 'PUT');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#modal').modal('hide');
                    Swal.fire({
                        title: "¡Éxito!",
                        text: response.message,
                        icon: "success"
                    }).then(() => {
                        if (window.parent.refreshTable) {
                            window.parent.refreshTable();
                        } else {
                            window.parent.location.reload();
                        }
                    });
                },
                error: function(response) {
                    var error = response.responseJSON;
                    Swal.fire({
                        title: "Error!",
                        text: error.message || 'Error al actualizar la zona',
                        icon: "error"
                    });
                },
            });
        });
    });
</script>