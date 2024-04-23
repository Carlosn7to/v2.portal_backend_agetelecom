<?php

namespace App\Models\Portal\AgeCommunicate\BillingRule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integrator extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'age_comunica_integradores';
    protected $connection = 'portal';
    protected $fillable = [
        'titulo',
        'configuracao',
        'instrucao',
        'canais',
        'status',
        'criado_por',
        'atualizado_por',
        'deletado_por',
    ];


    public function getConfiguracaoAttribute($value)
    {
        return json_decode($value, true);
    }
}
