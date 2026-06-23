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
        $package = $user->package;

        if (! $package || ! $package->stripe_price_id) {
            throw new \RuntimeException('User has no package or package not synced to Stripe.');
        }

        $paymentMethod = $user->defaultPaymentMethod();

        if (! $paymentMethod) {
            throw new \RuntimeException('User has no default payment method.');
        }

        $user->newSubscription('default', $package->stripe_price_id)
            ->create($paymentMethod->id);

        $user->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        Mail::to($user)->queue(new ApplicationApproved($user));
    }
}
