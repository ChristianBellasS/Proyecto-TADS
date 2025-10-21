@extends('adminlte::page')

@section('title', 'Listado de Asistencias Registradas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Listado de asistencias registradas</h1>
        <div class="d-flex">
            <button type="button" class="btn btn-outline-secondary mr-2">
                <i class="fa fa-share"></i> Ir al módulo
            </button>
            <button type="button" class="btn btn-success" id="btnRegistrar">
                <i class="fa fa-plus"></i> Agregar nueva asistencia
            </button>
        </div>
    </div>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filtrosForm">
                <div class="row g-4 align-items-end">
                    <!-- Fecha de inicio -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date" class="form-label fw-medium">Fecha de inicio</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                    </div>

                    <!-- Fecha de fin -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date" class="form-label fw-medium">Fecha de fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>

                    <!-- Buscar empleado -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search" class="form-label fw-medium">Buscar empleado</label>
                            <input type="text" class="form-control" id="search" name="search"
                                placeholder="DNI, nombre o apellido...">
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary" id="btnFiltrar">
                                    <i class="fa fa-filter me-1"></i> Filtrar
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="btnLimpiar">
                                    <i class="fa fa-eraser me-1"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de asistencias -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>DNI</th>
                            <th>EMPLEADO</th>
                            <th>FECHA Y HORA</th>
                            <th>TIPO</th>
                            <th>PERÍODO</th>
                            <th>ESTADO</th>
                            <th>NOTAS</th>
                            <th width="10px"></th>
                            <th width="10px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán dinámicamente via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <!-- Header elegante -->
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-clipboard-check mr-2 text-warning" id="modalTitle"></i>Registro de Asistencia
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="h5 mb-0">&times;</span>
                </button>
            </div>

            <!-- Body limpio -->
            <div class="modal-body p-4" id="modalBody" style="max-height: 70vh; overflow-y: auto;">
                <!-- El contenido se cargará aquí dinámicamente -->
            </div>
        </div>
    </div>
</div>

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 45px !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 14px !important;
            border: 1.5px solid #ced4da !important;
            border-radius: 8px !important;
            background-color: #fff !important;
            transition: all 0.2s ease-in-out;
        }

        /* Texto seleccionado */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #333 !important;
            font-size: 15px !important;
            line-height: normal !important;
            padding-left: 0 !important;
            margin-top: 2px;
        }

        /* Ícono del triángulo */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 10px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Hover / Focus */
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #007bff !important;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }

        /* Placeholder */
        .select2-selection__placeholder {
            color: #999 !important;
            font-style: italic;
        }

        /* Opciones del dropdown */
        .select2-results__option {
            padding: 10px 14px !important;
            font-size: 15px;
        }

        .select2-results__option--highlighted {
            background-color: #007bff !important;
            color: white !important;
        }

        /* Ancho y sombra del dropdown */
        .select2-dropdown {
            border-radius: 8px !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/es.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialización de DataTable
            var table = $('#table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('admin.attendances.index') }}",
                    "data": function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.search = $('#search').val();
                        d.type = $('#type').val();
                    }
                },
                "columns": [{
                        "data": "dni",
                        "name": "employee.dni"
                    },
                    {
                        "data": "employee_name",
                        "name": "employee.name"
                    },
                    {
                        "data": "attendance_date",
                        "name": "attendance_date",
                        "render": function(data) {
                            return data || '-';
                        }
                    },
                    {
                        "data": "type",
                        "name": "type",
                        "render": function(data, type, row) {
                            console.log('Tipo recibido:', data, 'Fila completa:', row);
                            var badgeClass = data === 'ENTRADA' ? 'bg-success' : 'bg-info';
                            var displayText = data === 'ENTRADA' ? 'Entrada' : 'Salida';
                            return '<span class="badge ' + badgeClass + '">' + displayText +
                                '</span>';
                        }
                    },
                    {
                        "data": "period",
                        "name": "period",
                        "render": function(data) {
                            var badgeClass = 'bg-primary';
                            var displayText = 'Mañana';

                            if (data == 2) {
                                displayText = 'Tarde';
                                badgeClass = 'bg-info';
                            } else if (data == 3) {
                                displayText = 'Noche';
                                badgeClass = 'bg-dark';
                            } else if (data == 4) {
                                displayText = 'Día completo';
                                badgeClass = 'bg-success';
                            }

                            return '<span class="badge ' + badgeClass + '">' + displayText +
                                '</span>';
                        }
                    },
                    {
                        "data": "status",
                        "name": "status",
                        "render": function(data) {
                            var badgeClass = 'bg-secondary';
                            var statusText = 'Presente';

                            if (data == 2) {
                                statusText = 'Ausente';
                                badgeClass = 'bg-danger';
                            } else if (data == 3) {
                                statusText = 'Tarde';
                                badgeClass = 'bg-warning';
                            } else if (data == 4) {
                                statusText = 'Permiso';
                                badgeClass = 'bg-info';
                            }

                            return '<span class="badge ' + badgeClass + '">' + statusText +
                                '</span>';
                        }
                    },
                    {
                        "data": "notes",
                        "name": "notes",
                        "render": function(data) {
                            return data || '-';
                        }
                    },
                    {
                        "data": "edit",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            return '<button class="btn btn-warning btn-sm btnEditar" data-id="' +
                                row.id + '">' +
                                '<i class="fa-solid fa-pen-to-square"></i>' +
                                '</button>';
                        }
                    },
                    {
                        "data": "delete",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            return '<form action="{{ url('admin/attendances') }}/' + row.id +
                                '" method="POST" class="frmDelete d-inline">' +
                                '@csrf' +
                                '@method('DELETE')' +
                                '<button type="submit" class="btn btn-danger btn-sm">' +
                                '<i class="fa-solid fa-trash"></i>' +
                                '</button>' +
                                '</form>';
                        }
                    }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "order": [
                    [2, 'desc'] // Ordenar por fecha descendente
                ]
            });

            // Aplicar filtros
            $('#btnFiltrar').click(function() {
                table.ajax.reload();
            });

            // Limpiar filtros
            $('#btnLimpiar').click(function() {
                $('#filtrosForm')[0].reset();
                table.ajax.reload();

                // Mostrar mensaje de confirmación
                Swal.fire({
                    title: 'Filtros limpiados',
                    text: 'Todos los filtros han sido restablecidos',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            // Eliminar asistencia con confirmación de SweetAlert
            $(document).on('click', '.frmDelete button[type="submit"]', function(event) {
                event.preventDefault();

                var form = $(this).closest('form');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esta acción!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminarlo!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: {
                                '_method': 'DELETE',
                                '_token': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    refreshTable();
                                    Swal.fire('¡Eliminado!', response.message,
                                        'success');
                                } else {
                                    Swal.fire('Error', response.message ||
                                        'Error al eliminar', 'error');
                                }
                            },
                            error: function(xhr) {
                                console.error('Error en eliminación:', xhr);
                                Swal.fire('Error',
                                    'Hubo un problema al eliminar el registro.',
                                    'error');
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear una nueva asistencia
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.attendances.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalTitle').html(
                            '<i class="fas fa-clipboard-check mr-2 text-warning"></i>Nueva Asistencia'
                        );
                        $('#modal').modal('show');
                        initSelect2();
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr, status, error);
                        Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
                    }
                });
            });

            // Abrir modal para editar una asistencia existente
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/attendances') }}/" + id + "/edit",
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalTitle').html(
                            '<i class="fas fa-clipboard-check mr-2 text-warning"></i>Editar Asistencia'
                        );
                        $('#modal').modal('show');
                        initSelect2();
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        Swal.fire('Error', 'No se pudo cargar el formulario de edición',
                            'error');
                    }
                });
            });

            // Función para inicializar Select2
            function initSelect2() {
                $('#employee_select').select2({
                    width: '100%',
                    language: "es",
                    placeholder: "Buscar empleado...",
                    allowClear: true,
                    dropdownParent: $('#modal'),
                    ajax: {
                        url: "{{ route('admin.employees.search') }}",
                        type: "GET",
                        dataType: 'json',
                        delay: 250,
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
                                        text: employee.name + ' ' + (employee.last_name || '') +
                                            ' - DNI: ' + employee.dni,
                                        full_name: employee.name + ' ' + (employee.last_name ||
                                            ''),
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
            }

            // Formatear cómo se muestra el empleado en los resultados
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

            // Formatear cómo se muestra la selección
            function formatEmployeeSelection(employee) {
                return employee.text;
            }

            // Función para refrescar la tabla
            function refreshTable() {
                table.ajax.reload();
            }
        });
    </script>
@stop
