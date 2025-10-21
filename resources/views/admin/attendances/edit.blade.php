{!! Form::model($attendance, [
    'route' => ['admin.attendances.update', $attendance->id],
    'method' => 'PUT',
    'id' => 'attendanceForm',
]) !!}
@include('admin.attendances.templates.form')
<div class="text-right mt-3">
    <button type="submit" class="btn btn-primary">
        <i class="fa fa-save"></i> Actualizar
    </button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        Cancelar
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
        initializeForm();

        $('#attendanceForm').on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });

        function initializeForm() {
            $('input[name="attendance_date"]').on('change', function() {
                validateDate($(this));
            });

            $('input[name="attendance_time"]').on('change', function() {
                validateTime($(this));
            });

            const attendanceDateInput = document.querySelector('input[name="attendance_date"]');
            if (attendanceDateInput && attendanceDateInput.value) {
                const date = new Date(attendanceDateInput.value);
                if (!isNaN(date.getTime())) {
                    const localDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
                    const formattedDate = localDate.toISOString().split('T')[0];
                    attendanceDateInput.value = formattedDate;
                }
            }

            const attendanceTimeInput = document.querySelector('input[name="attendance_time"]');
            if (attendanceTimeInput && attendanceTimeInput.value) {
                const time = attendanceTimeInput.value;
                if (time.match(/^\d{1,2}:\d{2}$/)) {
                    const [hours, minutes] = time.split(':');
                    const formattedTime = `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
                    attendanceTimeInput.value = formattedTime;
                }
            }
        }

        function validateDate(input) {
            const selectedDate = new Date(input.val());
            const today = new Date();

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

            const selectedLocal = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate
                .getDate());
            const todayLocal = new Date(today.getFullYear(), today.getMonth(), today.getDate());

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
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actualizando...'
            );

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-HTTP-Method-Override': 'PUT'
                },
                success: function(response) {
                    $('#modal').modal('hide');
                    Swal.fire({
                        title: "¡Éxito!",
                        text: response.message || "Asistencia actualizada correctamente",
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
                    var errorMessage = 'Error al actualizar la asistencia';

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

        // Prevenir envío duplicado
        var formSubmitted = false;
        $('#attendanceForm').on('submit', function(e) {
            if (formSubmitted) {
                e.preventDefault();
                return false;
            }
            formSubmitted = true;
        });
    });
</script>
