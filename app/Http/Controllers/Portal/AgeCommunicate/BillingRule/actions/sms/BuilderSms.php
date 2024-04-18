<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms;

class BuilderSms
{

    private $cellPhone;
    private $day;
    private $templates;

    public function __construct($cellPhone, $day)
    {
        $this->cellPhone = $cellPhone;
        $this->day = $day;
    }

    public function builder()
    {

        $this->sanitizeCellphone();
        $this->formatCellphone();

        $this->templates = (new TemplatesSms())->templates();


        $excel = [];

        foreach($this->templates as $template)
        {
            $excel[] = [
                'titulo' => $template->titulo,
                'conteudo' => $template->conteudo,
                'regra' => $template->regra,
                'status' => $template->status,
                'celular' => $this->cellPhone,
                'dia' => $this->day
            ];
        }

    }

    private function sanitizeCellphone() : void
    {
        $this->cellPhone = preg_replace('/[^0-9]/', '', $this->cellPhone);
    }

    private function formatCellphone() : void
    {

        //Verifica se o telefone é igual ou menos à 11 digitos , se for, adiciona o 55 no inicio pois indica
        // que não foi inserido o código do país
        $this->cellPhone = strlen($this->cellPhone) <= 11 ? '55' . $this->cellPhone : $this->cellPhone;

        // Verifica se o número já está com o 9 inserido no início, se não estiver, insere
        $this->cellPhone = strlen($this->cellPhone) == 12 ?
        substr_replace($this->cellPhone, '9', 4, 0) :
        $this->cellPhone;

    }


}
