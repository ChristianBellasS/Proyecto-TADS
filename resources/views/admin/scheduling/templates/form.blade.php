{{-- {!! Form::open(['route' => 'admin.scheduling.store', 'method' => 'POST', 'id' => 'schedulingForm']) !!} --}}

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
    <div class="form-group col-md-4">
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
    </div>
</div>

<!-- Días -->
<div class="form-group">
    <label>Días de trabajo *</label>
    <div class="d-flex flex-wrap gap-3">
        @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day)
            <div class="form-check">
                {!! Form::checkbox('work_days[]', $day, false, [
                    'class' => 'form-check-input',
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
    {{-- {!! Form::button('<i class="fas fa-save"></i> Guardar', [
        'type' => 'submit',
        'class' => 'btn btn-success',
        'id' => 'submitBtn',
    ]) !!} --}}
</div>

{{-- {!! Form::close() !!} --}}

@section('js')
    <script>
        $(document).ready(function() {
            console.log('JavaScript cargado - Iniciando validación...');

            // Estado de validación
            let isFormValid = false;

            const validationStatusHtml = `
        <div id="validationStatus" class="mb-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Complete los datos y valide la disponibilidad
            </div>
        </div>
    `;

            // Insertar después del grupo de personal
            $('#employee_group_select').closest('.form-group').after(validationStatusHtml);

            // Inicializar Select2
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
                        console.log('Resultados de búsqueda:', data); // Debug
                        if (!data || !data.results) {
                            return {
                                results: []
                            };
                        }

                        return {
                            results: data.results.map(group => ({
                                id: group.id,
                                text: group.text || group.name
                            })),
                            pagination: {
                                more: data.pagination && data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            // Evento cuando se selecciona un grupo
            $('#employee_group_select').on('change', function() {
                console.log('Grupo seleccionado:', $(this).val()); // Debug
                const groupId = $(this).val();

                if (!groupId) {
                    resetForm();
                    return;
                }

                showLoading();

                // Obtener datos completos del grupo
                fetch(`/admin/scheduling/group-data/${groupId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos del grupo recibidos:', data); // Debug
                        if (data.success) {
                            populateFormWithGroupData(data);
                            // Validar automáticamente después de cargar los datos
                            setTimeout(validateAll, 500);
                        } else {
                            throw new Error(data.message || 'Error al cargar los datos del grupo');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showValidationError('Error al cargar los datos del grupo: ' + error.message);
                        resetForm();
                    });
            });

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

                console.log('Populando formulario con:', data);

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
                    $('#driver_select').append(
                        new Option(driver.names, driver.id, true, true)
                    );
                }

                // Llenar ayudantes
                $('#assistant_1_select').empty().prop('disabled', false);
                $('#assistant_2_select').empty().prop('disabled', false);

                if (assistants.length > 0) {
                    // Ayudante 1
                    if (assistants[0]) {
                        $('#assistant_1_select').append(
                            new Option(assistants[0].names, assistants[0].id, true, true)
                        );
                    }
                    // Ayudante 2 (si existe)
                    if (assistants[1]) {
                        $('#assistant_2_select').append(
                            new Option(assistants[1].names, assistants[1].id, true, true)
                        );
                    }
                }

                // Mostrar información del grupo
                $('#group_info').show();

                // Hacer los selects de solo lectura
                $('#driver_select, #assistant_1_select, #assistant_2_select')
                    .prop('readonly', true)
                    .trigger('change');

                console.log('Formulario poblado correctamente'); // Debug
            }

            function resetForm() {
                $('#group_info').hide();
                $('#group_name, #zone_name, #shift_info, #vehicle_info').text('-');
                $('#hidden_zone_id, #hidden_shift_id, #hidden_vehicle_id').val('');

                $('#driver_select, #assistant_1_select, #assistant_2_select')
                    .html('<option value="">Seleccione un grupo primero...</option>')
                    .prop('disabled', true)
                    .prop('readonly', true);

                $('#submitBtn').prop('disabled', true);
                resetValidationStyles();
            }

            // Validación completa en tiempo real
            function validateAll() {
                console.log('Ejecutando validación...'); // Debug

                const startDate = $('input[name="start_date"]').val();
                const endDate = $('input[name="end_date"]').val();
                const driverId = $('#driver_select').val();
                const assistant1Id = $('#assistant_1_select').val();
                const assistant2Id = $('#assistant_2_select').val();
                const vehicleId = $('#hidden_vehicle_id').val();
                const zoneId = $('#hidden_zone_id').val();
                const workDays = getSelectedWorkDays();

                console.log('Datos para validar:', {
                    startDate,
                    endDate,
                    driverId,
                    assistant1Id,
                    assistant2Id,
                    vehicleId,
                    zoneId,
                    workDays
                }); // Debug

                // Validaciones básicas
                if (!startDate || !endDate) {
                    showValidationInfo('Complete las fechas de inicio y fin');
                    return;
                }

                if (!driverId) {
                    showValidationInfo('Seleccione un grupo de personal');
                    return;
                }

                if (!vehicleId || !zoneId) {
                    showValidationInfo('Espere a que se carguen los datos del grupo');
                    return;
                }

                const employeeIds = [driverId];
                if (assistant1Id) employeeIds.push(assistant1Id);
                if (assistant2Id) employeeIds.push(assistant2Id);

                // Mostrar loading
                showValidationLoading();

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
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Respuesta de validación:', response); // Debug
                        if (response.is_valid) {
                            showValidationSuccess(response.message);
                            markAllAsValid();
                            enableSubmitButton();
                        } else {
                            showValidationError(response.message, response.errors, response
                                .suggestions);
                            markAllAsInvalid();
                            disableSubmitButton();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error en AJAX:', xhr);
                        const errorMessage = xhr.responseJSON?.message || 'Error en la validación';
                        showValidationError('Error: ' + errorMessage);
                        markAllAsInvalid();
                        disableSubmitButton();
                    }
                });
            }

            // Funciones para mostrar estados de validación
            function showValidationLoading() {
                $('#validationStatus').html(`
            <div class="alert alert-info">
                <i class="fas fa-spinner fa-spin"></i> Validando disponibilidad...
            </div>
        `);
            }

            function showValidationInfo(message) {
                $('#validationStatus').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> ${message}
            </div>
        `);
                resetValidationStyles();
                disableSubmitButton();
            }

            function showValidationSuccess(message) {
                $('#validationStatus').html(`
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>${message}</strong>
                <div class="mt-2">
                    <i class="fas fa-thumbs-up"></i> Todo está en orden. Puede guardar la programación.
                </div>
            </div>
        `);
            }

            function showValidationError(message, errors = [], suggestions = []) {
                let errorHtml = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <strong>${message}</strong>
        `;

                if (errors && errors.length > 0) {
                    errorHtml += '<ul class="mb-0 mt-2">';
                    errors.forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                    errorHtml += '</ul>';
                }

                if (suggestions && suggestions.length > 0) {
                    errorHtml +=
                        '<div class="mt-2"><strong><i class="fas fa-lightbulb"></i> Sugerencias:</strong><ul>';
                    suggestions.forEach(suggestion => {
                        errorHtml += `<li>${suggestion}</li>`;
                    });
                    errorHtml += '</ul></div>';
                }

                errorHtml += '</div>';
                $('#validationStatus').html(errorHtml);
            }

            // Funciones para estilos visuales
            function markAllAsValid() {
                console.log('Marcando todo como válido (verde)'); // Debug
                // Marcar campos en verde
                $('.form-control').addClass('is-valid').removeClass('is-invalid');
                $('#group_info').removeClass('border-danger').addClass('border-success');
                $('.form-check-label').addClass('text-success');

                // Marcar checkboxes
                $('input[name="work_days[]"]').addClass('is-valid');

                // Cambiar color del botón de validar
                $('#checkAvailability')
                    .removeClass('btn-outline-info')
                    .addClass('btn-success')
                    .html('<i class="fas fa-check"></i> Validado ✓');
            }

            function markAllAsInvalid() {
                console.log('Marcando todo como inválido (rojo)'); // Debug
                // Marcar campos en rojo
                $('.form-control').addClass('is-invalid').removeClass('is-valid');
                $('#group_info').removeClass('border-success').addClass('border-danger');
                $('.form-check-label').addClass('text-danger');

                // Restaurar botón de validar
                $('#checkAvailability')
                    .removeClass('btn-success')
                    .addClass('btn-outline-info')
                    .html('<i class="fas fa-check"></i> Validar disponibilidad');
            }

            function resetValidationStyles() {
                console.log('Reseteando estilos de validación'); // Debug
                // Remover todos los estilos de validación
                $('.form-control').removeClass('is-valid is-invalid');
                $('#group_info').removeClass('border-success border-danger');
                $('.form-check-label').removeClass('text-success text-danger');
                $('input[name="work_days[]"]').removeClass('is-valid is-invalid');

                // Restaurar botón de validar
                $('#checkAvailability')
                    .removeClass('btn-success')
                    .addClass('btn-outline-info')
                    .html('<i class="fas fa-check"></i> Validar disponibilidad');
            }

            // Funciones para el botón de enviar
            /* function enableSubmitButton() {
                console.log('Activando botón de enviar'); // Debug
                $('#submitBtn')
                    .prop('disabled', false)
                    .removeClass('btn-secondary')
                    .addClass('btn-success')
                    .html('<i class="fas fa-save"></i> Guardar Programación');
                isFormValid = true;
            } */

            function disableSubmitButton() {
                console.log('Desactivando botón de enviar'); // Debug
                $('#submitBtn')
                    .prop('disabled', true)
                    .removeClass('btn-success')
                    .addClass('btn-secondary')
                    .html('<i class="fas fa-save"></i> Guardar Programación');
                isFormValid = false;
            }

            // Helper functions
            function getSelectedWorkDays() {
                const workDays = [];
                $('input[name="work_days[]"]:checked').each(function() {
                    workDays.push($(this).val());
                });
                return workDays;
            }

            // Event listeners para validación en tiempo real
            $('input[name="start_date"], input[name="end_date"]').on('change', function() {
                console.log('Fecha cambiada - validando...'); // Debug
                validateAll();
            });

            $('#checkAvailability').on('click', function() {
                console.log('Botón validar clickeado'); // Debug
                validateAll();
            });

            $('input[name="work_days[]"]').on('change', function() {
                console.log('Días de trabajo cambiados - validando...'); // Debug
                validateAll();
            });

            // Validar al cambiar cualquier campo relevante
            $('#driver_select, #assistant_1_select, #assistant_2_select').on('change', function() {
                console.log('Personal cambiado - validando...'); // Debug
                validateAll();
            });

            // Prevenir envío del formulario si no es válido
            $('#schedulingForm').on('submit', function(e) {
                if (!isFormValid) {
                    e.preventDefault();
                    showValidationError('Debe validar la disponibilidad antes de guardar');
                    return false;
                }
            });

            // Inicializar
            disableSubmitButton();
            console.log('Validación inicializada correctamente'); // Debug
        });
    </script>
@stop

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
