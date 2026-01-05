<?php

namespace Src\Gamification\Application\Actions;

use App\Events\UserLeveledUp;
use App\Settings\GamificationSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Src\Gamification\Domain\Enums\PharmacistLevel;
use Src\Gamification\Domain\Models\Task; // <-- Import DB
use Src\Shared\Domain\Models\User; // <-- Import Settings
use Src\Wallet\Application\Services\WalletService; // <-- Import WalletService

class AwardPointsAction
{
    public function __construct(
        private WalletService $walletService,
        private GamificationSettings $gamificationSettings
    ) {}

    public function execute(User $user, string $taskKey, Model $subjectable): void
    {
        $taskDefinition = \Src\Gamification\Domain\Models\TaskDefinition::where('key', $taskKey)->first();
        if (! $taskDefinition || $taskDefinition->points <= 0) {
            return;
        }

        DB::transaction(function () use ($user, $taskDefinition, $subjectable) {
            // --- 1. AWARD GAMIFICATION POINTS ---
            $user->gamificationLedgerEntries()->create([
                'task_definition_id' => $taskDefinition->id,
                'subjectable_id' => $subjectable->id,
                'subjectable_type' => $subjectable->getMorphClass(),
                'points_awarded' => $taskDefinition->points,
            ]);

            $newPointsBalance = $user->points_balance + $taskDefinition->points;

            $user->increment('points_balance', $taskDefinition->points);

            // --- 2. CREDIT THE CASH WALLET (THE FIX) ---
            $conversionRate = $this->gamificationSettings->point_to_ngn_conversion_rate;
            if ($conversionRate > 0) {
                $amountToCredit = (int) (($taskDefinition->points * $conversionRate) * 100); // Convert NGN to Kobo

                $this->walletService->credit(
                    owner: $user,
                    amountInKobo: $amountToCredit,
                    description: "Reward for task: {$taskDefinition->name}",
                    sourceable: $subjectable instanceof Task ? $subjectable : null
                );
            }

            $this->checkAndApplyLevelUp($user, $newPointsBalance);
        });
    }

    /**
     * Checks if a user's new point balance qualifies them for a level up.
     */
    private function checkAndApplyLevelUp(User $user, int $newPointsBalance): void
    {
        $currentLevel = $user->level;
        $newLevel = null;

        // Check for Gold level (highest priority)
        if ($currentLevel !== PharmacistLevel::Gold && $newPointsBalance >= $this->gamificationSettings->level_gold_threshold) {
            $newLevel = PharmacistLevel::Gold;
        }
        // Check for Silver level
        elseif ($currentLevel === PharmacistLevel::Bronze && $newPointsBalance >= $this->gamificationSettings->level_silver_threshold) {
            $newLevel = PharmacistLevel::Silver;
        }

        // If a new level has been achieved...
        if ($newLevel) {
            $user->update(['level' => $newLevel]);

            // Dispatch an event to handle notifications and other side effects
            UserLeveledUp::dispatch($user, $newLevel);
        }
    }
}
