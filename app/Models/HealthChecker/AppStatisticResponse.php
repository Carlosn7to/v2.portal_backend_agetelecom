<?php

namespace App\Models\HealthChecker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppStatisticResponse extends Model
{
    use HasFactory;

    protected $table = 'aplicacao_estatistica_respostas';
    protected $fillable = [
      'aplicacao_id',
      'tempo_resposta',
      'status_codigo',
    ];
    protected $connection = 'healthChecker';
}
