<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $firstName;

    public function __construct(string $otp, string $firstName)
    {
        $this->otp       = $otp;
        $this->firstName = $firstName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your UM Dining Center Login Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-otp',
        );
    }
}
