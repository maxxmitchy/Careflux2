<?php

namespace Src\Order\Application\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Order\Domain\Contracts\PlaceOrderActionInterface;
use Src\Order\Domain\Models\Invoice;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;
use Src\User\Domain\DTOs\CustomerDataDTO;
use Src\User\Domain\DTOs\ShippingDataDTO;

class PlaceOrderAction implements PlaceOrderActionInterface
{
    public function execute(
        CustomerDataDTO $customerData,
        ?ShippingDataDTO $shippingData,
        Collection $cartItems
    ): Collection {
        return DB::transaction(function () use ($customerData, $shippingData, $cartItems) {
            $user = Auth::user() ?? $this->findOrCreateUser($customerData);
            $patient = $this->findOrCreatePatient($user, $customerData);

            $itemsByPharmacy = $cartItems->groupBy('pharmacyId');
            $createdInvoices = collect();

            foreach ($itemsByPharmacy as $pharmacyId => $pharmacyCartItems) {
                if (is_null($pharmacyId)) {
                    continue;
                }

                $pharmacistId = $pharmacyCartItems->first()->pharmacistId ?? null;
                if (is_null($pharmacistId)) {
                    continue;
                }

                $subtotal = $pharmacyCartItems->sum(fn ($item) => $item->quantity * $item->price);

                // Simplified delivery fee logic for now
                $deliveryFee = 50000; // ₦500 in kobo
                $total = $subtotal + $deliveryFee;

                $finalShippingData = $shippingData ?? $customerData;

                $invoice = Invoice::create([
                    'patient_id' => $patient->id,
                    'user_id' => $pharmacistId, // The pharmacist managing the order
                    'pharmacy_id' => $pharmacyId,
                    'invoice_number' => 'INV-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)),
                    'status' => 'pending_payment',
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'total' => $total,
                    'shipping_name' => $finalShippingData->full_name,
                    'shipping_phone' => $finalShippingData->phone,
                    'shipping_location_area' => $finalShippingData->location_area,
                ]);

                $invoiceItems = $pharmacyCartItems->map(fn ($item) => [
                    'description' => $item->productName,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => ($item->quantity * $item->price),
                    'coupon_id' => $item->applied_coupon_id ?? null,
                    'discount_amount' => $item->discount_amount ?? 0,
                ])->all();

                $invoice->items()->createMany($invoiceItems);

                $invoice->update([
                    'total' => $invoice->items()->sum('total') + $invoice->delivery_fee,
                ]);

                $createdInvoices->push($invoice);
            }

            return $createdInvoices;
        });
    }

    private function findOrCreateUser(CustomerDataDTO $customerData): User
    {
        return User::firstOrCreate(
            ['email' => $customerData->email],
            [
                'name' => $customerData->full_name,
                'phone' => $customerData->phone,
                'password' => Hash::make(Str::random(16)),
                'is_patient' => true,
            ]
        );
    }

    private function findOrCreatePatient(User $user, CustomerDataDTO $customerData): Patient
    {
        return Patient::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $customerData->full_name,
                'phone' => $customerData->phone,
                'location_area' => $customerData->location_area,
            ]
        );
    }
}
