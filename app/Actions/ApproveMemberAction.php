<?php

namespace App\Actions;

use App\Mail\ApplicationApproved;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ApproveMemberAction
{
    public function execute(User $user): void
    {
        $user->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        Mail::to($user)->queue(new ApplicationApproved($user));
    }
}
