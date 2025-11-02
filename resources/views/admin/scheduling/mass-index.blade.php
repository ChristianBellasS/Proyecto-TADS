{{-- admin/scheduling/mass-index.blade.php --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Nueva Programación</h1>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt mr-2"></i>Seleccione Turno
        </h5>
    </div>
    <div class="card-body">
        <form id="massSchedulingForm">
            @csrf

            <!-- Rango de Fechas -->
            <div class="row mb-4">
                <div class="col-md-6">
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
                <div class="col-md-6">
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
            </div>

            <!-- Botón Validar Disponibilidad -->
            <div class="text-center mb-4">
                <button type="button" class="btn btn-warning btn-lg" id="btnValidate">
                    <i class="fas fa-check-circle mr-2"></i>Validar Disponibilidad
                </button>
            </div>

            <hr>

            <!-- Grupos en Bloques -->
            <div class="mb-4">
                <div id="groupsContainer">
                    @foreach ($groups as $group)
                        <div class="group-block mb-4 p-3 border rounded" data-group-id="{{ $group->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-uppercase mb-0">{{ $group->name }}</h6>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-group" 
                                        data-group-id="{{ $group->id }}" title="Eliminar grupo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2"><strong>Zona:</strong> {{ $group->zone->name ?? 'Sin zona' }}</div>
                                    <div class="mb-2">
                                        <strong>Turno:</strong> 
                                        <span class="badge badge-info ml-2">{{ $group->shift->name ?? 'Sin turno' }}</span>
                                        <input type="hidden" name="groups[{{ $group->id }}][shift_id]" value="{{ $group->shift_id }}">
                                    </div>
                                    <div class="mb-2"><strong>Días:</strong> {{ $group->days }}</div>
                                    <div class="mb-2">
                                        <strong>Vehículo:</strong>
                                        <span class="badge badge-secondary ml-2">
                                            {{ $group->vehicle->code ?? 'Sin vehículo' }} (Capacidad: {{ $group->vehicle->people_capacity ?? 0 }})
                                        </span>
                                        <input type="hidden" name="groups[{{ $group->id }}][vehicle_id]" value="{{ $group->vehicle_id }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Conductor con validación individual -->
                                    <div class="mb-2">
                                        <strong>Conductor:</strong>
                                        <select class="form-control mt-1 driver-select employee-select" 
                                                name="groups[{{ $group->id }}][driver_id]"
                                                data-role="conductor"
                                                data-group-id="{{ $group->id }}">
                                            <option value="">Seleccione conductor</option>
                                            @foreach ($employees->where('employeetype_id', 1) as $employee)
                                                <option value="{{ $employee->id}}"
                                                    {{ $group->driver_id == $employee->id ? 'selected' : '' }}
                                                    data-employee-name="{{ $employee->name }} {{ $employee->last_name }}">
                                                    {{ $employee->name }} {{ $employee->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="employee-validation-result mt-1" data-role="conductor" style="display: none;"></div>
                                    </div>

                                    <!-- Ayudante 1 con validación individual -->
                                    <div class="mb-2">
                                        <strong>Ayudante 1:</strong>
                                        <select class="form-control mt-1 assistant-select employee-select" 
                                                name="groups[{{ $group->id }}][assistant1_id]"
                                                data-role="ayudante1"
                                                data-group-id="{{ $group->id }}">
                                            <option value="">Seleccione ayudante</option>
                                            @foreach ($employees->where('employeetype_id', 2) as $employee)
                                                <option value="{{ $employee->id}}"
                                                    {{ $group->assistant1_id == $employee->id ? 'selected' : '' }}
                                                    data-employee-name="{{ $employee->name }} {{ $employee->last_name }}">
                                                    {{ $employee->name }} {{ $employee->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="employee-validation-result mt-1" data-role="ayudante1" style="display: none;"></div>
                                    </div>

                                    <!-- Ayudante 2 con validación individual -->
                                    <div class="mb-2">
                                        <strong>Ayudante 2:</strong>
                                        <select class="form-control mt-1 assistant-select employee-select" 
                                                name="groups[{{ $group->id }}][assistant2_id]"
                                                data-role="ayudante2"
                                                data-group-id="{{ $group->id }}">
                                            <option value="">Seleccione ayudante</option>
                                            @foreach ($employees->where('employeetype_id', 2) as $employee)
                                                <option value="{{ $employee->id}}"
                                                    {{ $group->assistant2_id == $employee->id ? 'selected' : '' }}
                                                    data-employee-name="{{ $employee->name }} {{ $employee->last_name }}">
                                                    {{ $employee->name }} {{ $employee->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="employee-validation-result mt-1" data-role="ayudante2" style="display: none;"></div>
                                    </div>

                                    @if($group->assistant3_id)
                                    <!-- Ayudante 3 con validación individual -->
                                    <div class="mb-2">
                                        <strong>Ayudante 3:</strong>
                                        <select class="form-control mt-1 assistant-select employee-select" 
                                                name="groups[{{ $group->id }}][assistant3_id]"
                                                data-role="ayudante3"
                                                data-group-id="{{ $group->id }}">
                                            <option value="">Seleccione ayudante</option>
                                            @foreach ($employees->where('employeetype_id', 2) as $employee)
                                                <option value="{{ $employee->id}}"
                                                    {{ $group->assistant3_id == $employee->id ? 'selected' : '' }}
                                                    data-employee-name="{{ $employee->name }} {{ $employee->last_name }}">
                                                    {{ $employee->name }} {{ $employee->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="employee-validation-result mt-1" data-role="ayudante3" style="display: none;"></div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Validación general del grupo -->
                            <div class="group-validation-result mt-2" style="display: none;"></div>
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
    }

    .group-block:hover {
        background-color: #e9ecef;
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
        padding: 10px 15px;
        margin-bottom: 10px;
        border-radius: 4px;
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
</style>

<script>
    $(document).ready(function() {
        let validationResults = null;
        const deletedGroups = new Set();

        // Validación individual de empleados cuando cambian los selects
        $('.employee-select').change(function() {
            const employeeId = $(this).val();
            const role = $(this).data('role');
            const groupId = $(this).data('group-id');
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const validationResult = $(this).siblings('.employee-validation-result');

            // Resetear estado
            $(this).removeClass('has-error has-warning valid');
            validationResult.hide().empty();

            if (!employeeId || !startDate) {
                return;
            }

            // Validar empleado individual
            validateIndividualEmployee(employeeId, role, startDate, endDate, $(this), validationResult);
        });

        // Función para validar empleado individual
        function validateIndividualEmployee(employeeId, role, startDate, endDate, selectElement, validationElement) {
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
                        // Tiene errores críticos
                        cssClass = 'validation-error';
                        selectElement.addClass('has-error').removeClass('has-warning valid');
                        message = '<i class="fas fa-times-circle mr-1"></i>' + response.errors.join(', ');
                    } else if (response.has_warnings) {
                        // Tiene advertencias
                        cssClass = 'validation-warning';
                        selectElement.addClass('has-warning').removeClass('has-error valid');
                        message = '<i class="fas fa-exclamation-triangle mr-1"></i>' + response.warnings.join(', ');
                    } else {
                        // Válido
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

        // Eliminar/restaurar grupo
        $('.delete-group').click(function() {
            const groupId = $(this).data('group-id');
            const block = $(this).closest('.group-block');
            
            if (deletedGroups.has(groupId)) {
                // Reactivar grupo
                deletedGroups.delete(groupId);
                block.removeClass('deleted');
                $(this).removeClass('btn-secondary').addClass('btn-outline-danger')
                      .html('<i class="fas fa-times"></i>');
                block.find('select').prop('disabled', false);
            } else {
                // Eliminar grupo
                deletedGroups.add(groupId);
                block.addClass('deleted');
                $(this).removeClass('btn-outline-danger').addClass('btn-secondary')
                      .html('<i class="fas fa-undo"></i>');
                block.find('select').prop('disabled', true);
            }
            
            // Limpiar validación
            $('#validationSection').hide();
            $('#btnGenerate').prop('disabled', true);
        });

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
                    $('#btnValidate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Validando...');
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
                    $('#btnValidate').prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i>Validar Disponibilidad');
                }
            });
        });

        // Generar programación
        $('#btnGenerate').click(function() {
            if (!validationResults || validationResults.has_errors) {
                Swal.fire('Error', 'No se puede generar la programación con errores de validación', 'error');
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

            $('.group-block').each(function() {
                const block = $(this);
                const groupId = block.data('group-id');
                
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
                    Swal.fire('Error', 'Todos los grupos deben tener al menos un ayudante seleccionado', 'error');
                    return false;
                }
            }

            return true;
        }

        function showValidationResults(results) {
            const validationAlerts = $('#validationAlerts');
            validationAlerts.empty();
            
            let hasErrors = false;
            let hasWarnings = false;

            results.validation_results.forEach(result => {
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
                    hasWarnings = true;
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
            if (results.validation_results.length > 0) {
                $('#validationSection').show();
            }

            // Habilitar/deshabilitar botón de generar
            $('#btnGenerate').prop('disabled', hasErrors);

            // Scroll a la sección de validación
            $('html, body').animate({
                scrollTop: $('#validationSection').offset().top - 100
            }, 500);
        }

        function updateBlocksVisualState(results) {
            // Resetear todos los blocks
            $('.group-block').removeClass('has-errors has-warnings valid');
            
            // Aplicar estados según validación
            results.validation_results.forEach(result => {
                const block = $(`.group-block[data-group-id="${result.group_id}"]`);
                
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
                    $('#btnGenerate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Registrando...');
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
                    Swal.fire('Error', response.message || 'Error al generar la programación', 'error');
                    $('#btnGenerate').prop('disabled', false).html('<i class="fas fa-calendar-plus mr-2"></i>Registrar Programación');
                }
            });
        }

        // Validar empleados cuando cambian las fechas
        $('#start_date, #end_date').change(function() {
            // Validar todos los empleados seleccionados cuando cambian las fechas
            $('.employee-select').each(function() {
                if ($(this).val()) {
                    $(this).trigger('change');
                }
            });
        });
    });
</script>