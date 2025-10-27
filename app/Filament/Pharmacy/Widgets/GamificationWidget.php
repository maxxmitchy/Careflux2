<?php

namespace App\Filament\Pharmacy\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Src\Gamification\Domain\Enums\PharmacistLevel;

class GamificationWidget extends Widget
{
    protected string $view = 'filament.pharmacy.widgets.gamification-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getLevelData(): array
    {
        $user = Filament::auth()->user();

        $levels = [
            PharmacistLevel::Bronze->value => ['threshold' => 0, 'next' => PharmacistLevel::Silver],
            PharmacistLevel::Silver->value => ['threshold' => 500, 'next' => PharmacistLevel::Gold],
            PharmacistLevel::Gold->value => ['threshold' => 2000, 'next' => null],
        ];

        /** @var PharmacistLevel $currentLevelEnum */
        $currentLevelEnum = $user->level;
        $currentLevelName = $currentLevelEnum->value;

        // --- THIS IS THE DEFINITIVE, TYPE-SAFE FIX ---

        /** @var ?PharmacistLevel $nextLevelEnum */
        $nextLevelEnum = $levels[$currentLevelName]['next'];

        // If there is no next level (i.e., they are at Gold), return max level data.
        if (is_null($nextLevelEnum)) {
            return [
                'currentLevel' => $currentLevelName,
                'points' => $user->points_balance,
                'progress' => 100,
                'pointsToNext' => 0,
                'nextLevel' => 'Max Level',
            ];
        }

        // If we get here, we know $nextLevelEnum is a valid enum instance.
        $nextLevelName = $nextLevelEnum->value;

        $currentLevelThreshold = $levels[$currentLevelName]['threshold'];
        $nextLevelThreshold = $levels[$nextLevelName]['threshold'];

        $pointsInLevel = $user->points_balance - $currentLevelThreshold;
        $pointsForLevel = $nextLevelThreshold - $currentLevelThreshold;

        // Ensure progress is never negative or over 100.
        $progress = $pointsForLevel > 0
            ? min(100, max(0, ($pointsInLevel / $pointsForLevel) * 100))
            : 100;

        return [
            'currentLevel' => $currentLevelName,
            'points' => $user->points_balance,
            'progress' => round($progress),
            'pointsToNext' => max(0, $nextLevelThreshold - $user->points_balance),
            'nextLevel' => $nextLevelName,
        ];
        // --- END OF FIX ---
    }
}
