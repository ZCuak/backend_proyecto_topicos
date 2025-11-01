<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendace extends Model
{
    use HasFactory, SoftDeletes;

    public static $auditName = 'ASISTENCIA DE PERSONAL';

    public static $auditFieldNames = [
        'user_id'    => 'Personal',
        'date'       => 'Fecha',
        'check_in'   => 'Hora de Entrada',
        'check_out'  => 'Hora de Salida',
        'status'     => 'Estado',
        'notes'      => 'Notas (Manuales)',
    ];

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'type',
        'status',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Tipos de asistencia
     */
    const TYPE_ENTRADA = 'ENTRADA';
    const TYPE_SALIDA = 'SALIDA';

    /**
     * Estados de asistencia
     */
    const STATUS_PRESENTE = 'PRESENTE';
    const STATUS_AUSENTE = 'AUSENTE';
    const STATUS_TARDANZA = 'TARDANZA';

    /**
     * Relación: Una asistencia pertenece a un usuario
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Asistencias del día actual
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }

    /**
     * Scope para filtrar asistencias por rango de fechas
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por usuario
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filtrar por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para filtrar por estado
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
