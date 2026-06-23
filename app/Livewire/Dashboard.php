<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public function cancelSubscription(): void
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();

        if (! $subscription) {
            return;
        }

        $subscription->cancel();

        $user->update(['status' => 'cancelled']);

        Flux::toast(variant: 'success', text: 'Subscription cancelled.');
    }

    #[Computed]
    public function collectedThisMonth()
    {
        return Payment::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');
    }

    #[Computed]
    public function cumulativeTotal()
    {
        return Payment::where('status', 'paid')->sum('amount');
    }

    #[Computed]
    public function totalExpensesThisMonth()
    {
        return Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
    }

    #[Computed]
    public function netThisMonth()
    {
        return $this->collectedThisMonth - $this->totalExpensesThisMonth;
    }

    #[Computed]
    public function pendingCount()
    {
        return User::pending()->count();
    }

    #[Computed]
    public function activeCount()
    {
        return User::active()->count();
    }

    #[Computed]
    public function suspendedCount()
    {
        return User::where('status', 'suspended')->count();
    }

    #[Computed]
    public function mrr()
    {
        return User::active()
            ->with('package')
            ->get()
            ->sum(fn ($user) => $user->package?->price ?? 0);
    }

    #[Computed]
    public function unpaidCount()
    {
        return User::active()
            ->whereDoesntHave('payments', function ($q) {
                $q->where('status', 'paid')
                    ->whereMonth('period_start', now()->month)
                    ->whereYear('period_start', now()->year);
            })
            ->count();
    }

    #[Computed]
    public function perUserContributions()
    {
        return User::whereHas('payments', function ($q) {
            $q->where('status', 'paid');
        })
            ->withSum(['payments as total_paid' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->orderByDesc('total_paid')
            ->get();
    }

    #[Computed]
    public function monthlyIncome(): array
    {
        $months = [];
        $totals = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $label = $date->format('M Y');
            $total = Payment::where('status', 'paid')
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('amount');

            $months[] = $label;
            $totals[] = $total / 100;
        }

        return ['labels' => $months, 'totals' => $totals];
    }

    #[Computed]
    public function monthlyExpenses(): array
    {
        $months = [];
        $totals = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $label = $date->format('M Y');
            $total = Expense::whereMonth('expense_date', $date->month)
                ->whereYear('expense_date', $date->year)
                ->sum('amount');

            $months[] = $label;
            $totals[] = $total / 100;
        }

        return ['labels' => $months, 'totals' => $totals];
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app');
    }
}
