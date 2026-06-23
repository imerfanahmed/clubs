<?php

namespace App\Mail;

use App\Models\CampaignDonation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignDonationConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CampaignDonation $donation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Contribution Has Been Confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign-donation-confirmed',
        );
    }
}
