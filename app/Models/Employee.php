<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;


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
}