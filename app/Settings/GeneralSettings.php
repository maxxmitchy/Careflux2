<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public bool $enable_registrations;

    public ?float $default_delivery_fee;

    public ?string $site_og_image;

    public static function group(): string
    {
        return 'general';
    }
}
