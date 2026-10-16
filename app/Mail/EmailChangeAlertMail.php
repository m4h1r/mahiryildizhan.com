<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $newEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hesabınızda E-posta Değişikliği İsteği / E-mail Change Requested',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.email-change-alert',
        );
    }
}
