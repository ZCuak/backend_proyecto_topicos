<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'zones';

    protected $fillable = [
        'name',
        'area',
        'description',
        'district_id',
        'sector_id'
    ];

    /**
     * 🔹 Relación: una zona pertenece a un distrito.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * 🔹 Relación: una zona pertenece a un sector.
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    /**
     * 🔹 Relación: una zona puede tener varios usuarios asignados (personal).
     */


    /**
     * 🔹 Relación: una zona puede tener varias coordenadas de perímetro.
     */
    public function coordinates()
    {
        return $this->hasMany(ZoneCoord::class, 'zone_id');
    }

    /**
     * 🔹 Relación: una zona puede tener varias rutas asignadas.
     */
    public function routes()
    {
        return $this->hasMany(Route::class, 'zone_id');
    }

    /**
     * 🔹 Relación: una zona puede tener varias programaciones (en caso de planificación).
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'zone_id');
    }
}
