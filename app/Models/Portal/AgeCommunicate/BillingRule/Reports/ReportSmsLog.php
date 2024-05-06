<?php

namespace App\Models\Portal\AgeCommunicate\BillingRule\Reports;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSmsLog extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $table = 'age_comunica_envios_logs';
    protected $fillable = [
        'envio_id',
        'bulk_id',
        'mensagem_id',
        'enviado_para',
        'resposta_webhook',
        'status',
       ];
}
