<?php

namespace Src\Subscription\Domain\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Src\Subscription\Domain\Models\Subscription;

trait HasSubscription
{
    /**
     * Get the subscribable model's subscription.
     */
    public function subscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscribable');
    }

    /**
     * Determine if the model has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    /**
     * Determine if the model is subscribed to a specific plan.
     */
    public function isSubscribedTo(string $planSlug): bool
    {
        return $this->hasActiveSubscription() && $this->subscription->plan->slug === $planSlug;
    }

    /**
     * Get a specific feature's value from the subscription plan.
     * Example: $pharmacy->getPlanFeature('patient_cap', 0);
     */
    public function getPlanFeature(string $featureKey, $default = null)
    {
        if (! $this->hasActiveSubscription()) {
            return $default;
        }

        // Access nested feature data, e.g., features['limits']['patient_cap']
        return data_get($this->subscription->plan->features, $featureKey, $default);
    }
}
