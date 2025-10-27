<x-mail::message>
# Your Personal Pharmacist is Ready to Connect!

Hello {{ $patient->full_name }},

Great news! We have assigned a dedicated personal pharmacist to you from the Careflux network. They will be your primary point of contact for follow-ups, medication questions, and proactive health management.

<x-mail::panel>
**Your Assigned Pharmacist**<br>
**Name:** {{ $patient->pharmacist->name }}<br>
**Pharmacy:** {{ $patient->pharmacist->pharmacy->name }}<br>
**Contact:** {{ $patient->pharmacist->phone }} (WhatsApp available)
</x-mail::panel>

### What Happens Next?

You can expect {{ $patient->pharmacist->name }} to reach out to you via WhatsApp or phone within the next 48 hours to introduce themselves and complete your onboarding.

In the meantime, you can explore your personal Health Portal.

<x-mail::button :url="route('filament.patient.pages.dashboard')">
Go to Your Health Portal
</x-mail::button>

Welcome to a new standard of care.

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
