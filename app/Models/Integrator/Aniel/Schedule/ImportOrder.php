<?php

namespace App\Models\Integrator\Aniel\Schedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportOrder extends Model
{
    use HasFactory;

    protected $table = 'aniel_importacao_ordens';
    protected $connection = 'portal';
    protected $fillable = [
        'atendimento_id',
        'protocolo',
        'contrato_id',
        'cliente_id',
        'cliente_documento',
        'cliente_nome',
        'email',
        'celular_1',
        'celular_2',
        'endereco',
        'numero',
        'complemento',
        'cidade',
        'bairro',
        'cep',
        'latitude',
        'longitude',
        'tipo_imovel',
        'tipo_servico',
        'node',
        'area_despacho',
        'observacao',
        'grupo',
        'data_agendamento',
        'status',
        'resposta',
        'status_id',
        'criado_por',
        'setor'
    ];


    public function statusOrder()
    {
        return $this->belongsTo(StatusOrder::class, 'status_id')
            ->select('id', 'titulo', 'descricao', 'cor_indicativa', 'created_at');
    }


}
