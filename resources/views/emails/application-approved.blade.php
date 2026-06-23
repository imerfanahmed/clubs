@component('mail::message')
# Welcome to {{ config('app.name') }}!

Your membership application has been approved.

**Package:** {{ $user->package?->name ?? 'N/A' }}
**Price:** {{ $user->package?->priceFormatted() ?? 'N/A' }}

Your first payment has been processed and your membership is now active.

@component('mail::button', ['url' => route('dashboard')])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
