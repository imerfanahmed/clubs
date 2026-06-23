<?php

namespace App\Actions;

use App\Models\User;

class DeactivateMemberAction
{
    public function execute(User $user): void
    {
        if ($user->activeSubscription()) {
            $user->activeSubscription()->cancelNow();
        }

        $user->update([
            'status' => 'suspended',
            'deactivated_at' => now(),
        ]);
    }
}
