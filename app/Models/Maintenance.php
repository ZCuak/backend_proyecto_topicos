<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
    ];

    /**
     * Relación: Un mantenimiento tiene muchos horarios
     */
    public function schedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }
}