<!-- Incluir SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="row">
    <div class="col-4">
        <div class="form-group">
            {!! Form::label('fecha_inicio', 'Fecha de Inicio *') !!}
            {!! Form::date('fecha_inicio', null, [
                'class' => 'form-control',
                'required',
                'id' => 'fecha_inicio',
            ]) !!}
            <div class="invalid-feedback" id="fecha_inicio_error" style="display: none;"></div>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            {!! Form::label('fecha_fin', 'Fecha de Fin *') !!}
            {!! Form::date('fecha_fin', null, [
                'class' => 'form-control',
                'required',
                'id' => 'fecha_fin',
            ]) !!}
            <div class="invalid-feedback" id="fecha_fin_error" style="display: none;"></div>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            {!! Form::label('change_type', 'Tipo de Cambio *') !!}
            {!! Form::select(
                'change_type',
                [
                    'conductor' => 'Cambio de Conductor',
                    'vehiculo' => 'Cambio de Vehículo',
                    'ocupante' => 'Cambio de Ocupante',
                ],
                null,
                [
                    'class' => 'form-control',
                    'placeholder' => 'Seleccione el tipo de cambio',
                    'required',
                    'id' => 'change_type',
                ],
            ) !!}
            <div class="invalid-feedback" id="change_type_error" style="display: none;"></div>
        </div>
    </div>
</div>

<!-- Campos para Cambio de Conductor -->
<div class="change-field conductor-fields" style="display: none;">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {!! Form::label('conductor_actual', 'Conductor a Reemplazar *') !!}
                <select class="form-control" id="conductor_actual" name="conductor_actual" disabled>
                    <option value="">Seleccione conductor a reemplazar</option>
                </select>
                <div class="invalid-feedback" id="conductor_actual_error" style="display: none;"></div>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {!! Form::label('nuevo_conductor', 'Nuevo Conductor *') !!}
                <select class="form-control" id="nuevo_conductor" name="nuevo_conductor" disabled>
                    <option value="">Seleccione nuevo conductor</option>
                </select>
                <div class="invalid-feedback" id="nuevo_conductor_error" style="display: none;"></div>
                <div class="alert alert-warning mt-2" id="conductor_warning" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Campos para Cambio de Vehículo -->
<div class="change-field vehiculo-fields" style="display: none;">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {!! Form::label('vehiculo_actual', 'Vehículo a Reemplazar *') !!}
                <select class="form-control" id="vehiculo_actual" name="vehiculo_actual" disabled>
                    <option value="">Seleccione vehículo a reemplazar</option>
                </select>
                <div class="invalid-feedback" id="vehiculo_actual_error" style="display: none;"></div>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {!! Form::label('nuevo_vehiculo', 'Nuevo Vehículo *') !!}
                <select class="form-control" id="nuevo_vehiculo" name="nuevo_vehiculo" disabled>
                    <option value="">Seleccione nuevo vehículo</option>
                </select>
                <div class="invalid-feedback" id="nuevo_vehiculo_error" style="display: none;"></div>
                <div class="alert alert-warning mt-2" id="vehiculo_warning" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Campos para Cambio de Ocupante -->
