<?php

namespace App\Services;

use App\Mail\CampaignDonationReceived;
use App\Models\CampaignDonation;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;

class CampaignCheckoutService
{
    /**
     * Create a one-off Stripe Checkout session for a money donation and
     * return the URL the donor should be redirected to.
     */
    public function createSession(CampaignDonation $donation): string
    {
        $campaign = $donation->campaign;

        $params = [
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($donation->currency),
                    'unit_amount' => $donation->amount,
                    'product_data' => [
                        'name' => 'Donation: '.$campaign->title,
                    ],
                ],
            ]],
            'metadata' => [
                'donation_id' => $donation->id,
                'campaign_id' => $campaign->id,
            ],
            'success_url' => route('campaigns.show', $campaign).'?donation=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('campaigns.show', $campaign).'?donation=cancelled',
        ];

        if ($donation->user?->stripe_id) {
            $params['customer'] = $donation->user->stripe_id;
        } elseif ($donation->recipientEmail()) {
            $params['customer_email'] = $donation->recipientEmail();
        }

        $session = Cashier::stripe()->checkout->sessions->create($params);

        $donation->update(['stripe_session_id' => $session->id]);

        return $session->url;
    }

    /**
     * Confirm a card donation when the donor is redirected back from Stripe
     * Checkout. Verifies the session was paid and marks the donation complete.
     */
    public function completeFromSession(string $sessionId): ?CampaignDonation
    {
        $donation = CampaignDonation::where('stripe_session_id', $sessionId)->first();

        if (! $donation) {
            return null;
        }

        if ($donation->status === CampaignDonation::STATUS_COMPLETED) {
            return $donation;
        }

        $session = $this->retrieveSession($sessionId);

        if (! $session || ($session->payment_status ?? null) !== 'paid') {
            return $donation;
        }

        $this->markPaid($donation, $session->payment_intent ?? null);

        return $donation;
    }

    protected function retrieveSession(string $sessionId): ?object
    {
        return Cashier::stripe()->checkout->sessions->retrieve($sessionId);
    }

    protected function markPaid(CampaignDonation $donation, ?string $paymentIntentId): void
    {
        $donation->update([
            'status' => CampaignDonation::STATUS_COMPLETED,
            'stripe_payment_intent_id' => $paymentIntentId,
            'paid_at' => now(),
        ]);

        if ($donation->recipientEmail()) {
            Mail::to($donation->recipientEmail())->queue(new CampaignDonationReceived($donation));
        }
    }
}
