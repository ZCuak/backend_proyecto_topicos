<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'brandmodels';

    protected $guarded = [
    ];

    /**
     * Relación: un color puede estar asociado a muchos vehículos.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
