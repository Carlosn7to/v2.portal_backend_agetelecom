<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms;

use App\Models\Portal\AgeCommunicate\BillingRule\Templates\Sms;

class TemplatesSms
{
    private $templates;
    private $template;

    public function getTemplate()
    {

    }

    public function templates()
    {
        $result = Sms::get(['titulo', 'conteudo', 'regra', 'status']);


        foreach($result as $template)
        {
            dd(json_decode($template->regra));
        }



    }

}
