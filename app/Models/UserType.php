<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'usertypes';

    protected $fillable = [
        'name',
        'is_system',
    ];

    /**
     * Relación: un tipo de usuario puede tener muchos usuarios.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'usertype_id');
    }
}
