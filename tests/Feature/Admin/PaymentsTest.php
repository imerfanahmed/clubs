<?php

use App\Livewire\Admin\Payments as AdminPayments;
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
        ->assertSet('userId', null)
        ->assertSet('reason', '')
        ->assertSet('reasonDescription', '');
});

test('admin can record manual payment successfully', function () {
    $this->actingAs($this->admin);

    $anotherMember = User::factory()->create();
    $anotherMember->assignRole('member');

    Livewire::test(AdminPayments::class)
        ->set('userId', $this->member->id)
        ->set('amount', '35.50')
        ->set('reason', 'Membership Fee')
        ->set('paidAt', '2026-06-20')
        ->set('paidToId', $anotherMember->id)
        ->call('recordPayment')
        ->assertHasNoErrors()
        ->assertSet('userId', null)
        ->assertSet('amount', '')
        ->assertSet('reason', '')
        ->assertSet('reasonDescription', '')
        ->assertSet('paidAt', now()->format('Y-m-d'))
        ->assertSet('paidToId', $this->admin->id);

    expect(Payment::count())->toBe(1);

    $payment = Payment::first();
    expect($payment->user_id)->toBe($this->member->id);
    expect($payment->amount)->toBe(3550);
    expect($payment->status)->toBe('paid');
    expect($payment->reason)->toBe('Membership Fee');
    expect($payment->reason_description)->toBeNull();
    expect($payment->stripe_invoice_id)->toStartWith('manual_');
    expect($payment->paid_at->format('Y-m-d'))->toBe('2026-06-20');
    expect($payment->paid_to_id)->toBe($anotherMember->id);
});

test('admin can record payment with other reason and description', function () {
    $this->actingAs($this->admin);

    Livewire::test(AdminPayments::class)
        ->set('userId', $this->member->id)
        ->set('amount', '15.00')
        ->set('reason', 'Other')
        ->set('reasonDescription', 'Fundraising contribution')
        ->set('paidAt', '2026-06-15')
        ->call('recordPayment')
        ->assertHasNoErrors();

    $payment = Payment::first();
    expect($payment->reason)->toBe('Other');
    expect($payment->reason_description)->toBe('Fundraising contribution');
});
