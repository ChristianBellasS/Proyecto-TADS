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
            <button class="btn btn-danger" id="btnBulkScheduling">
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
                            <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fecha de fin</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="end_date"
                                value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-info btn-block" id="btnFilter">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
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
                        "render": function(data) {
                            return new Date(data).toLocaleDateString('es-ES');
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

            // Programación masiva
            $('#btnBulkScheduling').click(function() {
                alert('Funcionalidad de programación masiva');
            });

            $(document).on('shown.bs.modal', '#modalProgramacion', function() {
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
                                        text: group.name ||
                                            'Sin nombre'
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

                    // Evento cuando se selecciona un grupo
                    $('#employee_group_select').on('change', function() {
                        const groupId = $(this).val();

                        if (groupId) {
                            loadGroupData(groupId);
                        } else {
                            resetForm();
                        }
                    });
                }

                $('select[id$="_select"]').not('#employee_group_select').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2({
                            language: "es",
                            placeholder: "Buscar empleado...",
                            allowClear: true,
                            width: '100%',
                            theme: 'bootstrap',
                            dropdownParent: $('#modalProgramacion')
                        });
                    }
                });
            });

            $(document).on('hidden.bs.modal', '#modalProgramacion', function() {
                $('select.select2-hidden-accessible').each(function() {
                    $(this).select2('destroy');
                });
            });

            function loadGroupData(groupId) {
                $('#group_info').hide();
                $('#driver_select, #assistant_1_select, #assistant_2_select')
                    .html('<option value="">Cargando...</option>')
                    .prop('disabled', true);

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
                            alert('Error: ' + data.message);
                            resetForm();
                        }
                    })
                    .catch(error => {
                        alert('Error al cargar los datos del grupo: ' + error.message);
                        resetForm();
                    });
            }

            function populateForm(data) {
                const group = data.group;
                const driver = data.driver;
                const assistants = data.assistants;

                console.log('Datos recibidos en populateForm:', data); // Para debug

                // Mostrar información del grupo
                $('#group_name').text(group.name);
                $('#zone_name').text(group.zone_name);
                $('#shift_info').text(`${group.shift_name} (${group.shift_hours})`);
                $('#vehicle_info').text(`${group.vehicle_name} - ${group.vehicle_plate}`);

                // Llenar campos hidden
                $('#hidden_zone_id').val(group.zone_id);
                $('#hidden_shift_id').val(group.shift_id);
                $('#hidden_vehicle_id').val(group.vehicle_id);

                // Llenar conductor - CORREGIDO
                $('#driver_select').empty().prop('disabled', false);
                if (driver && driver.id) {
                    $('#driver_select').append(
                        new Option(
                            `${driver.names} - ${driver.dni} (Conductor)`,
                            driver.id,
                            true,
                            true
                        )
                    );
                } else {
                    $('#driver_select').append(
                        new Option('No hay conductor asignado', '', true, true)
                    );
                }

                // Llenar ayudantes - CORREGIDO
                $('#assistant_1_select').empty().prop('disabled', false);
                $('#assistant_2_select').empty().prop('disabled', false);

                if (assistants.length > 0) {
                    // Ayudante 1
                    $('#assistant_1_select').append(
                        new Option(
                            `${assistants[0].names} - ${assistants[0].dni} (Ayudante)`, // Quitamos position
                            assistants[0].id,
                            true,
                            true
                        )
                    );

                    // Ayudante 2 (si existe)
                    if (assistants.length > 1) {
                        $('#assistant_2_select').append(
                            new Option(
                                `${assistants[1].names} - ${assistants[1].dni} (Ayudante)`, // Quitamos position
                                assistants[1].id,
                                true,
                                true
                            )
                        );
                    } else {
                        $('#assistant_2_select').append(
                            new Option('No hay segundo ayudante', '', true, true)
                        );
                    }
                } else {
                    $('#assistant_1_select').append(
                        new Option('No hay ayudantes asignados', '', true, true)
                    );
                    $('#assistant_2_select').append(
                        new Option('No hay ayudantes asignados', '', true, true)
                    );
                }

                // Hacer los selects de solo lectura
                $('#driver_select, #assistant_1_select, #assistant_2_select')
                    .prop('readonly', true)
                    .trigger('change.select2');
            }

            function resetForm() {
                $('#group_info').hide();
                $('#group_name, #zone_name, #shift_info, #vehicle_info').text('-');

                // Limpiar campos hidden
                $('#hidden_zone_id, #hidden_shift_id, #hidden_vehicle_id').val('');

                $('#driver_select, #assistant_1_select, #assistant_2_select')
                    .empty()
                    .append(new Option('Seleccione un grupo primero...', '', true, true))
                    .prop('readonly', true)
                    .prop('disabled', false)
                    .trigger('change.select2');
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

            // Función para reasignar/reprogramar (Botón Amarillo)
            $(document).on('click', '.btn-reassign', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: '¿Reasignar Programación?',
                    text: "¿Quieres reasignar el vehículo o reprogramar esta programación?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, reasignar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Aquí iría la lógica para reasignar/reprogramar
                        Swal.fire({
                            title: "Función en desarrollo",
                            text: "La funcionalidad de reasignar estará disponible pronto",
                            icon: "info"
                        });
                    }
                });
            });

            $(document).on('click', '.btn-view-group', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: "Detalles del Grupo",
                    text: "Mostrando información del personal asignado a esta programación",
                    icon: "info",
                    confirmButtonText: "Cerrar"
                });
            });

            // Manejar el envío del formulario dentro del modal
            $(document).on('submit', '#modalProgramacion form', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(this);

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
                        if (error.errors) {
                            var errorMessages = [];
                            for (var key in error.errors) {
                                errorMessages.push(error.errors[
                                    key][0]);
                            }
                            Swal.fire({
                                title: "Error!",
                                html: errorMessages
                                    .join('<br>'),
                                icon: "error"
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: error.message ||
                                    'Error al crear la programación',
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
    </style>
@stop
