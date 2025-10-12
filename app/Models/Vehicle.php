<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'name',
        'code',
        'plate',
        'year',
        'occupant_capacity',
        'load_capacity',
        'description',
        'status',
        'brand_id',
        'model_id',
        'type_id',
        'color_id',
    ];

    protected $casts = [
        'year' => 'integer',
        'occupant_capacity' => 'integer',
        'load_capacity' => 'integer',
    ];

    /**
     * 🔹 Relación: Marca
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * 🔹 Relación: Modelo
     */
    public function model()
    {
        return $this->belongsTo(BrandModel::class, 'model_id');
    }

    /**
     * 🔹 Relación: Tipo de vehículo
     */
    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'type_id');
    }

    /**
     * 🔹 Relación: Color
     */
    public function color()
    {
        return $this->belongsTo(VehicleColor::class, 'color_id');
    }

    /**
     * 🔹 Relación: Rutas o programaciones donde participa el vehículo
     */
    public function routes()
    {
        return $this->hasMany(VehicleRoute::class, 'vehicle_id');
    }
}
