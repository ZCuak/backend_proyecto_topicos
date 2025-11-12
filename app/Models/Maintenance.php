<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'name', 'start_date', 'end_date'];

    public function schedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }
}