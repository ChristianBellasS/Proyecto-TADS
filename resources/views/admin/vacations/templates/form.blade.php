<div class="row">
    <div class="col-6">
        <div class="form-group">
            
            <!-- {!! Form::label('employee_id', 'Empleado *') !!}
            <select name="employee_id" id="employee_id" class="form-control" required 
                {{ isset($vacation) ? 'disabled' : '' }}
                >
                <option value="">Seleccione empleado</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" 
                        {{ (isset($vacation) && $vacation->employee_id == $employee->id) ? 'selected' : '' }}
                        data-type="{{ $employee->employeetype->name }}">
                        {{ $employee->name }} {{ $employee->last_name }} - {{ $employee->dni }}
                        ({{ $employee->employeetype->name }})
                    </option>
                @endforeach
            </select>
            @if(isset($vacation))
                <input type="hidden" name="employee_id" value="{{ $vacation->employee_id }}">
            @endif  -->
            {!! Form::label('employee_id', 'Empleado *') !!}

            <select name="employee_id" id="employee_id" class="form-control" required>
                <option value=""></option> <!-- OPCIÓN VACÍA -->
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" 
                        {{ (isset($vacation) && $vacation->employee_id == $employee->id) ? 'selected' : '' }}
                        data-type="{{ $employee->employeetype->name }}">
                        {{ $employee->name }} {{ $employee->last_name }} - {{ $employee->dni }}
                        ({{ $employee->employeetype->name }})
                    </option>
                @endforeach
            </select>

        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('requested_days', 'Días Solicitados *') !!}
            {!! Form::number('requested_days', null, [
                'class' => 'form-control', 
                'placeholder' => 'Número de días',
                'min' => 1,
                'max' => 30,
                'required'
            ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="form-group">
            <!-- {!! Form::label('start_date', 'Fecha de Inicio *') !!}
            {!! Form::date('start_date', null, [
                'class' => 'form-control', 
                'required',
                'min' => now()

            ]) !!} -->
            {!! Form::label('start_date', 'Fecha de Inicio *') !!}
            {!! Form::date('start_date', 
                isset($vacation) ? $vacation->start_date->format('Y-m-d') : now()->format('Y-m-d'), 
                [
                    'class' => 'form-control',
                    'required',
                    'min' => now()->format('Y-m-d')
                ]) !!}


        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('end_date', 'Fecha de Fin *') !!}
            {!! Form::date('end_date', 
                isset($vacation) ? $vacation->end_date->format('Y-m-d') : null, 

                [
                'class' => 'form-control',
                'readonly' => true,
                'required'
            ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('notes', 'Notas') !!}
            {!! Form::textarea('notes', null, [
                'class' => 'form-control',
                'placeholder' => 'Observaciones o comentarios sobre la solicitud...',
                'rows' => 3,
            ]) !!}
        </div>
    </div>
</div>

<!-- Información de días disponibles -->
<div class="alert alert-info d-none" id="days-info">
    <i class="fas fa-info-circle"></i>
    <strong>Información:</strong> 
    Este empleado tiene <span id="available-days" class="font-weight-bold">0</span> días de vacaciones disponibles para el año actual.
    <br><small>Máximo 30 días por año según política de la empresa.</small>
</div>

<!-- Validaciones -->
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Importante:</strong>
    <ul class="mb-0 mt-2">
        <li>Solo personal <strong>nombrado</strong> y <strong>contrato permanente</strong> puede solicitar vacaciones</li>
        <li>No se pueden solicitar vacaciones en fechas que coincidan con otras solicitudes aprobadas o pendientes</li>
        <li>Las solicitudes pendientes pueden ser editadas o eliminadas</li>
    </ul>
</div>



<script>
                        // Inicializar Select2 para empleado
            // Inicializar el combo con búsqueda
            $('#employee_id').select2({
                placeholder: 'Buscar empleado por nombre o DNI',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modal'), // <-- Aquí pones el ID del modal

                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    }
                }
            });

            // Si ya hay un empleado seleccionado, lo muestra en Select2
            @if(isset($vacation))
                $('#employee_id').val({{ $vacation->employee_id }}).trigger('change');
            @endif
        // FFin de Select2
</script>