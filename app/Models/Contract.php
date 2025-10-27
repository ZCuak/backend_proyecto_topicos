<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\UserType;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contracts';

    protected $fillable = [
        'type',
        'date_start',
        'date_end',
        'vacation_days_per_year',
        'is_active',
        'user_id',
        'salary',
        'position_id',
        'department_id',
        'probation_period_months',
        'termination_reason',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end'   => 'date',
        'is_active'  => 'boolean',
        'salary' => 'decimal:2',
        'probation_period_months' => 'integer',
        'position_id' => 'integer',
        'department_id' => 'integer',
    ];

    /**
     * Usuario asociado al contrato
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Position hace referencia a UserType
     */
    public function position()
    {
        return $this->belongsTo(UserType::class, 'position_id');
    }

    /**
     * Departamento asociado
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
