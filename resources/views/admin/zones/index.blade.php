@extends('adminlte::page')

@section('title', 'Zonas')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nueva Zona
    </button>
    <h1>Lista de Zonas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Distrito</th>
                            <th>Provincia</th>
                            <th>Departamento</th>
                            <th>Descripción</th>
                            <th>Coordenadas</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th width="10px"></th>
                            <th width="10px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($zones as $zone)
                            <tr>
                                <td>{{ $zone->name }}</td>
                                <td>{{ $zone->district->name }}</td>
                                <td>{{ $zone->district->province->name }}</td>
                                <td>{{ $zone->district->province->department->name }}</td>
                                <td>{{ Str::limit($zone->description, 50) }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $zone->coordinates->count() }} puntos</span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $zone->status ? 'success' : 'danger' }}">
                                        {{ $zone->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>{{ $zone->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm btnEditar" data-id="{{ $zone->id }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                                <td>
                                    <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST"
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Formulario de Zonas</h5>
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
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                }
            });

            // Eliminar zona con confirmación
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
                                    'La zona ha sido eliminada.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema al eliminar la zona.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear una nueva zona
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.zones.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal .modal-body').html(response);
                        $('#modal .modal-title').html("Nueva Zona");
                        $('#modal').modal('show');
                        initializeMap();
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr, status, error);
                    }
                });
            });

            // Abrir modal para editar una zona existente
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/zones') }}/" + id + "/edit",
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalTitle').text('Editar Zona');
                        $('#modal').modal('show');
                        initializeMap();
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
                $.ajax({
                    url: "{{ route('admin.zones.index') }}",
                    type: "GET",
                    success: function(response) {
                        // Extraer solo la tabla del response
                        const tempDiv = $('<div>').html(response);
                        const newTable = tempDiv.find('.table-responsive').html();

                        // Reemplazar solo la tabla
                        $('.table-responsive').html(newTable);

                        // Re-inicializar DataTable
                        $('#table').DataTable({
                            "language": {
                                "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                            }
                        });
                    }
                });
            }

            // Inicializar mapa (función placeholder)
            function initializeMap() {
                console.log('Mapa inicializado');
                // Aquí iría la inicialización de Google Maps o Leaflet
            }
        });
    </script>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@stop
