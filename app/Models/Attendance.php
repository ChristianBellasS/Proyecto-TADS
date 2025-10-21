<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_date', 
        'type',
        'period',
        'status',
        'notes'
    ];

    protected $casts = [
        'attendance_date' => 'datetime',
    ];

    const STATUS_PRESENT = 1;
    const STATUS_ABSENT = 2;
    const STATUS_LATE = 3;
    const STATUS_LEAVE = 4;

    const TYPE_ENTRADA = 'ENTRADA';
    const TYPE_SALIDA = 'SALIDA';

    const PERIOD_MORNING = 1;
    const PERIOD_AFTERNOON = 2;
    const PERIOD_NIGHT = 3;
    const PERIOD_FULL_DAY = 4;

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereHas('employee', function($q) use ($search) {
            $q->where('dni', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%");
        });
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('attendance_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('attendance_date', '<=', $endDate);
        }
        return $query;
    }

    public function scopePeriod($query, $period)
    {
        if ($period) {
            $query->where('period', $period);
        }
        return $query;
    }

    public function scopeStatus($query, $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }

    // Nuevo scope para type
    public function scopeType($query, $type)
    {
        if ($type) {
            $query->where('type', $type);
        }
        return $query;
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_PRESENT => 'Presente',
            self::STATUS_ABSENT => 'Ausente',
            self::STATUS_LATE => 'Tarde',
            self::STATUS_LEAVE => 'Permiso',
            default => 'Desconocido'
        };
    }

    // Nuevo accesor para type
    public function getTypeTextAttribute()
    {
        return match($this->type) {
            self::TYPE_ENTRADA => 'Entrada',
            self::TYPE_SALIDA => 'Salida',
            default => 'Desconocido'
        };
    }

    public function getPeriodTextAttribute()
    {
        return match($this->period) {
            self::PERIOD_MORNING => 'Mañana',
            self::PERIOD_AFTERNOON => 'Tarde',
            self::PERIOD_NIGHT => 'Noche',
            self::PERIOD_FULL_DAY => 'Día completo',
            default => 'Desconocido'
        };
    }

    public function getEmployeeNameAttribute()
    {
        return $this->employee ? $this->employee->name . ' ' . $this->employee->last_name : null;
    }

    // Accesor para DNI del empleado
    public function getEmployeeDniAttribute()
    {
        return $this->employee ? $this->employee->dni : null;
    }

    // Método para verificar si es presente
    public function isPresent()
    {
        return $this->status === self::STATUS_PRESENT;
    }

    // Método para verificar si es ausente
    public function isAbsent()
    {
        return $this->status === self::STATUS_ABSENT;
    }

    // Método para verificar si es tarde
    public function isLate()
    {
        return $this->status === self::STATUS_LATE;
    }

    // Nuevos métodos para type
    public function isEntrada()
    {
        return $this->type === self::TYPE_ENTRADA;
    }

    public function isSalida()
    {
        return $this->type === self::TYPE_SALIDA;
    }
}