<?php

namespace App\Livewire;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Dashboard')]
class MemberDashboard extends Component
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

    public function render()
    {
        return view('livewire.member.dashboard')
            ->layout('layouts.app');
    }
}
