<?php

namespace App\Filament\Resources\MedicationInformation\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class MedicationInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Core Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(debounce: 500)
                        // Automatically create the slug from the name
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true),
                        TextInput::make('generic_name'),
                        Toggle::make('is_published')->label('Published')->default(true),
                    ])->columns(2),

                Section::make('Content')
                    ->schema([
                        MarkdownEditor::make('description')->columnSpanFull(),
                        MarkdownEditor::make('how_to_use')->columnSpanFull(),
                        MarkdownEditor::make('side_effects')->columnSpanFull(),
                    ]),
                Section::make('Linked Products')
                    ->description('Associate all relevant product variations with this information page.')
                    ->schema([
                        Select::make('pharmacyProducts')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return PharmacyProduct::query()
                                    ->whereHas('medicationVariant.medication', function (Builder $query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('generic_name', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->getOptionLabelsUsing(function (array $values): array {
                                // 1. Fetch the full models from the database.
                                return PharmacyProduct::whereIn('id', $values)
                                    ->get()
                                    // 2. Now that we have the models, map them and call the 'name' accessor.
                                    ->pluck('name', 'id')
                                    ->all();
                            }),
                    ]),
                Section::make('People Also Ask (for SEO)')
                    ->schema([
                        KeyValue::make('people_also_ask')
                            ->label('')
                            ->keyLabel('Question')
                            ->valueLabel('Answer')
                            ->reorderable(),
                    ]),

                Section::make('Related Content & Products (for Advertising)')
                    ->schema([
                        Select::make('relatedContent')
                            ->label('Related Content Pages')
                            ->relationship('relatedContent', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Select other medication pages to show as "See Also".'),

                        Select::make('relatedProducts')
                            ->label('Related Pharmacy Products')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Select specific products to advertise on this page.')
                            ->getSearchResultsUsing(function (string $search) {
                                return PharmacyProduct::query()
                                    ->whereHas('medicationVariant.medication', function (Builder $query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->getOptionLabelsUsing(function (array $values): array {
                                // 1. Fetch the full models from the database.
                                return PharmacyProduct::whereIn('id', $values)
                                    ->get()
                                    // 2. Now that we have the models, map them and call the 'name' accessor.
                                    ->pluck('name', 'id')
                                    ->all();
                            }),
                    ])->collapsible(),
            ]);
    }
}
