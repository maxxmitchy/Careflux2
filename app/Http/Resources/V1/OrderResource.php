<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Find the parent transaction for this invoice
        $pendingTransaction = $this->resource->getPendingTransaction();

        return [
            'id' => $this->id,
            'orderNumber' => $this->invoice_number,
            'status' => $this->status,
            'datePlaced' => $this->created_at,
            'pharmacy' => new PharmacySimpleResource($this->whenLoaded('pharmacy')),
            'financials' => [
                'subtotal' => $this->subtotal,
                'deliveryFee' => $this->delivery_fee,
                'total' => $this->total,
                'currency' => 'NGN',
            ],
            'shippingDetails' => [
                'name' => $this->shipping_name,
                'phone' => $this->shipping_phone,
                'locationArea' => $this->shipping_location_area,
            ],

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'transactionReference' => $this->when($pendingTransaction, $pendingTransaction->reference),
        ];
    }
}
