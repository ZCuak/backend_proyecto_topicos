<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'maintenance_id',
        'vehicle_id',
        'responsible_id', // 🎯 NUEVO
        'type',
        'day',
        'start_time',
        'end_time',
    ];

    /**
     * Relación: Un horario pertenece a un mantenimiento
     */
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    /**
     * Relación: Un horario pertenece a un vehículo
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * 🎯 NUEVO: Relación con el responsable (usuario)
     */
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    /**
     * Relación: Un horario tiene muchos registros (días generados)
     */
    public function records()
    {
        return $this->hasMany(MaintenanceRecord::class, 'schedule_id');
    }
}