<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia - Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .attendance-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .attendance-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .header-public {
            background: linear-gradient(135deg, #035286, #034c7c);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        .header-public h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }
        .header-public .lead {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .card-body {
            padding: 2.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .form-control, .select2-container .select2-selection--single {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #035286;
            box-shadow: 0 0 0 0.2rem rgba(3, 82, 134, 0.25);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .alert-info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border: none;
            border-radius: 15px;
            border-left: 5px solid #2196f3;
        }
        .badge {
            font-size: 0.85em;
            padding: 0.5em 0.75em;
            border-radius: 8px;
        }
        .employee-info-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            border-left: 5px solid #035286;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .invalid-feedback {
            display: block;
            font-weight: 500;
        }
        .select2-container--default .select2-selection--single {
            height: auto !important;
            min-height: 53px;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5 !important;
            padding-left: 0 !important;
        }
        .records-list {
            max-height: 200px;
            overflow-y: auto;
        }
        .footer {
            text-align: center;
            padding: 2rem;
            color: white;
            font-size: 0.9rem;
        }
        .footer a {
            color: white;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="attendance-container">
        <div class="attendance-card">
            <!-- Header -->
            <div class="header-public">
                <h1><i class="fas fa-fingerprint me-2"></i>Registro de Asistencia</h1>
                <p class="lead">Sistema de control de entrada y salida</p>
            </div>

            <!-- Form Content -->
            <div class="card-body">
                {!! Form::open(['route' => 'public.attendances.store', 'id' => 'attendanceForm']) !!}

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

                <!-- Form Fields -->
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-4">
                            {!! Form::label('employee_id', 'Empleado *') !!}
                            <select name="employee_id" id="employee_select" class="form-control" required>
                                <option value="">Seleccione un empleado</option>
                                @if(isset($employeeId))
                                    <option value="{{ $employeeId }}" selected></option>
                                @endif
                            </select>
                            <small class="form-text text-muted">Busque por nombre, apellido o DNI del empleado</small>
                        </div>

                        <!-- Información del empleado seleccionado -->
                        <div id="employee_info" class="employee-info-card p-3 mb-4 d-none">
                            <div class="row">
                                <div class="col-md-6">  
                                    <strong><i class="fas fa-user"></i> Nombre completo:</strong>
                                    <span id="info_fullname">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-id-card"></i> DNI:</strong>
                                    <span id="info_dni">-</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-envelope"></i> Email:</strong>
                                    <span id="info_email">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-phone"></i> Teléfono:</strong>
                                    <span id="info_phone">-</span>
                                </div>
                            </div>

                            <!-- Información de asistencia del día -->
                            <div id="attendance_info" class="mt-3 p-3 border rounded bg-white" style="display: none;">
                                <h6 class="mb-3"><i class="fas fa-history"></i> Registros del día:</h6>
                                <div id="today_records" class="records-list"></div>
                                <div id="suggestion_info" class="mt-3 p-2 rounded" style="display: none;">
                                    <small><i class="fas fa-info-circle"></i> <span id="suggestion_text"></span></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    {!! Form::label('attendance_date', 'Fecha *') !!}
                                    {!! Form::date('attendance_date', $attendanceDate ?? now()->format('Y-m-d'), [
                                        'class' => 'form-control',
                                        'required',
                                        'max' => \Carbon\Carbon::now()->format('Y-m-d'),
                                        'id' => 'attendance_date_input',
                                    ]) !!}
                                    <small class="form-text text-muted">Seleccione la fecha de asistencia</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    {!! Form::label('attendance_time', 'Hora *') !!}
                                    {!! Form::time('attendance_time', now()->format('H:i'), [
                                        'class' => 'form-control',
                                        'required',
                                        'id' => 'attendance_time_input',
                                    ]) !!}
                                    <small class="form-text text-muted">Seleccione la hora de registro</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3" >
                                    {!! Form::label('type', 'Tipo *') !!}
                                    {!! Form::select('type', [
                                        'ENTRADA' => 'Entrada',
                                        'SALIDA' => 'Salida',
                                    ], $suggestedType ?? 'ENTRADA', [
                                        'class' => 'form-control',
                                        'required',
                                        'id' => 'type_select',
                                        'disabled' => true,
                                    ]) !!}
                                    <small class="form-text text-muted" id="type_help">
                                        Tipo de registro
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    {!! Form::label('status', 'Estado *') !!}
                                    {!! Form::select('status', [
                                        1 => 'Presente',
                                        2 => 'Tarde',
                                    ], null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Seleccione el estado',
                                        'id' => 'status_select',
                                    ]) !!}
                                    <small class="form-text text-muted">Estado de la asistencia</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            {!! Form::label('notes', 'Notas') !!}
                            {!! Form::textarea('notes', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Agregue notas adicionales sobre la asistencia...',
                                'rows' => 3,
                                'id' => 'notes_input',
                            ]) !!}
                            <small class="form-text text-muted">Observaciones o comentarios sobre el registro</small>
                        </div>

                        <!-- Mensaje de bloqueo completo -->
                        <div id="complete_block_message" class="alert alert-warning d-none">
                            <i class="fas fa-ban"></i>
                            <strong>Asistencia completa:</strong>
                            Este empleado ya tiene registrada tanto la entrada como la salida para este día.
                            No se pueden agregar más registros.
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-secondary mr-3" onclick="window.location.reload()">
                        <i class="fas fa-redo"></i> Limpiar
                    </button>
                    <button type="submit" class="btn btn-success" id="submitButton">
                        <i class="fas fa-save"></i> Guardar Asistencia
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Sistema de Asistencia. Todos los derechos reservados.</p>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // URLs públicas corregidas
        const PUBLIC_ROUTES = {
            // employees_search: '/admin/public/employees/search',
            // attendances_store: '/admin/public/attendances',
            // day_records: '/admin/public/attendances/day-records'
            employees_search: '/public/attendances/search-employees',
            attendances_store: '/public/attendances',
            day_records: '/public/attendances/day-records'
        };

        $(document).ready(function() {
            
            // Inicializar Select2 para búsqueda de empleados
            $('#employee_select').select2({
                width: '100%',
                language: "es",
                placeholder: "Buscar empleado por nombre, apellido o DNI...",
                allowClear: true,
                ajax: {
                    url: PUBLIC_ROUTES.employees_search,
                    type: "GET",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: response.data.map(function(employee) {
                                return {
                                    id: employee.id,
                                    text: employee.name + ' ' + (employee.last_name || '') +
                                        ' - DNI: ' + employee.dni,
                                    full_name: employee.name + ' ' + (employee.last_name || ''),
                                    dni: employee.dni,
                                    email: employee.email,
                                    phone: employee.phone
                                };
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });

            // Inicializar el formulario
            initializeForm();

            // Manejar envío del formulario
            $('#attendanceForm').on('submit', function(e) {
                e.preventDefault();
                submitForm();
            });

            // Eventos para la lógica de bloqueo
            $('#employee_select').on('select2:select', function(e) {
                var data = e.params.data;
                showEmployeeInfo(data);
                const date = $('#attendance_date_input').val();
                loadTodayRecords(data.id, date);
            });

            $('#employee_select').on('select2:clear', function(e) {
                hideEmployeeInfo();
                resetForm();
            });

            $('#attendance_date_input').on('change', function() {
                const selectedEmployee = $('#employee_select').val();
                if (selectedEmployee) {
                    loadTodayRecords(selectedEmployee, $(this).val());
                } else {
                    resetForm();
                }
            });

            function initializeForm() {
                // Establecer fecha actual por defecto
                const today = new Date();
                const localDate = new Date(today.getTime() - (today.getTimezoneOffset() * 60000));
                const todayFormatted = localDate.toISOString().split('T')[0];

                if (!$('#attendance_date_input').val()) {
                    $('#attendance_date_input').val(todayFormatted);
                }

                // Establecer hora actual por defecto
                if (!$('#attendance_time_input').val()) {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    $('#attendance_time_input').val(`${hours}:${minutes}`);
                }

                // Validación de fecha futura
                $('#attendance_date_input').on('change', function() {
                    validateDate($(this));
                });

                // Validación de hora futura
                $('#attendance_time_input').on('change', function() {
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
                const selectedDate = new Date($('#attendance_date_input').val());
                const today = new Date();

                const selectedLocal = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate());
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
                if (!validateForm()) {
                    return;
                }

                var form = $('#attendanceForm');
                var formData = new FormData(form[0]);
                var typeValue = $('#type_select').val();

                formData.append('type', typeValue);

                console.log('📤 Enviando datos del formulario...');

                var submitBtn = $('#submitButton');
                var originalText = submitBtn.html();

                // Mostrar loading
                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...'
                );

                $.ajax({
                    url: PUBLIC_ROUTES.attendances_store,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('✅ Asistencia registrada correctamente');
                        
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message || "Asistencia registrada correctamente",
                            icon: "success",
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            // Limpiar formulario después del éxito
                            form[0].reset();
                            $('#employee_select').val(null).trigger('change');
                            hideEmployeeInfo();
                            resetForm();
                            initializeForm();
                        });
                    },
                    error: function(response) {
                        console.error('❌ Error al guardar:', response);
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
                            icon: "error",
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#dc3545'
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
                const attendanceDate = $('#attendance_date_input').val();
                if (!attendanceDate) {
                    showFieldWarning($('#attendance_date_input'), 'La fecha es requerida');
                    isValid = false;
                } else {
                    if (!validateDate($('#attendance_date_input'))) {
                        isValid = false;
                    }
                }

                // Validar hora
                const attendanceTime = $('#attendance_time_input').val();
                if (!attendanceTime) {
                    showFieldWarning($('#attendance_time_input'), 'La hora es requerida');
                    isValid = false;
                } else {
                    if (!validateTime($('#attendance_time_input'))) {
                        isValid = false;
                    }
                }

                // Validar tipo
                const type = $('#type_select').val();
                if (!type) {
                    showFieldWarning($('#type_select'), 'El tipo es requerido');
                    isValid = false;
                }

                // Validar estado
                const status = $('#status_select').val();
                if (!status) {
                    showFieldWarning($('#status_select'), 'El estado es requerido');
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
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            function showValidationErrors(errors) {
                clearAllValidations();

                if (errors.employee_id) {
                    showFieldWarning($('#employee_select'), errors.employee_id[0]);
                }
                if (errors.attendance_date) {
                    showFieldWarning($('#attendance_date_input'), errors.attendance_date[0]);
                }
                if (errors.attendance_time) {
                    showFieldWarning($('#attendance_time_input'), errors.attendance_time[0]);
                }
                if (errors.type) {
                    showFieldWarning($('#type_select'), errors.type[0]);
                }
                if (errors.status) {
                    showFieldWarning($('#status_select'), errors.status[0]);
                }
                if (errors.notes) {
                    showFieldWarning($('#notes_input'), errors.notes[0]);
                }
            }

            function loadTodayRecords(employeeId, date) {
                if (!employeeId || !date) {
                    resetForm();
                    return;
                }

                $.ajax({
                    url: PUBLIC_ROUTES.day_records,
                    type: "GET",
                    data: {
                        employee_id: employeeId,
                        date: date
                    },
                    success: function(response) {
                        displayTodayRecords(response.records);
                        determineFormStatus(response.records);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar registros:', error);
                        resetForm();
                    }
                });
            }

            function displayTodayRecords(records) {
                const attendanceInfo = $('#attendance_info');
                const todayRecords = $('#today_records');

                if (records && records.length > 0) {
                    let html = '<ul class="list-unstyled mb-0">';
                    records.forEach(record => {
                        const time = record.time;
                        const type = record.type === 'ENTRADA' ?
                            '<span class="badge bg-success me-2">Entrada</span>' :
                            '<span class="badge bg-info me-2">Salida</span>';
                        const status = record.status == 1 ?
                            '<span class="badge bg-primary">Presente</span>' :
                            '<span class="badge bg-warning">Tarde</span>';
                        html += `<li class="mb-2">${type} ${status} - ${time}</li>`;
                    });
                    html += '</ul>';
                    todayRecords.html(html);
                    attendanceInfo.show();
                } else {
                    todayRecords.html('<p class="text-muted mb-0">No hay registros para este día.</p>');
                    attendanceInfo.show();
                }
            }

            function determineFormStatus(records) {
                const hasEntry = records.some(r => r.type === 'ENTRADA');
                const hasExit = records.some(r => r.type === 'SALIDA');

                if (!hasEntry && !hasExit) {
                    setFormStatus('ENTRADA', true, 'Primer registro del día - debe ser ENTRADA', 'warning');
                } else if (hasEntry && !hasExit) {
                    setFormStatus('SALIDA', true, 'Tiene entrada registrada - debe ser SALIDA', 'warning');
                } else if (hasEntry && hasExit) {
                    setFormStatus('ENTRADA', false, 'Ya completó el ciclo - puede elegir nuevo tipo', 'success');
                } else if (!hasEntry && hasExit) {
                    setFormStatus('ENTRADA', false, 'Caso irregular - puede elegir tipo', 'info');
                }
            }

            function setFormStatus(suggestedType, isTypeLocked, message, messageType) {
                const typeSelect = $('#type_select');
                const helpText = $('#type_help');
                const suggestionDiv = $('#suggestion_info');
                const suggestionSpan = $('#suggestion_text');

                typeSelect.val(suggestedType);

                if (isTypeLocked) {
                    // typeSelect.prop('disabled', false);
                    typeSelect.prop('disabled', true);
                    typeSelect.prop('readonly', true);
                    typeSelect.addClass('bg-light');
                } else {
                    typeSelect.prop('readonly', false);
                    typeSelect.removeClass('bg-light');
                }

                if (isTypeLocked) {
                    helpText.html(`<i class="fas fa-lock text-${messageType}"></i> ${message}`);
                    helpText.removeClass('text-success text-info').addClass(`text-${messageType}`);
                } else {
                    helpText.html(`<i class="fas fa-unlock text-${messageType}"></i> ${message}`);
                    helpText.removeClass('text-warning text-danger').addClass(`text-${messageType}`);
                }

                if (message) {
                    suggestionSpan.text(message);
                    suggestionDiv.removeClass('bg-light bg-warning bg-danger bg-success bg-info')
                        .addClass(`bg-${messageType}`).show();

                    if (messageType === 'warning' || messageType === 'danger') {
                        suggestionDiv.addClass('text-white');
                    } else {
                        suggestionDiv.removeClass('text-white');
                    }
                } else {
                    suggestionDiv.hide();
                }
            }

            function resetForm() {
                $('#type_select').val('ENTRADA');
                $('#type_select').prop('readonly', false);
                $('#type_select').removeClass('bg-light');
                $('#type_help').html('Tipo de registro');
                $('#type_help').removeClass('text-warning text-danger text-success text-info');
                $('#attendance_info').hide();
                $('#suggestion_info').hide();
                $('#complete_block_message').addClass('d-none');
            }

            function showEmployeeInfo(employeeData) {
                $('#info_fullname').text(employeeData.full_name);
                $('#info_dni').text(employeeData.dni);
                $('#info_email').text(employeeData.email || 'No registrado');
                $('#info_phone').text(employeeData.phone || 'No registrado');
                $('#employee_info').removeClass('d-none');
            }

            function hideEmployeeInfo() {
                $('#employee_info').addClass('d-none');
                $('#info_fullname').text('-');
                $('#info_dni').text('-');
                $('#info_email').text('-');
                $('#info_phone').text('-');
                $('#attendance_info').hide();
            }
        });
    </script>
</body>
</html>