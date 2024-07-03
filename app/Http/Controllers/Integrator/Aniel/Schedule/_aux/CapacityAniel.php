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
//            SELECT tse.COD_TIPO_SERV, tse.descricao FROM TB_TIPO_SERVICO_EQUIPE tse
//        SQL;


        return <<<SQL
            SELECT DISTINCT
                dp.NUM_OBRA_ORIGINAL AS "N OS",
                dp.PROJETO,
                dp.COD_TIPO_SERV,
                CASE WHEN dp.COD_TIPO_SERV in ('96' , '4', '92', '51', '133') THEN 'MUDANDANÇA DE ENDEREÇO'
                WHEN dp.COD_TIPO_SERV in ('3', '95','1', '2') THEN 'INSTALAÇÃO'
                WHEN dp.COD_TIPO_SERV in ('57','55', '56', '41', '59', '97', '16') THEN 'MANUTENÇÃO'
                WHEN dp.COD_TIPO_SERV IN ('52', '94', '8', '10','93','5', '11', '58', '9', '6', '7', '99', '12', '63', '53', '161', '163') THEN 'VISITA TÉCNICA'
                ELSE dp.COD_TIPO_SERV END AS "TIPO_SERVICO",
                c.RAZAO_SOCIAL AS "Nome Cliente",
                CASE WHEN e.NOME IS NULL THEN 'SEM TÉCNICO ATRIBUIDO'
                ELSE e.NOME END AS "Nome Tecnico",
                dp.DIA AS "Criacao",
                ts.DESCRICAO as "Turno",
                ts.DESCRICAO AS "Turno_True",
                dp.DATA_MAXIMA AS "Data_do_Agendamento",
                dp.HORA_MAXIMA as "Hora_do_Agendamento"
            FROM TB_DOCUMENTO_PRODUCAO dp
            LEFT JOIN TB_CLIENTE c ON c.RAZAO_SOCIAL = dp.TITULAR
            LEFT JOIN TB_EQUIPE e ON e.EQUIPE = dp.EQUIPE
            LEFT JOIN TB_TURNO_SERVICO ts ON ts.ID = dp.COD_TURNO_SERV
                WHERE
                dp.DATA_MAXIMA BETWEEN '2024-06-19' AND '2024-06-19'
                AND dp.COD_TIPO_SERV NOT IN ('57','55', '56', '41', '59')
                -- and dp.NUM_OBRA_ORIGINAL = '99999999'
        SQL;


    }

}
