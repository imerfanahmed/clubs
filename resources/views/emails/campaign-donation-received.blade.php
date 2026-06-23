@component('mail::message')
# Thank You, {{ $donation->displayName() }}!

We have received your contribution to **{{ $donation->campaign->title }}**.

@if ($donation->type === \App\Models\CampaignDonation::TYPE_MONEY)
**Amount:** {{ $donation->amountFormatted() }}
**Method:** {{ ucfirst($donation->payment_method) }}
@else
**Pledge:** {{ $donation->pledge_quantity }} {{ $donation->pledgeItem?->unit ?? '' }} of {{ $donation->pledgeItem?->name }}
@endif

**Reference:** {{ $donation->reference }}

Your contribution is currently **pending confirmation** by our team. We will be in touch shortly to arrange the details. JazakAllah Khair for your generosity.

@component('mail::button', ['url' => route('campaigns.show', $donation->campaign)])
View Campaign
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
