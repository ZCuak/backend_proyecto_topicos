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
        'zone_id',
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

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'user_id');
    }

    
    public function vacations()
    {
        return $this->hasMany(Vacation::class, 'user_id');
    }
}
