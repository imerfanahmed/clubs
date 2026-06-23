<?php

namespace App\Listeners;

use App\Events\MemberRegistered;
use App\Mail\MemberRegistered as MemberRegisteredMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SendMemberRegisteredNotification
{
    public function handle(MemberRegistered $event): void
    {
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin)->queue(new MemberRegisteredMail($event->user));
        }
    }
}
