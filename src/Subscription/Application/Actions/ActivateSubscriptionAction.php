<?php

namespace Src\Subscription\Application\Actions;

use Src\Order\Domain\Models\Transaction;
use Src\Subscription\Domain\Models\Plan;

class ActivateSubscriptionAction
{
    public function execute(Transaction $transaction): void
    {
        if (! $transaction->transactionable instanceof Plan) {
            return;
        }

        $plan = $transaction->transactionable;
        $subscribable = $transaction->user->pharmacy;

        if (! $subscribable) {
            return;
        }

        // Use updateOrCreate to handle both new subscriptions and renewals seamlessly.
        $subscribable->subscription()->updateOrCreate(
            ['subscribable_id' => $subscribable->id, 'subscribable_type' => get_class($subscribable)],
            [
                'plan_id' => $plan->id,
                'payment_gateway' => $transaction->payment_gateway,
                'gateway_reference' => $transaction->reference, // Link to the parent transaction
                // If they already have a subscription, add time. If not, start from now.
                'expires_at' => $subscribable->subscription?->expires_at?->addMonth() ?? now()->addMonth(),
            ]
        );
    }
}
