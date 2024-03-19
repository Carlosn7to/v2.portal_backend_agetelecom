<?php

namespace App\Mail\Portal\Helpers\Notification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendNotification extends Mailable
{
    use Queueable, SerializesModels;

    private $subjectMail;

    /**
     * Create a new message in\stance.
     */
    public function __construct($subjectMail, $dataView, $filePath = null)
    {
        $this->subjectMail = $subjectMail;
        $this->dataView = $dataView;
        $this->filePath = $filePath;
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
            view: 'portal.mail.notification.sending',
            with: ['data' => $this->dataView]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return $this->filePath ? $this->filePath : [];
    }
}
