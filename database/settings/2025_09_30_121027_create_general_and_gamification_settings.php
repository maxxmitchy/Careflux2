<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // General Settings
        $this->migrator->add('general.enable_registrations', true);
        $this->migrator->add('general.default_delivery_fee', 500.00);

        // Gamification Settings
        $this->migrator->add('gamification.point_to_ngn_conversion_rate', 60.0);
    }
};
