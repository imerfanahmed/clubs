@component('mail::message')
# Membership Renewal Reminder

Hi {{ $user->name }},

Your membership will be renewed on **{{ $periodEnd->format('jS F Y') }}**.

**Package:** {{ $user->package?->name ?? 'N/A' }}
**Price:** {{ $user->package?->priceFormatted() ?? 'N/A' }}

No action is needed — the payment will be processed automatically.

@component('mail::button', ['url' => route('dashboard')])
View Membership
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
