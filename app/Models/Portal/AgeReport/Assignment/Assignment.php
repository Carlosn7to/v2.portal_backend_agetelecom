<?php

namespace App\Models\Portal\AgeReport\Assignment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $table = 'age_relatorio_solicitacoes';
    protected $fillable = [
      'relatorio_id',
        'usuario_id',
        'tipo',
        'status',
        'caminho_arquivo',
        'email'
    ];
}
