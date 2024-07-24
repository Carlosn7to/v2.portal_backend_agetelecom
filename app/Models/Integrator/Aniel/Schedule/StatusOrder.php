<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusOrder extends Model
{
    use HasFactory;

    protected $table = 'aniel_agenda_status';
    protected $connection = 'portal';
    protected $fillable = [
        'titulo',
        'descricao',
        'cor_indicativa'
    ];
}
