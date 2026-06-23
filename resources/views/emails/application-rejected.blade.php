@component('mail::message')
# Membership Application Update

Thank you for your interest in {{ config('app.name') }}.

Unfortunately, your membership application has been reviewed and we are unable to approve it at this time.

**Reason:** {{ $user->rejection_reason }}

If you have any questions, please contact us.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
