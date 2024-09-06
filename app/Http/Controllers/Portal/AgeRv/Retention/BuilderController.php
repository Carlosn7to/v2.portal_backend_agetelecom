<?php

namespace App\Http\Controllers\Portal\AgeRv\Retention;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuilderController extends Controller
{
    private $data;

    public function __construct()
    {
        $this->getData();
    }

    public function getSupervisorDashboard()
    {
        return $this->buildingInfo();
    }

    private function buildingInfo()
    {
        $dataExample = [
            'operatorName' => [
                // Atendimentos recebidos
                'assignmentsReceived' => 10,
                // Total de retenções realizadas
                'retentionsPerform' => 5,
                // Porcentagem de retenção de contratos
                'retentionsPerformPercentage' => 50,
                'offensivesCity' => [
                    // Cidades com maior taxa de cancelamento, recebem valores na estrela OURO, as demais recebem PRATA.
                    ['city' => 'Samambaia', 'perform' => 5],
                    ['city' => 'Ceilândia', 'perform' => 3],
                    ['city' => 'Recanto das Emas', 'perform' => 2],
                    ['city' => 'Riacho Fundo 1', 'perform' => 0],
                    ['city' => 'Riacho Fundo 2', 'perform' => 0],
                    ['city' => 'Santa Maria', 'perform' => 0]
                ],
                'averageAmount' => ['before' => 1000, 'after' => 2000, 'diff' => 0],
                'starsValues' => [
                    // Aumenta o valor da estrela com base na porcentagem de retidos / recebidos
                    'silver' => [
                        ['stage' => 1, 'initial' => 0, 'final' => .49, 'value' => 0],
                        ['stage' => 2, 'initial' => .5, 'final' => .6, 'value' => 2],
                        ['stage' => 3, 'initial' => .61, 'final' => .7, 'value' => 3],
                        ['stage' => 4, 'initial' => .71, 'final' => .75, 'value' => 4],
                        ['stage' => 5, 'initial' => .76, 'final' => 1000, 'value' => 5],
                    ],
                    'gold' => [
                        ['stage' => 1, 'initial' => 0, 'final' => .49, 'value' => 0],
                        ['stage' => 2, 'initial' => .5, 'final' => .6, 'value' => 3],
                        ['stage' => 3, 'initial' => .61, 'final' => .7, 'value' => 4],
                        ['stage' => 4, 'initial' => .71, 'final' => .75, 'value' => 6],
                        ['stage' => 5, 'initial' => .76, 'final' => 1000, 'value' => 7],
                    ]
                ],
                'mediatorRules' => [
                    // Aumenta ou reduz a comissão com base no valor do ticket médio que a base retida
                    // havia antes e depois da retenção
                    ['stage' => 1, 'initial' => 0, 'final' => 4, 'value' => .2],
                    ['stage' => 2, 'initial' => 4.01, 'final' => 6, 'value' => .15],
                    ['stage' => 3, 'initial' => 6.01, 'final' => 10, 'value' => .1],
                    ['stage' => 4, 'initial' => 10.01, 'final' => 20, 'value' => -.1],
                    ['stage' => 5, 'initial' => 20.01, 'final' => 1000, 'value' => -.2],
                ],
            ]
        ];

        $attendants = $this->data->groupBy('Atendente_Origem');

        return $attendants;
        $operatorsData = [];

        foreach ($attendants as $operatorName => $records) {
            // Inicializa os contadores e variáveis
            $assignmentsReceived = $this->getDataFiltered($records->whereIn('Catalago_de_Servico', ['CLIENTE NÃO RETIDO', 'CLIENTE RETIDO']));


            $retentionsPerform = $records->where('Catalago_de_Servico', 'CLIENTE RETIDO')->count();
            $retentionsPerformPercentage = $assignmentsReceived ? ($retentionsPerform / $assignmentsReceived) * 100 : 0;

            // Cálculo do ticket médio antes e depois (convertendo para float)
            $averageBefore = $records->avg(fn($record) => (float) $record->Valor_Antigo);
            $averageAfter = $records->avg(fn($record) => (float) $record->Valor_Atual);
            $averageDiff = $averageAfter - $averageBefore;


            // Montando as cidades ofensivas
            $cityCounts = $records->groupBy('Endereço')->map(fn($group) => $group->count())->sortDesc();
            $offensivesCity = [];
            foreach ($cityCounts as $city => $count) {
                $offensivesCity[] = ['city' => $city, 'perform' => $count];
            }

            // Adicionando valores padrões para estrelas, silver e gold
            $starsValues = [
                'silver' => [
                    ['stage' => 1, 'initial' => 0, 'final' => 0.49, 'value' => 0],
                    ['stage' => 2, 'initial' => 0.5, 'final' => 0.6, 'value' => 2],
                    ['stage' => 3, 'initial' => 0.61, 'final' => 0.7, 'value' => 3],
                    ['stage' => 4, 'initial' => 0.71, 'final' => 0.75, 'value' => 4],
                    ['stage' => 5, 'initial' => 0.76, 'final' => 1000, 'value' => 5],
                ],
                'gold' => [
                    ['stage' => 1, 'initial' => 0, 'final' => 0.49, 'value' => 0],
                    ['stage' => 2, 'initial' => 0.5, 'final' => 0.6, 'value' => 3],
                    ['stage' => 3, 'initial' => 0.61, 'final' => 0.7, 'value' => 4],
                    ['stage' => 4, 'initial' => 0.71, 'final' => 0.75, 'value' => 6],
                    ['stage' => 5, 'initial' => 0.76, 'final' => 1000, 'value' => 7],
                ],
            ];

            // Regras do mediador
            $mediatorRules = [
                ['stage' => 1, 'initial' => 0, 'final' => 4, 'value' => 0.2],
                ['stage' => 2, 'initial' => 4.01, 'final' => 6, 'value' => 0.15],
                ['stage' => 3, 'initial' => 6.01, 'final' => 10, 'value' => 0.1],
                ['stage' => 4, 'initial' => 10.01, 'final' => 20, 'value' => -0.1],
                ['stage' => 5, 'initial' => 20.01, 'final' => 1000, 'value' => -0.2],
            ];

            // Adiciona os dados formatados para o operador
            $operatorsData[mb_convert_case($operatorName, MB_CASE_TITLE, 'utf-8')] = [
                'assignmentsReceived' => $assignmentsReceived,
                'retentionsPerform' => $retentionsPerform,
                'retentionsPerformPercentage' => $retentionsPerformPercentage,
                'offensivesCity' => $offensivesCity,
                'averageAmount' => [
                    'before' => $averageBefore,
                    'after' => $averageAfter,
                    'diff' => $averageDiff,
                ],
                'starsValues' => $starsValues,
                'mediatorRules' => $mediatorRules,
            ];
        }

        return $operatorsData;


    }

