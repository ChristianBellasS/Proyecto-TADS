<div class="row">
    <div class="col-12">
        <!-- Campo oculto para position_id -->
        <input type="hidden" name="position_id" id="position_id" value="{{ isset($contract) ? $contract->position_id : '' }}">
        
        <!-- Campo oculto para vacation_days_per_year -->
        <input type="hidden" name="vacation_days_per_year" value="0">

        <div class="form-group">
            {!! Form::label('employee_id', 'Empleado') !!}
            <span class="text-danger">*</span>
            <select name="employee_id" id="employee_id" class="form-control select2" required>
                <option value="">Seleccione un empleado</option>
            </select>
        </div>

        <div class="form-group">
            {!! Form::label('contract_type', 'Tipo de Contrato') !!}
            <span class="text-danger">*</span>
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

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('start_date', 'Fecha de Inicio') !!}
                    <span class="text-danger">*</span>
                    {!! Form::date('start_date', isset($contract) ? $contract->start_date : null, ['class' => 'form-control', 'required', 'disabled', 'id' => 'start_date']) !!}
                    <small class="form-text text-danger" id="start_date_warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i> <span id="start_date_message"></span>
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('end_date', 'Fecha de Finalización') !!}
                    <span class="text-danger" id="end_date_required" style="display: none;">*</span>
                    {!! Form::date('end_date', isset($contract) ? $contract->end_date : null, ['class' => 'form-control', 'disabled', 'id' => 'end_date']) !!}
                    <small class="form-text text-muted" id="end_date_help">Dejar en blanco si es contrato Permanente o Nombrado</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('salary', 'Salario') !!}
                    <span class="text-danger">*</span>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><strong>S/</strong></span>
                        </div>
                        {!! Form::number('salary', isset($contract) ? $contract->salary : null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'placeholder' => '0.00', 'required', 'disabled']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('department_id', 'Departamento') !!}
                    <span class="text-danger">*</span>
                    <select name="department_id" id="department_id" class="form-control" required disabled>
                        <option value="">Primero seleccione tipo de contrato</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('probation_period_months', 'Período de Prueba (meses)') !!}
            {!! Form::number('probation_period_months', isset($contract) ? $contract->probation_period_months : '', ['class' => 'form-control', 'min' => '0', 'placeholder' => '0', 'disabled']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('is_active_label', '¿Contrato Activo?') !!}
            <span class="text-danger">*</span>
            <br>
            <div class="custom-control custom-switch custom-switch-modern mt-2">
                <input type="checkbox" class="custom-control-input" id="is_active_switch" name="is_active" value="1" {{ (!isset($contract) || $contract->is_active == 1) ? 'checked' : '' }} disabled>
                <label class="custom-control-label" for="is_active_switch">
                    <span class="switch-status" id="switch_label">{{ (!isset($contract) || $contract->is_active == 1) ? 'Activo' : 'Inactivo' }}</span>
                </label>
            </div>
        </div>

        <div class="form-group" id="termination_reason_group" style="{{ (!isset($contract) || $contract->is_active == 1) ? 'display: none;' : '' }}">
            {!! Form::label('termination_reason', 'Motivo de Terminación') !!}
            {!! Form::textarea('termination_reason', isset($contract) ? $contract->termination_reason : null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Ingrese el motivo de terminación del contrato', 'disabled']) !!}
            <small class="form-text text-muted">
                <i class="fas fa-info-circle"></i> Solo aplica si el contrato no está activo
            </small>
        </div>
    </div>
</div>

<style>
    /* Toggle Switch Moderno y Elegante */
    .custom-switch-modern .custom-control-label {
        padding-left: 3.5rem;
        padding-top: 0.3rem;
        cursor: pointer;
        user-select: none;
    }
    
    .custom-switch-modern .custom-control-label::before {
        height: 2rem;
        width: 4rem;
        border-radius: 2rem;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        border: none;
        transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .custom-switch-modern .custom-control-label::after {
        width: 1.6rem;
        height: 1.6rem;
        border-radius: 50%;
        background: white;
        box-shadow: 0 3px 8px rgba(0,0,0,0.3);
        transition: all 0.4s cubic-bezier(0.4, 0.0, 0.2, 1);
        top: 0.2rem;
        left: -2.1rem;
    }
    
    .custom-switch-modern .custom-control-input:checked ~ .custom-control-label::before {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(39, 174, 96, 0.3);
    }
    
    .custom-switch-modern .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(2rem);
    }

    .switch-status {
        font-weight: 600;
        font-size: 1rem;
        transition: color 0.3s ease;
    }

    .custom-switch-modern .custom-control-input:checked ~ .custom-control-label .switch-status {
        color: #27ae60;
    }

    .custom-switch-modern .custom-control-input:not(:checked) ~ .custom-control-label .switch-status {
        color: #e74c3c;
    }

    .select2-container {
        width: 100% !important;
    }
</style>

<script>
$(document).ready(function() {
    var isEditMode = {{ isset($contract) ? 'true' : 'false' }};
    var currentEmployeeId = null;
    
    if (isEditMode) {
        loadAllEmployeesForEdit();
    } else {
        loadAllEmployees();
    }

    // Toggle para mostrar/ocultar motivo de terminación
    $('#is_active_switch').on('change', function() {
        if ($(this).is(':checked')) {
            $('#switch_label').text('Activo');
            $('#termination_reason_group').slideUp();
            $('#termination_reason').val('');
        } else {
            $('#switch_label').text('Inactivo');
            $('#termination_reason_group').slideDown();
        }
    });

    // Cuando se selecciona un empleado
    $('#employee_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var positionId = selectedOption.data('position');
        currentEmployeeId = $(this).val();
        
        if (currentEmployeeId) {
            $('#position_id').val(positionId);
            $('#contract_type').prop('disabled', false);
            
            // Limpiar mensajes previos
            $('#contract_type_help').hide();
            $('#start_date_warning').hide();
        } else {
            $('#position_id').val('');
            $('#contract_type').prop('disabled', true).val('');
            $('#department_id').prop('disabled', true).val('');
            $('#contract_type_help').hide();
            $('#start_date_warning').hide();
            disableRestFields();
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
            
            // Actualizar requerimiento de fecha fin
            if (contractType === 'Temporal') {
                $('#end_date').attr('required', true);
                $('#end_date_required').show();
                $('#end_date_help').html('<i class="fas fa-exclamation-circle text-danger"></i> <strong>Obligatorio</strong> para contratos temporales');
            } else {
                $('#end_date').removeAttr('required');
                $('#end_date_required').hide();
                $('#end_date_help').html('Dejar en blanco si es contrato Permanente o Nombrado');
            }
            
            loadDepartments();
        } else {
            $('#department_id').prop('disabled', true).val('');
            $('#contract_type_help').hide();
            $('#start_date_warning').hide();
            disableRestFields();
        }
    });

    // Cuando se selecciona departamento, habilitar resto de campos
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
        if (contractType === 'Temporal' && currentEmployeeId) {
            checkLastTemporalContract();
        }
    });

    function checkLastTemporalContract() {
        $.ajax({
            url: "{{ route('admin.contracts.check-last-temporal') }}",
            type: "GET",
            data: { employee_id: currentEmployeeId },
            success: function(response) {
                if (response.has_temporal_contract) {
                    var startDateValue = $('#start_date').val();
                    
                    if (!response.can_create) {
                        $('#contract_type_help').show();
                        $('#contract_type_message').html(
                            '<strong>Restricción:</strong> El último contrato temporal finalizó el ' + 
                            response.last_end_date_formatted + '. Puede crear un nuevo contrato a partir del ' + 
                            response.min_start_date_formatted + ' (2 meses después).'
                        );
                        
                        // Establecer fecha mínima
                        $('#start_date').attr('min', response.min_start_date);
                        
                        if (startDateValue && startDateValue < response.min_start_date) {
                            $('#start_date_warning').show();
                            $('#start_date_message').text(
                                'La fecha de inicio debe ser al menos ' + response.min_start_date_formatted
                            );
                        } else {
                            $('#start_date_warning').hide();
                        }
                    } else {
                        $('#contract_type_help').show();
                        $('#contract_type_message').html(
                            '<strong>Información:</strong> Último contrato temporal finalizó el ' + 
                            response.last_end_date_formatted + '. Ya puede crear un nuevo contrato.'
                        );
                        $('#start_date').attr('min', response.min_start_date);
                        $('#start_date_warning').hide();
                    }
                } else {
                    $('#contract_type_help').hide();
                    $('#start_date_warning').hide();
                    $('#start_date').removeAttr('min');
                }
            },
            error: function(xhr) {
                console.error('Error al verificar contratos:', xhr);
            }
        });
    }

    function disableRestFields() {
        $('#start_date, #end_date, #salary, #probation_period_months, #is_active_switch, #termination_reason').prop('disabled', true);
    }

    function enableRestFields() {
        $('#start_date, #salary, #probation_period_months, #is_active_switch').prop('disabled', false);
        $('#end_date, #termination_reason').prop('disabled', false);
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

    function loadAllEmployees() {
        $.ajax({
            url: "{{ route('admin.contracts.get-all-employees') }}",
            type: "GET",
            success: function(response) {
                var employeeSelect = $('#employee_id');
                employeeSelect.empty();
                employeeSelect.append('<option value="">Seleccione un empleado</option>');
                
                $.each(response, function(index, employee) {
                    employeeSelect.append('<option value="' + employee.id + '" data-position="' + employee.position_id + '">' + employee.full_name + '</option>');
                });
                
                initSelect2();
            },
            error: function(xhr) {
                console.error('Error al cargar empleados:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar empleados'
                });
            }
        });
    }

    function loadAllEmployeesForEdit() {
        $.ajax({
            url: "{{ route('admin.contracts.get-all-employees') }}",
            type: "GET",
            success: function(response) {
                var employeeSelect = $('#employee_id');
                employeeSelect.empty();
                employeeSelect.append('<option value="">Seleccione un empleado</option>');
                
                @if(isset($contract))
                    var currentEmployeeId = {{ $contract->employee_id }};
                    var currentPositionId = {{ $contract->position_id }};
                @endif
                
                $.each(response, function(index, employee) {
                    var selected = currentEmployeeId == employee.id ? 'selected' : '';
                    employeeSelect.append('<option value="' + employee.id + '" data-position="' + employee.position_id + '" ' + selected + '>' + employee.full_name + '</option>');
                });
                
                $('#position_id').val(currentPositionId);
                $('#employee_id, #contract_type').prop('disabled', false);
                
                currentEmployeeId = {{ isset($contract) ? $contract->employee_id : 'null' }};
                
                loadDepartments();
                
                setTimeout(function() {
                    enableRestFields();
                    
                    // Verificar restricción si es temporal
                    var contractType = $('#contract_type').val();
                    if (contractType === 'Temporal') {
                        checkLastTemporalContract();
                    }
                }, 500);
                
                initSelect2();
            },
            error: function(xhr) {
                console.error('Error al cargar empleados:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar empleados'
                });
            }
        });
    }

    function initSelect2() {
        if ($('#employee_id').hasClass("select2-hidden-accessible")) {
            $('#employee_id').select2('destroy');
        }
        
        $('#employee_id').select2({
            placeholder: 'Buscar empleado por nombre...',
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            },
            dropdownParent: $('#modal')
        });
    }
});
</script>