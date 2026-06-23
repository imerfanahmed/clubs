<?php

namespace App\Livewire\Admin;

use App\Models\Expense;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Expenses')]
class Expenses extends Component
{
    public string $description = '';

    public string $amount = '';

    public string $category = '';

    public string $expenseDate = '';

    public function mount(): void
    {
        $this->expenseDate = now()->format('Y-m-d');
    }

    public function recordExpense(): void
    {
        $this->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category' => ['nullable', 'string', 'max:255'],
            'expenseDate' => ['required', 'date'],
        ]);

        $amountInPence = (int) round(((float) $this->amount) * 100);

        Expense::create([
            'description' => $this->description,
            'amount' => $amountInPence,
            'category' => $this->category ?: null,
            'expense_date' => $this->expenseDate,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['description', 'amount', 'category']);
        $this->expenseDate = now()->format('Y-m-d');

        Flux::toast(variant: 'success', text: 'Expense recorded successfully.');
    }

    public function deleteExpense(int $id): void
    {
        Expense::where('id', $id)->delete();

        Flux::toast(variant: 'success', text: 'Expense deleted.');
    }

    #[Computed]
    public function expenses()
    {
        return Expense::with('creator')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.expenses')
            ->layout('layouts.app');
    }
}
