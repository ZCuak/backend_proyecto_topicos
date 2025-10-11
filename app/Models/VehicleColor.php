<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleColor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehiclecolors';

    protected $fillable = [
        'name',
        'rgb_code',
    ];

    /**
     * Relación: un color puede estar asociado a muchos vehículos.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehiclecolor_id');
    }
}
