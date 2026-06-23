<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Member Profile')]
class UserProfile extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->load(['address', 'package']);
    }

    #[Computed]
    public function totalPaid()
    {
        return $this->user->payments()
            ->where('status', 'paid')
            ->sum('amount');
    }

    #[Computed]
    public function lastPayment()
    {
        return $this->user->payments()
            ->where('status', 'paid')
            ->latest('paid_at')
            ->first();
    }

    #[Computed]
    public function isPaidThisMonth()
    {
        return $this->user->payments()
            ->where('status', 'paid')
            ->whereMonth('period_start', now()->month)
            ->whereYear('period_start', now()->year)
            ->exists();
    }

    public function render()
    {
        return view('livewire.admin.user-profile')
            ->layout('layouts.app');
    }
}
