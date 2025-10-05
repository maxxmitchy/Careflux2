<?php

namespace App\Filament\Pharmacy\Resources\Communities\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Src\Shared\Domain\Models\User;

class CommunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('users')
                    ->label('Team Members')
                    ->helperText('Select other staff from your pharmacy to co-manage this community. You will be added automatically.')
                    ->multiple()
                    ->relationship('users', 'name')
                    ->options(function () {
                        $currentUser = Filament::auth()->user();

                        return User::where('pharmacy_id', $currentUser->pharmacy_id)
                            ->where('id', '!=', $currentUser->id) // Exclude the current user
                            ->pluck('name', 'id');
                    })
                    ->preload(),
            ]);
    }
}
