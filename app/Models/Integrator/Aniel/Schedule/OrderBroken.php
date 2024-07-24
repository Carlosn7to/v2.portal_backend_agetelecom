<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBroken extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $table = 'aniel_agenda_quebras';

    protected $fillable = [
      'os_id',
      'data',
      'protocolo',
      'servico',
      'subservico',
      'hora_agendamento',
      'periodo',
      'status',
      'localidade',
      'aberta_por',
      'setor',
      'solicitante_id',
      'aprovador_id'
    ];
}
