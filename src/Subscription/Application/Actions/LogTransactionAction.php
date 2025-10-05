<?php

namespace Src\Subscription\Application\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Src\Order\Domain\Models\Transaction;
use Src\Shared\Domain\Models\User;

class LogTransactionAction
{
    /**
     * @param  User  $actingUser  The user performing the action (for auditing).
     * @param  Model  $customer  The entity that is the customer (e.g., Pharmacy).
     * @param  Model  $transactionable  The item being purchased (e.g., Plan).
     */
    public function execute(User $actingUser, Model $customer, Model $transactionable, int $amountInKobo, string $gateway): Transaction
    {
        return Transaction::create([
            'reference' => (string) Str::uuid(),
            'user_id' => $actingUser->id, // The user who clicked the button
            'customer_id' => $customer->id, // --- THE FIX: Polymorphic customer
            'customer_type' => $customer->getMorphClass(), // --- THE FIX: Polymorphic customer
            'transactionable_id' => $transactionable->id,
            'transactionable_type' => $transactionable->getMorphClass(),
            'amount' => $amountInKobo,
            'currency' => 'NGN',
            'payment_gateway' => $gateway,
            'status' => 'pending',
        ]);
    }
}
