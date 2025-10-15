@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nueva Modelo
    </button>
    <h1>Lista de Modelos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive"> <!-- Hacer la tabla responsiva -->
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Modelo</th>
                            <th>Marca</th>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Actualización</th>
                            <th width="10px"></th>
                            <th width="10px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($models as $model)
                            <tr>
                                <td>{{ $model->name }}</td>
                                <td>{{ $model->brandname }}</td>
                                <td>{{ $model->code }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->created_at }}</td>
                                <td>{{ $model->updated_at }}</td>

                                <td>
                                    <button class="btn btn-warning btn-sm btnEditar" data-id="{{ $model->id }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                                <td>
                                    <form action="{{ route('admin.models.destroy', $model->id) }}" method="POST"
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
            </div> <!-- Fin de la tabla responsiva -->
        </div>
    </div>
@stop

<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document"> <!-- Centrar el modal -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Formulario de Modelo</h5>
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
                "ajax": "{{ route('admin.models.index') }}", // Usar el nombre correcto de la ruta
                "columns": [{
                        "data": "name",
                    },
                    {
                        "data": "brandname",
                    },
                    {
                        "data": "code",
                    },
                    {
                        "data": "description",
                    },
                    {
                        "data": "created_at",
                    },
                    {
                        "data": "updated_at",
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
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                }
            });

            // Eliminar marca con confirmación de SweetAlert
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
                    confirmButtonText: 'Sí, eliminarla!'
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
                                    'La marca ha sido eliminada.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema al eliminar la marca.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear una nueva marca
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.models.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal .modal-body').html(response);
                        $('#modal .modal-title').html("Nueva marca");
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
                        console.log(xhr, status, error); // Depurar si la petición AJAX falla
                    }
                });
            });

            // Abrir modal para editar una marca existente
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id'); // Obtener el id de la marca seleccionada
                $.ajax({
                    url: "{{ url('admin/models') }}/" + id +
                        "/edit", // Usamos la URL correcta para la edición
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#modalBody').html(
                            response); // Cargar el formulario de edición en el modal
                        $('#modalTitle').text('Editar Marca');
                        $('#modal').modal('show'); // Mostrar el modal

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

            // Función para refrescar la tabla después de agregar o eliminar una marca
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
