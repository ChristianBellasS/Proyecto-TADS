<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;


class Employee extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'dni',
        'birthdate',
        'license',
        'address',
        'telefono',
        'email',
        'password',
        'estado',
        'employeetype_id',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];



    // Método parA LA AUTENTICACIÓN CON DNI
    public function getAuthIdentifierName()
    {
        return 'dni';
    }


    // Relación con EmployeeType
    public function employeeType()
    {
        return $this->belongsTo(EmployeeType::class, 'employeetype_id');
    }

    // Verificar si es mayor de 18 años
    public function getIsAdultAttribute()
    {
        return $this->birthdate && $this->birthdate->age >= 18;
    }

    // Scope para empleados activos
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // Scope para empleados inactivos
    public function scopeInactivo($query)
    {
        return $query->where('estado', 'inactivo');
    }

    // Método para obtener nombre completo
    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->last_name;
    }

    //Nueva relación con Vacation

    // Relación con vacaciones
    public function vacations()
    {
        return $this->hasMany(Vacation::class);
    }

    // Obtener vacaciones aprobadas
    public function approvedVacations()
    {
        return $this->vacations()->where('status', 'Approved');
    }

    // Obtener vacaciones pendientes
    public function pendingVacations()
    {
        return $this->vacations()->where('status', 'Pending');
    }

    // Verificar si tiene vacaciones activas
    public function hasActiveVacations()
    {
        return $this->vacations()->active()->exists();
    }

    // Obtener días de vacaciones disponibles para el año actual
    public function getAvailableVacationDaysAttribute()
    {
        return Vacation::getRemainingVacationDays($this->id);
    }

    // Verificar si puede solicitar vacaciones
    public function canRequestVacation()
    {
        return Vacation::canEmployeeRequestVacation($this->id);
    }
    // Scope para empleados activos
    public function scopeActive($query)
    {
        return $query->where('estado', 'activo');
    }

    // En Employee.php
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    // Fin de la clase Employee

    // Relaciones como conductor
    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id')->where('employeetype_id', 1);
    }

    public function assistant1()
    {
        return $this->belongsTo(Employee::class, 'assistant1_id')->where('employeetype_id', 2);
    }

    public function assistant2()
    {
        return $this->belongsTo(Employee::class, 'assistant2_id')->where('employeetype_id', 2);
    }

    public function assistant3()
    {
        return $this->belongsTo(Employee::class, 'assistant3_id')->where('employeetype_id', 2);
    }

    public function assistant4()
    {
        return $this->belongsTo(Employee::class, 'assistant4_id')->where('employeetype_id', 2);
    }

    public function assistant5()
    {
        return $this->belongsTo(Employee::class, 'assistant5_id')->where('employeetype_id', 2);
    }

    // para programación

    public function groupDetails()
    {
        return $this->hasMany(GroupDetail::class);
    }

    public function schedulings()
    {
        return $this->belongsToMany(Scheduling::class, 'group_details', 'employee_id', 'scheduling_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Método para verificar disponibilidad
    public function isAvailableForDate($date)
    {
        // Verificar contrato activo
        $hasActiveContract = $this->contracts()
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date);
            })->exists();

        // Verificar vacaciones
        $onVacation = $this->vacations()
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();

        return $hasActiveContract && !$onVacation;
    }

    // Relación con grupos a través de configgroups
    public function employeeGroups()
    {
        return $this->belongsToMany(EmployeeGroup::class, 'configgroups', 'employee_id', 'employeegroup_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    // Accesor para obtener la posición/cargo desde EmployeeType
    public function getPositionAttribute()
    {
        return $this->employeeType ? $this->employeeType->name : 'Sin asignar';
    }

    // Método para verificar si es conductor
    public function isDriver()
    {
        return $this->employeeGroups()->wherePivot('role', 'conductor')->exists();
    }

    // Método para verificar si es ayudante
    public function isAssistant()
    {
        return $this->employeeGroups()->wherePivot('role', 'ayudante')->exists();
    }

    // Obtener grupos donde es conductor
    public function driverGroups()
    {
        return $this->employeeGroups()->wherePivot('role', 'conductor');
    }

    // Obtener grupos donde es ayudante
    public function assistantGroups()
    {
        return $this->employeeGroups()->wherePivot('role', 'ayudante');
    }

    /**
     * Validar si tiene contrato activo para una fecha
     */
    public function isActiveContract($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();

        return $this->contracts()
            ->where('is_active', true)
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->exists();
    }

    /**
     * Validar si tiene vacaciones en una fecha específica
     */
    public function hasVacation($date)
    {
        $date = Carbon::parse($date);

        return $this->vacations()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', 'Approved') // Cambie a 'approved' por 'Approved'
            ->exists();
    }

    /**
     * Validación completa para programación
     */
    public function canBeScheduled($date)
    {
        $date = Carbon::parse($date);

        // 1. Validar contrato activo
        if (!$this->isActiveContract($date)) {
            /*
            return [
                'can_be_scheduled' => false,
                'error' => 'No tiene contrato activo para la fecha ' . $date->format('d/m/Y'),
                'error_type' => 'contract'
            ];
            */
            // Nuevo código para permitir programación sin contrato activo
            return $this->contracts()
                ->where('is_active', true)
                ->where('start_date', '<=', $date)
                ->where(function ($query) use ($date) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $date);
                })
                ->exists();
        }

        // 2. Validar vacaciones
        if ($this->hasVacation($date)) {
            return [
                'can_be_scheduled' => false,
                'error' => 'Tiene vacaciones aprobadas para la fecha ' . $date->format('d/m/Y'),
                'error_type' => 'vacation'
            ];
        }
        // Inicio de nuevo código
        if ($this->estado !== 'activo') {
            return [
                // 'can_be_scheduled' => false, // Cambie esto a true para permitir la programación
                'can_be_scheduled' => true,
                'error' => 'Empleado inactivo',
                'error_type' => 'status'
            ];
        }
        // FIn de nuevo código

        return [
            'can_be_scheduled' => true,
            'error' => null,
            'error_type' => null
        ];
    }

    /**
     * Obtener el contrato activo actual
     */
    public function getCurrentContractAttribute()
    {
        return $this->contracts()
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();
    }

    /**
     * Obtener vacaciones futuras/aprobadas
     */
    public function getUpcomingApprovedVacations()
    {
        return $this->vacations()
            ->where('status', 'approved')
            ->where('end_date', '>=', now())
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Scope para empleados disponibles en una fecha
     */
    public function scopeAvailableForDate($query, $date)
    {
        $date = Carbon::parse($date);
        /*
        return $query->whereHas('contracts', function ($q) use ($date) {
            $q->where('is_active', true)
                ->where('start_date', '<=', $date)
                ->where(function ($q2) use ($date) {
                    $q2->whereNull('end_date')
                        ->orWhere('end_date', '>=', $date);
                });
        })
            ->whereDoesntHave('vacations', function ($q) use ($date) {
                $q->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->where('status', 'approved');
            });

        */
        // Nuevo código para considerar estado activo        
        return $query->where('estado', 'activo')
            ->whereHas('contracts', function ($q) use ($date) {
                $q->where('is_active', true)
                    ->where('start_date', '<=', $date)
                    ->where(function ($query) use ($date) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date);
                    });
            })
            ->whereDoesntHave('vacations', function ($q) use ($date) {
                $q->where('status', 'Approved')
                    ->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
            });
    }

    /**
     * Scope para filtrar solo ayudantes
     */
    public function scopeAssistants($query)
    {
        return $query->whereHas('employeeType', function ($q) {
            $q->where('name', 'Ayudante');
        });
    }

    /**
     * Scope para filtrar solo conductores
     */
    public function scopeDrivers($query)
    {
        return $query->whereHas('employeeType', function ($q) {
            $q->where('name', 'Conductor');
        });
    }
}
