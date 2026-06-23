<?php

namespace App\Livewire\Admin;

use App\Actions\ApproveMemberAction;
use App\Actions\RejectMemberAction;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pending Members')]
class PendingMembers extends Component
{
    public ?int $rejectingUserId = null;

    public string $rejectionReason = '';

    public function approve(int $userId, ApproveMemberAction $action): void
    {
        $user = User::findOrFail($userId);

        try {
            $action->execute($user);
            Flux::toast(variant: 'success', text: "{$user->name} approved.");
        } catch (\Exception $e) {
            Flux::toast(variant: 'error', text: $e->getMessage());
        }
    }

    public function confirmReject(int $userId): void
    {
        $this->rejectingUserId = $userId;
        $this->rejectionReason = '';
    }

    public function reject(RejectMemberAction $action): void
    {
        $this->validate(['rejectionReason' => ['required', 'string', 'max:1000']]);

        $user = User::findOrFail($this->rejectingUserId);
        $action->execute($user, $this->rejectionReason);

        $this->reset('rejectingUserId', 'rejectionReason');

        Flux::toast(variant: 'success', text: "{$user->name} rejected.");
    }

    public function cancelReject(): void
    {
        $this->reset('rejectingUserId', 'rejectionReason');
    }

    #[Computed]
    public function pendingUsers()
    {
        return User::pending()->with('package', 'address')->latest()->get();
    }

    public function render()
    {
        return view('livewire.admin.pending-members')
            ->layout('layouts.app');
    }
}
