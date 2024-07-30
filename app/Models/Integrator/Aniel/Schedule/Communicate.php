<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communicate extends Model
{
    use HasFactory;

    protected $table = 'aniel_agenda_comunicacao';
    protected $connection = 'portal';
    protected $fillable = [
        'mensagem_id',
        'os_id',
        'protocolo',
        'celular_cliente',
        'template',
        'dados',
        'status_envio',
        'status_resposta',
        'data_envio'
    ];
}
