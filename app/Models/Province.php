<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'provinces';

    protected $fillable = [
        'name',
        'code',
        'department_id',
    ];

    /**
     * 🔹 Relación: una provincia pertenece a un departamento.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * 🔹 Relación: una provincia tiene muchos distritos.
     */
    public function districts()
    {
        return $this->hasMany(District::class, 'province_id');
    }
}
