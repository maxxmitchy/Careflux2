<?php

namespace App\Filament\Technician\Resources\Orders\Pages;

use App\Filament\Technician\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
