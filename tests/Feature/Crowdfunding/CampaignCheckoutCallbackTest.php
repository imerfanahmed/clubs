<?php

use App\Mail\CampaignDonationReceived;
use App\Models\Campaign;
use App\Models\CampaignDonation;
use App\Services\CampaignCheckoutService;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

/**
 * Build a partial mock of the checkout service whose Stripe `retrieveSession`
 * call is stubbed, so the completion logic can be tested without hitting Stripe.
 */
function fakeCheckoutService(?array $session): CampaignCheckoutService
{
    $mock = Mockery::mock(CampaignCheckoutService::class)->makePartial();
    $mock->shouldAllowMockingProtectedMethods()
        ->shouldReceive('retrieveSession')
        ->andReturn($session ? (object) $session : null);

    return $mock;
}

test('a paid checkout callback auto-completes the card donation', function () {
    $campaign = Campaign::factory()->active()->create(['goal_amount' => 100000]);
    $donation = CampaignDonation::factory()->for($campaign)->card()->create([
        'amount' => 10000,
        'status' => CampaignDonation::STATUS_PENDING,
        'stripe_session_id' => 'cs_test_123',
    ]);

    $service = fakeCheckoutService(['payment_status' => 'paid', 'payment_intent' => 'pi_test_123']);

    $service->completeFromSession('cs_test_123');

    $donation->refresh();
    expect($donation->status)->toBe(CampaignDonation::STATUS_COMPLETED);
    expect($donation->stripe_payment_intent_id)->toBe('pi_test_123');
    expect($donation->paid_at)->not->toBeNull();
    expect($campaign->fresh()->raisedAmount())->toBe(10000);

    Mail::assertQueued(CampaignDonationReceived::class);
});

test('an unpaid checkout callback leaves the donation pending', function () {
    $campaign = Campaign::factory()->active()->create();
    $donation = CampaignDonation::factory()->for($campaign)->card()->create([
        'status' => CampaignDonation::STATUS_PENDING,
        'stripe_session_id' => 'cs_test_123',
    ]);

    fakeCheckoutService(['payment_status' => 'unpaid'])->completeFromSession('cs_test_123');

    expect($donation->fresh()->status)->toBe(CampaignDonation::STATUS_PENDING);
    Mail::assertNothingQueued();
});

test('completing the callback twice is idempotent', function () {
    $campaign = Campaign::factory()->active()->create();
    $donation = CampaignDonation::factory()->for($campaign)->card()->create([
        'amount' => 10000,
        'status' => CampaignDonation::STATUS_PENDING,
        'stripe_session_id' => 'cs_test_123',
    ]);

    $service = fakeCheckoutService(['payment_status' => 'paid', 'payment_intent' => 'pi_test_123']);
    $service->completeFromSession('cs_test_123');
    $service->completeFromSession('cs_test_123');

    expect(CampaignDonation::where('status', CampaignDonation::STATUS_COMPLETED)->count())->toBe(1);
    Mail::assertQueued(CampaignDonationReceived::class, 1);
});

test('an unknown session id is ignored', function () {
    $result = fakeCheckoutService(['payment_status' => 'paid'])->completeFromSession('cs_missing');

    expect($result)->toBeNull();
});
