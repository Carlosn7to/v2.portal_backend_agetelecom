<?php

namespace App\Routines\Portal\Users;

use App\Helpers\Portal\Mail\Notification\Builder;
use App\Models\Portal\User\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class UserSync
{
    // Relatório de estatísticas da sincronização
    private array $report = [
        'users_created' => 0,
        'users_not_created' => 0,
        'users_updated' => 0,
        'users_not_updated' => 0
    ];

    // Método invocável para iniciar a sincronização
    public function __invoke() : void
    {
        $this->builder();
    }

    // Método responsável pela sincronização dos usuários
    public function builder() : void
    {
        // Definir limite de tempo máximo para a execução da rotina (20 minutos)
        set_time_limit((60*20));

        // Obter usuários do LDAP
        $ldapUsers = (new \App\Ldap\Models\Ldap\User())->paginate(200000)
            ->map(function ($item) {
                // Mapear os atributos dos usuários do LDAP para um formato mais conveniente
                return [
                    'email' => $item->mail !== null ? $item->mail[0] : "",
                    'login' => $item->samaccountname !== null ? $item->samaccountname[0] : "",
                    'name' => $item->displayname !== null ? $item->displayname[0] : "",
                ];
            })->toArray();

        foreach ($ldapUsers as $key => $value) {
            // Verificar se o usuário já existe no banco de dados
            $existingUser = User::where('login', $value['login'])->first();

            if ($existingUser) {
                // Comparar os valores existentes com os valores do LDAP
                if ($existingUser->nome != $value['name'] || $existingUser->email != $value['email']) {
                    // Atualizar as informações do usuário no banco de dados
                    $existingUser->update([
                        'nome' => $value['name'],
                        'email' => $value['email'],
                        'modificado_por' => 7,
                    ]);

                    $this->report['users_updated']++;
                } else {
                    // Incrementar o contador de usuários não atualizados
                    $this->report['users_not_updated']++;
                }
            } else {
                // Se o usuário não existir, criá-lo
                $userCreated = User::create([
                    'nome' => $value['name'],
                    'login' => $value['login'],
                    'email' => $value['email'],
                    'password' => Hash::make("hW*nN'v_*Pl8T8$36|L_LC!!I3}VC)f6:\9Jw"),
                    'criado_por' => 7,
                    'modificado_por' => 7,
                ]);

                if ($userCreated) {
                    // Incrementar o contador de usuários criados com sucesso
                    $this->report['users_created']++;
                } else {
                    // Incrementar o contador de falha na criação de usuários
                    $this->report['users_not_created']++;
                }
            }
        }

        // Enviar o relatório de estatísticas da sincronização
        $this->sendingReport();
    }

    // Método responsável por enviar o relatório de estatísticas da sincronização
    public function sendingReport() : void
    {


        $dataView = [
            'header' => [
                'title' => 'informativo', // Título da imagem do e-mail
                'subTitle' => 'Usuários sincronizados Portal/AD' // Subtítulo da imagem do e-mail
            ],
            'messageMail' => 'Segue a listagem do resultado.', // Mensagem do corpo do e-mail

            // Tabela de informações do e-mail, caso não seja necessário mantenha a TableVisible como false
            'table' => [
                'titles' => ['Campo', 'Quantidade'], // Separar por vírgula os títulos das colunas
                'data' => [
                    [
                        'Usuários sincronizados',
                        $this->report['users_created']
                    ],
                    [
                        'Usuários não sincronizados',
                        $this->report['users_not_created']
                    ],
                    [
                        'Usuários atualizados',
                        $this->report['users_updated']
                    ],
                    [
                        'Usuários não atualizados',
                        $this->report['users_not_updated']
                    ]

                ], // Array multidimensional com os dados da tabela (Exemplo: ['coluna1' => 'valor1', 'coluna2' => 'valor2'])
            ],
            'tableVisible' => true
        ];

        $recipients = ['carlos.neto@agetelecom.com.br'];

        $builder = new Builder($dataView, $recipients, '[INFORMATIVO] - Relatório de usuários sincronizados', []);



    }


}

