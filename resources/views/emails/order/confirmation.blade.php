<x-mail::message>
# Your Careflux Order is Confirmed!

Hello {{ $user->name }},

Thank you for your order. We've successfully received your payment and have notified the pharmacy to begin preparing your items.

---

## Order Summary

@foreach($invoices as $invoice)
**Order #{{ $invoice->invoice_number }}**
**From:** {{ $invoice->pharmacy->name }}

<x-mail::table>
| Product | Quantity | Price |
|:--------|:--------:|------:|
@foreach($invoice->items as $item)
| {{ $item->description }} | {{ $item->quantity }} | ₦{{ number_format(($item->price * $item->quantity) / 100, 2) }} |
@endforeach
</x-mail::table>

<div style="text-align: right; margin-top: 8px;">
    <strong>Subtotal:</strong> ₦{{ number_format($invoice->subtotal / 100, 2) }}<br>
    <strong>Delivery:</strong> ₦{{ number_format($invoice->delivery_fee / 100, 2) }}<br>
    <strong style="font-size: 1.1em;">Total for this order: ₦{{ number_format($invoice->total / 100, 2) }}</strong>
</div>

@if(!$loop->last)
<hr style="margin-top: 20px; margin-bottom: 20px;">
@endif
@endforeach

---

## Total Paid

<div style="text-align: right; font-size: 1.2em; font-weight: bold;">
    ₦{{ number_format($invoices->sum('total') / 100, 2) }}
</div>

You will receive a follow-up notification from your pharmacist once your order is ready for delivery. You can also view your order history in your Patient Portal.

<x-mail::button :url="route('filament.patient.pages.dashboard')">
View Your Dashboard
</x-mail::button>

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
