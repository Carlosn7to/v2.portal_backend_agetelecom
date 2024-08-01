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
        'protocolo',
        'servico',
        'data_agendamento',
        'confirmacao_cliente',
        'localidade',
        'solicitante',
        'aprovador',
        'status',
        'cor_indicativa'
    ];
}
