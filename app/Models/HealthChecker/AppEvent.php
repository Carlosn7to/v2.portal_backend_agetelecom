<?php

namespace App\Models\HealthChecker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppEvent extends Model
{
    use HasFactory;

    protected $table = 'aplicacao_eventos';
    protected $connection = 'healthChecker';
    protected $fillable = [
        'aplicacao_id',
        'status',
        'evento',
        'tipo',
        'descricao',
        'dados'
    ];
}
