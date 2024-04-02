<?php

namespace App\Mail\Portal\AgeCommunicate\Rule\Billing;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendBilling extends Mailable
{
    use Queueable, SerializesModels;

    private $client;
    private $viewMail;
    private $subjectMail;

    /**
     * Create a new message instance.
     */
    public function __construct($view, $subject, $client, $billetPath = [])
    {
        $this->viewMail = $view;
        $this->subjectMail = $subject;
        $this->client = $client;
        $this->billetPath = $billetPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectMail,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'portal.mail.ageCommunicate.rule.billing.'.$this->viewMail,
            with: [
                'client' => $this->client,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [$this->billetPath ? $this->billetPath : []];
    }
}
