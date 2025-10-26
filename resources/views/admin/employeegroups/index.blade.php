@extends('adminlte::page')

@section('title', 'Grupos de Personal')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nuevo Grupo
    </button>
    <h1>Lista de Grupos de Personal</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Turno</th>
                            <th>Zona</th>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Ayudantes</th>
                            <th>Días</th>
                            <th>Estado</th>
                            <th width="120px">Fecha Creación</th>
                            <th width="40px"></th>
                            <th width="40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargan via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 1000px !important;">
        <div class="modal-content border-0 shadow">
            <!-- Header elegante -->
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-users mr-2 text-warning" id="modalTitle"></i>Formulario de Grupos de Personal
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

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        // Variable global para controlar envíos duplicados
        let isSubmitting = false;

        // Función global para refrescar la tabla
        window.refreshTable = function() {
            var table = $('#table').DataTable();
            table.ajax.reload(null, false);
        }

        $(document).ready(function() {
            // Inicialización de DataTable
            $('#table').DataTable({
                "ajax": "{{ route('admin.employeegroups.index') }}",
                "columns": [
                    { "data": "name" },
                    { "data": "shift" },
                    { "data": "zone" },
                    { "data": "vehicle" },
                    { "data": "driver" },
                    { "data": "assistants" },
                    { "data": "days" },
                    { 
                        "data": "status",
                        "render": function(data, type, row) {
                            const badgeClass = data == 'active' ? 'badge-success' : 'badge-danger';
                            const statusText = data == 'active' ? 'Activo' : 'Inactivo';
                            return `<span class="badge ${badgeClass}">${statusText}</span>`;
                        }
                    },
                    { "data": "created_at" },
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
                },
                "responsive": true,
                "autoWidth": false
            });

            // Eliminar grupo con confirmación
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
                    confirmButtonText: 'Sí, eliminarlo!',
                    cancelButtonText: 'Cancelar'
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
                                    'El grupo ha sido eliminado.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema al eliminar el grupo.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear nuevo grupo
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.employeegroups.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal .modal-body').html(response);
                        $('#modal .modal-title').html("Nuevo Grupo de Personal");
                        $('#modal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.log('Error al cargar formulario:', error);
                        Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
                    }
                });
            });

            // Abrir modal para editar grupo
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/employeegroups') }}/" + id + "/edit",
                    type: 'GET',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modal .modal-title').html("Editar Grupo de Personal");
                        $('#modal').modal('show');
                    },
                    error: function(xhr) {
                        console.log('Error al cargar edición:', xhr);
                        Swal.fire('Error', 'No se pudo cargar el formulario de edición', 'error');
                    }
                });
            });

            // SOLO UN evento de submit para evitar duplicados
            $(document).off('submit', '#employeeGroupForm').on('submit', '#employeeGroupForm', function(e) {
                e.preventDefault();
                handleFormSubmit(this);
            });
        });

        // Función única para manejar el envío del formulario
        function handleFormSubmit(form) {
            // Prevenir envíos duplicados
            if (isSubmitting) {
                return false;
            }
            isSubmitting = true;

            // Validar días de trabajo
            const dayCheckboxes = document.querySelectorAll('input[name="days[]"]:checked');
            if (dayCheckboxes.length === 0) {
                Swal.fire('Error', 'Seleccione al menos un día de trabajo', 'error');
                isSubmitting = false;
                return false;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Crear FormData
            const formData = new FormData(form);
            
            // Determinar método
            const method = form.method || 'POST';
            const url = form.action;

            // Enviar vía AJAX
            $.ajax({
                url: url,
                type: method,
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    isSubmitting = false;
                    Swal.close();
                    
                    Swal.fire({
                        title: "¡Éxito!",
                        text: response.message,
                        icon: "success",
                        confirmButtonText: "Aceptar"
                    }).then((result) => {
                        $('#modal').modal('hide');
                        refreshTable();
                    });
                },
                error: function(xhr) {
                    isSubmitting = false;
                    Swal.close();
                    
                    let errorMessage = 'Error al procesar la solicitud';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: "Error!",
                        text: errorMessage,
                        icon: "error"
                    });
                }
            });

            return false;
        }

        // Función global para limpiar selección de empleados
        function clearEmployeeSelection(targetId, containerId) {
            document.getElementById(targetId).value = '';
            if (document.getElementById(containerId)) {
                document.getElementById(containerId).innerHTML = '';
            }
        }
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
        
        .search-results {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            width: calc(100% - 30px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .search-result-item {
            background-color: #f8f9fa;
            transition: all 0.2s;
            cursor: pointer;
        }

        .search-result-item:hover {
            background-color: #007bff !important;
            color: white;
        }

        .search-result-item:hover small {
            color: #e0e0e0 !important;
        }

        .selected-employee .alert {
            margin-bottom: 0;
            padding: 8px 12px;
            position: relative;
        }

        .selected-employee .close {
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 1.2rem;
            line-height: 1;
        }
    </style>
@stop