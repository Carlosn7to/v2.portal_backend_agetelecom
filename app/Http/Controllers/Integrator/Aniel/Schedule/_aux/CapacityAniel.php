<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_aux;

use App\Http\Controllers\Integrator\Aniel\Schedule\_actions\SubServicesSync;

class CapacityAniel
{
    private $data;
    private $period;

    public function __construct($period)
    {
        $this->period = $period;
        $this->getData();
    }

    public function getCapacityAniel()
    {
        return collect($this->data);

    }

    private function dataConsolidation()
    {
        $dataConsolidated = [];

        foreach($this->data as $key => $value) {
            $osSearch = \DB::connection('aniel')->select($this->getQueryConsolidation($value->N_OS));

            $dataConsolidated[] = $osSearch[0];
        }

        return collect($dataConsolidated);


    }

    private function getQueryConsolidation($osOrigin)
    {




//        return <<<SQL
//           SELECT
//                dp.NUM_OBRA_ORIGINAL AS "N_OS",
//                dp.PROJETO,
//                dp.NUM_OBRA,
//                dp.COD_TIPO_SERV,
//                LOWER(tse.descricao) AS "TIPO_SERVICO_ANIEL",
//                c.RAZAO_SOCIAL AS "Nome Cliente",
//                CASE WHEN e.NOME IS NULL THEN 'SEM TÉCNICO ATRIBUIDO'
//                ELSE e.NOME END AS "Nome Tecnico",
//                dp.DIA AS "Criacao",
//                ts.DESCRICAO as "Turno",
//                dp.DATA_MAXIMA AS "Data_do_Agendamento",
//                dp.HORA_MAXIMA as "Hora_do_Agendamento",
//                dp.STATUS_EXECUCAO as "Status",
//                TRIM(
//                    CASE
//                        WHEN dp.STATUS_EXECUCAO = 0 THEN 'Aberta Aguardando Atendimento'
//                        WHEN dp.STATUS_EXECUCAO = 1 THEN 'Fechada Improdutiva'
//                        WHEN dp.STATUS_EXECUCAO = 2 THEN 'Fechada Produtiva'
//                        WHEN dp.STATUS_EXECUCAO = 3 THEN 'Atendimento Iniciado'
//                        WHEN dp.STATUS_EXECUCAO = 4 THEN 'Aberta Aguardando Agendamento'
//                        WHEN dp.STATUS_EXECUCAO = 6 THEN 'OS em Deslocamento'
//                        WHEN dp.STATUS_EXECUCAO = 9 THEN 'Cancelada'
//                        WHEN dp.STATUS_EXECUCAO = 10 THEN 'Paralisado'
//                        WHEN dp.STATUS_EXECUCAO = 11 THEN 'Atendimento Reiniciado'
//                        WHEN dp.STATUS_EXECUCAO = 13 THEN 'Aberta Aguardando Responsável'
//                        ELSE 'Status Desconhecido'
//                    END
//                ) AS "Status_Descritivo"
//            FROM TB_DOCUMENTO_PRODUCAO dp
//            LEFT JOIN TB_CLIENTE c ON c.RAZAO_SOCIAL = dp.TITULAR
//            LEFT JOIN TB_EQUIPE e ON e.EQUIPE = dp.EQUIPE
//            LEFT JOIN TB_TURNO_SERVICO ts ON ts.ID = dp.COD_TURNO_SERV
//            LEFT JOIN TB_TIPO_SERVICO_EQUIPE tse ON tse.COD_TIPO_SERV = dp.COD_TIPO_SERV
//            WHERE dp.NUM_OBRA_ORIGINAL = '$osOrigin' order by dp.NUM_OBRA desc
//        SQL;

    }

    private function getData()
    {
        $this->data = \DB::connection('aniel')->select($this->getQuery());

    }

    private function getQuery()
    {

        return <<<SQL
            SELECT DISTINCT
                dp.NUM_OBRA_ORIGINAL AS "N_OS",
                dp.NUM_OBRA,
                dp.PROJETO,
                dp.COD_TIPO_SERV,
                LOWER(tse.descricao) AS "TIPO_SERVICO_ANIEL",
                c.RAZAO_SOCIAL AS "Nome Cliente",
                CASE WHEN e.NOME IS NULL THEN 'SEM TÉCNICO ATRIBUIDO'
                     ELSE e.NOME END AS "Nome Tecnico",
                dp.DIA AS "Criacao",
                ts.DESCRICAO AS "Turno",
                dp.DATA_MAXIMA AS "Data_do_Agendamento",
                dp.HORA_MAXIMA AS "Hora_do_Agendamento",
                dp.STATUS_EXECUCAO as "Status",
                TRIM(
                    CASE
                        WHEN dp.STATUS_EXECUCAO = 0 THEN 'Aberta Aguardando Atendimento'
                        WHEN dp.STATUS_EXECUCAO = 1 THEN 'Fechada Improdutiva'
                        WHEN dp.STATUS_EXECUCAO = 2 THEN 'Fechada Produtiva'
                        WHEN dp.STATUS_EXECUCAO = 3 THEN 'Atendimento Iniciado'
                        WHEN dp.STATUS_EXECUCAO = 4 THEN 'Aberta Aguardando Agendamento'
                        WHEN dp.STATUS_EXECUCAO = 6 THEN 'OS em Deslocamento'
                        WHEN dp.STATUS_EXECUCAO = 9 THEN 'Cancelada'
                        WHEN dp.STATUS_EXECUCAO = 10 THEN 'Paralisado'
                        WHEN dp.STATUS_EXECUCAO = 11 THEN 'Atendimento Reiniciado'
                        WHEN dp.STATUS_EXECUCAO = 13 THEN 'Aberta Aguardando Responsável'
                        ELSE 'Status Desconhecido'
                    END
                ) AS "Status_Descritivo"
            FROM TB_DOCUMENTO_PRODUCAO dp
            LEFT JOIN TB_CLIENTE c ON c.RAZAO_SOCIAL = dp.TITULAR
            LEFT JOIN TB_EQUIPE e ON e.EQUIPE = dp.EQUIPE
            LEFT JOIN TB_TURNO_SERVICO ts ON ts.ID = dp.COD_TURNO_SERV
            LEFT JOIN TB_TIPO_SERVICO_EQUIPE tse ON tse.COD_TIPO_SERV = dp.COD_TIPO_SERV
            JOIN (
                SELECT
                    NUM_OBRA_ORIGINAL,
                    MAX(NUM_OBRA) AS max_num_obra
                FROM TB_DOCUMENTO_PRODUCAO
                GROUP BY NUM_OBRA_ORIGINAL
            ) sub ON dp.NUM_OBRA_ORIGINAL = sub.NUM_OBRA_ORIGINAL
                  AND dp.NUM_OBRA = sub.max_num_obra
            WHERE dp.DATA_MAXIMA = '{$this->period}'
            ORDER BY dp.NUM_OBRA DESC;
        SQL;


//        return <<<SQL
//            SELECT tdp.NUM_OBRA, tdp.NUM_OBRA_ORIGINAL, tdp.STATUS_EXECUCAO,
//                   tdp.DIA,
//                   tdp.DATA_MAXIMA
//            FROM TB_DOCUMENTO_PRODUCAO tdp
//            where tdp.NUM_OBRA_ORIGINAL = '1166450'
//        SQL;
//
//        return <<<SQL
//            SELECT DISTINCT
//                dp.NUM_OBRA_ORIGINAL AS "N_OS"
//            FROM TB_DOCUMENTO_PRODUCAO dp
//            WHERE dp.DATA_MAXIMA = '{$this->period}'
//        SQL;


    }

}


