<?php

namespace App\Filament\Patient\Resources\Orders\Pages;

use App\Filament\Patient\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
