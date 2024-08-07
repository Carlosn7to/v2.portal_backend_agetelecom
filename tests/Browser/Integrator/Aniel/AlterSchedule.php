<?php

namespace Tests\Browser\Integrator\Aniel;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AlterSchedule extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function clearTechnical(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://cliente01.sinapseinformatica.com.br:4383/AGE/Web/Aniel.Connect/?IdAcesso=18560#')
                ->type('#UserName', '9999')
                ->type('#Password', 'Age2023#')
                ->click('.login100-form-btn')
                ->visit('https://cliente01.sinapseinformatica.com.br:4383/AGE/Web/Aniel.Connect/pt-BR/Acompanhamento_Servico?CodCt=OP01&Projeto=CASA%20CLIENTE&Num_Obra=1186968&Num_Doc=1186968#!')
                ->click('#tecnico-tab')
                ->waitFor('iframe[src="/AGE/Web/Aniel.Connect/pt-BR/Acompanhamento_Servico/Tecnico?Cod_Ct=OP01&Projeto=CASA CLIENTE&Num_Obra=1186968&Num_Doc=1186968"]', 10) // Aguarda o `iframe` estar presente.
                ->withinFrame('iframe[src="/AGE/Web/Aniel.Connect/pt-BR/Acompanhamento_Servico/Tecnico?Cod_Ct=OP01&Projeto=CASA CLIENTE&Num_Obra=1186968&Num_Doc=1186968"]', function (Browser $browser) {
                    $browser->waitFor('.card')
                        ->waitFor('#accordion')
                        ->waitFor('.card-body')
                        ->assertSee('Trocar Responsável da OS')
                        ->clickAtXPath('/html/body/form/div[1]/div/div[1]/div/div/div/div/div[2]/a');
                        })
                ->click('.float-save')
                ->pause(1000);

        });
    }
}
