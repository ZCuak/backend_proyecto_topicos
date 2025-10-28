<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchedulingDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scheduling_details';

    protected $fillable = [
        'scheduling_id',
        'user_id',
        'usertype_id',
        'position_order',
        'attendance_status',
        'notes',
        'date',
    ];

    protected $casts = [
        'position_order' => 'integer',
        'date' => 'date',
    ];

    /**
     * Relación con la programación principal
     */
    public function scheduling()
    {
        return $this->belongsTo(Scheduling::class, 'scheduling_id');
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el tipo de usuario
     */
    public function userType()
    {
        return $this->belongsTo(UserType::class, 'usertype_id');
    }

    /**
     * Scope para obtener solo conductores (usertype_id = 1)
     */
    public function scopeConductors($query)
    {
        return $query->where('usertype_id', 1);
    }

    /**
     * Scope para obtener solo ayudantes (usertype_id = 2)
     */
    public function scopeAyudantes($query)
    {
        return $query->where('usertype_id', 2);
    }

    /**
     * Scope para obtener por estado de asistencia
     */
    public function scopeByAttendanceStatus($query, $status)
    {
        return $query->where('attendance_status', $status);
    }

    /**
     * Scope para obtener por orden de posición
     */
    public function scopeByPositionOrder($query, $order)
    {
        return $query->where('position_order', $order);
    }

    /**
     * Scope para obtener detalles por fecha
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope para obtener detalles por rango de fechas
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope para obtener detalles de un usuario en una fecha específica
     */
    public function scopeByUserAndDate($query, $userId, $date)
    {
        return $query->where('user_id', $userId)->where('date', $date);
    }

    /**
     * Verificar si es conductor (usertype_id = 1)
     */
    public function isConductor()
    {
        return $this->usertype_id == 1;
    }

    /**
     * Verificar si es ayudante (usertype_id = 2)
     */
    public function isAyudante()
    {
        return $this->usertype_id == 2;
    }

    /**
     * Obtener el nombre del rol en español
     */
    public function getRoleNameAttribute()
    {
        return $this->usertype_id == 1 ? 'Conductor' : 'Ayudante';
    }

    /**
     * Obtener el estado de asistencia en español
     */
    public function getAttendanceStatusNameAttribute()
    {
        $statuses = [
            'pendiente' => 'Pendiente',
            'presente' => 'Presente',
            'ausente' => 'Ausente',
            'justificado' => 'Justificado'
        ];

        return $statuses[$this->attendance_status] ?? $this->attendance_status;
    }
}