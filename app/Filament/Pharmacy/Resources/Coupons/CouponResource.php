<?php

namespace App\Filament\Pharmacy\Resources\Coupons;

use App\Filament\Pharmacy\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Pharmacy\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Pharmacy\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Pharmacy\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Pharmacy\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupon;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Ticket;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory & Stock';

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->where('pharmacy_id', Filament::auth()->user()->pharmacy_id);

        if (! Filament::auth()->user()->is_manager) {
            // Non-managers only see their own coupons
            $query->where('created_by_user_id', Filament::auth()->id());
        }

        return $query;
    }

    public static function getTabs(): array
    {
        if (! Filament::auth()->user()->is_manager) {
            return []; // No tabs for regular pharmacists
        }

        return [
            'all' => Tab::make(),
            'pending' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
            'active' => Tab::make('Active')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')->where('expires_at', '>', now())),
            'expired' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('expires_at', '<=', now())),
        ];
    }
}
