<?php

namespace App\Models\HealthChecker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppQueue extends Model
{
    use HasFactory;

    protected $table = 'aplicacao_filas';
    protected $fillable = [
        'aplicacao_id',
        'processadas',
        'pendentes',
        'erros'
    ];
    protected $connection = 'healthChecker';

    public function application()
    {
        return $this->belongsTo(App::class, 'aplicacao_id');
    }
}
