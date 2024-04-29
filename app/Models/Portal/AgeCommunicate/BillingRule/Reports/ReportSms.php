<?php

namespace App\Models\Portal\AgeCommunicate\BillingRule\Reports;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSms extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $table = 'age_comunica_envios_sms';
    protected $fillable = [
      'bulk_id',
        'mensagem_id',
        'contrato_id',
        'fatura_id',
        'celular',
        'celular_voalle',
        'segregacao',
        'regra',
        'status',
        'erro',
        'template_id'
    ];
}
