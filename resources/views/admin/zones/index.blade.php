@extends('adminlte::page')

@section('title', 'Zonas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Lista de Zonas</h1>
        <div>
            <button id="btnVerMapa" class="btn btn-success">
                <i class="fas fa-map-marked-alt"></i> Ver mapa de zonas
            </button>
            <button type="button" class="btn btn-success" id="btnRegistrar">
                <i class="fa fa-plus"></i> Nueva Zona
            </button>
        </div>
    </div>
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
                                <td>
                                    <button class="btn btn-primary btn-sm btnVerMapaZona" data-id="{{ $zone->id }}">
                                        <i class="fas fa-map"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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

    <div class="modal fade" id="modalMapaZonas" tabindex="-1" role="dialog" aria-labelledby="modalMapaZonasLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-map-marked-alt mr-2"></i>Explorador de zonas geográficas
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <!-- Panel de Control -->
                    <div class="row no-gutters">
                        <!-- Sidebar de Información -->
                        <div class="col-md-3 bg-light border-right">
                            <div class="p-3">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-filter mr-2"></i>Filtros de búsqueda
                                </h6>

                                <div class="form-group">
                                    <label class="font-weight-bold text-dark mb-2">
                                        <i class="fas fa-map-pin text-primary"></i> Departamento
                                    </label>
                                    <select id="map_department" class="form-control form-control-sm border-primary">
                                        <option value="">Seleccione departamento</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-dark mb-2">
                                        <i class="fas fa-city text-success"></i> Provincia
                                    </label>
                                    <select id="map_province" class="form-control form-control-sm border-success">
                                        <option value="">Seleccione provincia</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold text-dark mb-2">
                                        <i class="fas fa-building text-warning"></i> Distrito
                                    </label>
                                    <select id="map_district" class="form-control form-control-sm border-warning">
                                        <option value="">Seleccione distrito</option>
                                    </select>
                                </div>

                                <!-- Estadísticas en Tiempo Real -->
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-chart-bar mr-2"></i>Estadísticas
                                    </h6>

                                    <div id="statsContainer" class="text-center">
                                        <div class="alert alert-info py-2 mb-2">
                                            <small class="font-weight-bold">ZONAS ENCONTRADAS</small>
                                            <div class="h4 mb-0" id="zonesCount">0</div>
                                        </div>

                                        <div class="row text-center">
                                            <div class="col-12">
                                                <div class="border rounded p-2 bg-success text-white mb-2">
                                                    <small>ACTIVAS</small>
                                                    <div class="h6 mb-0" id="activeZones">0</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="border rounded p-2 bg-secondary text-white">
                                            <small>TOTAL PUNTOS</small>
                                            <div class="h6 mb-0" id="totalPoints">0</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Leyenda Interactiva -->
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-palette mr-2"></i>Leyenda del Mapa
                                    </h6>
                                    <div class="legend-item d-flex align-items-center mb-2">
                                        <div class="legend-color bg-success mr-2"
                                            style="width: 20px; height: 10px; border-radius: 2px;"></div>
                                        <small class="text-dark">Zonas Activas</small>
                                    </div>
                                    <div class="legend-item d-flex align-items-center">
                                        <div class="legend-color bg-primary mr-2"
                                            style="width: 20px; height: 10px; border-radius: 2px;"></div>
                                        <small class="text-dark">Distrito Seleccionado</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mapa Principal -->
                        <div class="col-md-9">
                            <!-- Barra de Herramientas del Mapa -->
                            <div class="bg-white border-bottom p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-0 text-dark" id="currentLocation">
                                            <i class="fas fa-crosshairs text-primary mr-2"></i>
                                            <span id="locationText">Seleccione una ubicación</span>
                                        </h6>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" id="zoomIn">
                                                <i class="fas fa-search-plus"></i>
                                            </button>
                                            <button class="btn btn-outline-primary" id="zoomOut">
                                                <i class="fas fa-search-minus"></i>
                                            </button>
                                            <button class="btn btn-outline-info" id="resetView">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenedor del Mapa -->
                            <div class="map-container position-relative">
                                <div id="mapView" style="height: 630px; width: 100%;"></div>

                                <!-- Información de Zona Hover -->
                                <div id="zoneTooltip" class="position-absolute bg-white border rounded shadow-sm p-2"
                                    style="display: none; z-index: 1000; max-width: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <div class="w-100">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle text-primary mr-1"></i>
                                    <span id="mapInfo">Haz clic en un polígono para ver detalles</span>
                                </small>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">
                                    <i class="fas fa-times mr-1"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Nuevo modal para ver mapa de zona -->
    <!-- Modal: Ver zona en mapa -->
    <div class="modal fade" id="modalVerZonaMapa" tabindex="-1" role="dialog" aria-labelledby="mapZoneModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="mapZoneModalLabel">
                        <i class="fas fa-map"></i> Mapa de la Zona
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="mapZoneBody">
                    <!-- Contenido dinámico -->
                </div>
            </div>
        </div>
    </div>

    <!--  FInal de nuevo modal -->
@stop

@section('css')
    {{-- CSS de Leaflet --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* Estilos para el modal de zona */
        .zone-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .zone-icon {
            transition: transform 0.3s ease;
        }

        .zone-icon:hover {
            transform: scale(1.05);
        }

        .stat-card {
            transition: all 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .rounded-lg {
            border-radius: 12px !important;
        }

        .custom-marker {
            background: transparent !important;
            border: none !important;
        }

        /* Mejoras para la tabla de coordenadas */
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }

        .font-monospace {
            font-family: 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', monospace;
        }

        /* Estilos para el mapa */
        .leaflet-popup-content {
            margin: 12px !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 8px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stat-card .h4 {
                font-size: 1.25rem !important;
            }

            .zone-header {
                text-align: center;
            }

            .zone-header .d-flex {
                flex-direction: column;
                text-align: center;
            }

            .zone-icon {
                margin-right: 0 !important;
                margin-bottom: 1rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

    <script>
        $(document).ready(function() {
            // --- Inicializar DataTable ---
            $('#table').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                }
            });

            // --- Confirmar eliminación ---
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
                                Swal.fire('¡Eliminado!', 'La zona ha sido eliminada.',
                                    'success');
                            },
                            error: function() {
                                Swal.fire('Error',
                                    'Hubo un problema al eliminar la zona.', 'error'
                                );
                            }
                        });
                    }
                });
            });

            // --- Registrar nueva zona ---
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.zones.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal .modal-body').html(response);
                        $('#modal .modal-title').html("Nueva Zona");
                        $('#modal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr, status, error);
                    }
                });
            });

            // --- Editar zona ---
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
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        Swal.fire('Error', 'No se pudo cargar el formulario de edición',
                            'error');
                    }
                });
            });

            // --- Refrescar tabla ---
            function refreshTable() {
                $.ajax({
                    url: "{{ route('admin.zones.index') }}",
                    type: "GET",
                    success: function(response) {
                        const tempDiv = $('<div>').html(response);
                        const newTable = tempDiv.find('.table-responsive').html();
                        $('.table-responsive').html(newTable);
                        $('#table').DataTable({
                            language: {
                                url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                            }
                        });
                    }
                });
            }

            $(document).on('zonaCreada', function() {
                refreshTable();
            });

            $('#modal').on('hidden.bs.modal', function() {
                refreshTable();
            });

            // --- Abrir mapa general ---
            $('#btnVerMapa').click(function() {
                $('#modalMapaZonas').modal('show');
            });
        });

        let mapGeneral = null;
        let zoneLayers = [];
        let currentDistrictMarker = null;

        const DEFAULT_DEPARTMENT_ID = 14;
        const DEFAULT_PROVINCE_NAME = 'Chiclayo';
        const DEFAULT_DISTRICT_NAME = 'Jose Leonardo Ortiz';

        // Función para actualizar estadísticas en tiempo real
        function updateStats(zones) {
            const totalZones = zones.length;
            const activeZones = zones.filter(zone => zone.status).length;
            const totalPoints = zones.reduce((sum, zone) => sum + (zone.coordinates ? zone.coordinates.length : 0), 0);

            $('#zonesCount').text(totalZones);
            $('#activeZones').text(activeZones);
            $('#totalPoints').text(totalPoints);

            // Actualizar información de ubicación
            const districtName = $('#map_district option:selected').text();
            const provinceName = $('#map_province option:selected').text();
            const departmentName = $('#map_department option:selected').text();

            if (districtName && districtName !== 'Seleccione distrito') {
                $('#locationText').html(`<strong>${districtName}</strong>, ${provinceName}, ${departmentName}`);
                $('#mapInfo').text(`${totalZones} zonas encontradas en esta ubicación`);
            }
        }

        function initializeGeneralMap() {
            if (!mapGeneral) {
                mapGeneral = L.map('mapView').setView([-6.7716, -79.8441], 13);

                // Capa base mejorada
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(mapGeneral);

                // Controles de zoom personalizados
                L.control.zoom({
                    position: 'topright'
                }).addTo(mapGeneral);

                // Escala
                L.control.scale({
                    imperial: false,
                    position: 'bottomleft'
                }).addTo(mapGeneral);

                setupMapControls();
            }
        }

        // Configurar controles del mapa
        function setupMapControls() {
            $('#zoomIn').click(function() {
                mapGeneral.zoomIn();
            });

            $('#zoomOut').click(function() {
                mapGeneral.zoomOut();
            });

            $('#resetView').click(function() {
                const districtName = $('#map_district option:selected').text();
                if (districtName && districtName !== 'Seleccione distrito') {
                    $('#map_district').trigger('change');
                } else {
                    mapGeneral.setView([-6.7716, -79.8441], 13);
                }
            });
        }

        // Cargar departamentos
        function cargarDepartamentos() {
            $('#map_department').empty().append('<option value="">Seleccione</option>');
            $('#map_province').empty().append('<option value="">Seleccione</option>');
            $('#map_district').empty().append('<option value="">Seleccione</option>');

            $.get('{{ route('admin.get.departments') }}')
                .done(function(departments) {
                    departments.forEach(dep => {
                        $('#map_department').append(`<option value="${dep.id}">${dep.name}</option>`);
                    });

                    // Seleccionar Lambayeque por defecto
                    $('#map_department').val(DEFAULT_DEPARTMENT_ID);

                    // Cargar provincias del departamento
                    loadProvinces(DEFAULT_DEPARTMENT_ID).then(function(provinces) {
                        const foundProv = provinces.find(p =>
                            p.name.toLowerCase().includes(DEFAULT_PROVINCE_NAME.toLowerCase())
                        );
                        if (!foundProv) return;
                        $('#map_province').val(foundProv.id);

                        // Cargar distritos
                        loadDistricts(foundProv.id).then(function(districts) {
                            const foundDist = districts.find(d =>
                                d.name.toLowerCase().includes(DEFAULT_DISTRICT_NAME.toLowerCase())
                            );
                            if (!foundDist) return;

                            $('#map_district').val(foundDist.id).trigger('change');
                        });
                    });
                })
                .fail(() => console.error('Error al cargar departamentos'));
        }

        function loadProvinces(departmentId) {
            return $.get('{{ route('admin.get.provinces', '') }}/' + departmentId)
                .then(function(data) {
                    $('#map_province').empty().append('<option value="">Seleccione</option>');
                    data.forEach(p => $('#map_province').append(`<option value="${p.id}">${p.name}</option>`));
                    return data;
                });
        }

        function loadDistricts(provinceId) {
            return $.get('{{ route('admin.get.districts', '') }}/' + provinceId)
                .then(function(data) {
                    $('#map_district').empty().append('<option value="">Seleccione</option>');
                    data.forEach(d => $('#map_district').append(`<option value="${d.id}">${d.name}</option>`));
                    return data;
                });
        }

        function geocodeDistrict(districtName) {
            return new Promise(function(resolve, reject) {
                // Mostrar loading
                $('#mapLoading').show();

                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search',
                    data: {
                        q: districtName + ', Perú',
                        format: 'json',
                        limit: 1
                    },
                    success: function(data) {
                        if (data && data.length > 0) {
                            const result = data[0];
                            const coords = {
                                lat: parseFloat(result.lat),
                                lng: parseFloat(result.lon),
                                boundingbox: result.boundingbox
                            };
                            console.log('Coordenadas encontradas para:', districtName, coords);
                            resolve(coords);
                        } else {
                            reject('No se encontraron coordenadas para: ' + districtName);
                        }
                    },
                    error: function() {
                        reject('Error en la geocodificación');
                    }
                });
            });
        }

        function getZoneColor(zone, index) {
            const colorPalette = [{
                    border: '#007bff',
                    fill: 'rgba(0, 123, 255, 0.3)'
                },
                {
                    border: '#28a745',
                    fill: 'rgba(40, 167, 69, 0.3)'
                },
                {
                    border: '#ffc107',
                    fill: 'rgba(255, 193, 7, 0.3)'
                },
                {
                    border: '#dc3545',
                    fill: 'rgba(220, 53, 69, 0.3)'
                },
                {
                    border: '#6f42c1',
                    fill: 'rgba(111, 66, 193, 0.3)'
                },
                {
                    border: '#e83e8c',
                    fill: 'rgba(232, 62, 140, 0.3)'
                },
                {
                    border: '#20c997',
                    fill: 'rgba(32, 201, 151, 0.3)'
                },
                {
                    border: '#fd7e14',
                    fill: 'rgba(253, 126, 20, 0.3)'
                }
            ];

            return colorPalette[index % colorPalette.length];
        }

        function mostrarZonasEnMapa(zones, districtCoords) {
            zoneLayers.forEach(layer => mapGeneral.removeLayer(layer));
            zoneLayers = [];

            if (currentDistrictMarker) {
                mapGeneral.removeLayer(currentDistrictMarker);
            }

            let zonasConCoordenadas = 0;
            let allZoneBounds = [];

            if (zones && zones.length > 0) {
                zones.forEach((zone, index) => {
                    if (zone.coordinates && zone.coordinates.length > 0) {
                        const coords = zone.coordinates.map(c => [parseFloat(c.latitude), parseFloat(c.longitude)]);

                        // Color basado en el estado de la zona
                        const colorInfo = getZoneColor(zone, index);

                        const polygon = L.polygon(coords, {
                            color: colorInfo.border,
                            fillColor: colorInfo.fill,
                            fillOpacity: 0.4,
                            weight: 3,
                            opacity: 0.8
                        }).addTo(mapGeneral);

                        // Popup mejorado
                        const popupContent = `
                        <div class="zone-popup">
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-map-marker-alt"></i> ${zone.name || 'Sin nombre'}
                            </h6>
                            <div class="mb-2">
                                <span class="badge badge-${zone.status ? 'success' : 'warning'}">
                                    ${zone.status ? 'Activa' : 'Inactiva'}
                                </span>
                                <span class="badge badge-info ml-1">
                                    ${zone.coordinates.length} puntos
                                </span>
                            </div>
                            ${zone.description ? `<p class="mb-2 small">${zone.description}</p>` : ''}
                        </div>
                    `;

                        polygon.bindPopup(popupContent, {
                            className: 'zone-popup-container',
                            maxWidth: 300
                        });

                        // Eventos de hover para tooltip
                        polygon.on('mouseover', function(e) {
                            const tooltip = $(`
                            <div>
                                <strong>${zone.name}</strong><br>
                                <small>${zone.status ? 'Activa' : 'Inactiva'} • ${zone.coordinates.length} puntos</small>
                            </div>
                        `);
                            $('#zoneTooltip').html(tooltip).show();
                        });

                        polygon.on('mouseout', function() {
                            $('#zoneTooltip').hide();
                        });

                        polygon.on('mousemove', function(e) {
                            const tooltip = $('#zoneTooltip');
                            const mapContainer = $('#mapView');
                            const mapOffset = mapContainer.offset();

                            tooltip.css({
                                left: (e.originalEvent.clientX - mapOffset.left + 10) + 'px',
                                top: (e.originalEvent.clientY - mapOffset.top - tooltip
                                    .outerHeight() - 10) + 'px'
                            });
                        });

                        zoneLayers.push(polygon);
                        zonasConCoordenadas++;
                        $(document).on('click', '.btnVerMapaZona', function() {
                            var id = $(this).data('id');

                            $.ajax({
                                url: "{{ url('admin/zones') }}/" + id,
                                type: "GET",
                                dataType: "json",
                                success: function(zone) {},
                                error: function(xhr) {
                                    console.error('Error:', xhr);
                                    Swal.fire('Error', 'No se pudo cargar la zona', 'error');
                                }
                            });
                        });
                        allZoneBounds.push(polygon.getBounds());
                    }
                });

                // Ajustar vista del mapa
                if (allZoneBounds.length > 0) {
                    const group = L.featureGroup(zoneLayers);
                    mapGeneral.fitBounds(group.getBounds().pad(0.1));
                } else if (districtCoords) {
                    // Centrar en el distrito si no hay zonas
                    mapGeneral.setView([districtCoords.lat, districtCoords.lng], 12);
                    currentDistrictMarker = L.marker([districtCoords.lat, districtCoords.lng])
                        .addTo(mapGeneral)
                        .bindPopup(
                            `<strong>${$('#map_district option:selected').text()}</strong><br>No hay zonas registradas`)
                        .openPopup();
                }

                // Actualizar estadísticas
                updateStats(zones);
            } else {
                // No hay zonas en el distrito
                if (districtCoords) {
                    mapGeneral.setView([districtCoords.lat, districtCoords.lng], 12);
                    currentDistrictMarker = L.marker([districtCoords.lat, districtCoords.lng])
                        .addTo(mapGeneral)
                        .bindPopup(
                            `<strong>${$('#map_district option:selected').text()}</strong><br>No hay zonas registradas en este distrito`
                        )
                        .openPopup();
                }
                updateStats([]);

                Swal.fire({
                    icon: 'info',
                    title: 'Sin zonas',
                    text: 'No se encontraron zonas registradas para este distrito'
                });
            }

            // Ocultar loading
            $('#mapLoading').hide();
        }

        // Función para mostrar zonas del distrito
        function mostrarZonasDistrito(distId) {
            // Mostrar loading
            $('#mapLoading').show();

            // Eliminar zonas previas
            zoneLayers.forEach(layer => mapGeneral.removeLayer(layer));
            zoneLayers = [];

            if (!distId) {
                console.log('No se proporcionó ID de distrito');
                $('#mapLoading').hide();
                return;
            }

            $.ajax({
                url: '{{ route('admin.zones.byDistrict', '') }}/' + distId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const zones = response.zones;
                    const district = response.district;

                    console.log('Zonas recibidas:', zones);
                    console.log('Distrito:', district);

                    // Primero, hacer zoom al distrito usando geocodificación
                    if (district) {
                        geocodeDistrict(district.name + ', ' + district.province + ', ' + district.department)
                            .then(function(coords) {
                                // Una vez que tenemos las coordenadas del distrito, mostrar las zonas
                                mostrarZonasEnMapa(zones, coords);
                            })
                            .catch(function(error) {
                                console.error('Error en geocodificación:', error);
                                // Si falla la geocodificación, mostrar zonas con vista por defecto
                                mostrarZonasEnMapa(zones, null);
                            });
                    } else {
                        mostrarZonasEnMapa(zones, null);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar zonas:', error);
                    $('#mapLoading').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar las zonas del distrito'
                    });
                }
            });
        }

        // ==== Eventos del Modal del Mapa ====
        $(document).ready(function() {
            // Cuando se abre el modal
            $('#modalMapaZonas').on('shown.bs.modal', function() {
                initializeGeneralMap();
                cargarDepartamentos();
                $('#mapLoading').hide();
            });

            // Cuando se cierra el modal
            $('#modalMapaZonas').on('hidden.bs.modal', function() {
                // Limpiar el mapa pero mantener la instancia para reutilización
                zoneLayers.forEach(layer => mapGeneral.removeLayer(layer));
                zoneLayers = [];

                if (currentDistrictMarker) {
                    mapGeneral.removeLayer(currentDistrictMarker);
                    currentDistrictMarker = null;
                }
            });

            // Cuando cambia el departamento
            $('#map_department').on('change', function() {
                const depId = $(this).val();
                if (depId) {
                    loadProvinces(depId);
                } else {
                    $('#map_province').empty().append('<option value="">Seleccione</option>');
                    $('#map_district').empty().append('<option value="">Seleccione</option>');
                }
            });

            // Cuando cambia la provincia
            $('#map_province').on('change', function() {
                const provId = $(this).val();
                if (provId) {
                    loadDistricts(provId);
                } else {
                    $('#map_district').empty().append('<option value="">Seleccione</option>');
                }
            });

            // Cuando cambia el distrito → mostrar zonas
            $('#map_district').on('change', function() {
                const distId = $(this).val();
                if (distId && mapGeneral) {
                    mostrarZonasDistrito(distId);
                }
            });

            // Mover el tooltip con el mouse
            $(document).on('mousemove', function(e) {
                const tooltip = $('#zoneTooltip');
                if (tooltip.is(':visible')) {
                    const mapContainer = $('#mapView');
                    const mapOffset = mapContainer.offset();

                    tooltip.css({
                        left: (e.clientX - mapOffset.left + 10) + 'px',
                        top: (e.clientY - mapOffset.top - tooltip.outerHeight() - 10) + 'px'
                    });
                }
            });


            //Ver mapa 
            // --- Ver zona en mapa ---
            /*
            $(document).on('click', '.btnVerMapaZona', function() {
                var id = $(this).data('id');

                $.ajax({
                    url: "{{ url('admin/zones') }}/" + id + "/map",
                    type: "GET",
                    success: function(response) {
                        $('#mapZoneBody').html(response);
                        $('#modalVerZonaMapa').modal('show');
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        Swal.fire('Error', 'No se pudo cargar el mapa de la zona', 'error');
                    }
                });
            });
            // $('#modalMapaZonas').on('shown.bs.modal', function () {
            //     if (!mapGeneral) {
            //         initializeGeneralMap();
            //     } else {
            //         mapGeneral.invalidateSize(); // 👈 Esto es CLAVE
            //     }

            //     cargarDepartamentos();
            //     $('#mapLoading').hide();
            // });
            $('#modalMapaZonas').on('shown.bs.modal', function () {
                initializeGeneralMap(); // tu función para crear el mapa
                setTimeout(() => {
                    mapGeneral.invalidateSize(); // 👈 esto fuerza a Leaflet a reajustar el tamaño
                }, 300);

            });
            */
            // variable global para la instancia del mapa del modal
            let mapZoneInstance = null;

            $(document).off('click', '.btnVerMapaZona').on('click', '.btnVerMapaZona', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: "{{ url('admin/zones') }}/" + id,
                    type: "GET",
                    dataType: "json",
                    success: function(zone) {
                        // HTML MEJORADO con diseño más atractivo
                        const detailsHtml = `
                <div class="row">
                    <!-- Columna izquierda - Detalles -->
                    <div class="col-md-5">
                        <!-- Header con gradiente -->
                        <div class="zone-header mb-4 p-4 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="d-flex align-items-center">
                                <div class="zone-icon bg-white rounded-circle p-3 shadow-sm mr-3">
                                    <i class="fas fa-map-marked-alt text-primary fa-2x"></i>
                                </div>
                                <div>
                                    <h4 class="text-white mb-1 font-weight-bold">${escapeHtml(zone.name)}</h4>
                                    <p class="text-white-50 mb-0">
                                        <i class="fas fa-location-dot mr-1"></i>
                                        ${zone.district?.name || 'Ubicación no especificada'}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjetas de estadísticas -->
                        <div class="row mb-4">
                            <div class="col-6 mb-3">
                                <div class="stat-card bg-primary text-white rounded-lg p-3 shadow-sm h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-map-pin fa-lg mr-2"></i>
                                        <small class="opacity-75">Puntos</small>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <strong class="h4 mb-0">${zone.coordinates ? zone.coordinates.length : 0}</strong>
                                        <i class="fas fa-layer-group fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-card bg-success text-white rounded-lg p-3 shadow-sm h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-trash fa-lg mr-2"></i>
                                        <small class="opacity-75">Residuos</small>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <strong class="h4 mb-0">${getAverageWaste(zone)}</strong>
                                        <i class="fas fa-weight fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-card bg-warning text-white rounded-lg p-3 shadow-sm h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-building fa-lg mr-2"></i>
                                        <small class="opacity-75">Departamento</small>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <strong class="h6 mb-0 text-truncate">${getDepartmentName(zone)}</strong>
                                        <i class="fas fa-landmark fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-card bg-info text-white rounded-lg p-3 shadow-sm h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-ruler-combined fa-lg mr-2"></i>
                                        <small class="opacity-75">Área</small>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <strong class="h6 mb-0">${calculateArea(zone.coordinates)}</strong>
                                        <i class="fas fa-expand fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-align-left mr-2"></i>Descripción de la Zona
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-dark mb-0" style="line-height: 1.6;">
                                    ${escapeHtml(zone.description || 'Esta zona no cuenta con una descripción detallada.')}
                                </p>
                            </div>
                        </div>

                        <!-- Coordenadas -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-location-dot mr-2"></i>Coordenadas del Polígono
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="60" class="border-0 py-3 text-center">#</th>
                                                <th class="border-0 py-3">Latitud</th>
                                                <th class="border-0 py-3">Longitud</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${zone.coordinates ? zone.coordinates.map((coord, index) => `
                                                        <tr class="border-bottom">
                                                            <td class="text-center text-muted font-weight-bold">${index + 1}</td>
                                                            <td class="font-monospace">${parseFloat(coord.latitude).toFixed(6)}</td>
                                                            <td class="font-monospace">${parseFloat(coord.longitude).toFixed(6)}</td>
                                                        </tr>
                                                    `).join('') : `
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted py-4">
                                                                <i class="fas fa-map-marker-alt fa-2x mb-2 d-block"></i>
                                                                No hay coordenadas registradas
                                                            </td>
                                                        </tr>
                                                    `}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha - Mapa -->
                    <div class="col-md-7">
                        <div>
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-map mr-2"></i>Visualización en Mapa
                                </h6>
                            </div>
                            <div>
                                <div id="zoneMap" style="height:770px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                        $('#mapZoneBody').html(detailsHtml);
                        $('#modalVerZonaMapa').modal('show');

                        // Inicializar mapa cuando el modal esté visible
                        $('#modalVerZonaMapa').one('shown.bs.modal', function() {
                            if (mapZoneInstance) {
                                mapZoneInstance.remove();
                            }

                            if (!zone.coordinates || zone.coordinates.length === 0) {
                                $('#zoneMap').html(`
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center bg-light rounded">
                            <i class="fas fa-map-marker-alt fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted mb-2">Sin coordenadas</h5>
                            <p class="text-muted text-center px-4">Esta zona no tiene coordenadas para mostrar en el mapa.</p>
                        </div>
                    `);
                                return;
                            }

                            const first = zone.coordinates[0];
                            mapZoneInstance = L.map('zoneMap').setView([parseFloat(first
                                .latitude), parseFloat(first.longitude)], 14);

                            // Tile layer mejorado
                            L.tileLayer(
                                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                                }).addTo(mapZoneInstance);

                            const latlngs = zone.coordinates.map(c => [parseFloat(c
                                .latitude), parseFloat(c.longitude)]);

                            // Polígono con estilo mejorado
                            const polygon = L.polygon(latlngs, {
                                color: '#667eea',
                                fillColor: '#667eea',
                                fillOpacity: 0.3,
                                weight: 3,
                                opacity: 0.8
                            }).addTo(mapZoneInstance);

                            mapZoneInstance.fitBounds(polygon.getBounds());

                            // Popup mejorado
                            polygon.bindPopup(`
                    <div class="text-center p-2">
                        <div class="mb-2">
                            <i class="fas fa-map-marked-alt text-primary fa-lg"></i>
                        </div>
                        <h6 class="font-weight-bold mb-1">${zone.name}</h6>
                        <p class="mb-1 text-muted small">
                            <i class="fas fa-location-dot mr-1"></i>
                            ${zone.district?.name || 'Ubicación no especificada'}
                        </p>
                        <p class="mb-1 text-muted small">
                            <i class="fas fa-trash mr-1"></i>
                            Residuos: ${getAverageWaste(zone)}
                        </p>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-layer-group mr-1"></i>
                            ${zone.coordinates.length} puntos
                        </p>
                    </div>
                `).openPopup();

                            // Agregar marcador en el centro
                            const center = polygon.getBounds().getCenter();
                            L.marker(center, {
                                    icon: L.divIcon({
                                        className: 'custom-marker',
                                        html: '<i class="fas fa-flag text-danger fa-lg"></i>',
                                        iconSize: [20, 20]
                                    })
                                }).addTo(mapZoneInstance)
                                .bindPopup(
                                    '<div class="text-center"><strong>Centro de la zona</strong></div>'
                                );

                            // Forzar tamaño del mapa
                            setTimeout(() => {
                                mapZoneInstance.invalidateSize();
                            }, 100);
                        });

                        // Limpiar al cerrar
                        $('#modalVerZonaMapa').off('hidden.bs.modal').on('hidden.bs.modal',
                            function() {
                                if (mapZoneInstance) {
                                    mapZoneInstance.remove();
                                    mapZoneInstance = null;
                                }
                                $('#mapZoneBody').empty();
                            });
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo cargar la información de la zona',
                            confirmButtonColor: '#667eea'
                        });
                    }
                });
            });

            // Función para calcular área aproximada (simplificada)
            function calculateArea(coordinates) {
                if (!coordinates || coordinates.length < 3) return 'N/A';

                // Cálculo simplificado del área (para demostración)
                const area = Math.round(coordinates.length * 0.1 * 100) / 100;
                return area + ' km²';
            }

            // Función para obtener residuos promedio
            function getAverageWaste(zone) {
                if (zone.average_waste !== null && zone.average_waste !== undefined && zone.average_waste !== '') {
                    return zone.average_waste + ' kg';
                }
                return 'N/A';
            }

            // Función para obtener el nombre del departamento
            function getDepartmentName(zone) {
                if (zone.district?.province?.department?.name) {
                    return zone.district.province.department.name;
                }
                if (zone.department) {
                    return zone.department;
                }
                if (zone.district?.department) {
                    return zone.district.department;
                }
                if (zone.province?.department) {
                    return zone.province.department;
                }
                return 'No especificado';
            }

            // Función para escapar HTML
            function escapeHtml(text) {
                if (!text && text !== 0) return '';
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }
            // FIn de ver mapa
        });
    </script>

    <!-- ============================================================
                        DEPENDENCIAS LEAFLET
                        ============================================================= -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>

    <!-- Añadir Turf.js (antes de tu JS que usa turf) -->
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

    <!-- <script>
        window.existingZones = {!! $zonesJson ?? '[]' !!};

        console.log(existingZones);
        window.currentZoneId = {!! $zone->id ?? 'null' !!};
    </script> -->



@stop
