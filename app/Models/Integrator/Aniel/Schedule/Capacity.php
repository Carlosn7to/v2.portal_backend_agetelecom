<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capacity extends Model
{
    use HasFactory;

    protected $table = 'aniel_agenda_capacidade';
    protected $fillable = [
        'servico_id',
        'periodo',
        'capacidade',
        'data_inicio',
        'data_fim'
    ];
    protected $connection = 'portal';


}
