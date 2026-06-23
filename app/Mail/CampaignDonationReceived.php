<?php

namespace App\Mail;

use App\Models\CampaignDonation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignDonationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CampaignDonation $donation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank You for Your Contribution',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign-donation-received',
        );
    }
}
