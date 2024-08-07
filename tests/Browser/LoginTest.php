<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function testExample(): void
    {
        $this->browse(function (Browser $browser) {

            $browser->visit('https://cliente01.sinapseinformatica.com.br:4383/AGE/Web/Aniel.Connect/?IdAcesso=18560#')
                ->type('#UserName', '28811')
                ->type('#Password', 'Jonas2023')
                ->waitFor('.table')
                ->clickAtXPath('/html/body/form/div/div/div[2]/div[4]/div/div/button');
//                ->waitFor('.welcome-text')
//                ->visit('https://ageportal.agetelecom.com.br/ageTools/home')
//                ->waitFor('.card')
//                ->clickAtXPath('//*[@id="content-page"]/div/div/div[1]/a')
//                ->waitFor('.not-data')
//                ->type('#name', 'Lucas Pereira Bispo')
//                ->keys('#name', '{enter}')

            // Captura do HTML da tabela
            $tabelaHTML = $browser->driver->executeScript('return document.querySelector(".table").outerHTML');
            preg_match_all('/<td[^>]*>(.*?)<\/td>/', $tabelaHTML, $matches);
            $itensPrimeiraLinha = isset($matches[1]) ? $matches[1] : [];

            $items = [];
            // Exibir os itens um abaixo do outro
            foreach ($itensPrimeiraLinha as $item) {
                $items[1][] = trim(strip_tags($item));
            }

            dump($items);

            $browser->pause(30000);

        });
    }

}
