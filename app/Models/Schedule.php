<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'schedules';

    protected $fillable = [
        'name',
        'time_start',
        'time_end',
        'description'
    ];

    protected $casts = [
        'time_start' => 'datetime:H:i',
        'time_end' => 'datetime:H:i'
    ];
}
