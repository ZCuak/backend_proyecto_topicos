<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
    ];
    /**
     * Relación: una marca puede tener muchos modelos (BrandModel)
     */
    public function models()
    {
        return $this->hasMany(BrandModel::class, 'brand_id');
    }

    /**
     * Relación: una marca puede tener muchos vehículos
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'brand_id');
    }

}
