<x-mail::message>
# NAFDAC Verification Alert

Hello {{ $product->user->name }},

Our system has flagged a potential issue with a NAFDAC number you provided for one of your product listings on Careflux.

**Product:** {{ $product->name }}
**NAFDAC Number Provided:** {{ $product->nafdac_number }}

<x-mail::panel>
**Reason for Flag:**<br>
{{ $reason }}
</x-mail::panel>

To ensure the safety and authenticity of all products on the platform, this item has been temporarily marked for review and may have limited visibility to patients.

Please review the product details and update the NAFDAC number at your earliest convenience.

<x-mail::button :url="route('filament.pharmacy.resources.pharmacy-product-resource.edit', ['record' => $product])">
Review & Correct Product
</x-mail::button>

If you believe this is an error or need assistance, please contact our support team.

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
