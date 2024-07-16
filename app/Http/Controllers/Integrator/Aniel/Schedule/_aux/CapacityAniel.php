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

    private function getData()
    {
        $this->data = \DB::connection('aniel')->select($this->getQuery());

    }

    private function getQuery()
    {
//        return <<<SQL
//            SELECT * FROM TB_DOCUMENTO_PRODUCAO tdp
//            where tdp.
//            rows 1
//        SQL;


        return <<<SQL
            SELECT DISTINCT
                dp.NUM_OBRA_ORIGINAL AS "N OS",
                dp.PROJETO,
                dp.COD_TIPO_SERV,
                LOWER(tse.descricao) AS "TIPO_SERVICO_ANIEL",
                c.RAZAO_SOCIAL AS "Nome Cliente",
                CASE WHEN e.NOME IS NULL THEN 'SEM TÉCNICO ATRIBUIDO'
                ELSE e.NOME END AS "Nome Tecnico",
                dp.DIA AS "Criacao",
                ts.DESCRICAO as "Turno",
                dp.DATA_MAXIMA AS "Data_do_Agendamento",
                dp.HORA_MAXIMA as "Hora_do_Agendamento"
            FROM TB_DOCUMENTO_PRODUCAO dp
            LEFT JOIN TB_CLIENTE c ON c.RAZAO_SOCIAL = dp.TITULAR
            LEFT JOIN TB_EQUIPE e ON e.EQUIPE = dp.EQUIPE
            LEFT JOIN TB_TURNO_SERVICO ts ON ts.ID = dp.COD_TURNO_SERV
            LEFT JOIN TB_TIPO_SERVICO_EQUIPE tse ON tse.COD_TIPO_SERV = dp.COD_TIPO_SERV
            WHERE dp.DATA_MAXIMA = '{$this->period}'
        SQL;


    }

}
