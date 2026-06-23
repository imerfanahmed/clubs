@component('mail::message')
# Payment Failed

Hi {{ $user->name }},

We were unable to process your membership payment of **{{ number_format($amount / 100, 2) }} GBP**.

Please update your payment method to avoid any disruption to your membership.

@component('mail::button', ['url' => route('dashboard')])
Update Payment Method
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
