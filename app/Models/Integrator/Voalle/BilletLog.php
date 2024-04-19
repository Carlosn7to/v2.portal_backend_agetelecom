<?php

namespace App\Models\Integrator\Voalle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BilletLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'integracao_voalle_boletos_log';
    protected $connection = 'portal';
    protected $fillable = [
        'fatura_id',
        'status',
        'error',
    ];

}
