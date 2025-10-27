<x-mail::message>
# Reset Your Password

You are receiving this email because we received a password reset request for your account.

Click the button below to reset your password. This link is valid for 60 minutes.

<x-mail::button :url="$resetUrl">
Reset Password
</x-mail::button>

If you did not request a password reset, no further action is required. Your account is secure.

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
