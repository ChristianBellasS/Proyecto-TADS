@extends('adminlte::page')

@section('title', 'Vehículos')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nuevo Vehículo
    </button>
    <h1>Lista de Vehículos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Placa</th>
                            <th>Año</th>
                            <th>Capacidad (kg)</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Tipo</th>
                            <th>Color</th>
                            <th>Estado</th>
                            <th width="10px"></th>
                            <th width="10px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vehicles as $vehicle)
                            <tr>
                                <td>{{ $vehicle->name }}</td>
                                <td>{{ $vehicle->code }}</td>
                                <td>{{ $vehicle->plate }}</td>
                                <td>{{ $vehicle->year }}</td>
                                <td>{{ $vehicle->load_capacity }}</td>
                                <td>{{ $vehicle->brand->name ?? 'N/A' }}</td>
                                <td>{{ $vehicle->brandModel->name ?? 'N/A' }}</td>
                                <td>{{ $vehicle->type->name ?? 'N/A' }}</td>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div style="width: 20px; height: 20px; background-color: {{ $vehicle->color->code ?? '#CCC' }}; border: 1px solid #ccc; border-radius: 3px; margin-right: 8px;"></div>
                                        {{ $vehicle->color->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    @if($vehicle->status == 1)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-sm btnEditar" data-id="{{ $vehicle->id }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                                <td>
                                    <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" class="frmDelete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
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
                <h5 class="modal-title" id="modalTitle">Formulario de Vehículos</h5>
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
                "ajax": "{{ route('admin.vehicles.index') }}",
                "columns": [
                    { "data": "name" },
                    { "data": "code" },
                    { "data": "plate" },
                    { "data": "year" },
                    { "data": "load_capacity" },
                    { "data": "brand" },
                    { "data": "model" },
                    { "data": "type" },
                    { 
                        "data": "color",
                        "orderable": false,
                        "searchable": false,
                    },
                    { 
                        "data": "status_badge",
                        "orderable": false,
                        "searchable": false,
                    },
                    { 
                        "data": "edit",
                        "orderable": false,
                        "searchable": false,
                    },
                    { 
                        "data": "delete",
                        "orderable": false,
                        "searchable": false,
                    }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                }
            });

            // Eliminar vehículo con confirmación de SweetAlert
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
                                    'El vehículo ha sido eliminado.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema al eliminar el vehículo.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear un nuevo vehículo
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.vehicles.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal .modal-body').html(response);
                        $('#modal .modal-title').html("Nuevo Vehículo");
                        $('#modal').modal('show');

                        // Manejar el envío del formulario dentro del modal
                        $('#modal form').on('submit', function(e) {
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
                                    $('#modal').modal('hide');
                                    refreshTable();
                                    Swal.fire({
                                        title: "Proceso exitoso!",
                                        text: response.message,
                                        icon: "success"
                                    });
                                },
                                error: function(response) {
                                    var error = response.responseJSON;
                                    Swal.fire({
                                        title: "Error!",
                                        text: error.message,
                                        icon: "error"
                                    });
                                }
                            });
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr, status, error);
                    }
                });
            });

            // Abrir modal para editar un vehículo existente
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/vehicles') }}/" + id + "/edit",
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalTitle').text('Editar Vehículo');
                        $('#modal').modal('show');

                        // Manejar el envío del formulario dentro del modal
                        $('#modal form').on('submit', function(e) {
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
                                    $('#modal').modal('hide');
                                    refreshTable();
                                    Swal.fire({
                                        title: "Proceso exitoso!",
                                        text: response.message,
                                        icon: "success"
                                    });
                                },
                                error: function(response) {
                                    var error = response.responseJSON;
                                    Swal.fire({
                                        title: "Error!",
                                        text: error.message,
                                        icon: "error"
                                    });
                                }
                            });
                        });
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        Swal.fire('Error', 'No se pudo cargar el formulario de edición', 'error');
                    }
                });
            });

            // Función para refrescar la tabla
            function refreshTable() {
                var table = $('#table').DataTable();
                table.ajax.reload();
            }
        });
    </script>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@stop