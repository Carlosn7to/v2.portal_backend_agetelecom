<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'aniel_agenda_servicos';
    protected $fillable = [
        'titulo',
        'descricao',
        'segmento'
    ];


    public function subServices()
    {
        return $this->hasMany(SubService::class, 'servico_id')
                ->select('id as subservice_id', 'servico_id', 'aniel_id', 'titulo');
    }

    public function capacity()
    {
        return $this->hasMany(Capacity::class, 'servico_id')
                ->select('id as id_capacity', 'servico_id', 'periodo', 'capacidade', 'data_inicio', 'data_fim');
    }
}
