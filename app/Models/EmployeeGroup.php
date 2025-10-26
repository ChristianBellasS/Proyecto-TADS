<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeGroup extends Model
{
    use HasFactory;

    protected $table = 'employeegroups';
    
    protected $guarded = [];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function assistant1()
    {
        return $this->belongsTo(Employee::class, 'assistant1_id');
    }

    public function assistant2()
    {
        return $this->belongsTo(Employee::class, 'assistant2_id');
    }

    public function assistant3()
    {
        return $this->belongsTo(Employee::class, 'assistant3_id');
    }

    public function assistant4()
    {
        return $this->belongsTo(Employee::class, 'assistant4_id');
    }

    public function assistant5()
    {
        return $this->belongsTo(Employee::class, 'assistant5_id');
    }

    // Método para obtener todos los ayudantes
    public function getAssistantsAttribute()
    {
        $assistants = [];
        for ($i = 1; $i <= 5; $i++) {
            $assistant = $this->{"assistant{$i}"};
            if ($assistant) {
                $assistants[] = $assistant;
            }
        }
        return collect($assistants);
    }
}