<div class="change-field ocupante-fields" style="display: none;">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {!! Form::label('ocupante_actual', 'Ocupante a Reemplazar *') !!}
                <select class="form-control" id="ocupante_actual" name="ocupante_actual" disabled>
                    <option value="">Seleccione ocupante a reemplazar</option>
                </select>
                <div class="invalid-feedback" id="ocupante_actual_error" style="display: none;"></div>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {!! Form::label('nuevo_ocupante', 'Nuevo Ocupante *') !!}
                <select class="form-control" id="nuevo_ocupante" name="nuevo_ocupante" disabled>
                    <option value="">Seleccione nuevo ocupante</option>
                </select>
                <div class="invalid-feedback" id="nuevo_ocupante_error" style="display: none;"></div>
                <div class="alert alert-warning mt-2" id="ocupante_warning" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('reason', 'Motivo del Cambio Masivo *') !!}
            {!! Form::textarea('reason', null, [
                'class' => 'form-control',
                'placeholder' => 'Describa el motivo del cambio masivo...',
                'rows' => 3,
                'required',
                'id' => 'reason'
            ]) !!}
            <div class="invalid-feedback" id="reason_error" style="display: none;"></div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        console.log('✅ Script cargado - VERSIÓN CON SWEETALERT');

        // Fechas por defecto
        var today = new Date();
        var nextWeek = new Date();
        nextWeek.setDate(today.getDate() + 7);

        $('#fecha_inicio').val(today.toISOString().split('T')[0]);
        $('#fecha_fin').val(nextWeek.toISOString().split('T')[0]);
        console.log('📅 Fechas establecidas:', $('#fecha_inicio').val(), $('#fecha_fin').val());

        // Función PRINCIPAL - Se ejecuta al seleccionar tipo
        function handleChangeType(changeType) {
            console.log('🎯 Tipo seleccionado:', changeType);

            if (!changeType) {
                console.log('❌ No hay tipo seleccionado');
                return;
            }

            // 1. MOSTRAR CAMPOS INMEDIATAMENTE
            $('.change-field').hide();
            $('.' + changeType + '-fields').show();
            console.log('👁️ Campos mostrados para:', changeType);

            // 2. HABILITAR SELECTS INMEDIATAMENTE
            var $selectActual = $('#' + changeType + '_actual');
            var $selectNuevo = $('#nuevo_' + changeType);

            $selectActual.prop('disabled', false);
            $selectNuevo.prop('disabled', false);
            console.log('🔓 Selects HABILITADOS');

            // 3. CARGAR DATOS
            loadReplaceResources(changeType);
            loadNewResources(changeType);
            
            // 4. ACTUALIZAR VALIDACIÓN
            updateGuardarButton();
        }

        function loadReplaceResources(changeType) {
            console.log('🔄 Cargando recursos a reemplazar para:', changeType);

            var fechaInicio = $('#fecha_inicio').val();
            var fechaFin = $('#fecha_fin').val();
            var $selectActual = $('#' + changeType + '_actual');

            $selectActual.html('<option value="">🔄 Cargando...</option>');

            $.ajax({
                url: "{{ route('admin.scheduling-changes.get-resources-by-range') }}",
                type: 'GET',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    change_type: changeType
                },
                success: function(response) {
                    console.log('✅ Respuesta recursos a reemplazar:', response);
                    var options = '<option value="">Seleccione una opción</option>';

                    if (response.resources && response.resources.length > 0) {
                        response.resources.forEach(function(item) {
                            options += '<option value="' + item.id + '">' + item.text +
                                '</option>';
                        });
                        $selectActual.html(options);
                        console.log('✅ Recursos a reemplazar cargados:', response.resources.length);
                    } else {
                        var message = changeType === 'conductor' ? '❌ No hay conductores' :
                            changeType === 'ocupante' ? '❌ No hay ocupantes' : '❌ No hay vehículos';
                        $selectActual.html('<option value="">' + message +
                            ' en este rango</option>');
                        console.log('❌ No hay recursos a reemplazar');
                    }
                    
                    updateGuardarButton();
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error cargando recursos a reemplazar:', error);
                    $selectActual.html('<option value="">❌ Error al cargar</option>');
                    updateGuardarButton();
                }
            });
        }

        function loadNewResources(changeType) {
            console.log('🔄 Cargando nuevos recursos para:', changeType);

            var $selectNuevo = $('#nuevo_' + changeType);
            $selectNuevo.html('<option value="">🔄 Cargando...</option>');

            $.ajax({
                url: "{{ route('admin.scheduling-changes.get-all-resources') }}",
                type: 'GET',
                data: {
                    change_type: changeType
                },
                success: function(response) {
                    console.log('✅ Respuesta nuevos recursos:', response);
                    var options = '<option value="">Seleccione una opción</option>';

                    if (response.resources && response.resources.length > 0) {
                        response.resources.forEach(function(item) {
                            options += '<option value="' + item.id + '">' + item.text +
                                '</option>';
                        });
                        $selectNuevo.html(options);
                        console.log('✅ Nuevos recursos cargados:', response.resources.length);
                    } else {
                        var message = changeType === 'conductor' ?
                            '❌ No hay conductores disponibles' :
                            changeType === 'ocupante' ? '❌ No hay ocupantes disponibles' :
                            '❌ No hay vehículos disponibles';
                        $selectNuevo.html('<option value="">' + message + '</option>');
                        console.log('❌ No hay nuevos recursos');
                    }
                    
                    updateGuardarButton();
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error cargando nuevos recursos:', error);
                    $selectNuevo.html('<option value="">❌ Error al cargar</option>');
                    updateGuardarButton();
                }
            });
        }

        // FUNCIONES DE VALIDACIÓN EN TIEMPO REAL
        function validateBasicFields() {
            let isValid = true;
            
            $('.invalid-feedback').hide();
            $('.alert-warning').hide();
            $('.form-control').removeClass('is-invalid');

            // Validar fechas
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            
            if (!fechaInicio) {
                $('#fecha_inicio').addClass('is-invalid');
                $('#fecha_inicio_error').text('La fecha de inicio es obligatoria').show();
                isValid = false;
            }
            
            if (!fechaFin) {
                $('#fecha_fin').addClass('is-invalid');
                $('#fecha_fin_error').text('La fecha de fin es obligatoria').show();
                isValid = false;
            }
            
            if (fechaInicio && fechaFin && new Date(fechaFin) < new Date(fechaInicio)) {
                $('#fecha_fin').addClass('is-invalid');
                $('#fecha_fin_error').text('La fecha de fin no puede ser anterior a la fecha de inicio').show();
                isValid = false;
            }
            
            // Validar tipo de cambio
            const changeType = $('#change_type').val();
            if (!changeType) {
                $('#change_type').addClass('is-invalid');
                $('#change_type_error').text('Debe seleccionar un tipo de cambio').show();
                isValid = false;
            }
            
            // Validar motivo
            const reason = $('#reason').val();
            if (!reason || reason.trim() === '') {
                $('#reason').addClass('is-invalid');
                $('#reason_error').text('El motivo es obligatorio').show();
                isValid = false;
            } else if (reason.trim().length < 10) {
                $('#reason').addClass('is-invalid');
                $('#reason_error').text('El motivo debe tener al menos 10 caracteres').show();
                isValid = false;
            }
            
            return isValid;
        }

        function validateSpecificFields() {
            const changeType = $('#change_type').val();
            if (!changeType) return false;
            
            let isValid = true;
            
            const resourceActual = $('#' + changeType + '_actual').val();
            const resourceNuevo = $('#nuevo_' + changeType).val();
            
            if (!resourceActual) {
                $('#' + changeType + '_actual').addClass('is-invalid');
                $('#' + changeType + '_actual_error').text('Debe seleccionar un recurso a reemplazar').show();
                isValid = false;
            }
            
            if (!resourceNuevo) {
                $('#nuevo_' + changeType).addClass('is-invalid');
                $('#nuevo_' + changeType + '_error').text('Debe seleccionar un nuevo recurso').show();
                isValid = false;
            }
            
            if (resourceActual && resourceNuevo && resourceActual === resourceNuevo) {
                $('#nuevo_' + changeType).addClass('is-invalid');
                $('#nuevo_' + changeType + '_error').text('No puede seleccionar el mismo recurso').show();
                $('#' + changeType + '_warning').text('⚠️ No puede seleccionar el mismo recurso para reemplazar').show();
                isValid = false;
            } else {
                $('#' + changeType + '_warning').hide();
            }
            
            return isValid;
        }

        function updateGuardarButton() {
            const basicValid = validateBasicFields();
            const specificValid = validateSpecificFields();
            
            const allValid = basicValid && specificValid;
            
            $('#btn-guardar').prop('disabled', !allValid);
        }

        // Función para confirmar y guardar con SweetAlert
        function confirmAndSave() {
            const changeType = $('#change_type').val();
            const resourceActual = $('#' + changeType + '_actual option:selected').text();
            const resourceNuevo = $('#nuevo_' + changeType + ' option:selected').text();
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            
            Swal.fire({
                title: '¿Confirmar Cambio Masivo?',
                html: `
                    <div class="text-left">
                        <p><strong>Tipo:</strong> ${$('#change_type option:selected').text()}</p>
                        <p><strong>Recurso actual:</strong> ${resourceActual}</p>
                        <p><strong>Nuevo recurso:</strong> ${resourceNuevo}</p>
                        <p><strong>Período:</strong> ${fechaInicio} a ${fechaFin}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, guardar cambios',
                cancelButtonText: 'Cancelar',
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitForm();
                }
            });
        }

        // Función para enviar el formulario
        function submitForm() {
            const formData = new FormData();
            
            formData.append('fecha_inicio', $('#fecha_inicio').val());
            formData.append('fecha_fin', $('#fecha_fin').val());
            formData.append('change_type', $('#change_type').val());
            formData.append('reason', $('#reason').val());
            
            const changeType = $('#change_type').val();
            formData.append(changeType + '_actual', $('#' + changeType + '_actual').val());
            formData.append('nuevo_' + changeType, $('#nuevo_' + changeType).val());
            
            // Mostrar loading
            $('#btn-guardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            
            $.ajax({
                url: "{{ route('admin.scheduling-changes.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message + '. Programaciones afectadas: ' + response.affected_count,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            window.location.href = "{{ route('admin.scheduling-changes.index') }}";
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                        $('#btn-guardar').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambio Masivo');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error enviando formulario:', error);
                    let errorMessage = 'Error al guardar los cambios';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                    $('#btn-guardar').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambio Masivo');
                }
            });
        }

        // EVENTOS
        $('#change_type').change(function() {
            var tipo = $(this).val();
            handleChangeType(tipo);
            updateGuardarButton();
        });

        $('#fecha_inicio, #fecha_fin').change(function() {
            var changeType = $('#change_type').val();
            if (changeType) {
                loadReplaceResources(changeType);
            }
            updateGuardarButton();
        });

        $('select[id$="_actual"], select[id^="nuevo_"]').change(function() {
            updateGuardarButton();
        });

        $('#reason').on('input', function() {
            const reason = $(this).val();
            if (!reason || reason.trim() === '') {
                $(this).addClass('is-invalid');
                $('#reason_error').text('El motivo es obligatorio').show();
            } else if (reason.trim().length < 10) {
                $(this).addClass('is-invalid');
                $('#reason_error').text('El motivo debe tener al menos 10 caracteres').show();
            } else {
                $(this).removeClass('is-invalid');
                $('#reason_error').hide();
            }
            updateGuardarButton();
        });

        $('#btn-guardar').click(function() {
            confirmAndSave();
        });

        console.log('✅ Script CON SWEETALERT cargado correctamente');
    });
</script>