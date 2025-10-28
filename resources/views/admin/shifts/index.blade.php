@extends('adminlte::page')

@section('title', 'Turnos')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nuevo Turno
    </button>
    <h1>Lista de Turnos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                            <th width="10px"></th>
                            <th width="10px"></th>
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

<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-clock mr-2 text-warning" id="modalTitle"></i>Formulario de Turnos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="h5 mb-0">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="modalBody" style="max-height: 80vh; overflow-y: auto;">
                <!-- El contenido se cargará aquí dinámicamente -->
            </div>
        </div>
    </div>
</div>

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
    var table;
    $(document).ready(function() {
        // Inicialización de DataTable
        table = $('#table').DataTable({
            "ajax": "{{ route('admin.shifts.index') }}",
            "columns": [
                { "data": "name" },
                { "data": "description" },
                { "data": "hour_in" },
                { "data": "hour_out" },
                { "data": "edit", "orderable": false, "searchable": false },
                { "data": "delete", "orderable": false, "searchable": false }
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            },
            "order": [[0, "desc"]]
        });

        // Eliminar turno con confirmación de SweetAlert
        $(document).on('submit', '.frmDelete', function(event) {
            var form = $(this);
            event.preventDefault();

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
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
                        data: form.serialize(),
                        success: function(response) {
                            table.ajax.reload();
                            Swal.fire(
                                '¡Eliminado!',
                                response.message || 'El turno ha sido eliminado.',
                                'success'
                            );
                        },
                        error: function(xhr) {
                            var error = xhr.responseJSON;
                            Swal.fire(
                                'Error',
                                error.message || 'Hubo un problema al eliminar el turno.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // Abrir modal para crear un nuevo turno
        $('#btnRegistrar').click(function() {
            $.ajax({
                url: "{{ route('admin.shifts.create') }}",
                type: "GET",
                success: function(response) {
                    $('#modal .modal-body').html(response);
                    $('#modal .modal-title').html('<i class="fas fa-clock mr-2 text-warning"></i>Nuevo Turno');
                    $('#modal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.log(xhr, status, error);
                    Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
                }
            });
        });

        // Abrir modal para editar un turno existente
        $(document).on('click', '.btnEditar', function() {
            var id = $(this).data('id');
            $.ajax({
                url: "{{ url('admin/shifts') }}/" + id + "/edit",
                type: 'GET',
                dataType: 'html',
                success: function(response) {
                    $('#modalBody').html(response);
                    $('#modal .modal-title').html('<i class="fas fa-clock mr-2 text-warning"></i>Editar Turno');
                    $('#modal').modal('show');
                },
                error: function(xhr) {
                    console.log('Error:', xhr);
                    Swal.fire('Error', 'No se pudo cargar el formulario de edición', 'error');
                }
            });
        });

        // Manejar el envío del formulario (crear/editar) via AJAX
        $(document).on('submit', '#shiftForm', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var url = form.attr('action');
            var method = form.find('input[name="_method"]').val() || 'POST';
            
            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#modal').modal('hide');
                    table.ajax.reload();
                    Swal.fire(
                        '¡Éxito!',
                        response.message,
                        'success'
                    );
                },
                error: function(xhr) {
                    var error = xhr.responseJSON;
                    var errorMessage = 'Hubo un problema al guardar el turno.';
                    
                    if (error && error.errors) {
                        errorMessage = '<ul class="text-left">';
                        $.each(error.errors, function(key, value) {
                            errorMessage += '<li>' + value[0] + '</li>';
                        });
                        errorMessage += '</ul>';
                    } else if (error && error.message) {
                        errorMessage = error.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: errorMessage
                    });
                }
            });
        });
    });
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    .nav-sidebar .nav-treeview {
        margin-left: 20px;
    }
    .nav-sidebar .nav-treeview > .nav-item {
        margin-left: 10px;
    }
</style>
@stop