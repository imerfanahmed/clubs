<?php

namespace App\Console\Commands;

use App\Mail\RenewalReminderMail;
use App\Models\ReminderLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendRenewalReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Send renewal reminder emails to members whose subscriptions expire in 3 days';

    public function handle(): void
    {
        $targetDate = now()->addDays(3)->startOfDay();

        $users = User::active()->get();

        $sent = 0;

        foreach ($users as $user) {
            $subscription = $user->activeSubscription();

            if (! $subscription || ! $subscription->asStripeSubscription()) {
                continue;
            }

            $periodEnd = $subscription->asStripeSubscription()->current_period_end;

            if (! $periodEnd) {
                continue;
            }

            $periodEndCarbon = Carbon::createFromTimestamp($periodEnd);

            if (! $periodEndCarbon->isSameDay($targetDate)) {
                continue;
            }

            $alreadySent = ReminderLog::where('user_id', $user->id)
                ->where('period_end', $periodEndCarbon)
                ->where('type', 'pre_renewal')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            Mail::to($user)->queue(new RenewalReminderMail($user, $periodEndCarbon));

            ReminderLog::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->stripe_id,
                'type' => 'pre_renewal',
                'period_end' => $periodEndCarbon,
                'sent_at' => now(),
            ]);

            $sent++;
        }

        $this->info("Sent {$sent} renewal reminders.");
    }
}
