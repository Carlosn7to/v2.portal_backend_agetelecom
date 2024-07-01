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
                'titulo' => $value->title,
                'vinculado_por' => auth('portal')->user()->id
            ]);


        }

        return response()->json([
            'message' => 'Serviços sincronizados com sucesso!',
            'status' => 'Sucesso'
        ], 201);


    }

    private function syncId()
    {
        $data = DB::connection('voalle')->select($this->getQuery());

        $subServices = new SubService();
        foreach($data as $key => $value) {
            $subServices->where('titulo', $value->title)->update(['voalle_id' => $value->id]);
        }

        return response()->json([
            'message' => 'Serviços atualizados com sucesso!',
            'status' => 'Sucesso'
        ], 201);
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
