<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'contracts';

    protected $fillable = [
        'type',
        'date_start',
        'date_end',
        'description',
        'vacation_days_per_year',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
