<?php

namespace App\Filament\Resources\DeliveryAnimations\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliveryAnimationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('headline')->required(),
                TextInput::make('subheadline')->required(),
                Textarea::make('description')->required(),
                TextInput::make('cta_text')->required(),
                TextInput::make('cta_url')->required()->url(),
                FileUpload::make('image_path')->image()->disk('public')->directory('animations')->imageEditor()->required(),
                Toggle::make('is_active')->helperText('Activating this will deactivate all others.'),
            ]);
    }
}
