<?php

namespace App\Livewire\Admin;

use App\Actions\DeactivateMemberAction;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Members')]
class Members extends Component
{
    public string $search = '';

    public string $statusFilter = '';

    public ?int $packageFilter = null;

    public function deactivate(int $userId, DeactivateMemberAction $action): void
    {
        $user = User::findOrFail($userId);
        $action->execute($user);

        Flux::toast(variant: 'success', text: "{$user->name} deactivated.");
    }

    public function reactivate(int $userId): void
    {
        $user = User::findOrFail($userId);

        if (! $user->package?->stripe_price_id) {
            Flux::toast(variant: 'error', text: 'Package not synced to Stripe.');

            return;
        }

        $paymentMethod = $user->defaultPaymentMethod();

        $user->newSubscription('default', $user->package->stripe_price_id)
            ->create($paymentMethod?->id);

        $user->update([
            'status' => 'active',
            'deactivated_at' => null,
        ]);

        Flux::toast(variant: 'success', text: "{$user->name} reactivated.");
    }

    #[Computed]
    public function members()
    {
        return User::query()
            ->with('package')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->packageFilter, fn ($q) => $q->where('package_id', $this->packageFilter))
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.members')
            ->layout('layouts.app');
    }
}
