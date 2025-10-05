<?php

namespace Src\Pharmacy\Application\Actions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Src\Pharmacy\Domain\Models\Community;
use Src\Shared\Domain\Models\User;

class CreateCommunityAction
{
    /**
     * Executes the logic to create a new community.
     *
     * @param  User  $user  The user attempting to create the community.
     * @param  array  $data  The form data for the new community (e.g., ['name' => '...']).
     * @param  string  $type  The type of community to create ('personal' or 'pharmacy').
     * @return Community|string Returns the created Community on success, or a 'payment_required' string.
     *
     * @throws Exception If the user is not authorized or limits are exceeded.
     */
    public function execute(User $user, array $data, string $type): Community|string
    {
        return DB::transaction(function () use ($user, $data, $type) {
            if ($type === 'personal') {
                return $this->createPersonalCommunity($user, $data);
            }

            if ($type === 'pharmacy') {
                return $this->createPharmacyCommunity($user, $data);
            }

            throw new \InvalidArgumentException('Invalid community type specified.');
        });
    }

    private function createPersonalCommunity(User $user, array $data): Community|string
    {
        $personalCommunityCount = $user->communities()->count();

        if ($personalCommunityCount === 0) {
            // The first personal community is always free.
            return $this->createAndAttach($user, $data);
        }

        // For subsequent personal communities, a payment is required.
        // We return a specific string to signal the controller/action to initiate payment.
        return 'payment_required';
    }

    private function createPharmacyCommunity(User $user, array $data): Community
    {
        if (! $user->is_manager || ! $user->pharmacy) {
            throw new Exception('You do not have permission to create a community for this pharmacy.');
        }

        $pharmacy = $user->pharmacy;

        if (! $pharmacy->hasActiveSubscription()) {
            throw new Exception('Your pharmacy needs an active subscription to create communities.');
        }

        $limit = $pharmacy->getPlanFeature('limits.max_communities', 0);
        $currentCount = $pharmacy->communities()->count();

        if ($currentCount >= $limit) {
            throw new Exception("Your pharmacy has reached its limit of {$limit} communities. Please upgrade your plan.");
        }

        return $this->createAndAttach($pharmacy, $data, $user);
    }

    /**
     * Helper to create the community and attach the creator.
     */
    private function createAndAttach(Model $owner, array $data, ?User $creator = null): Community
    {
        $community = $owner->communities()->create([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        // The creator is always the first member of the team.
        $community->users()->sync([$creator?->id ?? $owner->id]);

        return $community;
    }
}
