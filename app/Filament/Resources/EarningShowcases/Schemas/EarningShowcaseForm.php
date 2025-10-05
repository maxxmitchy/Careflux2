<?php

namespace App\Filament\Resources\EarningShowcases\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EarningShowcaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Main Content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->helperText('An internal name for this showcase, e.g., "Landing Page v2".')
                            ->columnSpanFull(),
                        TextInput::make('headline')->required(),
                        Textarea::make('description')->required()->rows(3)->columnSpanFull(),
                    ]),
                Section::make('Primary Call to Action')
                    ->description('The main button, typically for pharmacists.')
                    ->schema([
                        TextInput::make('cta_text')->required(),
                        TextInput::make('cta_url')->url()->required(),
                    ]),

                Section::make('Secondary Call to Action (Optional)')
                    ->description('An additional button, typically for patients/customers.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('secondary_cta_text')
                            ->label('Secondary CTA Button Text')
                            ->placeholder('e.g., Join as a Customer'),
                        TextInput::make('secondary_cta_url')
                            ->label('Secondary CTA Button URL')
                            ->url()
                            ->placeholder('e.g., /join'),
                    ]),
                Section::make('Visuals & Activation')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Pharmacist/Technician Image')
                            ->image()->disk('public')->directory('showcases')->required()
                            ->helperText('A professional image to display alongside the text content.'),
                        Toggle::make('is_active')
                            ->label('Active on Homepage')
                            ->helperText('Activating this will automatically deactivate any other active earning showcase.'),
                    ]),
            ]);
    }
}
