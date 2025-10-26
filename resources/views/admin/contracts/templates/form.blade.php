<div class="row">
    <div class="col-12">
        <!-- Campo oculto para position_id -->
        <input type="hidden" name="position_id" id="position_id" value="{{ isset($contract) ? $contract->position_id : '' }}">
        
        <!-- Campo oculto para vacation_days_per_year -->
        <input type="hidden" name="vacation_days_per_year" value="0">

        <!-- Empleado -->
        <div class="form-group">
            <label for="employee_id" class="font-weight-bold">Empleado <span class="text-danger">*</span></label>
            <select name="employee_id" id="employee_id" class="form-control select2-employee" required style="width: 100%;">
                <option value="">Seleccione un empleado</option>
                @if(isset($contract) && $contract->employee)
                    <option value="{{ $contract->employee_id }}" selected data-position="{{ $contract->position_id }}" data-dni="{{ $contract->employee->dni }}" data-position-name="{{ $contract->position->name ?? 'N/A' }}">
                        {{ $contract->employee->full_name }} - {{ $contract->employee->dni }}
                    </option>
                @endif
            </select>
            <small class="form-text text-muted">
                <i class="fas fa-info-circle"></i> Escriba al menos 2 letras para buscar empleados
            </small>
        </div>

        <!-- Tipo de Contrato -->
        <div class="form-group">
            <label for="contract_type" class="font-weight-bold">Tipo de Contrato <span class="text-danger">*</span></label>
            <select name="contract_type" id="contract_type" class="form-control" required disabled>
                <option value="">Primero seleccione un empleado</option>
                <option value="Permanente" {{ (isset($contract) && $contract->contract_type == 'Permanente') ? 'selected' : '' }}>Permanente</option>
                <option value="Nombrado" {{ (isset($contract) && $contract->contract_type == 'Nombrado') ? 'selected' : '' }}>Nombrado</option>
                <option value="Temporal" {{ (isset($contract) && $contract->contract_type == 'Temporal') ? 'selected' : '' }}>Temporal</option>
            </select>
            <small class="form-text text-muted" id="contract_type_help" style="display: none;">
                <i class="fas fa-info-circle text-info"></i> <span id="contract_type_message"></span>
            </small>
        </div>

        <!-- Fechas -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start_date" class="font-weight-bold">Fecha de Inicio <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="{{ isset($contract) ? (\Carbon\Carbon::parse($contract->start_date)->format('Y-m-d')) : '' }}" 
                           required disabled>
                    <small class="form-text text-danger" id="start_date_warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> <span id="start_date_message"></span>
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="end_date" class="font-weight-bold">Fecha de Finalización</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="{{ isset($contract) && $contract->end_date ? (\Carbon\Carbon::parse($contract->end_date)->format('Y-m-d')) : '' }}" 
                           disabled>
                    <small class="form-text text-muted" id="end_date_help">
                        Dejar en blanco si es contrato Permanente o Nombrado
                    </small>
                </div>
            </div>
        </div>

        <!-- Salario y Departamento -->
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="salary" class="font-weight-bold">Salario <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><strong>S/</strong></span>
                        </div>
                        <input type="number" name="salary" id="salary" class="form-control" 
                               step="0.01" min="0" placeholder="0.00" 
                               value="{{ isset($contract) ? $contract->salary : '' }}" 
                               required disabled>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="department_id" class="font-weight-bold">Departamento <span class="text-danger">*</span></label>
                    <select name="department_id" id="department_id" class="form-control" required disabled>
                        <option value="">Seleccione un departamento</option>
                        @if(isset($contract) && $contract->department)
                            <option value="{{ $contract->department_id }}" selected>{{ $contract->department->name }}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <!-- Período de Prueba -->
        <div class="form-group">
            <label for="probation_period_months" class="font-weight-bold">Período de Prueba (meses)</label>
            <input type="number" name="probation_period_months" id="probation_period_months" 
                   class="form-control" min="0" placeholder="0" 
                   value="{{ isset($contract) ? $contract->probation_period_months : '' }}" 
                   disabled>
            <small class="form-text text-muted" id="probation_help">
                Período de prueba para el contrato
            </small>
        </div>

        <!-- Estado del Contrato -->
        <div class="form-group">
            <label class="font-weight-bold d-block">¿Contrato Activo? <span class="text-danger">*</span></label>
            <div class="toggle-switch-container mt-2">
                <label class="toggle-switch">
                    <input type="checkbox" id="is_active_switch" name="is_active" value="1" 
                           {{ (!isset($contract) || $contract->is_active == 1) ? 'checked' : '' }} disabled>
                    <span class="toggle-slider"></span>
                    <span class="toggle-label" id="switch_label">{{ (!isset($contract) || $contract->is_active == 1) ? 'Activo' : 'Inactivo' }}</span>
                </label>
            </div>
        </div>

        <!-- Motivo de Terminación -->
        <div class="form-group" id="termination_reason_group" style="{{ (!isset($contract) || $contract->is_active == 1) ? 'display: none;' : '' }}">
            <label for="termination_reason" class="font-weight-bold">Motivo de Terminación <span class="text-danger">*</span></label>
            <textarea name="termination_reason" id="termination_reason" class="form-control" rows="3" 
                      placeholder="Ingrese el motivo de terminación del contrato">{{ isset($contract) ? $contract->termination_reason : '' }}</textarea>
            <small class="form-text text-muted">
                <i class="fas fa-info-circle text-danger"></i> <strong>Obligatorio</strong> cuando el contrato está inactivo
            </small>
        </div>
    </div>
