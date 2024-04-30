<?php

namespace App\Models\Portal\AgeCommunicate\BillingRule\Reports;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSmsLog extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $table = 'age_comunica_envios_sms_logs';
    protected $fillable = [
        'bulk_id',
        'mensagem_id',
        'celular',
        'resposta_infobip',
        'status',
       ];
}
