@extends('adminlte::page')

@section('title', 'Programaciones Diarias')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Programaciones</h1>
        <div>
            <a href="{{ route('admin.scheduling.index') }}" class="btn btn-success">
                <i class="fas fa-tasks"></i> Ir al módulo
            </a>
            <button type="button" class="btn btn-primary" id="btnNuevaProgramacion">
                <i class="fas fa-plus"></i> Nueva Programación
            </button>
            <button type="button" class="btn btn-danger" id="btnProgramacionMasiva">
                <i class="fas fa-layer-group"></i> Programación Masiva
            </button>
        </div>
    </div>

    <!-- Modal para Nueva Programación -->
    <div class="modal fade" id="modalProgramacion" tabindex="-1" role="dialog" aria-labelledby="modalProgramacionLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">

                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                    <h5 class="modal-title font-weight-bold" id="modalProgramacionLabel">
                        <i class="fas fa-plus mr-2 text-warning"></i> Nueva Programación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="h5 mb-0">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modalProgramacionBody" style="max-height: 85vh; overflow-y: auto;">
                    <!-- El contenido se cargará aquí via AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando formulario...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Programación Masiva -->
    <div class="modal fade" id="modalProgramacionMasiva" tabindex="-1" role="dialog"
        aria-labelledby="modalProgramacionMasivaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 1300px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                    <h5 class="modal-title font-weight-bold" id="modalProgramacionMasivaLabel">
                        <i class="fas fa-layer-group mr-2 text-warning"></i> Programación Masiva
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="h5 mb-0">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modalProgramacionMasivaBody" style="max-height: 85vh; overflow-y: auto;">
                    <!-- El contenido se cargará aquí via AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando programación masiva...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para Ver Detalles del Grupo -->
    <div class="modal fade" id="modalViewGroup" tabindex="-1" role="dialog" aria-labelledby="modalViewGroupLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                    <h5 class="modal-title font-weight-bold" id="modalViewGroupLabel">
                        <i class="fas fa-users mr-2 text-warning"></i> Detalles del Grupo
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="h5 mb-0">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modalViewGroupBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando detalles del grupo...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Cambios de Programación -->
    <div class="modal fade" id="modalChangeScheduling" tabindex="-1" role="dialog"
        aria-labelledby="modalChangeSchedulingLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                    <h5 class="modal-title font-weight-bold" id="modalChangeSchedulingLabel">
                        <i class="fas fa-exchange-alt mr-2 text-warning"></i> Modificar Programación
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="h5 mb-0">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modalChangeSchedulingBody" style="max-height: 80vh; overflow-y: auto;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando formulario de cambios...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fecha de inicio</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="start_date">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fecha de fin</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="end_date">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-info btn-block" id="btnFilter">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-block" id="btnClearFilter">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Programaciones -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dailyTable">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>ESTADO</th>
                            <th>ZONA</th>
                            <th>TURNOS</th>
                            <th>VEHÍCULO</th>
                            <th>GRUPO</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/es.js"></script>

    <script>
        $(document).ready(function() {
            // Inicialización de DataTable
            var table = $('#dailyTable').DataTable({
                "processing": true,
                "serverSide": false,
                "ajax": {
                    "url": "{{ route('admin.scheduling.daily-data') }}",
                    "type": "GET",
                    "data": function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    },
                    "dataSrc": ""
                },
                "columns": [{
                        "data": "date",
                        // "render": function(data) {
                        "render": function(data, type, row) {

                            if (!data) return 'N/A';

                            // Parsear manualmente YYYY-MM-DD a DD/MM/YYYY
                            // const [year, month, day] = data.split('-');
                            // return `${day}/${month}/${year}`;
                            // Mostrar formato bonito solo al dibujar
                            if (type === 'display' || type === 'filter') {
                                const [year, month, day] = data.split('-');
                                return `${day}/${month}/${year}`;
                            }

                            // Pero para ordenar y buscar, usar el formato original YYYY-MM-DD
                            return data;
                        }
                    },
                    {
                        "data": "status",
                        "render": function(data) {
                            var badgeClass = 'secondary';
                            var statusText = data;

                            switch (data) {
                                case 'programado':
                                    badgeClass = 'info';
                                    statusText = 'Programado';
                                    break;
                                case 'iniciado':
                                    badgeClass = 'success';
                                    statusText = 'Iniciado';
                                    break;
                                case 'completado':
                                    badgeClass = 'primary';
                                    statusText = 'Completado';
                                    break;
                                case 'cancelado':
                                    badgeClass = 'danger';
                                    statusText = 'Cancelado';
                                    break;
                            }

                            return '<span class="badge badge-' + badgeClass + '">' + statusText +
                                '</span>';
                        }
                    },
                    {
                        "data": "zone_name",
                        "render": function(data) {
                            return data || 'N/A';
                        }
                    },
                    {
                        "data": "shift_name",
                        "render": function(data) {
                            return data || 'N/A';
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return row.vehicle_name ? row.vehicle_name + ' - ' + row.vehicle_plate :
                                'N/A';
                        }
                    },
                    {
                        "data": "group_name",
                        "render": function(data) {
                            return data || 'N/A';
                        }
                    },
                    {
                        "data": null,
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            let buttons = '';

                            // Botón Amarillo (Reasignar/Reprogramar) - Icono de Doble Flecha Circular
                            buttons +=
                                '<button class="btn btn-sm btn-warning btn-reassign mr-1" data-id="' +
                                row.id + '" title="Reasignar/Reprogramar" style="color: white;">' +
                                '<i class="fas fa-redo-alt"></i>' +
                                '</button>';

                            // Botón Azul Turquesa (Ver Grupo/Personal) - Icono de Grupo de Personas
                            buttons +=
                                '<button class="btn btn-sm btn-info btn-view-group mr-1" data-id="' +
                                row.id + '" title="Ver Grupo/Personal" style="color: white;">' +
                                '<i class="fas fa-users"></i>' +
                                '</button>';

                            // Botón Rojo (Eliminar/Cancelar) - Icono de Círculo Tachado
                            buttons +=
                                '<button class="btn btn-sm btn-danger btn-delete mr-1" data-id="' +
                                row.id + '" title="Eliminar/Cancelar" style="color: white;">' +
                                '<i class="fas fa-ban"></i>' +
                                '</button>';

                            return '<div class="btn-group">' + buttons + '</div>';
                        }
                    }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "order": [
                    [0, "desc"]
                ]
            });

            // Filtrar datos
            $('#btnFilter').click(function() {
                table.ajax.reload();
            });

            // Limpiar filtros
            $('#btnClearFilter').click(function() {
                // Limpiar campos de fecha
                $('#start_date').val('');
                $('#end_date').val('');

                // Recargar tabla sin filtros
                table.ajax.reload();

                // Mostrar mensaje de confirmación
                Swal.fire({
                    icon: 'success',
                    title: 'Filtros limpiados',
                    text: 'Se han limpiado todos los filtros aplicados',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            // También puedes permitir filtrar con Enter
            $('#start_date, #end_date').keypress(function(e) {
                if (e.which == 13) { // Enter key
                    table.ajax.reload();
                }
            });

            // Abrir modal para crear una nueva programación
            $('#btnNuevaProgramacion').click(function() {
                $.ajax({
                    url: "{{ route('admin.scheduling.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modalProgramacionBody').html(response);
                        $('#modalProgramacionLabel').html("Nueva Programación");
                        $('#modalProgramacion').modal('show');
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
                    }
                });
            });

            // Programación masiva - CORREGIDO
            $('#btnProgramacionMasiva').click(function() {
                $.ajax({
                    url: "{{ route('admin.mass-scheduling.index') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modalProgramacionMasivaBody').html(response); // ✅ CON #
                        $('#modalProgramacionMasivaLabel').html(
                            "Programación Masiva"); // ✅ CON #
                        $('#modalProgramacionMasiva').modal('show');
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'No se pudo cargar la programación masiva', 'error');
                    }
                });
            });

            $(document).on('shown.bs.modal', '#modalProgramacion', function() {
                // Inicializar Select2 para grupos
                if ($('#employee_group_select').length > 0 && !$('#employee_group_select').hasClass(
                        'select2-hidden-accessible')) {
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
                                if (!data || !data.results) {
                                    return {
                                        results: []
                                    };
                                }
                                return {
                                    results: data.results.map(group => ({
                                        id: group.id,
                                        text: group.name || 'Sin nombre'
                                    })),
                                    pagination: {
                                        more: data.pagination && data.pagination.more
                                    }
                                };
                            },
                            cache: true,
                        },
                        minimumInputLength: 1
                    });

                    // ✅ AGREGAR ESTE EVENTO - Cuando se selecciona un grupo
                    $('#employee_group_select').on('change', function() {
                        const groupId = $(this).val();
                        if (groupId) {
                            loadGroupData(groupId);
                        } else {
                            resetForm();
                        }
                    });
                }

                // Inicializar Select2 para conductor
                if ($('#driver_select').length > 0 && !$('#driver_select').hasClass(
                        'select2-hidden-accessible')) {
                    $('#driver_select').select2({
                        language: "es",
                        placeholder: "Conductor...",
                        allowClear: true,
                        width: '100%',
                        theme: 'bootstrap',
                        dropdownParent: $('#modalProgramacion')
                    });
                }
            });

            $(document).on('hidden.bs.modal', '#modalProgramacion', function() {
                $('select.select2-hidden-accessible').each(function() {
                    $(this).select2('destroy');
                });
            });

            function loadGroupData(groupId) {
                $('#group_info').hide();
                $('#driver_select').html('<option value="">Cargando...</option>').prop('disabled', true);
                $('#assistantsContainer').empty();

                fetch(`/admin/scheduling/group-data/${groupId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            populateForm(data);
                            $('#group_info').show();
                        } else {
                            Swal.fire('Error', data.message || 'Error al cargar datos del grupo', 'error');
                            resetForm();
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Error al cargar los datos del grupo: ' + error.message, 'error');
                        resetForm();
                    });
            }

            function populateForm(data) {
                const group = data.group;
                const driver = data.driver;
                const assistants = data.assistants;

                console.log('Datos recibidos en populateForm:', data);

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
                        new Option(
                            `${driver.names} - ${driver.dni}`,
                            driver.id,
                            true,
                            true
                        )
                    );
                }

                // Limpiar contenedor de ayudantes y crear selects dinámicos
                $('#assistantsContainer').empty();

                assistants.forEach((assistant, index) => {
                    const selectHtml = `
            <div class="form-group col-md-6">
                <label>Ayudante ${index + 1}</label>
                <select name="assistant_ids[]" class="form-control assistant-select" ${index === 0 ? 'required' : ''}>
                    <option value="${assistant.id}" selected>${assistant.names} - ${assistant.dni}</option>
                </select>
            </div>
        `;
                    $('#assistantsContainer').append(selectHtml);
                });

                // Inicializar Select2 para los nuevos selects
                $('.assistant-select').select2({
                    theme: 'bootstrap',
                    width: '100%',
                    dropdownParent: $('#modalProgramacion'),
                    placeholder: 'Ayudante asignado',
                    allowClear: false
                });

                $('#group_info').show();
            }

            function resetForm() {
                $('#group_info').hide();
                $('#group_name, #zone_name, #shift_info, #vehicle_info').text('-');

                // Limpiar campos hidden
                $('#hidden_zone_id, #hidden_shift_id, #hidden_vehicle_id').val('');

                // Limpiar conductor
                $('#driver_select').empty()
                    .append(new Option('Seleccione un grupo primero...', '', true, true))
                    .prop('disabled', true);

                // Limpiar ayudantes
                $('#assistantsContainer').empty();
            }

            // Eliminar programación con confirmación de SweetAlert
            $(document).on('click', '.btn-delete', function(event) {
                event.preventDefault();
                var id = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡Esta acción no se puede deshacer!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/scheduling') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire(
                                    '¡Eliminado!',
                                    response.message ||
                                    'La programación ha sido eliminada.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                var error = xhr.responseJSON;
                                Swal.fire(
                                    'Error',
                                    error.message ||
                                    'Hubo un problema al eliminar la programación.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Función para reasignar/reprogramar
            $(document).on('click', '.btn-reassign', function() {
                var id = $(this).data('id');

                // Mostrar modal de carga
                $('#modalChangeSchedulingBody').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando formulario de cambios...</p>
        </div>
    `);
                $('#modalChangeScheduling').modal('show');

                // Cargar formulario de cambios
                $.ajax({
                    url: "{{ route('admin.scheduling.changes-form', ':id') }}".replace(':id', id),
                    type: "GET",
                    success: function(response) {
                        if (typeof response === 'string' || response instanceof String) {
                            $('#modalChangeSchedulingBody').html(response);
                        }
                        else if (response.success === false) {
                            $('#modalChangeSchedulingBody').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${response.message || 'Error al cargar el formulario'}
                    </div>
                `);
                        }
                        else {
                            $('#modalChangeSchedulingBody').html(`
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Respuesta inesperada del servidor
                    </div>
                `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', xhr.responseText);

                        let errorMessage = 'Error de conexión';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMessage = 'Recurso no encontrado';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Error interno del servidor';
                        }

                        $('#modalChangeSchedulingBody').html(`
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${errorMessage}
                </div>
            `);
                    }
                });
            });

            $(document).on('click', '.btn-view-group', function() {
                var id = $(this).data('id');

                // Mostrar modal de carga
                $('#modalViewGroupBody').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando detalles del grupo...</p>
        </div>
    `);
                $('#modalViewGroup').modal('show');

                // Cargar detalles del grupo
                $.ajax({
                    url: "{{ url('admin/scheduling/group-details') }}/" + id,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            $('#modalViewGroupBody').html(response.html);
                            $('#modalViewGroupLabel').html(`
                    <i class="fas fa-users mr-2 text-warning"></i> Detalles del Grupo
                `);
                        } else {
                            $('#modalViewGroupBody').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${response.message || 'Error al cargar los detalles del grupo'}
                    </div>
                `);
                        }
                    },
                    error: function(xhr) {
                        $('#modalViewGroupBody').html(`
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar los detalles del grupo
                </div>
            `);
                    }
                });
            });

            // Manejar el envío del formulario dentro del modal
            $(document).on('submit', '#modalProgramacion form', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);

                // Verificar si estamos en el modal (no en la página create)
                if (!form.closest('#modalProgramacion').length) {
                    return true; // Permitir envío normal si no está en modal
                }

                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#modalProgramacion').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message,
                            icon: "success"
                        });
                    },
                    error: function(xhr) {
                        var error = xhr.responseJSON;
                        if (error && error.errors) {
                            var errorMessages = [];
                            for (var key in error.errors) {
                                errorMessages.push(error.errors[key][0]);
                            }
                            Swal.fire({
                                title: "Error!",
                                html: errorMessages.join('<br>'),
                                icon: "error"
                            });
                        } else if (error && error.message) {
                            Swal.fire({
                                title: "Error!",
                                text: error.message,
                                icon: "error"
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: 'Error al crear la programación',
                                icon: "error"
                            });
                        }
                    }
                });
            });

            // Manejar envío del formulario de cambios
            $(document).on('submit', '#changeSchedulingForm', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#modalChangeScheduling').modal('hide');
                            table.ajax.reload();
                            Swal.fire({
                                title: "¡Éxito!",
                                text: response.message,
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Error al aplicar cambios',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        var error = xhr.responseJSON;
                        if (error && error.errors) {
                            var errorMessages = [];
                            for (var key in error.errors) {
                                errorMessages.push(error.errors[key][0]);
                            }
                            Swal.fire({
                                title: "Error!",
                                html: errorMessages.join('<br>'),
                                icon: "error"
                            });
                        } else if (error && error.message) {
                            Swal.fire({
                                title: "Error!",
                                text: error.message,
                                icon: "error"
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: 'Error al aplicar los cambios',
                                icon: "error"
                            });
                        }
                    }
                });
            });
        });
    </script>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/themes/bootstrap/select2-bootstrap.min.css"
        rel="stylesheet" />

    <style>
        .nav-sidebar .nav-treeview {
            margin-left: 20px;
        }

        .nav-sidebar .nav-treeview>.nav-item {
            margin-left: 10px;
        }

        .badge {
            font-size: 0.85em;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .gap-2 {
            gap: 0.5rem !important;
        }

        .d-flex.gap-2 .btn {
            margin: 0;
        }

        .btn-block {
            min-width: auto;
        }
    </style>
@stop
