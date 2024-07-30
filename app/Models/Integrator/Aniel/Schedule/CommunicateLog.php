<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicateLog extends Model
{
    use HasFactory;

    protected $table = 'aniel_agenda_comunicacao_log';
    protected $connection = 'portal';
    protected $fillable = [
        'envio_id',
        'status_envio',
        'status_resposta',
        'atualizado_em'
    ];
}
