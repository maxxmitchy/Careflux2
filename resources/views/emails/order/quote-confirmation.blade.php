<x-mail::message>
# Quote Request Received

Hello {{ $quoteRequest->patient_name }},

Thank you for your request. We have received it and our Health Assistant team is now working to verify the price and availability of the items you requested.

You will receive another notification as soon as we have an update. You can also track the status of your request in real-time using the button below.

**Request Summary:**
<x-mail::table>
| Product |
|:--------|
@foreach($quoteRequest->items as $item)
| {{ $item->productable->product_name }} |
@endforeach
</x-mail::table>

<x-mail::button :url="route('track.request', ['quoteRequest' => $quoteRequest])">
Track Your Request
</x-mail::button>

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
