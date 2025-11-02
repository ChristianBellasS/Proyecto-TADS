<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Programación Masiva</h1>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt mr-2"></i>Programación Masiva
        </h5>
    </div>
    <div class="card-body">
        <form id="massSchedulingForm">
            @csrf

            <!-- Rango de Fechas con Botón en línea -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Fecha de inicio: *</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                            <div class="input-group-append">
                                <span class="input-group-text bg-light">
                                    dd/mm/aaaa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Fecha de fin:</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="end_date" name="end_date">
                            <div class="input-group-append">
                                <span class="input-group-text bg-light">
                                    dd/mm/aaaa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">&nbsp;</label>
                        <button type="button" class="btn btn-warning btn-lg w-100" id="btnValidate"
                            style="height: 42px;">
                            <i class="fas fa-check-circle mr-2"></i>Validar Disponibilidad
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros de Turnos -->
            <div class="mb-4">
                <h6 class="font-weight-bold mb-3">Filtrar por Turno:</h6>
                <div class="d-flex flex-wrap gap-2" id="turnsContainer">
                    <button type="button" class="btn btn-outline-primary turn-btn active" data-turn="all">
                        Todos los Turnos
                    </button>
                    <button type="button" class="btn btn-outline-primary turn-btn" data-turn="Mañana">
                        Mañana
                    </button>
                    <button type="button" class="btn btn-outline-primary turn-btn" data-turn="Tarde">
                        Tarde
                    </button>
                    <button type="button" class="btn btn-outline-primary turn-btn" data-turn="Noche">
                        Noche
                    </button>
                </div>
            </div>

            <hr>

            <!-- Grupos en Bloques de 3 -->
            <div class="mb-4">
                <h5 class="font-weight-bold mb-3">Grupos de Trabajo</h5>
                <div class="row" id="groupsContainer">
                    @foreach ($groups as $group)
                        <div class="col-lg-4 col-md-6 mb-4 group-item" data-group-id="{{ $group->id }}"
                            data-turn="{{ $group->shift->name ?? '' }}">
                            <div class="group-block p-3 border rounded h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold text-uppercase mb-0">{{ $group->name }}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-group"
                                        data-group-id="{{ $group->id }}" title="Eliminar grupo">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <div class="mb-2">
                                    <strong>Zona:</strong> {{ $group->zone->name ?? 'Sin zona' }}
                                </div>
                                <div class="mb-2">
                                    <strong>Turno:</strong>
                                    <span class="badge badge-info ml-2 turn-badge">{{ $group->shift->name ?? 'Sin turno' }}</span>
                                    <input type="hidden" name="groups[{{ $group->id }}][shift_id]"
                                        value="{{ $group->shift_id }}">
                                </div>
                                <div class="mb-2">
                                    <strong>Días:</strong> {{ $group->days }}
                                </div>
                                <div class="mb-2">
                                    <strong>Vehículo:</strong>
                                    <span class="badge badge-secondary ml-2">
                                        {{ $group->vehicle->code ?? 'Sin vehículo' }} (Capacidad:
                                        {{ $group->vehicle->people_capacity ?? 0 }})
                                    </span>
                                    <input type="hidden" name="groups[{{ $group->id }}][vehicle_id]"
                                        value="{{ $group->vehicle_id }}">
                                </div>

                                <div class="mt-3">
                                    <!-- Conductor con validación individual -->
                                    <div class="mb-2">
                                        <strong>Conductor:</strong>
                                        <select class="form-control mt-1 driver-select employee-select"
                                            name="groups[{{ $group->id }}][driver_id]" data-role="conductor"
                                            data-group-id="{{ $group->id }}">
                                            <option value="">Seleccione conductor</option>
                                            @foreach ($employees->where('employeetype_id', 1) as $employee)
                                                <option value="{{ $employee->id }}"
                                                    {{ $group->driver_id == $employee->id ? 'selected' : '' }}
                                                    data-employee-name="{{ $employee->name }} {{ $employee->last_name }}">
                                                    {{ $employee->name }} {{ $employee->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="employee-validation-result mt-1" data-role="conductor"
                                            style="display: none;"></div>
                                    </div>

                                    <!-- Ayudante 1 con validación individual -->
                                    <div class="mb-2">
                                        <strong>Ayudante 1:</strong>
                                        <select class="form-control mt-1 assistant-select employee-select"
                                            name="groups[{{ $group->id }}][assistant1_id]" data-role="ayudante1"
                                            data-group-id="{{ $group->id }}">
                                            <option value="">Seleccione ayudante</option>
                                            @foreach ($employees->where('employeetype_id', 2) as $employee)
                                                <option value="{{ $employee->id }}"
                                                    {{ $group->assistant1_id == $employee->id ? 'selected' : '' }}
                                                    data-employee-name="{{ $employee->name }} {{ $employee->last_name }}">
                                                    {{ $employee->name }} {{ $employee->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="employee-validation-result mt-1" data-role="ayudante1"
                                            style="display: none;"></div>
                                    </div>

                                    <!-- Ayudante 2 con validación individual -->
                                    <div class="mb-2">
                                        <strong>Ayudante 2:</strong>
                                        <select class="form-control mt-1 assistant-select employee-select"
                                            name="groups[{{ $group->id }}][assistant2_id]" data-role="ayudante2"
                                            data-group-id="{{ $group->id }}">
                                            <option value="">Seleccione ayudante</option>
                                            @foreach ($employees->where('employeetype_id', 2) as $employee)
                                                <option value="{{ $employee->id }}"
                                                    {{ $group->assistant2_id == $employee->id ? 'selected' : '' }}
                                                    data-employee-name="{{ $employee->name }} {{ $employee->last_name }}">
                                                    {{ $employee->name }} {{ $employee->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="employee-validation-result mt-1" data-role="ayudante2"
                                            style="display: none;"></div>
                                    </div>

                                    @if ($group->assistant3_id)
                                        <!-- Ayudante 3 con validación individual -->
                                        <div class="mb-2">
                                            <strong>Ayudante 3:</strong>
                                            <select class="form-control mt-1 assistant-select employee-select"
                                                name="groups[{{ $group->id }}][assistant3_id]"
                                                data-role="ayudante3" data-group-id="{{ $group->id }}">
                                                <option value="">Seleccione ayudante</option>
                                                @foreach ($employees->where('employeetype_id', 2) as $employee)
                                                    <option value="{{ $employee->id }}"
                                                        {{ $group->assistant3_id == $employee->id ? 'selected' : '' }}
                                                        data-employee-name="{{ $employee->name }} {{ $employee->last_name }}">
                                                        {{ $employee->name }} {{ $employee->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="employee-validation-result mt-1" data-role="ayudante3"
                                                style="display: none;"></div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Validación general del grupo -->
                                <div class="group-validation-result mt-2" style="display: none;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Sección de Advertencias/Errores -->
            <div id="validationSection" class="mb-4" style="display: none;">
                <h5 class="text-warning mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Resultado de Validación General
                </h5>
                <div id="validationAlerts">
                    <!-- Las advertencias y errores se mostrarán aquí -->
                </div>
            </div>

            <!-- Botón Registrar Programación -->
            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary btn-lg" id="btnGenerate" disabled>
                    <i class="fas fa-calendar-plus mr-2"></i>Registrar Programación
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .group-block {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
        height: 100%;
    }

    .group-block:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .group-block.deleted {
        opacity: 0.6;
        background-color: #f8f9fa;
        border-left-color: #6c757d;
    }

    .group-block.has-errors {
        border-left-color: #dc3545;
        background-color: #fff5f5;
    }

    .group-block.has-warnings {
        border-left-color: #ffc107;
        background-color: #fffdf5;
    }

    .group-block.valid {
        border-left-color: #28a745;
        background-color: #f8fff9;
    }

    .employee-select.has-error {
        border-color: #dc3545;
        background-color: #f8d7da;
    }

    .employee-select.has-warning {
        border-color: #ffc107;
        background-color: #fff3cd;
    }

    .employee-select.valid {
        border-color: #28a745;
        background-color: #d4edda;
    }

    .employee-validation-result {
        font-size: 0.75rem;
        padding: 5px 8px;
        border-radius: 4px;
        margin-top: 2px;
    }

    .validation-error {
        color: #dc3545;
        background-color: #f8d7da;
        border-left: 3px solid #dc3545;
    }

    .validation-warning {
        color: #856404;
        background-color: #fff3cd;
        border-left: 3px solid #ffc107;
    }

    .validation-success {
        color: #155724;
        background-color: #d4edda;
        border-left: 3px solid #28a745;
    }

    .validation-alert {
        border-left: 4px solid;
        padding: 12px 15px;
        margin-bottom: 10px;
        border-radius: 6px;
    }

    .alert-error {
        border-left-color: #dc3545;
        background-color: #f8d7da;
    }

    .alert-warning {
        border-left-color: #ffc107;
        background-color: #fff3cd;
    }

    .alert-success {
        border-left-color: #28a745;
        background-color: #d4edda;
    }

    .input-group-text.bg-light {
        background-color: #e9ecef !important;
        color: #6c757d;
        font-style: italic;
    }

    .turn-btn {
        transition: all 0.3s;
        border: 2px solid #dee2e6;
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    .turn-btn.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
        transform: scale(1.05);
    }

    .turn-btn:hover:not(.active) {
        background-color: #e9ecef;
        border-color: #0d6efd;
    }

    /* Asegurar que los grupos se muestren en filas de 3 */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }

    .col-lg-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }

    @media (max-width: 992px) {
        .col-lg-4 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (max-width: 768px) {
        .col-lg-4 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    .group-item {
        display: block !important;
    }
</style>

<script>
    $(document).ready(function() {
        let validationResults = null;
        const deletedGroups = new Set();

        // FILTRO CORREGIDO Y FUNCIONANDO
        function initializeTurnFilter() {
            $('.turn-btn').off('click').on('click', function() {
                const turn = $(this).data('turn');
                
                console.log('Filtrando por turno:', turn);
                
                // Actualizar botones activos
                $('.turn-btn').removeClass('active');
                $(this).addClass('active');

                if (turn === 'all') {
                    // Mostrar todos los grupos
                    $('.group-item').show();
                } else {
                    // Ocultar todos primero
                    $('.group-item').hide();
                    
                    // Mostrar solo los grupos que tienen el turno seleccionado
                    $('.group-item').each(function() {
                        const groupTurn = $(this).data('turn');
                        console.log('Grupo ID:', $(this).data('group-id'), 'Turno:', groupTurn);
                        
                        if (groupTurn === turn) {
                            $(this).show();
                        }
                    });
                }
                
                // Reajustar el layout después de filtrar
                setTimeout(() => {
                    $('#groupsContainer').masonry?.('layout');
                }, 100);
            });
        }

        // Inicializar el filtro
        initializeTurnFilter();

        function initializeGroupEvents() {
            $('.delete-group').off('click').on('click', function() {
                const groupId = $(this).data('group-id');
                const block = $(this).closest('.group-block');

                if (deletedGroups.has(groupId)) {
                    deletedGroups.delete(groupId);
                    block.removeClass('deleted');
                    $(this).removeClass('btn-secondary').addClass('btn-outline-danger')
                        .html('<i class="fas fa-times"></i>');
                    block.find('select').prop('disabled', false);
                } else {
                    deletedGroups.add(groupId);
                    block.addClass('deleted');
                    $(this).removeClass('btn-outline-danger').addClass('btn-secondary')
                        .html('<i class="fas fa-undo"></i>');
                    block.find('select').prop('disabled', true);
                }

                $('#validationSection').hide();
                $('#btnGenerate').prop('disabled', true);
            });

            $('.employee-select').off('change').on('change', function() {
                const employeeId = $(this).val();
                const role = $(this).data('role');
                const groupId = $(this).data('group-id');
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const validationResult = $(this).siblings('.employee-validation-result');

                $(this).removeClass('has-error has-warning valid');
                validationResult.hide().empty();

                if (!employeeId || !startDate) return;

                validateIndividualEmployee(employeeId, role, startDate, endDate, $(this),
                    validationResult);
            });
        }

        // Función para validar empleado individual
        function validateIndividualEmployee(employeeId, role, startDate, endDate, selectElement,
            validationElement) {
            $.ajax({
                url: '{{ route('admin.mass-scheduling.validate-employee') }}',
                type: 'POST',
                data: JSON.stringify({
                    employee_id: employeeId,
                    role: role,
                    start_date: startDate,
                    end_date: endDate
                }),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    let message = '';
                    let cssClass = '';

                    if (response.has_errors) {
                        cssClass = 'validation-error';
                        selectElement.addClass('has-error').removeClass('has-warning valid');
                        message = '<i class="fas fa-times-circle mr-1"></i>' + response.errors.join(
                            ', ');
                    } else if (response.has_warnings) {
                        cssClass = 'validation-warning';
                        selectElement.addClass('has-warning').removeClass('has-error valid');
                        message = '<i class="fas fa-exclamation-triangle mr-1"></i>' + response
                            .warnings.join(', ');
                    } else {
                        cssClass = 'validation-success';
                        selectElement.addClass('valid').removeClass('has-error has-warning');
                        message = '<i class="fas fa-check-circle mr-1"></i>Empleado disponible';
                    }

                    validationElement.html(message).addClass(cssClass).show();
                },
                error: function(xhr) {
                    console.error('Error en validación individual:', xhr);
                }
            });
        }

        // Validar disponibilidad general
        $('#btnValidate').click(function() {
            const formData = getFormData();

            if (!validateForm(formData)) {
                return;
            }

            $.ajax({
                url: '{{ route('admin.mass-scheduling.validate') }}',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#btnValidate').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin mr-2"></i>Validando...');
                },
                success: function(response) {
                    validationResults = response;
                    showValidationResults(response);
                    updateBlocksVisualState(response);
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Ocurrió un error durante la validación', 'error');
                },
                complete: function() {
                    $('#btnValidate').prop('disabled', false).html(
                        '<i class="fas fa-check-circle mr-2"></i>Validar Disponibilidad'
                    );
                }
            });
        });

        // Generar programación
        $('#btnGenerate').click(function() {
            if (!validationResults || validationResults.has_errors) {
                Swal.fire('Error', 'No se puede generar la programación con errores de validación',
                    'error');
                return;
            }

            Swal.fire({
                title: '¿Registrar Programación?',
                text: `Se crearán programaciones para ${validationResults.validation_results.length} grupos`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    generateScheduling();
                }
            });
        });

        function getFormData() {
            const formData = {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                groups: []
            };

            $('.group-item:visible').each(function() {
                const groupId = $(this).data('group-id');
                const block = $(this).find('.group-block');

                // Saltar grupos eliminados
                if (deletedGroups.has(groupId)) {
                    return;
                }

                formData.groups.push({
                    group_id: groupId,
                    shift_id: block.find('input[name$="[shift_id]"]').val(),
                    vehicle_id: block.find('input[name$="[vehicle_id]"]').val(),
                    driver_id: block.find('.driver-select').val(),
                    assistant1_id: block.find('select[name$="[assistant1_id]"]').val(),
                    assistant2_id: block.find('select[name$="[assistant2_id]"]').val(),
                    assistant3_id: block.find('select[name$="[assistant3_id]"]').val(),
                    assistant4_id: block.find('select[name$="[assistant4_id]"]').val(),
                    assistant5_id: block.find('select[name$="[assistant5_id]"]').val()
                });
            });

            return formData;
        }

        function validateForm(formData) {
            if (!formData.start_date) {
                Swal.fire('Error', 'Seleccione la fecha de inicio', 'error');
                $('#start_date').focus();
                return false;
            }

            if (formData.groups.length === 0) {
                Swal.fire('Error', 'No hay grupos activos para programar', 'error');
                return false;
            }

            // Verificar que todos los grupos tengan datos requeridos
            for (let group of formData.groups) {
                if (!group.driver_id) {
                    Swal.fire('Error', 'Todos los grupos deben tener conductor seleccionado', 'error');
                    return false;
                }

                // Verificar que tenga al menos un ayudante
                const hasAssistant = group.assistant1_id || group.assistant2_id || group.assistant3_id ||
                    group.assistant4_id || group.assistant5_id;
                if (!hasAssistant) {
                    Swal.fire('Error', 'Todos los grupos deben tener al menos un ayudante seleccionado',
                        'error');
                    return false;
                }
            }

            return true;
        }

        function showValidationResults(results) {
            const validationAlerts = $('#validationAlerts');
            validationAlerts.empty();

            let hasErrors = false;

            results.validation_results.forEach(result => {
                // Saltar grupos eliminados
                if (deletedGroups.has(result.group_id)) {
                    return;
                }

                const alertClass = result.has_errors ? 'alert-error' :
                    result.has_warnings ? 'alert-warning' : 'alert-success';

                let alertHtml = `
                    <div class="validation-alert ${alertClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="font-weight-bold mb-2">
                                    <i class="fas fa-users mr-2"></i>${result.group_name}
                                    <small class="ml-2">- ${result.zone_name}</small>
                                </h6>
                            </div>
                            <div>
                                ${result.has_errors ? '<span class="badge badge-danger">Con Errores</span>' : ''}
                                ${result.has_warnings ? '<span class="badge badge-warning">Con Advertencias</span>' : ''}
                                ${!result.has_errors && !result.has_warnings ? '<span class="badge badge-success">Válido</span>' : ''}
                            </div>
                        </div>
                `;

                // Mostrar días no cubiertos
                if (result.uncovered_days && result.uncovered_days.length > 0) {
                    alertHtml += `
                        <div class="mb-2">
                            <strong class="text-warning"><i class="fas fa-calendar-times mr-1"></i>Días no cubiertos:</strong>
                            <ul class="mb-1 pl-3">
                                ${result.uncovered_days.map(day => `<li class="text-warning">${day}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }

                // Mostrar errores
                if (result.errors.length > 0) {
                    hasErrors = true;
                    alertHtml += `
                        <div class="mb-2">
                            <strong class="text-danger"><i class="fas fa-times-circle mr-1"></i>Errores:</strong>
                            <ul class="mb-1 pl-3">
                                ${result.errors.map(error => `<li class="text-danger">${error}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }

                // Mostrar advertencias
                if (result.warnings.length > 0) {
                    alertHtml += `
                        <div class="mb-2">
                            <strong class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Advertencias:</strong>
                            <ul class="mb-1 pl-3">
                                ${result.warnings.map(warning => `<li class="text-warning">${warning}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }

                alertHtml += `</div>`;
                validationAlerts.append(alertHtml);
            });

            // Mostrar/ocultar sección de validación
            if (validationAlerts.children().length > 0) {
                $('#validationSection').show();
            } else {
                $('#validationSection').hide();
            }

            // Habilitar/deshabilitar botón de generar
            $('#btnGenerate').prop('disabled', hasErrors);

            // Scroll a la sección de validación
            $('html, body').animate({
                scrollTop: $('#validationSection').offset().top - 100
            }, 500);
        }

        function updateBlocksVisualState(results) {
            $('.group-block').removeClass('has-errors has-warnings valid');

            results.validation_results.forEach(result => {
                if (deletedGroups.has(result.group_id)) return;

                const block = $(`.group-item[data-group-id="${result.group_id}"] .group-block`);

                if (result.has_errors) {
                    block.addClass('has-errors');
                } else if (result.has_warnings) {
                    block.addClass('has-warnings');
                } else {
                    block.addClass('valid');
                }
            });
        }

        function generateScheduling() {
            const formData = getFormData();

            $.ajax({
                url: '{{ route('admin.mass-scheduling.store') }}',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#btnGenerate').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin mr-2"></i>Registrando...');
                },
                success: function(response) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire('Error', response.message || 'Error al generar la programación',
                        'error');
                    $('#btnGenerate').prop('disabled', false).html(
                        '<i class="fas fa-calendar-plus mr-2"></i>Registrar Programación');
                }
            });
        }

        // Validar empleados cuando cambian las fechas
        $('#start_date, #end_date').change(function() {
            $('.employee-select').each(function() {
                if ($(this).val()) {
                    $(this).trigger('change');
                }
            });
        });

        // INICIALIZAR EVENTOS AL CARGAR
        initializeGroupEvents();
        initializeTurnFilter();
    });
</script>