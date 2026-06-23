<?php

use App\Livewire\Admin\Campaigns;
use App\Livewire\Admin\ManageCampaign;
use App\Mail\CampaignDonationConfirmed;
use App\Models\Campaign;
use App\Models\CampaignDonation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    (new RolePermissionSeeder)->run();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->member = User::factory()->create();
    $this->member->assignRole('member');
});

test('non-admins cannot access admin campaigns page', function () {
    $this->get(route('admin.campaigns'))->assertRedirect(route('login'));

    $this->actingAs($this->member);
    $this->get(route('admin.campaigns'))->assertForbidden();
});

test('admin can create a campaign with auto slug and pence goal', function () {
    $this->actingAs($this->admin);

    Livewire::test(Campaigns::class)
        ->set('title', 'New Wudu Area')
        ->set('summary', 'Help rebuild')
        ->set('description', 'A longer story')
        ->set('goalAmount', '15000.00')
        ->call('createCampaign')
        ->assertHasNoErrors();

    $campaign = Campaign::first();
    expect($campaign->title)->toBe('New Wudu Area');
    expect($campaign->slug)->toBe('new-wudu-area');
    expect($campaign->goal_amount)->toBe(1500000);
    expect($campaign->status)->toBe(Campaign::STATUS_DRAFT);
    expect($campaign->created_by)->toBe($this->admin->id);
});

test('slugs are unique across campaigns with the same title', function () {
    $this->actingAs($this->admin);

    Campaign::create(['title' => 'Roof Repair', 'goal_amount' => 1000]);
    Campaign::create(['title' => 'Roof Repair', 'goal_amount' => 1000]);

    expect(Campaign::pluck('slug')->all())->toBe(['roof-repair', 'roof-repair-1']);
});

test('admin can activate and close a campaign', function () {
    $this->actingAs($this->admin);
    $campaign = Campaign::factory()->create();

    Livewire::test(Campaigns::class)
        ->call('setStatus', $campaign->id, Campaign::STATUS_ACTIVE);
    expect($campaign->fresh()->status)->toBe(Campaign::STATUS_ACTIVE);

    Livewire::test(Campaigns::class)
        ->call('setStatus', $campaign->id, Campaign::STATUS_CLOSED);
    expect($campaign->fresh()->status)->toBe(Campaign::STATUS_CLOSED);
});

test('admin can add and remove pledge items', function () {
    $this->actingAs($this->admin);
    $campaign = Campaign::factory()->create();

    $component = Livewire::test(ManageCampaign::class, ['campaign' => $campaign])
        ->set('pledgeName', 'Bricks')
        ->set('pledgeUnit', 'bricks')
        ->set('pledgeTarget', '5000')
        ->call('addPledgeItem')
        ->assertHasNoErrors();

    expect($campaign->pledgeItems()->count())->toBe(1);

    $item = $campaign->pledgeItems()->first();
    $component->call('deletePledgeItem', $item->id);

    expect($campaign->pledgeItems()->count())->toBe(0);
});

test('approving a pending offline donation makes it count and emails the donor', function () {
    Mail::fake();
    $this->actingAs($this->admin);

    $campaign = Campaign::factory()->active()->create(['goal_amount' => 100000]);
    $donation = CampaignDonation::factory()->for($campaign)->create([
        'type' => CampaignDonation::TYPE_MONEY,
        'amount' => 5000,
        'payment_method' => CampaignDonation::METHOD_OFFLINE,
        'status' => CampaignDonation::STATUS_PENDING,
    ]);

    expect($campaign->raisedAmount())->toBe(0);

    Livewire::test(ManageCampaign::class, ['campaign' => $campaign])
        ->call('approveDonation', $donation->id);

    $donation->refresh();
    expect($donation->status)->toBe(CampaignDonation::STATUS_COMPLETED);
    expect($donation->approved_by)->toBe($this->admin->id);
    expect($campaign->fresh()->raisedAmount())->toBe(5000);

    Mail::assertQueued(CampaignDonationConfirmed::class);
});

test('rejecting a pending donation does not count it', function () {
    $this->actingAs($this->admin);

    $campaign = Campaign::factory()->active()->create();
    $donation = CampaignDonation::factory()->for($campaign)->create([
        'amount' => 5000,
        'status' => CampaignDonation::STATUS_PENDING,
    ]);

    Livewire::test(ManageCampaign::class, ['campaign' => $campaign])
        ->call('rejectDonation', $donation->id);

    expect($donation->fresh()->status)->toBe(CampaignDonation::STATUS_REJECTED);
    expect($campaign->fresh()->raisedAmount())->toBe(0);
});

test('approving a pledge donation increments the item achieved quantity', function () {
    $this->actingAs($this->admin);

    $campaign = Campaign::factory()->active()->create();
    $item = $campaign->pledgeItems()->create(['name' => 'Bricks', 'unit' => 'bricks', 'target_quantity' => 5000]);

    $donation = CampaignDonation::factory()->for($campaign)->pledge()->create([
        'pledge_item_id' => $item->id,
        'pledge_quantity' => 200,
        'status' => CampaignDonation::STATUS_PENDING,
    ]);

    expect($item->achievedQuantity())->toBe(0);

    Livewire::test(ManageCampaign::class, ['campaign' => $campaign])
        ->call('approveDonation', $donation->id);

    expect($item->fresh()->achievedQuantity())->toBe(200);
});
