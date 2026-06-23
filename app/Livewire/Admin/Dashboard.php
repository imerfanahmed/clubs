<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Admin Dashboard')]
class Dashboard extends Component
{
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

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.app');
    }
}
