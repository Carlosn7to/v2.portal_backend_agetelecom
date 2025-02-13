<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubService extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'aniel_agenda_subservicos';
    protected $fillable = [
        'id',
        'voalle_id',
        'aniel_id',
        'servico_id',
        'titulo',
        'descricao',
        'vinculado_por'
    ];
    protected $connection = 'portal';

}
