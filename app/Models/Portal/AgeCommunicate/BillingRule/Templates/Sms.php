<?php

namespace App\Models\Portal\AgeCommunicate\BillingRule\Templates;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sms extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'age_comunica_templates_sms';
    protected $connection = 'portal';
    protected $fillable = [
        'titulo',
        'conteudo',
        'regra',
        'integrador_id',
        'status',
        'criado_por',
        'atualizado_por',
        'deletado_por',
    ];


}
