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
                            <th width="140px">Nombre</th>
                            <th width="90px">Turno</th>
                            <th width="120px">Zona</th>
                            <th width="100px">Vehículo</th>
                            <th width="140px">Conductor</th>
                            <th width="180px">Ayudantes</th>
                            <th width="200px">Días</th>
                            <th width="90px">Estado</th>
                            <th width="120px">Fecha Creación</th>
                            <th width="50px"></th>
                            <th width="50px"></th>
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

        // Función para formatear días en badges
        function formatDays(daysString) {
            if (!daysString) return '';
            
            const daysMap = {
                'monday': 'Lun',
                'tuesday': 'Mar', 
                'wednesday': 'Mié',
                'thursday': 'Jue',
                'friday': 'Vie',
                'saturday': 'Sáb',
                'sunday': 'Dom'
            };
            
            if (daysString.includes(',')) {
                const days = daysString.split(',').map(d => d.trim());
                return days.map(day => {
                    const shortDay = daysMap[day] || day.substring(0, 3);
                    return `<span class="day-badge">${shortDay}</span>`;
                }).join('');
            }
            
            const shortDay = daysMap[daysString] || daysString.substring(0, 3);
            return `<span class="day-badge">${shortDay}</span>`;
        }

        // Función para formatear lista de ayudantes
        function formatAssistants(assistantsString) {
            if (!assistantsString) return '';
            
            if (assistantsString.includes(',')) {
                const assistants = assistantsString.split(',').map(a => a.trim());
                return assistants.map(assistant => 
                    `<div class="assistant-item">${assistant}</div>`
                ).join('');
            }
            
            return `<div class="assistant-item">${assistantsString}</div>`;
        }

        $(document).ready(function() {
            // Inicialización de DataTable
            $('#table').DataTable({
                "ajax": "{{ route('admin.employeegroups.index') }}",
                "columns": [
                    { 
                        "data": "name",
                        "width": "140px",
                        "render": function(data, type, row) {
                            return `<div class="group-name">${data || ''}</div>`;
                        }
                    },
                    { 
                        "data": "shift",
                        "width": "90px",
                        "render": function(data, type, row) {
                            const shiftClass = data === 'Mañana' ? 'shift-morning' : 
                                            data === 'Tarde' ? 'shift-afternoon' : 
                                            'shift-night';
                            return `<div class="shift-badge ${shiftClass}">${data || ''}</div>`;
                        }
                    },
                    { 
                        "data": "zone",
                        "width": "120px",
                        "render": function(data, type, row) {
                            return `<div class="zone-name">${data || ''}</div>`;
                        }
                    },
                    { 
                        "data": "vehicle",
                        "width": "100px",
                        "render": function(data, type, row) {
                            return `<div class="vehicle-plate">${data || ''}</div>`;
                        }
                    },
                    { 
                        "data": "driver",
                        "width": "140px",
                        "render": function(data, type, row) {
                            return `<div class="driver-name">${data || ''}</div>`;
                        }
                    },
                    { 
                        "data": "assistants",
                        "width": "180px",
                        "render": function(data, type, row) {
                            return `<div class="assistants-list">${formatAssistants(data)}</div>`;
                        }
                    },
                    { 
                        "data": "days",
                        "width": "200px",
                        "render": function(data, type, row) {
                            return `<div class="days-container">${formatDays(data)}</div>`;
                        }
                    },
                    { 
                        "data": "status",
                        "width": "90px",
                        "render": function(data, type, row) {
                            const badgeClass = data == 'active' ? 'status-active' : 'status-inactive';
                            const statusText = data == 'active' ? 'Activo' : 'Inactivo';
                            return `<span class="status-badge ${badgeClass}">${statusText}</span>`;
                        }
                    },
                    { 
                        "data": "created_at",
                        "width": "120px",
                        "render": function(data, type, row) {
                            if (!data) return '';
                            const date = new Date(data);
                            return `<div class="creation-date">
                                <small>${date.toLocaleDateString('es-ES')}</small>
                                <br>
                                <small class="text-muted">${date.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}</small>
                            </div>`;
                        }
                    },
                    {
                        "data": "edit",
                        "width": "50px",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            return `
                                    <button class="btn btn-warning btn-sm btnEditar" data-id="${row.id}" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                            `;
                        }
                    },
                    {
                        "data": "delete",
                        "width": "50px",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            return `
                                    <form action="{{ url('admin/employeegroups') }}/${row.id}" method="POST" class="frmDelete d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                            `;
                        }
                    }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "responsive": true,
                "autoWidth": false,
                "pageLength": 10,
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td').css('min-height', '70px');
                }
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
            if (isSubmitting) return false;
            isSubmitting = true;

            const dayCheckboxes = document.querySelectorAll('input[name="days[]"]:checked');
            if (dayCheckboxes.length === 0) {
                Swal.fire('Error', 'Seleccione al menos un día de trabajo', 'error');
                isSubmitting = false;
                return false;
            }

            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData(form);
            const method = form.method || 'POST';
            const url = form.action;

            $.ajax({
                url: url,
                type: method,
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                    Swal.fire({ title: "Error!", text: errorMessage, icon: "error" });
                }
            });

            return false;
        }

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

        /* ESTILOS MEJORADOS PARA LA TABLA */
        .table td {
            vertical-align: middle !important;
            padding: 12px 8px;
        }

        .table th {
            padding: 12px 8px;
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            vertical-align: middle !important;
        }

        /* Contenedor para botones alineados */
        .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }

        /* Asegurar que los botones tengan el mismo tamaño y estén centrados */
        .btn-sm {
            padding: 0.35rem 0.5rem !important;
            font-size: 0.8rem !important;
            border-radius: 6px !important;
            width: 32px !important;
            height: 32px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
        }

        /* Centrado perfecto para las celdas de botones */
        #table tbody tr td:nth-last-child(1),
        #table tbody tr td:nth-last-child(2) {
            text-align: center !important;
            vertical-align: middle !important;
        }

        .text-center {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            height: 100% !important;
        }

        /* Estilos específicos para cada columna */
        .group-name {
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.3;
        }

        .shift-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            display: inline-block;
            min-width: 60px;
        }

        .shift-morning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .shift-afternoon {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .shift-night {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .zone-name {
            font-weight: 500;
            color: #6c757d;
            line-height: 1.3;
        }

        .vehicle-plate {
            background-color: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 600;
            color: #495057;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .driver-name {
            font-weight: 500;
            color: #2c3e50;
            line-height: 1.3;
        }

        .assistants-list {
            max-height: 80px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .assistant-item {
            padding: 3px 6px;
            margin-bottom: 2px;
            background-color: #e9ecef;
            border-radius: 4px;
            font-size: 0.8rem;
            color: #495057;
            border-left: 3px solid #6c757d;
        }

        .assistant-item:last-child {
            margin-bottom: 0;
        }

        .days-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .day-badge {
            padding: 3px 6px;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            min-width: 28px;
            text-align: center;
        }

        .status-badge {
            padding: 6px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            display: block;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .creation-date {
            text-align: center;
            line-height: 1.3;
        }

        .creation-date small {
            font-size: 0.8rem;
        }

        /* Scroll suave */
        .table-responsive {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        /* Mejorar la legibilidad en pantallas pequeñas */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .table td, .table th {
                padding: 8px 4px;
            }
            
            .shift-badge, .status-badge {
                font-size: 0.7rem;
                padding: 3px 6px;
            }

            .btn-sm {
                width: 28px !important;
                height: 28px !important;
                padding: 0.25rem 0.4rem !important;
            }
        }

        /* Personalizar scrollbar */
        .assistants-list::-webkit-scrollbar {
            width: 4px;
        }

        .assistants-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }

        .assistants-list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }

        .assistants-list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
@stop