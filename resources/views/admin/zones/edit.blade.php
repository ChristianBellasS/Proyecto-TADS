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
        @if($zone->district)
            var departmentId = {{ $zone->district->province->department->id }};
            var provinceId = {{ $zone->district->province->id }};
            
            // Cargar provincias
            $.get('{{ route("admin.get.provinces", "") }}/' + departmentId, function(data) {
                $('#province_id').empty().append('<option value="">Seleccione provincia</option>');
                $.each(data, function(key, value) {
                    $('#province_id').append('<option value="' + value.id + '" ' + (value.id == provinceId ? 'selected' : '') + '>' + value.name + '</option>');
                });
                
                // Cargar distritos
                $.get('{{ route("admin.get.districts", "") }}/' + provinceId, function(data) {
                    $('#district_id').empty().append('<option value="">Seleccione distrito</option>');
                    $.each(data, function(key, value) {
                        $('#district_id').append('<option value="' + value.id + '" ' + (value.id == {{ $zone->district_id }} ? 'selected' : '') + '>' + value.name + '</option>');
                    });
                });
            });
        @endif

        // Manejar cambios en departamento y provincia (igual que en create)
        $('#department_id').change(function() {
            var departmentId = $(this).val();
            if (departmentId) {
                $.get('{{ route("admin.get.provinces", "") }}/' + departmentId, function(data) {
                    $('#province_id').empty().append('<option value="">Seleccione provincia</option>');
                    $('#district_id').empty().append('<option value="">Seleccione distrito</option>');
                    $.each(data, function(key, value) {
                        $('#province_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
            }
        });

        $('#province_id').change(function() {
            var provinceId = $(this).val();
            if (provinceId) {
                $.get('{{ route("admin.get.districts", "") }}/' + provinceId, function(data) {
                    $('#district_id').empty().append('<option value="">Seleccione distrito</option>');
                    $.each(data, function(key, value) {
                        $('#district_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
            }
        });

        // Manejar envío del formulario
        
        $('#zoneForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            // Agregar coordenadas al formData
            var coordinates = [];
            $('.coordinate-point').each(function() {
                coordinates.push({
                    latitude: $(this).find('.coord-lat').val(),
                    longitude: $(this).find('.coord-lng').val()
                });
            });
            formData.append('coordinates', JSON.stringify(coordinates));
            // --- ADICIÓN MÍNIMA para que Laravel reciba array válido ---
            coordinates.forEach(function(coord, index) {
                formData.append('coordinates[' + index + '][latitude]', coord.latitude);
                formData.append('coordinates[' + index + '][longitude]', coord.longitude);
            });

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#modal').modal('hide');
                    Swal.fire({
                        title: "Proceso exitoso!",
                        text: response.message,
                        icon: "success"
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(response) {
                    var error = response.responseJSON;
                    Swal.fire({
                        title: "Error!",
                        text: error.message || 'Error al actualizar la zona',
                        icon: "error"
                    });
                }
            });
        });
    });
    


</script>   