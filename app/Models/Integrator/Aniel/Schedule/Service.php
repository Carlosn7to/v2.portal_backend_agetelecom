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
}
