<?php

namespace App\Filament\Resources\Admin\ProductAlerts\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Admin\ProductAlerts\ProductAlertResource;

class CreateProductAlert extends CreateRecord
{
    protected static string $resource = ProductAlertResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = Auth::id();
        
        return $data;
    }
}
