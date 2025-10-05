<?php

namespace App\Filament\Pharmacy\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('productable_id')
                    ->label('Product')
                    ->options(fn () => PharmacyProduct::where('pharmacy_id', Auth::user()->pharmacy_id)
                        ->with('medicationVariant.medication')->get()->pluck('name', 'id')
                    )
                    ->required(),
                TextInput::make('discount_amount')
                    ->label('Discount (Naira)')
                    ->numeric()->prefix('₦')->required(),
                DateTimePicker::make('expires_at')->required()->native(false)->default(now()->addDays(7)),
                Hidden::make('productable_type')->default(PharmacyProduct::class),
                Hidden::make('code')->default(fn () => 'CFX-'.Str::upper(Str::random(8))),
            ]);
    }
}
