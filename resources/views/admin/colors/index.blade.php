@extends('adminlte::page')

@section('title', 'Colores')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nuevo Color
    </button>
    <h1>Lista de Colores</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Color</th>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Actualización</th>
                            <th width="10px"></th>
                            <th width="10px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($colors as $color)
                            <tr>
                                <td>
                                    <div
                                        style="width: 50px; height: 30px; background-color: {{ $color->code }}; border: 1px solid #ccc; border-radius: 4px;">
                                    </div>
                                </td>
                                <td>{{ $color->name }}</td>
                                <td>{{ $color->code }}</td>
                                <td>{{ $color->description }}</td>
                                <td>{{ $color->created_at }}</td>
                                <td>{{ $color->updated_at }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm btnEditar" data-id="{{ $color->id }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                                <td>
                                    <form action="{{ route('admin.colors.destroy', $color->id) }}" method="POST"
                                        class="frmDelete">
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
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <!-- Header elegante -->
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-car mr-2 text-warning" id="modalTitle"></i>Formulario de Colores
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="h5 mb-0">&times;</span>
                </button>
            </div>

            <!-- Body limpio -->
            <div class="modal-body p-4" id="modalBody" style="max-height: 80vh; overflow-y: auto;">
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
                "ajax": "{{ route('admin.colors.index') }}",
                "columns": [{
                        "data": "color_preview",
                        "orderable": false,
                        "searchable": false,
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "code"
                    },
                    {
                        "data": "description"
                    },
                    {
                        "data": "created_at"
                    },
                    {
                        "data": "updated_at"
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

            // Eliminar color con confirmación de SweetAlert
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
                                    'El color ha sido eliminado.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema al eliminar el color.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear un nuevo color
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.colors.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal .modal-body').html(response);
                        $('#modal .modal-title').html("Nuevo color");
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

            // Abrir modal para editar un color existente
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/colors') }}/" + id + "/edit",
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modal .modal-title').html("Editar color");
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
                        Swal.fire('Error', 'No se pudo cargar el formulario de edición',
                            'error');
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
    <style>
        .nav-sidebar .nav-treeview {
            margin-left: 20px;
        }

        .nav-sidebar .nav-treeview>.nav-item {
            margin-left: 10px;
        }
    </style>
@stop
