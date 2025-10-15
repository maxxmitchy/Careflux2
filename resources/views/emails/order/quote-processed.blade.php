<x-mail::message>
# Your Quote Request is Ready

Hello {{ $quoteRequest->patient_name }},

Our team has finished verifying the items you requested. Please review the summary below. You can add the available items to your cart by clicking the button.

### Available Items
@php
    $availableItems = $quoteRequest->items->where('status', 'available');
@endphp
@if($availableItems->isNotEmpty())
<x-mail::table>
| Product | Confirmed Price |
|:--------|----------------:|
@foreach($availableItems as $item)
| {{ $item->productable->product_name }} | ₦{{ number_format($item->negotiated_price / 100, 2) }} |
@endforeach
</x-mail::table>
@else
You have no available items in this request.
@endif

### Unavailable Items
@php
    $unavailableItems = $quoteRequest->items->where('status', 'unavailable');
@endphp
@if($unavailableItems->isNotEmpty())
<x-mail::table>
| Product |
|:--------|
@foreach($unavailableItems as $item)
| {{ $item->productable->product_name }} |
@endforeach
</x-mail::table>
@endif

<x-mail::button :url="route('track.request', ['quoteRequest' => $quoteRequest])">
View Quote & Add to Cart
</x-mail::button>

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
