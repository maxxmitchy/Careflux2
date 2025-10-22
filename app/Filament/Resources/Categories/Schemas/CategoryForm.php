<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->relationship('parent', 'name', modifyQueryUsing: fn ($query, ?Model $record) => $record ? $query->where('id', '!=', $record->id) : null)
                    ->searchable()->preload(),
                TextInput::make('name')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('icon')
                    ->label('Heroicon Name')
                    ->placeholder('e.g. shopping-cart, squares-2x2, heart')
                    ->helperText('Enter the Heroicon name (without prefix). Example: shopping-cart')
                    ->suffixIcon('heroicon-o-squares-2x2'),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('category_code')
                    ->label('Category Code')
                    ->required()
                    ->maxLength(10),
                Textarea::make('description')->columnSpanFull(),
                Toggle::make('is_visible')->label('Visible on site')->default(true),
                TextInput::make('sort_order')->numeric()->default(0),
            ]);
    }
}
