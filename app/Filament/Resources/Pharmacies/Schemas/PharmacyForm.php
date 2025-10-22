<?php

namespace App\Filament\Resources\Pharmacies\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Src\Location\Domain\Models\City;
use Src\Location\Domain\Models\State;

class PharmacyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Primary Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('phone')->tel(),
                        TextInput::make('api_token')
                            ->label('API Token')
                            // ->disabled()
                            ->columnSpanFull()
                            ->copyable()
                            ->extraInputAttributes(['class' => 'font-mono break-all'])
                            ->suffixAction(
                                Action::make('copy')
                                    ->icon('heroicon-m-clipboard')
                                    ->action(fn ($state) => \Filament\Notifications\Notification::make()
                                        ->title('Copied!')
                                        ->body('API token copied to clipboard.')
                                        ->success()
                                        ->send())
                                    ->requiresConfirmation(false)
                            ),
                        FileUpload::make('logo')->image()->disk('public')->directory('pharmacy-logos')->columnSpanFull(),
                    ]),

                // --- THIS IS THE COMPLETE, DYNAMIC LOCATION SECTION ---
                Section::make('Location & Address')
                    ->columns(2)
                    ->schema([
                        Select::make('country_id')
                            ->relationship('country', 'name')
                            ->searchable()->preload()->live()->required()
                            ->afterStateUpdated(fn (Set $set) => $set('state_id', null)), // Clear state on change

                        Select::make('state_id')
                            ->label('State')
                            ->options(fn (Get $get): array => State::where('country_id', $get('country_id'))->pluck('name', 'id')->all())
                            ->searchable()->preload()->live()->required()
                            ->afterStateUpdated(fn (Set $set) => $set('city_id', null)) // Clear city on change
                            ->createOptionForm([ // On-the-fly state creation
                                TextInput::make('name')->required(),
                            ])
                            ->createOptionUsing(function (array $data, Get $get): int {
                                return State::create(['country_id' => $get('country_id'), 'name' => $data['name']])->id;
                            }),

                        Select::make('city_id')
                            ->label('City')
                            ->options(fn (Get $get): array => State::find($get('state_id'))?->cities()->pluck('name', 'id')->all() ?? [])
                            ->searchable()->preload()->required()
                            ->createOptionForm([ // On-the-fly city creation
                                TextInput::make('name')->required(),
                            ])
                            ->createOptionUsing(function (array $data, Get $get): int {
                                return City::create(['state_id' => $get('state_id'), 'name' => $data['name']])->id;
                            }),

                        TextInput::make('address')->columnSpanFull(),
                    ]),
                // --- END OF LOCATION SECTION ---

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_approved')->label('Is Approved'),
                    ]),
            ]);
    }
}
