<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('employee_id', 'Empleado *') !!}
            <select name="employee_id" id="employee_select" class="form-control" required>
                <option value="">Seleccione un empleado</option>
                @if (isset($attendance) && $attendance->employee_id)
                    <option value="{{ $attendance->employee_id }}" selected>
                        {{ $attendance->employee->name }} {{ $attendance->employee->last_name }} -
                        {{ $attendance->employee->dni }}
                    </option>
                @elseif(isset($employeeId))
                    <option value="{{ $employeeId }}" selected></option>
                @endif
            </select>
            <small class="form-text text-muted">Busque por nombre, apellido o DNI del empleado</small>
        </div>

        <!-- Información del empleado seleccionado -->
        <div id="employee_info" class="alert alert-info d-none">
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
            <div id="attendance_info" class="mt-3 p-2 border rounded" style="display: none;">
                <h6 class="mb-2"><i class="fas fa-history"></i> Registros del día:</h6>
                <div id="today_records"></div>
                <div id="suggestion_info" class="mt-2 p-2 rounded" style="display: none;">
                    <small><i class="fas fa-info-circle"></i> <span id="suggestion_text"></span></small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('attendance_date', 'Fecha *') !!}
                    {!! Form::date(
                        'attendance_date',
                        isset($attendance)
                            ? $attendance->formatted_date ?? $attendance->attendance_date->format('Y-m-d')
                            : $attendanceDate ?? now()->format('Y-m-d'),
                        [
                            'class' => 'form-control',
                            'required',
                            'max' => \Carbon\Carbon::now()->format('Y-m-d'),
                            'id' => 'attendance_date_input',
                        ],
                    ) !!}
                    <small class="form-text text-muted">Seleccione la fecha de asistencia</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('attendance_time', 'Hora *') !!}
                    {!! Form::time(
                        'attendance_time',
                        isset($attendance)
                            ? $attendance->formatted_time ?? $attendance->attendance_date->format('H:i')
                            : now()->format('H:i'),
                        [
                            'class' => 'form-control',
                            'required',
                            'id' => 'attendance_time_input',
                        ],
                    ) !!}
                    <small class="form-text text-muted">Seleccione la hora de registro</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('type', 'Tipo *') !!}
                    {!! Form::select(
                        'type',
                        [
                            'ENTRADA' => 'Entrada',
                            'SALIDA' => 'Salida',
                        ],
                        isset($attendance) ? $attendance->type : $suggestedType ?? 'ENTRADA',
                        [
                            'class' => 'form-control',
                            'required',
                            'id' => 'type_select',
                        ],
                    ) !!}
                    <small class="form-text text-muted" id="type_help">
                        Tipo de registro
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('status', 'Estado *') !!}
                    {!! Form::select(
                        'status',
                        [
                            1 => 'Presente',
                            2 => 'Tarde',
                        ],
                        isset($attendance) ? $attendance->status : null,
                        [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Seleccione el estado',
                            'id' => 'status_select',
                        ],
                    ) !!}
                    <small class="form-text text-muted">Estado de la asistencia</small>
                </div>
            </div>
        </div>

        <div class="form-group mb-2">
            {!! Form::label('notes', 'Notas') !!}
            {!! Form::textarea('notes', isset($attendance) ? $attendance->notes : null, [
                'class' => 'form-control',
                'placeholder' => 'Agregue notas adicionales sobre la asistencia...',
                'rows' => 2,
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