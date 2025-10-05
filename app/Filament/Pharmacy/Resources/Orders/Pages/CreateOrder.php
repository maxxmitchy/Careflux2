<?php

namespace App\Filament\Pharmacy\Resources\Orders\Pages;

use App\Filament\Pharmacy\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
