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
<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                <h5 class="modal-title font-weight-bold" id="modalLabel">
                    <i class="fas fa-file-contract mr-2 text-warning"></i>Formulario de Contratos
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
                    { 
                        "data": "edit", 
                        "orderable": false, 
                        "searchable": false,
                        "className": "text-center"
                    },
                    { 
                        "data": "delete", 
                        "orderable": false, 
                        "searchable": false,
                        "className": "text-center"
                    }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "order": [[2, "desc"]],
                "responsive": true,
                "autoWidth": false
            });

            // Abrir modal para crear contrato - CORREGIDO
            $('#btnRegistrar').on('click', function() {
                console.log('Abriendo modal de nuevo contrato...');
                
                $.ajax({
                    url: "{{ route('admin.contracts.create') }}",
                    type: "GET",
                    success: function(response) {
                        console.log('Formulario de creación cargado correctamente');
                        $('#modalBody').html(response);
                        $('#modal .modal-title').html('<i class="fas fa-file-contract mr-2 text-warning"></i> Nuevo Contrato');
                        $('#modal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar formulario de creación:', error);
                        console.log('Status:', status);
                        console.log('Response:', xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar el formulario de creación: ' + error
                        });
                    }
                });
            });

            // Abrir modal para editar contrato - CORREGIDO
            $(document).on('click', '.btnEditar', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var id = $(this).data('id');
                console.log('Editando contrato ID:', id);
                
                if (!id) {
                    console.error('ID no definido en el botón editar');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'ID de contrato no válido'
                    });
                    return;
                }

                var editUrl = "{{ route('admin.contracts.index') }}/" + id + "/edit";
                console.log('URL de edición:', editUrl);
                
                $.ajax({
                    url: editUrl,
                    type: "GET",
                    success: function(response) {
                        console.log('Formulario de edición cargado correctamente');
                        $('#modalBody').html(response);
                        $('#modal .modal-title').html('<i class="fas fa-edit mr-2 text-warning"></i> Editar Contrato');
                        $('#modal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar formulario de edición:', error);
                        console.log('Status:', status);
                        console.log('Response:', xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar el formulario de edición: ' + error
                        });
                    }
                });
            });

            // Manejar envío del formulario - CORREGIDO
            $(document).on('submit', '#contractForm', function(e) {
                e.preventDefault();
                console.log('Formulario enviado');
                
                var form = $(this);
                var formData = new FormData(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var isEdit = method === 'PUT';

                // Mostrar loading
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + (isEdit ? 'Actualizando...' : 'Guardando...'));

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log('Contrato guardado correctamente:', response);
                        $('#modal').modal('hide');
                        table.ajax.reload(null, false);
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al guardar contrato:', error);
                        console.log('Status:', status);
                        console.log('Response:', xhr.responseText);
                        
                        let errorMsg = 'No se pudo ' + (isEdit ? 'actualizar' : 'guardar') + ' el contrato';
                        
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMsg = '<ul style="text-align: left; margin: 0; padding-left: 1rem;">';
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
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Confirmar eliminación - CORREGIDO
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
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar loading
                        var deleteBtn = form.find('button[type="submit"]');
                        var originalText = deleteBtn.html();
                        deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Desactivando...');

                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                console.log('Contrato desactivado:', response);
                                table.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Desactivado!',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error('Error al desactivar contrato:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'No se pudo desactivar el contrato'
                                });
                            },
                            complete: function() {
                                deleteBtn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });

            // Limpiar modal cuando se cierre
            $('#modal').on('hidden.bs.modal', function () {
                $('#modalBody').html('');
                console.log('Modal cerrado y limpiado');
            });

            // Debug: Verificar que el modal existe
            console.log('Modal element exists:', $('#modal').length > 0);
            console.log('BtnRegistrar exists:', $('#btnRegistrar').length > 0);
        });
    </script>

    <style>
        /* Estilos para la tabla */
        #table_wrapper {
            padding: 0;
        }
        
        #table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Estilos para los botones de acción */
        .btnEditar {
            transition: all 0.3s ease;
        }
        
        .btnEditar:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Asegurar que el modal se muestre correctamente */
        .modal {
            z-index: 1060;
        }

        .modal-backdrop {
            z-index: 1050;
        }
    </style>
@stop
