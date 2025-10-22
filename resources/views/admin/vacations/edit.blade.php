{!! Form::model($vacation, ['route' => ['admin.vacations.update', $vacation->id], 'method' => 'PUT', 'id' => 'vacationForm']) !!}
@include('admin.vacations.templates.form')
<div class="form-group text-right">
    <button type="submit" class="btn btn-success"> 
        <i class="fas fa-save"></i> Actualizar Solicitud
    </button>
    <button type="button" class="btn btn-danger" data-dismiss="modal"> 
        <i class="fas fa-window-close"></i> Cancelar
    </button>
</div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        // Mostrar días disponibles para el empleado
        var employeeId = {{ $vacation->employee_id }};
        // $.get('{{ route("admin.vacations.available-days", "") }}/' + employeeId, function(data) {
        $.get('{{ url("admin/vacations") }}/' + employeeId + '/available-days', function(data) {

            if (data.can_request) {
                // Sumar los días de esta solicitud a los disponibles
                // var availableWithCurrent = data.available_days + {{ $vacation->requested_days }};
                // var availableWithCurrent = Math.min(30, data.available_days + {{ $vacation->requested_days }});
                var availableWithCurrent = Math.min(30, data.available_days);

                $('#available-days').text(availableWithCurrent);
                $('#days-info').removeClass('d-none');
                $('#requested_days').attr('max', availableWithCurrent);
            }
        });

        // Calcular fecha de fin automáticamente
        $('#start_date, #requested_days').change(function() {
            var startDate = $('#start_date').val();
            var days = parseInt($('#requested_days').val()) || 0;
            
            if (startDate && days > 0) {
                var start = new Date(startDate);
                var endDate = new Date(start);
                endDate.setDate(start.getDate() + days - 1);
                
                $('#end_date').val(endDate.toISOString().split('T')[0]);
            }
        });

        // Manejar envío del formulario
        $('#vacationForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

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
                        // location.reload();
                        refreshTable();
                    });
                },
                error: function(response) {
                    var error = response.responseJSON;
                    Swal.fire({
                        title: "Error!",
                        text: error.message || 'Error al actualizar la solicitud',
                        icon: "error"
                    });
                }
            });
        });
                // Inicializar Select2 para empleado
        // Inicializar el combo con búsqueda
        $('#employee_id').select2({
            placeholder: 'Buscar empleado por nombre o DNI',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                }
            }
        });
        // FFin de Select2
    });
</script>