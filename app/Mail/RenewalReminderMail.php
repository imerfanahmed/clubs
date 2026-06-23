<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RenewalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Carbon $periodEnd,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Membership Renewal Is Due Soon',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.renewal-reminder',
        );
    }
}
