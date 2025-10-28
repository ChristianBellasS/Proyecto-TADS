{!! Form::open(['route' => 'admin.scheduling.store', 'method' => 'POST', 'id' => 'schedulingForm']) !!}

<!-- Campos hidden -->
<input type="hidden" id="hidden_zone_id" name="zone_id">
<input type="hidden" id="hidden_shift_id" name="shift_id">
<input type="hidden" id="hidden_vehicle_id" name="vehicle_id">

<!-- Fechas -->
<div class="form-row">
    <div class="form-group col-md-4">
        <label>Fecha de inicio *</label>
        {!! Form::date('start_date', null, ['class' => 'form-control', 'required']) !!}
    </div>
    <div class="form-group col-md-4">
        <label>Fecha de fin *</label>
        {!! Form::date('end_date', null, ['class' => 'form-control', 'required']) !!}
    </div>
    <div class="form-group col-md-4">
        <label>&nbsp;</label>
        <button type="button" id="checkAvailability" class="btn btn-outline-info btn-block">
            <i class="fas fa-check"></i> Validar disponibilidad
        </button>
    </div>
</div>

<!-- Grupo -->
<div class="form-group">
    <label>Grupo de Personal *</label>
    <select name="employee_group_id" id="employee_group_select" class="form-control" required>
        <option value="">Buscar grupo...</option>
    </select>
    <small class="text-muted">Busque por nombre, zona o turno</small>
</div>

<!-- Estado de validación -->
<div id="validationStatus" class="mb-3">
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Complete los datos y valide la disponibilidad
    </div>
</div>

<!-- Info Grupo -->
<div id="group_info" class="alert alert-light border" style="display: none">
    <div class="row">
        <div class="col-6 col-md-3">
            <small class="text-muted d-block">Grupo</small>
            <strong id="group_name">-</strong>
        </div>
        <div class="col-6 col-md-3">
            <small class="text-muted d-block">Zona</small>
            <strong id="zone_name">-</strong>
        </div>
        <div class="col-6 col-md-3">
            <small class="text-muted d-block">Turno</small>
            <strong id="shift_info">-</strong>
        </div>
        <div class="col-6 col-md-3">
            <small class="text-muted d-block">Vehículo</small>
            <strong id="vehicle_info">-</strong>
        </div>
    </div>
</div>

<!-- Personal -->
<div class="form-row">
    <div class="form-group col-md-4">
        <label>Conductor *</label>
        <select name="driver_id" id="driver_select" class="form-control" required readonly>
            <option value="">Seleccione grupo...</option>
        </select>
    </div>
    
    <!-- <div class="form-group col-md-4">
        <label>Ayudante 1 *</label>
        <select name="assistant_ids[]" id="assistant_1_select" class="form-control" required readonly>
            <option value="">Seleccione grupo...</option>
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Ayudante 2</label>
        <select name="assistant_ids[]" id="assistant_2_select" class="form-control" readonly>
            <option value="">Seleccione grupo...</option>
        </select>
    </div> -->
     <!-- Contenedor dinámico para ayudantes -->
    <div id="assistantsContainer" class="form-row col-md-8">
        <!-- Aquí se generarán los selects de ayudantes según el grupo -->
    </div>
</div>

<!-- Días -->
<div class="form-group">
    <label>Días de trabajo *</label>
    <div class="d-flex flex-wrap gap-3">
        @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day)
            <div class="form-check">
                {!! Form::checkbox('work_days[]', $day, false, [
                    'class' => 'form-check-input day-checkbox',
                    'id' => 'day_' . $day,
                ]) !!}
                <label class="form-check-label" for="day_{{ $day }}">{{ $day }}</label>
            </div>
        @endforeach
    </div>
</div>

<!-- Observaciones -->
<div class="form-group">
    <label>Observaciones</label>
    {!! Form::textarea('notes', null, [
        'class' => 'form-control',
        'rows' => 2,
        'placeholder' => 'Observaciones adicionales...',
    ]) !!}
</div>

<!-- Botones -->
<div class="form-group text-right pt-3 border-top">
    <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
    <button type="submit" class="btn btn-success" id="submitBtn" disabled>
        <i class="fas fa-save"></i> Guardar Programación
    </button>
