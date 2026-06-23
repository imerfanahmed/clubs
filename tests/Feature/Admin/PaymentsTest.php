<?php

use App\Livewire\Admin\Payments as AdminPayments;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    (new RolePermissionSeeder)->run();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->member = User::factory()->create();
    $this->member->assignRole('member');

    $this->package = Package::create([
        'name' => 'Basic',
        'slug' => 'basic',
        'description' => 'Standard club membership',
        'price' => 2500,
        'interval' => 'month',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->member->update(['package_id' => $this->package->id]);
});

test('non-admins cannot access admin payments page', function () {
    $this->get(route('admin.payments'))->assertRedirect(route('login'));

    $user = User::factory()->create();
    $user->assignRole('member');
    $this->actingAs($user);

    $this->get(route('admin.payments'))->assertForbidden();
});

test('admins can access admin payments page', function () {
    $this->actingAs($this->admin);

    $this->get(route('admin.payments'))
        ->assertOk()
        ->assertSeeLivewire(AdminPayments::class);
});

test('payments component has correct initial values', function () {
    $this->actingAs($this->admin);

    Livewire::test(AdminPayments::class)
        ->assertSet('paidAt', now()->format('Y-m-d'))
        ->assertSet('paidToId', $this->admin->id)
        ->assertSet('amount', '')
        ->assertSet('userId', null);
});

test('selecting a member autofills package and price in component', function () {
    $this->actingAs($this->admin);

    Livewire::test(AdminPayments::class)
        ->set('userId', $this->member->id)
        ->assertSet('packageId', $this->package->id)
        ->assertSet('amount', '25.00');
});

test('admin can record manual payment successfully', function () {
    $this->actingAs($this->admin);

    $anotherMember = User::factory()->create();
    $anotherMember->assignRole('member');

    Livewire::test(AdminPayments::class)
        ->set('userId', $this->member->id)
        ->set('amount', '35.50')
        ->set('packageId', $this->package->id)
        ->set('paidAt', '2026-06-20')
        ->set('paidToId', $anotherMember->id) // can select other members/users
        ->call('recordPayment')
        ->assertHasNoErrors()
        ->assertSet('userId', null)
        ->assertSet('amount', '')
        ->assertSet('packageId', null)
        ->assertSet('paidAt', now()->format('Y-m-d'))
        ->assertSet('paidToId', $this->admin->id);

    // Verify payment was recorded in the database
    expect(Payment::count())->toBe(1);

    $payment = Payment::first();
    expect($payment->user_id)->toBe($this->member->id);
    expect($payment->package_id)->toBe($this->package->id);
    expect($payment->amount)->toBe(3550); // Converted to pence
    expect($payment->status)->toBe('paid');
    expect($payment->stripe_invoice_id)->toStartWith('manual_');
    expect($payment->paid_at->format('Y-m-d'))->toBe('2026-06-20');
    expect($payment->paid_to_id)->toBe($anotherMember->id);
});
