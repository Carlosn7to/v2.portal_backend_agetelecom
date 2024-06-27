<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions;

use App\Models\Integrator\Aniel\Schedule\SubService;

class SubServicesSync
{

    public function sync()
    {
        $subServices = new SubService();
        $data = \DB::connection('voalle')->select($this->getQuery());

        foreach($data as $key => $value) {

            $subServices->firstOrCreate([
                'servico_id' => 1,
                'titulo' => $value->title,
                'vinculado_por' => auth('portal')->user()->id
            ]);


        }

        return response()->json([
            'message' => 'Serviços sincronizados com sucesso!',
            'status' => 'Sucesso'
        ], 201);


    }

    private function getQuery()
    {

        return <<<SQL
                select
                    title
                from erp.incident_types it
                where active is true and service_field is true
        SQL;
    }

}
