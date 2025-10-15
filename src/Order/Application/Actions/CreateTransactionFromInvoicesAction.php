<?php

namespace Src\Order\Application\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Src\Order\Domain\Models\Transaction;
use Src\Subscription\Application\Actions\LogTransactionAction;

class CreateTransactionFromInvoicesAction
{
    public function __construct(private LogTransactionAction $logTransactionAction) {}

    public function execute(Collection $invoices): Transaction
    {
        if ($invoices->isEmpty()) {
            throw new \InvalidArgumentException('Cannot create a transaction from an empty collection of invoices.');
        }

        $totalAmount = $invoices->sum('total');
        // The user who initiated the checkout is the primary actor.
        $actingUser = Auth::user() ?? $invoices->first()->patient->user;

        // In this B2C context, the transactionable entity (the subject of the purchase)
        // is most accurately the user/patient making the payment.
        $transactionable = $actingUser;

        // The customer in a marketplace model is often considered the end-user.
        $customer = $actingUser;

        $transaction = $this->logTransactionAction->execute(
            actingUser: $actingUser,
            customer: $customer,
            transactionable: $transactionable,
            amountInKobo: $totalAmount,
            gateway: 'transactpay'
        );

        // Add invoice IDs to metadata for reconciliation.
        $transaction->update(['metadata' => ['invoice_ids' => $invoices->pluck('id')->all()]]);

        return $transaction;
    }
}
