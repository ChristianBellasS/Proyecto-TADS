<div class="group-details">
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h6 class="mb-0 font-weight-bold">
                <i class="fas fa-info-circle mr-2"></i>Datos Generales
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item mb-3">
                        <small class="text-muted d-block mb-1">
                            <i class="far fa-calendar-alt mr-1"></i>Fecha
                        </small>
                        <div class="text-dark font-weight-medium d-flex align-items-center">
                            <i class="fas fa-calendar-day text-primary mr-2"></i>
                            {{ \Carbon\Carbon::parse($scheduling->date)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="info-item mb-3">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-map-marker-alt mr-1"></i>Zona
                        </small>
                        <div class="text-dark font-weight-medium d-flex align-items-center">
                            <i class="fas fa-map-marked-alt text-success mr-2"></i>
                            {{ $scheduling->group->zone->name ?? ($scheduling->zone->name ?? 'N/A') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-item mb-3">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-tag mr-1"></i>Estado
                        </small>
                        <div>
                            <span class="badge badge-success py-2 px-3 d-inline-flex align-items-center">
                                <i class="fas fa-check-circle mr-1"></i>{{ ucfirst($scheduling->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="info-item mb-3">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-clock mr-1"></i>Turno
                        </small>
                        <div class="text-dark font-weight-medium d-flex align-items-center">
                            <i class="fas fa-business-time text-info mr-2"></i>
                            {{ $scheduling->shift->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-truck mr-1"></i>Vehículo
                        </small>
                        <div class="text-dark font-weight-medium d-flex align-items-center">
                            <i class="fas fa-car text-secondary mr-2"></i>
                            @if ($scheduling->vehicle)
                                {{ $scheduling->vehicle->name }} - {{ $scheduling->vehicle->plate }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-gradient-success text-white py-3">
            <h6 class="mb-0 font-weight-bold">
                <i class="fas fa-users mr-2"></i>Personal Asignado
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 py-3 text-center" style="width: 30%">Rol</th>
                            <th class="border-0 py-3 text-center" style="width: 70%">Nombre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $driverDetail = $scheduling->groupDetails->where('role', 'conductor')->first();
                            $driver = $driverDetail ? $driverDetail->employee : null;
                            $assistants = $scheduling->groupDetails
                                ->where('role', 'ayudante')
                                ->pluck('employee')
                                ->filter();
                        @endphp

                        <!-- Conductor -->
                        <tr class="table-row-hover">
                            <td class="py-3 text-center">
                                <span class="badge badge-primary py-2 px-3">
                                    <i class="fas fa-user-tie mr-1"></i>Conductor
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="employee-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $driver ? $driver->name . ' ' . $driver->last_name : 'No asignado' }}</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Ayudantes -->
                        @foreach ($assistants as $assistant)
                            <tr class="table-row-hover">
                                <td class="py-3 text-center">
                                    <span class="badge badge-success py-2 px-3">
                                        <i class="fas fa-user-friends mr-1"></i>Ayudante
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="employee-avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center mr-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $assistant->name . ' ' . $assistant->last_name }}</strong>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        @if ($assistants->count() === 0)
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash fa-2x mb-3"></i>
                                        <p class="mb-0">No hay ayudantes asignados</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-gradient-info text-white py-3">
            <h6 class="mb-0 font-weight-bold">
                <i class="fas fa-history mr-2"></i>Historial de Cambios
            </h6>
        </div>
        <div class="card-body p-0">
            @php
                $changes = $scheduling->changes ?? collect([]);
            @endphp

            @if ($changes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4" style="width: 15%">Fecha</th>
                                <th class="py-3 text-center" style="width: 20%">Tipo</th>
                                <th class="py-3" style="width: 25%">Anterior</th>
                                <th class="py-3" style="width: 25%">Nuevo</th>
                                <th class="py-3 pe-4" style="width: 15%">Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($changes as $change)
                                <tr class="border-bottom">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex flex-column">
                                            <div class="text-primary font-weight-bold small">
                                                {{ $change->created_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $change->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>

                                    <td class="py-3 text-center">
                                        <span
                                            class="badge badge-pill py-2 px-3 
                                        @switch($change->change_type)
                                            @case('turno') badge-warning @break
                                            @case('vehiculo') badge-primary @break
                                            @case('ocupante') badge-success @break
                                            @default badge-info
                                        @endswitch">
                                            <i class="fas fa-exchange-alt mr-1"></i>
                                            {{ ucfirst($change->change_type) }}
                                        </span>
                                    </td>

                                    <td class="py-3">
                                        @if ($change->old_values)
                                            <div class="change-value old-value">
                                                @switch($change->change_type)
                                                    @case('turno')
                                                        <i class="fas fa-clock text-warning mr-2"></i>
                                                        <span
                                                            class="font-weight-medium">{{ $change->old_values['name'] ?? 'N/A' }}</span>
                                                    @break

                                                    @case('vehiculo')
                                                        <i class="fas fa-car text-primary mr-2"></i>
                                                        <div>
                                                            <div class="font-weight-medium">
                                                                {{ $change->old_values['name'] ?? 'N/A' }}</div>
                                                            <small
                                                                class="text-muted">{{ $change->old_values['plate'] ?? '' }}</small>
                                                        </div>
                                                    @break

                                                    @case('ocupante')
                                                        <i class="fas fa-user text-success mr-2"></i>
                                                        <span
                                                            class="font-weight-medium">{{ $change->old_values['name'] ?? 'N/A' }}</span>
                                                    @break
                                                @endswitch
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="py-3">
                                        @if ($change->new_values)
                                            <div class="change-value new-value">
                                                @switch($change->change_type)
                                                    @case('turno')
                                                        <i class="fas fa-clock text-warning mr-2"></i>
                                                        <span
                                                            class="font-weight-medium">{{ $change->new_values['name'] ?? 'N/A' }}</span>
                                                    @break

                                                    @case('vehiculo')
                                                        <i class="fas fa-car text-primary mr-2"></i>
                                                        <div>
                                                            <div class="font-weight-medium">
                                                                {{ $change->new_values['name'] ?? 'N/A' }}</div>
                                                            <small
                                                                class="text-muted">{{ $change->new_values['plate'] ?? '' }}</small>
                                                        </div>
                                                    @break

                                                    @case('ocupante')
                                                        <i class="fas fa-user text-success mr-2"></i>
                                                        <span
                                                            class="font-weight-medium">{{ $change->new_values['name'] ?? 'N/A' }}</span>
                                                    @break
                                                @endswitch
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="pe-4 py-3">
                                        @if ($change->reason)
                                            <div class="reason-text">
                                                <span class="text-dark">{{ $change->reason }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Sin historial de cambios</h5>
                        <p class="text-muted mb-0">No hay cambios registrados para esta programación</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="text-center mt-4">
        <button type="button" class="btn btn-danger btn-lg px-5 shadow-sm" data-dismiss="modal">
            <i class="fas fa-times mr-2"></i>Cerrar
        </button>
    </div>

</div>

<style>
    .group-details .card {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .group-details .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }

    .group-details .card-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
    }

    .info-item {
        padding: 8px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }

    .employee-avatar {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .change-item {
        display: flex;
        align-items: center;
    }

    .empty-state {
        padding: 20px;
    }

    .table-row-hover {
        transition: background-color 0.2s ease;
    }

    .table-row-hover:hover {
        background-color: #f8f9fa;
    }

    @media (max-width: 768px) {
        .group-details .card-body .row>div {
            margin-bottom: 15px;
        }

        .info-item {
            padding: 12px 0;
        }

        .employee-avatar {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }
    }
</style>