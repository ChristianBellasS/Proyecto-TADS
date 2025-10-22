@extends('adminlte::page')

@section('title', 'Vacaciones')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nueva Solicitud
    </button>
    <h1>Gestión de Vacaciones</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Fecha Solicitud</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Días Solicitados</th>
                            <th>Estado</th>
                            <th>Días Restantes</th>
                            <th>Notas</th>
                            <th>Fecha Creación</th>
                            <th width="120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vacations as $vacation)
                            <tr>
                                <td>{{ $vacation->employee->name }} {{ $vacation->employee->last_name }}</td>
                                <td>{{ $vacation->request_date->format('d/m/Y') }}</td>
                                <td>{{ $vacation->start_date->format('d/m/Y') }}</td>
                                <td>{{ $vacation->end_date->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $vacation->requested_days }}</span>
                                </td>
                                <td class="text-center">
                                    @if($vacation->status == 'Pending')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif($vacation->status == 'Approved')
                                        <span class="badge badge-success">Aprobado</span>
                                    @elseif($vacation->status == 'Rejected')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @elseif($vacation->status == 'Cancelled')
                                        <span class="badge badge-secondary">Cancelado</span>
                                    @elseif($vacation->status == 'Completed')
                                        <span class="badge badge-info">Completado</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $vacation->remaining_days > 0 ? 'info' : 'secondary' }}">
                                        {{ $vacation->remaining_days }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($vacation->notes, 30) }}</td>
                                <td>{{ $vacation->created_at->format('d/m/Y') }}</td>
                                <td>
                                    
                                    <!-- <div class="btn-group btn-group-sm">
                                        @if($vacation->status == 'Pending')
                                            <button class="btn btn-success btn-approve" data-id="{{ $vacation->id }}" title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger btn-reject" data-id="{{ $vacation->id }}" title="Rechazar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        @if(in_array($vacation->status, ['Pending', 'Approved']))
                                            <button class="btn btn-warning btn-cancel" data-id="{{ $vacation->id }}" title="Cancelar">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif

                                        @if($vacation->status == 'Pending')
                                            <button class="btn btn-info btnEditar" data-id="{{ $vacation->id }}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        
                                        @if($vacation->status == 'Pending')
                                            <form action="{{ route('admin.vacations.destroy', $vacation->id) }}" method="POST" class="d-inline frmDelete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div> -->
                                    <div class="btn-group btn-group-sm action-buttons">
                                        @if($vacation->status == 'Pending')
                                            <button class="btn btn-success btn-approve" data-id="{{ $vacation->id }}" title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger btn-reject" data-id="{{ $vacation->id }}" title="Rechazar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        @if(in_array($vacation->status, ['Pending', 'Approved']))
                                            <button class="btn btn-warning btn-cancel" data-id="{{ $vacation->id }}" title="Cancelar">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif

                                        @if($vacation->status == 'Pending')
                                            <button class="btn btn-info btnEditar" data-id="{{ $vacation->id }}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif

                                        @if($vacation->status == 'Pending')
                                            <form action="{{ route('admin.vacations.destroy', $vacation->id) }}" method="POST" class="frmDelete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Formulario de Vacaciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- El contenido se cargará aquí dinámicamente -->
            </div>
        </div>
    </div>
</div>

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialización de DataTable
            $('#table').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "order": [[1, "desc"]]
            });

            // Eliminar vacación con confirmación
            $(document).on('click', '.frmDelete', function(event) {
                var form = $(this);
                event.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminarlo!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                refreshTable();
                                Swal.fire(
                                    '¡Eliminado!',
                                    'La solicitud ha sido eliminada.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                var error = xhr.responseJSON;
                                Swal.fire(
                                    'Error',
                                    error.message || 'Hubo un problema al eliminar la solicitud.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Aprobar vacación
            $(document).on('click', '.btn-approve', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: '¿Aprobar solicitud?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, aprobar!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ url("admin/vacations") }}/' + id + '/approve', {
                            _token: '{{ csrf_token() }}'
                        }, function(response) {
                            refreshTable();
                            Swal.fire(
                                '¡Aprobado!',
                                response.message,
                                'success'
                            );
                        }).fail(function(xhr) {
                            var error = xhr.responseJSON;
                            Swal.fire(
                                'Error',
                                error.message || 'Error al aprobar la solicitud',
                                'error'
                            );
                        });
                    }
                });
            });

            // Rechazar vacación
            $(document).on('click', '.btn-reject', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: '¿Rechazar solicitud?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, rechazar!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ url("admin/vacations") }}/' + id + '/reject', {
                            _token: '{{ csrf_token() }}'
                        }, function(response) {
                            refreshTable();
                            Swal.fire(
                                '¡Rechazado!',
                                response.message,
                                'success'
                            );
                        }).fail(function(xhr) {
                            var error = xhr.responseJSON;
                            Swal.fire(
                                'Error',
                                error.message || 'Error al rechazar la solicitud',
                                'error'
                            );
                        });
                    }
                });
            });

            // Cancelar vacación
            $(document).on('click', '.btn-cancel', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: '¿Cancelar solicitud?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cancelar!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ url("admin/vacations") }}/' + id + '/cancel', {
                            _token: '{{ csrf_token() }}'
                        }, function(response) {
                            refreshTable();
                            Swal.fire(
                                '¡Cancelado!',
                                response.message,
                                'success'
                            );
                        }).fail(function(xhr) {
                            var error = xhr.responseJSON;
                            Swal.fire(
                                'Error',
                                error.message || 'Error al cancelar la solicitud',
                                'error'
                            );
                        });
                    }
                });
            });

            // Abrir modal para crear nueva solicitud
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.vacations.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal .modal-body').html(response);
                        $('#modal .modal-title').html("Nueva Solicitud de Vacaciones");
                        $('#modal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr, status, error);
                    }
                });
            });

            // Abrir modal para editar solicitud
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/vacations') }}/" + id + "/edit",
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalTitle').text('Editar Solicitud de Vacaciones');
                        $('#modal').modal('show');
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        Swal.fire('Error', 'No se pudo cargar el formulario de edición', 'error');
                    }
                });
            });

            // Función para refrescar la tabla
            // function refreshTable() {
            //     location.reload();
            // }
            //
            
            // table_responsive = $('.table-responsive');
            // table = $('#table').DataTable();

            function refreshTable() {
                $.ajax({
                    url: "{{ route('admin.vacations.index') }}",
                    type: "GET",
                    success: function(response) {
                        const tempDiv = $('<div>').html(response);
                        const newTable = tempDiv.find('.table-responsive').html();
                        $('.table-responsive').html(newTable);
                        $('#table').DataTable({
                        // table_responsive.html(newTable);
                        // table.DataTable({
                            language: {
                                url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                            }
                        });
                    }
                });
            }
            // Hacer que la función refreshTable esté disponible globalmente
            window.refreshTable = refreshTable;

            // Función para refrescar la tabla


            //


            
        });


    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .action-buttons .btn {
            margin-right: 4px;      /* Espacio entre botones */
            min-width: 40px;        /* Asegura que todos tengan ancho mínimo uniforme */
        }

        .action-buttons .btn:last-child {
            margin-right: 0;        /* El último botón no necesita margen */
        }

        .action-buttons form {
            display: inline-block;  /* Para alinear el form con los botones */
            margin: 0;              /* Quitar márgenes extra del form */
        }
    </style>
@stop