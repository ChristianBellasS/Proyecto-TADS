<div class="group-details">

    <!-- Información General -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white border-0 d-flex align-items-center">
            <i class="fas fa-info-circle text-primary fa-lg mr-2"></i>
            <h6 class="mb-0 text-primary fw-bold">Información general</h6>
        </div>
        <div class="card-body pt-0">
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="text-muted"><i
                                    class="fas fa-layer-group mr-2 text-secondary"></i><strong>Grupo:</strong></span>
                            <span class="ml-1">{{ $group->name ?? 'N/A' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted"><i
                                    class="fas fa-map-marker-alt mr-2 text-secondary"></i><strong>Zona:</strong></span>
                            <span class="ml-1">{{ $group->zone->name ?? ($scheduling->zone->name ?? 'N/A') }}</span>
                        </li>
                        <li>
                            <span class="text-muted"><i
                                    class="fas fa-toggle-on mr-2 text-secondary"></i><strong>Estado:</strong></span>
                            <span class="ml-1">
                                <span
                                    class="badge badge-pill badge-{{ ($group->status ?? 'active') == 'active' ? 'success' : 'secondary' }}">
                                    {{ ($group->status ?? 'active') == 'active' ? 'Activo' : 'Inactivo' }}
                                </span>
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="col-md-6">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="text-muted"><i
                                    class="fas fa-clock mr-2 text-secondary"></i><strong>Turno:</strong></span>
                            <span class="ml-1">{{ $group->shift->name ?? ($scheduling->shift->name ?? 'N/A') }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-muted"><i
                                    class="fas fa-hourglass-half mr-2 text-secondary"></i><strong>Horario:</strong></span>
                            <span class="ml-1">
                                @if ($group->shift ?? $scheduling->shift)
                                    {{ $group->shift->hour_in ?? $scheduling->shift->hour_in }} -
                                    {{ $group->shift->hour_out ?? $scheduling->shift->hour_out }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="text-muted"><i
                                    class="fas fa-car mr-2 text-secondary"></i><strong>Vehículo:</strong></span>
                            <span class="ml-1">
                                @if ($group->vehicle ?? $scheduling->vehicle)
                                    {{ $group->vehicle->name ?? $scheduling->vehicle->name }} -
                                    {{ $group->vehicle->plate ?? $scheduling->vehicle->plate }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Días de Trabajo -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-white border-0 d-flex align-items-center">
            <i class="fas fa-calendar-alt text-primary fa-lg mr-2"></i>
            <h6 class="mb-0 text-primary fw-bold">Días de trabajo</h6>
        </div>
        <div class="card-body pt-0">
            @php
                $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                $workDays = isset($group->days)
                    ? explode(',', $group->days)
                    : ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

            @endphp

            <div class="d-flex flex-wrap gap-2">
                @foreach ($days as $day)
                    <span
                        class="badge rounded-pill px-3 py-2 {{ in_array($day, $workDays) ? 'bg-success text-white' : 'bg-light text-muted border' }}">
                        <i
                            class="fas {{ in_array($day, $workDays) ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                        {{ $day }}
                    </span>
                @endforeach
            </div>

        </div>
    </div>

    <!-- Personal Asignado -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex align-items-center">
            <i class="fas fa-users text-primary fa-lg mr-2"></i>
            <h6 class="mb-0 text-primary fw-bold">Personal asignado</h6>
        </div>
        <div class="card-body pt-0">
            @php
                if (isset($group->driver)) {
                    $driver = $group->driver;
                    $assistants = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $assistant = $group->{"assistant$i"};
                        if ($assistant) {
                            $assistants[] = $assistant;
                        }
                    }
                } else {
                    $driverDetail = $scheduling->groupDetails->where('role', 'conductor')->first();
                    $driver = $driverDetail ? $driverDetail->employee : null;
                    $assistants = $scheduling->groupDetails->where('role', 'ayudante')->pluck('employee')->filter();
                }
            @endphp

            <!-- Conductor -->
            <div class="mb-4">
                <div class="border rounded-lg p-3 bg-light d-flex align-items-center shadow-sm">
                    <div class="mr-3">
                        <i class="fas fa-user-tie fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold text-dark">
                            {{ $driver ? $driver->name . ' ' . $driver->last_name : 'No asignado' }}
                        </h6>
                        @if ($driver)
                            <small class="text-muted d-block"><i class="fas fa-id-card mr-1"></i>DNI:
                                {{ $driver->dni }}</small>
                            <small class="text-muted d-block"><i
                                    class="fas fa-phone mr-1"></i>{{ $driver->telefono ?? 'Sin teléfono' }}</small>
                        @else
                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i>No
                                hay conductor asignado</span>
                        @endif
                    </div>
                    <span class="badge bg-primary text-white">Conductor</span>
                </div>
            </div>

            <!-- Ayudantes -->
            <div>
                <div class="mb-3">
                    <i class="fas fa-hands-helping text-success mr-2"></i>
                    <span class="fw-bold text-success">Ayudantes</span>
                </div>
                <div class="row">
                    @if (count($assistants) > 0)
                        @foreach ($assistants as $index => $assistant)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded-lg p-3 bg-light d-flex align-items-center shadow-sm">
                                    <div class="mr-3">
                                        <i class="fas fa-user fa-2x text-success"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold text-dark">{{ $assistant->name }}
                                            {{ $assistant->last_name }}</h6>
                                        <small class="text-muted d-block"><i class="fas fa-id-card mr-1"></i>DNI:
                                            {{ $assistant->dni }}</small>
                                        <small class="text-muted d-block"><i
                                                class="fas fa-phone mr-1"></i>{{ $assistant->telefono ?? 'Sin teléfono' }}</small>
                                    </div>
                                    <span class="badge bg-success text-white">Ayudante {{ $index + 1 }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="alert alert-warning text-center py-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>No hay ayudantes asignados
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
