<?php

namespace App\Filament\Pharmacy\Resources\Coupons\Pages;

use App\Filament\Pharmacy\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = Auth::id();
        $data['pharmacy_id'] = Auth::user()->pharmacy_id;
        $data['discount_amount'] = (int) ($data['discount_amount'] * 100);

        return $data;
    }
}
