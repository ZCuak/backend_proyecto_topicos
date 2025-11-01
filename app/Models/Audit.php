<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Audit extends Model
{
    use HasFactory;

    protected $table = 'audits';

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'user_name',
        'nota_adicional',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
