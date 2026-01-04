<?php

namespace App\Filament\Resources\Admin\ProductAlerts\Pages;

use App\Filament\Resources\Admin\ProductAlerts\ProductAlertResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductAlert extends CreateRecord
{
    protected static string $resource = ProductAlertResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = Auth::id();

        return $data;
    }
}
