{!! Form::open(['route' => 'admin.vacations.store', 'id' => 'vacationForm']) !!}
@include('admin.vacations.templates.form')
<div class="form-group text-right">
    <button type="submit" class="btn btn-success"> 
        <i class="fas fa-save"></i> Guardar Solicitud
    </button>
    <button type="button" class="btn btn-danger" data-dismiss="modal"> 
        <i class="fas fa-window-close"></i> Cancelar
    </button>
</div>
{!! Form::close() !!}


<script>
    $(document).ready(function() {
        // Manejar cambios en empleado
        $('#employee_id').change(function() {
            var employeeId = $(this).val();
            if (employeeId) {
                // $.get('{{ route("admin.vacations.available-days", "") }}/' + employeeId, function(data) {
                    $.get('{{ url("admin/vacations") }}/' + employeeId + '/available-days', function(data) {

                    if (data.can_request) {
                        $('#available-days').text(data.available_days);
                        $('#days-info').removeClass('d-none');
                        $('#requested_days').attr('max', data.available_days);
                    } else {
                        $('#days-info').addClass('d-none');
                        Swal.fire('Error', 'Este empleado no puede solicitar vacaciones', 'error');
                        $('#employee_id').val('');
                    }
                });
            } else {
                $('#days-info').addClass('d-none');
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
                        text: error.message || 'Error al guardar la solicitud',
                        icon: "error"
                    });
                }
            });
        });


    });
</script>
