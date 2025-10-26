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

<script>
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
                <div class="input-group">
                    <input type="text" class="form-control" 
                           id="assistant${i}_search" 
                           placeholder="Buscar ayudante..."
                           oninput="searchEmployees(this, 2, 'assistant${i}_id', 'selected_assistant${i}', 'assistant${i}_results')">
                    <input type="hidden" name="assistant${i}_id" id="assistant${i}_id" value="">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-danger" onclick="clearAssistantField(${i})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
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
}

// Función para limpiar selección
function clearEmployeeSelection(targetId, containerId) {
    document.getElementById(targetId).value = '';
    document.getElementById(containerId).innerHTML = '';
}

// Función para limpiar campo de ayudante
function clearAssistantField(number) {
    document.getElementById(`assistant${number}_id`).value = '';
    document.getElementById(`assistant${number}_search`).value = '';
    document.getElementById(`selected_assistant${number}`).innerHTML = '';
    document.getElementById(`assistant${number}_results`).innerHTML = '';
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

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página cargada - inicializando campos de ayudantes');
    
    @if(isset($group) && $group->vehicle)
        // Si estamos editando, generar campos basados en el vehículo seleccionado
        console.log('Editando grupo, vehículo seleccionado:', '{{ $group->vehicle->plate }}');
        
        // EJECUCIÓN INMEDIATA
        updateAssistantsFields();
    @else
        // Si es nuevo, verificar si ya hay un vehículo seleccionado
        const vehicleSelect = document.getElementById('vehicle_id');
        if (vehicleSelect.value) {
            console.log('Vehículo ya seleccionado:', vehicleSelect.value);
            updateAssistantsFields();
        }
    @endif
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
</style>