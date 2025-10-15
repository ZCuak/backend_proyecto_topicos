<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoneCoord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'zonecoords';

    protected $fillable = [
        'latitude',
        'longitude',
        'zone_id',
    ];

    /**
     * 🔹 Relación: una zona pertenece a un distrito.
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

}
