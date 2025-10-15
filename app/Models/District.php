<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'districts';

    protected $fillable = [
        'name',
        'code',
        'department_id',
        'province_id',
    ];

    /**
     * 🔹 Relación: una zona pertenece a un distrito.
     */
    public function sectors()
    {
        return $this->hasMany(Sector::class, 'district_id');
    }

    /**
     * 🔹 Relación: una zona puede tener varios usuarios asignados (personal).
     */
    public function zones()
    {
        return $this->hasMany(Zone::class, 'district_id');
    }
}
