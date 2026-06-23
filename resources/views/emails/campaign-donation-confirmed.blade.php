@component('mail::message')
# Contribution Confirmed

Assalamu Alaikum {{ $donation->displayName() }},

Your contribution to **{{ $donation->campaign->title }}** has been confirmed and now counts towards the campaign.

@if ($donation->type === \App\Models\CampaignDonation::TYPE_MONEY)
**Amount:** {{ $donation->amountFormatted() }}
@else
**Pledge:** {{ $donation->pledge_quantity }} {{ $donation->pledgeItem?->unit ?? '' }} of {{ $donation->pledgeItem?->name }}
@endif

**Reference:** {{ $donation->reference }}

JazakAllah Khair for supporting our community.

@component('mail::button', ['url' => route('campaigns.show', $donation->campaign)])
View Campaign
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
