<?php

namespace App\Models\HealthChecker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    use HasFactory;

    protected $table = 'aplicacoes';
    protected $connection = 'healthChecker';
    protected $fillable = [
        'nome',
        'descricao',
        'url',
        'ip',
        'status',
        'publica',
        'monitoramento',
    ];
}
