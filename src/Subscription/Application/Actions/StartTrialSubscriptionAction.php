<?php

namespace Src\Subscription\Application\Actions;

use Illuminate\Support\Facades\Log;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Subscription\Domain\Models\Plan;

class StartTrialSubscriptionAction
{
    public function execute(Pharmacy $pharmacy): void
    {
        // 1. Safety check: If the pharmacy already has any subscription, do nothing.
        if ($pharmacy->subscription()->exists()) {
            return;
        }

        // 2. Find the official "starter" plan for pharmacies.
        //    We will need to seed this plan into the database later.
        $starterPlan = Plan::where('slug', 'pharmacy-starter-trial')->first();

        if (! $starterPlan) {
            Log::error('The pharmacy-starter-trial plan is missing from the database. Cannot start trial for Pharmacy ID: '.$pharmacy->id);

            return;
        }

        // 3. Create a new subscription record for the pharmacy.
        $pharmacy->subscription()->create([
            'plan_id' => $starterPlan->id,
            'payment_gateway' => 'trial',
            'expires_at' => now()->addMonths(2),
        ]);
    }
}
