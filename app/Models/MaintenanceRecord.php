<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'schedule_id', 'date', 'description', 'image_path', 'status'];

    public function schedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }
}