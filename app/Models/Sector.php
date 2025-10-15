<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sectors';

    protected $fillable = [
        'name',
        'area',
        'description',
        'district_id',
    ];

    /**
     * 🔹 Relación: una zona pertenece a un distrito.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * 🔹 Relación: una zona puede tener varios usuarios asignados (personal).
     */
    public function zones()
    {
        return $this->hasMany(Zone::class, 'sector_id');
    }
}
