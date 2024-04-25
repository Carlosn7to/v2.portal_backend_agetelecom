<?php

namespace App\Models\Portal\AgeCommunicate\BillingRule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DataVoalle extends Model
{
    protected $connection = 'voalle';

    protected $data;

    public function __construct()
    {
        parent::__construct();
        $this->getData();
    }


    private function getData() : void
    {
        $this->data = DB::connection($this->getConnectionName())->select($this->getQuery());
    }

    private function getQuery() : string
    {
        return <<<SQL
            SELECT
                c.id AS "contract_id",
                i.title as "segregation",
                p.email AS "email",
                p.v_name AS "name",
                frt.document_amount,
                p.tx_id,
                CASE
                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
                    ELSE p.cell_phone_2
                END AS "phone",
                frt.typeful_line AS "barcode",
                frt.expiration_date AS "expiration_date",
                frt.competence AS "competence",
                CASE
                    WHEN frt.expiration_date > current_date THEN -(frt.expiration_date - current_date)
                    ELSE (current_date - frt.expiration_date)
                END AS "days_until_expiration",
                frt.id AS "frt_id"
            FROM erp.contracts c
            LEFT JOIN erp.people p ON p.id = c.client_id
            LEFT JOIN erp.financial_receivable_titles frt ON frt.contract_id = c.id
            left join erp.insignias i on i.id = p.insignia_id
            WHERE
                c.v_stage = 'Aprovado'
                AND c.v_status != 'Cancelado'
                AND frt.competence >= '2023-05-01'
                AND frt.deleted IS FALSE
                AND frt.finished IS FALSE
                AND frt.title LIKE '%FAT%'
                AND frt.p_is_receivable IS TRUE
                AND frt.typeful_line IS NOT NULL
                --and p.insignia_id is not null
        SQL;
    }

    public function getDataResults()
    {
        $formattedResults = [];

        foreach ($this->data as $row) {
            $formattedResults[] = $this->getAttributesData($row);
        }

        return $formattedResults;
    }

    private function getAttributesData($row)
    {
        return [
            'name' => $this->formmatedName($row->name),
            'email' => $this->sanitazeMail($row->email),
            'document_amount' => $row->document_amount,
            'tx_id' => $this->formmatedTxId($row->tx_id),
            'barcode' => $row->barcode,
            'expiration_date' => $row->expiration_date,
            'competence' => $row->competence,
            'frt_id' => $row->frt_id,
            'contract_id' => $row->contract_id,
            'segmentation' => $this->segregation != null ? $this->segregation : 'prata',
            'phone' => $this->sanitizeCellphone($row->phone),
            'phone_original' => $row->phone,
            'days_until_expiration' => $row->days_until_expiration
        ];
    }

    private function sanitazeMail($email)
    {
        if(filter_var($email, FILTER_SANITIZE_EMAIL)){
            return $email;
        } else {
            return false;
        }

    }

    private function formmatedName($name)
    {
        $name = mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');

        $lowerCaseWords = ["Da", "Das", "De", "Do", "Dos", "Di"];

        return preg_replace_callback(
            '/\b(' . implode('|', $lowerCaseWords) . ')\b/u',
            function($matches) {
                return mb_strtolower($matches[0]);
            },
            $name
        );
    }

    function formmatedTxId($txId)
    {
        $cleanedtxId = preg_replace('/[^0-9]/', '', $txId);

        switch (strlen($cleanedtxId)) {
            case 11: // CPF
                $formattedTxId = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cleanedtxId);
                break;
            case 14: // CNPJ
                $formattedTxId = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cleanedtxId);
                break;
            default:
                return $txId;
        }

        return $formattedTxId;

    }

    private function sanitizeCellphone($cellphone)
    {


        $cellphoneFormmated = preg_replace('/[^0-9]/', '', $cellphone);
        $cellphoneFormmated = $this->formatCellphone($cellphoneFormmated);


        return $cellphoneFormmated;


    }

    private function formatCellphone($cellphone)
    {
        $cellphone = strlen($cellphone) <= 11 ? '55' . $cellphone : $cellphone;
        $cellphone = strlen($cellphone) == 12 ?
            substr_replace($cellphone, '9', 4, 0) :
            $cellphone;

        return $cellphone;
    }
}
