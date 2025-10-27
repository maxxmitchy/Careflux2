<?php

namespace App\Filament\Resources\PharmacyShowcases\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PharmacyShowcaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->helperText('An internal name for this showcase, e.g., "Homepage B2B Section".')
                            ->columnSpanFull(),
                        TextInput::make('headline')->required(),
                        TextInput::make('subheadline')->required(),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Call to Action')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cta_text')
                            ->label('CTA Button Text')
                            ->required()
                            ->placeholder('e.g., Become a Partner'),
                        TextInput::make('cta_url')
                            ->label('CTA Button URL')
                            ->required()
                            ->url()
                            ->placeholder('e.g., /pharmacy/register'),
                    ]),

                Section::make('Visuals & Activation')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Background Image')
                            ->image()
                            ->disk('public')
                            ->directory('showcases')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active on Homepage')
                            ->helperText('Activating this will automatically deactivate any other active showcase.'),
                    ]),
            ]);
    }
}
