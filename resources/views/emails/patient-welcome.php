<x-mail::message>
# Welcome to Careflux, {{ $user->name }}!

@if($token)
Your personal pharmacist has created an account for you on Careflux. To get started and access your patient portal, please set a secure password for your account.
@else
Thank you for creating your account with Careflux. We're excited to help you on your journey to proactive health.
@endif

<x-mail::button :url="$url">
@if($token)
Set Your Password
@else
Go to Your Dashboard
@endif
</x-mail::button>

@if($token)
This password set link will expire in 60 minutes for your security.
@endif

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
