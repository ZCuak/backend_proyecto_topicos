<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes; 

    protected $table = 'users';

    protected $fillable = [
        'username',
        'dni',
        'firstname',
        'lastname',
        'birthdate',
        'license',
        'address',
        'email',
        'password',
        'profile_photo_path',
        'usertype_id',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthdate' => 'date'
    ];

    public function usertype()
    {
        return $this->belongsTo(UserType::class, 'usertype_id');
    }



    public function contracts()
    {
        return $this->hasMany(Contract::class, 'user_id');
    }

    
    public function vacations()
    {
        return $this->hasMany(Vacation::class, 'user_id');
    }

    /**
     * Obtener grupos del usuario a través de ConfigGroup
     */
    public function groups()
    {
        return $this->hasManyThrough(EmployeeGroup::class, ConfigGroup::class, 'user_id', 'id', 'id', 'group_id');
    }

    /**
     * Obtener contrato activo del usuario
     */
    public function activeContract()
    {
        return $this->hasOne(Contract::class, 'user_id')->where('is_active', true);
    }

    /**
     * Verificar si el usuario tiene contrato activo
     */
    public function hasActiveContract()
    {
        return $this->contracts()->where('is_active', true)->exists();
    }
}
