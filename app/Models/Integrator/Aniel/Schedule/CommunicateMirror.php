<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicateMirror extends Model
{
    use HasFactory;
    protected $connection = 'portal';
    protected $table = 'aniel_agenda_comunicacao_espelho';
    protected $fillable = [
        'os_id',
        'protocolo',
        'status_aniel',
        'status_aniel_descricao',
        'data_agendamento',
        'envio_confirmacao',
        'envio_deslocamento'
    ];
}
