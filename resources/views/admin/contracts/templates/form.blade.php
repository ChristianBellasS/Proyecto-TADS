<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('position_id', 'Posición') !!}
            <span class="text-danger">*</span>
            <select name="position_id" id="position_id" class="form-control" required>
                <option value="">Seleccione una posición primero</option>
                @foreach($positions as $position)
                    <option value="{{ $position->id }}" {{ (isset($contract) && $contract->position_id == $position->id) ? 'selected' : '' }}>
                        {{ $position->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            {!! Form::label('employee_id', 'Empleado') !!}
            <span class="text-danger">*</span>
            <select name="employee_id" id="employee_id" class="form-control" required disabled>
                <option value="">Primero seleccione una posición</option>
                @if(isset($contract))
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ $contract->employee_id == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            {!! Form::label('contract_type', 'Tipo de Contrato') !!}
            <span class="text-danger">*</span>
            <select name="contract_type" id="contract_type" class="form-control" required disabled>
                <option value="">Primero seleccione un empleado</option>
                <option value="Indefinido" {{ (isset($contract) && $contract->contract_type == 'Indefinido') ? 'selected' : '' }}>Indefinido</option>
                <option value="Temporal" {{ (isset($contract) && $contract->contract_type == 'Temporal') ? 'selected' : '' }}>Temporal</option>
                <option value="Por Obra o Servicio" {{ (isset($contract) && $contract->contract_type == 'Por Obra o Servicio') ? 'selected' : '' }}>Por Obra o Servicio</option>
                <option value="Prácticas" {{ (isset($contract) && $contract->contract_type == 'Prácticas') ? 'selected' : '' }}>Prácticas</option>
                <option value="Nombrado" {{ (isset($contract) && $contract->contract_type == 'Nombrado') ? 'selected' : '' }}>Nombrado</option>

            </select>
        </div>

        <div class="form-group">
            {!! Form::label('department_id', 'Departamento') !!}
            <span class="text-danger">*</span>
            <select name="department_id" id="department_id" class="form-control" required disabled>
                <option value="">Primero seleccione tipo de contrato</option>
                @if(isset($contract))
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $contract->department_id == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="form-group">
            {!! Form::label('start_date', 'Fecha de Inicio') !!}
            <span class="text-danger">*</span>
            {!! Form::date('start_date', isset($contract) ? $contract->start_date : null, ['class' => 'form-control', 'required', 'disabled' => !isset($contract)]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('end_date', 'Fecha de Fin') !!}
            {!! Form::date('end_date', isset($contract) ? $contract->end_date : null, ['class' => 'form-control', 'disabled' => !isset($contract)]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('salary', 'Salario') !!}
            <span class="text-danger">*</span>
            {!! Form::number('salary', isset($contract) ? $contract->salary : null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'placeholder' => '0.00', 'required', 'disabled' => !isset($contract)]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('vacation_days_per_year', 'Días de Vacaciones por Año') !!}
            <span class="text-danger">*</span>
            {!! Form::number('vacation_days_per_year', isset($contract) ? $contract->vacation_days_per_year : 0, ['class' => 'form-control', 'min' => '0', 'required', 'disabled' => !isset($contract)]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('probation_period_months', 'Meses de Prueba') !!}
            <span class="text-danger">*</span>
            {!! Form::number('probation_period_months', isset($contract) ? $contract->probation_period_months : 0, ['class' => 'form-control', 'min' => '0', 'required', 'disabled' => !isset($contract)]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('is_active', '¿Contrato Activo?') !!}
            <span class="text-danger">*</span>
            <select name="is_active" id="is_active" class="form-control" required disabled>
                <option value="1" {{ (!isset($contract) || $contract->is_active == 1) ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ (isset($contract) && $contract->is_active == 0) ? 'selected' : '' }}>No</option>
            </select>
        </div>

        <div class="form-group">
            {!! Form::label('termination_reason', 'Motivo de Terminación') !!}
            {!! Form::textarea('termination_reason', isset($contract) ? $contract->termination_reason : null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Opcional', 'disabled' => !isset($contract)]) !!}
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Si estamos editando, habilitar todos los campos
    @if(isset($contract))
        enableAllFields();
    @endif

    // Cuando se selecciona una posición, cargar empleados
    $('#position_id').change(function() {
        var positionId = $(this).val();
        
        if (positionId) {
            $.ajax({
                url: "{{ route('admin.contracts.get-employees-by-position') }}",
                type: "GET",
                data: { position_id: positionId },
                success: function(response) {
                    var employeeSelect = $('#employee_id');
                    employeeSelect.empty();
                    employeeSelect.append('<option value="">Seleccione un empleado</option>');
                    
                    $.each(response, function(index, employee) {
                        employeeSelect.append('<option value="' + employee.id + '">' + employee.full_name + '</option>');
                    });
                    
                    employeeSelect.prop('disabled', false);
                    
                    // Deshabilitar campos siguientes
                    $('#contract_type').prop('disabled', true).val('');
                    $('#department_id').prop('disabled', true).val('');
                    disableRestFields();
                },
                error: function() {
                    alert('Error al cargar empleados');
                }
            });
        } else {
            $('#employee_id').prop('disabled', true).empty().append('<option value="">Primero seleccione una posición</option>');
            $('#contract_type').prop('disabled', true).val('');
            $('#department_id').prop('disabled', true).val('');
            disableRestFields();
        }
    });

    // Cuando se selecciona un empleado, habilitar tipo de contrato
    $('#employee_id').change(function() {
        if ($(this).val()) {
            $('#contract_type').prop('disabled', false);
        } else {
            $('#contract_type').prop('disabled', true).val('');
            $('#department_id').prop('disabled', true).val('');
            disableRestFields();
        }
    });

    // Cuando se selecciona tipo de contrato, cargar departamentos
    $('#contract_type').change(function() {
        if ($(this).val()) {
            $.ajax({
                url: "{{ route('admin.contracts.get-departments') }}",
                type: "GET",
                success: function(response) {
                    var departmentSelect = $('#department_id');
                    departmentSelect.empty();
                    departmentSelect.append('<option value="">Seleccione un departamento</option>');
                    
                    $.each(response, function(index, department) {
                        departmentSelect.append('<option value="' + department.id + '">' + department.name + '</option>');
                    });
                    
                    departmentSelect.prop('disabled', false);
                },
                error: function() {
                    alert('Error al cargar departamentos');
                }
            });
        } else {
            $('#department_id').prop('disabled', true).val('');
            disableRestFields();
        }
    });

    // Cuando se selecciona departamento, habilitar resto de campos
    $('#department_id').change(function() {
        if ($(this).val()) {
            enableRestFields();
        } else {
            disableRestFields();
        }
    });

    function disableRestFields() {
        $('#start_date, #end_date, #salary, #vacation_days_per_year, #probation_period_months, #is_active, #termination_reason').prop('disabled', true);
    }

    function enableRestFields() {
        $('#start_date, #salary, #vacation_days_per_year, #probation_period_months, #is_active').prop('disabled', false);
        $('#end_date, #termination_reason').prop('disabled', false);
    }

    function enableAllFields() {
        $('#employee_id, #contract_type, #department_id, #start_date, #end_date, #salary, #vacation_days_per_year, #probation_period_months, #is_active, #termination_reason').prop('disabled', false);
    }
});
</script>