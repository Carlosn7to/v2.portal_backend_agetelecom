<?php

namespace App\Http\Controllers\Integrator\Aniel\Schedule\_actions;

use App\Models\Integrator\Aniel\Schedule\SubService;
use Illuminate\Support\Facades\DB;

class SubServicesSync
{

    public function sync()
    {
        $subServices = new SubService();
        $data = \DB::connection('voalle')->select($this->getQuery());

        foreach($data as $key => $value) {

            $subServices->firstOrCreate(['titulo' => $value->title],[
                'servico_id' => 1,
                'voalle_id' => $value->id,
                'titulo' => $value->title,
                'vinculado_por' => auth('portal')->user()->id
            ]);

        }

        return $this->syncIdAniel();

        return response()->json([
            'message' => 'Serviços sincronizados com sucesso!',
            'status' => 'Sucesso'
        ], 201);


    }

    private function syncIdAniel()
    {
        $data = DB::connection('aniel')->select($this->getQueryAniel());

        return $data;

        foreach($data as $key => $value) {

            $subService = SubService::where('titulo', $value->DESCRICAO)->first();

            if($subService) {
                $subService->update([
                    'aniel_id' => $value->COD_TIPO_SERV
                ]);
            }

        }

    }

    private function getQueryAniel()
    {
        return <<<SQL
            SELECT tse.COD_TIPO_SERV, tse.descricao FROM TB_TIPO_SERVICO_EQUIPE tse
        SQL;

    }

    private function getQuery()
    {

        return <<<SQL
                select
                    id,
                    title
                from erp.incident_types it
                where active is true and service_field is true
        SQL;
    }

}
