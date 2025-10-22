<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Src\Order\Application\Actions\VerifyTransactionAction;
use Src\Order\Domain\Models\Transaction;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Shared\Domain\Models\User;

class TransactionController extends Controller
{
    public function show(Request $request, Transaction $transaction): TransactionResource
    {
        $authenticatedEntity = $request->user();

        // --- THIS IS THE DEFINITIVE AUTHORIZATION FIX ---
        $isAuthorized = false;
        if ($authenticatedEntity instanceof User && $authenticatedEntity->id === $transaction->user_id) {
            // Case 1: The requester is the User who owns the transaction.
            $isAuthorized = true;
        } elseif ($authenticatedEntity instanceof Pharmacy && $authenticatedEntity->id === $transaction->customer_id) {
            // Case 2: The requester is the Pharmacy (Tenant) who is the customer on the transaction.
            $isAuthorized = true;
        }
        // --- END OF FIX ---

        if (! $isAuthorized) {
            abort(403, 'You are not authorized to view this transaction.');
        }

        if ($transaction->status !== 'pending') {
            abort(404, 'Transaction not found or has already been processed.');
        }

        return new TransactionResource($transaction->load('user'));
    }

    public function verify(Transaction $transaction, VerifyTransactionAction $verifyAction): JsonResponse
    {
        /** @var \Src\Pharmacy\Domain\Models\Pharmacy $actingTenant */
        $actingTenant = Auth::user();

        // AUTHORIZATION
        if ($transaction->customer_id !== $actingTenant->id || get_class($actingTenant) !== $transaction->customer_type) {
            abort(403, 'This token is not authorized to verify this transaction.');
        }

        // The transaction reference itself is what we need to verify with the payment gateway.
        $success = $verifyAction->execute($transaction->reference);

        return response()->json([
            'success' => $success,
            'status' => $transaction->fresh()->status,
        ]);
    }
}
