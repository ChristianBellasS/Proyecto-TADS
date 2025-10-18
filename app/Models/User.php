<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'usertype_id',
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

    // Relación con UserType
    public function userType()
    {
        return $this->belongsTo(UserType::class, 'usertype_id');
    }

    // Verificar si es mayor de 18 años
    public function getIsAdultAttribute()
    {
        return $this->birthdate && $this->birthdate->age >= 18;
    }

    // Scope para usuarios activos
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // Scope para usuarios inactivos
    public function scopeInactivo($query)
    {
        return $query->where('estado', 'inactivo');
    }
}