<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Zone;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Scheduling;

class EmployeeGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employeegroups';

    protected $guarded = [
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function schedulings()
    {
        return $this->hasMany(Scheduling::class, 'group_id');
    }

    public function configgroups()
    {
        return $this->hasMany(ConfigGroup::class, 'group_id');
    }

}
