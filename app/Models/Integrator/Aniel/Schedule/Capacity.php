<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capacity extends Model
{
    use HasFactory;

    protected $table = 'aniel_agenda_capacidade';
    protected $fillable = [
        'data',
        'dia_semana',
        'servico',
        'periodo',
        'hora_inicio',
        'hora_fim',
        'capacidade',
        'utilizado',
        'status',
        'atualizado_por',
        'motivo_fechamento'
    ];
    protected $connection = 'portal';


}
