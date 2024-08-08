<?php

namespace App\Mail\Portal\Helpers;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendQuality extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Novo plano de saúde - Unity Saúde',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'portal.mail.collaborators',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $files = [
            [
                'path' => storage_path('app\portal\Manual_Acesso_Rapido_Telemedicina_Unity_Saude.pdf'),
                'as' => 'Manual_Acesso_Rapido_Telemedicina.pdf',
                'mime' => 'application/pdf',
            ],
            [
                'path' => storage_path('app\portal\Manual_do_beneficiário_UNITY_SAÚDE.pdf'),
                'as' => 'Manual_do_beneficiário_UNITY_SAÚDE.pdf',
                'mime' => 'application/pdf',
            ],
            [
                'path' => storage_path('app\portal\Resumo_de_Rede.pdf'),
                'as' => 'Resumo_de_Rede.pdf',
                'mime' => 'application/pdf',
            ],
        ];

        $email = $this->view('portal.mail.collaborators');

        foreach ($files as $file) {
            $email->attach($file['path'], [
                'as' => $file['as'],
                'mime' => $file['mime'],
            ]);
        }

        return $email;
    }
}
