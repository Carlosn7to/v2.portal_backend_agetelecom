<?php

namespace App\Models\Portal\Structure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingService extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $fillable = ['servico', 'comando', 'descricao', 'log', 'data_hora_alerta', 'data_hora_resolucao'];
    protected $table = 'portal_monitoramento';
}
