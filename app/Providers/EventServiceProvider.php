<?php

namespace App\Providers;

use App\Events\MemberRegistered;
use App\Listeners\SendMemberRegisteredNotification;
use App\Listeners\StripeWebhookHandler;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MemberRegistered::class => [
            SendMemberRegisteredNotification::class,
        ],
        WebhookReceived::class => [
            StripeWebhookHandler::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