</div>

<style>
    /* Estilos para el formulario similar a la imagen */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .font-weight-bold {
        font-weight: 600 !important;
        color: #2c3e50;
    }
    
    .form-control {
        border: 1px solid #dce4ec;
        border-radius: 0.375rem;
        padding: 0.75rem 0.75rem; /* Aumentado el padding vertical */
        transition: all 0.3s ease;
        font-size: 1rem;
        height: auto;
        min-height: 48px; /* Altura mínima aumentada */
    }
    
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    
    .form-control:disabled {
        background-color: #f8f9fa;
        opacity: 0.7;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #dce4ec;
        border-right: none;
        min-height: 48px; /* Misma altura que los inputs */
        display: flex;
        align-items: center;
    }
    
    /* Select2 personalizado con más altura */
    .select2-employee + .select2-container .select2-selection--single {
        border: 1px solid #dce4ec;
        border-radius: 0.375rem;
        height: 48px !important; /* Altura aumentada */
        padding: 0.5rem 0.75rem;
    }
    
    .select2-employee + .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 0;
        font-size: 1rem;
    }
    
    .select2-employee + .select2-container .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3498db;
    }
    
    /* Toggle Switch Moderno - Estilo similar a la imagen */
    .toggle-switch-container {
        display: inline-block;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
        gap: 12px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    
    .toggle-slider {
        position: relative;
        width: 60px;
        height: 30px;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        border-radius: 34px;
        transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .toggle-slider:before {
        content: "";
        position: absolute;
        height: 24px;
        width: 24px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        border-radius: 50%;
        transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .toggle-switch input:checked + .toggle-slider {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(39, 174, 96, 0.3);
    }
    
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(30px);
    }
    
    .toggle-label {
        font-weight: 600;
        font-size: 1rem;
        color: #2c3e50;
        transition: color 0.3s ease;
        min-width: 60px;
    }
    
    .toggle-switch input:checked ~ .toggle-label {
        color: #27ae60;
    }
    
    .toggle-switch input:not(:checked) ~ .toggle-label {
        color: #e74c3c;
    }
    
    /* Estilos para campos habilitados/deshabilitados */
    .employee-info {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 2px;
    }
    
    .text-danger {
        color: #e74c3c !important;
    }
    
    .text-muted {
        color: #7f8c8d !important;
    }
    
    /* Mejorar la apariencia de los textareas */
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }
    
    /* Asegurar que todos los inputs tengan la misma altura */
    select.form-control:not(.select2-hidden-accessible) {
        height: 48px;
    }
</style>

<script>
$(document).ready(function() {
    var isEditMode = {{ isset($contract) ? 'true' : 'false' }};
    var currentEmployeeId = null;
    
    // Inicializar Select2 para búsqueda de empleados
    initEmployeeSelect2();

    // Toggle para mostrar/ocultar motivo de terminación
    $('#is_active_switch').on('change', function() {
        updateContractStatus();
    });

    // Cuando se selecciona un empleado
    $('#employee_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var positionId = selectedOption.data('position');
        currentEmployeeId = $(this).val();
        
        if (currentEmployeeId) {
            $('#position_id').val(positionId);
            $('#contract_type').prop('disabled', false);
            
            // Verificar contratos existentes del empleado
            checkEmployeeContracts();
            
            // Limpiar mensajes previos
            $('#contract_type_help').hide();
            $('#start_date_warning').hide();
        } else {
            resetForm();
            hideContractAlert();
        }
    });

    // Cuando se selecciona tipo de contrato
    $('#contract_type').on('change', function() {
        var contractType = $(this).val();
        
        if (contractType) {
            // Validar si es temporal y hay contratos previos
            if (contractType === 'Temporal' && currentEmployeeId) {
                checkLastTemporalContract();
            } else {
                $('#contract_type_help').hide();
                $('#start_date_warning').hide();
            }
            
            // Actualizar campos según tipo de contrato
            updateFieldsByContractType(contractType);
            
            // Cargar departamentos
            loadDepartments();
            
            // Re-verificar contratos con el nuevo tipo
            if (currentEmployeeId) {
                checkEmployeeContracts();
            }
        } else {
            resetFormAfterContractType();
            hideContractAlert();
        }
    });

    // Cuando se selecciona departamento
    $('#department_id').on('change', function() {
        if ($(this).val()) {
            enableRestFields();
        } else {
            disableRestFields();
        }
    });

    // Validar fecha de inicio cuando se cambia
    $('#start_date').on('change', function() {
        var contractType = $('#contract_type').val();
        if (currentEmployeeId) {
            checkEmployeeContracts();
            
            if (contractType === 'Temporal') {
                checkLastTemporalContract();
            }
        }
    });

    // Validar fecha de fin cuando se cambia
    $('#end_date').on('change', function() {
        if (currentEmployeeId) {
            checkEmployeeContracts();
        }
    });

    function updateContractStatus() {
        if ($('#is_active_switch').is(':checked')) {
            $('#switch_label').text('Activo').css('color', '#27ae60');
            $('#termination_reason_group').slideUp();
            $('#termination_reason').prop('required', false);
        } else {
            $('#switch_label').text('Inactivo').css('color', '#e74c3c');
            $('#termination_reason_group').slideDown();
            $('#termination_reason').prop('required', true);
        }
    }

    function updateFieldsByContractType(contractType) {
        // Primero habilitar todos los campos básicos
        $('#start_date, #salary, #probation_period_months, #is_active_switch').prop('disabled', false);
        
        if (contractType === 'Temporal') {
            // Temporal: habilitar fecha fin y actualizar mensaje
            $('#end_date').prop('disabled', false).prop('required', true);
            $('#end_date_help').html('<i class="fas fa-exclamation-circle text-danger"></i> <strong>Obligatorio</strong> para contratos temporales');
            $('#probation_help').text('Período de prueba para contrato temporal');
        } else {
            // Permanente o Nombrado: deshabilitar fecha fin y actualizar mensaje
            $('#end_date').prop('disabled', true).val('').prop('required', false);
            $('#end_date_help').text('Dejar en blanco si es contrato Permanente o Nombrado');
            $('#probation_help').text('Período de prueba para contrato ' + contractType);
        }
        
        // Actualizar estado del contrato
        updateContractStatus();
    }

    function checkLastTemporalContract() {
        if (!currentEmployeeId) return;
        
        $.ajax({
            url: "{{ route('admin.contracts.check-last-temporal') }}",
            type: "GET",
            data: { 
                employee_id: currentEmployeeId,
                exclude_id: isEditMode ? '{{ isset($contract) ? $contract->id : "" }}' : null
            },
            success: function(response) {
                if (response.has_temporal_contract) {
                    var startDateValue = $('#start_date').val();
                    
                    if (!response.can_create) {
                        var temporalMessage = '<strong>⏳ PERÍODO DE ESPERA REQUERIDO</strong><br><br>';
                        temporalMessage += `El último contrato temporal finalizó el <strong>${response.last_end_date_formatted}</strong>.<br>`;
                        temporalMessage += `Puede crear un nuevo contrato temporal a partir del <strong>${response.min_start_date_formatted}</strong> `;
                        temporalMessage += `(2 meses después de la finalización del contrato anterior).`;
                        
                        showContractAlert(temporalMessage, 'danger');
                        
                        // Establecer fecha mínima
                        $('#start_date').attr('min', response.min_start_date);
                        
                        if (startDateValue && new Date(startDateValue) < new Date(response.min_start_date)) {
                            $('#start_date_warning').show();
                            $('#start_date_message').text(
                                'La fecha de inicio debe ser al menos ' + response.min_start_date_formatted
                            );
                        } else {
                            $('#start_date_warning').hide();
                        }
                    } else {
                        var infoMessage = '<strong>ℹ️ INFORMACIÓN DE CONTRATO TEMPORAL</strong><br><br>';
                        infoMessage += `Último contrato temporal finalizó el <strong>${response.last_end_date_formatted}</strong>.<br>`;
                        infoMessage += `Ya puede crear un nuevo contrato temporal.`;
                        
                        showContractAlert(infoMessage, 'info');
                        $('#start_date').attr('min', response.min_start_date);
                        $('#start_date_warning').hide();
                    }
                } else {
                    $('#start_date').removeAttr('min');
                }
            },
            error: function(xhr) {
                console.error('Error al verificar contratos temporales:', xhr);
                hideContractAlert();
            }
        });
    }

    // Función para verificar contratos del empleado
    function checkEmployeeContracts() {
        if (!currentEmployeeId) return;
        
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        
        $.ajax({
            url: "{{ route('admin.contracts.check-employee-contracts') }}",
            type: "GET",
            data: {
                employee_id: currentEmployeeId,
                start_date: startDate,
                end_date: endDate,
                exclude_id: isEditMode ? '{{ isset($contract) ? $contract->id : "" }}' : null
            },
            success: function(response) {
                if (response.error) {
                    console.error('Error del servidor:', response.error);
                    hideContractAlert();
                    $('button[type="submit"]').prop('disabled', false);
                    return;
                }

                if (response.has_contracts) {
                    var message = '';
                    var alertType = 'warning';
                    var disableSubmit = false;
                    
                    if (response.has_overlap && response.overlapping_contracts.length > 0) {
                        // Hay superposición de contratos
                        alertType = 'danger';
                        disableSubmit = true;
                        message = '<strong>❌ CONFLICTO DE CONTRATOS</strong><br><br>';
                        message += 'No se puede crear el contrato debido a superposición con los siguientes contratos existentes:<br><br>';
                        
                        response.overlapping_contracts.forEach(function(contract, index) {
                            message += `<strong>Contrato ${index + 1}:</strong> ${contract.type} `;
                            message += contract.end_date === 'Permanente' 
                                ? `(Permanente desde ${contract.start_date})` 
                                : `(Del ${contract.start_date} al ${contract.end_date})`;
                            message += ` - ${contract.is_active ? '🟢 Activo' : '🔴 Inactivo'}<br>`;
                        });
                        
                        message += '<br><strong>Acción requerida:</strong> Ajuste las fechas o cancele el contrato existente antes de continuar.';
                        
                    } else if (response.has_active_contracts) {
                        // Tiene contratos activos pero no hay superposición
                        alertType = 'warning';
                        disableSubmit = false;
                        message = '<strong>⚠️ EMPLEADO CON CONTRATOS ACTIVOS</strong><br><br>';
                        message += 'Este empleado ya tiene los siguientes contratos activos:<br><br>';
                        
                        response.contracts.filter(c => c.is_active).forEach(function(contract, index) {
                            message += `<strong>Contrato ${index + 1}:</strong> ${contract.type} `;
                            message += contract.end_date === 'Permanente' 
                                ? `(Permanente desde ${contract.start_date})` 
                                : `(Del ${contract.start_date} al ${contract.end_date})`;
                            message += ` - 🟢 Activo<br>`;
                        });
                                                
                    } else {
                        // Tiene contratos pero todos inactivos
                        alertType = 'info';
                        disableSubmit = false;
                        message = '<strong>ℹ️ HISTORIAL DE CONTRATOS</strong><br><br>';
                        message += 'Este empleado tiene contratos anteriores (todos inactivos):<br><br>';
                        
                        response.contracts.slice(0, 3).forEach(function(contract, index) {
                            message += `<strong>Contrato ${index + 1}:</strong> ${contract.type} `;
                            message += contract.end_date === 'Permanente' 
                                ? `(Permanente desde ${contract.start_date})` 
                                : `(Del ${contract.start_date} al ${contract.end_date})`;
                            message += ` - 🔴 Inactivo<br>`;
                        });
                        
                        if (response.contracts.length > 3) {
                            message += `<br>... y ${response.contracts.length - 3} contrato(s) más.`;
                        }
                    }
                    
                    // Mostrar alerta
                    showContractAlert(message, alertType);
                    
                    // Deshabilitar o habilitar el botón de guardar
                    $('button[type="submit"]').prop('disabled', disableSubmit);
                    
                } else {
                    // No tiene contratos previos
                    hideContractAlert();
                    $('button[type="submit"]').prop('disabled', false);
                }
            },
            error: function(xhr) {
                console.error('Error al verificar contratos:', xhr);
                hideContractAlert();
                $('button[type="submit"]').prop('disabled', false);
                
                // Mostrar error genérico
                showContractAlert(
                    '<strong>⚠️ ERROR</strong><br><br>No se pudieron verificar los contratos existentes. Por favor, intente nuevamente.',
                    'warning'
                );
            }
        });
    }

    // Función para mostrar alertas de contratos
    function showContractAlert(message, type = 'warning') {
        var alertClass = '';
        switch(type) {
            case 'danger':
                alertClass = 'alert-danger';
                break;
            case 'warning':
                alertClass = 'alert-warning';
                break;
            case 'info':
                alertClass = 'alert-info';
                break;
            default:
                alertClass = 'alert-warning';
        }
        
        if ($('#contract-alert').length === 0) {
            $('.form-group:first').before(
                `<div id="contract-alert" class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <div id="contract-alert-content" style="font-size: 0.9rem; line-height: 1.4;"></div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`
            );
        }
        
        $('#contract-alert')
            .removeClass('alert-warning alert-danger alert-info')
            .addClass(alertClass)
            .find('#contract-alert-content')
            .html(message)
            .closest('#contract-alert')
            .show();
    }

    // Función para ocultar alerta de contratos
    function hideContractAlert() {
        $('#contract-alert').hide();
    }

    function disableRestFields() {
        $('#start_date, #end_date, #salary, #probation_period_months, #is_active_switch').prop('disabled', true);
    }

    function enableRestFields() {
        var contractType = $('#contract_type').val();
        if (contractType) {
            updateFieldsByContractType(contractType);
        }
    }

    function resetForm() {
        $('#position_id').val('');
        $('#contract_type').prop('disabled', true).val('');
        $('#department_id').prop('disabled', true).val('');
        $('#contract_type_help').hide();
        $('#start_date_warning').hide();
        disableRestFields();
    }

    function resetFormAfterContractType() {
        $('#department_id').prop('disabled', true).val('');
        $('#contract_type_help').hide();
        $('#start_date_warning').hide();
        disableRestFields();
    }

    function loadDepartments() {
        $.ajax({
            url: "{{ route('admin.contracts.get-departments') }}",
            type: "GET",
            success: function(response) {
                var departmentSelect = $('#department_id');
                departmentSelect.empty();
                departmentSelect.append('<option value="">Seleccione un departamento</option>');
                
                @if(isset($contract))
                    var currentDeptId = {{ $contract->department_id }};
                @else
                    var currentDeptId = null;
                @endif
                
                $.each(response, function(index, department) {
                    var selected = currentDeptId == department.id ? 'selected' : '';
                    departmentSelect.append('<option value="' + department.id + '" ' + selected + '>' + department.name + '</option>');
                });
                
                departmentSelect.prop('disabled', false);
                
                // Si estamos en modo edición y ya hay departamento seleccionado, habilitar campos
                if (isEditMode && currentDeptId) {
                    enableRestFields();
                }
            },
            error: function(xhr) {
                console.error('Error al cargar departamentos:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar departamentos'
                });
            }
        });
    }

    function initEmployeeSelect2() {
        $('#employee_id').select2({
            placeholder: 'Escriba al menos 2 letras para buscar...',
            allowClear: true,
            minimumInputLength: 2,
            language: {
                noResults: function() {
                    return "No se encontraron empleados";
                },
                searching: function() {
                    return "Buscando...";
                },
                inputTooShort: function(args) {
                    return "Escriba al menos " + args.minimum + " caracteres";
                }
            },
            ajax: {
                url: "{{ route('admin.contracts.search-employees') }}",
                type: "GET",
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results || data
                    };
                },
                cache: true
            },
            templateResult: formatEmployee,
            templateSelection: formatEmployeeSelection,
            dropdownParent: $('#modal')
        });
    }

    function formatEmployee(employee) {
        if (employee.loading) {
            return employee.text;
        }
        
        var $container = $(
            '<div class="employee-item">' +
                '<div class="font-weight-bold">' + employee.text + '</div>' +
                '<div class="employee-info">' +
                    '<strong>DNI:</strong> ' + (employee.dni || '') + ' | ' +
                    '<strong>Posición:</strong> ' + (employee.position_name || '') +
                '</div>' +
            '</div>'
        );
        
        return $container;
    }

    function formatEmployeeSelection(employee) {
        if (employee.id === "") {
            return employee.text;
        }
        
        // Guardar datos para usar después
        if (employee.position_id) {
            $('#position_id').val(employee.position_id);
        }
        
        return employee.text + (employee.dni ? ' - ' + employee.dni : '');
    }

    // Si estamos en modo edición, inicializar correctamente
    if (isEditMode) {
        currentEmployeeId = {{ isset($contract) ? $contract->employee_id : 'null' }};
        $('#employee_id, #contract_type').prop('disabled', false);
        
        // Configurar campos según tipo de contrato actual
        var currentContractType = $('#contract_type').val();
        if (currentContractType) {
            updateFieldsByContractType(currentContractType);
        }
        
        // Cargar departamentos
        setTimeout(function() {
            loadDepartments();
            
            // Verificar contratos existentes después de un delay
            if (currentEmployeeId) {
                setTimeout(function() {
                    checkEmployeeContracts();
                    
                    // Verificar restricción si es temporal
                    if (currentContractType === 'Temporal') {
                        checkLastTemporalContract();
                    }
                }, 1000);
            }
        }, 500);
    }
});
</script>