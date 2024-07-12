<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacityWeekly extends Model
{
    use HasFactory;

    protected $table = 'aniel_agenda_capacidade_semanal';
    protected $connection = 'portal';
    protected $fillable = [
        'servico_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
        'capacidade',
        'status',
        'criado_por',
        'atualizado_por',
        'data_final'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'servico_id', 'id');
    }
}
