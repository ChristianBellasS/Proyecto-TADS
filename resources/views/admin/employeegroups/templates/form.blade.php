<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('name', 'Nombre del Grupo *') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ej: GRUPO ZONA A', 'required']) !!}
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('shift_id', 'Turno *') !!}
            {!! Form::select('shift_id', $shifts->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione un turno'
            ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('zone_id', 'Zona *') !!}
            {!! Form::select('zone_id', $zones->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione una zona'
            ]) !!}
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('vehicle_id', 'Vehículo *') !!}
            <select name="vehicle_id" id="vehicle_id" class="form-control" 
                @if(isset($isEdit) && $isEdit) disabled @endif 
                required 
                onchange="updateAssistantsFields()">
                <option value="">Seleccione un vehículo</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" 
                            data-capacity="{{ $vehicle->people_capacity ?? 1 }}"
                            @if(isset($group) && $group->vehicle_id == $vehicle->id) selected @endif>
                        {{ $vehicle->plate }} - {{ $vehicle->name }} (Capacidad: {{ $vehicle->people_capacity ?? 1 }})
                        @if(isset($isEdit) && $isEdit && $group->vehicle_id == $vehicle->id)
                            (Asignado actualmente)
                        @endif
                    </option>
                @endforeach
            </select>
            
            @if(isset($isEdit) && $isEdit)
                {!! Form::hidden('vehicle_id', $group->vehicle_id) !!}
                <small class="form-text text-warning">
                    <i class="fas fa-info-circle"></i> El vehículo no puede ser modificado en edición
                </small>
            @endif
            
            <small class="form-text text-muted" id="capacity_info"></small>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('days', 'Días de trabajo *') !!}
    <div class="row">
        @php
            $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $selectedDays = isset($group) ? explode(',', $group->days) : [];
        @endphp
        @foreach($days as $day)
            <div class="col-md-4">
                <div class="form-check">
                    {!! Form::checkbox('days[]', $day, in_array($day, $selectedDays), [
                        'class' => 'form-check-input',
                        'id' => 'day_' . strtolower($day)
                    ]) !!}
                    {!! Form::label('day_' . strtolower($day), $day, ['class' => 'form-check-label']) !!}
                </div>
            </div>
        @endforeach
    </div>
</div>

<hr>
<small class="text-muted">Estos datos son para pre configuración no son obligatorios</small>

<!-- Conductor -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('driver_search', 'Conductor') !!}
            <input type="text" class="form-control" 
                   id="driver_search" 
                   placeholder="Escriba nombre, apellido o DNI para buscar conductores..."
                   oninput="searchEmployees(this, 1, 'driver_id', 'selected_driver', 'driver_results')">
            {!! Form::hidden('driver_id', isset($group) ? $group->driver_id : null, ['id' => 'driver_id']) !!}
            <div id="driver_results" class="search-results"></div>
            <div id="selected_driver" class="selected-employee mt-2">
                @if(isset($group) && $group->driver)
                    <div class="alert alert-success py-2 position-relative">
                        <i class="fas fa-check-circle"></i> 
                        <strong>{{ $group->driver->name }} {{ $group->driver->last_name }}</strong>
                        <small class="d-block">DNI: {{ $group->driver->dni }} | {{ $group->driver->employeeType->name ?? 'N/A' }}</small>
                        <button type="button" class="close position-absolute" style="top: 5px; right: 10px;" 
                                onclick="clearEmployeeSelection('driver_id', 'selected_driver')">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Ayudantes -->
<div class="row" id="assistants_container">
    <!-- Los campos de ayudantes se generarán dinámicamente aquí -->
</div>

<!-- Mensaje general de error -->
<div id="form_errors" class="alert alert-danger d-none mt-3">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>No se puede guardar el grupo:</strong>
    <ul id="error_list" class="mb-0 mt-2"></ul>
</div>

<script>
// Variable global para controlar empleados con conflictos
let conflictedEmployees = new Set();

