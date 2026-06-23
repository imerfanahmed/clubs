<?php

use App\Listeners\StripeWebhookHandler;
use App\Mail\CampaignDonationReceived;
use App\Models\Campaign;
use App\Models\CampaignDonation;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;

beforeEach(function () {
    Mail::fake();
});

function checkoutCompletedPayload(int $donationId, string $paymentStatus = 'paid'): array
{
    return [
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_123',
                'payment_status' => $paymentStatus,
                'payment_intent' => 'pi_test_123',
                'metadata' => ['donation_id' => $donationId],
            ],
        ],
    ];
}

test('a completed checkout session marks the card donation as completed', function () {
    $campaign = Campaign::factory()->active()->create(['goal_amount' => 100000]);
    $donation = CampaignDonation::factory()->for($campaign)->card()->create([
        'amount' => 10000,
        'status' => CampaignDonation::STATUS_PENDING,
        'stripe_session_id' => 'cs_test_123',
    ]);

    (new StripeWebhookHandler)->handle(new WebhookReceived(checkoutCompletedPayload($donation->id)));

    $donation->refresh();
    expect($donation->status)->toBe(CampaignDonation::STATUS_COMPLETED);
    expect($donation->stripe_payment_intent_id)->toBe('pi_test_123');
    expect($donation->paid_at)->not->toBeNull();
    expect($campaign->fresh()->raisedAmount())->toBe(10000);

    Mail::assertQueued(CampaignDonationReceived::class);
});

test('an unpaid checkout session does not complete the donation', function () {
    $campaign = Campaign::factory()->active()->create();
    $donation = CampaignDonation::factory()->for($campaign)->card()->create([
        'amount' => 10000,
        'status' => CampaignDonation::STATUS_PENDING,
    ]);

    (new StripeWebhookHandler)->handle(new WebhookReceived(checkoutCompletedPayload($donation->id, 'unpaid')));

    expect($donation->fresh()->status)->toBe(CampaignDonation::STATUS_PENDING);
});

test('the webhook is idempotent when replayed', function () {
    $campaign = Campaign::factory()->active()->create();
    $donation = CampaignDonation::factory()->for($campaign)->card()->create([
        'amount' => 10000,
        'status' => CampaignDonation::STATUS_PENDING,
    ]);

    $handler = new StripeWebhookHandler;
    $handler->handle(new WebhookReceived(checkoutCompletedPayload($donation->id)));
    $handler->handle(new WebhookReceived(checkoutCompletedPayload($donation->id)));

    expect(CampaignDonation::where('status', CampaignDonation::STATUS_COMPLETED)->count())->toBe(1);
    Mail::assertQueued(CampaignDonationReceived::class, 1);
});

test('a checkout session with an unknown donation id is ignored', function () {
    (new StripeWebhookHandler)->handle(new WebhookReceived(checkoutCompletedPayload(999999)));

    expect(CampaignDonation::count())->toBe(0);
});
