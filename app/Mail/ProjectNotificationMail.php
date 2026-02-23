<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $msgContent;

    public $subjectLine;

    /**
     * Create a new message instance.
     */
    public function __construct($msgContent, $subjectLine = 'Project System Notification')
    {
        $this->msgContent = $msgContent;
        $this->subjectLine = $subjectLine;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Project Notification Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        // Pake text() biar formatnya ngikutin \n dari WA tanpa harus bikin file blade view lagi
        return $this->subject($this->subjectLine)
            ->text('emails.plain_text')
            ->with(['msgContent' => $this->msgContent]);
    }
}
