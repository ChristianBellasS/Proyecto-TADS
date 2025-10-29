{!! Form::open(['route' => 'admin.scheduling.store', 'method' => 'POST', 'id' => 'schedulingForm']) !!}

<!-- Campos hidden -->
<input type="hidden" id="hidden_zone_id" name="zone_id">
<input type="hidden" id="hidden_shift_id" name="shift_id">
<input type="hidden" id="hidden_vehicle_id" name="vehicle_id">

<!-- Fechas -->
<div class="form-row">
    <div class="form-group col-md-4">
        <label>Fecha de inicio *</label>
        <input type="date" name="start_date" class="form-control" required pattern="\d{2}/\d{2}/\d{4}"
            placeholder="DD/MM/YYYY">
    </div>
    <div class="form-group col-md-4">
        <label>Fecha de fin *</label>
        <input type="date" name="end_date" class="form-control" required pattern="\d{2}/\d{2}/\d{4}"
            placeholder="DD/MM/YYYY">
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
        <select name="driver_id" id="driver_select" class="form-control" required>
            <option value="">Seleccione grupo...</option>
        </select>
    </div>

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
                        pagination: data.pagination || {
                            more: false
                        }
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

        /* $('#schedulingForm').on('submit', function(e) {
            e.preventDefault();

            // Convertir fechas de YYYY-MM-DD a DD/MM/YYYY
            const startDateInput = $('input[name="start_date"]');
            const endDateInput = $('input[name="end_date"]');

            const startDateValue = startDateInput.val();
            const endDateValue = endDateInput.val();

            if (startDateValue && endDateValue) {
                // Convertir formato YYYY-MM-DD a DD/MM/YYYY
                const startDateConverted = convertDateFormat(startDateValue);
                const endDateConverted = convertDateFormat(endDateValue);

                // Reemplazar los valores originales con los convertidos
                startDateInput.val(startDateConverted);
                endDateInput.val(endDateConverted);

                // Enviar formulario
                this.submit();
            } else {
                showError('Las fechas son requeridas');
            }
        });

        // Función para convertir YYYY-MM-DD a DD/MM/YYYY
        function convertDateFormat(dateString) {
            const parts = dateString.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            return dateString;
        } */

        // Función para validar todo
        function validateAll() {
            const startDate = $('input[name="start_date"]').val();
            const endDate = $('input[name="end_date"]').val();
            const driverId = $('#driver_select').val();
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
                showValidationInfo('Seleccione un conductor');
                return;
            }

            // Recolectar dinámicamente los IDs de todos los ayudantes
            const assistantIds = $('.assistant-select').map(function() {
                return $(this).val();
            }).get().filter(id => id);

            // Validar que haya al menos un ayudante
            if (assistantIds.length === 0) {
                showValidationInfo('Seleccione al menos un ayudante');
                return;
            }

            if (!vehicleId || !zoneId || !shiftId) {
                showValidationInfo('Espere a que se carguen los datos del grupo');
                return;
            }

            // Todos los empleados (conductor + ayudantes)
            const employeeIds = [driverId, ...assistantIds];

            showValidationLoading();

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
                        showValidationError(response.message, response.errors, response
                            .suggestions);
                        disableSubmitButton();
                    }
                },
                error: function(xhr) {
                    showValidationError('Error en la validación: ' + (xhr.responseJSON?.message ||
                        'Error desconocido'));
                    disableSubmitButton();
                }
            });
        }

        // Funciones auxiliares
        function showLoading() {
            $('#group_info').hide();
            $('#driver_select').html('<option value="">Cargando...</option>').prop('disabled', true);
            $('#assistantsContainer').empty();
        }

        function populateFormWithGroupData(data) {
            const group = data.group;
            const driver = data.driver;
            const assistants = data.assistants;
            const workDays = data.work_days || [];

            // Mostrar información del grupo
            $('#group_name').text(group.name);
            $('#zone_name').text(group.zone_name);
            $('#shift_info').text(`${group.shift_name} (${group.shift_hours})`);
            $('#vehicle_info').text(`${group.vehicle_name} - ${group.vehicle_plate}`);

            // Llenar campos hidden
            $('#hidden_zone_id').val(group.zone_id);
            $('#hidden_shift_id').val(group.shift_id);
            $('#hidden_vehicle_id').val(group.vehicle_id);

            // Inicializar Select2 para conductor
            initializeDriverSelect();

            // Establecer conductor después de inicializar Select2
            setTimeout(() => {
                if (driver && driver.id) {
                    const option = new Option(driver.names, driver.id, true, true);
                    $('#driver_select').append(option).trigger('change');
                }
            }, 500);

            // Limpiar contenedor de ayudantes
            $('#assistantsContainer').empty();

            // Crear selects para ayudantes
            assistants.forEach((assistant, index) => {
                const requiredAttr = index === 0 ? 'required' : '';
                const selectHtml = `
                    <div class="form-group col-md-6">
                        <label>Ayudante ${index + 1} ${index === 0 ? '*' : ''}</label>
                        <select name="assistant_ids[]" class="form-control assistant-select" ${requiredAttr}>
                            <!-- Dejar vacío para que Select2 cargue sugerencias -->
                        </select>
                    </div>
                `;
                $('#assistantsContainer').append(selectHtml);
            });

            // Inicializar Select2 para ayudantes
            initializeAssistantSelects();

            // Establecer ayudantes después de inicializar Select2
            setTimeout(() => {
                assistants.forEach((assistant, index) => {
                    if (assistant && assistant.id) {
                        const $select = $('.assistant-select').eq(index);
                        if ($select.length) {
                            const option = new Option(assistant.names, assistant.id, true,
                                true);
                            $select.append(option).trigger('change');
                        }
                    }
                });
            }, 800);

            // Marcar los días de trabajo
            $('.day-checkbox').prop('checked', false);
            workDays.forEach(day => {
                $(`#day_${day}`).prop('checked', true);
            });

            $('#group_info').show();
        }

        function initializeDriverSelect() {
            // Destruir Select2 si ya está inicializado
            if ($('#driver_select').hasClass('select2-hidden-accessible')) {
                $('#driver_select').select2('destroy');
            }

            // Limpiar opciones existentes
            $('#driver_select').empty().prop('disabled', false);

            // Inicializar Select2 para conductor
            $('#driver_select').select2({
                theme: 'bootstrap',
                width: '100%',
                dropdownParent: $('#modalProgramacion'),
                placeholder: 'Buscar conductor disponible...',
                allowClear: true,
                ajax: {
                    url: '{{ route('admin.scheduling.search-available-drivers') }}',
                    type: 'GET',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        const data = {
                            search: params.term,
                            date: $('input[name="start_date"]').val() ||
                                '{{ now()->format('Y-m-d') }}',
                            exclude_employees: getCurrentEmployeeIds(),
                            _token: '{{ csrf_token() }}'
                        };
                        return data;
                    },
                    processResults: function(data) {
                        return {
                            results: data.results || [],
                            pagination: data.pagination || {
                                more: false
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });
        }

        function initializeAssistantSelects() {
            $('.assistant-select').each(function() {
                const $select = $(this);

                // Destruir Select2 si ya está inicializado
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                // Limpiar opciones existentes
                $select.empty();

                $select.select2({
                    theme: 'bootstrap',
                    width: '100%',
                    dropdownParent: $('#modalProgramacion'),
                    placeholder: 'Buscar ayudante disponible...',
                    allowClear: true,
                    ajax: {
                        url: '{{ route('admin.scheduling.search-available-assistants') }}',
                        type: 'GET',
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            const data = {
                                search: params.term,
                                date: $('input[name="start_date"]').val() ||
                                    '{{ now()->format('Y-m-d') }}',
                                exclude_employees: getCurrentEmployeeIds(),
                                _token: '{{ csrf_token() }}'
                            };
                            return data;
                        },
                        processResults: function(data) {
                            return {
                                results: data.results || [],
                                pagination: data.pagination || {
                                    more: false
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1
                });
            });
        }

        function getCurrentEmployeeIds() {
            const driverId = $('#driver_select').val();
            const currentAssistantIds = $('.assistant-select').map(function() {
                const val = $(this).val();
                return val && val !== '' ? parseInt(val) : null;
            }).get().filter(id => id !== null);

            const allIds = [driverId ? parseInt(driverId) : null, ...currentAssistantIds].filter(id => id !==
                null);

            return allIds;
        }

        function resetForm() {
            $('#group_info').hide();
            $('#driver_select').html('<option value="">Seleccione un grupo primero...</option>').prop(
                'disabled', true);
            $('#assistantsContainer').empty();

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
            $('#validationStatus').html(
                '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Validando disponibilidad...</div>'
            );
        }

        function showValidationInfo(message) {
            $('#validationStatus').html(
                `<div class="alert alert-info"><i class="fas fa-info-circle"></i> ${message}</div>`);
        }

        function showValidationSuccess(message) {
            $('#validationStatus').html(
                `<div class="alert alert-success"><i class="fas fa-check-circle"></i> ${message}</div>`);
        }

        function showValidationError(message, errors = [], suggestions = []) {
            let html =
                `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <strong>${message}</strong>`;

            if (errors.length > 0) {
                html += '<ul class="mb-0 mt-2">';
                const uniqueErrors = [...new Set(errors)];
                uniqueErrors.forEach(error => html += `<li>${error}</li>`);
                html += '</ul>';
            }

            if (suggestions.length > 0) {
                html += '<div class="mt-2"><strong><i class="fas fa-lightbulb"></i> Sugerencias:</strong><ul>';
                const uniqueSuggestions = [...new Set(suggestions)];
                uniqueSuggestions.forEach(suggestion => html += `<li>${suggestion}</li>`);
                html += '</ul></div>';
            }

            html += '</div>';

            const currentHtml = $('#validationStatus').html();
            if (currentHtml.trim() !== html.trim()) {
                $('#validationStatus').html(html);
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

    .select2-container--bootstrap .select2-results__option {
        padding: 10px 12px;
        border-bottom: 1px solid #f8f9fa;
        transition: background-color 0.2s ease;
        font-size: 14px;
    }

    .select2-container--bootstrap .select2-results__option--highlighted {
        background-color: #007bff !important;
        color: white !important;
    }

    .select2-container--bootstrap .select2-results__option:before {
        content: "•";
        margin-right: 8px;
        color: #6c757d;
        font-weight: bold;
    }

    .employee-badge {
        background: #e9ecef;
        color: #495057;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        margin-left: auto;
    }

    .select2-container--bootstrap .select2-selection__choice__remove {
        color: white;
        margin-right: 4px;
        opacity: 0.8;
    }

    .select2-container--bootstrap .select2-selection__choice__remove:hover {
        opacity: 1;
    }
</style>
