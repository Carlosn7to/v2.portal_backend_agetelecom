<?php

namespace App\Models\Portal\AgeReport\Management;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $table = 'age_relatorios';
    protected $fillable = [
        'nome',
        'tipo',
        'area',
        'iframe',
        'descricao',
        'consulta',
        'filtros',
        'conexao',
        'criado_por',
        'atualizado_por',
    ];
}
