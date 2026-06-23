<?php

use App\Livewire\RegisterMember;
use App\Models\Package;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    (new RolePermissionSeeder)->run();

    $this->package = Package::create([
        'name' => 'Student',
        'slug' => 'student',
        'description' => 'Discounted membership for students',
        'price' => 1500,
        'interval' => 'month',
        'is_active' => true,
        'sort_order' => 1,
    ]);
});

test('registration flow does not create user until card details are confirmed', function () {
    // 1. Initial State
    $component = Livewire::test(RegisterMember::class)
        ->assertSet('step', 1);

    // 2. Step 1: Personal details validation and submission
    $component->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('phone', '+447123456789')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('submitPersonal')
        ->assertSet('step', 2)
        ->assertHasNoErrors();

    // 3. Step 2: Address validation and submission
    $component->set('line_1', '123 Test Street')
        ->set('city', 'London')
        ->set('postcode', 'SW1A 1AA')
        ->set('country', 'GB')
        ->call('submitAddress')
        ->assertSet('step', 3)
        ->assertHasNoErrors();

    // 4. Step 3: Package validation and submission (generates SetupIntent client_secret)
    $component->set('package_id', $this->package->id)
        ->call('submitPackage')
        ->assertSet('step', 4)
        ->assertHasNoErrors();

    // Verify that NO user is created in the database yet
    expect(User::count())->toBe(0);
    expect($component->get('clientSecret'))->not->toBeEmpty();

    // 5. Step 4: Card details confirm payment
    $component->set('paymentMethodId', 'pm_card_visa')
        ->call('confirmPayment')
        ->assertSet('step', 5)
        ->assertHasNoErrors();

    // Verify that the user, address, role, and stripe customer are created now
    expect(User::count())->toBe(1);

    $user = User::first();
    expect($user->name)->toBe('John Doe');
    expect($user->email)->toBe('john@example.com');
    expect($user->status)->toBe('pending');
    expect($user->package_id)->toBe($this->package->id);
    expect($user->hasRole('member'))->toBeTrue();

    // Verify address is created
    expect($user->address)->not->toBeNull();
    expect($user->address->line_1)->toBe('123 Test Street');
    expect($user->address->city)->toBe('London');
    expect($user->address->postcode)->toBe('SW1A 1AA');

    // Verify Stripe customer and default payment method are set
    expect($user->stripe_id)->not->toBeNull();
    expect($user->pm_type)->not->toBeNull();
    expect($user->pm_last_four)->not->toBeNull();

    // Verify user is authenticated
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->id)->toBe($user->id);
});

test('registration validations are enforced at confirmPayment step', function () {
    // 1. Enter valid details in step 1, 2, 3
    $component = Livewire::test(RegisterMember::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('phone', '+447123456789')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('submitPersonal')
        ->set('line_1', '123 Test Street')
        ->set('city', 'London')
        ->set('postcode', 'SW1A 1AA')
        ->set('country', 'GB')
        ->call('submitAddress')
        ->set('package_id', $this->package->id)
        ->call('submitPackage');

    // 2. Change email to duplicate right before confirming payment
    User::factory()->create(['email' => 'jane@example.com']);

    $component->set('paymentMethodId', 'pm_card_visa')
        ->call('confirmPayment')
        ->assertHasErrors(['email']);

    // Verify no new user is created (only the pre-existing one from factory)
    expect(User::count())->toBe(1);
});

test('registration works with skip card details option', function () {
    $component = Livewire::test(RegisterMember::class)
        ->set('name', 'Bob Smith')
        ->set('email', 'bob@example.com')
        ->set('phone', '+447123456789')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('submitPersonal')
        ->set('line_1', '456 Test Avenue')
        ->set('city', 'Manchester')
        ->set('postcode', 'M1 1AA')
        ->set('country', 'GB')
        ->call('submitAddress')
        ->set('package_id', $this->package->id)
        ->call('submitPackage')
        ->assertSet('step', 4);

    // Enable skip card details
    $component->set('skipCardDetails', true)
        ->assertSet('skipCardDetails', true);

    // Submit without paymentMethodId
    $component->call('confirmPayment')
        ->assertSet('step', 5)
        ->assertHasNoErrors();

    expect(User::count())->toBe(1);

    $user = User::first();
    expect($user->name)->toBe('Bob Smith');
    expect($user->email)->toBe('bob@example.com');
    expect($user->status)->toBe('pending');
    expect($user->hasRole('member'))->toBeTrue();
    expect($user->address)->not->toBeNull();

    // Verify Stripe customer was NOT created
    expect($user->stripe_id)->toBeNull();

    // Verify user is authenticated
    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->id)->toBe($user->id);
});
