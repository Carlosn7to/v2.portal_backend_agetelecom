<?php

namespace App\Models\Portal\AgeCommunicate\BillingRule\Templates;

use App\Models\Portal\AgeCommunicate\BillingRule\Integrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'age_comunica_templates';
    protected $connection = 'portal';
    protected $fillable = [
        'titulo',
        'conteudo',
        'regra',
        'integrador_id',
        'status',
        'criado_por',
        'atualizado_por',
        'deletado_por',
    ];



    /**
     * Get the value of the "regra" attribute as an array.
     *
     * @param  string  $value
     * @return array|null
     */
    public function getRegraAttribute($value)
    {
        // Decodificar o valor da chave 'regra' para um array associativo
        return json_decode($value, true);
    }

    /**
     * Set the value of the "regra" attribute as JSON.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setRegraAttribute($value)
    {
        // Codificar o valor da chave 'regra' para JSON antes de definir o atributo
        $this->attributes['regra'] = json_encode($value);
    }

    public function getVariaveisAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setVariaveisAttribute($value)
    {
        $this->attributes['variaveis'] = json_encode($value);
    }

    public function integrator()
    {
        return $this->belongsTo(Integrator::class, 'integrador_id', 'id')
                    ->select('id', 'titulo', 'configuracao', 'status');
    }


    public static function getAllTemplates()
    {
        return static::with('integrator')->get()->map->getAttributesForBuilding();
    }


    public function getAttributesForBuilding()
    {

        $this->load('integrator');

        $integratorAttributes = $this->integrator ? $this->integrator->toArray() : [];


        $attributes = [
            'id_template' => $this->id,
            'title' => $this->titulo,
            'channel' => $this->canal,
            'template_integrator' => $this->template_integradora,
            'content' => $this->conteudo,
            'rule' => $this->regra,
            'status' => $this->status,
            'variables' => $this->variaveis,
            'integrator' => $integratorAttributes,
        ];

        return $attributes;


    }
}
