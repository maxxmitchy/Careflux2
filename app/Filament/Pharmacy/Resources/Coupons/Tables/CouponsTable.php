<?php

namespace App\Filament\Pharmacy\Resources\Coupons\Tables;

use App\Models\Coupon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        $isManager = Filament::auth()->user()->is_manager;

        return $table
            ->columns([
                TextColumn::make('productable.name')->label('Product'),
                TextColumn::make('code')->copyable(),
                TextColumn::make('discount_amount')->money('NGN')->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray'
                    }),
                TextColumn::make('creator.name')->label('Created By')->toggleable(isToggledHiddenByDefault: ! $isManager),
                TextColumn::make('expires_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Coupon $record) => $record->update(['status' => 'approved', 'approved_by_user_id' => Auth::id()]))
                    ->visible(fn (Coupon $record): bool => $isManager && $record->status === 'pending'),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Coupon $record) => $record->update(['status' => 'rejected']))
                    ->visible(fn (Coupon $record): bool => $isManager && $record->status === 'pending'),
                EditAction::make()->visible(fn (Coupon $record): bool => $record->status === 'pending'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
