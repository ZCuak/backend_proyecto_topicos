<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'date',
        'description',
        'image_path',
        'completed', // 🎯 NUEVO
    ];

    protected $casts = [
        'completed' => 'boolean', // 🎯 Asegurar que sea booleano
        'date' => 'date',
    ];

    /**
     * Relación: Un registro pertenece a un horario
     */
    public function schedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'schedule_id');
    }
}