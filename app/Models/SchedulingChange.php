<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulingChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduling_id',
        'changed_by',
        'change_type',
        'reason',
        'old_values',
        'new_values'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function scheduling()
    {
        return $this->belongsTo(Scheduling::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(Employee::class, 'changed_by');
    }
}
