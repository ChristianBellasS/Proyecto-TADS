@extends('adminlte::page')

@section('title', 'Contratos')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nuevo Contrato
    </button>
    <h1>Lista de Contratos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Tipo Contrato</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Salario</th>
                            <th>Departamento</th>
                            <th>Posición</th>
                            <th>Activo</th>
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
                    <i class="fas fa-file-contract mr-2 text-warning" id="modalTitle"></i>Formulario de Contratos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="h5 mb-0">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="modalBody" style="max-height: 70vh; overflow-y: auto;">
                <!-- El contenido se cargará aquí dinámicamente -->
            </div>
        </div>
    </div>
</div>

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialización de DataTable con AJAX
            var table = $('#table').DataTable({
                "ajax": "{{ route('admin.contracts.index') }}",
                "columns": [
                    { "data": "employee_name" },
                    { "data": "contract_type" },
                    { "data": "start_date" },
                    { "data": "end_date" },
                    { "data": "salary" },
                    { "data": "department_name" },
                    { "data": "position_name" },
                    { "data": "is_active_badge" },
                    { "data": "edit", "orderable": false, "searchable": false },
                    { "data": "delete", "orderable": false, "searchable": false }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "order": [[2, "desc"]] // Ordenar por fecha de inicio descendente
            });

            // Abrir modal para crear contrato
            $('#btnRegistrar').on('click', function() {
                $.ajax({
                    url: "{{ route('admin.contracts.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modal .modal-title').html('<i class="fas fa-file-contract mr-2 text-warning"></i>Nuevo contrato');
                        $('#modal').modal('show');

                        // Manejar submit del formulario
                        $('#modal form').off('submit').on('submit', function(e) {
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
                                    table.ajax.reload();
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Éxito!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                },
                                error: function(xhr) {
                                    let errorMsg = 'No se pudo guardar el contrato';
                                    
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        let errors = xhr.responseJSON.errors;
                                        errorMsg = '<ul style="text-align: left;">';
                                        Object.keys(errors).forEach(function(key) {
                                            errors[key].forEach(function(error) {
                                                errorMsg += '<li>' + error + '</li>';
                                            });
                                        });
                                        errorMsg += '</ul>';
                                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: errorMsg
                                    });
                                }
                            });
                        });
                    },
                    error: function(xhr) {
                        console.error('Error al cargar formulario:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar el formulario de creación'
                        });
                    }
                });
            });

            // Abrir modal para editar contrato
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ route('admin.contracts.index') }}/" + id + "/edit",
                    type: "GET",
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modal .modal-title').html('<i class="fas fa-file-contract mr-2 text-warning"></i>Editar contrato');
                        $('#modal').modal('show');

                        // Manejar submit del formulario
                        $('#modal form').off('submit').on('submit', function(e) {
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
                                    $('#modal').modal('hide');
                                    table.ajax.reload();
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Actualizado!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                },
                                error: function(xhr) {
                                    let errorMsg = 'No se pudo actualizar el contrato';
                                    
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        let errors = xhr.responseJSON.errors;
                                        errorMsg = '<ul style="text-align: left;">';
                                        Object.keys(errors).forEach(function(key) {
                                            errors[key].forEach(function(error) {
                                                errorMsg += '<li>' + error + '</li>';
                                            });
                                        });
                                        errorMsg += '</ul>';
                                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: errorMsg
                                    });
                                }
                            });
                        });
                    },
                    error: function(xhr) {
                        console.error('Error al cargar formulario de edición:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar el formulario de edición'
                        });
                    }
                });
            });

            // Confirmar eliminación
            $(document).on('submit', '.frmDelete', function(e) {
                e.preventDefault();
                var form = $(this);
                Swal.fire({
                    title: '¿Desactivar contrato?',
                    text: "El contrato quedará inactivo.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Desactivado!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'No se pudo desactivar el contrato'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
