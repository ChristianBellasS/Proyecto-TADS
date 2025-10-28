{!! Form::open(['route' => 'admin.attendances.store', 'id' => 'attendanceForm']) !!}

@if(isset($lastAttendance) && $lastAttendance)
<div class="alert alert-info mb-4">
    <h6 class="alert-heading"><i class="fas fa-history me-2"></i>Último registro encontrado</h6>
    <div class="row mt-2">
        <div class="col-md-6">
            <strong>Tipo:</strong> 
            <span class="badge {{ $lastAttendance->type === 'ENTRADA' ? 'bg-success' : 'bg-info' }}">
                {{ $lastAttendance->type === 'ENTRADA' ? 'Entrada' : 'Salida' }}
            </span>
        </div>
        <div class="col-md-6">
            <strong>Hora:</strong> {{ $lastAttendance->attendance_date->format('H:i') }}
        </div>
    </div>
</div>
@endif

@include('admin.attendances.templates.form')

<div class="form-group text-right mt-4">
    <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
    <button type="submit" class="btn btn-success" id="submitButton">
        <i class="fas fa-save"></i> Guardar Asistencia
    </button>
</div>
{!! Form::close() !!}

<style>
    .modal-body {
        max-height: none !important;
        overflow-y: visible !important;
        padding: 1.5rem !important;
    }
    .is-invalid {
        border-color: #dc3545 !important;
    }
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
</style>

