<?php

namespace App\Models\HealthChecker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppResource extends Model
{
    use HasFactory;

    protected $connection = 'healthChecker';
    protected $table = 'aplicacao_recursos';
    protected $fillable = [
        'aplicacao_id',
        'cpu_nucleos_totais',
        'cpu_uso',
        'cpu_disponivel',
        'ram_total',
        'ram_uso',
        'disco_total',
        'disco_uso',
    ];
}