<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'request_date',
        'requested_days',
        'start_date',
        'end_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'request_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relación con empleado
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scope para vacaciones pendientes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    // Scope para vacaciones aprobadas
    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    // Scope para vacaciones activas (fecha actual entre start_date y end_date)
    public function scopeActive($query)
    {
        return $query->where('status', 'Approved')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    // Verificar si las vacaciones están activas
    public function isActive()
    {
        return $this->status === 'Approved' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    // Verificar si las vacaciones están pendientes
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    // Obtener días restantes de vacaciones
    public function getRemainingDaysAttribute()
    {
        if ($this->status !== 'Approved') {
            return 0;
        }

        $today = now();
        if ($today->gt($this->end_date)) {
            return 0;
        }

        if ($today->lt($this->start_date)) {
            return $this->requested_days;
        }

        return $this->end_date->diffInDays($today) + 1;
    }

    // Validar si el empleado puede solicitar vacaciones (solo nombrado y contrato permanente)
    /*
    public static function canEmployeeRequestVacation($employeeId)
    {
        $employee = Employee::with('employeetype')->find($employeeId);
        
        if (!$employee) {
            return false;
        }

        // Solo personal nombrado y contrato permanente pueden solicitar vacaciones
        $allowedTypes = ['nombrado', 'contrato permanente'];
        return in_array(strtolower($employee->employeetype->name), $allowedTypes);
    }
        */
    //
    // Validar si el empleado puede solicitar vacaciones (solo según contrato activo)
    public static function canEmployeeRequestVacation($employeeId)
    {
        // Obtener empleado
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return false;
        }

        // Obtener el contrato activo más reciente
        $contract = $employee->contracts()
                            ->where('is_active', true)
                            ->latest('start_date')
                            ->first();

        if (!$contract) {
            return false;
        }

        // Solo personal con contrato 'nombrado' o 'contrato permanente' puede solicitar vacaciones
        $allowedTypes = ['nombrado', 'contrato permanente'];

        return in_array(strtolower($contract->contract_type), $allowedTypes);
    }

    ///

    // Validar días máximos de vacaciones (30 días por año)
    public static function getRemainingVacationDays($employeeId, $year = null)
    {
        $year = $year ?? now()->year;
        $maxDays = 30;

        $usedDays = self::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->whereYear('start_date', $year)
            ->sum('requested_days');

        return max(0, $maxDays - $usedDays);
    }
}