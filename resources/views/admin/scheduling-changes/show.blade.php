<div class="container-fluid">
    <!-- Header con información principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card gradient-card-primary border-0 shadow-3d">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                Detalles del Cambio #{{ $schedulingChange->id }}
                            </h3>
                            <p class="text-white-50 mb-0">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                {{ $schedulingChange->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-pill badge-xl 
                                @if($schedulingChange->change_type == 'turno') badge-warning
                                @elseif($schedulingChange->change_type == 'vehiculo') badge-info
                                @else badge-success @endif glow">
                                <i class="fas 
                                    @if($schedulingChange->change_type == 'turno') fa-clock
                                    @elseif($schedulingChange->change_type == 'vehiculo') fa-car
                                    @else fa-user @endif mr-1">
                                </i>
                                {{ ucfirst($schedulingChange->change_type) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparación Antes/Después -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card comparison-card border-0 shadow-3d animate__animated animate__fadeInLeft">
                <div class="card-header bg-gradient-danger text-white rounded-top-3d">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-white-20 mr-3">
                            <i class="fas fa-arrow-left text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 text-white">Valores Anteriores</h5>
                            <small class="text-white-70">Estado previo al cambio</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $oldValues = $schedulingChange->old_values;
                        $newValues = $schedulingChange->new_values;
                    @endphp
                    
                    @if($schedulingChange->change_type == 'turno')
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-danger">
                                <i class="fas fa-clock text-danger"></i>
                            </div>
                            <div class="info-content">
                                <label>Turno</label>
                                <span class="value">{{ $oldValues['name'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-info">
                                <i class="fas fa-sign-in-alt text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Hora de Entrada</label>
                                <span class="value">{{ $oldValues['hour_in'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-success">
                                <i class="fas fa-sign-out-alt text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Hora de Salida</label>
                                <span class="value">{{ $oldValues['hour_out'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @elseif($schedulingChange->change_type == 'vehiculo')
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-primary">
                                <i class="fas fa-car text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>Vehículo</label>
                                <span class="value">{{ $oldValues['name'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-warning">
                                <i class="fas fa-tag text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Placa</label>
                                <span class="value badge badge-secondary">{{ $oldValues['plate'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-info">
                                <i class="fas fa-user text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Nombre</label>
                                <span class="value">{{ $oldValues['name'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-warning">
                                <i class="fas fa-id-card text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>DNI</label>
                                <span class="value badge badge-primary">{{ $oldValues['dni'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-success">
                                <i class="fas fa-user-tag text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Rol</label>
                                <span class="value badge badge-primary">{{ $oldValues['role'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-purple">
                                <i class="fas fa-briefcase text-purple"></i>
                            </div>
                            <div class="info-content">
                                <label>Tipo de Empleado</label>
                                <span class="value">{{ $oldValues['employee_type'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card comparison-card border-0 shadow-3d animate__animated animate__fadeInRight">
                <div class="card-header bg-gradient-success text-white rounded-top-3d">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-white-20 mr-3">
                            <i class="fas fa-arrow-right text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 text-white">Valores Nuevos</h5>
                            <small class="text-white-70">Estado después del cambio</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($schedulingChange->change_type == 'turno')
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-danger">
                                <i class="fas fa-clock text-danger"></i>
                            </div>
                            <div class="info-content">
                                <label>Turno</label>
                                <span class="value text-success">{{ $newValues['name'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-info">
                                <i class="fas fa-sign-in-alt text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Hora de Entrada</label>
                                <span class="value text-success">{{ $newValues['hour_in'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-success">
                                <i class="fas fa-sign-out-alt text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Hora de Salida</label>
                                <span class="value text-success">{{ $newValues['hour_out'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @elseif($schedulingChange->change_type == 'vehiculo')
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-primary">
                                <i class="fas fa-car text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>Vehículo</label>
                                <span class="value text-success">{{ $newValues['name'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-warning">
                                <i class="fas fa-tag text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>Placa</label>
                                <span class="value badge badge-success">{{ $newValues['plate'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-info">
                                <i class="fas fa-user text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>Nombre</label>
                                <span class="value text-success">{{ $newValues['name'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-warning">
                                <i class="fas fa-id-card text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>DNI</label>
                                <span class="value badge badge-success">{{ $newValues['dni'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-success">
                                <i class="fas fa-user-tag text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Rol</label>
                                <span class="value badge badge-success">{{ $newValues['role'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon-wrapper bg-light-purple">
                                <i class="fas fa-briefcase text-purple"></i>
                            </div>
                            <div class="info-content">
                                <label>Tipo de Empleado</label>
                                <span class="value text-success">{{ $newValues['employee_type'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-3d animate__animated animate__fadeInUp">
                <div class="card-header bg-gradient-info text-white rounded-top-3d">
                    <div class="d-flex align-items-center">
                        <div class="icon-container bg-white-20 mr-3">
                            <i class="fas fa-info-circle text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 text-white">Información del Cambio</h5>
                            <small class="text-white-70">Detalles adicionales del proceso</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <label>Tipo de Cambio</label>
                                    <span class="stat-value">
                                        <span class="badge badge-pill 
                                            @if($schedulingChange->change_type == 'turno') badge-warning
                                            @elseif($schedulingChange->change_type == 'vehiculo') badge-info
                                            @else badge-success @endif">
                                            {{ ucfirst($schedulingChange->change_type) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="stat-content">
                                    <label>Realizado por</label>
                                    <span class="stat-value">{{ $schedulingChange->changedBy->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <label>Fecha del Cambio</label>
                                    <span class="stat-value">{{ $schedulingChange->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="stat-content">
                                    <label>Programación</label>
                                    <span class="stat-value">#{{ $schedulingChange->scheduling_id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Motivo del cambio -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="reason-card">
                                <div class="reason-header">
                                    <i class="fas fa-comment-dots mr-2"></i>
                                    <strong>Motivo del Cambio</strong>
                                </div>
                                <div class="reason-content">
                                    <p class="mb-0">{{ $schedulingChange->reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($schedulingChange->scheduling)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="additional-info">
                                <div class="info-badge">
                                    <i class="fas fa-calendar-day mr-1"></i>
                                    <strong>Fecha de Programación:</strong>
                                    <span class="ml-1">
                                        @if($schedulingChange->scheduling->date)
                                            {{ \Carbon\Carbon::parse($schedulingChange->scheduling->date)->format('d/m/Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Librerías para animaciones y efectos -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
    /* Variables de colores */
    :root {
        --primary: #035286;
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --warning: #ffc107;
        --info: #17a2b8;
        --light: #f8f9fa;
        --dark: #343a40;
        --purple: #6f42c1;
    }

    /* Efectos 3D y sombras */
    .shadow-3d {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1), 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .shadow-3d:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15), 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .rounded-top-3d {
        border-radius: 15px 15px 0 0 !important;
    }

    /* Gradientes */
    .gradient-card-primary {
        background: linear-gradient(135deg, #035286 0%, #034c7c 100%);
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    /* Icon containers */
    .icon-container {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-white-20 {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Items de información */
    .info-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .info-item:hover {
        background: #f8f9fa;
        border-radius: 8px;
        padding-left: 10px;
        padding-right: 10px;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .info-content {
        flex: 1;
    }

    .info-content label {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 2px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-content .value {
        font-size: 1rem;
        font-weight: 600;
        color: #343a40;
    }

    /* Colores de iconos de fondo */
    .bg-light-danger { background: rgba(220, 53, 69, 0.1); }
    .bg-light-success { background: rgba(40, 167, 69, 0.1); }
    .bg-light-info { background: rgba(23, 162, 184, 0.1); }
    .bg-light-warning { background: rgba(255, 193, 7, 0.1); }
    .bg-light-primary { background: rgba(3, 82, 134, 0.1); }
    .bg-light-purple { background: rgba(111, 66, 193, 0.1); }

    /* Tarjetas de estadísticas */
    .stat-card {
        text-align: center;
        padding: 20px 15px;
        border-radius: 15px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        background: white;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: white;
        font-size: 1.5rem;
    }

    .stat-content label {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #343a40;
    }

    /* Tarjeta de motivo */
    .reason-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 0;
        overflow: hidden;
    }

    .reason-header {
        background: rgba(23, 162, 184, 0.1);
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
        color: #138496;
    }

    .reason-content {
        padding: 20px;
        font-size: 1rem;
        line-height: 1.6;
        color: #495057;
    }

    /* Badge mejorado */
    .badge-xl {
        font-size: 1rem;
        padding: 8px 16px;
    }

    .glow {
        animation: glow 2s ease-in-out infinite alternate;
    }

    @keyframes glow {
        from {
            box-shadow: 0 0 5px currentColor;
        }
        to {
            box-shadow: 0 0 15px currentColor;
        }
    }

    /* Información adicional */
    .additional-info {
        text-align: center;
    }

    .info-badge {
        display: inline-block;
        background: rgba(40, 167, 69, 0.1);
        color: #218838;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 0.9rem;
    }

    /* Animaciones personalizadas */
    .animate__animated {
        animation-duration: 0.6s;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .info-item {
            flex-direction: column;
            text-align: center;
        }
        
        .icon-wrapper {
            margin-right: 0;
            margin-bottom: 10px;
        }
        
        .stat-card {
            margin-bottom: 15px;
        }
    }
</style>

<script>
    // Efectos de animación adicionales
    document.addEventListener('DOMContentLoaded', function() {
        // Efecto de aparición escalonada para los items
        const infoItems = document.querySelectorAll('.info-item');
        infoItems.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
            item.classList.add('animate__animated', 'animate__fadeInUp');
        });

        // Efecto hover en tarjetas de comparación
        const comparisonCards = document.querySelectorAll('.comparison-card');
        comparisonCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    });
</script>