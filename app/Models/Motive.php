<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motive extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function audits()
    {
        return $this->hasMany(Audit::class, 'motive_id');
    }
}
