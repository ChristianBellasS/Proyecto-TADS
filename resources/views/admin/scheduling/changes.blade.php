<form id="changeSchedulingForm" action="{{ route('admin.scheduling.apply-changes', $scheduling->id) }}" method="POST">
    @csrf

    <input type="hidden" name="scheduling_id" value="{{ $scheduling->id }}">
    {{-- <input type="hidden" name="changes" id="changesInput"> --}}

    <div class="row">
        <!-- Sección 1: Cambio de Turno -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-clock mr-2"></i>Cambio de turno</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Turno actual</label>
                        <input type="text" class="form-control current-shift"
                            value="{{ $scheduling->shift->name }} ({{ $scheduling->shift->hour_in }} - {{ $scheduling->shift->hour_out }})"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label>Nuevo Turno</label>
                        <select name="new_shift_id" class="form-control select2 shift-select">
                            <option value="">Seleccione un nuevo turno</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}" data-name="{{ $shift->name }}"
                                    data-hour_in="{{ $shift->hour_in }}" data-hour_out="{{ $shift->hour_out }}">
                                    {{ $shift->name }} ({{ $shift->hour_in }} - {{ $shift->hour_out }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Motivo del cambio</label>
                        <textarea name="shift_reason" class="form-control shift-reason" rows="2"
                            placeholder="Ingrese el motivo del cambio de turno"></textarea>
                    </div>

                    <button type="button" class="btn btn-success btn-sm btn-add-change" data-type="turno">
                        <i class="fas fa-plus mr-1"></i> Agregar cambio
                    </button>
                </div>
            </div>
        </div>

        <!-- Sección 2: Cambio de Vehículo -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-truck mr-2"></i>Cambio de Vehículo</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Vehículo actual</label>
                        <input type="text" class="form-control current-vehicle"
                            value="{{ $scheduling->vehicle->name }} - {{ $scheduling->vehicle->plate }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Nuevo vehículo</label>
                        <select name="new_vehicle_id" class="form-control select2 vehicle-select">
                            <option value="">Seleccione un nuevo vehículo</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" data-name="{{ $vehicle->name }}"
                                    data-plate="{{ $vehicle->plate }}">
                                    {{ $vehicle->name }} - {{ $vehicle->plate }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Motivo del cambio</label>
                        <textarea name="vehicle_reason" class="form-control vehicle-reason" rows="2"
                            placeholder="Ingrese el motivo del cambio de vehículo"></textarea>
                    </div>

                    <button type="button" class="btn btn-success btn-sm btn-add-change" data-type="vehiculo">
                        <i class="fas fa-plus mr-1"></i> Agregar cambio
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-users mr-2"></i>Cambio de Personal</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Personal actual</label>
                        <select name="current_employee_id" class="form-control select2 current-employee-select"
                            id="currentEmployeeSelect">
                            <option value="">Seleccione un personal</option>
                            @foreach ($scheduling->employees as $employee)
                                <option value="{{ $employee->id }}" data-name="{{ $employee->name }}"
                                    data-last_name="{{ $employee->last_name }}" data-dni="{{ $employee->dni }}"
                                    data-role="{{ $employee->pivot->role ?? 'N/A' }}"
                                    data-employee-type="{{ $employee->employeeType->name ?? 'N/A' }}">
                                    {{ $employee->name }} {{ $employee->last_name }} - {{ $employee->dni }}
                                    ({{ $employee->pivot->role ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nuevo personal</label>
                        <select name="new_employee_id" class="form-control select2 new-employee-select"
                            id="newEmployeeSelect" disabled>
                            <option value="">Seleccione una opción</option>
                            <!-- Se llenará dinámicamente -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Motivo del cambio</label>
                        <textarea name="employee_reason" class="form-control employee-reason" rows="2"
                            placeholder="Ingrese el motivo del cambio de personal"></textarea>
                    </div>

                    <button type="button" class="btn btn-success btn-sm btn-add-change" data-type="ocupante"
                        id="btnAddEmployeeChange" disabled>
                        <i class="fas fa-plus mr-1"></i> Agregar cambio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 3: Cambios Registrados -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-list mr-2"></i>Cambios Registrados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="changesTable">
                    <thead>
                        <tr>
                            <th>Tipo de cambio</th>
                            <th>Valor anterior</th>
                            <th>Valor nuevo</th>
                            <th>Motivo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los cambios se agregarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>

            <div id="noChangesMessage" class="text-center py-4">
                <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                <p class="text-muted">No hay cambios registrados. Agregue cambios usando los botones superiores.</p>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="mt-4 text-right">
        <button type="submit" class="btn btn-primary" id="btnSaveChanges" disabled>
            <i class="fas fa-save mr-1"></i> <span id="saveText">Guardar cambios</span>
            <span id="validatingText" style="display: none;">Validando...</span>
        </button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        let changes = [];

        $('.select2').select2({
            theme: 'bootstrap',
            width: '100%',
            placeholder: 'Seleccione una opción',
            allowClear: true
        });

        function updateMainTable() {
            console.log('Actualizando tabla principal...');

            if ($.fn.DataTable.isDataTable('#schedulingTable')) {
                $('#schedulingTable').DataTable().ajax.reload(null, false);
                console.log('DataTable recargado');
                return;
            }

            console.log('Recargando página completa...');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        function checkExistingConflicts() {
            const warningRows = $('#changesTable tbody tr.table-warning');
            return warningRows.length > 0;
        }

        function updateSaveButtonState() {
            const btnSave = $('#btnSaveChanges');
            const hasConflicts = checkExistingConflicts();

            if (changes.length > 0 && !hasConflicts) {
                btnSave.prop('disabled', false);
            } else {
                btnSave.prop('disabled', true);
            }
        }

        function removeValidationStatus(type) {
            $(`[data-type="${type}"]`).closest('.card').find('.validation-status').remove();
        }

        function validateFormFields(type) {
            switch (type) {
                case 'turno':
                    if (!$('.shift-select').val()) {
                        Swal.fire('Error', 'Por favor seleccione un nuevo turno', 'error');
                        return false;
                    }
                    if (!$('.shift-reason').val().trim()) {
                        Swal.fire('Error', 'Por favor ingrese el motivo del cambio', 'error');
                        return false;
                    }
                    break;

                case 'vehiculo':
                    if (!$('.vehicle-select').val()) {
                        Swal.fire('Error', 'Por favor seleccione un nuevo vehículo', 'error');
                        return false;
                    }
                    if (!$('.vehicle-reason').val().trim()) {
                        Swal.fire('Error', 'Por favor ingrese el motivo del cambio', 'error');
                        return false;
                    }
                    break;

                case 'ocupante':
                    if (!$('.current-employee-select').val()) {
                        Swal.fire('Error', 'Por favor seleccione el personal actual', 'error');
                        return false;
                    }
                    if (!$('#newEmployeeSelect').val()) {
                        Swal.fire('Error', 'Por favor seleccione el nuevo personal', 'error');
                        return false;
                    }
                    if (!$('.employee-reason').val().trim()) {
                        Swal.fire('Error', 'Por favor ingrese el motivo del cambio', 'error');
                        return false;
                    }
                    break;
            }
            return true;
        }

        function showChangeRowWarning(index, message) {
            const row = $('#changesTable tbody tr').eq(index);
            row.find('.warning-conflict-btn').remove();
            row.removeClass('table-warning');

            const warningBtn = $(`
                <button type="button" class="btn btn-sm btn-warning mr-1 warning-conflict-btn" 
                        data-message="${message.replace(/"/g, '&quot;')}">
                    <i class="fas fa-exclamation-triangle"></i>
                </button>
            `);

            row.find('.btn-remove-change').before(warningBtn);
            row.addClass('table-warning');

            warningBtn.on('click', function() {
                const conflictMessage = $(this).data('message');
                Swal.fire({
                    icon: 'warning',
                    title: 'Conflicto Detectado',
                    html: `<div class="text-left">
                            <p><strong>Este cambio tiene un conflicto:</strong></p>
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                ${conflictMessage}
                            </div>
                           </div>`,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ffc107',
                    width: '600px'
                });
            });
        }

        // Función para remover advertencia de fila
        function removeChangeRowWarning(index) {
            const row = $('#changesTable tbody tr').eq(index);
            row.find('.warning-conflict-btn').remove();
            row.removeClass('table-warning');
        }

        function validateChange(type) {
            const schedulingId = {{ $scheduling->id }};
            let newValueId, employeeRole;

            switch (type) {
                case 'turno':
                    newValueId = $('.shift-select').val();
                    if (!newValueId) {
                        removeValidationStatus(type);
                        return;
                    }
                    break;
                case 'vehiculo':
                    newValueId = $('.vehicle-select').val();
                    if (!newValueId) {
                        removeValidationStatus(type);
                        return;
                    }
                    break;
                case 'ocupante':
                    newValueId = $('#newEmployeeSelect').val();
                    if (!newValueId) {
                        removeValidationStatus(type);
                        return;
                    }
                    const currentEmployee = $('.current-employee-select').find('option:selected');
                    employeeRole = currentEmployee.data('role');
                    break;
            }

            const pendingChanges = getPendingChangesForValidation(type);

            $.ajax({
                url: `/admin/scheduling/${schedulingId}/validate-change`,
                type: 'POST',
                data: {
                    change_type: type,
                    new_value_id: newValueId,
                    employee_role: employeeRole,
                    pending_changes: pendingChanges,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.valid) {
                        showChangeValidationSuccess(type, response.message);
                    } else {
                        showChangeValidationError(type, response.message, response.errors, response
                            .suggestions);
                    }
                },
                error: function(xhr) {
                    console.error('Error en validación:', xhr);
                    showChangeValidationError(type, 'Error de conexión en la validación');
                }
            });
        }

        function getPendingChangesForValidation(excludeCurrentType = null) {
            const pending = [];

            if (excludeCurrentType !== 'turno') {
                const shiftValue = $('.shift-select').val();
                if (shiftValue) {
                    pending.push({
                        type: 'turno',
                        new_values: {
                            id: parseInt(shiftValue)
                        }
                    });
                }
            }

            if (excludeCurrentType !== 'vehiculo') {
                const vehicleValue = $('.vehicle-select').val();
                if (vehicleValue) {
                    pending.push({
                        type: 'vehiculo',
                        new_values: {
                            id: parseInt(vehicleValue)
                        }
                    });
                }
            }

            if (excludeCurrentType !== 'ocupante') {
                changes.forEach(change => {
                    if (change.type === 'ocupante') {
                        pending.push({
                            type: 'ocupante',
                            new_values: {
                                id: change.new_values.id,
                                role: change.new_values.role
                            }
                        });
                    }
                });
            }

            changes.forEach(change => {
                if (change.type === 'vehiculo' && excludeCurrentType !== 'vehiculo') {
                    pending.push({
                        type: 'vehiculo',
                        new_values: {
                            id: change.new_values.id
                        }
                    });
                }
                if (change.type === 'turno' && excludeCurrentType !== 'turno') {
                    pending.push({
                        type: 'turno',
                        new_values: {
                            id: change.new_values.id
                        }
                    });
                }
            });

            return pending;
        }

        function showChangeValidationSuccess(type, message) {
            $(`[data-type="${type}"]`).closest('.card').find('.validation-status').remove();
            $(`[data-type="${type}"]`).closest('.card').append(`
                <div class="validation-status mt-2">
                    <div class="alert alert-success alert-sm mb-0">
                        <i class="fas fa-check-circle"></i> ${message}
                    </div>
                </div>
            `);
        }

        function showChangeValidationError(type, message, errors = [], suggestions = []) {
            $(`[data-type="${type}"]`).closest('.card').find('.validation-status').remove();

            let html = `<div class="validation-status mt-2">
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="fas fa-exclamation-triangle"></i> <strong>${message}</strong>`;

            if (errors && errors.length > 0) {
                html += '<ul class="mb-0 mt-2">';
                errors.forEach(error => html += `<li>${error}</li>`);
                html += '</ul>';
            }

            if (suggestions && suggestions.length > 0) {
                html += '<div class="mt-2"><strong><i class="fas fa-lightbulb"></i> Sugerencias:</strong><ul>';
                suggestions.forEach(suggestion => html += `<li>${suggestion}</li>`);
                html += '</ul></div>';
            }

            html += '</div></div>';

            $(`[data-type="${type}"]`).closest('.card').append(html);
        }

        // Eventos de validación
        $('.shift-select').on('change', function() {
            if ($(this).val()) {
                validateChange('turno');
            } else {
                removeValidationStatus('turno');
            }
        });

        $('.vehicle-select').on('change', function() {
            if ($(this).val()) {
                validateChange('vehiculo');
            } else {
                removeValidationStatus('vehiculo');
            }
        });

        $('#newEmployeeSelect').on('change', function() {
            if ($(this).val()) {
                validateChange('ocupante');
            } else {
                removeValidationStatus('ocupante');
            }
        });

        // Filtrar nuevos empleados
        $(document).on('change', '#currentEmployeeSelect', function() {
            const selectedOption = $(this).find('option:selected');
            const employeeType = selectedOption.data('employee-type');
            const currentEmployeeId = selectedOption.val();

            const newEmployeeSelect = $('#newEmployeeSelect');
            const btnAddEmployeeChange = $('#btnAddEmployeeChange');

            if (!currentEmployeeId) {
                newEmployeeSelect.prop('disabled', true);
                newEmployeeSelect.empty().append('<option value="">Seleccione una opción</option>');
                newEmployeeSelect.trigger('change.select2');
                btnAddEmployeeChange.prop('disabled', true);
                removeValidationStatus('ocupante');
                return;
            }

            const allEmployees = @json($employees);
            const filteredEmployees = allEmployees.filter(emp => {
                return emp.employee_type_name === employeeType && emp.id != currentEmployeeId;
            });

            newEmployeeSelect.prop('disabled', false);
            newEmployeeSelect.empty().append('<option value="">Seleccione una opción</option>');

            filteredEmployees.forEach(employee => {
                newEmployeeSelect.append(
                    new Option(
                        `${employee.full_name} - ${employee.dni} (${employee.employee_type_name})`,
                        employee.id,
                        false,
                        false
                    )
                );
            });

            newEmployeeSelect.trigger('change.select2');
            btnAddEmployeeChange.prop('disabled', filteredEmployees.length === 0);
        });

        // Agregar cambio
        $('.btn-add-change').click(function() {
            const type = $(this).data('type');

            if (checkExistingConflicts()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Conflictos pendientes',
                    html: 'No puede agregar nuevos cambios mientras haya conflictos en la tabla.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            validateChangeBeforeAdd(type);
        });

        function validateChangeBeforeAdd(type) {
            const schedulingId = {{ $scheduling->id }};
            let newValueId, employeeRole;

            if (!validateFormFields(type)) {
                return;
            }

            switch (type) {
                case 'turno':
                    newValueId = $('.shift-select').val();
                    break;
                case 'vehiculo':
                    newValueId = $('.vehicle-select').val();
                    break;
                case 'ocupante':
                    newValueId = $('#newEmployeeSelect').val();
                    const currentEmployee = $('.current-employee-select').find('option:selected');
                    employeeRole = currentEmployee.data('role');
                    break;
            }

            const pendingChanges = getPendingChangesForValidation();

            $.ajax({
                url: `/admin/scheduling/${schedulingId}/validate-change`,
                type: 'POST',
                data: {
                    change_type: type,
                    new_value_id: newValueId,
                    employee_role: employeeRole,
                    pending_changes: pendingChanges,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.valid) {
                        addChange(type);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            html: response.message
                        });
                        showChangeValidationError(type, response.message, response.errors, response
                            .suggestions);
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Error en la validación del servidor', 'error');
                }
            });
        }

        function addChange(type) {
            if (checkExistingConflicts()) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede agregar',
                    text: 'Existen conflictos en los cambios actuales. Resuélvalos primero.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            let newValue, oldValue, reason, changeData;

            switch (type) {
                case 'turno':
                    const shiftSelect = $('.shift-select');
                    const shiftOption = shiftSelect.find('option:selected');

                    newValue = {
                        id: parseInt(shiftOption.val()),
                        name: shiftOption.data('name'),
                        hour_in: shiftOption.data('hour_in'),
                        hour_out: shiftOption.data('hour_out')
                    };

                    oldValue = {
                        id: parseInt('{{ $scheduling->shift->id }}'),
                        name: '{{ $scheduling->shift->name }}',
                        hour_in: '{{ $scheduling->shift->hour_in }}',
                        hour_out: '{{ $scheduling->shift->hour_out }}'
                    };

                    reason = $('.shift-reason').val();
                    break;

                case 'vehiculo':
                    const vehicleSelect = $('.vehicle-select');
                    const vehicleOption = vehicleSelect.find('option:selected');

                    newValue = {
                        id: parseInt(vehicleOption.val()),
                        name: vehicleOption.data('name'),
                        plate: vehicleOption.data('plate')
                    };

                    oldValue = {
                        id: parseInt('{{ $scheduling->vehicle->id }}'),
                        name: '{{ $scheduling->vehicle->name }}',
                        plate: '{{ $scheduling->vehicle->plate }}'
                    };

                    reason = $('.vehicle-reason').val();
                    break;

                case 'ocupante':
                    const currentEmployeeSelect = $('.current-employee-select');
                    const newEmployeeSelect = $('.new-employee-select');
                    const currentEmployeeOption = currentEmployeeSelect.find('option:selected');
                    const newEmployeeOption = newEmployeeSelect.find('option:selected');

                    const allEmployees = @json($employees);
                    const newEmployeeData = allEmployees.find(emp => emp.id == newEmployeeOption.val());

                    newValue = {
                        id: parseInt(newEmployeeOption.val()),
                        name: newEmployeeData ? newEmployeeData.full_name : newEmployeeOption.text(),
                        dni: newEmployeeData ? newEmployeeData.dni : 'N/A',
                        role: currentEmployeeOption.data('role'),
                        employee_type: newEmployeeData ? newEmployeeData.employee_type_name : 'N/A'
                    };

                    oldValue = {
                        id: parseInt(currentEmployeeOption.val()),
                        name: currentEmployeeOption.data('name') + ' ' + currentEmployeeOption.data(
                            'last_name'),
                        dni: currentEmployeeOption.data('dni'),
                        role: currentEmployeeOption.data('role'),
                        employee_type: currentEmployeeOption.data('employee-type')
                    };

                    reason = $('.employee-reason').val();
                    break;
            }

            changeData = {
                type: type,
                reason: reason,
                old_values: oldValue,
                new_values: newValue
            };

            let existingIndex = -1;

            if (type === 'ocupante') {
                existingIndex = changes.findIndex(change =>
                    change.type === type && change.old_values.id === oldValue.id
                );
            } else {
                existingIndex = changes.findIndex(change => change.type === type);
            }

            if (existingIndex !== -1) {
                changes[existingIndex] = changeData;
            } else {
                changes.push(changeData);
            }

            updateChangesTable();
            updateSaveButton();
            clearForm(type);

            Swal.fire('Éxito', 'Cambio agregado correctamente', 'success');
        }

        function updateChangesTable() {
            const tbody = $('#changesTable tbody');
            const noChangesMessage = $('#noChangesMessage');

            tbody.empty();

            if (changes.length === 0) {
                tbody.hide();
                noChangesMessage.show();
                return;
            }

            noChangesMessage.hide();
            tbody.show();

            changes.forEach((change, index) => {
                let oldValueText, newValueText, typeText;

                switch (change.type) {
                    case 'turno':
                        typeText = 'Turno';
                        oldValueText =
                            `${change.old_values.name} (${change.old_values.hour_in} - ${change.old_values.hour_out})`;
                        newValueText =
                            `${change.new_values.name} (${change.new_values.hour_in} - ${change.new_values.hour_out})`;
                        break;
                    case 'vehiculo':
                        typeText = 'Vehículo';
                        oldValueText = `${change.old_values.name} - ${change.old_values.plate}`;
                        newValueText = `${change.new_values.name} - ${change.new_values.plate}`;
                        break;
                    case 'ocupante':
                        typeText = 'Personal';
                        oldValueText =
                            `${change.old_values.name} - ${change.old_values.dni} (${change.old_values.role})`;
                        newValueText =
                            `${change.new_values.name} - ${change.new_values.dni} (${change.new_values.role})`;
                        break;
                }

                const row = `
                    <tr>
                        <td>${typeText}</td>
                        <td>${oldValueText}</td>
                        <td>${newValueText}</td>
                        <td>${change.reason}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger btn-remove-change" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        function clearForm(type) {
            switch (type) {
                case 'turno':
                    $('.shift-select').val('').trigger('change.select2');
                    $('.shift-reason').val('');
                    removeValidationStatus('turno');
                    break;
                case 'vehiculo':
                    $('.vehicle-select').val('').trigger('change.select2');
                    $('.vehicle-reason').val('');
                    removeValidationStatus('vehiculo');
                    break;
                case 'ocupante':
                    $('.current-employee-select').val('').trigger('change.select2');
                    $('.new-employee-select').val('').trigger('change.select2');
                    $('.employee-reason').val('');
                    $('#newEmployeeSelect').prop('disabled', true)
                        .empty()
                        .append('<option value="">Seleccione una opción</option>')
                        .trigger('change.select2');
                    $('#btnAddEmployeeChange').prop('disabled', true);
                    removeValidationStatus('ocupante');
                    break;
            }
        }

        function updateSaveButton() {
            updateSaveButtonState();
        }

        // Remover cambio
        $(document).on('click', '.btn-remove-change', function() {
            try {
                const index = parseInt($(this).data('index'));
                if (!isNaN(index) && index >= 0 && index < changes.length && changes[index]) {
                    changes.splice(index, 1);
                    updateChangesTable();
                    updateSaveButton();
                } else {
                    updateChangesTable();
                    updateSaveButton();
                }
            } catch (error) {
                console.error('Error al remover cambio:', error);
                updateChangesTable();
                updateSaveButton();
            }
        });

        $('#changeSchedulingForm').submit(function(e) {
            e.preventDefault();

            if (!changes || !Array.isArray(changes) || changes.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Agregue cambios primero',
                    text: 'No hay cambios para guardar. Use los botones "Agregar Cambio" para modificar la programación.',
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'Entendido'
                });
                return false;
            }

            const hasValidChanges = changes.some(change =>
                change &&
                change.type &&
                change.new_values &&
                change.new_values.id
            );

            if (!hasValidChanges) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cambios inválidos',
                    text: 'Los cambios no tienen la estructura correcta.',
                    confirmButtonText: 'Revisar'
                });
                return false;
            }

            if (checkExistingConflicts()) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se puede guardar',
                    html: 'Existen conflictos en los cambios. Resuélvalos antes de guardar.',
                    confirmButtonText: 'Entendido'
                });
                return false;
            }

            $('#saveText').hide();
            $('#validatingText').show();
            $('#btnSaveChanges').prop('disabled', true);

            const schedulingId = {{ $scheduling->id }};

            $.ajax({
                url: `/admin/scheduling/${schedulingId}/validate-all-changes`,
                type: 'POST',
                data: {
                    pending_changes: changes,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#saveText').show();
                    $('#validatingText').hide();
                    $('#btnSaveChanges').prop('disabled', false);

                    if (response.valid) {
                        proceedWithSave();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Conflictos encontrados',
                            html: response.errors.join('<br>')
                        });
                    }
                },
                error: function(xhr) {
                    $('#saveText').show();
                    $('#validatingText').hide();
                    $('#btnSaveChanges').prop('disabled', false);

                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        text: 'No se pudo realizar la validación final. ¿Desea continuar?',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Revisar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            proceedWithSave();
                        }
                    });
                }
            });

            return false;
        });

        function proceedWithSave() {
            if (!changes || changes.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error interno',
                    text: 'No se pudieron recuperar los cambios. Recargue la página.',
                    confirmButtonText: 'Recargar'
                }).then(() => {
                    location.reload();
                });
                return;
            }

            console.log('Cambios a enviar:', changes);

            $('#saveText').hide();
            $('#validatingText').show();
            $('#btnSaveChanges').prop('disabled', true);

            const formData = new FormData();
            formData.append('scheduling_id', {{ $scheduling->id }});
            formData.append('changes', JSON.stringify(changes));
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: $('#changeSchedulingForm').attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    $('#saveText').show();
                    $('#validatingText').hide();
                    $('#btnSaveChanges').prop('disabled', false);

                    console.log('Respuesta:', response);

                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            changes = [];
                            updateChangesTable();
                            updateSaveButtonState();
                            $('#modalChangeScheduling').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error al aplicar cambios'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $('#saveText').show();
                    $('#validatingText').hide();
                    $('#btnSaveChanges').prop('disabled', false);

                    console.error('Error:', xhr.responseJSON);

                    let errorMessage = 'Error al guardar los cambios';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        }
        // Inicializar tabla
        updateChangesTable();
    });
</script>

<style>
    .card-equal-height {
        min-height: 400px;
        display: flex;
        flex-direction: column;
    }

    .card-equal-height .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .card-equal-height .form-group:last-child {
        margin-top: auto;
    }

    .shift-reason,
    .vehicle-reason,
    .employee-reason {
        min-height: 80px;
        resize: vertical;
    }

    .select2-container .select2-selection--single {
        height: 38px;
    }

    .select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }

    .validation-status .alert {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .validation-status .alert ul {
        margin-bottom: 0;
    }

    .validation-status .alert li {
        font-size: 0.8rem;
    }

    .table-warning {
        background-color: #fff3cd !important;
        border-left: 4px solid #ffc107;
    }

    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }

    .employee-item {
        padding: 2px 0;
        font-size: 0.875rem;
        border-bottom: 1px solid #f8f9fa;
    }

    .employee-item:last-child {
        border-bottom: none;
    }
</style>
