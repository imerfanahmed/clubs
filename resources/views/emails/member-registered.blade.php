@component('mail::message')
# New Member Registration

A new applicant has registered:

**Name:** {{ $applicant->name }}
**Email:** {{ $applicant->email }}
**Phone:** {{ $applicant->phone }}
**Package:** {{ $applicant->package?->name ?? 'Not selected' }}

@component('mail::button', ['url' => route('admin.members.pending')])
Review Application
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
