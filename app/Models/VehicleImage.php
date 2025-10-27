<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicleimages';

    protected $fillable = [
        'path',
        'is_profile',
        'vehicle_id',
    ];

    protected $casts = [
        'is_profile' => 'boolean',
    ];

    /**
     * 🔹 Relación: Vehículo
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * 🔹 Obtener URL completa de la imagen
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }

    /**
     * 🔹 Obtener ruta completa del archivo
     */
    public function getFullPathAttribute()
    {
        return storage_path('app/public/' . $this->path);
    }
}
