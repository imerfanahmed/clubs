<?php

use App\Livewire\Admin\CreateMember;
use App\Models\Package;
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
});

test('non-admins cannot access create member page', function () {
    $this->get(route('admin.members.create'))->assertRedirect(route('login'));

    $this->actingAs($this->member);

    $this->get(route('admin.members.create'))->assertForbidden();
});

test('admins can access create member page', function () {
    $this->actingAs($this->admin);

    $this->get(route('admin.members.create'))
        ->assertOk()
        ->assertSeeLivewire(CreateMember::class);
});

test('admin can create a member successfully', function () {
    $this->actingAs($this->admin);

    Livewire::test(CreateMember::class)
        ->set('name', 'New Member')
        ->set('email', 'new@example.com')
        ->set('phone', '+447123456789')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('package_id', $this->package->id)
        ->set('line_1', '123 Test Street')
        ->set('city', 'London')
        ->set('postcode', 'SW1A 1AA')
        ->set('country', 'GB')
        ->call('create')
        ->assertHasNoErrors();

    expect(User::count())->toBe(3);

    $user = User::where('email', 'new@example.com')->first();
    expect($user->name)->toBe('New Member');
    expect($user->status)->toBe('active');
    expect($user->package_id)->toBe($this->package->id);
    expect($user->hasRole('member'))->toBeTrue();
    expect($user->address)->not->toBeNull();
    expect($user->address->line_1)->toBe('123 Test Street');
    expect($user->address->postcode)->toBe('SW1A 1AA');
});

test('create member validation is enforced', function () {
    $this->actingAs($this->admin);

    Livewire::test(CreateMember::class)
        ->set('name', '')
        ->set('email', '')
        ->set('phone', '')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->set('country', '')
        ->call('create')
        ->assertHasErrors([
            'name', 'email', 'phone', 'password',
            'package_id', 'line_1', 'city', 'postcode', 'country',
        ]);
});

test('create member requires unique email', function () {
    $this->actingAs($this->admin);

    Livewire::test(CreateMember::class)
        ->set('name', 'Duplicate Email')
        ->set('email', $this->member->email)
        ->set('phone', '+447123456789')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('package_id', $this->package->id)
        ->set('line_1', '123 Test Street')
        ->set('city', 'London')
        ->set('postcode', 'SW1A 1AA')
        ->set('country', 'GB')
        ->call('create')
        ->assertHasErrors(['email']);
});
