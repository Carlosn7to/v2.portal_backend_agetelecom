<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\Reports;

use App\Http\Controllers\Controller;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\ReportSms;
use App\Models\Portal\AgeCommunicate\BillingRule\Reports\ReportSmsLog;
use Illuminate\Http\Request;
use Infobip\Model\SmsInboundMessageResult;
use Infobip\ObjectSerializer;

class RealTimeController extends Controller
{

    public function __construct()
    {
        $this->middleware('portal.ageCommunicate.infoBip.access');
    }

    public function handle(Request $request)
    {

        $result = $request->json('results');

        // Inicializa a query básica usando messageId, que sempre existe
        $query = ReportSms::where('mensagem_id', $result[0]['messageId']);

        // Adiciona condição para bulkId apenas se ele existir
        if (isset($result[0]['bulkId'])) {
            $query = $query->orWhere('bulk_id', $result[0]['bulkId']);
        }

        // Executa a consulta
        $reports = $query->get();

        // Inicializa uma flag para verificar se houve mudanças
        $changes = false;

        foreach ($reports as $report) {
            // Verifica se há diferença entre os dados atuais e os novos dados
            if ($report->status !== $result[0]['status']['groupId'] ||
                $report->status_descricao !== $result[0]['status']['id']) {
                $changes = true;

                // Atualiza o registro já que os dados são diferentes
                $report->update([
                    'status' => $result[0]['status']['groupId'],
                    'status_descricao' => $result[0]['status']['id']
                ]);
            }
        }

        $log = ReportSmsLog::create([
            'bulk_id' => $result[0]['bulkId'],
            'mensagem_id' => $result[0]['messageId'],
            'celular' => $result[0]['to'],
            'resposta_infobip' => json_encode($result),
            'status' => $changes ? 2 : 3
        ]);

        return response()->json(['message' => 'Webhook recebido com sucesso!'], 200);
    }
}
