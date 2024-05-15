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
                c.v_status AS "contract_status",
                p.email AS "email",
                p.v_name AS "name",
                frt.document_amount,
                p.tx_id,
                CASE
                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
                    ELSE p.cell_phone_2
                END AS "phone",
                frt.typeful_line AS "typeful_line",
                frt.expiration_date AS "expiration_date",
                frt.competence AS "competence",
                CASE
                    WHEN frt.expiration_date > current_date THEN -(frt.expiration_date - current_date)
                    ELSE (current_date - frt.expiration_date)
                END AS "days_until_expiration",
                frt.id AS "frt_id",
                frt.pix_qr_code as "pix_qrcode"
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
                and frt.financer_nature_id = 59
                --and p.insignia_id is not null
                --and frt.pix_qr_code is null
                and c.id NOT IN (93824, 93818, 93820, 93816, 101199, 93814, 93791, 93739, 93731, 93691, 93683, 93682, 66300, 73745, 66027, 93679, 93678, 66017, 93665, 65528, 93651, 93644, 93645, 18305, 93664, 93641, 93635, 93631, 93589, 93569, 93550, 93531, 85681, 93549, 93502, 93449, 80564, 93365, 93361, 93359, 93342, 21485, 46366, 93279, 93310, 93251, 93230, 93147, 93154, 93131, 96255, 93504, 96255)
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
            'contract_status' =>$row->contract_status,
            'tx_id' => $this->formmatedTxId($row->tx_id),
            'barcode' => $row->typeful_line,
            'expiration_date' => $row->expiration_date,
            'competence' => $row->competence,
            'frt_id' => $row->frt_id,
            'contract_id' => $row->contract_id,
            'segmentation' => $this->segregation != null ? $this->segregation : 'prata',
            'phone' => $this->sanitizeCellphone($row->phone),
            'phone_original' => $row->phone,
            'days_until_expiration' => $row->days_until_expiration,
            'pix_qrcode' => $row->pix_qrcode
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

        $dddValids = [
            11, 12, 13, 14, 15, 16, 17, 18, 19, // São Paulo
            21, 22, 24, // Rio de Janeiro
            27, 28, // Espírito Santo
            31, 32, 33, 34, 35, 37, 38, // Minas Gerais
            41, 42, 43, 44, 45, 46, // Paraná
            47, 48, 49, // Santa Catarina
            51, 53, 54, 55, // Rio Grande do Sul
            61, 62, 64, // Goiás
            63, // Tocantins
            65, 66, // Mato Grosso
            67, // Mato Grosso do Sul
            68, // Acre
            69, // Rondônia
            71, 73, 74, 75, 77, // Bahia
            79, // Sergipe
            81, 82, // Pernambuco
            83, // Paraíba
            84, // Rio Grande do Norte
            85, 88, // Ceará
            86, 89, // Piauí
            87, // Pernambuco
            91, 93, 94, // Pará
            92, 97, // Amazonas
            95, // Roraima
            96, // Amapá
            98, 99 // Maranhão
        ];

        if(!in_array(substr($cellphoneFormmated, 0, 2), $dddValids)){
            return false;
        }

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
