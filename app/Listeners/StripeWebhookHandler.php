<?php

namespace App\Listeners;

use App\Mail\CampaignDonationReceived;
use App\Mail\PaymentFailedMail;
use App\Models\CampaignDonation;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;

class StripeWebhookHandler
{
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? '';

        match ($type) {
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($payload),
            'invoice.payment_failed' => $this->handlePaymentFailed($payload),
            'charge.refunded' => $this->handleChargeRefunded($payload),
            'checkout.session.completed' => $this->handleCheckoutCompleted($payload),
            default => null,
        };
    }

    protected function handleCheckoutCompleted(array $payload): void
    {
        $session = $payload['data']['object'];

        $donationId = $session['metadata']['donation_id'] ?? null;

        if (! $donationId || ($session['payment_status'] ?? null) !== 'paid') {
            return;
        }

        $donation = CampaignDonation::where('id', $donationId)
            ->where('status', CampaignDonation::STATUS_PENDING)
            ->first();

        if (! $donation) {
            return;
        }

        $donation->update([
            'status' => CampaignDonation::STATUS_COMPLETED,
            'stripe_payment_intent_id' => $session['payment_intent'] ?? null,
            'paid_at' => now(),
        ]);

        if ($donation->recipientEmail()) {
            Mail::to($donation->recipientEmail())->queue(new CampaignDonationReceived($donation));
        }
    }

    protected function handlePaymentSucceeded(array $payload): void
    {
        $invoice = $payload['data']['object'];

        $user = User::where('stripe_id', $invoice['customer'])->first();

        if (! $user) {
            return;
        }

        $packageId = null;
        $lines = $invoice['lines']['data'] ?? [];
        foreach ($lines as $line) {
            if (isset($line['price']['product'])) {
                $package = Package::where('stripe_product_id', $line['price']['product'])->first();
                $packageId = $package?->id;
                break;
            }
        }

        Payment::updateOrCreate(
            ['stripe_invoice_id' => $invoice['id']],
            [
                'user_id' => $user->id,
                'package_id' => $packageId,
                'stripe_payment_intent_id' => $invoice['payment_intent'] ?? null,
                'amount' => $invoice['total'],
                'currency' => strtoupper($invoice['currency']),
                'status' => 'paid',
                'period_start' => isset($invoice['period_start'])
                    ? Carbon::createFromTimestamp($invoice['period_start'])
                    : null,
                'period_end' => isset($invoice['period_end'])
                    ? Carbon::createFromTimestamp($invoice['period_end'])
                    : null,
                'paid_at' => now(),
            ]
        );
    }

    protected function handlePaymentFailed(array $payload): void
    {
        $invoice = $payload['data']['object'];

        $user = User::where('stripe_id', $invoice['customer'])->first();

        if (! $user) {
            return;
        }

        $packageId = null;
        $lines = $invoice['lines']['data'] ?? [];
        foreach ($lines as $line) {
            if (isset($line['price']['product'])) {
                $package = Package::where('stripe_product_id', $line['price']['product'])->first();
                $packageId = $package?->id;
                break;
            }
        }

        Payment::updateOrCreate(
            ['stripe_invoice_id' => $invoice['id']],
            [
                'user_id' => $user->id,
                'package_id' => $packageId,
                'stripe_payment_intent_id' => $invoice['payment_intent'] ?? null,
                'amount' => $invoice['total'],
                'currency' => strtoupper($invoice['currency']),
                'status' => 'failed',
                'period_start' => isset($invoice['period_start'])
                    ? Carbon::createFromTimestamp($invoice['period_start'])
                    : null,
                'period_end' => isset($invoice['period_end'])
                    ? Carbon::createFromTimestamp($invoice['period_end'])
                    : null,
            ]
        );

        Mail::to($user)->queue(new PaymentFailedMail($user, $invoice['total']));

        $failedCount = Payment::where('user_id', $user->id)
            ->where('status', 'failed')
            ->count();

        if ($failedCount >= 3) {
            $user->update(['status' => 'suspended']);
        }
    }

    protected function handleChargeRefunded(array $payload): void
    {
        $charge = $payload['data']['object'];
        $paymentIntentId = $charge['payment_intent'] ?? null;

        if (! $paymentIntentId) {
            return;
        }

        Payment::where('stripe_payment_intent_id', $paymentIntentId)
            ->where('status', 'paid')
            ->update(['status' => 'refunded']);
    }
}
