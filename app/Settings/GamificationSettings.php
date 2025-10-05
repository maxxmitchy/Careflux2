<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GamificationSettings extends Settings
{
    public float $point_to_ngn_conversion_rate; // e.g., 60 (meaning 1 point = 60 NGN)

    public int $level_silver_threshold; // Points required to reach Silver

    public int $level_gold_threshold;   // Points required to reach Gold

    public static function group(): string
    {
        return 'gamification';
    }
}
