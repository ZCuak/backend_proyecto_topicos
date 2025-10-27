<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * 🔹 Relación: un departamento tiene muchas provincias.
     */
    public function provinces()
    {
        return $this->hasMany(Province::class, 'department_id');
    }

    /**
     * 🔹 Relación: un departamento tiene muchos distritos (a través de provincias).
     */
    public function districts()
    {
        return $this->hasManyThrough(District::class, Province::class, 'department_id', 'province_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'department_id');
    }
}
