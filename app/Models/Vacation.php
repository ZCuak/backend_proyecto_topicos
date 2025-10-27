<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Vacation extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'vacations';

    protected $fillable = [
        'user_id',
        'year',
        'start_date',
        'end_date',
        'days_programmed',
        'days_pending',
        'max_days',
        'status',
    ];

    protected $attributes = [
        'status' => 'pendiente',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
