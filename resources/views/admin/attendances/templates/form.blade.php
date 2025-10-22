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
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('attendance_date', 'Fecha *') !!}
                    {!! Form::date(
                        'attendance_date',
                        isset($attendance) ? $attendance->formatted_date ?? $attendance->attendance_date->format('Y-m-d') : null,
                        [
                            'class' => 'form-control',
                            'required',
                            'max' => \Carbon\Carbon::now()->format('Y-m-d'),
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
                        isset($attendance) ? $attendance->formatted_time ?? $attendance->attendance_date->format('H:i') : null,
                        [
                            'class' => 'form-control',
                            'required',
                        ],
                    ) !!}
                    <small class="form-text text-muted">Seleccione la hora de registro</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('type', 'Tipo *') !!}
                    {!! Form::select(
                        'type',
                        [
                            'ENTRADA' => 'Entrada',
                            'SALIDA' => 'Salida',
                        ],
                        isset($attendance) ? $attendance->type : null,
                        [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Seleccione el tipo',
                        ],
                    ) !!}
                    <small class="form-text text-muted">Tipo de registro</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('period', 'Período *') !!}
                    {!! Form::select(
                        'period',
                        [
                            1 => 'Mañana',
                            2 => 'Tarde',
                            3 => 'Noche',
                            4 => 'Día completo',
                        ],
                        isset($attendance) ? $attendance->period : null,
                        [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Seleccione el período',
                        ],
                    ) !!}
                    <small class="form-text text-muted">Seleccione el turno o período</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('status', 'Estado *') !!}
                    {!! Form::select(
                        'status',
                        [
                            1 => 'Presente',
                            2 => 'Ausente',
                            3 => 'Tarde',
                            4 => 'Permiso',
                        ],
                        isset($attendance) ? $attendance->status : null,
                        [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Seleccione el estado',
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
            ]) !!}
            <small class="form-text text-muted">Observaciones o comentarios sobre el registro</small>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#employee_select').select2({
            language: "es",
            placeholder: "Buscar por nombre, apellido o DNI...",
            allowClear: true,
            dropdownParent: $('#modal'),
            width: '100%',
            ajax: {
                url: "{{ route('admin.employees.search') }}",
                type: "GET",
                dataType: 'json',
                delay: 300,
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
                                text: employee.name + ' ' + employee.last_name +
                                    ' - DNI: ' + employee.dni,
                                full_name: employee.name + ' ' + employee.last_name,
                                dni: employee.dni,
                                email: employee.email,
                                phone: employee.telefono || 'No registrado'
                            };
                        })
                    };
                },

                cache: true
            },
            minimumInputLength: 2,
            templateResult: formatEmployee,
            templateSelection: formatEmployeeSelection
        });

        $('#employee_select').on('select2:select', function(e) {
            var data = e.params.data;
            showEmployeeInfo(data);
        });

        // Cuando se limpia la selección, ocultar información
        $('#employee_select').on('select2:clear', function(e) {
            hideEmployeeInfo();
        });

        // Formatear cómo se muestran los resultados en el dropdown
        function formatEmployee(employee) {
            if (!employee.id) {
                return employee.text;
            }

            var $container = $(
                '<div class="employee-option">' +
                '<div class="employee-name">' +
                employee.full_name +
                '<span class="badge badge-primary employee-badge">DNI: ' + employee.dni + '</span>' +
                '</div>' +
                '<div class="employee-details">' +
                '<span class="employee-detail-item"><i class="fas fa-envelope"></i>' + (employee.email ||
                    'Sin email') + '</span>' +
                '<span class="employee-detail-item"><i class="fas fa-phone"></i>' + employee.phone +
                '</span>' +
                '</div>' +
                '</div>'
            );

            return $container;
        }

        // Formatear cómo se muestra la selección en el input
        function formatEmployeeSelection(employee) {
            if (!employee.id) {
                return employee.text;
            }
            return employee.full_name + ' - DNI: ' + employee.dni;
        }

        // Mostrar información detallada del empleado
        function showEmployeeInfo(employeeData) {
            $('#info_fullname').text(employeeData.full_name);
            $('#info_dni').text(employeeData.dni);
            $('#info_email').text(employeeData.email || 'No registrado');
            $('#info_phone').text(employeeData.phone || 'No registrado');
            $('#employee_info').removeClass('d-none').addClass('fade-in');
        }

        // Ocultar información del empleado
        function hideEmployeeInfo() {
            $('#employee_info').addClass('d-none');
            $('#info_fullname').text('-');
            $('#info_dni').text('-');
            $('#info_email').text('-');
            $('#info_phone').text('-');
        }

        // Si estamos editando, cargar información del empleado
        @if (isset($attendance) && $attendance->employee_id)
            // Simular datos del empleado para mostrar en la info
            var employeeData = {
                full_name: '{{ $attendance->employee->name }} {{ $attendance->employee->last_name }}',
                dni: '{{ $attendance->employee->dni }}',
                email: '{{ $attendance->employee->email }}',
                phone: '{{ $attendance->employee->phone ?? 'No registrado' }}'
            };
            showEmployeeInfo(employeeData);
        @endif

        // Configurar fecha por defecto a hoy si es nuevo registro
        const attendanceDateInput = document.querySelector('input[name="attendance_date"]');
        if (attendanceDateInput && !attendanceDateInput.value) {
            const today = new Date().toISOString().split('T')[0];
            attendanceDateInput.value = today;
        }

        // Configurar hora por defecto a la hora actual si es nuevo registro
        const attendanceTimeInput = document.querySelector('input[name="attendance_time"]');
        if (attendanceTimeInput && !attendanceTimeInput.value) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            attendanceTimeInput.value = `${hours}:${minutes}`;
        }

        // Validar que la fecha no sea futura
        attendanceDateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();

            if (selectedDate > today) {
                alert('No se puede registrar asistencia con fecha futura');
                this.value = today.toISOString().split('T')[0];
            }
        });

        // Validar que la hora no sea futura si la fecha es hoy
        attendanceTimeInput.addEventListener('change', function() {
            const selectedDate = new Date(attendanceDateInput.value);
            const today = new Date();
            const currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());

            // Solo validar hora si la fecha seleccionada es hoy
            if (selectedDate.getTime() === currentDate.getTime()) {
                const [hours, minutes] = this.value.split(':');
                const selectedTime = new Date();
                selectedTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);

                if (selectedTime > today) {
                    alert('No se puede registrar asistencia con hora futura');
                    const currentHours = String(today.getHours()).padStart(2, '0');
                    const currentMinutes = String(today.getMinutes()).padStart(2, '0');
                    this.value = `${currentHours}:${currentMinutes}`;
                }
            }
        });

        // Agregar animación CSS
        const style = document.createElement('style');
        style.textContent = `
            .fade-in {
                animation: fadeIn 0.5s ease-in;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .select2-container--default .select2-results > .select2-results__options {
                max-height: 300px;
            }
            
            .employee-option {
                padding: 8px 12px;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .employee-option:last-child {
                border-bottom: none;
            }
            
            .employee-option:hover {
                background-color: #f8f9fa;
            }
            
            .employee-name {
                font-size: 14px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 2px;
            }
            
            .employee-details {
                font-size: 12px;
                color: #6c757d;
            }
            
            .employee-detail-item {
                display: inline-block;
                margin-right: 15px;
            }
            
            .employee-detail-item i {
                width: 14px;
                margin-right: 4px;
                color: #7f8c8d;
            }
            
            .employee-badge {
                font-size: 11px;
                padding: 2px 6px;
                border-radius: 10px;
                margin-left: 8px;
                background: #007bff;
                color: white;
            }
        `;
        document.head.appendChild(style);
    });
</script>
