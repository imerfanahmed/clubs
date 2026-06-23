<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $applicant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Member Registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.member-registered',
        );
    }
}
