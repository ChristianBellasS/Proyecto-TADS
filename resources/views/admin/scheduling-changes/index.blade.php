@extends('adminlte::page')

@section('title', 'Cambios de Programaciones')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nuevo Cambio
    </button>
    <h1>Cambios de Programaciones</h1>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary btn-block" id="btnFiltrar">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Programación</th>
                            <th>Tipo de Cambio</th>
                            <th>Realizado por</th>
                            <th>Fecha del Cambio</th>
                            <th>Motivo</th>
                            <th width="120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedulingChanges as $change)
                            <tr>
                                <td>{{ $change->id }}</td>
                                <td>#{{ $change->scheduling_id }}</td>
                                <td>
                                    <span class="badge 
                                        @if($change->change_type == 'turno') bg-warning
                                        @elseif($change->change_type == 'vehiculo') bg-info
                                        @else bg-success @endif">
                                        {{ ucfirst($change->change_type) }}
                                    </span>
                                </td>
                                <td>{{ $change->changedBy->name ?? 'N/A' }}</td>
                                <td>{{ $change->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ Str::limit($change->reason, 50) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('admin.scheduling-changes.destroy', $change->id) }}" method="POST" class="d-inline frmDelete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Eliminar">
                                                <i class="fa-solid fa-trash"></i>
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

<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-exchange-alt mr-2 text-warning" id="modalTitle"></i>Gestión de Cambios
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

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/es.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#table').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "order": [[0, "desc"]]
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
                    confirmButtonText: 'Sí, eliminarlo!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                location.reload();
                                Swal.fire('¡Eliminado!', 'El cambio ha sido eliminado.', 'success');
                            },
                            error: function(xhr) {
                                Swal.fire('Error', 'Hubo un problema al eliminar el cambio.', 'error');
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear nuevo cambio - VERSIÓN SIMPLIFICADA
            $('#btnRegistrar').click(function() {
                console.log('Intentando cargar formulario de creación...');
                
                $.ajax({
                    url: "{{ route('admin.scheduling-changes.create') }}",
                    type: "GET",
                    success: function(response) {
                        console.log('Formulario cargado exitosamente');
                        $('#modalBody').html(response);
                        $('#modalTitle').text("Nuevo Cambio");
                        $('#modal').modal('show');
                        
                        // Inicializar Select2 si existe
                        if ($('.select2').length) {
                            $('.select2').select2({
                                width: '100%',
                                dropdownParent: $('#modal')
                            });
                        }
                        
                        // Manejar cambios en tipo de cambio
                        $('#change_type').change(function() {
                            var tipo = $(this).val();
                            $('.change-field').hide();
                            if (tipo) {
                                $('.' + tipo + '-fields').show();
                            }
                        }).trigger('change');
                        
                        // Manejar envío del formulario
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
                                    location.reload();
                                    Swal.fire('Éxito!', response.message, 'success');
                                },
                                error: function(xhr) {
                                    var error = xhr.responseJSON;
                                    Swal.fire('Error!', error?.message || 'Error desconocido', 'error');
                                }
                            });
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', xhr);
                        Swal.fire({
                            title: "Error!",
                            text: "No se pudo cargar el formulario. Estado: " + xhr.status,
                            icon: "error"
                        });
                    }
                });
            });

            // Abrir modal para editar
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                
                $.ajax({
                    url: "{{ url('admin/scheduling-changes') }}/" + id + "/edit",
                    type: 'GET',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalTitle').text("Editar Cambio");
                        $('#modal').modal('show');
                        
                        // Inicializar Select2 si existe
                        if ($('.select2').length) {
                            $('.select2').select2({
                                width: '100%',
                                dropdownParent: $('#modal')
                            });
                        }
                        
                        // Manejar cambios en tipo de cambio
                        $('#change_type').change(function() {
                            var tipo = $(this).val();
                            $('.change-field').hide();
                            if (tipo) {
                                $('.' + tipo + '-fields').show();
                            }
                        }).trigger('change');
                        
                        // Manejar envío del formulario
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
                                    location.reload();
                                    Swal.fire('Éxito!', response.message, 'success');
                                },
                                error: function(xhr) {
                                    var error = xhr.responseJSON;
                                    Swal.fire('Error!', error?.message || 'Error desconocido', 'error');
                                }
                            });
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'No se pudo cargar el formulario de edición', 'error');
                    }
                });
            });

            // Establecer fechas por defecto
            var today = new Date().toISOString().split('T')[0];
            var weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            
            $('#fecha_fin').val(today);
            $('#fecha_inicio').val(weekAgo.toISOString().split('T')[0]);
        });
    </script>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .btn-group .btn {
            margin: 0 2px;
        }
    </style>
@stop