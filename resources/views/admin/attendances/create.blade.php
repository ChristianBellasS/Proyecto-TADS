{!! Form::open(['route' => 'admin.attendances.store', 'id' => 'attendanceForm']) !!}
@include('admin.attendances.templates.form')
<div class="form-group text-right mt-4">
    <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
    <button type="submit" class="btn btn-success">
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

    .attendance-form {
        min-height: auto;
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
            // Establecer fecha actual por defecto - CORREGIDO
            const today = new Date();
            // Ajustar al huso horario local
            const localDate = new Date(today.getTime() - (today.getTimezoneOffset() * 60000));
            const todayFormatted = localDate.toISOString().split('T')[0];

            if (!$('input[name="attendance_date"]').val()) {
                $('input[name="attendance_date"]').val(todayFormatted);
            }

            // Establecer hora actual por defecto - CORREGIDO
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

            // CORREGIDO: Comparar solo las partes de fecha (sin hora) en hora local
            const selectedLocal = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate
                .getDate());
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

            // CORREGIDO: Comparar solo las partes de fecha (sin hora) en hora local
            const selectedLocal = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate
                .getDate());
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
            // Validaciones previas al envío
            if (!validateForm()) {
                return;
            }

            var form = $('#attendanceForm');
            var formData = new FormData(form[0]);
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
                    $('#modal').modal('hide');
                    Swal.fire({
                        title: "¡Éxito!",
                        text: response.message || "Asistencia registrada correctamente",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Refrescar la tabla principal
                        if (typeof refreshTable === 'function') {
                            refreshTable();
                        } else if (window.refreshTable) {
                            window.refreshTable();
                        } else {
                            // Recargar la página como fallback
                            location.reload();
                        }
                    });
                },
                error: function(response) {
                    var error = response.responseJSON;
                    var errorMessage = 'Error al guardar la asistencia';

                    if (error && error.errors) {
                        // Mostrar todos los errores de validación
                        var errors = Object.values(error.errors).join('<br>');
                        errorMessage = errors;

                        // Mostrar errores en los campos específicos
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

            // Validar período
            const period = $('select[name="period"]').val();
            if (!period) {
                showFieldWarning($('select[name="period"]'), 'El período es requerido');
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
            if (errors.period) {
                showFieldWarning($('select[name="period"]'), errors.period[0]);
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

        $('select[name="period"]').on('change', function() {
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
