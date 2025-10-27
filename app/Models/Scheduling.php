<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scheduling extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schedulings';

    protected $fillable = [
        'group_id',
        'schedule_id',
        'vehicle_id',
        'zone_id',
        'date',
        'status',
        'notes',
    ];

    protected $dates = [
        'date'
    ];

    /**
     * Relaciones
     */
    public function group()
    {
        return $this->belongsTo(EmployeeGroup::class, 'group_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

}
