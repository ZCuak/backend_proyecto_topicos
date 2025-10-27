<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Zone;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Scheduling;

class ConfigGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'configgroups';

    protected $guarded = [
    ];

    public function group()
    {
        return $this->belongsTo(EmployeeGroup::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
