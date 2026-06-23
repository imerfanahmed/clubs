<?php

namespace App\Actions;

use App\Mail\ApplicationRejected;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class RejectMemberAction
{
    public function execute(User $user, string $reason): void
    {
        $user->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        Mail::to($user)->queue(new ApplicationRejected($user));
    }
}
