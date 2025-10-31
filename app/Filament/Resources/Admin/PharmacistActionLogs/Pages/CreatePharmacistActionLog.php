<?php

namespace App\Filament\Resources\Admin\PharmacistActionLogs\Pages;

use App\Filament\Resources\Admin\PharmacistActionLogs\PharmacistActionLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePharmacistActionLog extends CreateRecord
{
    protected static string $resource = PharmacistActionLogResource::class;
}
