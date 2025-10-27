<?php

namespace App\Filament\Pages;

use App\Settings\GamificationSettings;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use UnitEnum;

class Settings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    // We register both settings classes that this page will manage.
    // protected static string $settings = GeneralSettings::class;
    protected static string $settings = GamificationSettings::class;

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('General Settings')
                            ->schema([
                                Forms\Components\Toggle::make('enable_registrations')
                                    ->label('Enable All New User Registrations')
                                    ->helperText('Globally turn on or off the ability for any new user to register.'),

                                Forms\Components\TextInput::make('default_delivery_fee')
                                    ->label('Default Delivery Fee (in Naira)')
                                    ->numeric()
                                    ->step(50)
                                    ->prefix('₦')
                                    ->helperText('The base delivery fee for orders.'),
                                Forms\Components\FileUpload::make('site_og_image')
                                    ->label('Default Social Sharing (OG) Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('seo')
                                    ->helperText('Upload a 1200x630px image to be used when sharing links to the site.')
                                    ->columnSpanFull(),
                            ]),

                        // Tab::make('Gamification Settings')
                        //     ->schema([
                        //         Forms\Components\TextInput::make('point_to_ngn_conversion_rate')
                        //             ->label('Points to Naira Conversion Rate')
                        //             ->numeric()
                        //             ->required()
                        //             ->step(1)
                        //             ->helperText('The value of 1 point in Naira. (e.g., enter 60 for 1 point = ₦60)'),
                        //     ]),

                        Tab::make('Gamification Settings')
                            ->schema([
                                Forms\Components\TextInput::make('point_to_ngn_conversion_rate')
                                    ->label('Points to Naira Conversion Rate')
                                    ->helperText('The value of 1 point in Naira. (e.g., enter 60 for 1 point = ₦60)')
                                    ->numeric()
                                    ->required(),

                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('level_silver_threshold')
                                            ->label('Silver Level Threshold')
                                            ->numeric()
                                            ->required()
                                            ->default(500)
                                            ->helperText('Points required to reach Silver level.'),

                                        Forms\Components\TextInput::make('level_gold_threshold')
                                            ->label('Gold Level Threshold')
                                            ->numeric()
                                            ->required()
                                            ->default(2000)
                                            ->helperText('Points required to reach Gold level.'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * This method tells the settings page which other settings classes it should also manage.
     * This is crucial for saving the data from the 'Gamification Settings' tab.
     */
    protected function getRegisteredSettings(): array
    {
        return [
            GeneralSettings::class,
            GamificationSettings::class,
        ];
    }
}
