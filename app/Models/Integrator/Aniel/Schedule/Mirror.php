<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mirror extends Model
{
    use HasFactory;

    protected $table = 'aniel_agenda_espelho';
    protected $connection = 'portal';
    protected $fillable = [
        'cliente_id',
        'protocolo',
        'servico',
        'sub_servico',
        'data_agendamento',
        'confirmacao_cliente',
        'confirmacao_deslocamento',
        'localidade',
        'solicitante',
        'aprovador',
        'status',
        'cor_indicativa',
        'responsavel'
    ];
}
