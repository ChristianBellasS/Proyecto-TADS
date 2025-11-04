@extends('adminlte::page')

@section('title', 'Cambios de Programaciones')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Cambios de Programaciones</h1>
        <div>
            <button type="button" class="btn btn-success" id="btnRegistrar">
                <i class="fas fa-exchange-alt"></i> &nbsp; Nuevo Cambio Masivo
            </button>
        </div>
    </div>

    <!-- Modal para Cambios Masivos -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                    <h5 class="modal-title font-weight-bold" id="modalLabel">
                        <i class="fas fa-exchange-alt mr-2 text-warning"></i> Cambio Masivo
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

    <!-- Modal para Detalles del Cambio -->
    <div class="modal fade" id="modalDetails" tabindex="-1" role="dialog" aria-labelledby="modalDetailsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                    <h5 class="modal-title font-weight-bold" id="modalDetailsLabel">
                        <i class="fas fa-history mr-2 text-warning"></i> Detalles del Cambio
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="h5 mb-0">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modalDetailsBody">
                    <!-- El contenido se cargará aquí dinámicamente -->
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fecha de inicio</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Fecha de fin</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo de cambio</label>
                        <select class="form-control" id="tipo_cambio">
                            <option value="">Todos los tipos</option>
                            <option value="turno">Turno</option>
                            <option value="conductor">Conductor</option>
                            <option value="vehiculo">Vehículo</option>
                            <option value="ocupante">Ocupante</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-info btn-block" id="btnFiltrar">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-block" id="btnClearFilter">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Cambios -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm" id="changesTable">
                    <thead class="thead-light">
                        <tr>
                            <th>TIPO DE CAMBIO</th>
                            <th>FECHA CAMBIO</th>
                            <th>ANTES</th>
                            <th>DESPUÉS</th>
                            <th>REALIZADO POR</th>
                            <th width="100px">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedulingChanges as $change)
                            <tr data-change-date="{{ $change->created_at->format('Y-m-d') }}" data-change-type="{{ $change->change_type }}">
                                <td>
                                    <span class="badge 
                                        @if($change->change_type == 'turno') bg-warning
                                        @elseif($change->change_type == 'vehiculo') bg-info
                                        @else bg-success @endif">
                                        <i class="fas 
                                            @if($change->change_type == 'turno') fa-clock
                                            @elseif($change->change_type == 'vehiculo') fa-car
                                            @else fa-user @endif mr-1">
                                        </i>
                                        {{ ucfirst($change->change_type) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        {{ $change->created_at->format('d/m/Y') }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $change->created_at->format('H:i') }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $oldValues = $change->old_values;
                                        $newValues = $change->new_values;
                                    @endphp
                                    
                                    @if($change->change_type == 'turno')
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-warning mr-2"></i>
                                            <div>
                                                <strong class="d-block">{{ $oldValues['name'] ?? 'N/A' }}</strong>
                                                <small class="text-muted">
                                                    {{ $oldValues['hour_in'] ?? '' }} - {{ $oldValues['hour_out'] ?? '' }}
                                                </small>
                                            </div>
                                        </div>
                                    @elseif($change->change_type == 'vehiculo')
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-car text-info mr-2"></i>
                                            <div>
                                                <strong class="d-block">{{ $oldValues['name'] ?? 'N/A' }}</strong>
                                                <small class="text-muted">{{ $oldValues['plate'] ?? '' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user text-success mr-2"></i>
                                            <div>
                                                <strong class="d-block">{{ $oldValues['name'] ?? 'N/A' }}</strong>
                                                <small class="text-muted">
                                                    {{ $oldValues['dni'] ?? '' }} • {{ $oldValues['role'] ?? '' }}
                                                </small>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($change->change_type == 'turno')
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-success mr-2"></i>
                                            <div>
                                                <strong class="d-block">{{ $newValues['name'] ?? 'N/A' }}</strong>
                                                <small class="text-muted">
                                                    {{ $newValues['hour_in'] ?? '' }} - {{ $newValues['hour_out'] ?? '' }}
                                                </small>
                                            </div>
                                        </div>
                                    @elseif($change->change_type == 'vehiculo')
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-car text-success mr-2"></i>
                                            <div>
                                                <strong class="d-block">{{ $newValues['name'] ?? 'N/A' }}</strong>
                                                <small class="text-muted">{{ $newValues['plate'] ?? '' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user text-success mr-2"></i>
                                            <div>
                                                <strong class="d-block">{{ $newValues['name'] ?? 'N/A' }}</strong>
                                                <small class="text-muted">
                                                    {{ $newValues['dni'] ?? '' }} • {{ $newValues['role'] ?? '' }}
                                                </small>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-cog text-primary mr-2"></i>
                                        <span>{{ $change->changedBy->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info btn-view-details" 
                                                data-change-id="{{ $change->id }}" 
                                                title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form action="{{ route('admin.scheduling-changes.destroy', $change->id) }}" 
                                              method="POST" class="d-inline frmDelete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/es.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            var table = $('#changesTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "order": [[1, "desc"]],
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "columnDefs": [
                    { "orderable": false, "targets": [5] }
                ]
            });

            // Variable para almacenar el filtro actual
            var currentFilter = null;

            // Función para aplicar filtros
            function aplicarFiltros() {
                var fechaInicio = $('#fecha_inicio').val();
                var fechaFin = $('#fecha_fin').val();
                var tipoCambio = $('#tipo_cambio').val();

                // Remover filtro anterior si existe
                if (currentFilter !== null) {
                    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(filtro => 
                        filtro !== currentFilter
                    );
                    currentFilter = null;
                }

                // Solo aplicar filtro si hay al menos un filtro activo
                if (fechaInicio || fechaFin || tipoCambio) {
                    currentFilter = function(settings, data, dataIndex) {
                        var row = $('#changesTable').DataTable().row(dataIndex).node();
                        var rowDate = $(row).data('change-date');
                        var rowType = $(row).data('change-type');
                        
                        var matchFecha = true;
                        var matchTipo = true;

                        // Filtrar por fecha
                        if (fechaInicio) {
                            if (rowDate < fechaInicio) {
                                matchFecha = false;
                            }
                        }
                        if (fechaFin) {
                            if (rowDate > fechaFin) {
                                matchFecha = false;
                            }
                        }

                        // Filtrar por tipo de cambio
                        if (tipoCambio) {
                            if (rowType !== tipoCambio) {
                                matchTipo = false;
                            }
                        }

                        return matchFecha && matchTipo;
                    };

                    $.fn.dataTable.ext.search.push(currentFilter);
                }

                table.draw();
                
                // Mostrar mensaje de éxito
                var totalFiltrado = table.rows({ search: 'applied' }).count();
                var totalGeneral = table.rows().count();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Filtros aplicados',
                    text: `Mostrando ${totalFiltrado} de ${totalGeneral} registros`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            // Filtrar cambios al hacer click en el botón
            $('#btnFiltrar').click(function() {
                aplicarFiltros();
            });

            // También filtrar con Enter en los campos
            $('#fecha_inicio, #fecha_fin, #tipo_cambio').keypress(function(e) {
                if (e.which == 13) {
                    aplicarFiltros();
                }
            });

            // Limpiar filtros
            $('#btnClearFilter').click(function() {
                // Limpiar campos
                $('#fecha_inicio').val('');
                $('#fecha_fin').val('');
                $('#tipo_cambio').val('');

                // Remover filtro actual
                if (currentFilter !== null) {
                    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(filtro => 
                        filtro !== currentFilter
                    );
                    currentFilter = null;
                }

                // Recargar tabla sin filtros
                table.draw();

                // Mostrar mensaje de confirmación
                var totalGeneral = table.rows().count();
                Swal.fire({
                    icon: 'success',
                    title: 'Filtros limpiados',
                    text: `Mostrando todos los ${totalGeneral} registros`,
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            // Eliminar cambio con confirmación
            $(document).on('submit', '.frmDelete', function(event) {
                event.preventDefault();
                var form = $(this);
                
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
                            data: form.serialize(),
                            success: function(response) {
                                table.row(form.closest('tr')).remove().draw();
                                Swal.fire({
                                    title: '¡Eliminado!',
                                    text: 'El cambio ha sido eliminado.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error', 'Hubo un problema al eliminar el cambio.', 'error');
                            }
                        });
                    }
                });
            });

            // Ver detalles del cambio
            $(document).on('click', '.btn-view-details', function() {
                var changeId = $(this).data('change-id');
                
                // Mostrar loading en el modal
                $('#modalDetailsBody').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando detalles del cambio...</p>
                    </div>
                `);
                
                $('#modalDetails').modal('show');
                
                // Cargar detalles via AJAX
                $.ajax({
                    url: "{{ route('admin.scheduling-changes.show', ':id') }}".replace(':id', changeId),
                    type: "GET",
                    success: function(response) {
                        $('#modalDetailsBody').html(response);
                    },
                    error: function(xhr) {
                        $('#modalDetailsBody').html(`
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Error al cargar los detalles del cambio
                            </div>
                        `);
                    }
                });
            });

            // Abrir modal para crear nuevo cambio masivo
            $('#btnRegistrar').click(function() {
                $('#modalBody').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando formulario...</p>
                    </div>
                `);
                
                $('#modal').modal('show');
                
                $.ajax({
                    url: "{{ route('admin.scheduling-changes.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalLabel').html(`
                            <i class="fas fa-exchange-alt mr-2 text-warning"></i> Cambio Masivo
                        `);
                    },
                    error: function(xhr, status, error) {
                        $('#modalBody').html(`
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Error al cargar el formulario
                            </div>
                        `);
                    }
                });
            });

            // Establecer fechas por defecto
            var today = new Date().toISOString().split('T')[0];
            var weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            
            // Solo establecer valores por defecto si no hay valores en los inputs
            if (!$('#fecha_fin').val()) {
                $('#fecha_fin').val(today);
            }
            if (!$('#fecha_inicio').val()) {
                $('#fecha_inicio').val(weekAgo.toISOString().split('T')[0]);
            }

            // NO aplicar filtros automáticamente al cargar la página
            // Los filtros solo se aplican cuando se hace click en "Filtrar"
        });
    </script>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/themes/bootstrap/select2-bootstrap.min.css" rel="stylesheet" />
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .btn-group .btn {
            margin: 0 2px;
            padding: 0.25rem 0.5rem;
        }
        .badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }
        .table td {
            vertical-align: middle;
            padding: 0.5rem;
        }
        .table th {
            padding: 0.75rem 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .gap-2 {
            gap: 0.5rem !important;
        }
        .d-flex.gap-2 .btn {
            margin: 0;
        }
        .btn-block {
            min-width: auto;
        }
        .dataTables_wrapper {
            font-size: 0.875rem;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(3, 82, 134, 0.04);
        }
        .thead-light th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
    </style>
@stop