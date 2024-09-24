<?php

namespace App\Http\Controllers\Portal\Services\BlockedScreen;


class IdentifyClient
{
    public string $identify;
    public $data;

    public function __construct($identify)
    {
        $this->identify = $this->removeCharacters($identify);
        $this->data = $this->getDataFromVoalle();
    }

    public function response()
    {
        return $this->data;
    }

    private function getDataFromVoalle()
    {
        $query = "select p.tx_id, p.cell_phone_1 as cellphone from erp.people p where p.tx_id = :tx_id";
        $result = \DB::connection('voalle')->select($query, ['tx_id' => $this->identify]);


        if(count($result) > 0) {
            return [
                'tx_id' => $result[0]->tx_id,
                'cellphone' => $this->removeCharacters($result[0]->cellphone),
            ];
        }

        return false;
    }


    private function removeCharacters($identify)
    {
        return preg_replace('/[^0-9]/', '', $identify);
    }
}


