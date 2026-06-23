<?php

namespace App\Livewire\Admin;

use App\Models\Package;
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

    public ?int $packageId = null;

    public function mount(): void
    {
        $this->paidAt = now()->format('Y-m-d');
        $this->paidToId = Auth::id();
    }

    public function updatedUserId(int $userId): void
    {
        $user = User::with('package')->find($userId);
        if ($user && $user->package) {
            $this->packageId = $user->package_id;
            $this->amount = number_format($user->package->price / 100, 2, '.', '');
        } else {
            $this->packageId = null;
            $this->amount = '';
        }
    }

    public function recordPayment(): void
    {
        $this->validate([
            'userId' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paidAt' => ['required', 'date'],
            'paidToId' => ['required', 'exists:users,id'],
            'packageId' => ['nullable', 'exists:packages,id'],
        ]);

        $amountInPence = (int) round(((float) $this->amount) * 100);

        Payment::create([
            'user_id' => $this->userId,
            'package_id' => $this->packageId,
            'stripe_invoice_id' => 'manual_'.strtolower(Str::random(24)),
            'stripe_payment_intent_id' => null,
            'amount' => $amountInPence,
            'currency' => 'GBP',
            'status' => 'paid',
            'period_start' => Carbon::parse($this->paidAt)->startOfDay(),
            'period_end' => Carbon::parse($this->paidAt)->addMonth()->endOfDay(),
            'paid_at' => Carbon::parse($this->paidAt),
            'paid_to_id' => $this->paidToId,
        ]);

        $this->reset(['userId', 'amount', 'packageId']);
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
    public function packages()
    {
        return Package::where('is_active', true)->orderBy('sort_order')->get();
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get();
    }

    #[Computed]
    public function payments()
    {
        return Payment::with(['user', 'package', 'paidTo'])
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.payments')
            ->layout('layouts.app');
    }
}
