<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Payments')]
class Payments extends Component
{
    public ?int $userId = null;

    public string $amount = '';

    public string $paidAt = '';

    public ?int $paidToId = null;

    public string $reason = '';

    public string $reasonDescription = '';

    public function mount(): void
    {
        $this->paidAt = now()->format('Y-m-d');
        $this->paidToId = Auth::id();
    }

    public function recordPayment(): void
    {
        $this->validate([
            'userId' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paidAt' => ['required', 'date'],
            'paidToId' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:255'],
            'reasonDescription' => ['nullable', 'string', 'max:1000'],
        ]);

        $amountInPence = (int) round(((float) $this->amount) * 100);

        Payment::create([
            'user_id' => $this->userId,
            'stripe_invoice_id' => 'manual_'.strtolower(Str::random(24)),
            'stripe_payment_intent_id' => null,
            'amount' => $amountInPence,
            'currency' => 'GBP',
            'status' => 'paid',
            'reason' => $this->reason,
            'reason_description' => $this->reason === 'Other' ? $this->reasonDescription : null,
            'period_start' => Carbon::parse($this->paidAt)->startOfDay(),
            'period_end' => Carbon::parse($this->paidAt)->addMonth()->endOfDay(),
            'paid_at' => Carbon::parse($this->paidAt),
            'paid_to_id' => $this->paidToId,
        ]);

        $this->reset(['userId', 'amount', 'reason', 'reasonDescription']);
        $this->paidAt = now()->format('Y-m-d');
        $this->paidToId = Auth::id();

        Flux::toast(variant: 'success', text: 'Manual payment recorded successfully.');
    }

    #[Computed]
    public function members()
    {
        return User::role('member')->orderBy('name')->get();
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get();
    }

    #[Computed]
    public function payments()
    {
        return Payment::with(['user', 'paidTo'])
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.payments')
            ->layout('layouts.app');
    }
}
