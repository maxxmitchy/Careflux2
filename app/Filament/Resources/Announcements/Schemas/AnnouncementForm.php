<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Textarea::make('message')
                            ->required()
                            ->rows(3)
                            ->helperText('The main text to display in the banner.'),

                        TextInput::make('link_text')
                            ->nullable()
                            ->placeholder('e.g., Learn More')
                            ->helperText('(Optional) The text for the clickable link.'),

                        TextInput::make('link_url')
                            ->nullable()
                            ->url()
                            ->helperText('(Optional) The URL the link should point to.'),

                        Toggle::make('is_active')
                            ->label('Active on Homepage')
                            ->helperText('Activating this will automatically deactivate any other active announcement.'),
                    ]),
            ]);
    }
}
