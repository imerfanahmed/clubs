<?php

use App\Livewire\Campaigns\Show;
use App\Mail\CampaignDonationReceived;
use App\Models\Campaign;
use App\Models\CampaignDonation;
use App\Models\User;
use App\Services\CampaignCheckoutService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();
    Mail::fake();
});

test('the public campaign page renders for a guest', function () {
    $campaign = Campaign::factory()->active()->create(['title' => 'New Wudu Area']);

    $this->get(route('campaigns.show', $campaign))
        ->assertOk()
        ->assertSee('New Wudu Area')
        ->assertSeeLivewire(Show::class);
});

test('a guest can submit an offline money donation which stays pending', function () {
    $campaign = Campaign::factory()->active()->create();

    Livewire::test(Show::class, ['campaign' => $campaign])
        ->set('contributionType', CampaignDonation::TYPE_MONEY)
        ->set('amount', '50')
        ->set('paymentMethod', CampaignDonation::METHOD_OFFLINE)
        ->set('donorName', 'Abdul')
        ->set('donorEmail', 'abdul@example.com')
        ->call('donate')
        ->assertHasNoErrors()
        ->assertSet('justDonated', true);

    $donation = CampaignDonation::first();
    expect($donation->status)->toBe(CampaignDonation::STATUS_PENDING);
    expect($donation->amount)->toBe(5000);
    expect($donation->donor_name)->toBe('Abdul');
    expect($donation->user_id)->toBeNull();
    expect($campaign->fresh()->raisedAmount())->toBe(0);

    Mail::assertQueued(CampaignDonationReceived::class);
});

test('a guest can submit an item pledge', function () {
    $campaign = Campaign::factory()->active()->create();
    $item = $campaign->pledgeItems()->create(['name' => 'Bricks', 'unit' => 'bricks', 'target_quantity' => 5000]);

    Livewire::test(Show::class, ['campaign' => $campaign])
        ->set('contributionType', CampaignDonation::TYPE_PLEDGE)
        ->set('pledgeItemId', $item->id)
        ->set('pledgeQuantity', '200')
        ->set('donorName', 'Bilal')
        ->set('donorEmail', 'bilal@example.com')
        ->call('donate')
        ->assertHasNoErrors();

    $donation = CampaignDonation::first();
    expect($donation->type)->toBe(CampaignDonation::TYPE_PLEDGE);
    expect($donation->pledge_quantity)->toBe(200);
    expect($donation->status)->toBe(CampaignDonation::STATUS_PENDING);
});

test('a logged in member donation links to their record and skips guest fields', function () {
    $member = User::factory()->create(['name' => 'Yusuf', 'email' => 'yusuf@example.com']);
    $member->assignRole('member');
    $this->actingAs($member);

    $campaign = Campaign::factory()->active()->create();

    Livewire::test(Show::class, ['campaign' => $campaign])
        ->assertSet('isMember', true)
        ->assertSet('donorName', 'Yusuf')
        ->set('amount', '25')
        ->set('paymentMethod', CampaignDonation::METHOD_OFFLINE)
        ->call('donate')
        ->assertHasNoErrors();

    $donation = CampaignDonation::first();
    expect($donation->user_id)->toBe($member->id);
    expect($donation->donor_name)->toBeNull();
});

test('a card money donation redirects to the stripe checkout url', function () {
    $campaign = Campaign::factory()->active()->create();

    $this->mock(CampaignCheckoutService::class, function ($mock) {
        $mock->shouldReceive('createSession')->once()->andReturnUsing(function (CampaignDonation $donation) {
            $donation->update(['stripe_session_id' => 'cs_test_123']);

            return 'https://stripe.test/checkout/cs_test_123';
        });
    });

    Livewire::test(Show::class, ['campaign' => $campaign])
        ->set('amount', '100')
        ->set('paymentMethod', CampaignDonation::METHOD_CARD)
        ->set('donorName', 'Omar')
        ->set('donorEmail', 'omar@example.com')
        ->call('donate')
        ->assertRedirect('https://stripe.test/checkout/cs_test_123');

    $donation = CampaignDonation::first();
    expect($donation->payment_method)->toBe(CampaignDonation::METHOD_CARD);
    expect($donation->status)->toBe(CampaignDonation::STATUS_PENDING);
    expect($donation->stripe_session_id)->toBe('cs_test_123');
});

test('returning from stripe with a paid session auto-completes the donation', function () {
    $campaign = Campaign::factory()->active()->create(['goal_amount' => 100000]);
    $donation = CampaignDonation::factory()->for($campaign)->card()->create([
        'amount' => 10000,
        'status' => CampaignDonation::STATUS_PENDING,
        'stripe_session_id' => 'cs_test_123',
    ]);

    $service = Mockery::mock(CampaignCheckoutService::class)->makePartial();
    $service->shouldAllowMockingProtectedMethods()
        ->shouldReceive('retrieveSession')
        ->andReturn((object) ['payment_status' => 'paid', 'payment_intent' => 'pi_test_123']);
    $this->instance(CampaignCheckoutService::class, $service);

    $this->get(route('campaigns.show', $campaign).'?donation=success&session_id=cs_test_123')
        ->assertOk();

    expect($donation->fresh()->status)->toBe(CampaignDonation::STATUS_COMPLETED);
    expect($campaign->fresh()->raisedAmount())->toBe(10000);
});

test('donations are rejected when the campaign is not active', function () {
    $campaign = Campaign::factory()->create(['status' => Campaign::STATUS_DRAFT]);

    Livewire::test(Show::class, ['campaign' => $campaign])
        ->set('amount', '50')
        ->set('paymentMethod', CampaignDonation::METHOD_OFFLINE)
        ->set('donorName', 'Abdul')
        ->set('donorEmail', 'abdul@example.com')
        ->call('donate');

    expect(CampaignDonation::count())->toBe(0);
});

test('validation errors are shown for missing fields', function () {
    $campaign = Campaign::factory()->active()->create();

    Livewire::test(Show::class, ['campaign' => $campaign])
        ->set('amount', '')
        ->set('donorName', '')
        ->set('donorEmail', '')
        ->call('donate')
        ->assertHasErrors(['amount', 'donorName', 'donorEmail']);
});
