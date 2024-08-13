<?php

namespace App\Models\Integrator\Aniel\Schedule;

use App\Models\Portal\User\User;
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
        'data_fechamento',
        'hora_fechamento',
        'atualizado_por',
        'motivo_fechamento'
    ];
    protected $connection = 'portal';

    public function user()
    {
        return $this->belongsTo(User::class, 'atualizado_por', 'id')->select('id', 'nome');
    }

}
