<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Api\V1\OrderStoreRequestData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Src\Order\Application\Actions\CreateApiOrderAction;
use Src\Order\Domain\DTOs\CartItemDTO;
use Src\Order\Domain\Models\Invoice;
use Src\Order\Domain\Models\Transaction;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Shared\Domain\Models\User;
use Src\User\Domain\DTOs\CustomerDataDTO as DomainCustomerDTO;

class OrderController extends Controller
{
    public function store(OrderStoreRequestData $requestData, CreateApiOrderAction $createApiOrderAction): JsonResponse
    {
        /** @var \Src\Pharmacy\Domain\Models\Pharmacy $actingTenant */
        $actingTenant = Auth::user();

        $customerDTO = DomainCustomerDTO::from($requestData->customer);

        $cartItems = collect($requestData->items)->map(function (array $item) {
            $product = PharmacyProduct::with(['pharmacy', 'user', 'medicationVariant.medication'])
                ->findOrFail($item['pharmacy_product_id']);

            return CartItemDTO::from([
                'cartKey' => 'api-'.uniqid(),
                'uniqueId' => 'pharmacy::'.$product->id,
                'type' => 'pharmacy',
                'productName' => $product->name,
                'sourceName' => $product->pharmacy->name,
                'imageUrl' => $product->image,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'pharmacyId' => $product->pharmacy_id,
                'pharmacistId' => $product->user_id,
                'isPrescription' => $product->is_prescription,
                'verificationId' => null,
                'productId' => $product->id,
            ]);
        });

        $invoices = $createApiOrderAction->execute(
            actingTenant: $actingTenant,
            customerData: $customerDTO,
            cartItems: $cartItems
        );

        $customerUser = $invoices->first()->patient->user;

        $transaction = $this->createParentTransaction($invoices, $customerUser, $actingTenant);

        return response()->json([
            'message' => 'Order created successfully. Proceed to payment.',
            'transaction_reference' => $transaction->reference,
        ], 201);
    }

    private function createParentTransaction(\Illuminate\Support\Collection $invoices, User $customerUser, \Src\Pharmacy\Domain\Models\Pharmacy $actingTenant): Transaction
    {
        $totalAmount = $invoices->sum('total');

        return Transaction::create([
            'reference' => Str::uuid(),
            'user_id' => $customerUser->id,
            'customer_id' => $actingTenant->id,
            'customer_type' => $actingTenant->getMorphClass(),
            'transactionable_id' => $invoices->first()->id,
            'transactionable_type' => Invoice::class,
            'amount' => $totalAmount,
            'currency' => 'NGN',
            'payment_gateway' => 'transactpay',
            'status' => 'pending',
            'metadata' => ['invoice_ids' => $invoices->pluck('id')->all()],
        ]);
    }
}