</div>

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        // Estado de validación
        let isFormValid = false;

        // Inicializar Select2 para búsqueda de grupos
        $('#employee_group_select').select2({
            language: "es",
            placeholder: "Buscar grupo de personal...",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap',
            dropdownParent: $('#modalProgramacion'),
            ajax: {
                url: '{{ route('admin.scheduling.search-employee-groups') }}',
                type: 'GET',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results || [],
                        pagination: data.pagination || { more: false }
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });

        // Cuando se selecciona un grupo
        $('#employee_group_select').on('change', function() {
            const groupId = $(this).val();

            if (!groupId) {
                resetForm();
                return;
            }

            showLoading();
            
            // Obtener datos del grupo
            $.get(`/admin/scheduling/group-data/${groupId}`)
                .done(function(data) {
                    if (data.success) {
                        populateFormWithGroupData(data);
                        validateAll();
                    } else {
                        showError('Error al cargar los datos del grupo');
                        resetForm();
                    }
                })
                .fail(function() {
                    showError('Error al cargar los datos del grupo');
                    resetForm();
                });
        });

        // Validar disponibilidad al hacer clic en el botón
        $('#checkAvailability').on('click', function() {
            validateAll();
        });

        // Validar cuando cambian las fechas o días
        $('input[name="start_date"], input[name="end_date"]').on('change', function() {
            validateAll();
        });

        $('.day-checkbox').on('change', function() {
            validateAll();
        });

        // Función para validar todo
        function validateAll() {
            const startDate = $('input[name="start_date"]').val();
            const endDate = $('input[name="end_date"]').val();
            const driverId = $('#driver_select').val();
            const assistant1Id = $('#assistant_1_select').val();
            const assistant2Id = $('#assistant_2_select').val();
            const vehicleId = $('#hidden_vehicle_id').val();
            const zoneId = $('#hidden_zone_id').val();
            const shiftId = $('#hidden_shift_id').val();
            const workDays = getSelectedWorkDays();

            // Validaciones básicas
            if (!startDate || !endDate) {
                showValidationInfo('Complete las fechas de inicio y fin');
                return;
            }

            if (!driverId) {
                showValidationInfo('Seleccione un grupo de personal');
                return;
            }

            if (!vehicleId || !zoneId || !shiftId) {
                showValidationInfo('Espere a que se carguen los datos del grupo');
                return;
            }

            /*
            const employeeIds = [driverId];
            if (assistant1Id) employeeIds.push(assistant1Id);
            if (assistant2Id) employeeIds.push(assistant2Id);

            showValidationLoading();
            */
                // Recolectar dinámicamente los IDs de todos los ayudantes
            const assistantIds = $('.assistant-select').map(function() {
                return $(this).val();
            }).get();

            // Todos los empleados (conductor + ayudantes)
            const employeeIds = [driverId, ...assistantIds];
            // Llamar a la validación del servidor
            $.ajax({
                url: '{{ route('admin.scheduling.check-availability') }}',
                type: 'POST',
                data: {
                    employee_ids: employeeIds,
                    start_date: startDate,
                    end_date: endDate,
                    work_days: workDays,
                    vehicle_id: vehicleId,
                    zone_id: zoneId,
                    shift_id: shiftId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.is_valid) {
                        showValidationSuccess(response.message);
                        enableSubmitButton();
                    } else {
                        showValidationError(response.message, response.errors, response.suggestions);
                        disableSubmitButton();
                    }
                },
                error: function(xhr) {
                    showValidationError('Error en la validación: ' + (xhr.responseJSON?.message || 'Error desconocido'));
                    disableSubmitButton();
                }
            });
        }

        // Funciones auxiliares
        function showLoading() {
            $('#group_info').hide();
            $('#driver_select, #assistant_1_select, #assistant_2_select')
                .html('<option value="">Cargando...</option>')
                .prop('disabled', true);
        }

        function populateFormWithGroupData(data) {
            const group = data.group;
            const driver = data.driver;
            const assistants = data.assistants;
            const workDays = data.work_days || []; // Agregado para marcar días     

            // Mostrar información del grupo
            $('#group_name').text(group.name);
            $('#zone_name').text(group.zone_name);
            $('#shift_info').text(`${group.shift_name} (${group.shift_hours})`);
            $('#vehicle_info').text(`${group.vehicle_name} - ${group.vehicle_plate}`);

            // Llenar campos hidden
            $('#hidden_zone_id').val(group.zone_id);
            $('#hidden_shift_id').val(group.shift_id);
            $('#hidden_vehicle_id').val(group.vehicle_id);

            // Llenar conductor
            $('#driver_select').empty().prop('disabled', false);
            if (driver && driver.id) {
                $('#driver_select').append(new Option(driver.names, driver.id, true, true));
            }

            // Llenar ayudantes
            /*
            $('#assistant_1_select').empty().prop('disabled', false);
            $('#assistant_2_select').empty().prop('disabled', false);

            if (assistants.length > 0) {
                if (assistants[0]) {
                    $('#assistant_1_select').append(new Option(assistants[0].names, assistants[0].id, true, true));
                }
                if (assistants[1]) {
                    $('#assistant_2_select').append(new Option(assistants[1].names, assistants[1].id, true, true));
                }
            }
            */
           // Limpiar contenedor de ayudantes
            $('#assistantsContainer').empty();

            // Crear un select por cada ayudante que venga del grupo
            assistants.forEach((assistant, index) => {
                const requiredAttr = index === 0 ? 'required' : ''; // solo el primer ayudante obligatorio
                const selectHtml = `
                    <div class="form-group col-md-6">
                        <label>Ayudante ${index + 1} ${index === 0 ? '*' : ''}</label>
                        <select name="assistant_ids[]" class="form-control assistant-select" ${requiredAttr} readonly>
                            <option value="${assistant.id}" selected>${assistant.names}</option>
                        </select>
                    </div>
                `;
                $('#assistantsContainer').append(selectHtml);
            });

            // Inicializar Select2 en los ayudantes recién creados
            $('#assistantsContainer .assistant-select').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap',
                        width: '100%',
                        dropdownParent: $('#modalProgramacion'),
                        placeholder: 'Seleccione ayudante...',
                        allowClear: true
                    });
                }
            });



            // Marcar los días de trabajo
            $('.day-checkbox').prop('checked', false); // primero desmarcar todos
            workDays.forEach(day => {
                $(`#day_${day}`).prop('checked', true);
            });

            $('#group_info').show();
        }

        function resetForm() {
            $('#group_info').hide();
            $('#driver_select, #assistant_1_select, #assistant_2_select')
                .html('<option value="">Seleccione un grupo primero...</option>')
                .prop('disabled', true);
                
            disableSubmitButton();
            showValidationInfo('Complete los datos y valide la disponibilidad');
        }

        function getSelectedWorkDays() {
            const workDays = [];
            $('.day-checkbox:checked').each(function() {
                workDays.push($(this).val());
            });
            return workDays;
        }

        function showValidationLoading() {
            $('#validationStatus').html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Validando disponibilidad...</div>');
        }

        function showValidationInfo(message) {
            $('#validationStatus').html(`<div class="alert alert-info"><i class="fas fa-info-circle"></i> ${message}</div>`);
        }

        function showValidationSuccess(message) {
            $('#validationStatus').html(`<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${message}</div>`);
        }

        function showValidationError(message, errors = [], suggestions = []) {


            // Filtrar duplicados
            // errors = [...new Set(errors)];
            // suggestions = [...new Set(suggestions)];

            let html = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <strong>${message}</strong>`;
            
            if (errors.length > 0) {
                html += '<ul class="mb-0 mt-2">';
                errors.forEach(error => html += `<li>${error}</li>`);
                html += '</ul>';
            }
            
            if (suggestions.length > 0) {
                html += '<div class="mt-2"><strong><i class="fas fa-lightbulb"></i> Sugerencias:</strong><ul>';
                suggestions.forEach(suggestion => html += `<li>${suggestion}</li>`);
                html += '</ul></div>';
            }
            
            html += '</div>';
            // $('#validationStatus').html(html);
                // Verificamos si el contenido actual ya es igual al que queremos mostrar
            const currentHtml = $('#validationStatus').html();
            if (currentHtml.trim() !== html.trim()) {
               $('#validationStatus').html(html); // Solo reemplazamos si es diferente
            }
        }

        function enableSubmitButton() {
            $('#submitBtn').prop('disabled', false);
            isFormValid = true;
        }

        function disableSubmitButton() {
            $('#submitBtn').prop('disabled', true);
            isFormValid = false;
        }

        function showError(message) {
            Swal.fire('Error', message, 'error');
        }

        // Inicializar
        disableSubmitButton();
    });
</script>

<style>
    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -5px;
        margin-left: -5px;
    }

    .form-group {
        margin-bottom: 1rem;
        padding-right: 5px;
        padding-left: 5px;
        flex: 1 0 0%;
    }

    .gap-3 {
        gap: 1rem;
    }

    .alert-light {
        background-color: #f8f9fa;
    }
</style>