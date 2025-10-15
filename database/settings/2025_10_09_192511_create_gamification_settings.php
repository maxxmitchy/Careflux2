<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('gamification.level_silver_threshold', 100.0);

        $this->migrator->add('gamification.level_gold_threshold', 300.0);
    }
};
