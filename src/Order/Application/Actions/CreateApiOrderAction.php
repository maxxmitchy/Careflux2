<?php

namespace Src\Order\Application\Actions;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Order\Domain\Models\Invoice;
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Shared\Domain\Models\User;
use Src\User\Domain\DTOs\CustomerDataDTO;

class CreateApiOrderAction
{
    /**
     * Handles the entire order creation process initiated via the API.
     *
     * @param  Pharmacy  $actingTenant  The authenticated Pharmacy tenant making the request.
     * @param  CustomerDataDTO  $customerData  The details of the end customer making the purchase.
     * @param  Collection  $cartItems  A collection of CartItemDTOs.
     * @return Collection A collection of the newly created Invoice models.
     *
     * @throws Exception
     */
    public function execute(Pharmacy $actingTenant, CustomerDataDTO $customerData, Collection $cartItems): Collection
    {
        return DB::transaction(function () use ($actingTenant, $customerData, $cartItems) {
            // Step 1: Find or Create the Patient's User account. This is the END USER.
            $patientUser = User::firstOrCreate(
                ['email' => $customerData->email],
                [
                    'name' => $customerData->full_name,
                    'phone' => $customerData->phone,
                    'password' => Hash::make(Str::random(16)),
                    'is_patient' => true,
                    'verified_at' => now(), // Auto-verify patient user accounts
                ]
            );

            // Step 2: Find or Create the Patient Profile for that User.
            $patient = Patient::firstOrCreate(
                ['user_id' => $patientUser->id],
                [
                    'full_name' => $customerData->full_name,
                    'phone' => $customerData->phone,
                    // We can pre-assign this patient to a default pharmacist from the tenant pharmacy
                    'pharmacist_id' => $actingTenant->users()->where('is_pharmacist', true)->first()?->id,
                ]
            );

            // The cart from a single storefront should only contain items from that one pharmacy.
            // We'll process it as one group.
            $pharmacyId = $cartItems->first()?->pharmacyId;

            // --- Security & Integrity Check ---
            if (is_null($pharmacyId) || $pharmacyId !== $actingTenant->id) {
                throw new Exception('Order creation failed: Cart contains items from an unauthorized or invalid pharmacy.');
            }

            // --- Invoice & Item Creation ---
            $subtotal = $cartItems->sum(fn ($item) => $item->quantity * $item->price);
            $deliveryFee = 50000; // ₦500 in kobo, placeholder
            $total = $subtotal + $deliveryFee;

            $invoice = Invoice::create([
                'patient_id' => $patient->id,
                // Assign the order management to the first available pharmacist in that pharmacy
                'user_id' => $actingTenant->users()->where('is_pharmacist', true)->first()?->id,
                'pharmacy_id' => $pharmacyId,
                'invoice_number' => 'INV-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)),
                'status' => 'pending_payment',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'shipping_name' => $customerData->full_name,
                'shipping_phone' => $customerData->phone,
                'shipping_location_area' => $customerData->location_area,
            ]);

            $invoiceItems = $cartItems->map(fn ($item) => [
                'description' => $item->productName,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => ($item->quantity * $item->price),
                'pharmacy_product_id' => $item->productId,
            ])->all();

            $invoice->items()->createMany($invoiceItems);

            return collect([$invoice]);
        });
    }
}