// Función para verificar disponibilidad del empleado
function checkEmployeeAvailability(employeeId, fieldName, currentGroupId = null) {
    if (!employeeId) {
        hideEmployeeWarning(fieldName);
        conflictedEmployees.delete(fieldName);
        updateSubmitButton();
        return;
    }

    $.ajax({
        url: '{{ route("admin.employeegroups.check-employee") }}',
        type: 'GET',
        data: {
            employee_id: employeeId,
            current_group_id: currentGroupId
        },
        success: function(response) {
            if (!response.available) {
                // Mostrar advertencia y agregar a conflictos
                showEmployeeWarning(fieldName, response.message);
                conflictedEmployees.add(fieldName);
            } else {
                // Ocultar advertencia y quitar de conflictos
                hideEmployeeWarning(fieldName);
                conflictedEmployees.delete(fieldName);
            }
            updateSubmitButton();
        },
        error: function() {
            // En caso de error, ocultar advertencia
            hideEmployeeWarning(fieldName);
            conflictedEmployees.delete(fieldName);
            updateSubmitButton();
        }
    });
}

// Función para mostrar advertencia
function showEmployeeWarning(fieldName, message) {
    // Remover advertencia anterior si existe
    $(`#${fieldName}-warning`).remove();
    
    // Crear elemento de advertencia
    const warningDiv = $(`
        <div id="${fieldName}-warning" class="alert alert-warning alert-dismissible fade show mt-2">
            <i class="fas fa-exclamation-triangle"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    // Insertar después del campo correspondiente
    $(`#${fieldName}`).closest('.form-group').append(warningDiv);
}

// Función para ocultar advertencia
function hideEmployeeWarning(fieldName) {
    $(`#${fieldName}-warning`).remove();
}

// Función para actualizar el estado del botón de envío
function updateSubmitButton() {
    const submitButton = $('button[type="submit"]');
    const formErrors = $('#form_errors');
    const errorList = $('#error_list');
    
    if (conflictedEmployees.size > 0) {
        // Deshabilitar botón y mostrar errores
        submitButton.prop('disabled', true).addClass('btn-secondary').removeClass('btn-success');
        formErrors.removeClass('d-none');
        
        // Limpiar y actualizar lista de errores
        errorList.empty();
        conflictedEmployees.forEach(fieldName => {
            const fieldLabel = getFieldLabel(fieldName);
            errorList.append(`<li>${fieldLabel} ya está asignado a otro grupo</li>`);
        });
    } else {
        // Habilitar botón y ocultar errores
        submitButton.prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
        formErrors.addClass('d-none');
    }
}

// Función para obtener etiqueta del campo
function getFieldLabel(fieldName) {
    const labels = {
        'driver_id': 'Conductor',
        'assistant1_id': 'Ayudante 1',
        'assistant2_id': 'Ayudante 2', 
        'assistant3_id': 'Ayudante 3',
        'assistant4_id': 'Ayudante 4',
        'assistant5_id': 'Ayudante 5'
    };
    return labels[fieldName] || fieldName;
}

// Función para validar el formulario antes de enviar
function validateFormBeforeSubmit() {
    if (conflictedEmployees.size > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            html: `No se puede guardar el grupo porque los siguientes empleados ya están asignados a otros grupos:<br><br>
                  <strong>${Array.from(conflictedEmployees).map(field => getFieldLabel(field)).join('<br>')}</strong><br><br>
                  Por favor, asigne empleados libres o quite las asignaciones conflictivas.`,
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    return true;
}

// Función para actualizar campos de ayudantes
// Función para actualizar campos de ayudantes
function updateAssistantsFields() {
    const vehicleSelect = document.getElementById('vehicle_id');
    const capacityInfo = document.getElementById('capacity_info');
    const assistantsContainer = document.getElementById('assistants_container');
    
    const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
    
    if (!selectedOption.value) {
        capacityInfo.textContent = '';
        assistantsContainer.innerHTML = '';
        return;
    }
    
    const capacity = parseInt(selectedOption.getAttribute('data-capacity'));
    const maxAssistants = Math.max(0, capacity - 1);
    
    // Actualizar info de capacidad
    if (maxAssistants === 0) {
        capacityInfo.textContent = `Capacidad: ${capacity} personas (solo conductor)`;
        capacityInfo.className = 'form-text text-info font-weight-bold';
    } else {
        capacityInfo.textContent = `Capacidad: ${capacity} personas (1 conductor + ${maxAssistants} ayudantes)`;
        capacityInfo.className = 'form-text text-info font-weight-bold';
    }
    
    // Generar campos de ayudantes
    assistantsContainer.innerHTML = '';
    
    for (let i = 1; i <= maxAssistants && i <= 5; i++) {
        const fieldDiv = document.createElement('div');
        fieldDiv.className = 'col-md-6 mb-3';
        fieldDiv.innerHTML = `
            <div class="form-group">
                <label for="assistant${i}_search">Ayudante ${i}</label>
                <input type="text" class="form-control" 
                       id="assistant${i}_search" 
                       placeholder="Buscar ayudante..."
                       oninput="searchEmployees(this, 2, 'assistant${i}_id', 'selected_assistant${i}', 'assistant${i}_results')">
                <input type="hidden" name="assistant${i}_id" id="assistant${i}_id" value="">
                <div id="assistant${i}_results" class="search-results"></div>
                <div id="selected_assistant${i}" class="selected-employee mt-2"></div>
            </div>
        `;
        assistantsContainer.appendChild(fieldDiv);
    }
    
    // Cargar ayudantes existentes si estamos editando - DESPUÉS de crear los campos
    @if(isset($group))
    setTimeout(() => {
        loadExistingAssistants();
    }, 100);
    @endif
}

// Función para limpiar campo de ayudante
function clearAssistantField(number) {
    document.getElementById(`assistant${number}_id`).value = '';
    document.getElementById(`assistant${number}_search`).value = '';
    document.getElementById(`selected_assistant${number}`).innerHTML = '';
    document.getElementById(`assistant${number}_results`).innerHTML = '';
    hideEmployeeWarning(`assistant${number}_id`);
    conflictedEmployees.delete(`assistant${number}_id`);
    updateSubmitButton();
}

// Función para buscar empleados
function searchEmployees(input, type, targetId, containerId, resultsId) {
    const searchTerm = input.value;
    
    if (searchTerm.length < 2) {
        document.getElementById(resultsId).innerHTML = '';
        return;
    }
    
    // Mostrar loading
    document.getElementById(resultsId).innerHTML = '<div class="text-muted p-2">Buscando...</div>';
    
    fetch(`{{ route('admin.employeegroups.search.employees') }}?type=${type}&search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            const resultsContainer = document.getElementById(resultsId);
            resultsContainer.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(employee => {
                    const div = document.createElement('div');
                    div.className = 'search-result-item p-2 border-bottom';
                    div.style.cursor = 'pointer';
                    div.innerHTML = `
                        <strong>${employee.name} ${employee.last_name}</strong><br>
                        <small class="text-muted">DNI: ${employee.dni} | ${employee.position}</small>
                    `;
                    
                    div.addEventListener('click', function() {
                        selectEmployee(employee, targetId, containerId);
                        resultsContainer.innerHTML = '';
                        input.value = '';
                    });
                    
                    // Efecto hover
                    div.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#007bff';
                        this.style.color = 'white';
                        this.querySelector('small').style.color = '#e0e0e0';
                    });
                    
                    div.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '';
                        this.style.color = '';
                        this.querySelector('small').style.color = '';
                    });
                    
                    resultsContainer.appendChild(div);
                });
            } else {
                resultsContainer.innerHTML = '<div class="text-muted p-2">No se encontraron empleados</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById(resultsId).innerHTML = '<div class="text-danger p-2">Error en la búsqueda</div>';
        });
}

// Función para seleccionar empleado
function selectEmployee(employee, targetId, containerId) {
    document.getElementById(targetId).value = employee.id;
    const selectedContainer = document.getElementById(containerId);
    selectedContainer.innerHTML = `
        <div class="alert alert-success py-2 position-relative">
            <i class="fas fa-check-circle"></i> 
            <strong>${employee.name} ${employee.last_name}</strong>
            <small class="d-block">DNI: ${employee.dni} | ${employee.position}</small>
            <button type="button" class="close position-absolute" style="top: 5px; right: 10px;" 
                    onclick="clearEmployeeSelection('${targetId}', '${containerId}')">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    // Verificar disponibilidad del empleado seleccionado
    const currentGroupId = @if(isset($group) && $isEdit) {{ $group->id }} @else null @endif;
    checkEmployeeAvailability(employee.id, targetId, currentGroupId);
}

// Función para limpiar selección
function clearEmployeeSelection(targetId, containerId) {
    document.getElementById(targetId).value = '';
    document.getElementById(containerId).innerHTML = '';
    hideEmployeeWarning(targetId);
    conflictedEmployees.delete(targetId);
    updateSubmitButton();
}

// Función para limpiar campo de ayudante
function clearAssistantField(number) {
    document.getElementById(`assistant${number}_id`).value = '';
    document.getElementById(`assistant${number}_search`).value = '';
    document.getElementById(`selected_assistant${number}`).innerHTML = '';
    document.getElementById(`assistant${number}_results`).innerHTML = '';
    hideEmployeeWarning(`assistant${number}_id`);
    conflictedEmployees.delete(`assistant${number}_id`);
    updateSubmitButton();
}

// Cargar ayudantes existentes en edición
function loadExistingAssistants() {
    console.log('Cargando ayudantes existentes...');
    
    @if(isset($group))
        @for($i = 1; $i <= 5; $i++)
            @if($group->{"assistant$i"})
                console.log('Cargando ayudante {{$i}}:', '{{ $group->{"assistant$i"}->name }}');
                
                // Verificar que el campo exista antes de intentar llenarlo
                if (document.getElementById('assistant{{$i}}_id')) {
                    const assistant{{$i}} = {
                        id: {{ $group->{"assistant$i"}->id }},
                        name: '{{ $group->{"assistant$i"}->name }}',
                        last_name: '{{ $group->{"assistant$i"}->last_name }}',
                        dni: '{{ $group->{"assistant$i"}->dni }}',
                        position: '{{ $group->{"assistant$i"}->employeeType->name ?? "N/A" }}'
                    };
                    selectEmployee(assistant{{$i}}, 'assistant{{$i}}_id', 'selected_assistant{{$i}}');
                } else {
                    console.warn('Campo assistant{{$i}}_id no encontrado');
                }
            @else
                console.log('No hay ayudante {{$i}} asignado');
            @endif
        @endfor
    @endif
}

// Inicializar eventos de validación
function initializeValidationEvents() {
    const currentGroupId = @if(isset($group) && $isEdit) {{ $group->id }} @else null @endif;
    
    // Evento para conductor
    $('#driver_id').on('change', function() {
        const employeeId = $(this).val();
        checkEmployeeAvailability(employeeId, 'driver_id', currentGroupId);
    });
    
    // Eventos para ayudantes (se agregan dinámicamente)
    $(document).on('change', '[id^="assistant"][id$="_id"]', function() {
        const employeeId = $(this).val();
        const fieldName = $(this).attr('id');
        checkEmployeeAvailability(employeeId, fieldName, currentGroupId);
    });
    
    // Validar formulario antes de enviar
    $('#employeeGroupForm').on('submit', function(e) {
        if (!validateFormBeforeSubmit()) {
            e.preventDefault();
            return false;
        }
    });
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página cargada - inicializando campos de ayudantes');
    
    @if(isset($group) && $group->vehicle)
        // Si estamos editando, generar campos basados en el vehículo seleccionado
        console.log('Editando grupo, vehículo seleccionado:', '{{ $group->vehicle->plate }}');
        updateAssistantsFields();
    @else
        // Si es nuevo, verificar si ya hay un vehículo seleccionado
        const vehicleSelect = document.getElementById('vehicle_id');
        if (vehicleSelect.value) {
            console.log('Vehículo ya seleccionado:', vehicleSelect.value);
            updateAssistantsFields();
        }
    @endif
    
    // Inicializar eventos de validación
    initializeValidationEvents();
});

// Forzar carga después de un tiempo por si acaso
setTimeout(() => {
    @if(isset($group) && $group->vehicle)
    if (document.getElementById('assistants_container').children.length === 0) {
        console.log('Forzando carga de campos de ayudantes...');
        updateAssistantsFields();
    }
    @endif
}, 500);
</script>

<style>
.search-results {
    position: absolute;
    z-index: 1000;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
    width: calc(100% - 30px);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-result-item {
    background-color: #f8f9fa;
    transition: all 0.2s;
}

.search-result-item:hover {
    background-color: #007bff !important;
    color: white;
}

.search-result-item:hover small {
    color: #e0e0e0 !important;
}

.selected-employee .alert {
    margin-bottom: 0;
    padding: 8px 12px;
    position: relative;
}

.selected-employee .close {
    position: absolute;
    top: 5px;
    right: 10px;
    font-size: 1.2rem;
    line-height: 1;
}

/* Estilo para select deshabilitado en edición */
select:disabled {
    background-color: #f8f9fa;
    opacity: 1;
    color: #6c757d;
    cursor: not-allowed;
}

/* Estilo para botón deshabilitado */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>