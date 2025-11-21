<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'maintenance_id', 'vehicle_id', 'type', 'day', 'start_time', 'end_time','user_id'];

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function records()
    {
        return $this->hasMany(MaintenanceRecord::class, 'schedule_id');
    }
}