    private function getDataFiltered($items)
    {

        $count = 0;

        foreach($items as $item) {
            $cancel = collect(\DB::connection('voalle')->select($this->getDateCancelContractIfExist($item->contrato_id)));

            if(! $cancel) {
                $count++;
                break;
            }
        }


        return $count;
    }

    private function getDateCancelContractIfExist($contractId)
    {
        return <<<SQL
                    select * from erp.contracts c where c.id = $contractId and c.v_stage = 'Cancelado'
                SQL;

    }

    private function getData()
    {
        $this->data = collect(\DB::connection('voalle')->select($this->getQuery()));
    }

    private function getQuery()
    {
        return <<<SQL
                WITH eventuais AS (
                    SELECT
                        c.id AS contrato,
                        c.v_stage AS estagio,
                        c.v_status AS status,
                        ct.title AS titulo,
                        p.name AS cliente,
                        cev.month_year AS competencia,
                        c.amount AS valor,
                        SUM(cev.v_total_amount) AS total_eventual,
                        (c.amount + SUM(cev.v_total_amount)) AS total_value,
                        cev.created_by AS id_operador
                    FROM erp.contracts c
                    LEFT JOIN erp.contract_eventual_values cev ON cev.contract_id = c.id
                    LEFT JOIN erp.people p ON p.id = c.client_id
                    LEFT JOIN erp.contract_types ct ON ct.id = c.contract_type_id
                    WHERE cev.month_year = '2024-07-01'
                        AND c.v_stage != 'Cancelado'
                        AND cev.deleted IS false
                        AND EXISTS (SELECT 1 FROM erp.contract_eventual_values cev2 WHERE cev2.contract_id = c.id)
                    GROUP BY c.id, ct.id, p.name, cev.month_year, cev.created_by
                )
                SELECT
                    a.title AS "Protocolo des",
                    ai.protocol AS "Nº protocolo",
                    vu.name AS "Atendente_Origem",
                    cs.title AS "Catalago_de_Servico",
                    csi.title AS "itens de serviço",
                    csc.title AS "Sub item",
                    sp.title AS "Problema",
                    sc.title AS "Contexto",
                    a.beginning_date AS "data abertura",
                    CASE EXTRACT(MONTH FROM a.beginning_date)
                        WHEN 1 THEN 'Janeiro'
                        WHEN 2 THEN 'Fevereiro'
                        WHEN 3 THEN 'Março'
                        WHEN 4 THEN 'Abril'
                        WHEN 5 THEN 'Maio'
                        WHEN 6 THEN 'Junho'
                        WHEN 7 THEN 'Julho'
                        WHEN 8 THEN 'Agosto'
                        WHEN 9 THEN 'Setembro'
                        WHEN 10 THEN 'Outubro'
                        WHEN 11 THEN 'Novembro'
                        WHEN 12 THEN 'Dezembro'
                    END AS "mes abertura",
                    EXTRACT(YEAR FROM a.beginning_date) AS "ano abertura",
                    p2.id AS "ID cliente",
                    c2.id AS "contrato_id",
                    COALESCE(
                        (
                            SELECT
                                regexp_replace(ce.description, '.*Serviço Incluído:.*?Val. Unitário:R\$(\d+,\d+).*', '\1')
                            FROM erp.contract_events ce
                            WHERE ce.contract_id = c2.id
                                AND date(ce.date) = date(a.beginning_date)
                                AND ce.description NOT LIKE '%não geração de valores eventuais%'
                                AND contract_event_type_id = 133
                                AND ce.description ~ 'Serviço Incluído'
                            LIMIT 1
                        ), c.amount::text
                    ) AS "Valor_Atual",
                    COALESCE(
                        (
                            SELECT
                                regexp_replace(ce.description, '.*Serviço Excluído:.*?Val. Unitário:R\$(\d+,\d+).*', '\1')
                            FROM erp.contract_events ce
                            WHERE ce.contract_id = c2.id
                                AND date(ce.date) = date(a.beginning_date)
                                AND ce.description NOT LIKE '%não geração de valores eventuais%'
                                AND contract_event_type_id = 133
                                AND ce.description ~ 'Serviço Excluído'
                            LIMIT 1
                        ), c.amount::text
                    ) AS "Valor_Antigo",
                    e.total_eventual AS "Valor_eventual",
                    e.total_value AS "Valor_Total",
                    c2.v_status AS "Status",
                    c2.v_stage AS "Situacao",
                    pa.neighborhood AS "Endereço",
                    (c2.cancellation_date - c2.approval_date) AS "Aging do Contrato",
                    CASE
                        WHEN c2.final_date != '2050-01-01' THEN 'Fidelizado'
                        ELSE 'Não Fidelizado'
                    END AS "Fidelizado"
                FROM erp.assignments a
                LEFT JOIN erp.assignment_incidents ai ON ai.assignment_id = a.id
                LEFT JOIN erp.incident_types it ON it.id = ai.incident_type_id
                LEFT JOIN erp.catalog_services cs ON cs.id = ai.catalog_service_id
                LEFT JOIN erp.catalog_services_items csi ON csi.id = ai.catalog_service_item_id
                LEFT JOIN erp.catalog_service_item_classes csc ON csc.id = ai.catalog_service_item_class_id
                LEFT JOIN erp.solicitation_problems sp ON sp.id = ai.solicitation_problem_id
                LEFT JOIN erp.solicitation_classifications sc ON sc.id = ai.solicitation_classification_id
                LEFT JOIN erp.people p ON p.id = a.responsible_id
                LEFT JOIN erp.people p2 ON p2.id = a.requestor_id
                LEFT JOIN erp.v_users vu ON vu.id = a.created_by
                JOIN erp.contract_service_tags AS cst ON ai.contract_service_tag_id = cst.id
                JOIN erp.contracts c ON c.id = cst.contract_id
                JOIN erp.people_addresses pa ON pa.id = c.people_address_id
                JOIN erp.contracts AS c2 ON cst.contract_id = c2.id
                LEFT JOIN eventuais e ON e.contrato = c.id AND vu.id = e.id_operador
                WHERE it.id = 1068 and a.beginning_date >= '2024-06-01' and a.beginning_date < '2024-07-01'
        SQL;

    }
}
