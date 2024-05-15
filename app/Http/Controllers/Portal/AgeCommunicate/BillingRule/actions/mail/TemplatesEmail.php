<?php

namespace App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\mail;

use App\Models\Portal\AgeCommunicate\BillingRule\Templates\Template;

class TemplatesEmail
{
    private $templates;
    private $template;

    public function __construct()
    {
        $this->getTemplates();
    }

    public function getTemplate($rankClient, $dayRule)
    {
        foreach($this->templates as $template){
            $rule = $template['rule']['categorias'][$rankClient];

            if(in_array($dayRule, $rule)){
                $this->template = [
                    'title' => $template['title'],
                    'content' => $template['content'],
                    'rule_specified' => [
                        'rank' => $rankClient,
                        'day' => $dayRule,
                    ],
                    'status' => $template['status'],
                    'integrator' => $template['integrator']
                ];
                break;
            }

            $this->template = null;
        }

        return $this->template;


    }

    public function getTemplates()
    {
        $this->templates = Template::getAllTemplates();

        return $this->templates->where('channel', 'Email');
    }

}
