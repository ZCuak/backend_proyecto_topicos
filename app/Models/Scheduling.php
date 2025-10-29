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
        'start_date',
        'end_date',
        'status',
        'notes',
        'days',
    ];

    protected $dates = [
        'date',
        'start_date',
        'end_date'
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

    /**
     * Relación con los detalles de programación
     */
    public function details()
    {
        return $this->hasMany(SchedulingDetail::class, 'scheduling_id');
    }

    /**
     * Obtener solo los conductores de esta programación (usertype_id = 1)
     */
    public function conductors()
    {
        return $this->details()->where('usertype_id', 1);
    }

    /**
     * Obtener solo los ayudantes de esta programación (usertype_id = 2)
     */
    public function ayudantes()
    {
        return $this->details()->where('usertype_id', 2);
    }

    /**
     * Obtener usuarios de esta programación
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, SchedulingDetail::class, 'scheduling_id', 'id', 'id', 'user_id');
    }

    /**
     * Obtener el conductor principal (usertype_id = 1, position_order = 1)
     */
    public function conductor()
    {
        return $this->details()->where('usertype_id', 1)->where('position_order', 1)->first();
    }

    /**
     * Obtener ayudantes ordenados por posición (usertype_id = 2)
     */
    public function ayudantesOrdered()
    {
        return $this->details()->where('usertype_id', 2)->orderBy('position_order');
    }

    /**
     * Verificar si es una programación de rango (tiene start_date y end_date)
     */
    public function isRangeScheduling()
    {
        return !is_null($this->start_date) && !is_null($this->end_date);
    }

    /**
     * Obtener la duración en días
     */
    public function getDurationInDays()
    {
        if ($this->isRangeScheduling()) {
            return \Carbon\Carbon::parse($this->start_date)->diffInDays(\Carbon\Carbon::parse($this->end_date)) + 1;
        }
        return 1; // Programación de un solo día
    }

    /**
     * Obtener todas las fechas del rango
     */
    public function getAllDates()
    {
        if (!$this->isRangeScheduling()) {
            $dateValue = is_string($this->date) ? $this->date : $this->date->format('Y-m-d');
            return [$dateValue];
        }

        $dates = [];
        $current = \Carbon\Carbon::parse($this->start_date);
        $end = \Carbon\Carbon::parse($this->end_date);

        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Scope para programaciones de rango
     */
    public function scopeRangeSchedulings($query)
    {
        return $query->whereNotNull('start_date')->whereNotNull('end_date');
    }

    /**
     * Scope para programaciones de un solo día
     */
    public function scopeSingleDaySchedulings($query)
    {
        return $query->whereNull('start_date')->whereNull('end_date');
    }

    /**
     * Obtener los días seleccionados como array
     */
    public function getDaysArrayAttribute()
    {
        if (empty($this->days)) {
            return [];
        }

        return is_array($this->days) ? $this->days : json_decode($this->days, true);
    }

    /**
     * Establecer los días desde un array
     */
    public function setDaysAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['days'] = json_encode($value);
        } else {
            $this->attributes['days'] = $value;
        }
    }

    /**
     * Verificar si un día específico está seleccionado
     */
    public function hasDay($day)
    {
        $days = $this->getDaysArrayAttribute();
        return in_array($day, $days);
    }

    /**
     * Obtener los nombres de los días en español
     */
    public function getDaysNamesAttribute()
    {
        $daysMap = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo',
        ];

        $selectedDays = $this->getDaysArrayAttribute();
        return array_map(function($day) use ($daysMap) {
            return $daysMap[$day] ?? $day;
        }, $selectedDays);
    }
}
