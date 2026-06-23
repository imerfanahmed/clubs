<?php

use App\Livewire\Admin\Expenses;
use App\Models\Expense;
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

test('non-admins cannot access admin expenses page', function () {
    $this->get(route('admin.expenses'))->assertRedirect(route('login'));

    $this->actingAs($this->member);

    $this->get(route('admin.expenses'))->assertForbidden();
});

test('admins can access admin expenses page', function () {
    $this->actingAs($this->admin);

    $this->get(route('admin.expenses'))
        ->assertOk()
        ->assertSeeLivewire(Expenses::class);
});

test('expenses component has correct initial values', function () {
    $this->actingAs($this->admin);

    Livewire::test(Expenses::class)
        ->assertSet('expenseDate', now()->format('Y-m-d'))
        ->assertSet('description', '')
        ->assertSet('amount', '')
        ->assertSet('category', '');
});

test('admin can record an expense successfully', function () {
    $this->actingAs($this->admin);

    Livewire::test(Expenses::class)
        ->set('description', 'Office rent')
        ->set('amount', '500.00')
        ->set('category', 'Rent')
        ->set('expenseDate', '2026-06-20')
        ->call('recordExpense')
        ->assertHasNoErrors()
        ->assertSet('description', '')
        ->assertSet('amount', '')
        ->assertSet('category', '')
        ->assertSet('expenseDate', now()->format('Y-m-d'));

    expect(Expense::count())->toBe(1);

    $expense = Expense::first();
    expect($expense->description)->toBe('Office rent');
    expect($expense->amount)->toBe(50000);
    expect($expense->category)->toBe('Rent');
    expect($expense->expense_date->format('Y-m-d'))->toBe('2026-06-20');
    expect($expense->created_by)->toBe($this->admin->id);
});

test('expense validation is enforced', function () {
    $this->actingAs($this->admin);

    Livewire::test(Expenses::class)
        ->set('description', '')
        ->set('amount', '')
        ->set('category', '')
        ->call('recordExpense')
        ->assertHasErrors(['description', 'amount']);
});

test('admin can delete an expense', function () {
    $this->actingAs($this->admin);

    $expense = Expense::create([
        'description' => 'Test expense',
        'amount' => 1000,
        'expense_date' => now(),
        'created_by' => $this->admin->id,
    ]);

    expect(Expense::count())->toBe(1);

    Livewire::test(Expenses::class)
        ->call('deleteExpense', $expense->id);

    expect(Expense::count())->toBe(0);
});
