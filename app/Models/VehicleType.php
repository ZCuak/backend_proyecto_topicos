<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicletypes';

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * 🔹 Relación: un tipo puede tener varios vehículos asociados.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'type_id');
    }
}