<script>
    $(document).ready(function() {
        // Inicializar el formulario
        initializeForm();

        // Manejar envío del formulario
        $('#attendanceForm').on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });

        function initializeForm() {
            // Establecer fecha actual por defecto
            const today = new Date();
            const localDate = new Date(today.getTime() - (today.getTimezoneOffset() * 60000));
            const todayFormatted = localDate.toISOString().split('T')[0];

            if (!$('input[name="attendance_date"]').val()) {
                $('input[name="attendance_date"]').val(todayFormatted);
            }

            // Establecer hora actual por defecto
            if (!$('input[name="attendance_time"]').val()) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                $('input[name="attendance_time"]').val(`${hours}:${minutes}`);
            }

            // Validación de fecha futura
            $('input[name="attendance_date"]').on('change', function() {
                validateDate($(this));
            });

            // Validación de hora futura
            $('input[name="attendance_time"]').on('change', function() {
                validateTime($(this));
            });
        }

        function validateDate(input) {
            const selectedDate = new Date(input.val());
            const today = new Date();

            const selectedLocal = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate());
            const todayLocal = new Date(today.getFullYear(), today.getMonth(), today.getDate());

            if (selectedLocal > todayLocal) {
                showFieldWarning(input, 'No se puede registrar asistencia con fecha futura');
                return false;
            } else {
                hideFieldWarning(input);
                return true;
            }
        }

        function validateTime(input) {
            const selectedDate = new Date($('input[name="attendance_date"]').val());
            const today = new Date();

            const selectedLocal = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate());
            const todayLocal = new Date(today.getFullYear(), today.getMonth(), today.getDate());

            // Solo validar hora si la fecha seleccionada es hoy
            if (selectedLocal.getTime() === todayLocal.getTime()) {
                const [hours, minutes] = input.val().split(':');
                const selectedTime = new Date();
                selectedTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);

                if (selectedTime > today) {
                    showFieldWarning(input, 'No se puede registrar asistencia con hora futura');
                    return false;
                }
            }

            hideFieldWarning(input);
            return true;
        }

        function showFieldWarning(input, message) {
            input.addClass('is-invalid');
            let feedback = input.next('.invalid-feedback');
            if (feedback.length === 0) {
                input.after('<div class="invalid-feedback">' + message + '</div>');
            } else {
                feedback.text(message);
            }
        }

        function hideFieldWarning(input) {
            input.removeClass('is-invalid');
            input.next('.invalid-feedback').remove();
        }

        function submitForm() {
            if (!validateForm()) {
                return;
            }

            var form = $('#attendanceForm');
            var formData = new FormData(form[0]);
            
            console.log('📤 Datos del formulario:');
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();

            // Mostrar loading
            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...'
            );

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('✅ Respuesta del servidor:', response);
                    $('#modal').modal('hide');
                    
                    Swal.fire({
                        title: "¡Éxito!",
                        text: response.message || "Asistencia registrada correctamente",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        refreshAttendanceTable();
                    });
                },
                error: function(response) {
                    console.error('❌ Error en la respuesta:', response);
                    var error = response.responseJSON;
                    var errorMessage = 'Error al guardar la asistencia';

                    if (error && error.errors) {
                        var errors = Object.values(error.errors).join('<br>');
                        errorMessage = errors;
                        showValidationErrors(error.errors);
                    } else if (error && error.message) {
                        errorMessage = error.message;
                    }

                    Swal.fire({
                        title: "Error!",
                        html: errorMessage,
                        icon: "error"
                    });
                },
                complete: function() {
                    // Restaurar botón
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        function refreshAttendanceTable() {
            console.log('Refrescando tabla de asistencias...');
            
            // Método 1: Si existe la función global refreshTable
            if (typeof refreshTable === 'function') {
                refreshTable();
                console.log('Tabla refrescada usando refreshTable()');
                return;
            }
            
            // Método 2: Si existe window.refreshTable
            if (typeof window.refreshTable === 'function') {
                window.refreshTable();
                console.log('Tabla refrescada usando window.refreshTable()');
                return;
            }
            
            // Método 3: Si existe la DataTable directamente
            if ($.fn.DataTable.isDataTable('#table')) {
                $('#table').DataTable().ajax.reload(null, false);
                console.log('Tabla refrescada usando DataTable().ajax.reload()');
                return;
            }
            
            // Método 4: Último recurso - recargar página
            console.log('No se encontró método de refresco, recargando página...');
            location.reload();
        }

        function validateForm() {
            let isValid = true;
            clearAllValidations();

            // Validar empleado seleccionado
            const employeeId = $('#employee_select').val();
            if (!employeeId) {
                showFieldWarning($('#employee_select'), 'Debe seleccionar un empleado');
                isValid = false;
            }

            // Validar fecha
            const attendanceDate = $('input[name="attendance_date"]').val();
            if (!attendanceDate) {
                showFieldWarning($('input[name="attendance_date"]'), 'La fecha es requerida');
                isValid = false;
            } else {
                // Validar que no sea fecha futura
                if (!validateDate($('input[name="attendance_date"]'))) {
                    isValid = false;
                }
            }

            // Validar hora
            const attendanceTime = $('input[name="attendance_time"]').val();
            if (!attendanceTime) {
                showFieldWarning($('input[name="attendance_time"]'), 'La hora es requerida');
                isValid = false;
            } else {
                // Validar que no sea hora futura si la fecha es hoy
                if (!validateTime($('input[name="attendance_time"]'))) {
                    isValid = false;
                }
            }

            // Validar tipo
            const type = $('select[name="type"]').val();
            if (!type) {
                showFieldWarning($('select[name="type"]'), 'El tipo es requerido');
                isValid = false;
            }

            // Validar estado
            const status = $('select[name="status"]').val();
            if (!status) {
                showFieldWarning($('select[name="status"]'), 'El estado es requerido');
                isValid = false;
            }

            if (!isValid) {
                Swal.fire({
                    title: "Formulario incompleto",
                    text: "Por favor complete todos los campos requeridos correctamente",
                    icon: "warning",
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            return isValid;
        }

        function clearAllValidations() {
            // Limpiar todas las validaciones visuales
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        }

        function showValidationErrors(errors) {
            clearAllValidations();

            // Mostrar errores en campos específicos
            if (errors.employee_id) {
                showFieldWarning($('#employee_select'), errors.employee_id[0]);
            }
            if (errors.attendance_date) {
                showFieldWarning($('input[name="attendance_date"]'), errors.attendance_date[0]);
            }
            if (errors.attendance_time) {
                showFieldWarning($('input[name="attendance_time"]'), errors.attendance_time[0]);
            }
            if (errors.type) {
                showFieldWarning($('select[name="type"]'), errors.type[0]);
            }
            if (errors.status) {
                showFieldWarning($('select[name="status"]'), errors.status[0]);
            }
            if (errors.notes) {
                showFieldWarning($('textarea[name="notes"]'), errors.notes[0]);
            }
        }

        // Limpiar validaciones cuando el usuario interactúa con los campos
        $('#employee_select').on('change', function() {
            if ($(this).val()) {
                hideFieldWarning($(this));
            }
        });

        $('input[name="attendance_date"]').on('input', function() {
            hideFieldWarning($(this));
        });

        $('input[name="attendance_time"]').on('input', function() {
            hideFieldWarning($(this));
        });

        $('select[name="type"]').on('change', function() {
            if ($(this).val()) {
                hideFieldWarning($(this));
            }
        });

        $('select[name="status"]').on('change', function() {
            if ($(this).val()) {
                hideFieldWarning($(this));
            }
        });
    });
</script>